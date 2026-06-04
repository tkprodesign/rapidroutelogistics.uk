<?php
include('./app.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | Rapid Route Logistics</title>
    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/forms.css?v=<?php echo time(); ?>">
</head>
<body class="login-page">

<section class="form">
    <form method="post" action="">
        <?php if ($tokenValid): ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
        <?php endif; ?>
        <div class="container">

            <div class="heading">
                <span class="auth-logo-wrap">
                    <img src="/assets/images/branding/transparent/logo.png" alt="Rapid Route Logistics Logo" class="logo">
                </span>
                <h2>Reset Password</h2>
                <?php if ($tokenValid): ?>
                    <p>Choose a new password for your account.</p>
                <?php endif; ?>
            </div>

            <?php if (!empty($error)): ?>
                <div class="form-errors">
                    <p><?= htmlspecialchars($error) ?></p>
                    <p style="margin-top:10px;"><a href="/login/forgot-password/">Request a new reset link</a></p>
                </div>
            <?php elseif (!empty($success)): ?>
                <div class="form-success">
                    <p><?= $success ?></p>
                </div>
            <?php endif; ?>

            <?php if ($tokenValid): ?>
            <div class="content">
                <div class="input-box">
                    <input type="password" name="new_password" placeholder="New Password*" required minlength="8">
                </div>

                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirm New Password*" required minlength="8">
                </div>

                <div class="action-box">
                    <button type="submit" class="btn-primary">
                        Set New Password
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                    <p class="signup-text">
                        Remember your password? <a href="/login/">Login</a>
                    </p>
                </div>
            </div>
            <?php elseif (empty($success)): ?>
            <div class="content">
                <div class="action-box">
                    <p class="signup-text">
                        <a href="/login/forgot-password/">Request a new password reset link</a>
                    </p>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </form>
</section>

<script src="/assets/scripts/forms.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>
