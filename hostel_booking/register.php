<?php
/** Student registration. Wardens are never created here: the role is always "student". */
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('rooms.php');
}

$errors = [];
$old    = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['name']  = trim($_POST['name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm      = $_POST['password_confirmation'] ?? '';

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form expired. Please try again.';
    }
    if ($old['name'] === '' || mb_strlen($old['name']) > 100) {
        $errors['name'] = 'Enter your full name (up to 100 characters).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL) || strlen($old['email']) > 150) {
        $errors['email'] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Use at least 8 characters with letters and numbers.';
    } elseif ($password !== $confirm) {
        $errors['password'] = 'The two passwords do not match.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'That email is already registered.';
        }
    }

    if (!$errors) {
        db()->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'student')")
            ->execute([$old['name'], $old['email'], password_hash($password, PASSWORD_DEFAULT)]);
        flash('success', 'Account created. You can log in now.');
        redirect('login.php');
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow">
    <h1>Create a student account</h1>
    <?php if (isset($errors['form'])): ?><p class="flash error"><?= e($errors['form']) ?></p><?php endif; ?>
    <form method="post" action="register.php" novalidate>
        <?= csrf_field() ?>
        <label for="name">Full name</label>
        <input id="name" name="name" value="<?= e($old['name']) ?>" required maxlength="100">
        <?php if (isset($errors['name'])): ?><p class="field-error"><?= e($errors['name']) ?></p><?php endif; ?>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($old['email']) ?>" required maxlength="150">
        <?php if (isset($errors['email'])): ?><p class="field-error"><?= e($errors['email']) ?></p><?php endif; ?>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <label for="password_confirmation">Confirm password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" required>
        <?php if (isset($errors['password'])): ?><p class="field-error"><?= e($errors['password']) ?></p><?php endif; ?>

        <div class="form-actions"><button type="submit">Register</button></div>
    </form>
    <p class="muted">Already registered? <a href="login.php">Log in</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
