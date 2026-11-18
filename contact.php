<?php
// Receives the website's contact forms, saves each enquiry as a numbered lead, then emails it.
// Shared code and settings are in the private itsn-leads folder beside public_html.
declare(strict_types=1);
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');

$wantsJson = stripos((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false
          || stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false;

function reply(int $code, bool $ok, string $message, array $extra = []): void {
    global $wantsJson;
    http_response_code($code);
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => $ok, 'message' => $message] + $extra);
    } else {   // visitor without JavaScript: a plain page
        header('Content-Type: text/html; charset=utf-8');
        $t = $ok ? 'Thank you' : 'Sorry, your message didn’t send';
        echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . $t . '</title>'
           . '<body style="font-family:system-ui,sans-serif;max-width:560px;margin:12vh auto;padding:0 20px;line-height:1.5;color:#0a1526">'
           . '<h1>' . $t . '</h1><p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p><p><a href="/">Back to IT Security Now</a></p>';
    }
    exit;
}

$leadsDir = getenv('ITSN_LEADS_DIR') ?: dirname(__DIR__) . '/itsn-leads';
if (!is_file($leadsDir . '/lib.php')) {
    error_log('ITSN contact.php: itsn-leads folder not found at ' . $leadsDir);
    reply(500, false, 'Please try again or email info@itsecuritynow.com.');
}
require $leadsDir . '/lib.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { reply(405, false, 'Please use the form on the website.'); }

// Only accept submissions sent from this website's own pages.
$allowed = array_map('strtolower', (array)itsn_cfg('allowed_hosts'));
$origin = (string)($_SERVER['HTTP_ORIGIN'] ?? '');
$refHost = strtolower((string)parse_url((string)($_SERVER['HTTP_REFERER'] ?? ''), PHP_URL_HOST));
$originHost = strtolower((string)parse_url($origin, PHP_URL_HOST));
if (!($originHost !== '' ? in_array($originHost, $allowed, true) : ($refHost !== '' && in_array($refHost, $allowed, true)))) {
    reply(403, false, 'Submissions are only accepted from the IT Security Now website.');
}

// Read the fields (JSON from the website's script, or a normal form post without JavaScript).
$raw = (string)file_get_contents('php://input', false, null, 0, 65536);
$in = stripos((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json') !== false ? (json_decode($raw, true) ?: []) : $_POST;
if (!is_array($in)) { $in = []; }
$get = fn(string $k): string => is_scalar($in[$k] ?? null) ? (string)$in[$k] : '';

// Bots fill in the hidden field. Pretend it worked and keep nothing.
if ($get('_honey') !== '') { reply(200, true, 'Thanks — an ITSN expert will be in touch shortly.', ['id' => '']); }

// At most 5 submissions per visitor every 10 minutes.
$window = (int)(itsn_cfg('rate_limit_window') ?: 600);
if (itsn_hits('form', $window, false) >= (int)(itsn_cfg('rate_limit_count') ?: 5)) {
    reply(429, false, 'Too many messages in a short time. Please wait a few minutes or email info@itsecuritynow.com.');
}
itsn_hits('form', $window, true);

$lead = [
    'form'         => itsn_clean($get('_form') ?: 'Contact form', 80),
    'name'         => itsn_clean($get('name'), 120),
    'email'        => itsn_clean($get('email'), 200),
    'organization' => itsn_clean($get('organization'), 160),
    'industry'     => itsn_clean($get('industry'), 80),
    'phone'        => itsn_clean($get('phone'), 40),
    'message'      => itsn_clean($get('message'), 5000, true),
    'source'       => itsn_clean($get('_source'), 160),
    'landing_page' => itsn_clean($get('landing_page'), 300),
    'referrer'     => itsn_clean($get('came_from'), 200),
    'ip'           => itsn_client_ip(),
    'user_agent'   => itsn_clean((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 300),
    'status'       => 'New',
];

if ($lead['name'] === '' || $lead['message'] === '') { reply(422, false, 'Please fill in your name and your message.'); }
if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) { reply(422, false, 'Please enter a valid email address.'); }

// Link-stuffed messages are saved as Spam for review, and not emailed.
$links = preg_match_all('~(https?://|www\.|\[url)~i', $lead['message'] . ' ' . $lead['name']);
if ($links >= 3 || preg_match('~https?://~i', $lead['name'])) { $lead['status'] = 'Spam'; }

// 1. Save first, so the enquiry is never lost even if email fails.
try {
    $lead = itsn_add_lead($lead);
} catch (Throwable $e) {
    error_log('ITSN contact.php: could not save lead: ' . $e->getMessage());
    reply(500, false, 'Please try again or email info@itsecuritynow.com.');
}

// 2. Then email it.
$status = $lead['status'] === 'Spam' ? 'skipped (spam)' : itsn_send_lead_email($lead);
try { itsn_update_lead($lead['id'], ['email_status' => $status]); } catch (Throwable $e) { error_log('ITSN contact.php: ' . $e->getMessage()); }
if (strpos($status, 'failed') === 0) { error_log('ITSN contact.php: email for ' . $lead['id'] . ' ' . $status); }

reply(200, true, 'Thanks — an ITSN expert will be in touch shortly.', ['id' => $lead['status'] === 'Spam' ? '' : $lead['id']]);
