<?php
// ===================================================
// Admin Login
// ===================================================
require_once __DIR__ . '/../includes/auth.php';

if (isAdmin()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (!$email || !$pass) {
        $error = 'অনুগ্রহ করে ইমেইল ও পাসওয়ার্ড দিন।';
    } elseif (adminLogin($email, $pass)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'ইমেইল বা পাসওয়ার্ড ভুল।';
    }
}

$settings = getAllSettings();
?>
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন | <?= htmlspecialchars($settings['site_title'] ?? 'বাংলা শপ') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-page">
    <div class="login-bg"></div>
    <div class="login-card">
        <div class="login-logo">
            <div class="login-logo-icon">🛍️</div>
            <h1>কন্ট্রোল প্যানেল লগইন</h1>
            <p>অ্যাডমিন হিসেবে লগইন করুন</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert-error"><i class="fa fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group mb-4">
                <label class="form-label">ইমেইল বা ইউজারনেম</label>
                <input type="text" name="email" class="form-control" placeholder="admin@banglashop.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <div class="form-group mb-6">
                <label class="form-label">পাসওয়ার্ড</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="login-btn">
                <i class="fa fa-sign-in-alt"></i> লগইন করুন
            </button>
        </form>
        <div style="text-align:center;margin-top:24px">
            <a href="/" class="btn-admin-outline btn-admin-sm"><i class="fa fa-arrow-left"></i> ওয়েবসাইটে ফিরে যান</a>
        </div>
    </div>
</body>
</html>
