<?php
// =============================================
// Product List
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'ডিলিট ব্যর্থ']);
    }
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$total = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$products = $db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset")->fetchAll();
$pager = paginate($total, $limit, $page);
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <h1 class="admin-page-title">প্রোডাক্ট লিস্ট</h1>
        <p class="admin-page-subtitle">আপনার স্টোরের সকল পণ্য এখান থেকে ম্যানেজ করুন।</p>
    </div>
    <a href="/admin/product-form.php" class="btn-admin btn-admin-primary">
        <i class="fa fa-plus"></i> নতুন প্রোডাক্ট
    </a>
</div>

<div class="admin-card">
    <div class="filter-bar">
        <input type="text" id="productSearch" class="filter-input flex-1" placeholder="প্রোডাক্ট খুঁজুন..." style="max-width:300px">
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table" id="productsTable">
            <thead>
                <tr>
                    <th>ছবি</th>
                    <th>নাম</th>
                    <th>ক্যাটাগরি</th>
                    <th>ক্রয়মূল্য</th>
                    <th>বিক্রয়মূল্য</th>
                    <th>স্টক</th>
                    <th>অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:40px">কোনো প্রোডাক্ট নেই</td></tr>
                <?php else: ?>
                <?php foreach ($products as $p): 
                    $img = getFirstImage($p);
                ?>
                <tr>
                    <td><img src="<?= htmlspecialchars($img) ?>" class="product-thumb" alt="thumb"></td>
                    <td>
                        <strong style="color:var(--white)"><?= htmlspecialchars($p['title']) ?></strong>
                        <?php if ($p['is_featured']): ?> <span class="badge badge-warning" style="font-size:0.6rem;padding:2px 6px">ফিচার্ড</span> <?php endif; ?>
                        <br><span class="text-sm text-muted">SKU: <?= htmlspecialchars($p['sku'] ?: '—') ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
                    <td class="text-muted">৳<?= number_format($p['buying_price']) ?></td>
                    <td class="fw-700 text-primary">৳<?= number_format($p['selling_price']) ?></td>
                    <td>
                        <?php if ($p['stock_qty'] > 10): ?>
                            <span class="badge badge-success"><?= $p['stock_qty'] ?></span>
                        <?php elseif ($p['stock_qty'] > 0): ?>
                            <span class="badge badge-warning"><?= $p['stock_qty'] ?></span>
                        <?php else: ?>
                            <span class="badge badge-danger">স্টক আউট</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <a href="/product.php?id=<?= $p['id'] ?>" target="_blank" class="btn-icon view" title="দেখুন"><i class="fa fa-external-link-alt"></i></a>
                            <a href="/admin/product-form.php?id=<?= $p['id'] ?>" class="btn-icon edit" title="এডিট"><i class="fa fa-edit"></i></a>
                            <button onclick="deleteProduct(<?= $p['id'] ?>, '<?= addslashes($p['title']) ?>')" class="btn-icon del" title="ডিলিট"><i class="fa fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($pager['total_pages'] > 1): ?>
<div class="pagination">
    <?php if ($pager['has_prev']): ?>
    <a href="?page=<?= $page - 1 ?>" class="page-btn"><i class="fa fa-chevron-left"></i></a>
    <?php endif; ?>
    <?php for ($i = max(1, $page - 2); $i <= min($pager['total_pages'], $page + 2); $i++): ?>
    <a href="?page=<?= $i ?>" class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($pager['has_next']): ?>
    <a href="?page=<?= $page + 1 ?>" class="page-btn"><i class="fa fa-chevron-right"></i></a>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
function deleteProduct(id, name) {
    if (!confirm(`"${name}" ডিলিট করতে চান?`)) return;
    fetch('/admin/products.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${id}`
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); }
        else { alert('ডিলিট ব্যর্থ'); }
    });
}
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>

            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
