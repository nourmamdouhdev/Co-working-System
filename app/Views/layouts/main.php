<?php

declare(strict_types=1);

$user = \App\Core\Auth::user();
$appName = (string) config('app.name', 'Co-working System');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? $appName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(app_url('/assets/styles.css')) ?>">
</head>
<body class="<?= $user ? 'is-authenticated' : 'is-guest' ?>">
<header class="topbar">
    <div class="container topbar-inner">
        <a href="<?= e(app_url('/')) ?>" class="brand">
            <span>Workspace Control</span>
            <strong><?= e($appName) ?></strong>
        </a>
        <?php if ($user): ?>
            <nav class="nav">
                <a href="<?= e(app_url('/checkin')) ?>">Check-In</a>
                <a href="<?= e(app_url('/checkout')) ?>">Checkout</a>
                <a href="<?= e(app_url('/visits/active')) ?>">Active Visits</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="<?= e(app_url('/admin/dashboard/daily')) ?>">Daily Dashboard</a>
                    <a href="<?= e(app_url('/admin/staff')) ?>">Staff</a>
                    <a href="<?= e(app_url('/admin/products')) ?>">Products</a>
                    <a href="<?= e(app_url('/admin/settings')) ?>">Settings</a>
                <?php endif; ?>
            </nav>
            <form method="POST" action="<?= e(app_url('/logout')) ?>" class="inline-form logout-form">
                <?= csrf_field() ?>
                <button type="submit">Logout (<?= e($user['name']) ?>)</button>
            </form>
        <?php endif; ?>
    </div>
</header>

<main class="container page">
    <?php if ($success = flash('success')): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    <?php if ($error = flash('error')): ?>
        <div class="alert alert-error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php include $viewPath; ?>
</main>
</body>
</html>
<?php clear_old_input(); ?>
