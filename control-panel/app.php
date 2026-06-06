<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Chicago');
require_once __DIR__ . '/../common-sections/globals.php';


$cpEmailConfig = [];
$cpEmailConfigPath = __DIR__ . '/../common-sections/email-secrets.php';
if (file_exists($cpEmailConfigPath)) {
    $loadedCpEmailConfig = include $cpEmailConfigPath;
    if (is_array($loadedCpEmailConfig)) {
        $cpEmailConfig = $loadedCpEmailConfig;
    }
}

if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
    $phpMailerCandidates = [
        __DIR__ . '/../common-sections/PHPMailer/src',
        __DIR__ . '/PHPMailer/src',
        __DIR__ . '/../PHPMailer/src',
        __DIR__ . '/../vendor/phpmailer/phpmailer/src',
    ];
    foreach ($phpMailerCandidates as $mailerSrcDir) {
        if (file_exists($mailerSrcDir . '/PHPMailer.php') && file_exists($mailerSrcDir . '/SMTP.php') && file_exists($mailerSrcDir . '/Exception.php')) {
            require_once $mailerSrcDir . '/PHPMailer.php';
            require_once $mailerSrcDir . '/SMTP.php';
            require_once $mailerSrcDir . '/Exception.php';
            break;
        }
    }
}

$allowedAdminEmails = [
    'tkprodesign96@gmail.com',
    'admin@rapidroutelogistics.uk'
];

$cookieEmailRaw = '';
if (isset($_COOKIE['user_Email']) && $_COOKIE['user_Email'] !== '') {
    $cookieEmailRaw = (string)$_COOKIE['user_Email'];
} elseif (isset($_COOKIE['user_email']) && $_COOKIE['user_email'] !== '') {
    $cookieEmailRaw = (string)$_COOKIE['user_email'];
}

if ($cookieEmailRaw === '') {
    header('Location: /dashboard/');
    exit();
}

$cookieEmail = strtolower(trim($cookieEmailRaw));
if (!in_array($cookieEmail, $allowedAdminEmails, true)) {
    header('Location: /dashboard/');
    exit();
}

if (!isset($_SESSION['email']) || strtolower((string)$_SESSION['email']) !== $cookieEmail) {
    $_SESSION['email'] = $cookieEmail;
}

function cp_get_tracking_number_from_post(): string {
    $tracking = '';
    if (isset($_POST['tracking_number'])) {
        $tracking = trim((string)$_POST['tracking_number']);
    }
    if ($tracking === '' && isset($_POST['tracking_id'])) {
        $tracking = trim((string)$_POST['tracking_id']);
    }
    return $tracking;
}

function cp_map_shipment_type(string $raw): string {
    $raw = strtolower(trim($raw));
    if (in_array($raw, ['standard', 'express', 'overnight'], true)) {
        return $raw;
    }
    if ($raw === 'air') return 'express';
    if (in_array($raw, ['ship', 'road', 'rail'], true)) return 'standard';
    return 'standard';
}

$cp_quote_update_notice = '';
$cp_quote_update_notice_type = '';
$cp_quote_delete_notice = '';
$cp_quote_delete_notice_type = '';
$cp_user_delete_notice = '';
$cp_user_delete_notice_type = '';
$cp_shipment_delete_notice = '';
$cp_shipment_delete_notice_type = '';
$cp_location_event_notice = '';
$cp_location_event_notice_type = '';
$cp_user_pay_block_notice = '';
$cp_user_pay_block_notice_type = '';
$cp_support_email_notice = '';
$cp_support_email_notice_type = '';
$cp_exception_payment_notice = '';
$cp_exception_payment_notice_type = '';
$cp_shipment_proof_notice = '';
$cp_shipment_proof_notice_type = '';
$cp_negative_event_notice = '';
$cp_negative_event_notice_type = '';
$cp_arrival_date_notice = '';
$cp_arrival_date_notice_type = '';
$cp_shipment_detail_notice = '';
$cp_shipment_detail_notice_type = '';
$cp_shipment_event_edit_notice = '';
$cp_shipment_event_edit_notice_type = '';

function cp_ensure_shipment_location_event_payment_columns(mysqli $dbconn): void {
    $columnSql = [
        "ALTER TABLE shipment_location_events ADD COLUMN payment_amount DECIMAL(10,2) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN payment_reason VARCHAR(255) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN negative_event_paid TINYINT(1) NOT NULL DEFAULT 0",
        "ALTER TABLE shipment_location_events ADD COLUMN negative_event_paid_at_epoch BIGINT NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN transport_mode VARCHAR(40) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN event_type VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN location_type VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN vessel_name VARCHAR(190) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN voyage_number VARCHAR(80) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN port_of_departure VARCHAR(190) NULL DEFAULT NULL",
        "ALTER TABLE shipment_location_events ADD COLUMN port_of_arrival VARCHAR(190) NULL DEFAULT NULL",
    ];

    foreach ($columnSql as $sql) {
        try {
            $dbconn->query($sql);
        } catch (Throwable $e) {
            // Ignore duplicate-column and missing-table failures here; insert/query logic handles actual table usage.
        }
    }
}
cp_ensure_shipment_location_event_payment_columns($dbconn);

if (isset($_SESSION['cp_quote_notice']) && is_array($_SESSION['cp_quote_notice'])) {
    $cp_quote_update_notice = (string)($_SESSION['cp_quote_notice']['message'] ?? '');
    $cp_quote_update_notice_type = (string)($_SESSION['cp_quote_notice']['type'] ?? '');
    unset($_SESSION['cp_quote_notice']);
}
if (isset($_SESSION['cp_quote_delete_notice']) && is_array($_SESSION['cp_quote_delete_notice'])) {
    $cp_quote_delete_notice = (string)($_SESSION['cp_quote_delete_notice']['message'] ?? '');
    $cp_quote_delete_notice_type = (string)($_SESSION['cp_quote_delete_notice']['type'] ?? '');
    unset($_SESSION['cp_quote_delete_notice']);
}
if (isset($_SESSION['cp_user_delete_notice']) && is_array($_SESSION['cp_user_delete_notice'])) {
    $cp_user_delete_notice = (string)($_SESSION['cp_user_delete_notice']['message'] ?? '');
    $cp_user_delete_notice_type = (string)($_SESSION['cp_user_delete_notice']['type'] ?? '');
    unset($_SESSION['cp_user_delete_notice']);
}
if (isset($_SESSION['cp_shipment_delete_notice']) && is_array($_SESSION['cp_shipment_delete_notice'])) {
    $cp_shipment_delete_notice = (string)($_SESSION['cp_shipment_delete_notice']['message'] ?? '');
    $cp_shipment_delete_notice_type = (string)($_SESSION['cp_shipment_delete_notice']['type'] ?? '');
    unset($_SESSION['cp_shipment_delete_notice']);
}
if (isset($_SESSION['cp_location_notice']) && is_array($_SESSION['cp_location_notice'])) {
    $cp_location_event_notice = (string)($_SESSION['cp_location_notice']['message'] ?? '');
    $cp_location_event_notice_type = (string)($_SESSION['cp_location_notice']['type'] ?? '');
    unset($_SESSION['cp_location_notice']);
}
if (isset($_SESSION['cp_user_block_notice']) && is_array($_SESSION['cp_user_block_notice'])) {
    $cp_user_pay_block_notice = (string)($_SESSION['cp_user_block_notice']['message'] ?? '');
    $cp_user_pay_block_notice_type = (string)($_SESSION['cp_user_block_notice']['type'] ?? '');
    unset($_SESSION['cp_user_block_notice']);
}
if (isset($_SESSION['cp_support_email_notice']) && is_array($_SESSION['cp_support_email_notice'])) {
    $cp_support_email_notice = (string)($_SESSION['cp_support_email_notice']['message'] ?? '');
    $cp_support_email_notice_type = (string)($_SESSION['cp_support_email_notice']['type'] ?? '');
    unset($_SESSION['cp_support_email_notice']);
}
if (isset($_SESSION['cp_exception_payment_notice']) && is_array($_SESSION['cp_exception_payment_notice'])) {
    $cp_exception_payment_notice = (string)($_SESSION['cp_exception_payment_notice']['message'] ?? '');
    $cp_exception_payment_notice_type = (string)($_SESSION['cp_exception_payment_notice']['type'] ?? '');
    unset($_SESSION['cp_exception_payment_notice']);
}
if (isset($_SESSION['cp_shipment_proof_notice']) && is_array($_SESSION['cp_shipment_proof_notice'])) {
    $cp_shipment_proof_notice = (string)($_SESSION['cp_shipment_proof_notice']['message'] ?? '');
    $cp_shipment_proof_notice_type = (string)($_SESSION['cp_shipment_proof_notice']['type'] ?? '');
    unset($_SESSION['cp_shipment_proof_notice']);
}
if (isset($_SESSION['cp_negative_event_notice']) && is_array($_SESSION['cp_negative_event_notice'])) {
    $cp_negative_event_notice = (string)($_SESSION['cp_negative_event_notice']['message'] ?? '');
    $cp_negative_event_notice_type = (string)($_SESSION['cp_negative_event_notice']['type'] ?? '');
    unset($_SESSION['cp_negative_event_notice']);
}
if (isset($_SESSION['cp_arrival_date_notice']) && is_array($_SESSION['cp_arrival_date_notice'])) {
    $cp_arrival_date_notice = (string)($_SESSION['cp_arrival_date_notice']['message'] ?? '');
    $cp_arrival_date_notice_type = (string)($_SESSION['cp_arrival_date_notice']['type'] ?? '');
    unset($_SESSION['cp_arrival_date_notice']);
}
if (isset($_SESSION['cp_shipment_detail_notice']) && is_array($_SESSION['cp_shipment_detail_notice'])) {
    $cp_shipment_detail_notice = (string)($_SESSION['cp_shipment_detail_notice']['message'] ?? '');
    $cp_shipment_detail_notice_type = (string)($_SESSION['cp_shipment_detail_notice']['type'] ?? '');
    unset($_SESSION['cp_shipment_detail_notice']);
}
if (isset($_SESSION['cp_shipment_event_edit_notice']) && is_array($_SESSION['cp_shipment_event_edit_notice'])) {
    $cp_shipment_event_edit_notice = (string)($_SESSION['cp_shipment_event_edit_notice']['message'] ?? '');
    $cp_shipment_event_edit_notice_type = (string)($_SESSION['cp_shipment_event_edit_notice']['type'] ?? '');
    unset($_SESSION['cp_shipment_event_edit_notice']);
}

function cp_parse_datetime_local_to_epoch(string $raw): ?int {
    $raw = trim($raw);
    if ($raw === '') {
        return null;
    }
    $epoch = strtotime($raw);
    if ($epoch === false || $epoch <= 0) {
        return null;
    }
    return (int)$epoch;
}

function cp_nullable_decimal_from_post(string $key): ?float {
    $raw = trim((string)($_POST[$key] ?? ''));
    if ($raw === '') {
        return null;
    }
    return is_numeric($raw) ? (float)$raw : null;
}

function cp_detect_arrival_column_type(mysqli $dbconn): string {
    $type = 'numeric';
    $sql = "SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND COLUMN_NAME = 'estimated_delivery_time' LIMIT 1";
    $result = $dbconn->query($sql);
    if ($result && ($row = $result->fetch_assoc())) {
        $dataType = strtolower(trim((string)($row['DATA_TYPE'] ?? '')));
        if (in_array($dataType, ['datetime', 'timestamp', 'date'], true)) {
            $type = 'datetime';
        }
    }
    return $type;
}

function cp_format_arrival_for_storage(string $columnType, int $epoch): string {
    if ($columnType === 'datetime') {
        return date('Y-m-d H:i:s', $epoch);
    }
    return (string)$epoch;
}

function cp_ensure_shipment_payment_proof_columns(mysqli $dbconn): void {
    $columnSql = [
        "ALTER TABLE shipment_payment_proofs ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT 'pending_confirmation'",
        "ALTER TABLE shipment_payment_proofs ADD COLUMN confirmed_at_epoch BIGINT NULL DEFAULT NULL",
        "ALTER TABLE shipment_payment_proofs ADD COLUMN confirmed_by VARCHAR(190) NULL DEFAULT NULL"
    ];
    foreach ($columnSql as $sql) {
        try {
            $dbconn->query($sql);
        } catch (Throwable $e) {
            // Ignore duplicate-column or missing-table errors.
        }
    }
}
function cp_table_has_column(mysqli $dbconn, string $table, string $column): bool {
    $tableEsc = $dbconn->real_escape_string($table);
    $columnEsc = $dbconn->real_escape_string($column);
    $sql = "SHOW COLUMNS FROM `{$tableEsc}` LIKE '{$columnEsc}'";
    $res = $dbconn->query($sql);
    return (bool)($res && $res->num_rows > 0);
}
function cp_password_secret_for_mailbox(string $fromEmail): string {
    $mailbox = strtolower(trim(explode('@', $fromEmail)[0] ?? ''));
    $map = [
        'billing' => 'BILLING_EMAIL_PASSWORD',
        'shipments' => 'SHIPMENTS_EMAIL_PASSWORD',
        'admin' => 'ADMIN_EMAIL_PASSWORD',
        'support' => 'SUPPORT_EMAIL_PASSWORD',
        'tracking' => 'TRACKING_EMAIL_PASSWORD',
        'noreply' => 'NOREPLY_EMAIL_PASSWORD',
    ];
    return $map[$mailbox] ?? '';
}

function cp_resolve_secret(string $name): string {
    global $cpEmailConfig;
    if ($name === '') {
        return '';
    }
    $value = getenv($name);
    if ($value !== false && trim((string)$value) !== '') {
        return trim((string)$value);
    }
    if (isset($_ENV[$name]) && trim((string)$_ENV[$name]) !== '') {
        return trim((string)$_ENV[$name]);
    }
    if (isset($_SERVER[$name]) && trim((string)$_SERVER[$name]) !== '') {
        return trim((string)$_SERVER[$name]);
    }
    if (isset($cpEmailConfig[$name]) && trim((string)$cpEmailConfig[$name]) !== '') {
        return trim((string)$cpEmailConfig[$name]);
    }
    return '';
}

function cp_resolve_mail_setting(string $envName, string $default = ''): string {
    global $cpEmailConfig;
    $value = getenv($envName);
    if ($value !== false && trim((string)$value) !== '') {
        return trim((string)$value);
    }
    if (isset($_ENV[$envName]) && trim((string)$_ENV[$envName]) !== '') {
        return trim((string)$_ENV[$envName]);
    }
    if (isset($_SERVER[$envName]) && trim((string)$_SERVER[$envName]) !== '') {
        return trim((string)$_SERVER[$envName]);
    }
    if (isset($cpEmailConfig[$envName]) && trim((string)$cpEmailConfig[$envName]) !== '') {
        return trim((string)$cpEmailConfig[$envName]);
    }
    return $default;
}

function cp_send_smtp_html_email(string $toEmail, string $fromEmail, string $subject, string $htmlBody): bool {
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
        error_log('control-panel: PHPMailer is not available');
        return false;
    }

    $passwordSecret = cp_password_secret_for_mailbox($fromEmail);
    $smtpPassword = cp_resolve_secret($passwordSecret);
    if ($smtpPassword === '') {
        error_log('control-panel: missing smtp password secret for ' . $fromEmail . ' expected_secret=' . $passwordSecret);
        return false;
    }

    $smtpHost = cp_resolve_mail_setting('SMTP_HOST', 'mail.spacemail.com');
    $smtpPort = (int)cp_resolve_mail_setting('SMTP_PORT', '465');
    $smtpSecure = cp_resolve_mail_setting('SMTP_SECURE', \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS);

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $fromEmail;
        $mail->Password = $smtpPassword;
        $mail->SMTPSecure = $smtpSecure;
        $mail->Port = $smtpPort;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom($fromEmail, 'Rapid Route Logistics');
        $mail->addAddress($toEmail);
        $mail->addReplyTo('support@rapidroutelogistics.uk', 'Rapid Route Logistics Support');
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = trim(preg_replace('/\s+/', ' ', strip_tags($htmlBody)));
        return $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        error_log('control-panel: PHPMailer failed for to=' . $toEmail . ' from=' . $fromEmail . ' subject=' . $subject . ' err=' . $e->getMessage());
    }

    return false;
}

function cp_build_location_event_email_html(array $payload, string $recipientType): string {
    $trackingNumber = htmlspecialchars((string)($payload['tracking_number'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $statusText = htmlspecialchars((string)($payload['status_text'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $locationName = htmlspecialchars((string)($payload['location_name'] ?? '-'), ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars((string)($payload['city'] ?? ''), ENT_QUOTES, 'UTF-8');
    $stateRegion = htmlspecialchars((string)($payload['state_region'] ?? ''), ENT_QUOTES, 'UTF-8');
    $countryCode = htmlspecialchars((string)($payload['country_code'] ?? ''), ENT_QUOTES, 'UTF-8');
    $eventTimeEpoch = (int)($payload['event_time_epoch'] ?? 0);
    $eventTimeText = $eventTimeEpoch > 0 ? date('F j, Y h:i A T', $eventTimeEpoch) : '-';
    $eventTimeText = htmlspecialchars($eventTimeText, ENT_QUOTES, 'UTF-8');
    $recipientName = trim((string)($payload[$recipientType . '_name'] ?? 'Customer'));
    $safeRecipientName = htmlspecialchars($recipientName !== '' ? $recipientName : 'Customer', ENT_QUOTES, 'UTF-8');
    $roleText = $recipientType === 'sender' ? 'sender' : 'receiver';
    $safeRoleText = htmlspecialchars($roleText, ENT_QUOTES, 'UTF-8');
    $locationPieces = array_filter([$locationName, $city, $stateRegion, $countryCode], static fn($v) => trim((string)$v) !== '');
    $locationText = implode(', ', array_map(static fn($v) => htmlspecialchars_decode($v, ENT_QUOTES), $locationPieces));
    $safeLocationText = htmlspecialchars($locationText !== '' ? $locationText : '-', ENT_QUOTES, 'UTF-8');
    $trackUrl = 'https://rapidroutelogistics.uk/track/?id=' . rawurlencode((string)($payload['tracking_number'] ?? ''));

    return '<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Shipment Location Update</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6;padding:24px 0;">
<tr><td align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="640" style="max-width:640px;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
<tr><td style="background-color:#0f172a;padding:16px 28px;"><img src="https://rapidroutelogistics.uk/assets/images/branding/transparent/logo.png" alt="Rapid Route Logistics" width="220" style="display:block;border:0;max-width:220px;height:auto;"></td></tr>
<tr><td style="padding:24px 40px 8px 40px;"><h1 style="margin:0;font-size:24px;line-height:1.3;color:#0f172a;">Shipment location event added</h1></td></tr>
<tr><td style="padding:0 40px 14px 40px;"><p style="margin:0;font-size:15px;line-height:1.7;color:#374151;">Hello ' . $safeRecipientName . ', this is an automatic update for the ' . $safeRoleText . ' on your shipment.</p></td></tr>
<tr><td style="padding:0 40px 18px 40px;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="border:1px solid #e5e7eb;border-radius:8px;">
<tr><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Tracking Number</td><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">' . $trackingNumber . '</td></tr>
<tr><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Status</td><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">' . $statusText . '</td></tr>
<tr><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:13px;color:#6b7280;">Location</td><td style="padding:12px 14px;border-bottom:1px solid #e5e7eb;font-size:14px;color:#111827;">' . $safeLocationText . '</td></tr>
<tr><td style="padding:12px 14px;font-size:13px;color:#6b7280;">Event Time</td><td style="padding:12px 14px;font-size:14px;color:#111827;">' . $eventTimeText . '</td></tr>
</table>
</td></tr>
<tr><td style="padding:0 40px 24px 40px;"><a href="' . htmlspecialchars($trackUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background-color:#1d4ed8;color:#ffffff;text-decoration:none;padding:12px 20px;border-radius:6px;font-size:14px;font-weight:bold;">Track Shipment</a></td></tr>
<tr><td style="background-color:#f8fafc;border-top:1px solid #e5e7eb;padding:16px 24px;"><p style="margin:0;font-size:11px;line-height:1.5;color:#6b7280;">© 2026 Rapid Route Logistics. Please do not reply to this email.</p></td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
}

function cp_send_location_event_notifications(mysqli $dbconn, int $shipmentId, string $trackingNumber, array $eventPayload): array {
    $shipmentRow = null;

    if ($shipmentId > 0) {
        $stmtShipment = $dbconn->prepare(
            "SELECT tracking_number, sender_name, sender_email, receiver_name, receiver_email
             FROM shipments
             WHERE id = ?
             LIMIT 1"
        );
        if ($stmtShipment) {
            $stmtShipment->bind_param('i', $shipmentId);
            $stmtShipment->execute();
            $res = $stmtShipment->get_result();
            $shipmentRow = $res ? $res->fetch_assoc() : null;
            $stmtShipment->close();
        }
    }

    if (!$shipmentRow && $trackingNumber !== '') {
        $stmtShipment = $dbconn->prepare(
            "SELECT tracking_number, sender_name, sender_email, receiver_name, receiver_email
             FROM shipments
             WHERE tracking_number = ?
             ORDER BY id DESC
             LIMIT 1"
        );
        if ($stmtShipment) {
            $stmtShipment->bind_param('s', $trackingNumber);
            $stmtShipment->execute();
            $res = $stmtShipment->get_result();
            $shipmentRow = $res ? $res->fetch_assoc() : null;
            $stmtShipment->close();
        }
    }

    if (!$shipmentRow) {
        return ['attempted' => 0, 'sent' => 0, 'failed' => 0, 'error' => 'Shipment record not found for notification emails.'];
    }

    $payload = array_merge($shipmentRow, $eventPayload);
    $subject = 'Shipment Tracking Update: ' . (string)($payload['tracking_number'] ?? $trackingNumber);

    $recipients = [
        'sender' => trim((string)($shipmentRow['sender_email'] ?? '')),
        'receiver' => trim((string)($shipmentRow['receiver_email'] ?? '')),
    ];

    $attempted = 0;
    $sent = 0;
    $failed = 0;

    foreach ($recipients as $role => $email) {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            continue;
        }
        $attempted++;
        $html = cp_build_location_event_email_html($payload, $role);
        if (cp_send_smtp_html_email($email, 'tracking@rapidroutelogistics.uk', $subject, $html)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    return ['attempted' => $attempted, 'sent' => $sent, 'failed' => $failed];
}

function cp_send_resend_html_email(string $toEmail, string $subject, string $html): array {
    $apiKey = getenv('RESEND_API_KEY');
    if (!$apiKey || trim($apiKey) === '') {
        $apiKey = 're_AzyocZ26_Lx4bpNbTyHtUFxpikY4mBjjE';
    }
    $apiKey = trim((string)$apiKey);
    if ($apiKey === '') {
        return ['ok' => false, 'error' => 'Missing Resend API key.'];
    }

    $payload = [
        'from' => 'support@rapidroutelogistics.uk',
        'to' => [$toEmail],
        'subject' => $subject,
        'html' => $html
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiKey}",
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($curlErr !== '') {
        return ['ok' => false, 'error' => 'Resend request failed: ' . $curlErr];
    }
    if ($httpCode !== 200 && $httpCode !== 201) {
        return ['ok' => false, 'error' => 'Resend rejected request (' . $httpCode . ').', 'response' => $response];
    }

    return ['ok' => true, 'response' => $response];
}

function cp_build_support_email_html(array $paragraphs, string $adminEmail, string $subject = ''): string {
    $year = date('Y');
    $safeSubject = htmlspecialchars($subject !== '' ? $subject : 'A message from our Support Team', ENT_QUOTES, 'UTF-8');

    $paragraphsHtml = '';
    foreach ($paragraphs as $para) {
        $para = trim((string)$para);
        if ($para === '') continue;
        $safePara = nl2br(htmlspecialchars($para, ENT_QUOTES, 'UTF-8'));
        $paragraphsHtml .= '<p style="margin:0 0 20px 0;font-size:15px;line-height:1.8;color:#2c3e35;">' . $safePara . '</p>';
    }
    if ($paragraphsHtml === '') {
        $paragraphsHtml = '<p style="margin:0;font-size:15px;line-height:1.8;color:#2c3e35;">(No message content)</p>';
    }

    return '<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<title>' . $safeSubject . '</title>
<style>
@media only screen and (max-width:600px){
  .rrl-wrap{padding:16px 8px!important}
  .rrl-card{border-radius:12px!important}
  .rrl-header{padding:22px 24px!important}
  .rrl-body{padding:28px 24px 24px 24px!important}
  .rrl-footer{padding:18px 24px!important}
  .rrl-logo{width:180px!important;max-width:180px!important}
  .rrl-h1{font-size:22px!important}
}
@media (prefers-color-scheme:dark){
  .rrl-wrap{background:#071318!important}
  .rrl-card{background:#0f1e25!important;border-color:#1e3a40!important}
  .rrl-body{background:#0f1e25!important}
  .rrl-h1{color:#ecf5f2!important}
  .rrl-p{color:#b8ccc9!important}
  .rrl-signoff{color:#8aa9a4!important}
  .rrl-footer{background:#09161c!important;border-color:#1e3a40!important}
  .rrl-footer-text{color:#8aa9a4!important}
  .rrl-footer-link{color:#1ec9a4!important}
}
</style>
</head>
<body style="margin:0;padding:0;background:#e8f1ef;font-family:Arial,Helvetica,sans-serif;color:#14232b;">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" class="rrl-wrap" style="background:#e8f1ef;padding:36px 16px;">
<tr><td align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" class="rrl-card" style="width:600px;max-width:600px;background:#ffffff;border:1px solid #d0e4df;border-radius:16px;overflow:hidden;box-shadow:0 12px 40px rgba(20,35,43,0.10);">

  <!-- HEADER -->
  <tr><td class="rrl-header" style="background:#14232b;padding:28px 36px;border-bottom:4px solid #1A9B82;">
    <a href="https://rapidroutelogistics.uk/" target="_blank" rel="noopener" style="text-decoration:none;display:inline-block;">
      <img src="https://rapidroutelogistics.uk/assets/images/branding/transparent/logo-alt.png" alt="Rapid Route Logistics" width="210" class="rrl-logo" style="display:block;border:0;width:210px;max-width:210px;height:auto;">
    </a>
  </td></tr>

  <!-- BODY -->
  <tr><td class="rrl-body" style="padding:36px 36px 32px 36px;background:#ffffff;">
    <h1 class="rrl-h1" style="margin:0 0 28px 0;font-size:24px;line-height:1.3;color:#14232b;font-weight:800;letter-spacing:-0.01em;border-bottom:2px solid #e8f1ef;padding-bottom:20px;">' . $safeSubject . '</h1>

' . $paragraphsHtml . '

    <!-- DIVIDER -->
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:28px 0 20px 0;">
    <tr><td style="border-top:1px solid #ddeee8;font-size:0;line-height:0;">&nbsp;</td></tr>
    </table>

    <p style="margin:0 0 6px 0;font-size:14px;line-height:1.6;color:#14232b;font-weight:700;">Warm regards,</p>
    <p class="rrl-signoff" style="margin:0 0 24px 0;font-size:14px;line-height:1.6;color:#14232b;">The Rapid Route Logistics Support Team</p>

    <p class="rrl-signoff" style="margin:0;font-size:13px;line-height:1.7;color:#60847e;">
      You can reply to this email directly or reach us at
      <a href="mailto:support@rapidroutelogistics.uk" style="color:#1A9B82;text-decoration:none;font-weight:700;">support@rapidroutelogistics.uk</a>.
    </p>
  </td></tr>

  <!-- FOOTER -->
  <tr><td class="rrl-footer" style="background:#f0f8f5;border-top:1px solid #d0e4df;padding:20px 36px;">
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
    <tr>
      <td style="vertical-align:middle;">
        <p class="rrl-footer-text" style="margin:0 0 3px 0;font-size:12px;line-height:1.5;color:#5a7a74;font-weight:700;">Rapid Route Logistics</p>
        <p class="rrl-footer-text" style="margin:0;font-size:11px;line-height:1.5;color:#7a9490;">Secure, fast &amp; reliable delivery operations</p>
      </td>
      <td align="right" style="vertical-align:middle;">
        <a href="https://rapidroutelogistics.uk/" class="rrl-footer-link" style="font-size:11px;color:#1A9B82;text-decoration:none;font-weight:700;">rapidroutelogistics.uk</a>
      </td>
    </tr>
    </table>
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:14px;">
    <tr><td style="border-top:1px solid #d0e4df;padding-top:14px;">
      <p class="rrl-footer-text" style="margin:0;font-size:10px;line-height:1.6;color:#9bb5b0;">&copy; ' . $year . ' Rapid Route Logistics. All rights reserved.</p>
    </td></tr>
    </table>
  </td></tr>

</table>
</td></tr>
</table>
</body>
</html>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_support_email']) && !empty($_POST['send_support_email'])) {
    $receiverEmail = trim((string)($_POST['support_receiver_email'] ?? ''));
    $subject = trim((string)($_POST['support_subject'] ?? ''));
    $rawParagraphs = isset($_POST['support_paragraphs']) && is_array($_POST['support_paragraphs'])
        ? $_POST['support_paragraphs']
        : [];
    $paragraphs = array_values(array_filter(array_map('trim', array_map('strval', $rawParagraphs)), static fn($p) => $p !== ''));

    if ($receiverEmail === '' || !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
        $cp_support_email_notice = 'Receiver email must be valid.';
        $cp_support_email_notice_type = 'error';
    } elseif ($subject === '') {
        $cp_support_email_notice = 'Subject is required.';
        $cp_support_email_notice_type = 'error';
    } elseif (count($paragraphs) === 0) {
        $cp_support_email_notice = 'At least one paragraph is required.';
        $cp_support_email_notice_type = 'error';
    } else {
        $html = cp_build_support_email_html($paragraphs, $cookieEmail, $subject);
        $sent = cp_send_smtp_html_email($receiverEmail, 'support@rapidroutelogistics.uk', $subject, $html);
        if ($sent) {
            $cp_support_email_notice = 'Support email sent successfully.';
            $cp_support_email_notice_type = 'success';
        } else {
            $cp_support_email_notice = 'Support email could not be sent. Check SMTP credentials.';
            $cp_support_email_notice_type = 'error';
        }
    }

    $_SESSION['cp_support_email_notice'] = [
        'message' => $cp_support_email_notice,
        'type' => $cp_support_email_notice_type
    ];
    header('Location: /control-panel/page/#cp-support-email');
    exit();
}

// Confirm exception issue payment by id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_exception_payment']) && !empty($_POST['confirm_exception_payment'])) {
    $exceptionPaymentId = isset($_POST['exception_payment_id']) ? (int)$_POST['exception_payment_id'] : 0;
    $confirmedAt = time();

    if ($exceptionPaymentId <= 0) {
        $cp_exception_payment_notice = 'Payment ID must be a valid number.';
        $cp_exception_payment_notice_type = 'error';
    } else {
        $stmt = $dbconn->prepare(
            "UPDATE exception_issue_payments
             SET status = 'confirmed',
                 updated_at_epoch = ?,
                 confirmed_at_epoch = ?,
                 confirmed_by = ?
             WHERE id = ? AND status = 'pending_confirmation'
             LIMIT 1"
        );

        if (!$stmt) {
            $cp_exception_payment_notice = 'Unable to prepare exception payment confirmation.';
            $cp_exception_payment_notice_type = 'error';
        } else {
            $stmt->bind_param("iisi", $confirmedAt, $confirmedAt, $cookieEmail, $exceptionPaymentId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_exception_payment_notice = "Exception payment #{$exceptionPaymentId} confirmed successfully.";
                $cp_exception_payment_notice_type = 'success';
            } else {
                $stmtCheck = $dbconn->prepare("SELECT id, status FROM exception_issue_payments WHERE id = ? LIMIT 1");
                if ($stmtCheck) {
                    $stmtCheck->bind_param("i", $exceptionPaymentId);
                    $stmtCheck->execute();
                    $resCheck = $stmtCheck->get_result();
                    $rowCheck = $resCheck ? $resCheck->fetch_assoc() : null;
                    $stmtCheck->close();

                    if (!$rowCheck) {
                        $cp_exception_payment_notice = "Exception payment #{$exceptionPaymentId} was not found.";
                    } elseif (strtolower((string)($rowCheck['status'] ?? '')) === 'confirmed') {
                        $cp_exception_payment_notice = "Exception payment #{$exceptionPaymentId} is already confirmed.";
                    } else {
                        $cp_exception_payment_notice = "Exception payment #{$exceptionPaymentId} could not be confirmed from its current status.";
                    }
                } else {
                    $cp_exception_payment_notice = "Exception payment #{$exceptionPaymentId} could not be confirmed.";
                }
                $cp_exception_payment_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_exception_payment_notice'] = [
        'message' => $cp_exception_payment_notice,
        'type' => $cp_exception_payment_notice_type
    ];
    header('Location: /control-panel/page/#cp-exception-payments');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_shipment_payment_proof']) && !empty($_POST['confirm_shipment_payment_proof'])) {
    cp_ensure_shipment_payment_proof_columns($dbconn);
    $hasStatusColumn = cp_table_has_column($dbconn, 'shipment_payment_proofs', 'status');
    $proofId = isset($_POST['shipment_payment_proof_id']) ? (int)$_POST['shipment_payment_proof_id'] : 0;

    if (!$hasStatusColumn) {
        $cp_shipment_proof_notice = 'Payment proof status columns are unavailable in this database. Please run schema update.';
        $cp_shipment_proof_notice_type = 'error';
    } elseif ($proofId <= 0) {
        $cp_shipment_proof_notice = 'Payment proof ID must be a valid number.';
        $cp_shipment_proof_notice_type = 'error';
    } else {
        $stmt = $dbconn->prepare(
            "UPDATE shipment_payment_proofs
             SET status = 'confirmed', confirmed_at_epoch = ?, confirmed_by = ?
             WHERE id = ? AND status <> 'confirmed'
             LIMIT 1"
        );
        if (!$stmt) {
            $cp_shipment_proof_notice = 'Unable to prepare shipment payment proof confirmation.';
            $cp_shipment_proof_notice_type = 'error';
        } else {
            $confirmedAt = time();
            $confirmedBy = $_SESSION['email'] ?? 'admin@rapidroutelogistics.uk';
            $stmt->bind_param("isi", $confirmedAt, $confirmedBy, $proofId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_shipment_proof_notice = "Shipment payment proof #{$proofId} confirmed.";
                $cp_shipment_proof_notice_type = 'success';
            } else {
                $cp_shipment_proof_notice = "Proof #{$proofId} was already confirmed or not found.";
                $cp_shipment_proof_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_shipment_proof_notice'] = [
        'message' => $cp_shipment_proof_notice,
        'type' => $cp_shipment_proof_notice_type
    ];
    header('Location: /control-panel/page/#cp-payment-proofs');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_negative_event_paid']) && !empty($_POST['update_negative_event_paid'])) {
    cp_ensure_shipment_location_event_payment_columns($dbconn);

    $eventId = isset($_POST['negative_event_id']) ? (int)$_POST['negative_event_id'] : 0;
    $paidStatusRaw = strtolower(trim((string)($_POST['negative_event_paid_status'] ?? 'unpaid')));
    $isPaid = $paidStatusRaw === 'paid' ? 1 : 0;
    $paidAt = $isPaid === 1 ? time() : null;
    $updatedAt = time();

    if ($eventId <= 0) {
        $cp_negative_event_notice = 'Event ID must be a valid number.';
        $cp_negative_event_notice_type = 'error';
    } else {
        $stmtCheck = $dbconn->prepare("SELECT id FROM shipment_location_events WHERE id = ? AND event_severity = 'negative' LIMIT 1");
        if (!$stmtCheck) {
            $cp_negative_event_notice = 'Unable to validate negative event record.';
            $cp_negative_event_notice_type = 'error';
        } else {
            $stmtCheck->bind_param("i", $eventId);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $eventExists = ($resCheck && $resCheck->num_rows > 0);
            $stmtCheck->close();

            if (!$eventExists) {
                $cp_negative_event_notice = "Negative event #{$eventId} was not found.";
                $cp_negative_event_notice_type = 'error';
            } else {
                $stmt = $dbconn->prepare(
                    "UPDATE shipment_location_events
                     SET negative_event_paid = ?, negative_event_paid_at_epoch = IF(? = 1, ?, NULL), updated_at_epoch = ?
                     WHERE id = ? AND event_severity = 'negative'
                     LIMIT 1"
                );

                if (!$stmt) {
                    $cp_negative_event_notice = 'Unable to prepare negative event update.';
                    $cp_negative_event_notice_type = 'error';
                } else {
                    $stmt->bind_param("iiiii", $isPaid, $isPaid, $paidAt, $updatedAt, $eventId);
                    $stmt->execute();
                    $stmt->close();

                    $cp_negative_event_notice = $isPaid === 1
                        ? "Negative event #{$eventId} marked as paid."
                        : "Negative event #{$eventId} marked as unpaid.";
                    $cp_negative_event_notice_type = 'success';
                }
            }
        }
    }

    $_SESSION['cp_negative_event_notice'] = [
        'message' => $cp_negative_event_notice,
        'type' => $cp_negative_event_notice_type
    ];
    header('Location: /control-panel/page/#cp-negative-events');
    exit();
}


// Update the full shipment record from the shipment detail page.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shipment_detail']) && !empty($_POST['update_shipment_detail'])) {
    $shipmentId = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
    $trackingNumber = trim((string)($_POST['tracking_number'] ?? ''));
    $senderName = trim((string)($_POST['sender_name'] ?? ''));
    $senderEmail = trim((string)($_POST['sender_email'] ?? ''));
    $senderPhone = trim((string)($_POST['sender_phone'] ?? ''));
    $receiverName = trim((string)($_POST['receiver_name'] ?? ''));
    $receiverEmail = trim((string)($_POST['receiver_email'] ?? ''));
    $receiverPhone = trim((string)($_POST['receiver_phone'] ?? ''));
    $originAddress = trim((string)($_POST['origin_address'] ?? ''));
    $destinationAddress = trim((string)($_POST['destination_address'] ?? ''));
    $shipmentType = cp_map_shipment_type((string)($_POST['shipment_type'] ?? 'standard'));
    $status = strtolower(trim((string)($_POST['status'] ?? 'pending')));
    $currentLocation = trim((string)($_POST['current_location'] ?? ''));
    $completionRaw = trim((string)($_POST['completion_percentage'] ?? '0'));
    $notes = trim((string)($_POST['notes'] ?? ''));
    $estimatedDeliveryEpoch = cp_parse_datetime_local_to_epoch((string)($_POST['estimated_delivery_time'] ?? ''));
    $deliveredAtEpoch = cp_parse_datetime_local_to_epoch((string)($_POST['delivered_at'] ?? ''));
    $length = cp_nullable_decimal_from_post('length');
    $width = cp_nullable_decimal_from_post('width');
    $height = cp_nullable_decimal_from_post('height');
    $weight = cp_nullable_decimal_from_post('weight');
    $validStatuses = ['pending', 'picked_up', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'cancelled'];
    $redirect = $shipmentId > 0 ? '/control-panel/shipments/detail.php?id=' . $shipmentId : '/control-panel/shipments/';

    if ($shipmentId <= 0) {
        $cp_shipment_detail_notice = 'Shipment ID must be valid.';
        $cp_shipment_detail_notice_type = 'error';
    } elseif ($trackingNumber === '' || $senderName === '' || $receiverName === '' || $originAddress === '' || $destinationAddress === '') {
        $cp_shipment_detail_notice = 'Tracking number, sender, receiver, origin, and destination are required.';
        $cp_shipment_detail_notice_type = 'error';
    } elseif ($senderEmail !== '' && !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
        $cp_shipment_detail_notice = 'Sender email is invalid.';
        $cp_shipment_detail_notice_type = 'error';
    } elseif ($receiverEmail !== '' && !filter_var($receiverEmail, FILTER_VALIDATE_EMAIL)) {
        $cp_shipment_detail_notice = 'Receiver email is invalid.';
        $cp_shipment_detail_notice_type = 'error';
    } elseif (!in_array($status, $validStatuses, true)) {
        $cp_shipment_detail_notice = 'Shipment status is invalid.';
        $cp_shipment_detail_notice_type = 'error';
    } elseif ($completionRaw === '' || !ctype_digit($completionRaw) || (int)$completionRaw < 0 || (int)$completionRaw > 100) {
        $cp_shipment_detail_notice = 'Completion percentage must be between 0 and 100.';
        $cp_shipment_detail_notice_type = 'error';
    } else {
        $stmtCurrent = $dbconn->prepare('SELECT tracking_number FROM shipments WHERE id = ? LIMIT 1');
        $currentTracking = '';
        if ($stmtCurrent) {
            $stmtCurrent->bind_param('i', $shipmentId);
            $stmtCurrent->execute();
            $resCurrent = $stmtCurrent->get_result();
            $rowCurrent = $resCurrent ? $resCurrent->fetch_assoc() : null;
            $stmtCurrent->close();
            $currentTracking = $rowCurrent ? (string)$rowCurrent['tracking_number'] : '';
        }

        if ($currentTracking === '') {
            $cp_shipment_detail_notice = "Shipment #{$shipmentId} was not found.";
            $cp_shipment_detail_notice_type = 'error';
        } else {
            $stmtDuplicate = $dbconn->prepare('SELECT id FROM shipments WHERE tracking_number = ? AND id <> ? LIMIT 1');
            $duplicateFound = false;
            if ($stmtDuplicate) {
                $stmtDuplicate->bind_param('si', $trackingNumber, $shipmentId);
                $stmtDuplicate->execute();
                $resDuplicate = $stmtDuplicate->get_result();
                $duplicateFound = (bool)($resDuplicate && $resDuplicate->num_rows > 0);
                $stmtDuplicate->close();
            }

            if ($duplicateFound) {
                $cp_shipment_detail_notice = 'Another shipment already uses tracking number ' . $trackingNumber . '.';
                $cp_shipment_detail_notice_type = 'error';
            } else {
                $completion = (int)$completionRaw;
                $updatedAt = time();
                $arrivalColumnType = cp_detect_arrival_column_type($dbconn);
                $estimatedDeliveryValue = $estimatedDeliveryEpoch !== null ? cp_format_arrival_for_storage($arrivalColumnType, $estimatedDeliveryEpoch) : null;
                $dbconn->begin_transaction();
                try {
                    $stmt = $dbconn->prepare(
                        'UPDATE shipments SET tracking_number = ?, sender_name = ?, sender_email = NULLIF(?, \'\'), sender_phone = NULLIF(?, \'\'), receiver_name = ?, receiver_email = NULLIF(?, \'\'), receiver_phone = NULLIF(?, \'\'), origin_address = ?, destination_address = ?, length = ?, width = ?, height = ?, weight = ?, shipment_type = ?, status = ?, current_location = NULLIF(?, \'\'), completion_percentage = ?, estimated_delivery_time = ?, delivered_at = ?, notes = NULLIF(?, \'\'), date_updated = ? WHERE id = ? LIMIT 1'
                    );
                    if (!$stmt) {
                        throw new RuntimeException('Unable to prepare shipment update.');
                    }
                    $stmt->bind_param(
                        'sssssssssddddsssisisii',
                        $trackingNumber,
                        $senderName,
                        $senderEmail,
                        $senderPhone,
                        $receiverName,
                        $receiverEmail,
                        $receiverPhone,
                        $originAddress,
                        $destinationAddress,
                        $length,
                        $width,
                        $height,
                        $weight,
                        $shipmentType,
                        $status,
                        $currentLocation,
                        $completion,
                        $estimatedDeliveryValue,
                        $deliveredAtEpoch,
                        $notes,
                        $updatedAt,
                        $shipmentId
                    );
                    $stmt->execute();
                    $stmt->close();

                    if ($trackingNumber !== $currentTracking) {
                        $stmtEvents = $dbconn->prepare('UPDATE shipment_location_events SET tracking_number = ?, updated_at_epoch = ? WHERE shipment_id = ? OR tracking_number = ?');
                        if ($stmtEvents) {
                            $stmtEvents->bind_param('siis', $trackingNumber, $updatedAt, $shipmentId, $currentTracking);
                            $stmtEvents->execute();
                            $stmtEvents->close();
                        }
                    }

                    $dbconn->commit();
                    $cp_shipment_detail_notice = "Shipment #{$shipmentId} updated successfully.";
                    $cp_shipment_detail_notice_type = 'success';
                    $redirect = '/control-panel/shipments/detail.php?id=' . $shipmentId;
                } catch (Throwable $e) {
                    $dbconn->rollback();
                    $cp_shipment_detail_notice = 'Could not update shipment. ' . $e->getMessage();
                    $cp_shipment_detail_notice_type = 'error';
                }
            }
        }
    }

    $_SESSION['cp_shipment_detail_notice'] = [
        'message' => $cp_shipment_detail_notice,
        'type' => $cp_shipment_detail_notice_type
    ];
    header('Location: ' . $redirect);
    exit();
}

// Update a shipment event from the shipment detail page.
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shipment_event']) && !empty($_POST['update_shipment_event'])) {
    cp_ensure_shipment_location_event_payment_columns($dbconn);

    $eventId = isset($_POST['event_id']) ? (int)$_POST['event_id'] : 0;
    $shipmentId = isset($_POST['shipment_id']) ? (int)$_POST['shipment_id'] : 0;
    $trackingNumber = trim((string)($_POST['event_tracking_number'] ?? ''));
    $locationLabel = strtolower(trim((string)($_POST['event_location_label'] ?? 'checkpoint')));
    $eventSeverity = strtolower(trim((string)($_POST['event_severity'] ?? 'neutral')));
    $transportMode = strtolower(trim((string)($_POST['event_transport_mode'] ?? '')));
    $eventType = trim((string)($_POST['event_type'] ?? ''));
    $locationType = trim((string)($_POST['event_location_type'] ?? ''));
    $locationName = trim((string)($_POST['event_location_name'] ?? ''));
    $city = trim((string)($_POST['event_city'] ?? ''));
    $stateRegion = trim((string)($_POST['event_state_region'] ?? ''));
    $countryCode = strtoupper(trim((string)($_POST['event_country_code'] ?? '')));
    $postalCode = trim((string)($_POST['event_postal_code'] ?? ''));
    $statusText = trim((string)($_POST['event_status_text'] ?? ''));
    $issueNote = trim((string)($_POST['event_issue_note'] ?? ''));
    $paymentAmount = cp_nullable_decimal_from_post('event_payment_amount');
    $paymentAmountRaw = trim((string)($_POST['event_payment_amount'] ?? ''));
    $paymentReason = trim((string)($_POST['event_payment_reason'] ?? ''));
    $vesselName = trim((string)($_POST['event_vessel_name'] ?? ''));
    $voyageNumber = trim((string)($_POST['event_voyage_number'] ?? ''));
    $portOfDeparture = trim((string)($_POST['event_port_of_departure'] ?? ''));
    $portOfArrival = trim((string)($_POST['event_port_of_arrival'] ?? ''));
    $eventTimeEpoch = cp_parse_datetime_local_to_epoch((string)($_POST['event_time'] ?? ''));
    $isCurrent = isset($_POST['event_is_current']) ? 1 : 0;
    $negativePaid = isset($_POST['event_negative_paid']) ? 1 : 0;
    $validLocationLabels = ['origin', 'checkpoint', 'exception', 'destination'];
    $validSeverities = ['neutral', 'negative'];
    $redirect = $shipmentId > 0 ? '/control-panel/shipments/detail.php?id=' . $shipmentId . '#event-' . $eventId : '/control-panel/shipments/';

    if ($eventId <= 0 || $shipmentId <= 0) {
        $cp_shipment_event_edit_notice = 'Event ID and shipment ID are required.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif ($trackingNumber === '' || $locationName === '' || $statusText === '') {
        $cp_shipment_event_edit_notice = 'Tracking number, location name, and status text are required.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif (!in_array($locationLabel, $validLocationLabels, true)) {
        $cp_shipment_event_edit_notice = 'Event location label is invalid.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif (!in_array($eventSeverity, $validSeverities, true)) {
        $cp_shipment_event_edit_notice = 'Event severity is invalid.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif ($countryCode !== '' && !preg_match('/^[A-Z]{2,8}$/', $countryCode)) {
        $cp_shipment_event_edit_notice = 'Country code must be 2 to 8 letters.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif ($paymentAmountRaw !== '' && $paymentAmount === null) {
        $cp_shipment_event_edit_notice = 'Payment amount must be a valid number.';
        $cp_shipment_event_edit_notice_type = 'error';
    } elseif ($eventTimeEpoch === null) {
        $cp_shipment_event_edit_notice = 'Event time is required.';
        $cp_shipment_event_edit_notice_type = 'error';
    } else {
        $nowEpoch = time();
        $isOrigin = $locationLabel === 'origin' ? 1 : 0;
        $isDestination = $locationLabel === 'destination' ? 1 : 0;
        $paidAt = $negativePaid === 1 ? $nowEpoch : null;
        $dbconn->begin_transaction();
        try {
            if ($isCurrent === 1) {
                $stmtClearCurrent = $dbconn->prepare('UPDATE shipment_location_events SET is_current = NULL, updated_at_epoch = ? WHERE shipment_id = ? AND id <> ?');
                if ($stmtClearCurrent) {
                    $stmtClearCurrent->bind_param('iii', $nowEpoch, $shipmentId, $eventId);
                    $stmtClearCurrent->execute();
                    $stmtClearCurrent->close();
                }
            }
            if ($isOrigin === 1) {
                $stmtClearOrigin = $dbconn->prepare('UPDATE shipment_location_events SET is_origin = NULL, updated_at_epoch = ? WHERE shipment_id = ? AND id <> ?');
                if ($stmtClearOrigin) {
                    $stmtClearOrigin->bind_param('iii', $nowEpoch, $shipmentId, $eventId);
                    $stmtClearOrigin->execute();
                    $stmtClearOrigin->close();
                }
            }
            if ($isDestination === 1) {
                $stmtClearDestination = $dbconn->prepare('UPDATE shipment_location_events SET is_destination = NULL, updated_at_epoch = ? WHERE shipment_id = ? AND id <> ?');
                if ($stmtClearDestination) {
                    $stmtClearDestination->bind_param('iii', $nowEpoch, $shipmentId, $eventId);
                    $stmtClearDestination->execute();
                    $stmtClearDestination->close();
                }
            }

            $stmt = $dbconn->prepare(
                'UPDATE shipment_location_events SET tracking_number = ?, location_label = ?, event_severity = ?, transport_mode = NULLIF(?, \'\'), event_type = NULLIF(?, \'\'), location_type = NULLIF(?, \'\'), is_current = IF(? = 1, 1, NULL), is_origin = IF(? = 1, 1, NULL), is_destination = IF(? = 1, 1, NULL), location_name = ?, city = NULLIF(?, \'\'), state_region = NULLIF(?, \'\'), country_code = NULLIF(?, \'\'), postal_code = NULLIF(?, \'\'), status_text = ?, issue_note = NULLIF(?, \'\'), payment_amount = ?, payment_reason = NULLIF(?, \'\'), vessel_name = NULLIF(?, \'\'), voyage_number = NULLIF(?, \'\'), port_of_departure = NULLIF(?, \'\'), port_of_arrival = NULLIF(?, \'\'), negative_event_paid = ?, negative_event_paid_at_epoch = IF(? = 1, ?, NULL), event_time_epoch = ?, updated_at_epoch = ? WHERE id = ? AND shipment_id = ? LIMIT 1'
            );
            if (!$stmt) {
                throw new RuntimeException('Unable to prepare event update.');
            }
            $stmt->bind_param(
                'ssssssiiisssssssdsssssiiiiiii',
                $trackingNumber,
                $locationLabel,
                $eventSeverity,
                $transportMode,
                $eventType,
                $locationType,
                $isCurrent,
                $isOrigin,
                $isDestination,
                $locationName,
                $city,
                $stateRegion,
                $countryCode,
                $postalCode,
                $statusText,
                $issueNote,
                $paymentAmount,
                $paymentReason,
                $vesselName,
                $voyageNumber,
                $portOfDeparture,
                $portOfArrival,
                $negativePaid,
                $negativePaid,
                $paidAt,
                $eventTimeEpoch,
                $nowEpoch,
                $eventId,
                $shipmentId
            );
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            $dbconn->commit();

            $cp_shipment_event_edit_notice = $affected >= 0 ? "Event #{$eventId} saved." : "Event #{$eventId} was not updated.";
            $cp_shipment_event_edit_notice_type = 'success';
        } catch (Throwable $e) {
            $dbconn->rollback();
            $cp_shipment_event_edit_notice = 'Could not update shipment event. ' . $e->getMessage();
            $cp_shipment_event_edit_notice_type = 'error';
        }
    }

    $_SESSION['cp_shipment_event_edit_notice'] = [
        'message' => $cp_shipment_event_edit_notice,
        'type' => $cp_shipment_event_edit_notice_type
    ];
    header('Location: ' . $redirect);
    exit();
}

// Update shipment status by tracking number
$cp_shipment_status_notice = '';
$cp_shipment_status_notice_type = 'success';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shipment_status']) && !empty($_POST['update_shipment_status'])) {
    $statusTrackingNumber = trim((string)($_POST['status_tracking_number'] ?? ''));
    $newStatus = strtolower(trim((string)($_POST['new_shipment_status'] ?? '')));
    $newCompletionRaw = trim((string)($_POST['new_completion_pct'] ?? ''));

    $validStatuses = ['pending', 'picked_up', 'shipped', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'cancelled'];

    if ($statusTrackingNumber === '') {
        $cp_shipment_status_notice = 'Tracking Number is required.';
        $cp_shipment_status_notice_type = 'error';
    } elseif (!in_array($newStatus, $validStatuses, true)) {
        $cp_shipment_status_notice = 'Please select a valid status.';
        $cp_shipment_status_notice_type = 'error';
    } elseif ($newCompletionRaw !== '' && (!is_numeric($newCompletionRaw) || (int)$newCompletionRaw < 0 || (int)$newCompletionRaw > 100)) {
        $cp_shipment_status_notice = 'Completion % must be a whole number between 0 and 100.';
        $cp_shipment_status_notice_type = 'error';
    } else {
        $newCompletion = ($newCompletionRaw !== '') ? (int)$newCompletionRaw : null;
        $updatedAt = time();

        if ($newCompletion !== null) {
            $stmtStatus = $dbconn->prepare(
                "UPDATE shipments SET status = ?, completion_percentage = ?, date_updated = ? WHERE tracking_number = ? LIMIT 1"
            );
            if ($stmtStatus) {
                $stmtStatus->bind_param('siis', $newStatus, $newCompletion, $updatedAt, $statusTrackingNumber);
            }
        } else {
            $stmtStatus = $dbconn->prepare(
                "UPDATE shipments SET status = ?, date_updated = ? WHERE tracking_number = ? LIMIT 1"
            );
            if ($stmtStatus) {
                $stmtStatus->bind_param('sis', $newStatus, $updatedAt, $statusTrackingNumber);
            }
        }

        if (!$stmtStatus) {
            $cp_shipment_status_notice = 'Unable to prepare status update.';
            $cp_shipment_status_notice_type = 'error';
        } else {
            $stmtStatus->execute();
            $affected = $stmtStatus->affected_rows;
            $stmtStatus->close();

            if ($affected > 0) {
                $cp_shipment_status_notice = 'Status updated to "' . htmlspecialchars($newStatus) . '" for tracking number ' . htmlspecialchars($statusTrackingNumber) . '.';
                $cp_shipment_status_notice_type = 'success';
            } elseif ($dbconn->errno === 0) {
                $cp_shipment_status_notice = 'No shipment found with tracking number "' . htmlspecialchars($statusTrackingNumber) . '". Nothing was updated.';
                $cp_shipment_status_notice_type = 'error';
            } else {
                $cp_shipment_status_notice = 'Database error: ' . htmlspecialchars($dbconn->error);
                $cp_shipment_status_notice_type = 'error';
            }
        }
    }
}

// Update shipment arrival date by tracking number
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_shipment_arrival_date']) && !empty($_POST['update_shipment_arrival_date'])) {
    $trackingNumber = trim((string)($_POST['arrival_tracking_number'] ?? ''));
    $arrivalRaw = trim((string)($_POST['arrival_date'] ?? ''));

    if ($trackingNumber === '') {
        $cp_arrival_date_notice = 'Tracking Number is required.';
        $cp_arrival_date_notice_type = 'error';
    } elseif ($arrivalRaw === '') {
        $cp_arrival_date_notice = 'Arrival date is required.';
        $cp_arrival_date_notice_type = 'error';
    } else {
        $arrivalEpoch = strtotime($arrivalRaw);
        if ($arrivalEpoch === false || $arrivalEpoch <= 0) {
            $cp_arrival_date_notice = 'Arrival date format is invalid.';
            $cp_arrival_date_notice_type = 'error';
        } else {
            $columnType = cp_detect_arrival_column_type($dbconn);
            $arrivalValue = cp_format_arrival_for_storage($columnType, (int)$arrivalEpoch);
            $updatedAt = time();

            $stmt = $dbconn->prepare("UPDATE shipments SET estimated_delivery_time = ?, date_updated = ? WHERE tracking_number = ? LIMIT 1");
            if (!$stmt) {
                $cp_arrival_date_notice = 'Unable to prepare arrival date update.';
                $cp_arrival_date_notice_type = 'error';
            } else {
                $stmt->bind_param('sis', $arrivalValue, $updatedAt, $trackingNumber);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                if ($affected > 0) {
                    $storageLabel = $columnType === 'datetime' ? 'datetime' : 'epoch number';
                    $cp_arrival_date_notice = 'Arrival date updated for tracking ' . $trackingNumber . ' (stored as ' . $storageLabel . ').';
                    $cp_arrival_date_notice_type = 'success';
                } else {
                    $cp_arrival_date_notice = 'No shipment was updated. Check tracking number or unchanged value.';
                    $cp_arrival_date_notice_type = 'error';
                }
            }
        }
    }

    $_SESSION['cp_arrival_date_notice'] = [
        'message' => $cp_arrival_date_notice,
        'type' => $cp_arrival_date_notice_type
    ];
    header('Location: /control-panel/page/#cp-update-arrival-date');
    exit();
}

// Delete users row by id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_site_user']) && !empty($_POST['delete_site_user'])) {
    $userId = isset($_POST['delete_user_id']) ? (int)$_POST['delete_user_id'] : 0;

    if ($userId <= 0) {
        $cp_user_delete_notice = 'User ID must be a valid number.';
        $cp_user_delete_notice_type = 'error';
    } else {
        $stmt = $dbconn->prepare("DELETE FROM users WHERE id = ? LIMIT 1");
        if (!$stmt) {
            $cp_user_delete_notice = 'Unable to prepare user delete.';
            $cp_user_delete_notice_type = 'error';
        } else {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_user_delete_notice = "User #{$userId} deleted successfully.";
                $cp_user_delete_notice_type = 'success';
            } else {
                $cp_user_delete_notice = "User #{$userId} was not found.";
                $cp_user_delete_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_user_delete_notice'] = [
        'message' => $cp_user_delete_notice,
        'type' => $cp_user_delete_notice_type
    ];
    header('Location: /control-panel/page/#cp-delete-site-user');
    exit();
}

// Delete shipment row by id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_shipment_record']) && !empty($_POST['delete_shipment_record'])) {
    $shipmentId = isset($_POST['delete_shipment_id']) ? (int)$_POST['delete_shipment_id'] : 0;

    if ($shipmentId <= 0) {
        $cp_shipment_delete_notice = 'Shipment ID must be a valid number.';
        $cp_shipment_delete_notice_type = 'error';
    } else {
        $stmt = $dbconn->prepare("DELETE FROM shipments WHERE id = ? LIMIT 1");
        if (!$stmt) {
            $cp_shipment_delete_notice = 'Unable to prepare shipment delete.';
            $cp_shipment_delete_notice_type = 'error';
        } else {
            $stmt->bind_param("i", $shipmentId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_shipment_delete_notice = "Shipment #{$shipmentId} deleted successfully.";
                $cp_shipment_delete_notice_type = 'success';
            } else {
                $cp_shipment_delete_notice = "Shipment #{$shipmentId} was not found.";
                $cp_shipment_delete_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_shipment_delete_notice'] = [
        'message' => $cp_shipment_delete_notice,
        'type' => $cp_shipment_delete_notice_type
    ];
    header('Location: /control-panel/page/#cp-delete-shipment');
    exit();
}

// Update shipment_service_quotes (price + duration) by id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_service_quote']) && !empty($_POST['update_service_quote'])) {
    $quoteId = isset($_POST['quote_id']) ? (int)$_POST['quote_id'] : 0;
    $priceRaw = trim((string)($_POST['quote_price'] ?? ''));
    $durationRaw = trim((string)($_POST['quote_duration'] ?? ''));

    if ($quoteId <= 0) {
        $cp_quote_update_notice = 'Quote ID must be a valid number.';
        $cp_quote_update_notice_type = 'error';
    } elseif ($priceRaw === '' || !is_numeric($priceRaw) || (float)$priceRaw < 0) {
        $cp_quote_update_notice = 'Price must be a valid number (0 or greater).';
        $cp_quote_update_notice_type = 'error';
    } elseif ($durationRaw === '' || !ctype_digit($durationRaw) || (int)$durationRaw <= 0) {
        $cp_quote_update_notice = 'Duration must be a whole number greater than 0.';
        $cp_quote_update_notice_type = 'error';
    } else {
        $price = (float)$priceRaw;
        $duration = (int)$durationRaw;
        $updatedAt = time();

        $sql = "UPDATE shipment_service_quotes
                SET price = ?, duration = ?, updated_at_epoch = ?
                WHERE id = ?
                LIMIT 1";
        $stmt = $dbconn->prepare($sql);

        if (!$stmt) {
            $cp_quote_update_notice = 'Unable to prepare quote update.';
            $cp_quote_update_notice_type = 'error';
        } else {
            $stmt->bind_param("diii", $price, $duration, $updatedAt, $quoteId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_quote_update_notice = "Quote #{$quoteId} updated successfully.";
                $cp_quote_update_notice_type = 'success';
            } else {
                $cp_quote_update_notice = "No record updated. Check if Quote ID #{$quoteId} exists or values are unchanged.";
                $cp_quote_update_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_quote_notice'] = [
        'message' => $cp_quote_update_notice,
        'type' => $cp_quote_update_notice_type
    ];
    header('Location: /control-panel/page/#cp-edit-service-quote');
    exit();
}

// Delete shipment_service_quotes row by id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_service_quote']) && !empty($_POST['delete_service_quote'])) {
    $quoteId = isset($_POST['delete_quote_id']) ? (int)$_POST['delete_quote_id'] : 0;

    if ($quoteId <= 0) {
        $cp_quote_delete_notice = 'Quote ID must be a valid number.';
        $cp_quote_delete_notice_type = 'error';
    } else {
        $stmt = $dbconn->prepare("DELETE FROM shipment_service_quotes WHERE id = ? LIMIT 1");
        if (!$stmt) {
            $cp_quote_delete_notice = 'Unable to prepare quote delete.';
            $cp_quote_delete_notice_type = 'error';
        } else {
            $stmt->bind_param("i", $quoteId);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();

            if ($affected > 0) {
                $cp_quote_delete_notice = "Quote #{$quoteId} deleted successfully.";
                $cp_quote_delete_notice_type = 'success';
            } else {
                $cp_quote_delete_notice = "Quote #{$quoteId} was not found.";
                $cp_quote_delete_notice_type = 'error';
            }
        }
    }

    $_SESSION['cp_quote_delete_notice'] = [
        'message' => $cp_quote_delete_notice,
        'type' => $cp_quote_delete_notice_type
    ];
    header('Location: /control-panel/page/#cp-delete-service-quote');
    exit();
}

// Insert shipment_location_events row
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_location_event']) && !empty($_POST['add_location_event'])) {
    cp_ensure_shipment_location_event_payment_columns($dbconn);

    $trackingNumber = trim((string)($_POST['event_tracking_number'] ?? ''));
    $locationLabel = strtolower(trim((string)($_POST['event_location_label'] ?? 'checkpoint')));
    $eventSeverity = strtolower(trim((string)($_POST['event_severity'] ?? 'neutral')));
    $isCurrent = 1;
    $isOrigin = 0;
    $isDestination = 0;
    $locationName = trim((string)($_POST['event_location_name'] ?? ''));
    $city = trim((string)($_POST['event_city'] ?? ''));
    $stateRegion = trim((string)($_POST['event_state_region'] ?? ''));
    $countryCode = strtoupper(trim((string)($_POST['event_country_code'] ?? 'US')));
    $postalCode = trim((string)($_POST['event_postal_code'] ?? ''));
    $statusText = trim((string)($_POST['event_status_text'] ?? ''));
    $issueNote = '';
    $paymentAmountRaw = trim((string)($_POST['event_payment_amount'] ?? ''));
    $paymentReason = trim((string)($_POST['event_payment_reason'] ?? ''));
    $transportMode = strtolower(trim((string)($_POST['event_transport_mode'] ?? '')));
    $eventType = trim((string)($_POST['event_type'] ?? ''));
    $locationType = trim((string)($_POST['event_location_type'] ?? ''));
    $vesselName = trim((string)($_POST['event_vessel_name'] ?? ''));
    $voyageNumber = trim((string)($_POST['event_voyage_number'] ?? ''));
    $portOfDeparture = trim((string)($_POST['event_port_of_departure'] ?? ''));
    $portOfArrival = trim((string)($_POST['event_port_of_arrival'] ?? ''));
    $nowEpoch = time();
    $eventTimeEpoch = $nowEpoch;

    $shipmentId = 0;
    if ($trackingNumber !== '') {
        $stmtLookup = $dbconn->prepare("SELECT id FROM shipments WHERE tracking_number = ? ORDER BY id DESC LIMIT 1");
        if ($stmtLookup) {
            $stmtLookup->bind_param('s', $trackingNumber);
            $stmtLookup->execute();
            $resLookup = $stmtLookup->get_result();
            $rowLookup = $resLookup ? $resLookup->fetch_assoc() : null;
            $stmtLookup->close();
            $shipmentId = $rowLookup ? (int)$rowLookup['id'] : 0;
        }
    }

    $validLocationLabels = ['origin', 'checkpoint', 'exception', 'destination'];
    $validSeverities = ['neutral', 'negative'];
    if ($trackingNumber === '') {
        $cp_location_event_notice = 'Tracking Number is required.';
        $cp_location_event_notice_type = 'error';
    } elseif ($shipmentId <= 0) {
        $cp_location_event_notice = 'No shipment found with tracking number "' . htmlspecialchars($trackingNumber) . '". Please check and try again.';
        $cp_location_event_notice_type = 'error';
    } elseif ($locationName === '') {
        $cp_location_event_notice = 'Location Name is required.';
        $cp_location_event_notice_type = 'error';
    } elseif ($statusText === '') {
        $cp_location_event_notice = 'Status Text is required.';
        $cp_location_event_notice_type = 'error';
    } elseif (!in_array($locationLabel, $validLocationLabels, true)) {
        $cp_location_event_notice = 'Location label is invalid.';
        $cp_location_event_notice_type = 'error';
    } elseif (!in_array($eventSeverity, $validSeverities, true)) {
        $cp_location_event_notice = 'Event severity is invalid.';
        $cp_location_event_notice_type = 'error';
    } elseif (!preg_match('/^[A-Z]{2}$/', $countryCode)) {
        $cp_location_event_notice = 'Country code must be a valid 2-letter code (e.g., US).';
        $cp_location_event_notice_type = 'error';
    } elseif ($paymentAmountRaw !== '' && (!is_numeric($paymentAmountRaw) || (float)$paymentAmountRaw < 0)) {
        $cp_location_event_notice = 'Payment amount must be a valid number (0 or greater).';
        $cp_location_event_notice_type = 'error';
    } elseif ($paymentAmountRaw !== '' && $paymentReason === '') {
        $cp_location_event_notice = 'Add what the payment is for when a payment amount is provided.';
        $cp_location_event_notice_type = 'error';
    } else {
        $paymentAmount = ($paymentAmountRaw !== '') ? (float)$paymentAmountRaw : null;

        if ($locationLabel === 'origin') {
            $isOrigin = 1;
            $isDestination = 0;
        } elseif ($locationLabel === 'destination') {
            $isOrigin = 0;
            $isDestination = 1;
        } else {
            $isOrigin = 0;
            $isDestination = 0;
        }

        // Keep a single "current" event for this shipment/tracking.
        $stmtClearCurrent = $dbconn->prepare(
            "UPDATE shipment_location_events
             SET is_current = NULL, updated_at_epoch = ?
             WHERE shipment_id = ? OR tracking_number = ?"
        );
        if ($stmtClearCurrent) {
            $stmtClearCurrent->bind_param("iis", $nowEpoch, $shipmentId, $trackingNumber);
            try {
                $stmtClearCurrent->execute();
            } catch (Throwable $e) {
                // Keep moving; the insert path below will surface a proper admin notice if needed.
            }
            $stmtClearCurrent->close();
        }

        // Unique indexes allow only one origin and one destination flag per shipment.
        if ($isOrigin === 1) {
            $stmtClearOrigin = $dbconn->prepare(
                "UPDATE shipment_location_events
                 SET is_origin = NULL, updated_at_epoch = ?
                 WHERE (shipment_id = ? OR tracking_number = ?) AND is_origin = 1"
            );
            if ($stmtClearOrigin) {
                $stmtClearOrigin->bind_param("iis", $nowEpoch, $shipmentId, $trackingNumber);
                try {
                    $stmtClearOrigin->execute();
                } catch (Throwable $e) {
                    // If this cleanup fails, the insert below will still produce a user-facing notice.
                }
                $stmtClearOrigin->close();
            }
        }

        if ($isDestination === 1) {
            $stmtClearDestination = $dbconn->prepare(
                "UPDATE shipment_location_events
                 SET is_destination = NULL, updated_at_epoch = ?
                 WHERE (shipment_id = ? OR tracking_number = ?) AND is_destination = 1"
            );
            if ($stmtClearDestination) {
                $stmtClearDestination->bind_param("iis", $nowEpoch, $shipmentId, $trackingNumber);
                try {
                    $stmtClearDestination->execute();
                } catch (Throwable $e) {
                    // If this cleanup fails, the insert below will still produce a user-facing notice.
                }
                $stmtClearDestination->close();
            }
        }

        $sql = "INSERT INTO shipment_location_events
                (shipment_id, tracking_number, location_label, event_severity, transport_mode, event_type, location_type, is_current, is_origin, is_destination, location_name, city, state_region, country_code, postal_code, status_text, issue_note, payment_amount, payment_reason, vessel_name, voyage_number, port_of_departure, port_of_arrival, negative_event_paid, negative_event_paid_at_epoch, event_time_epoch, created_at_epoch, updated_at_epoch)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, IF(? = 1, 1, NULL), IF(? = 1, 1, NULL), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NULL, ?, ?, ?)";
        $stmt = $dbconn->prepare($sql);

        if (!$stmt) {
            $cp_location_event_notice = 'Unable to prepare location event insert.';
            $cp_location_event_notice_type = 'error';
        } else {
            $stmt->bind_param(
                "issssssiiisssssssdsssssiii",
                $shipmentId,
                $trackingNumber,
                $locationLabel,
                $eventSeverity,
                $transportMode,
                $eventType,
                $locationType,
                $isCurrent,
                $isOrigin,
                $isDestination,
                $locationName,
                $city,
                $stateRegion,
                $countryCode,
                $postalCode,
                $statusText,
                $issueNote,
                $paymentAmount,
                $paymentReason,
                $vesselName,
                $voyageNumber,
                $portOfDeparture,
                $portOfArrival,
                $eventTimeEpoch,
                $nowEpoch,
                $nowEpoch
            );

            try {
                if ($stmt->execute()) {
                    $insertedId = (int)$stmt->insert_id;
                    $notificationResult = cp_send_location_event_notifications(
                        $dbconn,
                        $shipmentId,
                        $trackingNumber,
                        [
                            'tracking_number' => $trackingNumber,
                            'status_text' => $statusText,
                            'location_name' => $locationName,
                            'city' => $city,
                            'state_region' => $stateRegion,
                            'country_code' => $countryCode,
                            'event_time_epoch' => $eventTimeEpoch,
                        ]
                    );

                    $cp_location_event_notice = "Location event #{$insertedId} added successfully.";
                    if ((int)($notificationResult['attempted'] ?? 0) > 0) {
                        $cp_location_event_notice .= ' Email notifications sent: ' . (int)$notificationResult['sent'] . '/' . (int)$notificationResult['attempted'] . '.';
                    } elseif (!empty($notificationResult['error'])) {
                        $cp_location_event_notice .= ' ' . (string)$notificationResult['error'];
                    } else {
                        $cp_location_event_notice .= ' No valid sender/receiver email found for notification.';
                    }

                    $cp_location_event_notice_type = ((int)($notificationResult['failed'] ?? 0) > 0) ? 'error' : 'success';
                } else {
                    $cp_location_event_notice = 'Could not insert location event. Check shipment/tracking values and try again.';
                    $cp_location_event_notice_type = 'error';
                }
            } catch (Throwable $e) {
                $cp_location_event_notice = 'Could not insert location event. Existing origin/destination flags were adjusted if needed, but the new event still failed validation.';
                $cp_location_event_notice_type = 'error';
            }
            $stmt->close();
        }
    }

    $_SESSION['cp_location_notice'] = [
        'message' => $cp_location_event_notice,
        'type' => $cp_location_event_notice_type
    ];
    header('Location: /control-panel/page/#cp-add-location-event');
    exit();
}

// Set users.pay_block = 1 and update users.pay_block_tittle/users.pay_block_message by user id
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user_pay_block']) && !empty($_POST['update_user_pay_block'])) {
    $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $payBlockTittle = trim((string)($_POST['pay_block_tittle'] ?? ''));
    $payBlockMessage = trim((string)($_POST['pay_block_message'] ?? ''));
    $payBlock = 1;

    if ($userId <= 0) {
        $cp_user_pay_block_notice = 'User ID must be a valid number.';
        $cp_user_pay_block_notice_type = 'error';
    } else {
        $userExists = false;
        $stmtUserCheck = $dbconn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        if ($stmtUserCheck) {
            $stmtUserCheck->bind_param("i", $userId);
            $stmtUserCheck->execute();
            $resUserCheck = $stmtUserCheck->get_result();
            $userExists = ($resUserCheck && $resUserCheck->num_rows > 0);
            $stmtUserCheck->close();
        }
        if (!$userExists) {
            $cp_user_pay_block_notice = "User #{$userId} was not found.";
            $cp_user_pay_block_notice_type = 'error';
        } elseif ($payBlockTittle === '' && $payBlockMessage === '') {
            $sql = "UPDATE users SET pay_block = ?, pay_block_tittle = NULL, pay_block_message = NULL WHERE id = ? LIMIT 1";
            $stmt = $dbconn->prepare($sql);
            if (!$stmt) {
                $cp_user_pay_block_notice = 'Unable to prepare user payment-block update.';
                $cp_user_pay_block_notice_type = 'error';
            } else {
                $stmt->bind_param("ii", $payBlock, $userId);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                if ($affected > 0) {
                    $cp_user_pay_block_notice = "User #{$userId} payment block updated.";
                } else {
                    $cp_user_pay_block_notice = "User #{$userId} already has these payment block values.";
                }
                $cp_user_pay_block_notice_type = 'success';
            }
        } elseif ($payBlockTittle === '') {
            $sql = "UPDATE users SET pay_block = ?, pay_block_tittle = NULL, pay_block_message = ? WHERE id = ? LIMIT 1";
            $stmt = $dbconn->prepare($sql);
            if (!$stmt) {
                $cp_user_pay_block_notice = 'Unable to prepare user payment-block update.';
                $cp_user_pay_block_notice_type = 'error';
            } else {
                $stmt->bind_param("isi", $payBlock, $payBlockMessage, $userId);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                if ($affected > 0) {
                    $cp_user_pay_block_notice = "User #{$userId} payment block updated.";
                } else {
                    $cp_user_pay_block_notice = "User #{$userId} already has these payment block values.";
                }
                $cp_user_pay_block_notice_type = 'success';
            }
        } elseif ($payBlockMessage === '') {
            $sql = "UPDATE users SET pay_block = ?, pay_block_tittle = ?, pay_block_message = NULL WHERE id = ? LIMIT 1";
            $stmt = $dbconn->prepare($sql);
            if (!$stmt) {
                $cp_user_pay_block_notice = 'Unable to prepare user payment-block update.';
                $cp_user_pay_block_notice_type = 'error';
            } else {
                $stmt->bind_param("isi", $payBlock, $payBlockTittle, $userId);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                if ($affected > 0) {
                    $cp_user_pay_block_notice = "User #{$userId} payment block updated.";
                } else {
                    $cp_user_pay_block_notice = "User #{$userId} already has these payment block values.";
                }
                $cp_user_pay_block_notice_type = 'success';
            }
        } else {
            $sql = "UPDATE users SET pay_block = ?, pay_block_tittle = ?, pay_block_message = ? WHERE id = ? LIMIT 1";
            $stmt = $dbconn->prepare($sql);
            if (!$stmt) {
                $cp_user_pay_block_notice = 'Unable to prepare user payment-block update.';
                $cp_user_pay_block_notice_type = 'error';
            } else {
                $stmt->bind_param("issi", $payBlock, $payBlockTittle, $payBlockMessage, $userId);
                $stmt->execute();
                $affected = $stmt->affected_rows;
                $stmt->close();

                if ($affected > 0) {
                    $cp_user_pay_block_notice = "User #{$userId} payment block updated.";
                } else {
                    $cp_user_pay_block_notice = "User #{$userId} already has these payment block values.";
                }
                $cp_user_pay_block_notice_type = 'success';
            }
        }
    }

    $_SESSION['cp_user_block_notice'] = [
        'message' => $cp_user_pay_block_notice,
        'type' => $cp_user_pay_block_notice_type
    ];
    header('Location: /control-panel/page/#cp-user-payment-block');
    exit();
}





// Subription Function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['subscribe_button']) && !empty($_POST['subscribe_button'])) {
    header("Refresh:0");
    exit();
    $subscribe_email = $_POST['subscribe_email'];
    $stmt = $dbconn->prepare("INSERT INTO newsletter_subscribers (email) VALUES (?)");
    $stmt->bind_param("s", $subscribe_email);
    $stmt->execute();
    $stmt->close();
    
    // Subscriber notification to admin email
$to = 'admin@rapidroutelogistics.uk';
$from = 'alert@rapidroutelogistics.uk';
    $fromName = 'Alert'; 
    
    $subject = 'New Subcriber'; 
    
    $htmlContent = ' 
        <html> 
        <head> 
            <title>New Subsriber | Levend Shipping</title> 
        </head> 
        <body style="border: 2px dashed #230c54; padding-left: 5px; padding-right: 5px;"> 
            <h1>You have a new subsriber to your newsletter!</h1>
            <h3 style="color: #1D1D37;">'.$subscribe_email.'</h3>
        </body> 
        </html>'; 
    
        // Set content-type header for sending HTML email 
        $headers = "MIME-Version: 1.0" . "\r\n"; 
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
        
        // Additional headers 
        $headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
        
        
        // Send email 
        if(mail($to, $subject, $htmlContent, $headers)){ 
            $mail_sent = 'Email has sent successfully.';
            $value = 1;
        }
        else{ 
            $mail_sent = 'Email sending failed.'; 
        }



    // Thanking Subscriber email
    $to2 = $subscribe_email; 
$from2 = 'alert@rapidroutelogistics.uk';
    $fromName2 = 'Alert'; 
    
    $subject2 = 'Thank You For Subsribing'; 
    
    $htmlContent2 = ' 
        <html> 
        <head> 
            <title>Thank You For Subsribing | Levend Shipping Inc.</title> 
        </head> 
        <body style="border: 2px dashed #230c54; padding-left: 5px; padding-right: 5px;"> 
            <h1>Thank You For Subsribing to our Newsletter service!</h1>
        </body> 
        </html>'; 
    
        // Set content-type header for sending HTML email 
        $headers2 = "MIME-Version: 1.0" . "\r\n"; 
        $headers2 .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
        
        // Additional headers 
        $headers2 .= 'From: '.$fromName2.'<'.$from2.'>' . "\r\n"; 
        
        // Send email 
        if(mail($to2, $subject2, $htmlContent2, $headers2)){ 
            $mail_sent = 'Email has sent successfully.';
            $value = 1;
        }
        else{ 
            $mail_sent = 'Email sending failed.'; 
        }

    header("location:?subscription_success=yes");        
}





// Change package location email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_location']) && !empty($_POST['change_location'])) {
    $tracking_number = cp_get_tracking_number_from_post();
    $new_location = $_POST['new_location'];
    if ($tracking_number !== '') {
        $updatedAt = time();
        $stmt = $dbconn->prepare("UPDATE shipments SET current_location = ?, date_updated = ? WHERE tracking_number = ?");
        $stmt->bind_param("sis", $new_location, $updatedAt, $tracking_number);
        $stmt->execute();
        $stmt->close();
    }
}





// Cancel order
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_order']) && !empty($_POST['cancel_order'])) {
    $tracking_number = cp_get_tracking_number_from_post();
    if ($tracking_number !== '') {
        $updatedAt = time();
        $stmt = $dbconn->prepare("UPDATE shipments SET status = 'cancelled', date_updated = ? WHERE tracking_number = ?");
        $stmt->bind_param("is", $updatedAt, $tracking_number);
        $stmt->execute();
        $stmt->close();
    }
}





// Create Item Function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create-item-button']) && !empty($_POST['create-item-button'])) {
    // Form data
    $create_item_id = $_POST['create-item-id'];
    $create_item_name = $_POST['create-item-name'];
    $create_item_description = $_POST['create-item-description'];
    $create_image_item_number = $_POST['create_image_item_number'];
   
    // Query
    $stmt = $dbconn->prepare("INSERT INTO items (order_id, item_name, item_description, item_number) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $create_item_id, $create_item_name, $create_item_description, $create_image_item_number);
    $stmt->execute();
    $stmt->close();   
}





// Create order function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_order']) && !empty($_POST['create_order'])) {
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $item_name = trim((string)($_POST['item_name'] ?? ''));
    $item_description = trim((string)($_POST['item_description'] ?? ''));
    $total_price = trim((string)($_POST['total_price'] ?? ''));
    $price_breakdown = trim((string)($_POST['price_breakdown'] ?? ''));
    $origin = trim((string)($_POST['origin'] ?? ''));
    $destination = trim((string)($_POST['destination'] ?? ''));
    $duration = (int)($_POST['duration'] ?? 3);
    $duration = $duration > 0 ? $duration : 3;
    $shipmentType = cp_map_shipment_type((string)($_POST['delivery_type'] ?? 'standard'));

    $now = time();
    $estimatedDelivery = $now + ($duration * 86400);
    $status = 'pending';
    $currentLocation = $origin !== '' ? $origin : 'Origin Facility';
    $completion = 0;
    $length = 1.0;
    $width = 1.0;
    $height = 1.0;
    $weight = 1.0;
    $senderPhone = null;
    $receiverName = 'Receiver';
    $receiverEmail = $email !== '' ? $email : 'receiver@example.com';
    $receiverPhone = null;
    $deliveredAt = null;
    $notes = trim("Item: {$item_name}; Description: {$item_description}; Price: {$total_price}; Breakdown: {$price_breakdown}");

    $userId = null;
    if ($email !== '') {
        $userStmt = $dbconn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($userStmt) {
            $userStmt->bind_param("s", $email);
            $userStmt->execute();
            $userRes = $userStmt->get_result();
            $userRow = $userRes ? $userRes->fetch_assoc() : null;
            if ($userRow && isset($userRow['id'])) {
                $userId = (int)$userRow['id'];
            }
            $userStmt->close();
        }
    }

    $trackingNumber = '1Z' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));

    $sql = "INSERT INTO shipments
        (tracking_number, sender_name, sender_email, sender_phone, user_id, receiver_name, receiver_email, receiver_phone, origin_address, destination_address, length, width, height, weight, shipment_type, status, current_location, completion_percentage, estimated_delivery_time, date_created, date_updated, delivered_at, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $dbconn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param(
            "ssssisssssddddsssiiiiis",
            $trackingNumber,
            $name,
            $email,
            $senderPhone,
            $userId,
            $receiverName,
            $receiverEmail,
            $receiverPhone,
            $origin,
            $destination,
            $length,
            $width,
            $height,
            $weight,
            $shipmentType,
            $status,
            $currentLocation,
            $completion,
            $estimatedDelivery,
            $now,
            $now,
            $deliveredAt,
            $notes
        );
        $stmt->execute();
        $stmt->close();
    }
}





// Send Custom Email Function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['send_mail']) && !empty($_POST['send_mail'])) {
    $email = $_POST["email"];
    $subject = $_POST["subject"];
    $content = $_POST["content"];


    // Email sending disabled in this project.
}





// Upload Item image function
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_picture']) && !empty($_POST['upload_picture'])) {
   

        $tracking_id = $_POST['tracking_id'];
        $item_number = $_POST['item_number'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        
      
        // File upload handling
        $file_name = $_FILES['image']['name'];
        $file_temp = $_FILES['image']['tmp_name'];
        $file_destination = '../assets/images/items/' . $file_name; // Set your destination path
        move_uploaded_file($file_temp, $file_destination);
        
        // Database insert
        $stmt = $dbconn->prepare("INSERT INTO items (tracking_id, item_number, name, description, image_link) VALUES ( ?, ?, ?, ?, ?)");
        $stmt->bind_param("sisss", $tracking_id, $item_number, $name, $description,  $file_destination);
        $stmt->execute();
    
}





// Create free Quote function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['free-quote-button']) && !empty($_POST['free-quote-button'])) {// Request Free Quote
    // Collect form data and sanitize
    $free_quote_name = htmlspecialchars(trim($_POST['free-quote-name']));
    $free_quote_email = filter_var($_POST['free-quote-email'], FILTER_SANITIZE_EMAIL);
    $free_quote_number = htmlspecialchars(trim($_POST['free-quote-number']));
    $free_quote_freight_method = htmlspecialchars(trim($_POST['free-quote-freight-method']));
    $free_quote_request = htmlspecialchars(trim($_POST['free-quote-request']));
    $free_quote_request_time = time();

    // Database Connection (Assuming $dbconn is MySQLi)
    $stmt = $dbconn->prepare("INSERT INTO free_quotes_requests (name, email, number, method, request, time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $free_quote_name, $free_quote_email, $free_quote_number, $free_quote_freight_method, $free_quote_request, $free_quote_request_time);
    $stmt->execute();
    $stmt->close();

    // Email sending disabled in this project.
    header("location:?request-sent=yes");        
}




// Delete shipment function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_shipment']) && !empty($_POST['delete_shipment'])) {
    $tracking_number = cp_get_tracking_number_from_post();
    if ($tracking_number !== '') {
        $stmt = $dbconn->prepare("DELETE FROM shipments WHERE tracking_number = ?");
        $stmt->bind_param("s", $tracking_number);
        $stmt->execute();
        $stmt->close();
    }
}



// Delete Quote function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_quote']) && !empty($_POST['delete_quote'])) {
    $id = $_POST['tracking_id'];
    mysqli_query($dbconn, "DELETE FROM quotes WHERE id = $id");
       
}





// Delete item function
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_item']) && !empty($_POST['delete_item'])) {

    $id = $_POST['tracking_id'];
    $item_number = $_POST['item_number'];

    mysqli_query($dbconn, "DELETE FROM items WHERE tracking_id = '$id' AND item_number = $item_number");
}
?>
