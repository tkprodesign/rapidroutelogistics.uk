<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('America/New_York');

require_once __DIR__ . '/../../common-sections/globals.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = trim($_POST['email'] ?? "");

    if ($login_input === "") {
        $error = "Please enter your email or username.";
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS password_reset_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL,
            expires_at INT NOT NULL,
            used TINYINT(1) NOT NULL DEFAULT 0,
            created_at INT NOT NULL,
            INDEX idx_token (token),
            INDEX idx_user (user_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $stmt = $conn->prepare(
            "SELECT id, email, username FROM users WHERE email = ? OR username = ? LIMIT 1"
        );
        $stmt->bind_param("ss", $login_input, $login_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $error = "No account found with that email or username.";
        } else {
            $user = $result->fetch_assoc();
            $stmt->close();

            $token = bin2hex(random_bytes(32));
            $expiresAt = time() + 3600;
            $now = time();
            $userId = (int)$user['id'];

            $delStmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ? AND used = 0");
            $delStmt->bind_param("i", $userId);
            $delStmt->execute();
            $delStmt->close();

            $insStmt = $conn->prepare(
                "INSERT INTO password_reset_tokens (user_id, token, expires_at, used, created_at) VALUES (?, ?, ?, 0, ?)"
            );
            $insStmt->bind_param("isii", $userId, $token, $expiresAt, $now);
            $insStmt->execute();
            $insStmt->close();

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'rapidroutelogistics.uk';
            $resetLink = $scheme . '://' . $host . '/login/reset-password/?token=' . urlencode($token);

            $bodyHtml = '
                <p style="font-size:1rem;color:#334;margin-bottom:18px;">
                    We received a request to reset the password for your Rapid Route Logistics account.
                </p>
                <p style="font-size:1rem;color:#334;margin-bottom:24px;">
                    Click the button below to choose a new password. This link expires in <strong>1 hour</strong>.
                </p>
                <div style="text-align:center;margin:28px 0;">
                    <a href="' . htmlspecialchars($resetLink) . '"
                       style="display:inline-block;background:#1A9B82;color:#fff;padding:14px 32px;border-radius:999px;font-weight:700;font-size:1rem;text-decoration:none;">
                        Reset My Password
                    </a>
                </div>
                <p style="font-size:0.9rem;color:#667;margin-top:24px;">
                    Or copy this link into your browser:<br>
                    <a href="' . htmlspecialchars($resetLink) . '" style="color:#1A9B82;word-break:break-all;">' . htmlspecialchars($resetLink) . '</a>
                </p>
                <p style="font-size:0.85rem;color:#999;margin-top:20px;">
                    If you did not request a password reset, you can safely ignore this email.
                </p>
            ';

            $htmlEmail = rrl_email_brand_shell(
                'Reset Your Password',
                'Reset your Rapid Route Logistics account password.',
                $bodyHtml
            );

            $emailResult = rrl_send_resend_email(
                [$user['email']],
                'Reset Your Password — Rapid Route Logistics',
                $htmlEmail
            );

            if ($emailResult['ok']) {
                $success = "Password reset instructions have been sent to <strong>" . htmlspecialchars($user['email']) . "</strong>. Please check your inbox (and spam folder).";
            } else {
                error_log('Password reset email failed: ' . ($emailResult['error'] ?? 'unknown'));
                $success = "Password reset instructions have been sent if that account exists. Please check your inbox.";
            }
        }

    }
}
?>
