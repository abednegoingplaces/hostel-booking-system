<?php
/**
 * JSON API: rooms with their free beds.
 *   GET rooms_api.php                         -> every room, default semester
 *   GET rooms_api.php?semester=Jan-Apr%202027 -> a chosen semester
 *   GET rooms_api.php?hostel_id=1             -> one hostel only
 */
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$semester = $_GET['semester'] ?? DEFAULT_SEMESTER;
if (!in_array($semester, SEMESTERS, true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Unknown semester.', 'semesters' => SEMESTERS]);
    exit;
}
$hostelId = filter_input(INPUT_GET, 'hostel_id', FILTER_VALIDATE_INT) ?: null;

$sql = "SELECT r.id, r.room_number, r.capacity, r.price_per_semester, r.photo_path,
               h.id AS hostel_id, h.name AS hostel_name, h.gender,
               r.capacity - (SELECT COUNT(*) FROM bookings b
                             WHERE b.room_id = r.id AND b.semester = ? AND b.status = 'approved') AS free_beds
        FROM rooms r JOIN hostels h ON h.id = r.hostel_id";
$params = [$semester];
if ($hostelId) {
    $sql .= ' WHERE h.id = ?';
    $params[] = $hostelId;
}
$sql .= ' ORDER BY h.name, r.room_number';
$stmt = db()->prepare($sql);
$stmt->execute($params);

$rooms = array_map(fn ($r) => [
    'id'                 => (int) $r['id'],
    'room_number'        => $r['room_number'],
    'hostel'             => ['id' => (int) $r['hostel_id'], 'name' => $r['hostel_name'], 'gender' => $r['gender']],
    'capacity'           => (int) $r['capacity'],
    'free_beds'          => max(0, (int) $r['free_beds']),
    'price_per_semester' => (float) $r['price_per_semester'],
    'photo_url'          => $r['photo_path'],
], $stmt->fetchAll());

echo json_encode(['semester' => $semester, 'count' => count($rooms), 'data' => $rooms], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
