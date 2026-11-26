<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Demo data: the same hostels, rooms, users and bookings as the plain PHP version,
 * plus a few extra students made by the factory.
 *
 * Logins: warden@hostel.test / Warden@1234   and   student@hostel.test / Student@1234
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->warden()->create(['name' => 'Grace Wanjiru', 'email' => 'warden@hostel.test', 'password' => 'Warden@1234']);

        $brian = User::factory()->create(['name' => 'Brian Otieno', 'email' => 'student@hostel.test', 'password' => 'Student@1234']);
        $faith = User::factory()->create(['name' => 'Faith Achieng', 'email' => 'faith@hostel.test', 'password' => 'Student@1234']);
        $kevin = User::factory()->create(['name' => 'Kevin Mwangi', 'email' => 'kevin@hostel.test', 'password' => 'Student@1234']);
        $mercy = User::factory()->create(['name' => 'Mercy Chebet', 'email' => 'mercy@hostel.test', 'password' => 'Student@1234']);
        User::factory(5)->create(['password' => 'Student@1234']); // extra students from the factory

        $mtKenya = Hostel::create(['name' => 'Mt Kenya Hall', 'location' => 'Main Campus, Block A', 'gender' => 'male', 'description' => 'Close to the library and the dining hall.']);
        $nyandarua = Hostel::create(['name' => 'Nyandarua Hall', 'location' => 'Main Campus, Block C', 'gender' => 'female', 'description' => 'Quiet hostel with a study room on every floor.']);
        $tana = Hostel::create(['name' => 'Tana Court', 'location' => 'Town Campus', 'gender' => 'mixed', 'description' => 'Self-contained rooms near the town campus.']);

        $room = fn (Hostel $h, string $no, int $beds, int $price) => $h->rooms()->create([
            'room_number' => $no, 'capacity' => $beds, 'price_per_semester' => $price,
        ]);
        $a101 = $room($mtKenya, 'A101', 2, 8500);
        $a102 = $room($mtKenya, 'A102', 4, 6500);
        $a103 = $room($mtKenya, 'A103', 1, 12000);
        $c201 = $room($nyandarua, 'C201', 2, 8500);
        $c202 = $room($nyandarua, 'C202', 3, 7000);
        $room($tana, 'T01', 1, 15000);
        $t02 = $room($tana, 'T02', 2, 11000);

        // Room A103 (1 bed) is already full for Jan-Apr 2027 and Kevin has a pending booking for it:
        // approving Kevin is refused, which shows the capacity rule in the demo.
        $book = fn (User $u, Room $r, string $semester, string $status) => Booking::factory()
            ->status($status)->create(['user_id' => $u->id, 'room_id' => $r->id, 'semester' => $semester]);
        $book($faith, $a103, 'Jan-Apr 2027', 'approved');
        $book($kevin, $a103, 'Jan-Apr 2027', 'pending');
        $book($mercy, $c201, 'Jan-Apr 2027', 'pending');
        $book($brian, $a102, 'Sep-Dec 2026', 'approved');
        $book($mercy, $c202, 'Sep-Dec 2026', 'rejected');
        $book($kevin, $t02, 'Sep-Dec 2026', 'cancelled');
    }
}
