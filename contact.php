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

// Never print PHP warnings into the reply (they break the page's message and reveal server paths).
ini_set('display_errors', '0');

// Mail-server login (host, port, username, password, from). Written to contact-config.php on the server by the
// "Publish to live site" workflow from the repository's SMTP_* secrets — never stored in the repository.
$smtp = is_file(__DIR__ . '/contact-config.php') ? (include __DIR__ . '/contact-config.php') : null;

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
$from = (is_array($smtp) && !empty($smtp['from'])) ? $smtp['from'] : SEND_FROM;
$headers = [
    'From: Overnight Radio Website <' . $from . '>',
    "Reply-To: $encodedName <$email>",
    'Content-Type: text/plain; charset=UTF-8',
    'MIME-Version: 1.0',
];

$sent = is_array($smtp) && !empty($smtp['host'])
    ? smtp_send($smtp, $from, SEND_TO, SUBJECT, $body, $headers)
    : @mail(SEND_TO, SUBJECT, $body, implode("\r\n", $headers), '-f' . $from);
$sent ? finish(true, 'Message sent.')
      : finish(false, 'Sorry, your message could not be sent. Please email info@overnightradio.com.', 500);

/* Minimal SMTP client: SSL on port 465, STARTTLS on other ports, AUTH LOGIN. Returns true when the mail server
   accepts the message; problems are written to the server's PHP error log, never shown to the visitor. */
function smtp_send(array $c, string $from, string $to, string $subject, string $body, array $headers): bool {
    $port = (int)($c['port'] ?? 587);
    $host = ($port === 465 ? 'ssl://' : 'tcp://') . $c['host'];
    $fp = @stream_socket_client("$host:$port", $errno, $errstr, 15);
    if (!$fp) { error_log("contact.php: cannot connect to {$c['host']}:$port ($errstr)"); return false; }
    stream_set_timeout($fp, 15);
    $read = function () use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) { $data .= $line; if (isset($line[3]) && $line[3] === ' ') break; }
        return $data;
    };
    $cmd = function (string $line, array $ok) use ($fp, $read) {
        if ($line !== '') fwrite($fp, $line . "\r\n");
        $reply = $read();
        if (!in_array((int)substr($reply, 0, 3), $ok, true)) {
            error_log('contact.php: SMTP said ' . trim($reply));
            return false;
        }
        return true;
    };
    $me = $_SERVER['SERVER_NAME'] ?? 'overnightradio.com';
    $ok = $cmd('', [220]) && $cmd("EHLO $me", [250]);
    if ($ok && $port !== 465) {
        $ok = $cmd('STARTTLS', [220])
           && stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)
           && $cmd("EHLO $me", [250]);
    }
    $ok = $ok && $cmd('AUTH LOGIN', [334]) && $cmd(base64_encode($c['username']), [334])
              && $cmd(base64_encode($c['password']), [235])
              && $cmd("MAIL FROM:<$from>", [250]) && $cmd("RCPT TO:<$to>", [250, 251]) && $cmd('DATA', [354]);
    if ($ok) {
        $msg = implode("\r\n", array_merge([
            'To: <' . $to . '>',
            'Subject: ' . $subject,
            'Date: ' . date('r'),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@overnightradio.com>',
        ], $headers)) . "\r\n\r\n" . str_replace("\n", "\r\n", str_replace("\r\n", "\n", $body));
        $msg = preg_replace('/^\./m', '..', $msg);   // a line starting with "." must be doubled
        $ok = $cmd($msg . "\r\n.", [250]);
    }
    @fwrite($fp, "QUIT\r\n");
    fclose($fp);
    return $ok;
}
