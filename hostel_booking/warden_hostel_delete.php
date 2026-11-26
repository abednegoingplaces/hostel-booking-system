<?php
/** Warden: delete a hostel. Refused while any of its rooms has a pending or approved booking. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');
require_post_with_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT) ?: 0;

$stmt = db()->prepare(
    "SELECT COUNT(*) FROM bookings b JOIN rooms r ON r.id = b.room_id
     WHERE r.hostel_id = ? AND b.status IN ('pending', 'approved')"
);
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    flash('error', 'This hostel has pending or approved bookings. Reject or wait for them before deleting it.');
    redirect('warden_hostels.php');
}

// Remove the room photos from disk, then the hostel (its rooms go with it: ON DELETE CASCADE).
$photos = db()->prepare('SELECT photo_path FROM rooms WHERE hostel_id = ?');
$photos->execute([$id]);
foreach ($photos->fetchAll(PDO::FETCH_COLUMN) as $path) {
    delete_room_photo($path);
}

$stmt = db()->prepare('DELETE FROM hostels WHERE id = ?');
$stmt->execute([$id]);
flash($stmt->rowCount() ? 'success' : 'error', $stmt->rowCount() ? 'Hostel deleted.' : 'That hostel no longer exists.');
redirect('warden_hostels.php');
