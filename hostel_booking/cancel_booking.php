<?php
/** A student cancels one of their own pending bookings. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('student');
require_post_with_csrf();

$bookingId = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);

// The WHERE clause checks ownership and status, so a student can never cancel
// someone else's booking or an already approved one, even with a forged form.
$stmt = db()->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
$stmt->execute([$bookingId ?: 0, $_SESSION['user_id']]);

if ($stmt->rowCount() === 1) {
    flash('success', 'Booking cancelled.');
} else {
    flash('error', 'That booking cannot be cancelled. Only your own pending bookings can be cancelled.');
}
redirect('my_bookings.php');
