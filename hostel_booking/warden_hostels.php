<?php
/** Warden: list hostels (the "R" in CRUD) with links to add, edit and delete. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');

$hostels = db()->query(
    'SELECT h.*, COUNT(r.id) AS room_count
     FROM hostels h LEFT JOIN rooms r ON r.hostel_id = h.id
     GROUP BY h.id ORDER BY h.name'
)->fetchAll();

$pageTitle = 'Manage hostels';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Manage hostels</h1>
    <a class="button" href="warden_hostel_form.php">Add hostel</a>
</div>

<div class="card table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Location</th><th>Gender</th><th class="num">Rooms</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($hostels as $h): ?>
            <tr>
                <td><strong><?= e($h['name']) ?></strong><br><span class="muted"><?= e($h['description']) ?></span></td>
                <td><?= e($h['location']) ?></td>
                <td><?= e(ucfirst($h['gender'])) ?></td>
                <td class="num"><?= (int) $h['room_count'] ?></td>
                <td>
                    <div class="actions">
                        <a class="button small light" href="warden_rooms.php?hostel_id=<?= (int) $h['id'] ?>">Rooms</a>
                        <a class="button small light" href="warden_hostel_form.php?id=<?= (int) $h['id'] ?>">Edit</a>
                        <form method="post" action="warden_hostel_delete.php" class="inline"
                              onsubmit="return confirm('Delete this hostel and all its rooms?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
                            <button type="submit" class="small danger">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$hostels): ?>
            <tr><td colspan="5" class="muted">No hostels yet. Add the first one.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
