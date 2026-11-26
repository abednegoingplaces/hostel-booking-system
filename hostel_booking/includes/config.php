<?php
// Database settings: XAMPP defaults (user "root", empty password). Change them if yours differ.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'hostel_booking');
define('DB_USER', 'root');
define('DB_PASS', '');

// Semesters students can book. A fixed list keeps the free-bed count exact.
const SEMESTERS = ['Sep-Dec 2026', 'Jan-Apr 2027', 'May-Aug 2027'];
const DEFAULT_SEMESTER = 'Jan-Apr 2027';

// Room photo uploads.
const UPLOAD_DIR       = __DIR__ . '/../uploads/rooms/';
const UPLOAD_URL       = 'uploads/rooms/';
const UPLOAD_MAX_BYTES = 2 * 1024 * 1024; // 2 MB
const UPLOAD_TYPES     = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
