<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

date_default_timezone_set('America/New_York');

require_once __DIR__ . '/../../common-sections/globals.php';

$error = "";
$success = "";
$tokenValid = false;
$tokenRow = null;

$token = trim($_GET['token'] ?? '');
if ($token === '') {
    $token = trim($_POST['token'] ?? '');
}

if ($token !== '') {
    $checkStmt = $conn->prepare(
        "SELECT id, user_id, expires_at, used FROM password_reset_tokens WHERE token = ? LIMIT 1"
    );
    $checkStmt->bind_param("s", $token);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 1) {
        $tokenRow = $checkResult->fetch_assoc();
        $checkStmt->close();

        if ((int)$tokenRow['used'] === 1) {
            $error = "This reset link has already been used. Please request a new one.";
        } elseif ((int)$tokenRow['expires_at'] < time()) {
            $error = "This reset link has expired. Please request a new one.";
        } else {
            $tokenValid = true;
        }
    } else {
        $checkStmt->close();
        $error = "Invalid or expired reset link. Please request a new one.";
    }
} else {
    $error = "No reset token provided.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid && $tokenRow !== null) {
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($newPassword) < 8 || !preg_match('/[A-Za-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        $error = "Password must be at least 8 characters and include a letter and a number.";
        $tokenValid = true;
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
        $tokenValid = true;
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $userId = (int)$tokenRow['user_id'];

        $updStmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updStmt->bind_param("si", $hashed, $userId);

        if ($updStmt->execute()) {
            $updStmt->close();

            $markUsed = $conn->prepare("UPDATE password_reset_tokens SET used = 1 WHERE id = ?");
            $markUsed->bind_param("i", $tokenRow['id']);
            $markUsed->execute();
            $markUsed->close();

            $tokenValid = false;
            $success = "Your password has been reset successfully. You can now <a href=\"/login/\">log in</a> with your new password.";
        } else {
            $updStmt->close();
            $error = "Something went wrong. Please try again.";
        }
    }
}
?>
