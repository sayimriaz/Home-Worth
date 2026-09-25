<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

if (!empty($_SESSION['admin_ok'])) {
    header('Location: ' . hw_admin_url('dashboard'));
    exit;
}

$needsSetup = !hw_admin_exists();
$error = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!hw_verify_csrf($_POST['csrf'] ?? null) || !hw_same_origin() || hw_honeypot_tripped()) {
        $error = 'Please try again.';
    } elseif (!$needsSetup && ($lock = hw_login_blocked())) {
        $error = $lock;
    } else {
        $username = hw_clean_text((string) ($_POST['username'] ?? ''), 40);
        $password = (string) ($_POST['password'] ?? '');

        if ($needsSetup) {
            $confirm = (string) ($_POST['confirm'] ?? '');
            if (!preg_match('/^[A-Za-z0-9._-]{3,40}$/', $username)) {
                $error = 'Choose a username with 3–40 letters, numbers, dots, underscores, or hyphens.';
            } elseif (strlen($password) < HW_PASSWORD_MIN || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
                $error = 'Password must be at least 12 characters and include letters and numbers.';
            } elseif (!hash_equals($password, $confirm)) {
                $error = 'Passwords do not match.';
            } elseif (!hw_create_admin($username, $password)) {
                $error = 'Could not create the admin account. Please try again.';
            } else {
                session_regenerate_id(true);
                $_SESSION['admin_ok'] = true;
                $_SESSION['admin_user'] = $username;
                hw_rotate_csrf();
                header('Location: ' . hw_admin_url('dashboard'));
                exit;
            }
        } else {
            usleep(random_int(150000, 350000));
            if (hw_verify_admin($username, $password)) {
                hw_login_success();
                session_regenerate_id(true);
                $_SESSION['admin_ok'] = true;
                $_SESSION['admin_user'] = $username;
                hw_rotate_csrf();
                header('Location: ' . hw_admin_url('dashboard'));
                exit;
            }
            $error = hw_login_fail() ?? 'Invalid username or password.';
        }
    }
}

$pageTitle = $needsSetup ? 'Create Admin – Home Worth' : 'Admin Login – Home Worth';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <meta name="googlebot" content="noindex, nofollow, noarchive">
    <meta name="theme-color" content="#151D31">
    <title><?php echo hw_e($pageTitle); ?></title>
    <link rel="icon" href="<?php echo hw_e(hw_url('assets/images/favicon.png')); ?>" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo hw_e(hw_url('assets/css/style.css')); ?>">
</head>
<body class="admin-auth">
    <div class="admin-auth-shell">
        <aside class="admin-auth-brand">
            <span class="brand-mark"><img src="<?php echo hw_e(hw_url('assets/images/logo.png')); ?>" alt=""></span>
            <p class="admin-auth-kicker">Home Worth</p>
            <h2><?php echo $needsSetup ? 'Create admin account' : 'Admin login'; ?></h2>
            <p><?php echo $needsSetup ? 'This form appears only until the first admin account is created.' : 'Sign in to view property requests.'; ?></p>
        </aside>
        <main class="admin-box">
            <h1><?php echo $needsSetup ? 'Create account' : 'Sign in'; ?></h1>
            <?php if ($needsSetup): ?>
                <p class="admin-box-note">Password must be at least 12 characters and include letters and numbers.</p>
            <?php endif; ?>
            <?php if ($error): ?>
                <p class="form-alert" role="alert"><?php echo hw_e($error); ?></p>
            <?php endif; ?>
            <form method="post" autocomplete="off">
                <?php echo hw_csrf_field(); ?>
                <div class="hp" aria-hidden="true">
                    <label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>
                <label class="field">
                    <span>Username</span>
                    <input type="text" name="username" placeholder="Username" maxlength="40" required>
                </label>
                <label class="field">
                    <span>Password</span>
                    <input type="password" name="password" placeholder="Password" required>
                </label>
                <?php if ($needsSetup): ?>
                    <label class="field">
                        <span>Confirm password</span>
                        <input type="password" name="confirm" placeholder="Confirm password" required>
                    </label>
                <?php endif; ?>
                <button class="btn btn-gold full" type="submit"><?php echo $needsSetup ? 'Create account' : 'Sign in'; ?></button>
            </form>
        </main>
    </div>
</body>
</html>
