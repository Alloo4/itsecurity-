<?php
// ITSN website leads: shared code used by public_html/contact.php and public_html/leads.php.
// Lives in the private itsn-leads folder beside public_html, so it can't be opened from the web.
declare(strict_types=1);

const ITSN_COLUMNS = ['id','created','form','name','email','organization','industry','phone','message',
    'source','landing_page','referrer','ip','user_agent','status','notes','email_status','updated'];
const ITSN_STATUSES = ['New','Contacted','Qualified','Proposal sent','Won','Lost','Spam','Test'];
const ITSN_HIDDEN_STATUSES = ['Spam','Test'];

function itsn_dir(): string { return __DIR__; }

function itsn_cfg(?string $key = null) {
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require itsn_dir() . '/config.php';
        if (!is_array($cfg)) { throw new RuntimeException('config.php must return an array'); }
    }
    return $key === null ? $cfg : ($cfg[$key] ?? null);
}

function itsn_data_dir(): string {
    $d = itsn_dir() . '/data';
    if (!is_dir($d)) {
        if (!@mkdir($d, 0750, true) && !is_dir($d)) { throw new RuntimeException('Cannot create ' . $d); }
    }
    // Belt and braces: even if this folder were ever moved inside public_html, refuse web access.
    if (!is_file($d . '/.htaccess')) { @file_put_contents($d . '/.htaccess', "Require all denied\n"); }
    return $d;
}

function itsn_csv_path(): string { return itsn_data_dir() . '/leads.csv'; }

// ---------- text helpers ----------
function itsn_clean(string $s, int $max, bool $multiline = false): string {
    $s = str_replace("\0", '', $s);
    $s = $multiline ? preg_replace("/\r\n?/", "\n", $s) : preg_replace('/[\r\n\t]+/', ' ', $s);
    $s = trim((string)$s);
    if (strlen($s) > $max) {                       // cut on a character boundary, not mid-letter
        $s = preg_match('/^.{0,' . $max . '}/us', $s, $m) ? $m[0] : substr($s, 0, $max);
    }
    return $s;
}
function h($s): string { return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function itsn_client_ip(): string { return (string)($_SERVER['REMOTE_ADDR'] ?? ''); }

// ---------- locked access to the records file ----------
function itsn_with_lock(callable $fn) {
    $lock = fopen(itsn_data_dir() . '/leads.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX)) { throw new RuntimeException('Cannot lock the records file'); }
    try { return $fn(); } finally { flock($lock, LOCK_UN); fclose($lock); }
}

function itsn_read_all(): array {
    $p = itsn_csv_path();
    if (!is_file($p)) { return []; }
    $rows = []; $fh = fopen($p, 'r');
    $head = fgetcsv($fh, 0, ',', '"', '');
    while (($r = fgetcsv($fh, 0, ',', '"', '')) !== false) {
        if ($r === [null]) { continue; }
        $row = [];
        foreach (ITSN_COLUMNS as $i => $c) {
            $j = $head ? array_search($c, $head, true) : $i;
            $row[$c] = ($j !== false && isset($r[$j])) ? $r[$j] : '';
        }
        $rows[] = $row;
    }
    fclose($fh);
    return $rows;
}

function itsn_write_all(array $rows): void {
    $p = itsn_csv_path();
    if (is_file($p)) { @copy($p, $p . '.bak'); }   // previous version kept as leads.csv.bak
    $tmp = $p . '.tmp';
    $fh = fopen($tmp, 'w');
    fputcsv($fh, ITSN_COLUMNS, ',', '"', '');
    foreach ($rows as $r) { fputcsv($fh, array_map(fn($c) => (string)($r[$c] ?? ''), ITSN_COLUMNS), ',', '"', ''); }
    fclose($fh);
    if (!rename($tmp, $p)) { throw new RuntimeException('Cannot save the records file'); }
}

/** Saves a new lead and returns it with its new ID (ITSN-0001, ITSN-0002 ...). */
function itsn_add_lead(array $lead): array {
    return itsn_with_lock(function () use ($lead) {
        $rows = itsn_read_all();
        $max = 0;
        foreach ($rows as $r) { if (preg_match('/^ITSN-(\d+)$/', $r['id'], $m)) { $max = max($max, (int)$m[1]); } }
        $lead['id'] = sprintf('ITSN-%04d', $max + 1);
        $lead['created'] = $lead['updated'] = date('c');
        $lead += array_fill_keys(ITSN_COLUMNS, '');
        $rows[] = $lead;
        itsn_write_all($rows);
        return $lead;
    });
}

function itsn_update_lead(string $id, array $changes): ?array {
    return itsn_with_lock(function () use ($id, $changes) {
        $rows = itsn_read_all(); $found = null;
        foreach ($rows as &$r) {
            if ($r['id'] === $id) {
                foreach ($changes as $k => $v) { if (in_array($k, ITSN_COLUMNS, true) && $k !== 'id') { $r[$k] = (string)$v; } }
                $r['updated'] = date('c'); $found = $r;
            }
        }
        unset($r);
        if ($found) { itsn_write_all($rows); }
        return $found;
    });
}

// ---------- simple per-visitor counters (rate limit, login attempts) ----------
function itsn_hits(string $bucket, int $window, bool $add): int {
    $dir = itsn_data_dir() . '/limits'; if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
    $f = $dir . '/' . $bucket . '-' . hash('sha256', itsn_client_ip() . '|itsn') . '.json';
    $now = time();
    $list = is_file($f) ? (json_decode((string)file_get_contents($f), true) ?: []) : [];
    $list = array_values(array_filter($list, fn($t) => is_int($t) && $t > $now - $window));
    if ($add) { $list[] = $now; }
    file_put_contents($f, json_encode($list), LOCK_EX);
    return count($list);
}
function itsn_hits_clear(string $bucket): void {
    $f = itsn_data_dir() . '/limits/' . $bucket . '-' . hash('sha256', itsn_client_ip() . '|itsn') . '.json';
    if (is_file($f)) { @unlink($f); }
}

// ---------- email ----------
function itsn_mime_header(string $s): string {
    return preg_match('/[^\x20-\x7E]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
}

/** Emails a lead to the inbox. Returns 'sent' or 'failed: reason'. */
function itsn_send_lead_email(array $lead): string {
    $to = (string)itsn_cfg('inbox_to');
    $from = (string)itsn_cfg('mail_from');
    $fromName = (string)(itsn_cfg('mail_from_name') ?: 'ITSN Website');
    $host = (itsn_cfg('allowed_hosts') ?: ['itsecuritynow.com'])[0];
    $subject = '[' . $lead['id'] . '] New website enquiry: ' . $lead['form'] . ($lead['name'] !== '' ? ' from ' . $lead['name'] : '');

    $lines = [
        'Lead ID'       => $lead['id'],
        'Form'          => $lead['form'],
        'Name'          => $lead['name'],
        'Email'         => $lead['email'],
        'Organization'  => $lead['organization'],
        'Industry'      => $lead['industry'],
        'Phone'         => $lead['phone'],
        'Source'        => $lead['source'],
        'First page'    => $lead['landing_page'],
        'Came from'     => $lead['referrer'],
        'Received'      => date('D j M Y, g:i a T', strtotime($lead['created'])),
    ];
    $body = "New enquiry from the IT Security Now website.\n\n";
    foreach ($lines as $k => $v) { if ($v !== '') { $body .= str_pad($k . ':', 15) . $v . "\n"; } }
    $body .= "\nMessage:\n" . $lead['message'] . "\n\n";
    $body .= "Reply to this email to answer " . ($lead['name'] ?: 'the visitor') . " directly.\n";
    $body .= "Track this lead: https://{$host}/leads.php?id=" . rawurlencode($lead['id']) . "\n";

    $replyName = str_replace(['"', '\\'], '', $lead['name']);
    $headers = [
        'From: ' . itsn_mime_header($fromName) . ' <' . $from . '>',
        'Reply-To: ' . ($replyName !== '' ? '"' . itsn_mime_header($replyName) . '" ' : '') . '<' . $lead['email'] . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-ITSN-Lead: ' . $lead['id'],
    ];
    $cc = trim((string)itsn_cfg('inbox_cc'));
    if ($cc !== '' && filter_var($cc, FILTER_VALIDATE_EMAIL)) { $headers[] = 'Cc: ' . $cc; }

    if (itsn_cfg('mail_mode') === 'file') {
        $dir = itsn_data_dir() . '/outbox'; if (!is_dir($dir)) { @mkdir($dir, 0750, true); }
        $ok = file_put_contents($dir . '/' . $lead['id'] . '.eml',
            "To: {$to}\r\nSubject: " . itsn_mime_header($subject) . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $body) !== false;
        return $ok ? 'sent' : 'failed: could not write test email';
    }
    if (!function_exists('mail')) { return 'failed: mail() is disabled on this server'; }
    $ok = @mail($to, itsn_mime_header($subject), $body, implode("\r\n", $headers), '-f' . $from);
    return $ok ? 'sent' : 'failed: the server refused to send';
}

// ---------- where the visitor came from ----------
function itsn_source_label(string $s): string { return $s !== '' ? $s : 'Direct / bookmark'; }

// ---------- System check ----------
function itsn_system_checks(): array {
    $c = [];
    $c[] = ['PHP 8.1 or newer', version_compare(PHP_VERSION, '8.1.0', '>='), 'Running PHP ' . PHP_VERSION];
    try { $d = itsn_data_dir(); $w = is_writable($d); } catch (Throwable $e) { $d = itsn_dir() . '/data'; $w = false; }
    $c[] = ['Records folder can be written', $w, $d];
    $root = realpath((string)($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';
    $real = realpath(itsn_dir()) ?: itsn_dir();
    $outside = $root === '' || strpos(str_replace('\\', '/', $real) . '/', rtrim(str_replace('\\', '/', $root), '/') . '/') !== 0;
    $c[] = ['itsn-leads folder is outside public_html (private)', $outside, $real];
    $c[] = ['Dashboard password is set', (string)itsn_cfg('dashboard_password_hash') !== '', 'config.php'];
    $mailOk = function_exists('mail') && !in_array('mail', array_map('trim', explode(',', (string)ini_get('disable_functions'))), true);
    $c[] = ['Server can send email', $mailOk || itsn_cfg('mail_mode') === 'file', itsn_cfg('mail_mode') === 'file' ? 'Test mode: emails written to data/outbox' : 'PHP mail()'];
    $domain = substr(strrchr((string)itsn_cfg('mail_from'), '@') ?: '@itsecuritynow.com', 1);
    $spf = [];
    foreach ((@dns_get_record($domain, DNS_TXT) ?: []) as $r) {
        $t = $r['txt'] ?? implode('', $r['entries'] ?? []);
        if (stripos($t, 'v=spf1') === 0) { $spf[] = $t; }
    }
    $spfOk = count($spf) === 1 && stripos($spf[0], '_spf.mailspamprotection.com') !== false;
    $why = count($spf) === 0 ? 'No SPF record found for ' . $domain : (count($spf) > 1 ? 'More than one SPF record: keep only one' : $spf[0]);
    $c[] = ['SPF record allows SiteGround to send', $spfOk, $why];
    return $c;
}
