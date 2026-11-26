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

Every grey box below is a command you can copy (use the copy button on the box) and paste into a terminal.
The commands work in **PowerShell** and **Command Prompt** on Windows. macOS/Linux versions are given where they differ.

### Step 1: Install the tools (once)

| Tool | Needed for | Download |
|---|---|---|
| XAMPP 8.2 (Apache, MySQL, PHP 8.2) | both versions | https://www.apachefriends.org/ |
| Composer | Laravel version | https://getcomposer.org/ |
| Node.js 18 or newer | Laravel version | https://nodejs.org/ |
| Git | downloading the code | https://git-scm.com/ |

Open a **new** terminal and check that they work. Each command should print a version number:

```
php -v
composer --version
node -v
git --version
```

If `php` is "not recognised", add `C:\xampp\php` to your Windows PATH, then open a new terminal.

### Step 2: Download the code

```
git clone https://github.com/abednegoingplaces/hostel-booking-system.git
cd hostel-booking-system
```

Run all the commands below from this `hostel-booking-system` folder unless a step says otherwise.

### Step 3: Start Apache and MySQL

Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL** (both turn green). You can open the panel with:

```
C:\xampp\xampp-control.exe
```

### Step 4a: Run the plain PHP version

**1. Copy the site into XAMPP's web folder:**

```
xcopy /E /I /Y hostel_booking C:\xampp\htdocs\hostel_booking
```

**2. Create the database and add the demo data** (always `database.sql` first, then `seed.sql`):

```
C:\xampp\mysql\bin\mysql.exe -u root -e "source hostel_booking/database.sql"
C:\xampp\mysql\bin\mysql.exe -u root -e "source hostel_booking/seed.sql"
```

**3. Open the site:** http://localhost/hostel_booking/ and log in with a [demo account](#demo-accounts-on-your-computer).

### Step 4b: Run the Laravel version

**1. Create the database:**

```
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS hostel_booking_laravel"
```

**2. Go into the Laravel folder:**

```
cd hostel_booking_laravel
```

**3. Set it up (first time only).** These commands download the packages, create the settings file and secret key, build the tables and demo data, link the photo folder and build the page styles. It takes a few minutes.

Windows:

```
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install
npm run build
```

macOS / Linux: the same commands, but use `cp .env.example .env` instead of the `copy` line.

**4. Start the site:**

```
php artisan serve
```

**5. Open the site:** http://127.0.0.1:8000 and log in with a [demo account](#demo-accounts-on-your-computer).
To stop the site, press **Ctrl + C** in the terminal.

**Next time** you only need to start MySQL in XAMPP and run these two commands from the `hostel-booking-system` folder:

```
cd hostel_booking_laravel
php artisan serve
```

`.env.example` already uses XAMPP's MySQL (user `root`, no password). If your MySQL has a password, put it after `DB_PASSWORD=` in the `.env` file.

### Step 5: Run the automated tests (optional)

From the `hostel_booking_laravel` folder:

```
php artisan test
```

All 48 tests should pass. They use a temporary in-memory database, so your demo data is not touched.

### Demo accounts (on your computer)

| Role | Email | Password |
|---|---|---|
| Warden | `warden@hostel.test` | `Warden@1234` |
| Student | `student@hostel.test` | `Student@1234` |

More students: `faith@hostel.test`, `kevin@hostel.test` and `mercy@hostel.test`, all with the student password.
The live site uses **different passwords**, which the team keeps privately.

### Reset the demo data

Plain PHP (from the `hostel-booking-system` folder):

```
C:\xampp\mysql\bin\mysql.exe -u root -e "source hostel_booking/database.sql"
C:\xampp\mysql\bin\mysql.exe -u root -e "source hostel_booking/seed.sql"
```

Laravel (from the `hostel_booking_laravel` folder):

```
php artisan migrate:fresh --seed
```

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
