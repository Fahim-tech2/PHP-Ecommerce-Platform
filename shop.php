<?php
// =============================================
// Shop / Catalog Page — শপ পেজ
// =============================================
require_once __DIR__ . '/includes/functions.php';
$settings = getAllSettings();
$categories = getCategories();

// Filters
$search     = trim($_GET['search'] ?? '');
$catId      = (int)($_GET['category'] ?? 0);
$sort       = $_GET['sort'] ?? 'newest';
$inStockOnly = !empty($_GET['in_stock']);
$minPrice   = (float)($_GET['min_price'] ?? 0);
$maxPrice   = (float)($_GET['max_price'] ?? 10000);
$page       = max(1, (int)($_GET['page'] ?? 1));
$perPage    = 12;
$offset     = ($page - 1) * $perPage;

$filters = [
    'search' => $search,
    'category_id' => $catId,
    'sort' => $sort,
    'in_stock' => $inStockOnly,
    'min_price' => $minPrice > 0 ? $minPrice : null,
    'max_price' => $maxPrice < 10000 ? $maxPrice : null,
];

$products = getProducts($filters, $perPage, $offset);

// Count total (simple approach)
$db = getDB();
$where = ['1=1']; $params = [];
if ($catId) { $where[] = 'category_id = ?'; $params[] = $catId; }
if ($search) { $where[] = 'title LIKE ?'; $params[] = '%'.$search.'%'; }
if ($inStockOnly) $where[] = 'stock_qty > 0';
$cntStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE " . implode(' AND ', $where));
$cntStmt->execute($params);
$totalProducts = (int)$cntStmt->fetchColumn();
$pager = paginate($totalProducts, $perPage, $page);

$activeCategory = $catId ? getCategory($catId) : null;

$pageTitle = ($activeCategory ? htmlspecialchars($activeCategory['name']) . ' — ' : '') . 'শপ | ' . ($settings['site_title'] ?? 'বাংলা শপ');
$pageDesc = 'আমাদের সব পণ্য দেখুন এবং সহজেই অর্ডার করুন।';
include __DIR__ . '/includes/header.php';
?>

<!-- Page Hero -->
<div class="page-hero">
    <div class="container">
        <h1 class="page-hero-title">
            <?= $activeCategory ? '📦 ' . htmlspecialchars($activeCategory['name']) : '🛍️ সব পণ্য' ?>
        </h1>
        <div class="breadcrumb">
            <a href="/">হোম</a>
            <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
            <?php if ($activeCategory): ?>
                <a href="/shop.php">শপ</a>
                <i class="fa fa-chevron-right" style="font-size:0.65rem"></i>
                <span><?= htmlspecialchars($activeCategory['name']) ?></span>
            <?php else: ?>
                <span>শপ</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<section class="section">
    <div class="container">
        <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
            <button id="filterToggleBtn" class="btn btn-ghost btn-sm" onclick="document.getElementById('shopSidebar').classList.toggle('mobile-open')" style="display:none">
                <i class="fa fa-filter"></i> ফিল্টার
            </button>
        </div>
        <div class="shop-layout">
            <!-- Sidebar Filter -->
            <aside class="shop-sidebar" id="shopSidebar">
                <form method="GET" action="/shop.php" id="filterForm">
                    <div class="filter-title"><i class="fa fa-filter"></i> ফিল্টার</div>

                    <!-- Category -->
                    <div class="filter-group">
                        <div class="filter-group-label">ক্যাটাগরি</div>
                        <ul class="filter-list">
                            <li>
                                <label>
                                    <input type="radio" name="category" value="" <?= !$catId ? 'checked' : '' ?> onchange="this.form.submit()">
                                    সব ক্যাটাগরি
                                </label>
                            </li>
                            <?php foreach ($categories as $cat): ?>
                            <li>
                                <label>
                                    <input type="radio" name="category" value="<?= $cat['id'] ?>" <?= $catId == $cat['id'] ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <?= htmlspecialchars($cat['icon_url'] ?: '') ?> <?= htmlspecialchars($cat['name']) ?>
                                </label>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <div class="filter-group-label">দামের রেঞ্জ</div>
                        <div class="price-range">
                            <div class="range-labels">
                                <span>৳০</span>
                                <span id="priceLabel">৳<?= number_format($maxPrice) ?></span>
                            </div>
                            <input type="range" id="priceSlider" name="max_price" min="0" max="10000" step="50"
                                   value="<?= $maxPrice ?>" onchange="this.form.submit()">
                        </div>
                    </div>

                    <!-- Stock Filter -->
                    <div class="filter-group">
                        <div class="filter-group-label">স্টক</div>
                        <ul class="filter-list">
                            <li>
                                <label>
                                    <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?> onchange="this.form.submit()">
                                    শুধু স্টকে আছে
                                </label>
                            </li>
                        </ul>
                    </div>

                    <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                    <?php endif; ?>

                    <button type="button" class="btn btn-ghost btn-sm btn-block"
                        onclick="window.location='/shop.php'" style="margin-top:8px">
                        <i class="fa fa-times"></i> ফিল্টার রিসেট
                    </button>
                </form>
            </aside>

            <!-- Products -->
            <div>
                <div class="shop-toolbar">
                    <span class="shop-count">
                        <?= $totalProducts ?> টি পণ্য পাওয়া গেছে
                        <?= $search ? '— "' . htmlspecialchars($search) . '"' : '' ?>
                    </span>
                    <select class="sort-select" onchange="window.location=updateParam('sort',this.value)">
                        <option value="newest" <?= $sort==='newest'?'selected':'' ?>>নতুন পণ্য</option>
                        <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>দাম: কম থেকে বেশি</option>
                        <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>দাম: বেশি থেকে কম</option>
                        <option value="popular" <?= $sort==='popular'?'selected':'' ?>>জনপ্রিয় পণ্য</option>
                    </select>
                </div>

                <?php if (empty($products)): ?>
                <div class="text-center" style="padding:80px 0">
                    <div style="font-size:4rem;margin-bottom:16px">🔍</div>
                    <h3 style="color:var(--text2);margin-bottom:8px">কোনো পণ্য পাওয়া যায়নি</h3>
                    <p class="text-muted">অন্য কীওয়ার্ড বা ফিল্টার দিয়ে চেষ্টা করুন।</p>
                    <a href="/shop.php" class="btn btn-primary" style="margin-top:16px">সব পণ্য দেখুন</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $p):
                        $img = getFirstImage($p);
                        $discount = ($p['old_price'] > 0 && $p['old_price'] > $p['selling_price'])
                            ? round((1 - $p['selling_price'] / $p['old_price']) * 100) : 0;
                        $inStock = (int)$p['stock_qty'] > 0;
                        $currency = getSetting('currency_symbol', '৳');
                    ?>
                    <div class="product-card">
                        <div class="product-card-img">
                            <a href="/product.php?id=<?= $p['id'] ?>">
                                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['title']) ?>" loading="lazy">
                            </a>
                            <?php if ($discount > 0): ?>
                                <span class="discount-badge">-<?= $discount ?>%</span>
                            <?php endif; ?>
                            <?php if ($p['is_featured']): ?>
                                <span class="featured-badge">⭐</span>
                            <?php endif; ?>
                            <?php if (!$inStock): ?>
                                <div class="out-of-stock-overlay">স্টক শেষ</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-card-body">
                            <?php if (!empty($p['category_name'])): ?>
                                <div class="product-cat"><?= htmlspecialchars($p['category_name']) ?></div>
                            <?php endif; ?>
                            <a href="/product.php?id=<?= $p['id'] ?>" class="product-name" style="color:var(--text);text-decoration:none">
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
                                <button class="btn-add-cart" disabled style="opacity:0.5;flex:1">স্টক নেই</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pager['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($pager['has_prev']): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>" class="page-btn"><i class="fa fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($pager['total_pages'], $page + 2); $i++): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    <?php if ($pager['has_next']): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>" class="page-btn"><i class="fa fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
function updateParam(key, value) {
    const url = new URL(window.location.href);
    url.searchParams.set(key, value);
    return url.toString();
}
// Show filter btn on mobile
if (window.innerWidth <= 900) {
    document.getElementById('filterToggleBtn').style.display = 'flex';
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
