<?php
// =============================================
// Admin Layout Header & Sidebar
// =============================================
require_once __DIR__ . '/auth.php';
requireAdmin();
$settings = getAllSettings();
$siteName = $settings['site_title'] ?? 'বাংলা শপ';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>কন্ট্রোল প্যানেল | <?= htmlspecialchars($siteName) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-brand">
                <a href="/admin/dashboard.php" class="sidebar-logo">
                    <div class="sidebar-logo-icon">🛍️</div>
                    <div>
                        <div class="sidebar-logo-text"><?= htmlspecialchars(mb_substr($siteName, 0, 15)) ?></div>
                        <div class="sidebar-logo-badge">কন্ট্রোল প্যানেল</div>
                    </div>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="sidebar-section-label">ওভারভিউ</div>
                <a href="/admin/dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                    <i class="fa fa-chart-pie"></i> ড্যাশবোর্ড
                </a>
                
                <div class="sidebar-section-label">ম্যানেজমেন্ট</div>
                <a href="/admin/orders.php" class="sidebar-link <?= in_array($currentPage, ['orders', 'order-detail']) ? 'active' : '' ?>">
                    <i class="fa fa-shopping-cart"></i> অর্ডারসমূহ
                    <?php
                        $pending = getDB()->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
                        if ($pending > 0) echo '<span class="badge-count">'.$pending.'</span>';
                    ?>
                </a>
                <a href="/admin/products.php" class="sidebar-link <?= in_array($currentPage, ['products', 'product-form']) ? 'active' : '' ?>">
                    <i class="fa fa-box"></i> প্রোডাক্ট লিস্ট
                </a>
                <a href="/admin/categories.php" class="sidebar-link <?= $currentPage === 'categories' ? 'active' : '' ?>">
                    <i class="fa fa-tags"></i> ক্যাটাগরি
                </a>
                
                <div class="sidebar-section-label">সেটিংস</div>
                <a href="/admin/shipping.php" class="sidebar-link <?= $currentPage === 'shipping' ? 'active' : '' ?>">
                    <i class="fa fa-truck"></i> শিপিং ও জোন
                </a>
                <?php if (adminRole() === 'admin'): ?>
                <a href="/admin/users.php" class="sidebar-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                    <i class="fa fa-users"></i> স্টাফ ও ইউজার
                </a>
                <a href="/admin/tracking-settings.php" class="sidebar-link <?= $currentPage === 'tracking-settings' ? 'active' : '' ?>">
                    <i class="fa fa-chart-line"></i> ট্র্যাকিং (Pixel/GTM)
                </a>
                <a href="/admin/settings.php" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
                    <i class="fa fa-cog"></i> সাইট সেটিংস
                </a>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-avatar"><?= mb_substr(adminName(), 0, 1) ?></div>
                    <div class="sidebar-user-info">
                        <div class="name"><?= htmlspecialchars(adminName()) ?></div>
                        <div class="role"><?= adminRole() === 'admin' ? 'অ্যাডমিনিস্ট্রেটর' : 'ম্যানেজার' ?></div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Topbar -->
            <header class="admin-topbar no-print">
                <button class="topbar-btn" id="adminMenuBtn" style="display:none"><i class="fa fa-bars"></i></button>
                <div class="topbar-title"></div>
                <div class="topbar-actions">
                    <a href="/" target="_blank" class="store-link"><i class="fa fa-external-link-alt"></i> স্টোর ভিজিট</a>
                    <a href="?logout=1" class="logout-link"><i class="fa fa-sign-out-alt"></i> লগআউট</a>
                </div>
            </header>

            <div class="admin-content">
                <!-- Content goes here -->
<?php
if (isset($_GET['logout'])) {
    adminLogout();
}
?>
