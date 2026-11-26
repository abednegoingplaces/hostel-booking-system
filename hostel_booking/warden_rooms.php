<?php
/** Warden: list rooms with free beds and who is in each room for a semester. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');

$semester = selected_semester();
$hostelId = filter_input(INPUT_GET, 'hostel_id', FILTER_VALIDATE_INT) ?: null;
$hostels  = db()->query('SELECT id, name FROM hostels ORDER BY name')->fetchAll();
$rooms    = rooms_with_free_beds($semester, $hostelId);

// Names of the students with an approved booking in each room this semester.
$stmt = db()->prepare(
    "SELECT b.room_id, u.name FROM bookings b JOIN users u ON u.id = b.user_id
     WHERE b.semester = ? AND b.status = 'approved' ORDER BY u.name"
);
$stmt->execute([$semester]);
$occupants = [];
foreach ($stmt->fetchAll() as $row) {
    $occupants[$row['room_id']][] = $row['name'];
}

$pageTitle = 'Manage rooms';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Manage rooms</h1>
    <a class="button" href="warden_room_form.php<?= $hostelId ? '?hostel_id=' . $hostelId : '' ?>">Add room</a>
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
    <div style="min-width:auto"><button type="submit">Show</button></div>
</form>

<div class="card table-wrap">
    <table>
        <thead>
        <tr><th>Hostel</th><th>Room</th><th class="num">Beds</th><th class="num">Free</th><th class="num">Price</th><th>In the room (<?= e($semester) ?>)</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($rooms as $r): ?>
            <tr>
                <td><?= e($r['hostel_name']) ?></td>
                <td><strong><?= e($r['room_number']) ?></strong><?= $r['photo_path'] ? ' <span class="muted">· photo</span>' : '' ?></td>
                <td class="num"><?= (int) $r['capacity'] ?></td>
                <td class="num"><span class="badge <?= $r['free_beds'] > 0 ? 'free' : 'full' ?>"><?= (int) $r['free_beds'] ?></span></td>
                <td class="num"><?= money($r['price_per_semester']) ?></td>
                <td><?= e(implode(', ', $occupants[$r['id']] ?? [])) ?: '<span class="muted">Nobody yet</span>' ?></td>
                <td>
                    <div class="actions">
                        <a class="button small light" href="warden_room_form.php?id=<?= (int) $r['id'] ?>">Edit</a>
                        <form method="post" action="warden_room_delete.php" class="inline" onsubmit="return confirm('Delete this room?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                            <button type="submit" class="small danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rooms): ?>
            <tr><td colspan="7" class="muted">No rooms yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
