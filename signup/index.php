<?php include('app.php');?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Rapid Route Logistics</title>
    
    <link rel="stylesheet" href="/assets/stylesheets/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/forms.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/ts/main.css?v=<?php echo time(); ?>" media="screen and (max-width: 1120px)">
    <link rel="stylesheet" href="/assets/stylesheets/ts/home.css?v=<?php echo time(); ?>" media="screen and (max-width: 1120px)">
    <link rel="stylesheet" href="/assets/stylesheets/ms/main.css?v=<?php echo time(); ?>" media="screen and (max-width: 760px)">
    <link rel="stylesheet" href="/assets/stylesheets/ms/home.css?v=<?php echo time(); ?>" media="screen and (max-width: 760px)">

    <link rel="shortcut icon" href="/assets/images/branding/mark-only.png?v=<?php echo time(); ?>" type="image/png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    
    <?php
    $signupDevMode = (function() {
        $h = strtolower($_SERVER['HTTP_HOST'] ?? '');
        return str_contains($h,'replit.dev')||str_contains($h,'replit.app')||str_contains($h,'localhost')||str_contains($h,'127.0.0.1')||$h==='';
    })();
    if (!$signupDevMode): ?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php endif; ?>
</head>

<body>
<?php include("../common-sections/header.html"); ?>

<section class="form">
    <form action="" method="post" data-signup-form>
        <div class="container">
            <div class="heading">
                <h2>Sign Up</h2>
                <p>Already have a profile? <a href="/login/">Log In</a></p>
            </div>

            <div class="content">
                <?php if (!empty($errors)): ?>
                    <div class="form-errors" role="alert" aria-live="polite">
                        <?php foreach ($errors as $error): ?>
                            <p><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="input-box">
                    <input type="text" name="name" placeholder="First and Last Name">
                </div>

                <div class="input-box">
                    <input type="email" name="email" placeholder="Email">
                </div>

                <div class="input-group">
                    <div class="input-box country-code">
                        <select name="country_code"></select>
                    </div>
                    <div class="input-box phone-number">
                        <input type="tel" name="phone_number" placeholder="Phone Number" inputmode="numeric" pattern="[0-9]*">
                    </div>
                </div>

                <div class="input-box">
                    <input type="text" name="username" placeholder="Username">
                </div>

                <div class="input-box password-field">
                    <input type="password" name="password" placeholder="Password" autocomplete="new-password" aria-describedby="signup-password-help">
                    <div class="password-requirements" id="signup-password-help" aria-live="polite">
                        <p>Password must include:</p>
                        <ul>
                            <li data-password-rule="length">At least 8 characters</li>
                            <li data-password-rule="letter">At least one letter</li>
                            <li data-password-rule="number">At least one number</li>
                        </ul>
                    </div>
                </div>

                <div class="input-box checkbox">
                    <input type="checkbox" name="accept_terms" required>
                    <p>
                        I agree to the
                        <a href="/legal/terms-and-conditions/">Rapid Route Logistics Terms and Conditions of Service</a>
                        and the
                        <a href="/legal/website-terms-of-use/">Rapid Route Logistics Website Terms of Use</a>,
                        which contain important terms about my shipping activity and my use of Rapid Route Logistics services,
                        including limitations of liability and how disputes will be handled.
                    </p>
                </div>

                <?php if (!$signupDevMode): ?>
                <div class="input-box">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAACwnvMl9sbRLv3K2"></div>
                </div>
                <?php endif; ?>

                <div class="input-box">
                    <button type="submit">
                        Sign Up
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>

<?php include("../common-sections/footer.html"); ?>

<script src="/assets/scripts/forms.js?v=<?php echo time(); ?>" defer></script>
<!-- <script src="/assets/scripts/user.js?v=<?php echo time(); ?>"></script> -->
</body>
</html>