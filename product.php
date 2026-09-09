<?php
// =============================================
// Product Detail Page — প্রোডাক্ট ডিটেইল
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$id = (int)($_GET['id'] ?? 0);
$product = $id ? getProduct($id) : null;
if (!$product) { header('Location: /shop.php'); exit; }

$images  = getProductImages($product);
$mainImg = !empty($images) ? '/' . $images[0] : 'https://placehold.co/400x400/1a1a2e/6b6b8a?text=No+Image';
$reviews = getProductReviews($id);
$avgRating = getAverageRating($id);
$variants = json_decode($product['variants'] ?? '[]', true) ?: [];
$inStock = (int)$product['stock_qty'] > 0;
$currency = getSetting('currency_symbol', '৳');
$discount = ($product['old_price'] > 0 && $product['old_price'] > $product['selling_price'])
    ? round((1 - $product['selling_price'] / $product['old_price']) * 100) : 0;

$pageTitle = htmlspecialchars($product['title']) . ' | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc  = mb_substr(strip_tags($product['description']), 0, 160);
$productId   = $product['id'];
$productName = $product['title'];
$productPrice = $product['selling_price'];
include __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
    <div class="container">
        <div class="breadcrumb">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <a href="/shop.php">শপ</a>
            <?php if (!empty($product['category_name'])): ?>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <a href="/shop.php?category=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name']) ?></a>
            <?php endif; ?>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <span><?= htmlspecialchars(mb_substr($product['title'], 0, 40)) ?></span>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div class="product-detail-grid">
            <!-- Gallery -->
            <div class="product-gallery">
                <div class="gallery-main">
                    <img src="<?= htmlspecialchars($mainImg) ?>" alt="<?= htmlspecialchars($product['title']) ?>"
                         id="galleryMain" onclick="openLightbox(this.src)">
                </div>
                <?php if (count($images) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach ($images as $i => $img): ?>
                    <div class="gallery-thumb <?= $i === 0 ? 'active' : '' ?>"
                         data-src="/<?= htmlspecialchars($img) ?>"
                         onclick="document.getElementById('galleryMain').src=this.dataset.src;document.querySelectorAll('.gallery-thumb').forEach(t=>t.classList.remove('active'));this.classList.add('active')">
                        <img src="/<?= htmlspecialchars($img) ?>" alt="পণ্যের ছবি <?= $i+1 ?>">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- SKU & Meta -->
                <div style="margin-top:20px;padding:16px;background:var(--card);border:1px solid var(--border);border-radius:var(--radius)">
                    <?php if ($product['sku']): ?>
                    <div style="font-size:0.8rem;color:var(--text3);margin-bottom:6px">SKU: <span style="color:var(--text)"><?= htmlspecialchars($product['sku']) ?></span></div>
                    <?php endif; ?>
                    <div style="font-size:0.8rem;color:var(--text3);margin-bottom:6px">ক্যাটাগরি: <a href="/shop.php?category=<?= $product['category_id'] ?>" style="color:var(--primary-light)"><?= htmlspecialchars($product['category_name'] ?? '—') ?></a></div>
                    <div style="display:flex;gap:8px;margin-top:12px">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']) ?>"
                           target="_blank" class="btn btn-ghost btn-sm"><i class="fab fa-facebook"></i> শেয়ার</a>
                        <button onclick="navigator.share({title:'<?= addslashes($product['title']) ?>',url:window.location.href})" class="btn btn-ghost btn-sm">
                            <i class="fa fa-share-alt"></i> শেয়ার
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info -->
            <div class="product-detail-info">
                <?php if (!empty($product['category_name'])): ?>
                <div class="product-detail-cat"><?= htmlspecialchars($product['category_name']) ?></div>
                <?php endif; ?>
                <h1 class="product-detail-name"><?= htmlspecialchars($product['title']) ?></h1>

                <!-- Rating -->
                <div class="rating-wrap">
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa<?= $i <= $avgRating ? 's' : 'r' ?> fa-star"></i>
                        <?php endfor; ?>
                    </div>
                    <span class="rating-count"><?= $avgRating ?> (<?= count($reviews) ?> রিভিউ)</span>
                </div>

                <!-- Price -->
                <div class="product-detail-price">
                    <span class="detail-price"><?= $currency ?><?= number_format($product['selling_price'], 0) ?></span>
                    <?php if ($product['old_price'] > 0 && $product['old_price'] > $product['selling_price']): ?>
                        <span class="detail-old-price"><?= $currency ?><?= number_format($product['old_price'], 0) ?></span>
                        <span class="detail-discount">-<?= $discount ?>%</span>
                    <?php endif; ?>
                </div>

                <!-- Stock Status -->
                <div class="stock-status">
                    <?php if ($inStock): ?>
                        <i class="fa fa-check-circle stock-in"></i>
                        <span class="stock-in">স্টকে আছে (<?= $product['stock_qty'] ?> টি)</span>
                    <?php else: ?>
                        <i class="fa fa-times-circle stock-out"></i>
                        <span class="stock-out">স্টকে নেই</span>
                    <?php endif; ?>
                </div>

                <!-- Variants -->
                <?php if (!empty($variants)): ?>
                <?php foreach ($variants as $varGroup): ?>
                <div class="variant-section">
                    <div class="variant-label"><?= htmlspecialchars($varGroup['name']) ?>:</div>
                    <div class="variant-options">
                        <?php foreach ($varGroup['options'] as $opt): ?>
                        <button class="variant-btn" data-group="<?= htmlspecialchars($varGroup['name']) ?>"><?= htmlspecialchars($opt) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>

                <!-- Quantity & Actions -->
                <?php if ($inStock): ?>
                <div style="margin-bottom:20px">
                    <div class="variant-label">পরিমাণ:</div>
                    <div class="d-flex align-center gap-12" style="margin-top:8px;flex-wrap:wrap">
                        <div class="qty-selector">
                            <button class="qty-btn" onclick="changeQty(-1)">−</button>
                            <span class="qty-num" id="detailQty">1</span>
                            <button class="qty-btn" onclick="changeQty(1)">+</button>
                        </div>
                    </div>
                </div>
                <div class="product-detail-actions">
                    <button class="btn btn-ghost" style="flex:1" onclick="addToCartDetail()">
                        <i class="fa fa-shopping-cart"></i> কার্টে যোগ করুন
                    </button>
                    <a href="/checkout.php?direct=<?= $product['id'] ?>&qty=1" class="btn btn-accent" style="flex:1" id="buyNowBtn">
                        <i class="fa fa-bolt"></i> এখনই অর্ডার করুন
                    </a>
                </div>
                <?php else: ?>
                <div class="btn btn-ghost" style="opacity:0.5;cursor:not-allowed;width:100%;text-align:center">স্টক শেষ হয়েছে</div>
                <?php endif; ?>

                <!-- Trust -->
                <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:24px;padding-top:20px;border-top:1px solid var(--border)">
                    <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text3)">
                        <i class="fa fa-truck" style="color:var(--primary-light)"></i> ক্যাশ অন ডেলিভারি
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text3)">
                        <i class="fa fa-shield-alt" style="color:var(--success)"></i> ১০০% অরিজিনাল
                    </div>
                    <div style="display:flex;align-items:center;gap:6px;font-size:0.8rem;color:var(--text3)">
                        <i class="fa fa-redo" style="color:var(--warning)"></i> ৭ দিনের রিটার্ন
                    </div>
                </div>

                <!-- Tabs: Description & Reviews -->
                <div class="product-detail-desc">
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="desc">বিবরণ</button>
                        <button class="tab-btn" data-tab="reviews">রিভিউ (<?= count($reviews) ?>)</button>
                    </div>
                    <div class="tab-content active" id="tab-desc">
                        <?php if (!empty($product['description'])): ?>
                            <div style="font-size:0.95rem;color:var(--text2);line-height:1.8">
                                <?= nl2br(htmlspecialchars($product['description'])) ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">কোনো বিবরণ নেই।</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-content" id="tab-reviews">
                        <?php if (empty($reviews)): ?>
                            <p class="text-muted">এখনো কোনো রিভিউ নেই।</p>
                        <?php else: ?>
                        <?php foreach ($reviews as $rev): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-name">
                                    <i class="fa fa-user-circle" style="color:var(--primary-light)"></i>
                                    <?= htmlspecialchars($rev['reviewer_name']) ?>
                                </div>
                                <div class="review-date"><?= date('d M Y', strtotime($rev['created_at'])) ?></div>
                            </div>
                            <div class="stars" style="margin-bottom:6px;font-size:0.8rem">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star" style="color:<?= $i <= $rev['rating'] ? 'var(--warning)' : 'var(--text3)' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="review-comment"><?= htmlspecialchars($rev['comment']) ?></p>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox -->
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.9);z-index:9999;align-items:center;justify-content:center"
     onclick="this.style.display='none'">
    <img id="lightboxImg" src="" style="max-width:90vw;max-height:90vh;border-radius:var(--radius)">
</div>

<script>
let detailQty = 1;
function changeQty(d) {
    detailQty = Math.max(1, detailQty + d);
    document.getElementById('detailQty').textContent = detailQty;
    document.getElementById('buyNowBtn').href = '/checkout.php?direct=<?= $product['id'] ?>&qty=' + detailQty;
}
function addToCartDetail() {
    addToCart(<?= $product['id'] ?>, '<?= addslashes($product['title']) ?>', <?= $product['selling_price'] ?>, '<?= $mainImg ?>', detailQty);
}
function openLightbox(src) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lightboxImg').src = src;
    lb.style.display = 'flex';
}
// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('tab-' + this.dataset.tab).classList.add('active');
    });
});
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
