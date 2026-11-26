<?php
/** Warden: all bookings, filtered by status and semester, with approve and reject buttons. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');

const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];

$status   = in_array($_GET['status'] ?? '', STATUSES, true) ? $_GET['status'] : 'pending';
$semester = in_array($_GET['semester'] ?? '', SEMESTERS, true) ? $_GET['semester'] : '';

$sql = "SELECT b.*, u.name AS student_name, u.email, r.room_number, r.capacity, h.name AS hostel_name,
               r.capacity - (SELECT COUNT(*) FROM bookings x
                             WHERE x.room_id = b.room_id AND x.semester = b.semester AND x.status = 'approved') AS free_beds
        FROM bookings b
        JOIN users u ON u.id = b.user_id
        JOIN rooms r ON r.id = b.room_id
        JOIN hostels h ON h.id = r.hostel_id
        WHERE b.status = ?";
$params = [$status];
if ($semester) {
    $sql .= ' AND b.semester = ?';
    $params[] = $semester;
}
$sql .= ' ORDER BY b.created_at, b.id';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$counts = db()->query('SELECT status, COUNT(*) FROM bookings GROUP BY status')->fetchAll(PDO::FETCH_KEY_PAIR);

$pageTitle = 'Bookings';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>Bookings</h1>
        <p class="muted">Approving takes one free bed. A room that is already full cannot be approved.</p>
    </div>
</div>

<form method="get" class="filters card">
    <div>
        <label for="status">Status</label>
        <select id="status" name="status">
            <?php foreach (STATUSES as $s): ?>
                <option value="<?= $s ?>" <?= $s === $status ? 'selected' : '' ?>><?= ucfirst($s) ?> (<?= (int) ($counts[$s] ?? 0) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="semester">Semester</label>
        <select id="semester" name="semester">
            <option value="">All semesters</option>
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
        <tr><th>Student</th><th>Hostel / room</th><th>Semester</th><th class="num">Free beds</th><th>Status</th><th>Booked on</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><strong><?= e($b['student_name']) ?></strong><br><span class="muted"><?= e($b['email']) ?></span></td>
                <td><?= e($b['hostel_name']) ?> · <?= e($b['room_number']) ?></td>
                <td><?= e($b['semester']) ?></td>
                <td class="num"><?= (int) $b['free_beds'] ?> of <?= (int) $b['capacity'] ?></td>
                <td><span class="badge <?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td>
                <td><?= e(date('j M Y', strtotime($b['created_at']))) ?></td>
                <td>
                    <?php if ($b['status'] === 'pending'): ?>
                        <div class="actions">
                            <form method="post" action="warden_booking_action.php" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                                <button type="submit" name="action" value="approve" class="small success">Approve</button>
                                <button type="submit" name="action" value="reject" class="small danger">Reject</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
            <tr><td colspan="7" class="muted">No <?= e($status) ?> bookings.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
