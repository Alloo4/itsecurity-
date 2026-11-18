<?php
// ITSN leads dashboard: private, password-protected list of every website enquiry.
// Shared code and settings are in the private itsn-leads folder beside public_html.
declare(strict_types=1);
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow');
header('X-Frame-Options: DENY');

$leadsDir = getenv('ITSN_LEADS_DIR') ?: dirname(__DIR__) . '/itsn-leads';
if (!is_file($leadsDir . '/lib.php')) {
    http_response_code(500);
    exit('<!doctype html><meta charset="utf-8"><title>Setup incomplete</title><body style="font-family:system-ui;max-width:640px;margin:10vh auto;padding:0 20px">'
       . '<h1>Setup incomplete</h1><p>The <code>itsn-leads</code> folder was not found. It must sit <strong>beside</strong> <code>public_html</code>, not inside it, and contain <code>config.php</code> and <code>lib.php</code>.</p>');
}
require $leadsDir . '/lib.php';

const IDLE_LIMIT = 8 * 3600;        // signed out after 8 hours without activity
const LOGIN_TRIES = 5;              // ...attempts
const LOGIN_WINDOW = 15 * 60;       // ...per 15 minutes

$https = (($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
session_name('itsn_leads');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
session_start();
if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }
$csrf = $_SESSION['csrf'];
$self = strtok((string)($_SERVER['REQUEST_URI'] ?? '/leads.php'), '?') ?: '/leads.php';

function csrf_ok(): bool { return hash_equals((string)($_SESSION['csrf'] ?? ''), (string)($_POST['csrf'] ?? '')); }
function go(string $to): void { header('Location: ' . $to, true, 303); exit; }
function flash(string $m): void { $_SESSION['flash'] = $m; }

$authed = false;
$hash = (string)itsn_cfg('dashboard_password_hash');
$post = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';
$action = (string)($_POST['action'] ?? '');

// ---------- first run: create the password line ----------
if ($hash === '') {
    $line = ''; $err = '';
    if ($post && $action === 'setup' && csrf_ok()) {
        $p1 = (string)($_POST['p1'] ?? ''); $p2 = (string)($_POST['p2'] ?? '');
        if (strlen($p1) < 12) { $err = 'Use at least 12 characters.'; }
        elseif (!hash_equals($p1, $p2)) { $err = 'The two passwords don’t match.'; }
        else { $line = "'dashboard_password_hash' => '" . password_hash($p1, PASSWORD_DEFAULT) . "',"; }
    }
    page('Set your dashboard password', function () use ($line, $err, $csrf) { ?>
      <div class="card narrow">
        <h1>Set your dashboard password</h1>
        <?php if ($line): ?>
          <p>Copy this whole line. In SiteGround File Manager, open <code>itsn-leads/config.php</code>, replace the line
             <code>'dashboard_password_hash' =&gt; '',</code> with it, and save. Then reload this page and log in.</p>
          <textarea readonly rows="3" onclick="this.select()"><?= h($line) ?></textarea>
          <p class="muted">Only this scrambled version is stored. Nobody, including SiteGround, can read the password from it.</p>
        <?php else: ?>
          <p>The dashboard stays locked until a password is set. Choose one of at least 12 characters.</p>
          <?php if ($err): ?><p class="bad"><?= h($err) ?></p><?php endif; ?>
          <form method="post">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="setup">
            <label>Password<input type="password" name="p1" minlength="12" required autocomplete="new-password"></label>
            <label>Type it again<input type="password" name="p2" minlength="12" required autocomplete="new-password"></label>
            <button>Create password line</button>
          </form>
        <?php endif; ?>
      </div>
    <?php });
    exit;
}

// ---------- sign in / out ----------
$authed = !empty($_SESSION['authed']) && (time() - (int)($_SESSION['seen'] ?? 0)) < IDLE_LIMIT;
if (!$authed && !empty($_SESSION['authed'])) { $_SESSION = ['csrf' => $csrf]; flash('You were signed out after 8 hours without activity.'); }

if ($post && $action === 'logout' && csrf_ok()) { $_SESSION = []; session_destroy(); go($self); }

if (!$authed) {
    $err = '';
    if ($post && $action === 'login') {
        if (itsn_hits('login', LOGIN_WINDOW, false) >= LOGIN_TRIES) {
            $err = 'Too many attempts. Wait 15 minutes and try again.';
        } elseif (csrf_ok() && password_verify((string)($_POST['password'] ?? ''), $hash)) {
            itsn_hits_clear('login');
            session_regenerate_id(true);
            $_SESSION['authed'] = true; $_SESSION['seen'] = time(); $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $back = (string)($_POST['back'] ?? '');
            go(preg_match('~^\?[\w=&%.+-]*$~', $back) ? $self . $back : $self);
        } else {
            $n = itsn_hits('login', LOGIN_WINDOW, true);
            $err = $n >= LOGIN_TRIES ? 'Too many attempts. Wait 15 minutes and try again.' : 'Wrong password.';
        }
    }
    $back = isset($_GET['id']) ? '?id=' . rawurlencode((string)$_GET['id']) : '';
    $flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
    page('Log in', function () use ($err, $csrf, $back, $flash) { ?>
      <div class="card narrow">
        <h1>ITSN leads</h1>
        <?php if ($flash): ?><p class="muted"><?= h($flash) ?></p><?php endif; ?>
        <?php if ($err): ?><p class="bad"><?= h($err) ?></p><?php endif; ?>
        <form method="post">
          <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="login"><input type="hidden" name="back" value="<?= h($back) ?>">
          <label>Password<input type="password" name="password" required autofocus autocomplete="current-password"></label>
          <button>Log in</button>
        </form>
      </div>
    <?php });
    exit;
}
$_SESSION['seen'] = time();

// ---------- actions ----------
if ($post) {
    if (!csrf_ok()) { flash('That form had expired. Please try again.'); go($self); }
    if ($action === 'save') {
        $id = (string)($_POST['id'] ?? '');
        $status = in_array($_POST['status'] ?? '', ITSN_STATUSES, true) ? (string)$_POST['status'] : 'New';
        $notes = itsn_clean((string)($_POST['notes'] ?? ''), 10000, true);
        flash(itsn_update_lead($id, ['status' => $status, 'notes' => $notes]) ? "Saved $id." : 'Lead not found.');
        go($self . '?id=' . rawurlencode($id));
    }
    if ($action === 'testlead') {
        $lead = itsn_add_lead(['form' => 'System test', 'name' => 'System test', 'email' => (string)itsn_cfg('inbox_to'),
            'message' => 'Test lead sent from the dashboard System check. Set its status to Test.', 'source' => 'System check',
            'ip' => itsn_client_ip(), 'status' => 'Test']);
        $r = itsn_send_lead_email($lead);
        itsn_update_lead($lead['id'], ['email_status' => $r]);
        flash($r === 'sent' ? "Test lead {$lead['id']} saved and emailed to " . itsn_cfg('inbox_to') . '. Check the inbox (and Junk) within 5 minutes.'
                            : "Test lead {$lead['id']} saved, but the email {$r}.");
        go($self . '?view=system');
    }
    go($self);
}

$rows = itsn_read_all();
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);

// ---------- one lead ----------
if (isset($_GET['id'])) {
    $lead = null; foreach ($rows as $r) { if ($r['id'] === $_GET['id']) { $lead = $r; } }
    if (!$lead) { flash('Lead not found.'); go($self); }
    page($lead['id'], function () use ($lead, $csrf, $self, $flash) {
        $subj = rawurlencode('Re: [' . $lead['id'] . '] Your enquiry to IT Security Now');
        $fields = ['Form' => 'form', 'Name' => 'name', 'Email' => 'email', 'Organization' => 'organization', 'Industry' => 'industry',
                   'Phone' => 'phone', 'Source' => 'source', 'First page' => 'landing_page', 'Came from' => 'referrer', 'Received' => 'created',
                   'Email to inbox' => 'email_status', 'IP address' => 'ip']; ?>
      <p><a href="<?= h($self) ?>">&larr; All leads</a></p>
      <?php if ($flash): ?><p class="ok"><?= h($flash) ?></p><?php endif; ?>
      <div class="split">
        <div class="card">
          <h1><?= h($lead['id']) ?> · <?= h($lead['name']) ?></h1>
          <dl><?php foreach ($fields as $label => $k): if ($lead[$k] === '') continue; ?>
            <dt><?= h($label) ?></dt><dd><?= $k === 'email' ? '<a href="mailto:' . h($lead['email']) . '">' . h($lead['email']) . '</a>'
                : ($k === 'phone' ? '<a href="tel:' . h(preg_replace('/[^\d+]/', '', $lead['phone'])) . '">' . h($lead['phone']) . '</a>'
                : ($k === 'created' ? h(date('j M Y, g:i a', strtotime($lead['created']))) : h($k === 'source' ? itsn_source_label($lead[$k]) : $lead[$k]))) ?></dd>
          <?php endforeach; ?></dl>
          <h2>Message</h2><div class="msg"><?= nl2br(h($lead['message'])) ?></div>
          <p><a class="btn" href="mailto:<?= h($lead['email']) ?>?subject=<?= $subj ?>">Reply by email</a></p>
        </div>
        <div class="card">
          <form method="post">
            <input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= h($lead['id']) ?>">
            <label>Status<select name="status"><?php foreach (ITSN_STATUSES as $s): ?><option<?= $s === $lead['status'] ? ' selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?></select></label>
            <label>Notes<textarea name="notes" rows="12" placeholder="Who called, what was agreed, deal size, next step"><?= h($lead['notes']) ?></textarea></label>
            <button>Save</button>
            <p class="muted">Last updated <?= h(date('j M Y, g:i a', strtotime($lead['updated'] ?: $lead['created']))) ?></p>
          </form>
        </div>
      </div>
    <?php });
    exit;
}

// ---------- System check ----------
if (($_GET['view'] ?? '') === 'system') {
    page('System check', function () use ($csrf, $self, $flash) { ?>
      <p><a href="<?= h($self) ?>">&larr; All leads</a></p>
      <?php if ($flash): ?><p class="ok"><?= h($flash) ?></p><?php endif; ?>
      <div class="card"><h1>System check</h1><table>
        <?php foreach (itsn_system_checks() as [$label, $ok, $detail]): ?>
          <tr><td class="<?= $ok ? 'ok' : 'bad' ?>" style="width:2em;font-size:20px"><?= $ok ? '✓' : '✗' ?></td><td><strong><?= h($label) ?></strong><br><span class="muted"><?= h($detail) ?></span></td></tr>
        <?php endforeach; ?>
        <tr><td></td><td><strong>Records file</strong><br><span class="muted"><?= h(itsn_csv_path()) ?></span></td></tr>
      </table>
      <form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="testlead"><button>Send a test lead</button></form></div>
    <?php });
    exit;
}

// ---------- list (and CSV export of the same filters) ----------
$fStatus = (string)($_GET['status'] ?? '');
$q = trim((string)($_GET['q'] ?? ''));
$list = array_values(array_filter($rows, function ($r) use ($fStatus, $q) {
    if ($fStatus === '' ? in_array($r['status'], ITSN_HIDDEN_STATUSES, true) : $r['status'] !== $fStatus) { return false; }
    if ($q !== '') { foreach (['id','name','email','organization','message','notes','source'] as $k) { if (stripos($r[$k], $q) !== false) { return true; } } return false; }
    return true;
}));
usort($list, fn($a, $b) => strcmp($b['created'], $a['created']));

if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="itsn-leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");   // lets Excel read accents correctly
    fputcsv($out, ITSN_COLUMNS, ',', '"', '');
    // A cell starting with = + - @ could run as a formula in Excel; prefix it so it shows as text.
    foreach ($list as $r) { fputcsv($out, array_map(fn($c) => preg_match('/^[=+\-@\t\r]/', (string)$r[$c]) ? "'" . $r[$c] : (string)$r[$c], ITSN_COLUMNS), ',', '"', ''); }
    exit;
}

$real = array_values(array_filter($rows, fn($r) => !in_array($r['status'], ITSN_HIDDEN_STATUSES, true)));
$count = fn(string $s) => count(array_filter($real, fn($r) => $r['status'] === $s));
$sources = [];
foreach ($real as $r) { $k = itsn_source_label($r['source']); $sources[$k] = ($sources[$k] ?? 0) + 1; }
arsort($sources);
$failed = count(array_filter($real, fn($r) => strpos($r['email_status'], 'failed') === 0));

page('Leads', function () use ($list, $real, $count, $sources, $failed, $fStatus, $q, $self, $flash) {
    $qs = http_build_query(array_filter(['status' => $fStatus, 'q' => $q])); ?>
  <?php if ($flash): ?><p class="ok"><?= h($flash) ?></p><?php endif; ?>
  <div class="cards">
    <div class="card stat"><span><?= count($real) ?></span>Total leads</div>
    <div class="card stat hot"><span><?= $count('New') ?></span>New: awaiting follow-up</div>
    <div class="card stat"><span><?= $count('Qualified') + $count('Proposal sent') ?></span>Qualified or proposal sent</div>
    <div class="card stat good"><span><?= $count('Won') ?></span>Won</div>
  </div>
  <?php if ($failed): ?><p class="bad"><?= $failed ?> lead<?= $failed > 1 ? 's' : '' ?> could not be emailed. They are safe here; open them to follow up, and see System check.</p><?php endif; ?>
  <div class="split">
    <div class="card"><h2>Pipeline</h2><table><?php foreach (array_diff(ITSN_STATUSES, ITSN_HIDDEN_STATUSES) as $s): ?>
      <tr><td><a href="?status=<?= rawurlencode($s) ?>"><?= h($s) ?></a></td><td class="num"><?= $count($s) ?></td></tr><?php endforeach; ?></table></div>
    <div class="card"><h2>Where leads come from</h2><table><?php foreach (array_slice($sources, 0, 8, true) as $s => $n): ?>
      <tr><td><?= h($s) ?></td><td class="num"><?= $n ?></td></tr><?php endforeach; if (!$sources): ?><tr><td class="muted">No leads yet</td></tr><?php endif; ?></table></div>
  </div>
  <div class="card">
    <form class="filters" method="get">
      <select name="status" onchange="this.form.submit()"><option value="">All (without Spam and Test)</option>
        <?php foreach (ITSN_STATUSES as $s): ?><option<?= $s === $fStatus ? ' selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?></select>
      <input type="search" name="q" value="<?= h($q) ?>" placeholder="Search name, email, message, notes">
      <button>Filter</button>
      <a class="btn ghost" href="?<?= h($qs . ($qs ? '&' : '') . 'export=1') ?>">Export CSV</a>
    </form>
    <div class="tablewrap"><table class="leads">
      <tr><th>ID</th><th>Received</th><th>Name</th><th>Form</th><th>Source</th><th>Status</th></tr>
      <?php foreach ($list as $r): ?>
        <tr onclick="location='?id=<?= h(rawurlencode($r['id'])) ?>'"><td><a href="?id=<?= h(rawurlencode($r['id'])) ?>"><?= h($r['id']) ?></a></td>
          <td><?= h(date('j M Y, g:i a', strtotime($r['created']))) ?></td>
          <td><?= h($r['name']) ?><br><span class="muted"><?= h($r['email']) ?></span></td>
          <td><?= h($r['form']) ?></td><td><?= h(itsn_source_label($r['source'])) ?></td>
          <td><span class="pill s-<?= h(strtolower(str_replace(' ', '-', $r['status']))) ?>"><?= h($r['status']) ?></span><?= strpos($r['email_status'], 'failed') === 0 ? ' <span class="bad" title="' . h($r['email_status']) . '">email failed</span>' : '' ?></td></tr>
      <?php endforeach; if (!$list): ?><tr><td colspan="6" class="muted">No leads match.</td></tr><?php endif; ?>
    </table></div>
  </div>
<?php });

// ---------- page frame ----------
function page(string $title, callable $body): void {
    global $authed, $csrf, $self; ?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow"><title><?= h($title) ?> · ITSN leads</title>
<style>
:root{--bg:#f3f6f9;--card:#fff;--text:#0a1526;--muted:#5b6b7d;--line:#dde5ec;--teal:#13566f;--good:#1a8a4a;--bad:#b4232c;--hot:#c27a00}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--text);font:15px/1.5 system-ui,-apple-system,"Segoe UI",sans-serif}
header{background:var(--teal);color:#fff;padding:12px 20px;display:flex;gap:16px;align-items:center;flex-wrap:wrap}
header a,header button{color:#fff;background:none;border:0;font:inherit;cursor:pointer;text-decoration:none;padding:0}header strong{margin-right:auto}
main{max-width:1180px;margin:0 auto;padding:20px 16px 60px}h1{font-size:22px;margin:0 0 14px}h2{font-size:16px;margin:0 0 10px}
.card{background:var(--card);border:1px solid var(--line);border-radius:12px;padding:18px 20px;margin-bottom:16px}.narrow{max-width:440px;margin:8vh auto}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px}.stat{font-size:13px;color:var(--muted)}.stat span{display:block;font-size:30px;font-weight:700;color:var(--text)}
.stat.hot span{color:var(--hot)}.stat.good span{color:var(--good)}
.split{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px}
table{width:100%;border-collapse:collapse}td,th{text-align:left;padding:8px 6px;border-top:1px solid var(--line);vertical-align:top}th{font-size:12px;text-transform:uppercase;color:var(--muted);border-top:0}
.num{text-align:right;font-weight:600}.leads tr:not(:first-child){cursor:pointer}.leads tr:not(:first-child):hover{background:#f7fafc}.tablewrap{overflow-x:auto}
label{display:block;margin:0 0 12px;font-weight:600;font-size:13px}input,select,textarea{display:block;width:100%;margin-top:4px;padding:9px 10px;border:1px solid #b9c6d2;border-radius:8px;font:inherit}
button,.btn{display:inline-block;background:var(--teal);color:#fff;border:0;border-radius:8px;padding:9px 16px;font:600 14px system-ui,sans-serif;cursor:pointer;text-decoration:none}
.btn.ghost{background:#fff;color:var(--teal);border:1px solid var(--teal)}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:10px}.filters select,.filters input{width:auto;flex:1 1 200px;margin:0}
dl{display:grid;grid-template-columns:max-content 1fr;gap:6px 16px;margin:0 0 16px}dt{color:var(--muted)}dd{margin:0;overflow-wrap:anywhere}
.msg{background:#f7fafc;border:1px solid var(--line);border-radius:8px;padding:12px;margin-bottom:16px;overflow-wrap:anywhere}
.muted{color:var(--muted)}.ok{color:var(--good);font-weight:600}.bad{color:var(--bad);font-weight:600}
.pill{display:inline-block;padding:2px 10px;border-radius:999px;font-size:12px;font-weight:600;background:#e6edf3}.s-new{background:#fff1d6;color:#8a5600}.s-won{background:#dcf3e5;color:#11643a}.s-lost,.s-spam{background:#f6dfe1;color:#8a1c23}
code{background:#eef3f7;padding:1px 5px;border-radius:4px}textarea[readonly]{font:12px ui-monospace,monospace}
</style></head><body>
<header><strong>ITSN leads</strong><?php if ($authed): ?><a href="<?= h($self) ?>">Leads</a><a href="?view=system">System check</a>
<form method="post" style="margin:0"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="logout"><button>Log out</button></form><?php endif; ?></header>
<main><?php $body(); ?></main></body></html>
<?php }
