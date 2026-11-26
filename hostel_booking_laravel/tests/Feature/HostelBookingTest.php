<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HostelBookingTest extends TestCase
{
    use RefreshDatabase;

    private const SEM = 'Jan-Apr 2027';

    private function warden(): User
    {
        return User::factory()->warden()->create();
    }

    // ---- Public pages and API ---------------------------------------------------

    public function test_public_pages_load_with_seeded_data(): void
    {
        $this->seed();
        $hostel = Hostel::first();

        $this->get('/')->assertOk()->assertSee('Find and book your hostel room');
        $this->get(route('hostels.index'))->assertOk()->assertSee('Mt Kenya Hall');
        $this->get(route('hostels.show', $hostel))->assertOk()->assertSee('A101');
        $this->get(route('rooms.index'))->assertOk()->assertSee('Log in to book');
    }

    public function test_api_lists_rooms_with_free_beds(): void
    {
        $this->seed();

        $this->getJson('/api/rooms?semester='.urlencode(self::SEM))
            ->assertOk()
            ->assertJsonPath('semester', self::SEM)
            ->assertJsonPath('count', 7)
            ->assertJsonFragment(['room_number' => 'A103', 'capacity' => 1, 'free_beds' => 0]);

        $this->getJson('/api/rooms?semester=bad')->assertStatus(422);
    }

    // ---- Roles --------------------------------------------------------------------

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('bookings.index'))->assertRedirect(route('login'));
        $this->get(route('warden.bookings.index'))->assertRedirect(route('login'));
    }

    public function test_students_cannot_open_warden_pages(): void
    {
        $student = User::factory()->create();

        $this->actingAs($student)->get(route('warden.bookings.index'))->assertForbidden();
        $this->actingAs($student)->get(route('warden.hostels.create'))->assertForbidden();
        $this->actingAs($student)->post(route('warden.hostels.store'), ['name' => 'X'])->assertForbidden();
    }

    public function test_wardens_cannot_book_rooms(): void
    {
        $room = Room::factory()->create();

        $this->actingAs($this->warden())
            ->post(route('bookings.store'), ['room_id' => $room->id, 'semester' => self::SEM])
            ->assertForbidden();
    }

    public function test_registration_always_creates_a_student(): void
    {
        $this->post('/register', [
            'name' => 'Sneaky', 'email' => 'sneaky@test.com', 'role' => 'warden',
            'password' => 'Secret123!', 'password_confirmation' => 'Secret123!',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertSame('student', User::where('email', 'sneaky@test.com')->value('role'));
    }

    public function test_dashboard_sends_each_role_to_its_page(): void
    {
        $this->actingAs($this->warden())->get(route('dashboard'))->assertRedirect(route('warden.bookings.index'));
        $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertRedirect(route('rooms.index'));
    }

    // ---- Booking rules ------------------------------------------------------------

    public function test_student_books_a_room_and_it_is_pending(): void
    {
        $student = User::factory()->create();
        $room = Room::factory()->create(['capacity' => 2]);

        $this->actingAs($student)
            ->post(route('bookings.store'), ['room_id' => $room->id, 'semester' => self::SEM])
            ->assertRedirect(route('bookings.index'));

        $this->assertDatabaseHas('bookings', ['user_id' => $student->id, 'room_id' => $room->id, 'status' => 'pending']);
    }

    public function test_student_cannot_book_a_full_room(): void
    {
        $room = Room::factory()->create(['capacity' => 1]);
        Booking::factory()->status('approved')->create(['room_id' => $room->id, 'semester' => self::SEM]);
        $student = User::factory()->create();

        $this->actingAs($student)
            ->post(route('bookings.store'), ['room_id' => $room->id, 'semester' => self::SEM])
            ->assertSessionHasErrors('room_id');

        $this->assertDatabaseMissing('bookings', ['user_id' => $student->id]);
    }

    public function test_student_cannot_hold_two_active_bookings_in_one_semester(): void
    {
        $student = User::factory()->create();
        Booking::factory()->create(['user_id' => $student->id, 'semester' => self::SEM]);
        $other = Room::factory()->create();

        $this->actingAs($student)
            ->post(route('bookings.store'), ['room_id' => $other->id, 'semester' => self::SEM])
            ->assertSessionHasErrors('semester');
    }

    public function test_invalid_semester_is_rejected(): void
    {
        $room = Room::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('bookings.store'), ['room_id' => $room->id, 'semester' => 'Someday'])
            ->assertSessionHasErrors('semester');
    }

    public function test_student_can_cancel_own_pending_booking(): void
    {
        $student = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $student->id]);

        $this->actingAs($student)->patch(route('bookings.cancel', $booking))->assertRedirect(route('bookings.index'));

        $this->assertSame('cancelled', $booking->fresh()->status);
    }

    public function test_student_cannot_cancel_someone_elses_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs(User::factory()->create())->patch(route('bookings.cancel', $booking))->assertForbidden();

        $this->assertSame('pending', $booking->fresh()->status);
    }

    public function test_student_cannot_cancel_an_approved_booking(): void
    {
        $student = User::factory()->create();
        $booking = Booking::factory()->status('approved')->create(['user_id' => $student->id]);

        $this->actingAs($student)->patch(route('bookings.cancel', $booking))->assertForbidden();
    }

    // ---- Warden approvals ---------------------------------------------------------

    public function test_warden_approves_and_free_beds_go_down(): void
    {
        $room = Room::factory()->create(['capacity' => 2]);
        $booking = Booking::factory()->create(['room_id' => $room->id, 'semester' => self::SEM]);

        $this->actingAs($this->warden())->patch(route('warden.bookings.approve', $booking))->assertSessionHas('success');

        $this->assertSame('approved', $booking->fresh()->status);
        $this->assertSame(1, Room::withFreeBeds(self::SEM)->find($room->id)->free_beds);
    }

    public function test_warden_cannot_approve_into_a_full_room(): void
    {
        $room = Room::factory()->create(['capacity' => 1]);
        Booking::factory()->status('approved')->create(['room_id' => $room->id, 'semester' => self::SEM]);
        $pending = Booking::factory()->create(['room_id' => $room->id, 'semester' => self::SEM]);

        $this->actingAs($this->warden())->patch(route('warden.bookings.approve', $pending))->assertSessionHas('error');

        $this->assertSame('pending', $pending->fresh()->status);
    }

    public function test_warden_rejects_a_pending_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->actingAs($this->warden())->patch(route('warden.bookings.reject', $booking));

        $this->assertSame('rejected', $booking->fresh()->status);
    }

    // ---- Warden CRUD, validation and uploads --------------------------------------

    public function test_warden_hostel_crud(): void
    {
        $warden = $this->warden();

        $this->actingAs($warden)->post(route('warden.hostels.store'), [
            'name' => 'Test Hall', 'location' => 'North Campus', 'gender' => 'mixed', 'description' => '<script>alert(1)</script>',
        ])->assertRedirect(route('warden.hostels.index'));
        $hostel = Hostel::where('name', 'Test Hall')->firstOrFail();

        $this->actingAs($warden)->get(route('warden.hostels.index'))
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false); // escaped output

        $this->actingAs($warden)->put(route('warden.hostels.update', $hostel), [
            'name' => 'Test Hall 2', 'location' => 'North Campus', 'gender' => 'female',
        ])->assertRedirect(route('warden.hostels.index'));
        $this->assertSame('Test Hall 2', $hostel->fresh()->name);

        $this->actingAs($warden)->delete(route('warden.hostels.destroy', $hostel));
        $this->assertModelMissing($hostel);
    }

    public function test_hostel_validation_errors_keep_old_input(): void
    {
        $this->actingAs($this->warden())
            ->from(route('warden.hostels.create'))
            ->post(route('warden.hostels.store'), ['name' => '', 'location' => 'Somewhere', 'gender' => 'robots'])
            ->assertRedirect(route('warden.hostels.create'))
            ->assertSessionHasErrors(['name', 'gender'])
            ->assertSessionHasInput('location', 'Somewhere');
    }

    public function test_warden_adds_room_with_photo_and_duplicates_are_refused(): void
    {
        Storage::fake('public');
        $warden = $this->warden();
        $hostel = Hostel::factory()->create();

        $this->actingAs($warden)->post(route('warden.rooms.store'), [
            'hostel_id' => $hostel->id, 'room_number' => 'x1', 'capacity' => 2, 'price_per_semester' => 9000,
            'photo' => UploadedFile::fake()->image('room.jpg', 400, 300),
        ])->assertRedirect();

        $room = Room::where('room_number', 'X1')->firstOrFail();
        Storage::disk('public')->assertExists($room->photo_path);

        $this->actingAs($warden)->post(route('warden.rooms.store'), [
            'hostel_id' => $hostel->id, 'room_number' => 'X1', 'capacity' => 2, 'price_per_semester' => 9000,
        ])->assertSessionHasErrors('room_number');
    }

    public function test_non_image_upload_is_refused(): void
    {
        Storage::fake('public');
        $hostel = Hostel::factory()->create();

        // A real file named .jpg that actually holds PHP code: the content check must catch it.
        $path = tempnam(sys_get_temp_dir(), 'evil');
        file_put_contents($path, '<?php echo "hacked"; ?>');
        $evil = new UploadedFile($path, 'evil.jpg', 'image/jpeg', null, true);

        $this->actingAs($this->warden())->post(route('warden.rooms.store'), [
            'hostel_id' => $hostel->id, 'room_number' => 'X2', 'capacity' => 2, 'price_per_semester' => 9000,
            'photo' => $evil,
        ])->assertSessionHasErrors('photo');
        $this->assertDatabaseMissing('rooms', ['room_number' => 'X2']);
    }

    public function test_room_with_active_bookings_cannot_be_deleted(): void
    {
        $room = Room::factory()->create();
        Booking::factory()->create(['room_id' => $room->id]);

        $this->actingAs($this->warden())->delete(route('warden.rooms.destroy', $room))->assertSessionHas('error');

        $this->assertModelExists($room);
    }

    public function test_room_cannot_shrink_below_approved_students(): void
    {
        $room = Room::factory()->create(['capacity' => 2]);
        Booking::factory()->status('approved')->count(2)->create(['room_id' => $room->id, 'semester' => self::SEM]);

        $this->actingAs($this->warden())->put(route('warden.rooms.update', $room), [
            'hostel_id' => $room->hostel_id, 'room_number' => $room->room_number, 'capacity' => 1, 'price_per_semester' => 5000,
        ])->assertSessionHasErrors('capacity');
    }
}
