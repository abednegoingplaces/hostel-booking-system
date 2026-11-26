<?php
/**
 * Loaded at the top of every page: secure session, database and helper functions.
 */
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');   // reject session IDs the server did not create
    ini_set('session.use_only_cookies', '1');  // never take the session ID from the URL
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,      // JavaScript cannot read the cookie
        'samesite' => 'Lax',
    ]);
    session_name('HOSTELSID');   // own cookie name, so it never mixes with other apps on localhost
    session_start();
}

const SESSION_IDLE_LIMIT = 1800; // log out after 30 minutes of inactivity

// ---- Output and forms -------------------------------------------------------

/** Escape output for HTML (stops cross-site scripting). */
function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Stop any request that is not a POST carrying the session's CSRF token. */
function require_post_with_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('405 Method Not Allowed');
    }
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        http_response_code(403); // Apache has no 419 status, so use 403
        exit('403 Page Expired: the form token is missing or invalid. Go back, refresh and try again.');
    }
}

function csrf_is_valid($token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function money($amount): string
{
    return 'KSh ' . number_format((float) $amount);
}

/** The semester picked in the URL (?semester=...), or the default one. */
function selected_semester(): string
{
    $semester = $_GET['semester'] ?? DEFAULT_SEMESTER;
    return in_array($semester, SEMESTERS, true) ? $semester : DEFAULT_SEMESTER;
}

// ---- Login and roles --------------------------------------------------------

function current_user(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return ['id' => $_SESSION['user_id'], 'name' => $_SESSION['name'], 'role' => $_SESSION['role']];
}

function is_warden(): bool
{
    return ($_SESSION['role'] ?? '') === 'warden';
}

function is_student(): bool
{
    return ($_SESSION['role'] ?? '') === 'student';
}

/** Send visitors to the login page; also catches hijacked or idle sessions. */
function require_login(): void
{
    if (!isset($_SESSION['user_id'])) {
        flash('info', 'Please log in first.');
        redirect('login.php');
    }
    $fingerprint = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? '');
    if (!hash_equals($_SESSION['fingerprint'] ?? '', $fingerprint)) {
        redirect('logout.php');
    }
    if (time() - ($_SESSION['last_activity'] ?? 0) > SESSION_IDLE_LIMIT) {
        redirect('logout.php?reason=timeout');
    }
    $_SESSION['last_activity'] = time();
}

/** Role-based access: stop the page unless the logged-in user has this role. */
function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('403 Forbidden: you do not have permission to view this page.');
    }
}

// ---- Free beds --------------------------------------------------------------

/**
 * Rooms with their free beds for one semester.
 * Free beds = capacity minus approved bookings for that semester, worked out on every request,
 * so the number can never go out of date.
 */
function rooms_with_free_beds(string $semester, ?int $hostelId = null): array
{
    $sql = "SELECT r.*, h.name AS hostel_name, h.gender, h.location,
                   r.capacity - (SELECT COUNT(*) FROM bookings b
                                 WHERE b.room_id = r.id AND b.semester = ? AND b.status = 'approved') AS free_beds
            FROM rooms r
            JOIN hostels h ON h.id = r.hostel_id";
    $params = [$semester];
    if ($hostelId) {
        $sql .= ' WHERE r.hostel_id = ?';
        $params[] = $hostelId;
    }
    $sql .= ' ORDER BY h.name, r.room_number';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** Save an uploaded room photo. Returns [path, error]; path is null when no file was sent. */
function save_room_photo(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The photo could not be uploaded. Try a smaller file.'];
    }
    if ($file['size'] > UPLOAD_MAX_BYTES) {
        return [null, 'The photo must be 2 MB or smaller.'];
    }
    // Check the real file contents, not the browser-supplied type.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(UPLOAD_TYPES[$mime]) || @getimagesize($file['tmp_name']) === false) {
        return [null, 'The photo must be a JPG, PNG or WEBP image.'];
    }
    $name = bin2hex(random_bytes(16)) . '.' . UPLOAD_TYPES[$mime];   // random name, safe extension
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        return [null, 'The photo could not be saved on the server.'];
    }
    return [UPLOAD_URL . $name, null];
}

function delete_room_photo(?string $path): void
{
    if ($path && str_starts_with($path, UPLOAD_URL)) {
        $full = UPLOAD_DIR . basename($path);
        if (is_file($full)) {
            unlink($full);
        }
    }
}
