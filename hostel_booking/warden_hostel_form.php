<?php
/** Warden: add a hostel (no id) or edit one (?id=). */
require_once __DIR__ . '/includes/bootstrap.php';
require_role('warden');

const GENDERS = ['male', 'female', 'mixed'];

$id     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$hostel = ['name' => '', 'location' => '', 'gender' => 'mixed', 'description' => ''];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM hostels WHERE id = ?');
    $stmt->execute([$id]);
    $hostel = $stmt->fetch();
    if (!$hostel) {
        http_response_code(404);
        exit('404 Not Found: no such hostel.');
    }
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post_with_csrf();

    $hostel['name']        = trim($_POST['name'] ?? '');
    $hostel['location']    = trim($_POST['location'] ?? '');
    $hostel['gender']      = $_POST['gender'] ?? '';
    $hostel['description'] = trim($_POST['description'] ?? '');

    if ($hostel['name'] === '' || mb_strlen($hostel['name']) > 100) {
        $errors['name'] = 'Enter a hostel name (up to 100 characters).';
    } else {
        $stmt = db()->prepare('SELECT 1 FROM hostels WHERE name = ? AND id <> ?');
        $stmt->execute([$hostel['name'], $id ?? 0]);
        if ($stmt->fetch()) {
            $errors['name'] = 'Another hostel already has that name.';
        }
    }
    if ($hostel['location'] === '' || mb_strlen($hostel['location']) > 150) {
        $errors['location'] = 'Enter a location (up to 150 characters).';
    }
    if (!in_array($hostel['gender'], GENDERS, true)) {
        $errors['gender'] = 'Choose male, female or mixed.';
    }
    if (mb_strlen($hostel['description']) > 1000) {
        $errors['description'] = 'Keep the description under 1000 characters.';
    }

    if (!$errors) {
        $values = [$hostel['name'], $hostel['location'], $hostel['gender'], $hostel['description'] ?: null];
        if ($id) {
            db()->prepare('UPDATE hostels SET name = ?, location = ?, gender = ?, description = ? WHERE id = ?')
                ->execute([...$values, $id]);
            flash('success', 'Hostel updated.');
        } else {
            db()->prepare('INSERT INTO hostels (name, location, gender, description) VALUES (?, ?, ?, ?)')
                ->execute($values);
            flash('success', 'Hostel added.');
        }
        redirect('warden_hostels.php');
    }
}

$pageTitle = $id ? 'Edit hostel' : 'Add hostel';
require __DIR__ . '/includes/header.php';
$err = fn (string $f) => isset($errors[$f]) ? '<p class="field-error">' . e($errors[$f]) . '</p>' : '';
?>
<div class="card form">
    <h1><?= e($pageTitle) ?></h1>
    <form method="post" novalidate>
        <?= csrf_field() ?>
        <label for="name">Name</label>
        <input id="name" name="name" value="<?= e($hostel['name']) ?>" required maxlength="100">
        <?= $err('name') ?>

        <label for="location">Location</label>
        <input id="location" name="location" value="<?= e($hostel['location']) ?>" required maxlength="150">
        <?= $err('location') ?>

        <label for="gender">Gender</label>
        <select id="gender" name="gender">
            <?php foreach (GENDERS as $g): ?>
                <option value="<?= $g ?>" <?= $hostel['gender'] === $g ? 'selected' : '' ?>><?= ucfirst($g) ?></option>
            <?php endforeach; ?>
        </select>
        <?= $err('gender') ?>

        <label for="description">Description</label>
        <textarea id="description" name="description" maxlength="1000"><?= e($hostel['description']) ?></textarea>
        <?= $err('description') ?>

        <div class="form-actions">
            <button type="submit"><?= $id ? 'Save changes' : 'Add hostel' ?></button>
            <a class="button light" href="warden_hostels.php">Cancel</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
