<?php
/** Warden: add a room (optionally ?hostel_id=) or edit one (?id=), with a photo upload. */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');

$hostels = db()->query('SELECT id, name FROM hostels ORDER BY name')->fetchAll();
if (!$hostels) {
    flash('info', 'Add a hostel before adding rooms.');
    redirect('warden_hostel_form.php');
}

$id   = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$room = [
    'hostel_id'          => filter_input(INPUT_GET, 'hostel_id', FILTER_VALIDATE_INT) ?: '',
    'room_number'        => '',
    'capacity'           => '',
    'price_per_semester' => '',
    'photo_path'         => null,
];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM rooms WHERE id = ?');
    $stmt->execute([$id]);
    $room = $stmt->fetch();
    if (!$room) {
        http_response_code(404);
        exit('404 Not Found: no such room.');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf();

    $room['hostel_id']          = filter_input(INPUT_POST, 'hostel_id', FILTER_VALIDATE_INT) ?: '';
    $room['room_number']        = strtoupper(trim($_POST['room_number'] ?? ''));
    $room['capacity']           = trim($_POST['capacity'] ?? '');
    $room['price_per_semester'] = trim($_POST['price_per_semester'] ?? '');

    if (!in_array((int) $room['hostel_id'], array_map('intval', array_column($hostels, 'id')), true)) {
        $errors['hostel_id'] = 'Choose a hostel.';
    }
    if (!preg_match('/^[A-Z0-9-]{1,20}$/', $room['room_number'])) {
        $errors['room_number'] = 'Room number: 1 to 20 letters, numbers or dashes.';
    } elseif (!isset($errors['hostel_id'])) {
        // Room numbers must be unique within a hostel.
        $stmt = db()->prepare('SELECT 1 FROM rooms WHERE hostel_id = ? AND room_number = ? AND id <> ?');
        $stmt->execute([$room['hostel_id'], $room['room_number'], $id ?? 0]);
        if ($stmt->fetch()) {
            $errors['room_number'] = 'That hostel already has a room with this number.';
        }
    }
    if (filter_var($room['capacity'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 12]]) === false) {
        $errors['capacity'] = 'Beds must be a whole number from 1 to 12.';
    } elseif ($id) {
        // Do not shrink a room below the number of students already approved in it.
        $stmt = db()->prepare("SELECT MAX(c) FROM (SELECT COUNT(*) c FROM bookings WHERE room_id = ? AND status = 'approved' GROUP BY semester) t");
        $stmt->execute([$id]);
        $taken = (int) $stmt->fetchColumn();
        if ((int) $room['capacity'] < $taken) {
            $errors['capacity'] = "This room already has {$taken} approved students in one semester.";
        }
    }
    if (!is_numeric($room['price_per_semester']) || $room['price_per_semester'] < 0 || $room['price_per_semester'] > 1000000) {
        $errors['price_per_semester'] = 'Enter a price between 0 and 1,000,000.';
    }

    if (!$errors) {
        [$newPhoto, $photoError] = save_room_photo($_FILES['photo'] ?? []);
        if ($photoError) {
            $errors['photo'] = $photoError;
        }
    }

    if (!$errors) {
        $photo = $room['photo_path'];
        if ($newPhoto) {
            delete_room_photo($photo); // replace the old photo
            $photo = $newPhoto;
        }
        $values = [$room['hostel_id'], $room['room_number'], $room['capacity'], $room['price_per_semester'], $photo];
        if ($id) {
            db()->prepare('UPDATE rooms SET hostel_id = ?, room_number = ?, capacity = ?, price_per_semester = ?, photo_path = ? WHERE id = ?')
                ->execute([...$values, $id]);
            flash('success', 'Room updated.');
        } else {
            db()->prepare('INSERT INTO rooms (hostel_id, room_number, capacity, price_per_semester, photo_path) VALUES (?, ?, ?, ?, ?)')
                ->execute($values);
            flash('success', 'Room added.');
        }
        redirect('warden_rooms.php?hostel_id=' . (int) $room['hostel_id']);
    }
}

$pageTitle = $id ? 'Edit room' : 'Add room';
require __DIR__ . '/includes/header.php';
$err = fn (string $f) => isset($errors[$f]) ? '<p class="field-error">' . e($errors[$f]) . '</p>' : '';
?>
<div class="card form">
    <h1><?= e($pageTitle) ?></h1>
    <form method="post" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>
        <label for="hostel_id">Hostel</label>
        <select id="hostel_id" name="hostel_id">
            <option value="">Choose a hostel</option>
            <?php foreach ($hostels as $h): ?>
                <option value="<?= (int) $h['id'] ?>" <?= (int) $room['hostel_id'] === (int) $h['id'] ? 'selected' : '' ?>><?= e($h['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $err('hostel_id') ?>

        <label for="room_number">Room number</label>
        <input id="room_number" name="room_number" value="<?= e($room['room_number']) ?>" required maxlength="20">
        <?= $err('room_number') ?>

        <label for="capacity">Beds (capacity)</label>
        <input type="number" id="capacity" name="capacity" value="<?= e($room['capacity']) ?>" min="1" max="12" required>
        <?= $err('capacity') ?>

        <label for="price_per_semester">Price per semester (KSh)</label>
        <input type="number" id="price_per_semester" name="price_per_semester" value="<?= e($room['price_per_semester']) ?>" min="0" step="50" required>
        <?= $err('price_per_semester') ?>

        <label for="photo">Photo (JPG, PNG or WEBP, up to 2 MB)</label>
        <?php if ($room['photo_path']): ?>
            <img src="<?= e($room['photo_path']) ?>" alt="Current photo" style="max-width:220px;border-radius:8px;display:block;margin-bottom:8px">
        <?php endif; ?>
        <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp">
        <?= $err('photo') ?>

        <div class="form-actions">
            <button type="submit"><?= $id ? 'Save changes' : 'Add room' ?></button>
            <a class="button light" href="warden_rooms.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
