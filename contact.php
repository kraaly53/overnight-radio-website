<?php
/*
 * Overnight Radio — contact form.
 * contact.html sends the form here; this emails it to info@overnightradio.com.
 * It runs on the real server only (GitHub Pages can't run PHP), so the preview's form sends to
 * https://overnightradio.com/contact.php once this file has been published.
 */

// ---- Settings ---------------------------------------------------------------------------------
const SEND_TO   = 'info@overnightradio.com';
const SEND_FROM = 'website@overnightradio.com';      // must be an @overnightradio.com address
const SUBJECT   = 'Website Inquiry';
const ALLOWED_ORIGINS = [                             // sites allowed to send to this form
    'https://overnightradio.com',
    'https://www.overnightradio.com',
    'https://kraaly53.github.io',                     // the GitHub Pages preview
];
const MIN_SECONDS  = 3;   // real people take longer than this to fill the form in
const MAX_PER_HOUR = 5;   // messages allowed from one visitor per hour
// ------------------------------------------------------------------------------------------------

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, ALLOWED_ORIGINS, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
}
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// The page's script asks for JSON; without JavaScript the browser posts normally and is sent back.
$wantsJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

function finish(bool $ok, string $message, int $code = 200): void {
    global $wantsJson;
    if ($wantsJson) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message]);
    } else {
        header('Location: contact.html?sent=' . ($ok ? '1' : '0'), true, 303);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') finish(false, 'Please use the form on the Contact page.', 405);
if ($origin !== '' && !in_array($origin, ALLOWED_ORIGINS, true)) finish(false, 'Not allowed.', 403);

$clean = fn($v, $max) => mb_substr(trim(str_replace("\0", '', (string)$v)), 0, $max);
$name    = $clean($_POST['name'] ?? '', 200);
$email   = $clean($_POST['email'] ?? '', 200);
$message = $clean($_POST['message'] ?? '', 5000);

// Spam traps: a hidden field people never fill in, and a minimum time on the page (the page's
// script records when it opened). Pretend success so spam bots don't learn anything.
$started = (int)($_POST['started'] ?? 0);
if (($_POST['website'] ?? '') !== '' || ($started > 0 && time() - $started < MIN_SECONDS)) {
    finish(true, 'Message sent.');
}

if ($name === '' || preg_match('/[\r\n]/', $name)) finish(false, 'Please enter your name.', 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL))     finish(false, 'Please enter a valid email address.', 422);

// Simple per-visitor limit.
$ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$file = sys_get_temp_dir() . '/onr-contact-' . md5($ip);
$recent = array_filter(
    @json_decode((string)@file_get_contents($file), true) ?: [],
    fn($t) => $t > time() - 3600
);
if (count($recent) >= MAX_PER_HOUR) finish(false, 'Too many messages. Please email us instead.', 429);
$recent[] = time();
@file_put_contents($file, json_encode(array_values($recent)));

$body = "Name: $name\nEmail: $email\n\nMessage:\n" . ($message === '' ? '(none)' : $message) . "\n\n"
      . "--\nSent from the contact form on overnightradio.com\n";
$encodedName = '=?UTF-8?B?' . base64_encode($name) . '?=';
$headers = implode("\r\n", [
    'From: Overnight Radio Website <' . SEND_FROM . '>',
    "Reply-To: $encodedName <$email>",
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
]);

$sent = mail(SEND_TO, SUBJECT, $body, $headers, '-f' . SEND_FROM);
$sent ? finish(true, 'Message sent.')
      : finish(false, 'Sorry, your message could not be sent. Please email info@overnightradio.com.', 500);
