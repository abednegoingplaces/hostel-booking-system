<?php
/** A student's own bookings. Only rows with this student's user_id are ever loaded. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('student');

$stmt = db()->prepare(
    'SELECT b.*, r.room_number, r.price_per_semester, h.name AS hostel_name
     FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     JOIN hostels h ON h.id = r.hostel_id
     WHERE b.user_id = ?
     ORDER BY b.created_at DESC, b.id DESC'
);
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();

$pageTitle = 'My bookings';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <div>
        <h1>My bookings</h1>
        <p class="muted">You can cancel a booking while it is still pending.</p>
    </div>
    <a class="button" href="rooms.php">Book a room</a>
</div>

<div class="card table-wrap">
    <table>
        <thead>
        <tr><th>Hostel</th><th>Room</th><th>Semester</th><th class="num">Price</th><th>Status</th><th>Booked on</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
            <tr>
                <td><?= e($b['hostel_name']) ?></td>
                <td><?= e($b['room_number']) ?></td>
                <td><?= e($b['semester']) ?></td>
                <td class="num"><?= money($b['price_per_semester']) ?></td>
                <td><span class="badge <?= e($b['status']) ?>"><?= e(ucfirst($b['status'])) ?></span></td>
                <td><?= e(date('j M Y', strtotime($b['created_at']))) ?></td>
                <td>
                    <?php if ($b['status'] === 'pending'): ?>
                        <form method="post" action="cancel_booking.php" class="inline" onsubmit="return confirm('Cancel this booking?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="booking_id" value="<?= (int) $b['id'] ?>">
                            <button type="submit" class="small light">Cancel</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?>
            <tr><td colspan="7" class="muted">You have no bookings yet.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
