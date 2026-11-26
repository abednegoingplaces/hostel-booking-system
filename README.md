# Hostel Booking System

A web app where **students book hostel rooms online** and the **warden approves or rejects each booking**.
No queues and no paper forms, and a room can never be overbooked.

We built the same system twice: first in **plain PHP**, then in **Laravel**.
This is our Group 27 project for the Computer Science course Web Systems and Technologies II.

**Live site (Laravel version):** https://group27-hostel-booking.onrender.com
The first visit can take about a minute while the free server wakes up.

---

## What it does

**Students can:**
- create an account and log in
- browse hostels and rooms, filtered by hostel and semester
- see how many beds are still free in each room
- book a room for a semester (one booking per semester)
- follow their booking's status, and cancel it while it is still pending

**The warden can:**
- add, edit and delete hostels and rooms, including a photo of each room
- see every booking and approve or reject the pending ones
- see who has been placed in each room

**Visitors who are not logged in** can browse hostels and rooms but cannot book.

There is also a public **JSON API**, `GET /api/rooms`, that lists every room and its free beds.

## How a booking works

1. A student books a room that still has a free bed. The booking is saved as **pending**.
2. The warden **approves** it, which takes one bed, or **rejects** it, which leaves the bed free.
3. The student sees the new status on their *My bookings* page.

**Free beds = the room's capacity minus its approved bookings for that semester.**
The number is worked out every time it is shown, so it is never out of date.
If two approvals race for the last bed, the second one is refused.

## The two versions

| | Plain PHP | Laravel 12 |
|---|---|---|
| Folder | [`hostel_booking/`](hostel_booking) | [`hostel_booking_laravel/`](hostel_booking_laravel) |
| Runs on | XAMPP (Apache + MySQL) | `php artisan serve` locally, Render online |
| Database | MySQL (`database.sql` + `seed.sql`) | MySQL locally, Postgres online (migrations + seeders) |
| Login | PHP sessions, `password_hash` | Laravel Breeze |
| Tests | 39-step check against the running site | 48 automated tests (`php artisan test`) |

Both versions use the same four tables, `users`, `hostels`, `rooms` and `bookings`, and the same demo data.

## Running it locally (step by step)

These steps are for Windows. They work the same on macOS and Linux with that system's versions of the tools.

### Step 1: Install the tools (once)

| Tool | Needed for | Get it | Check it works (in a new terminal) |
|---|---|---|---|
| XAMPP 8.2 (Apache, MySQL, PHP 8.2) | both versions | https://www.apachefriends.org/ | `php -v` shows 8.2 or newer |
| Composer | Laravel version | https://getcomposer.org/ | `composer --version` |
| Node.js 18 or newer | Laravel version | https://nodejs.org/ | `node -v` |
| Git (optional) | downloading the code | https://git-scm.com/ | `git --version` |

If `php` is "not recognised", add `C:\xampp\php` to your Windows PATH, then open a **new** terminal.

### Step 2: Get the code

With Git:

```bash
git clone https://github.com/abednegoingplaces/hostel-booking-system.git
cd hostel-booking-system
```

Without Git: on the GitHub page, click **Code > Download ZIP**, then unzip it.

### Step 3: Start XAMPP

Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`) and click **Start** next to **Apache** and **MySQL**. Both should turn green.

### Step 4a: Run the plain PHP version

1. Copy the `hostel_booking` folder into `C:\xampp\htdocs\`.
2. Open http://localhost/phpmyadmin and click **Import**.
3. Choose `hostel_booking/database.sql` and click **Go**. This creates the database and its tables.
4. Import `hostel_booking/seed.sql` the same way. This adds the demo data.
5. Open **http://localhost/hostel_booking/** and log in with a demo account (below).

### Step 4b: Run the Laravel version

1. In phpMyAdmin, open the **SQL** tab, run `CREATE DATABASE hostel_booking_laravel;` and click **Go**.
2. Open a terminal in the `hostel_booking_laravel` folder and run these commands one at a time:

   ```bash
   composer install             # downloads Laravel and the PHP packages
   copy .env.example .env       # creates the settings file (macOS/Linux: cp .env.example .env)
   php artisan key:generate     # creates the app's secret key
   php artisan migrate --seed   # creates the tables and the demo data
   php artisan storage:link     # lets uploaded room photos show on the site
   npm install                  # downloads the front-end packages
   npm run build                # builds the CSS and JavaScript for the login pages
   php artisan serve            # starts the site
   ```

3. Open **http://127.0.0.1:8000** and log in with a demo account (below).
4. To stop the site, press **Ctrl + C** in the terminal. Next time, you only need to start MySQL and run `php artisan serve`.

`.env.example` already points at XAMPP's MySQL (user `root`, no password). If your MySQL uses a password, put it in `DB_PASSWORD` in `.env`.

### Step 5: Run the tests (optional)

In the `hostel_booking_laravel` folder, run `php artisan test`. All 48 tests should pass. The tests use a temporary in-memory database, so your demo data is not touched.

### Demo accounts (on your computer)

| Role | Email | Password |
|---|---|---|
| Warden | `warden@hostel.test` | `Warden@1234` |
| Student | `student@hostel.test` | `Student@1234` |

More students: `faith@`, `kevin@` and `mercy@hostel.test`, all with the same student password.
The live site uses **different passwords**, which the team keeps privately.

To reset the demo data: import the two SQL files again for plain PHP, or run `php artisan migrate:fresh --seed` for Laravel.

## Try these in the demo

- Log in as a student and open `/warden/bookings`. You get **403 Forbidden**, because students cannot open warden pages.
- As the warden, try to approve Kevin's booking for room **A103**. It is refused, because the room is already full.
- As the warden, add a room with the same number as another room in the same hostel. You get a validation error.

## If something goes wrong

| Problem | Fix |
|---|---|
| "Could not connect to the database" / `SQLSTATE[HY000] [2002]` | MySQL isn't running. Start it in the XAMPP Control Panel. |
| Apache won't start (port 80 busy) | Close Skype or IIS, or change `Listen 80` to `Listen 8080` in XAMPP's `httpd.conf` and use http://localhost:8080/hostel_booking/ |
| `php`, `composer` or `npm` "not recognised" | Install the tool, add it to PATH, then open a **new** terminal. |
| Laravel: "No application encryption key" | Run `php artisan key:generate`. |
| Laravel: "Vite manifest not found" | Run `npm install` and then `npm run build`. |
| Laravel: "419 Page Expired" | The form sat open too long. Refresh the page and try again. |
| Laravel: port 8000 already in use | Run `php artisan serve --port=8010` and open http://127.0.0.1:8010 |
| Room photos don't show | Run `php artisan storage:link` (Laravel). For plain PHP, check that `uploads/rooms/` exists. |

## Project structure

```
hostel_booking/                 plain PHP version
  includes/                     database connection, login and role checks, header/footer
  warden_*.php                  warden pages (hostels, rooms, bookings)
  rooms_api.php                 JSON API
hostel_booking_laravel/         Laravel version
  app/Models/                   User, Hostel, Room, Booking
  app/Http/Controllers/         public pages, student bookings, Warden/ pages, Api/
  app/Http/Requests/            form validation
  app/Http/Middleware/          role check (role:warden, role:student)
  database/migrations/          tables
  database/seeders/             demo data
  resources/views/              Blade pages
  tests/Feature/                automated tests
  Dockerfile, docker/           used for the live site
HOW_TO_RUN.md                   detailed setup, and where each Week 14 requirement is in the code
```

## Live hosting

The Laravel version runs on **Render** (free plan, Docker), with a **Neon** Postgres database.
Every push to `main` deploys automatically. Settings and secrets live in Render's environment variables, not in this repository.
On the free plan, photos uploaded on the live site are lost when the server restarts.

## Team (Group 27)

| Member | Built and presents |
|---|---|
| Abednego Ndimu | Database and models; booking, approval and cancellation |
| Ayyubu Wanga | Hostel and room management, photo upload, JSON API |
| Stephen Macharia | Login, roles and security; slides and ER diagram |
