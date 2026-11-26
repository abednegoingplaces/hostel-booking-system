<?php
require_once __DIR__ . '/includes/bootstrap.php';

$timedOut = ($_GET['reason'] ?? '') === 'timeout';

// Clear the session data, delete the cookie, then destroy the session on the server.
$_SESSION = [];
$params = session_get_cookie_params();
setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
session_destroy();

redirect($timedOut ? 'login.php?reason=timeout' : 'index.php');
