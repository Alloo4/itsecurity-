<?php
// ITSN website leads: settings.
// This file lives in the private itsn-leads folder BESIDE public_html, never inside it.
return [
    // Where every new lead is emailed.
    'inbox_to' => 'info@itsecuritynow.com',

    // Optional copy of every lead, e.g. 'dan@itsecuritynow.com'. Leave '' for none.
    'inbox_cc' => '',

    // The address lead emails come from. Replying goes to the visitor, not to this address.
    'mail_from' => 'website@itsecuritynow.com',
    'mail_from_name' => 'ITSN Website',

    // Dashboard password. Leave '' and open https://itsecuritynow.com/leads.php to create this line.
    'dashboard_password_hash' => '',

    // Websites allowed to send form submissions. Anything else is rejected.
    'allowed_hosts' => ['itsecuritynow.com', 'www.itsecuritynow.com'],

    // Spam limits.
    'rate_limit_count' => 5,      // submissions allowed per visitor...
    'rate_limit_window' => 600,   // ...per this many seconds (10 minutes)

    // 'mail' sends real email. 'file' only writes emails to data/outbox (for testing).
    'mail_mode' => 'mail',
];
