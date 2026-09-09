<?php
// =============================================
// Frontend Header Template
// =============================================
require_once __DIR__ . '/functions.php';
$settings = getAllSettings();
$siteName = $settings['site_title'] ?? 'বাংলা শপ';
$logoUrl  = $settings['logo_url'] ?? '';
$primaryColor = $settings['primary_color'] ?? '#6C63FF';
$accentColor  = $settings['accent_color'] ?? '#FF6584';
$categories   = getCategories();
$currentPage  = basename($_SERVER['PHP_SELF'], '.php');

// Pixel & GTM
$pixelEnabled  = ($settings['pixel_enabled'] ?? '0') === '1';
$gtmEnabled    = ($settings['gtm_enabled'] ?? '0') === '1';
$metaPixelId   = $settings['meta_pixel_id'] ?? '';
$gtmId         = $settings['gtm_container_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc ?? ($settings['tagline'] ?? 'বাংলাদেশের সেরা অনলাইন শপ')) ?>">
    <link rel="icon" href="<?= $settings['favicon_url'] ? '/' . $settings['favicon_url'] : '/assets/img/favicon.ico' ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <style>
        :root {
            --primary: <?= htmlspecialchars($primaryColor) ?>;
            --accent: <?= htmlspecialchars($accentColor) ?>;
            --primary-dark: <?= adjustBrightness($primaryColor, -20) ?>;
            --primary-light: <?= adjustBrightness($primaryColor, 20) ?>;
            --accent-dark: <?= adjustBrightness($accentColor, -20) ?>;
        }
    </style>
    <?php if ($gtmEnabled && $gtmId): ?>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','<?= htmlspecialchars($gtmId) ?>');</script>
    <!-- End Google Tag Manager -->
    <?php endif; ?>
    <?php if ($pixelEnabled && $metaPixelId): ?>
    <!-- Meta Pixel Code -->
    <script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '<?= htmlspecialchars($metaPixelId) ?>');
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id=<?= htmlspecialchars($metaPixelId) ?>&ev=PageView&noscript=1"/></noscript>
    <!-- End Meta Pixel Code -->
    <?php endif; ?>
    <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body data-page="<?= $currentPage ?>"
      data-product-id="<?= $productId ?? '' ?>"
      data-product-name="<?= htmlspecialchars($productName ?? '') ?>"
      data-product-price="<?= $productPrice ?? '' ?>">
<?php if ($gtmEnabled && $gtmId): ?>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= htmlspecialchars($gtmId) ?>"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<?php endif; ?>

<header class="site-header">
    <div class="header-inner">
        <!-- Logo -->
        <a href="/" class="site-logo">
            <?php if ($logoUrl): ?>
                <img src="/<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($siteName) ?>" class="logo-img">
            <?php else: ?>
                <div class="logo-emoji">🛍️</div>
            <?php endif; ?>
            <div class="logo-text"><?= htmlspecialchars($siteName) ?></div>
        </a>

        <!-- Search Bar -->
        <div class="search-wrap">
            <input type="text" id="searchInput" class="search-input" placeholder="🔍 পণ্য খুঁজুন..." autocomplete="off">
            <button class="search-btn" onclick="window.location='/shop.php?search='+encodeURIComponent(document.getElementById('searchInput').value)">
                <i class="fa fa-search"></i>
            </button>
            <div class="search-results" id="searchResults"></div>
        </div>

        <!-- Nav (Desktop) -->
        <nav class="main-nav" id="mainNav">
            <a href="/" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
                <i class="fa fa-home"></i> হোম
            </a>
            <a href="/shop.php" class="nav-link <?= $currentPage === 'shop' ? 'active' : '' ?>">
                <i class="fa fa-store"></i> শপ
            </a>
            <a href="/track.php" class="nav-link <?= $currentPage === 'track' ? 'active' : '' ?>">
                <i class="fa fa-map-marker-alt"></i> ট্র্যাক
            </a>
            <a href="/contact.php" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>">
                <i class="fa fa-phone"></i> যোগাযোগ
            </a>
            <a href="/faq.php" class="nav-link <?= $currentPage === 'faq' ? 'active' : '' ?>">
                <i class="fa fa-question-circle"></i> FAQ
            </a>
        </nav>

        <!-- Cart Button -->
        <button class="cart-btn" id="cartBtn" aria-label="কার্ট">
            <i class="fa fa-shopping-cart"></i>
            <span class="cart-badge cart-count" id="cartBadge" style="display:none">0</span>
        </button>

        <!-- Mobile Menu -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="মেনু">
            <i class="fa fa-bars"></i>
        </button>
    </div>
</header>

<!-- Cart Drawer -->
<div class="cart-overlay" id="cartOverlay"></div>
<div class="cart-drawer" id="cartDrawer">
    <div class="cart-drawer-header">
        <h3>🛒 আমার কার্ট <span class="cart-count" style="font-size:0.85rem;color:var(--text3)">(0 পণ্য)</span></h3>
        <button class="cart-close-btn" id="cartCloseBtn"><i class="fa fa-times"></i></button>
    </div>
    <div class="cart-items" id="cartItemsContainer">
        <div class="cart-empty" id="cartEmpty">
            <i class="fa fa-shopping-cart"></i>
            <p>কার্ট খালি আছে</p>
            <a href="/shop.php" class="btn btn-primary btn-sm" style="margin-top:12px">কেনাকাটা শুরু করুন</a>
        </div>
    </div>
    <div class="cart-drawer-footer">
        <div class="coupon-wrap">
            <input type="text" id="couponInput" class="coupon-input" placeholder="কুপন কোড লিখুন...">
            <button class="coupon-btn" id="couponApplyBtn">প্রয়োগ</button>
        </div>
        <div class="cart-summary">
            <div class="cart-summary-row">
                <span>সাবটোটাল</span>
                <span id="cartSubtotal">৳০</span>
            </div>
            <div class="cart-summary-row">
                <span>ডিসকাউন্ট</span>
                <span id="cartDiscount">—</span>
            </div>
            <div class="cart-summary-row total">
                <span>মোট</span>
                <span id="cartTotal">৳০</span>
            </div>
        </div>
        <button class="btn-checkout" onclick="window.location='/checkout.php'">
            <i class="fa fa-lock"></i> চেকআউট করুন
        </button>
    </div>
</div>

<?php
function adjustBrightness(string $hex, int $amount): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) !== 6) return '#' . $hex;
    list($r, $g, $b) = array_map('hexdec', str_split($hex, 2));
    $r = max(0, min(255, $r + $amount));
    $g = max(0, min(255, $g + $amount));
    $b = max(0, min(255, $b + $amount));
    return sprintf('#%02x%02x%02x', $r, $g, $b);
}
?>
