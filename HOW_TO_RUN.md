# Hostel Booking System: how to run it

The same system is built twice, as the proposal says:

- `hostel_booking` = plain PHP + MySQL, runs in XAMPP (Apache + MySQL)
- `hostel_booking_laravel` = Laravel 12 + Breeze, runs with `php artisan serve` (uses XAMPP's MySQL)

Demo logins (the same in both versions):

| Email                 | Password       | Role    |
|-----------------------|----------------|---------|
| warden@hostel.test    | Warden@1234    | warden  |
| student@hostel.test   | Student@1234   | student |
| faith@ / kevin@ / mercy@hostel.test | Student@1234 | student |

The demo data includes one full room (Mt Kenya Hall, A103, 1 bed, taken by Faith) and a pending booking for it from Kevin.
Approving Kevin is refused, which shows the capacity rule working.

---

## A. Plain PHP version

On this PC everything is already set up:

1. Open **C:\xampp\xampp-control.exe** and start **Apache** and **MySQL**.
2. Open **http://localhost/hostel_booking/**

The running copy is `C:\xampp\htdocs\hostel_booking`. The copy in this folder is the source.
If you edit the source, copy it into htdocs again.

### On another computer

1. Install XAMPP and start Apache and MySQL.
2. Copy the `hostel_booking` folder into `C:\xampp\htdocs\`.
3. In http://localhost/phpmyadmin, **Import** `database.sql`, then **Import** `seed.sql`.
4. Open http://localhost/hostel_booking/

To reset the demo data, import `database.sql` and `seed.sql` again.

---

## B. Laravel version

On this PC everything is already set up:

1. In the XAMPP Control Panel, start **MySQL**.
2. Open a terminal in the project:

   ```
   cd "C:\Users\Admin\Academics\Projects\BICT 216 Group Project\hostel_booking_laravel"
   php artisan serve
   ```

3. Open the address that `php artisan serve` prints (usually **http://127.0.0.1:8000**).
   If another app is already using port 8000 (for example the Section F project), run
   `php artisan serve --port=8010` and open http://127.0.0.1:8010.
4. Press **Ctrl + C** to stop the server.

| Command | What it does |
|---|---|
| `php artisan migrate:fresh --seed` | Rebuilds the tables and reloads the demo data |
| `php artisan route:list --except-vendor` | Shows every route (resource controllers, named routes) |
| `php artisan test` | Runs the 48 automated tests |

### On another computer

1. Install XAMPP (start MySQL), Composer and Node.js.
2. Copy `hostel_booking_laravel` (you can leave out `vendor` and `node_modules`).
3. In phpMyAdmin run `CREATE DATABASE hostel_booking_laravel;`
4. In a terminal inside the folder:

   ```
   composer install
   copy .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm install
   npm run build
   php artisan serve
   ```

`.env.production.example` is the production .env plan (debug off, secure cookies, own database user).

---

## Where each Week 14 requirement lives (Laravel version)

Use this table for the code walkthrough part of the presentation.

| Requirement | Where to show it |
|---|---|
| MVC, 3+ related models | `app/Models/User.php`, `Hostel.php`, `Room.php`, `Booking.php`; migrations in `database/migrations/2026_11_26_*` |
| Resource controllers, named routes, route model binding | `routes/web.php`; `app/Http/Controllers/Warden/HostelController.php` (`edit(Hostel $hostel)`) |
| Blade master layout and partials | `resources/views/layouts/master.blade.php`, `resources/views/partials/*` |
| Full CRUD | Warden hostels and rooms: `Warden/HostelController.php`, `Warden/RoomController.php` |
| Form Request validation, error feedback, old() | `app/Http/Requests/HostelRequest.php`, `RoomRequest.php`, `StoreBookingRequest.php`; `@error` and `old()` in `resources/views/warden/*/form.blade.php` |
| File upload | Room photo: `Warden/RoomController@store` (`store('rooms', 'public')`), `php artisan storage:link` |
| Authentication | Breeze: `routes/auth.php`, `app/Http/Controllers/Auth/*` |
| Authorization | `app/Http/Middleware/EnsureUserHasRole.php` (`role:warden`, `role:student`), `app/Policies/BookingPolicy.php` |
| Security | `@csrf` in every form, `{{ }}` escaping, `$fillable` (role and status are not fillable), Eloquent only |
| JSON API | `routes/api.php` → `GET /api/rooms`, `app/Http/Resources/RoomResource.php` |
| Seeders and factories | `database/seeders/DatabaseSeeder.php`, `database/factories/*` |
| Deployment plan | `.env.production.example` |

The booking rules (the part most worth explaining):

- Free beds = capacity minus **approved** bookings for that semester (`Room::scopeWithFreeBeds`).
- A student can hold only one pending or approved booking per semester, and cannot book a full room.
- Approving runs in a database transaction that locks the room row and re-counts approved bookings, so a room can never be overfilled (`Warden/BookingController@approve`).
- Students can cancel only their own pending bookings (`BookingPolicy@cancel`).
- A room or hostel with pending or approved bookings cannot be deleted.

## Small change from the proposal

Login is by **email** in both versions (Breeze's default), so the `users` table has no separate `username` column.
Everything else follows the proposal's database design.
