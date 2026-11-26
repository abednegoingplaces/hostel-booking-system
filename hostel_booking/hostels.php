<?php
/** Public list of hostels with their rooms and free beds. */
require_once __DIR__ . '/includes/bootstrap.php';

$semester = selected_semester();
$hostels  = db()->query('SELECT * FROM hostels ORDER BY name')->fetchAll();

// Total rooms and free beds per hostel for the chosen semester.
$summary = [];
foreach (rooms_with_free_beds($semester) as $room) {
    $id = $room['hostel_id'];
    $summary[$id]['rooms'] = ($summary[$id]['rooms'] ?? 0) + 1;
    $summary[$id]['free']  = ($summary[$id]['free'] ?? 0) + max(0, (int) $room['free_beds']);
}

$pageTitle = 'Hostels';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Hostels</h1>
        <p class="muted">Free beds are shown for <?= e($semester) ?>.</p>
    </div>
    <form method="get" class="filters">
        <div>
            <label for="semester">Semester</label>
            <select id="semester" name="semester" onchange="this.form.submit()">
                <?php foreach (SEMESTERS as $s): ?>
                    <option value="<?= e($s) ?>" <?= $s === $semester ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>

<div class="grid">
    <?php foreach ($hostels as $hostel): ?>
        <?php $s = $summary[$hostel['id']] ?? ['rooms' => 0, 'free' => 0]; ?>
        <div class="card">
            <h3 style="margin-top:0"><?= e($hostel['name']) ?></h3>
            <p class="muted"><?= e($hostel['location']) ?> · <?= e(ucfirst($hostel['gender'])) ?></p>
            <p><?= e($hostel['description']) ?></p>
            <p><strong><?= $s['rooms'] ?></strong> rooms · <strong><?= $s['free'] ?></strong> free beds</p>
            <a class="button small" href="rooms.php?hostel_id=<?= (int) $hostel['id'] ?>&semester=<?= urlencode($semester) ?>">See rooms</a>
        </div>
    <?php endforeach; ?>
    <?php if (!$hostels): ?>
        <p class="muted">No hostels yet.</p>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
