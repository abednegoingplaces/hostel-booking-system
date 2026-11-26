<?php
/** Room search: filter by hostel and semester, see free beds, and book (students). */
require_once __DIR__ . '/includes/bootstrap.php';

$semester = selected_semester();
$hostelId = filter_input(INPUT_GET, 'hostel_id', FILTER_VALIDATE_INT) ?: null;
$onlyFree = isset($_GET['only_free']);

$hostels = db()->query('SELECT id, name FROM hostels ORDER BY name')->fetchAll();
$rooms   = rooms_with_free_beds($semester, $hostelId);
if ($onlyFree) {
    $rooms = array_filter($rooms, fn ($r) => $r['free_beds'] > 0);
}

$pageTitle = 'Rooms';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Rooms</h1>
        <p class="muted">Free beds = beds in the room minus approved bookings for the semester.</p>
    </div>
</div>

<form method="get" class="filters card">
    <div>
        <label for="hostel_id">Hostel</label>
        <select id="hostel_id" name="hostel_id">
            <option value="">All hostels</option>
            <?php foreach ($hostels as $h): ?>
                <option value="<?= (int) $h['id'] ?>" <?= $hostelId === (int) $h['id'] ? 'selected' : '' ?>><?= e($h['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="semester">Semester</label>
        <select id="semester" name="semester">
            <?php foreach (SEMESTERS as $s): ?>
                <option value="<?= e($s) ?>" <?= $s === $semester ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="min-width:auto">
        <label><input type="checkbox" name="only_free" value="1" style="width:auto" <?= $onlyFree ? 'checked' : '' ?>> Only rooms with free beds</label>
    </div>
    <div style="min-width:auto"><button type="submit">Search</button></div>
</form>

<div class="grid">
    <?php foreach ($rooms as $room): ?>
        <div class="card room-card">
            <?php if ($room['photo_path']): ?>
                <img src="<?= e($room['photo_path']) ?>" alt="Room <?= e($room['room_number']) ?>">
            <?php else: ?>
                <div class="no-photo">No photo yet</div>
            <?php endif; ?>
            <h3>Room <?= e($room['room_number']) ?></h3>
            <p class="muted"><?= e($room['hostel_name']) ?> · <?= e(ucfirst($room['gender'])) ?></p>
            <p>
                <?= money($room['price_per_semester']) ?> per semester<br>
                <?= (int) $room['capacity'] ?> beds ·
                <?php if ($room['free_beds'] > 0): ?>
                    <span class="badge free"><?= (int) $room['free_beds'] ?> free</span>
                <?php else: ?>
                    <span class="badge full">Full</span>
                <?php endif; ?>
            </p>

            <?php if (is_student() && $room['free_beds'] > 0): ?>
                <form method="post" action="book.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="room_id" value="<?= (int) $room['id'] ?>">
                    <input type="hidden" name="semester" value="<?= e($semester) ?>">
                    <button type="submit" class="small">Book for <?= e($semester) ?></button>
                </form>
            <?php elseif (!current_user() && $room['free_beds'] > 0): ?>
                <a class="button small light" href="login.php">Log in to book</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <p class="muted">No rooms match your search.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
