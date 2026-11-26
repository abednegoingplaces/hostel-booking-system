<?php
/** Student books a room for a semester. The booking starts as "pending". */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('student');
require_post_with_csrf();

$userId   = (int) $_SESSION['user_id'];
$roomId   = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
$semester = $_POST['semester'] ?? '';
$back     = 'rooms.php?semester=' . urlencode(in_array($semester, SEMESTERS, true) ? $semester : DEFAULT_SEMESTER);

if (!$roomId || !in_array($semester, SEMESTERS, true)) {
    flash('error', 'Choose a valid room and semester.');
    redirect($back);
}

$room = null;
foreach (rooms_with_free_beds($semester) as $r) {
    if ((int) $r['id'] === $roomId) {
        $room = $r;
    }
}
if (!$room) {
    flash('error', 'That room does not exist.');
    redirect($back);
}
if ($room['free_beds'] <= 0) {
    flash('error', 'Room ' . $room['room_number'] . ' is full for ' . $semester . '.');
    redirect($back);
}

// One active (pending or approved) booking per student per semester.
$stmt = db()->prepare("SELECT 1 FROM bookings WHERE user_id = ? AND semester = ? AND status IN ('pending', 'approved')");
$stmt->execute([$userId, $semester]);
if ($stmt->fetch()) {
    flash('error', 'You already have a pending or approved booking for ' . $semester . '. Cancel it first to choose another room.');
    redirect('my_bookings.php');
}

db()->prepare("INSERT INTO bookings (user_id, room_id, semester, status) VALUES (?, ?, ?, 'pending')")
    ->execute([$userId, $roomId, $semester]);

flash('success', 'Booking sent for room ' . $room['room_number'] . ' (' . $room['hostel_name'] . '), ' . $semester . '. The warden will review it.');
redirect('my_bookings.php');
