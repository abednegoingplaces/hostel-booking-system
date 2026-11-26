<?php
/** Shared page header. Set $pageTitle before including. */
$user = current_user();
$current = basename($_SERVER['SCRIPT_NAME']);
$nav = function (string $file, string $label) use ($current): string {
    $active = $file === $current ? ' class="active"' : '';
    return '<a href="' . e($file) . '"' . $active . '>' . e($label) . '</a>';
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Hostel Booking') ?> | Hostel Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <div class="wrap topbar-inner">
        <a class="brand" href="index.php">Hostel Booking</a>
        <nav>
            <?= $nav('hostels.php', 'Hostels') ?>
            <?= $nav('rooms.php', 'Rooms') ?>
            <?php if ($user && $user['role'] === 'student'): ?>
                <?= $nav('my_bookings.php', 'My bookings') ?>
            <?php elseif ($user && $user['role'] === 'warden'): ?>
                <?= $nav('warden_bookings.php', 'Bookings') ?>
                <?= $nav('warden_hostels.php', 'Manage hostels') ?>
                <?= $nav('warden_rooms.php', 'Manage rooms') ?>
            <?php endif; ?>
        </nav>
        <div class="account">
            <?php if ($user): ?>
                <span class="muted"><?= e($user['name']) ?> · <?= e(ucfirst($user['role'])) ?></span>
                <form method="post" action="logout.php" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="link">Log out</button>
                </form>
            <?php else: ?>
                <a href="login.php">Log in</a>
                <a href="register.php" class="button small">Register</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="wrap">
<?php foreach (take_flashes() as $flash): ?>
    <p class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></p>
<?php endforeach; ?>
