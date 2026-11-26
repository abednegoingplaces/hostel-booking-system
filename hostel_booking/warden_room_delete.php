<?php
/** Warden: delete a room. Refused while it has a pending or approved booking. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');
require_post_with_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = db()->prepare("SELECT COUNT(*) FROM bookings WHERE room_id = ? AND status IN ('pending', 'approved')");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    flash('error', 'This room has pending or approved bookings, so it cannot be deleted.');
    redirect('warden_rooms.php');
}

$stmt = db()->prepare('SELECT photo_path FROM rooms WHERE id = ?');
$stmt->execute([$id]);
$room = $stmt->fetch();
if (!$room) {
    flash('error', 'That room no longer exists.');
    redirect('warden_rooms.php');
}

delete_room_photo($room['photo_path']);
db()->prepare('DELETE FROM rooms WHERE id = ?')->execute([$id]);
flash('success', 'Room deleted.');
redirect('warden_rooms.php');
