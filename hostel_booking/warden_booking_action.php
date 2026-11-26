<?php
/**
 * Warden approves or rejects a pending booking.
 * Approval runs inside a transaction that locks the room row, re-counts the approved
 * bookings and refuses when the room is full, so two approvals can never overfill a room.
 */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');
require_post_with_csrf();

$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT) ?: 0;
$action    = $_POST['action'] ?? '';
$back      = 'warden_bookings.php';

if ($action === 'reject') {
    $stmt = db()->prepare("UPDATE bookings SET status = 'rejected' WHERE id = ? AND status = 'pending'");
    $stmt->execute([$bookingId]);
    flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Booking rejected.' : 'Only pending bookings can be rejected.');
    redirect($back);
}

if ($action !== 'approve') {
    flash('error', 'Unknown action.');
    redirect($back);
}

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ? FOR UPDATE');
    $stmt->execute([$bookingId]);
    $booking = $stmt->fetch();
    if (!$booking || $booking['status'] !== 'pending') {
        throw new RuntimeException('Only pending bookings can be approved.');
    }

    // Lock the room so no other approval for it can run at the same time.
    $stmt = $pdo->prepare('SELECT room_number, capacity FROM rooms WHERE id = ? FOR UPDATE');
    $stmt->execute([$booking['room_id']]);
    $room = $stmt->fetch();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND semester = ? AND status = 'approved'");
    $stmt->execute([$booking['room_id'], $booking['semester']]);
    if ((int) $stmt->fetchColumn() >= (int) $room['capacity']) {
        throw new RuntimeException('Room ' . $room['room_number'] . ' is already full for ' . $booking['semester'] . '. Reject this booking instead.');
    }

    $stmt = $pdo->prepare("SELECT 1 FROM bookings WHERE user_id = ? AND semester = ? AND status = 'approved'");
    $stmt->execute([$booking['user_id'], $booking['semester']]);
    if ($stmt->fetch()) {
        throw new RuntimeException('This student already has an approved room for ' . $booking['semester'] . '.');
    }

    $pdo->prepare("UPDATE bookings SET status = 'approved' WHERE id = ?")->execute([$bookingId]);
    $pdo->commit();
    flash('success', 'Booking approved. Room ' . $room['room_number'] . ' now has one bed fewer.');
} catch (RuntimeException $e) {
    $pdo->rollBack();
    flash('error', $e->getMessage());
}
redirect($back);
