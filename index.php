<?php
// =============================================
// Home Page — হোম পেজ
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$pageTitle = ($settings['site_title'] ?? 'বাংলা শপ') . ' — ' . ($settings['tagline'] ?? 'সারা দেশে ক্যাশ অন ডেলিভারি');
$pageDesc  = $settings['tagline'] ?? 'বাংলাদেশের সেরা অনলাইন শপ। সারা দেশে ক্যাশ অন ডেলিভারি।';
$categories = getCategories();
$featuredProducts = getProducts(['is_featured' => 1], 8);
$allProducts = getProducts([], 8);
include __DIR__ . '/includes/header.php';
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-particles"></div>
    <div class="hero-content">
        <div class="hero-text">
            <div class="hero-badge">
                <i class="fa fa-fire" style="color:var(--accent)"></i>
                🔥 বিশেষ অফার চলছে
            </div>
            <h1 class="hero-title">
                <?= nl2br(htmlspecialchars($settings['hero_title'] ?? "বাংলাদেশের সেরা\nঅনলাইন শপিং")) ?>
            </h1>
            <p class="hero-desc">
                <?= htmlspecialchars($settings['hero_subtitle'] ?? 'সারা দেশে ক্যাশ অন ডেলিভারি • দ্রুত ডেলিভারি • ১০০% অরিজিনাল প্রোডাক্ট') ?>
            </p>
            <div class="hero-actions">
                <a href="/shop.php" class="btn btn-primary btn-lg">
                    <i class="fa fa-shopping-bag"></i> এখনই কিনুন
                </a>
                <a href="/shop.php?sort=popular" class="btn btn-outline btn-lg">
                    <i class="fa fa-tag"></i> অফার দেখুন
                </a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat">
                    <div class="num">১০,০০০+</div>
                    <div class="label">সন্তুষ্ট গ্রাহক</div>
                </div>
                <div class="hero-stat">
                    <div class="num">৫০০+</div>
                    <div class="label">পণ্য</div>
                </div>
                <div class="hero-stat">
                    <div class="num">৯৯%</div>
                    <div class="label">ডেলিভারি সাকসেস</div>
                </div>
            </div>
        </div>
        <div class="hero-image-wrap">
            <div class="hero-image-glow"></div>
            <div style="width:100%;max-width:400px;background:var(--card);border-radius:var(--radius-lg);overflow:hidden;border:1px solid var(--border2);box-shadow:var(--shadow)">
                <div style="background:linear-gradient(135deg,rgba(108,99,255,0.3),rgba(255,101,132,0.2));padding:40px;text-align:center">
                    <div style="font-size:6rem">🛍️</div>
                    <div style="font-size:1.2rem;font-weight:700;color:var(--white);margin-top:16px">প্রিমিয়াম শপিং</div>
                    <div style="font-size:0.875rem;color:var(--text3);margin-top:6px">সেরা মানের পণ্য সেরা দামে</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TRUST BADGES -->
<section class="trust-section">
    <div class="container">
        <div class="trust-grid">
            <div class="trust-item" data-aos>
                <div class="trust-icon">🚚</div>
                <div>
                    <h4>সারা দেশে ডেলিভারি</h4>
                    <p>ক্যাশ অন ডেলিভারি সুবিধা</p>
                </div>
            </div>
            <div class="trust-item" data-aos>
                <div class="trust-icon">⚡</div>
                <div>
                    <h4>দ্রুত ডেলিভারি</h4>
                    <p>ঢাকায় ২৪ ঘণ্টায়</p>
                </div>
            </div>
            <div class="trust-item" data-aos>
                <div class="trust-icon">✅</div>
                <div>
                    <h4>১০০% অরিজিনাল</h4>
                    <p>গ্যারান্টিযুক্ত পণ্য</p>
                </div>
            </div>
            <div class="trust-item" data-aos>
                <div class="trust-icon">🔒</div>
                <div>
                    <h4>নিরাপদ পেমেন্ট</h4>
                    <p>বিকাশ, নগদ, রকেট</p>
                </div>
            </div>
            <div class="trust-item" data-aos>
                <div class="trust-icon">📞</div>
                <div>
                    <h4>২৪/৭ সাপোর্ট</h4>
                    <p>সর্বদা আপনার পাশে</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES -->
<?php if (!empty($categories)): ?>
<section class="section">
    <div class="container">
        <div class="section-head">
            <h2 class="section-title">ক্যাটাগরি</h2>
            <a href="/shop.php" class="see-all">সব দেখুন <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
            <a href="/shop.php?category=<?= $cat['id'] ?>" class="category-card" data-aos>
                <div class="category-icon">
                    <?php if ($cat['image_url']): ?>
                        <img src="/<?= htmlspecialchars($cat['image_url']) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" class="category-img">
                    <?php else: ?>
                        <?= htmlspecialchars($cat['icon_url'] ?: '📦') ?>
                    <?php endif; ?>
                </div>
                <div class="category-name"><?= htmlspecialchars($cat['name']) ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FLASH SALE -->
<section class="section" style="background:var(--bg2);padding:60px 0">
    <div class="container">
        <div class="flash-sale-head" data-aos>
            <h2 class="section-title" style="margin-bottom:0">🔥 হট ডিল</h2>
            <div class="timer-wrap">
                <span style="font-size:0.85rem;color:var(--text3);margin-right:4px">শেষ হবে:</span>
                <div class="timer-block">
                    <div class="num timer-h">০৬</div>
                    <div class="lbl">ঘণ্টা</div>
                </div>
                <span class="timer-sep">:</span>
                <div class="timer-block">
                    <div class="num timer-m">০০</div>
                    <div class="lbl">মিনিট</div>
                </div>
                <span class="timer-sep">:</span>
                <div class="timer-block">
                    <div class="num timer-s">০০</div>
                    <div class="lbl">সেকেন্ড</div>
                </div>
            </div>
            <a href="/shop.php?sort=popular" class="see-all">সব দেখুন <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php if (empty($featuredProducts)): ?>
            <p class="text-center text-muted">এখনো কোনো পণ্য যোগ করা হয়নি।</p>
        <?php else: ?>
        <div class="products-grid" id="flashTimer">
            <?php foreach ($featuredProducts as $p): renderProductCard($p); endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- POPULAR PRODUCTS -->
<section class="section">
    <div class="container">
        <div class="section-head" data-aos>
            <h2 class="section-title">জনপ্রিয় পণ্য</h2>
            <a href="/shop.php" class="see-all">সব পণ্য <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php if (empty($allProducts)): ?>
            <p class="text-center text-muted">এখনো কোনো পণ্য যোগ করা হয়নি।</p>
        <?php else: ?>
        <div class="products-grid">
            <?php foreach ($allProducts as $p): renderProductCard($p); endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- WHY US -->
<section class="section" style="background:var(--bg2)">
    <div class="container">
        <div class="section-head" data-aos>
            <h2 class="section-title">কেন আমাদের বেছে নেবেন?</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:24px">
            <?php
            $reasons = [
                ['icon'=>'🏆','title'=>'সেরা মানের পণ্য','desc'=>'প্রতিটি পণ্য কঠোরভাবে মান নিয়ন্ত্রণ করা হয়।'],
                ['icon'=>'💰','title'=>'সেরা দাম','desc'=>'বাজারের সেরা দামে পণ্য পাবেন আমাদের কাছে।'],
                ['icon'=>'🔄','title'=>'সহজ রিটার্ন','desc'=>'৭ দিনের মধ্যে সহজে রিটার্ন করার সুবিধা।'],
                ['icon'=>'💬','title'=>'বাংলা কাস্টমার সাপোর্ট','desc'=>'বাংলায় কথা বলুন, আমরা সবসময় প্রস্তুত।'],
            ];
            foreach ($reasons as $r): ?>
            <div class="category-card" data-aos style="text-align:left;padding:24px;display:flex;gap:16px;align-items:flex-start">
                <div style="font-size:2rem;flex-shrink:0"><?= $r['icon'] ?></div>
                <div>
                    <h3 style="font-size:1rem;font-weight:700;color:var(--white);margin-bottom:6px"><?= $r['title'] ?></h3>
                    <p style="font-size:0.85rem;color:var(--text3)"><?= $r['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
function renderProductCard(array $p): void {
    $img = getFirstImage($p);
    $discount = ($p['old_price'] > 0 && $p['old_price'] > $p['selling_price'])
        ? round((1 - $p['selling_price'] / $p['old_price']) * 100) : 0;
    $inStock = (int)$p['stock_qty'] > 0;
    $currency = getSetting('currency_symbol', '৳');
    ?>
    <div class="product-card" data-aos>
        <div class="product-card-img">
            <a href="/product.php?id=<?= $p['id'] ?>">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
            </a>
            <?php if ($discount > 0): ?>
                <span class="discount-badge">-<?= $discount ?>%</span>
            <?php endif; ?>
            <?php if ($p['is_featured']): ?>
                <span class="featured-badge">⭐ ফিচার্ড</span>
            <?php endif; ?>
            <?php if (!$inStock): ?>
                <div class="out-of-stock-overlay">স্টক শেষ</div>
            <?php endif; ?>
        </div>
        <div class="product-card-body">
            <?php if (!empty($p['category_name'])): ?>
                <div class="product-cat"><?= htmlspecialchars($p['category_name']) ?></div>
            <?php endif; ?>
            <a href="/product.php?id=<?= $p['id'] ?>" class="product-name" style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;color:var(--text);text-decoration:none">
                <?= htmlspecialchars($p['title']) ?>
            </a>
            <div class="product-price-wrap">
                <span class="product-price"><?= $currency ?><?= number_format($p['selling_price'], 0) ?></span>
                <?php if ($p['old_price'] > 0 && $p['old_price'] > $p['selling_price']): ?>
                    <span class="product-old-price"><?= $currency ?><?= number_format($p['old_price'], 0) ?></span>
                <?php endif; ?>
            </div>
            <div class="product-card-actions">
                <?php if ($inStock): ?>
                <button class="btn-add-cart" onclick="addToCart(<?= $p['id'] ?>, '<?= addslashes($p['title']) ?>', <?= $p['selling_price'] ?>, '<?= $img ?>')">
                    <i class="fa fa-shopping-cart"></i> কার্টে যোগ
                </button>
                <a href="/product.php?id=<?= $p['id'] ?>" class="btn-order-now">
                    <i class="fa fa-bolt"></i> অর্ডার
                </a>
                <?php else: ?>
                <button class="btn-add-cart" disabled style="opacity:0.5;cursor:not-allowed;flex:1">স্টক নেই</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}
include __DIR__ . '/includes/footer.php';
