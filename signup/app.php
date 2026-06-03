<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Set company timezone
date_default_timezone_set('America/New_York');

require_once __DIR__ . '/../common-sections/globals.php';

// Load email secrets
$signupEmailConfig = [];
$signupEmailConfigPath = __DIR__ . '/../common-sections/email-secrets.php';
if (file_exists($signupEmailConfigPath)) {
    $loadedSignupEmailConfig = include $signupEmailConfigPath;
    if (is_array($loadedSignupEmailConfig)) {
        $signupEmailConfig = $loadedSignupEmailConfig;
    }
}

/* -------------------------
   TURNSTILE VERIFY
-------------------------- */
function signup_verify_turnstile(string $token, string $remoteIp): bool {
    // Bypass Turnstile on dev / Replit preview environments
    $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
    if (
        str_contains($host, 'replit.dev') ||
        str_contains($host, 'replit.app') ||
        str_contains($host, 'localhost') ||
        str_contains($host, '127.0.0.1') ||
        $host === ''
    ) {
        return true;
    }

    $secretKey = '0x4AAAAAACwnvIudy3lvL60Re4JVpWPk5Ks';

    if ($token === '') return false;

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'secret' => $secretKey,
        'response' => $token,
        'remoteip' => $remoteIp,
    ]));

    $response = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        return false;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) && !empty($decoded['success']);
}

/* -------------------------
   SECRET RESOLVER
-------------------------- */
function signup_resolve_secret(string $name): string {
    global $signupEmailConfig;

    if ($name === '') return '';

    if (isset($signupEmailConfig[$name]) && trim($signupEmailConfig[$name]) !== '') {
        return trim($signupEmailConfig[$name]);
    }

    return rrl_env([$name], '');
}

/* -------------------------
   SEND EMAIL (RESEND API)
-------------------------- */
function signup_send_verification_email(string $toEmail, string $recipientName, int $verificationCode): bool {

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $apiKey = signup_resolve_secret('RESEND_API_KEY');
    if ($apiKey === '') {
        error_log('signup: missing RESEND_API_KEY');
        return false;
    }

    $fromEmail = signup_resolve_secret('NOREPLY_FROM_EMAIL');
    if ($fromEmail === '') {
        $fromEmail = 'noreply@rapidroutelogistics.uk';
    }

    $safeCode = htmlspecialchars((string)$verificationCode, ENT_QUOTES, 'UTF-8');
    $displayName = $recipientName !== '' ? $recipientName : 'Customer';

    $body = rrl_email_h1('Verify your account')
        . rrl_email_p('Hi ' . $displayName . ', thanks for signing up. Use the verification code below to activate your Rapid Route Logistics account.')
        . rrl_email_code_panel($safeCode, 'This code expires in 15 minutes.')
        . rrl_email_p('If you did not request this signup, you can safely ignore this message or contact support.');

    $html = rrl_email_brand_shell(
        'Signup Verification Code',
        'Your Rapid Route Logistics verification code is inside.',
        $body
    );

    $data = [
        "from" => "Rapid Route Logistics <{$fromEmail}>",
        "to" => [$toEmail],
        "subject" => "Your verification code",
        "html" => $html
    ];

    $ch = curl_init("https://api.resend.com/emails");

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$apiKey}",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($response === false || $httpCode < 200 || $httpCode >= 300) {
        error_log("signup: Resend failed HTTP {$httpCode} | " . (string)$response);
        return false;
    }

    return true;
}

/* -------------------------
   MAIN LOGIC
-------------------------- */

$errors = [];

$alreadySignedIn = !empty($_SESSION['user_id']) || !empty($_COOKIE['user_email']);
if ($alreadySignedIn) {
    header("Location: /dashboard/");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name         = trim($_POST["name"] ?? "");
    $email        = trim($_POST["email"] ?? "");
    $country_code = $_POST["country_code"] ?? null;
    $phone_number = !empty($_POST["phone_number"]) ? trim($_POST["phone_number"]) : null;
    $username     = trim($_POST["username"] ?? "");
    $password     = $_POST["password"] ?? "";
    $terms        = isset($_POST["accept_terms"]);
    $turnstileToken = trim($_POST["cf-turnstile-response"] ?? "");
    $remoteIp = $_SERVER['REMOTE_ADDR'] ?? '';

    // VALIDATION
    if ($name === "") $errors[] = "Name is required.";
    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if ($username === "") $errors[] = "Username is required.";
    if ($password === "" || strlen($password) < 8 || !preg_match("/[A-Za-z]/", $password) || !preg_match("/[0-9]/", $password)) $errors[] = "Password must be at least 8 characters and include a letter and a number.";
    if (!$terms) $errors[] = "You must accept the terms.";
    if ($phone_number !== null && !preg_match("/^[0-9]+$/", $phone_number)) $errors[] = "Phone must be digits only.";

    if (!signup_verify_turnstile($turnstileToken, $remoteIp)) {
        $errors[] = "Turnstile verification failed.";
    }

    // DUPLICATE CHECK
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors[] = "Email exists.";
        $stmt->close();

        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors[] = "Username exists.";
        $stmt->close();
    }

    // INSERT
    if (empty($errors)) {

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $created = time();

        $stmt = $conn->prepare("INSERT INTO users (name,email,country_code,phone_number,username,password,created_at) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssi", $name,$email,$country_code,$phone_number,$username,$hashed,$created);

        if ($stmt->execute()) {

            $code = random_int(100000, 999999);
            $now = time();

            $v = $conn->prepare("INSERT INTO verification_code (email,code,date_created) VALUES (?,?,?)");
            $v->bind_param("sii",$email,$code,$now);
            $v->execute();
            $v->close();

            if (!signup_send_verification_email($email,$name,$code)) {
                $conn->query("DELETE FROM users WHERE email='{$email}'");
                $conn->query("DELETE FROM verification_code WHERE email='{$email}'");
                $errors[] = "Email failed to send.";
            } else {
                header("Location: /emailVerificationAndLogin/?email=" . urlencode($email));
                exit();
            }

        } else {
            $errors[] = "Registration failed.";
        }

        $stmt->close();
    }
}

$conn->close();
?>
