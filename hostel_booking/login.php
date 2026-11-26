<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect(is_warden() ? 'warden_bookings.php' : 'rooms.php');
}

const MAX_ATTEMPTS    = 5;
const LOCKOUT_SECONDS = 60;

$errors = [];
$email  = '';

if (($_GET['reason'] ?? '') === 'timeout') {
    flash('info', 'Your session expired. Please log in again.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attempts = $_SESSION['login_attempts'] ?? 0;
    $lockedAt = $_SESSION['locked_at'] ?? 0;

    if ($attempts >= MAX_ATTEMPTS && time() - $lockedAt < LOCKOUT_SECONDS) {
        $errors[] = 'Too many failed attempts. Try again in ' . (LOCKOUT_SECONDS - (time() - $lockedAt)) . ' seconds.';
    } else {
        if ($attempts >= MAX_ATTEMPTS) {
            $attempts = 0; // the lockout is over
        }
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
            $errors[] = 'Your form expired. Please try again.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($password === '' || strlen($password) > 255) {
            $errors[] = 'Enter your password.';
        }

        if (!$errors) {
            $stmt = db()->prepare('SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true); // new session ID stops session fixation
                $_SESSION['user_id']       = (int) $user['id'];
                $_SESSION['name']          = $user['name'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['fingerprint']   = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
                $_SESSION['last_activity'] = time();
                unset($_SESSION['login_attempts'], $_SESSION['locked_at'], $_SESSION['csrf_token']);

                flash('success', 'Welcome back, ' . $user['name'] . '.');
                redirect($user['role'] === 'warden' ? 'warden_bookings.php' : 'rooms.php');
            }

            // One message for both cases, so nobody can tell which emails are registered.
            $errors[] = 'Wrong email or password.';
            $_SESSION['login_attempts'] = ++$attempts;
            if ($attempts >= MAX_ATTEMPTS) {
                $_SESSION['locked_at'] = time();
            }
        }
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="card narrow">
    <h1>Log in</h1>
    <?php if ($errors): ?>
        <ul class="errors"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul>
    <?php endif; ?>
    <form method="post" action="login.php" novalidate>
        <?= csrf_field() ?>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <div class="form-actions"><button type="submit">Log in</button></div>
    </form>
    <p class="muted">No account? <a href="register.php">Register as a student</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
