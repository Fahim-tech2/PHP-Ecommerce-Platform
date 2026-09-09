<?php
// =============================================
// Orders List
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$statusFilter = $_GET['status'] ?? '';
$searchFilter = trim($_GET['search'] ?? '');

$where = ['1=1'];
$params = [];
if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
}
if ($searchFilter) {
    $where[] = "(tracking_code LIKE ? OR customer_phone LIKE ?)";
    $params[] = "%$searchFilter%";
    $params[] = "%$searchFilter%";
}
$whereSql = implode(' AND ', $where);

$cntStmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE $whereSql");
$cntStmt->execute($params);
$total = $cntStmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM orders WHERE $whereSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$orders = $stmt->fetchAll();

$pager = paginate($total, $limit, $page);

// Count by status
$counts = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
$statusCount = ['pending'=>0, 'processing'=>0, 'shipped'=>0, 'delivered'=>0, 'cancelled'=>0, 'returned'=>0];
foreach ($counts as $c) { $statusCount[$c['status']] = $c['cnt']; }
$allCount = array_sum($statusCount);
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <h1 class="admin-page-title">অর্ডার লিস্ট</h1>
        <p class="admin-page-subtitle">সব অর্ডারের তালিকা এবং স্ট্যাটাস ম্যানেজমেন্ট।</p>
    </div>
</div>

<div class="admin-card">
    <div class="filter-bar" style="flex-direction:column;align-items:stretch;gap:16px">
        <!-- Tabs -->
        <div class="filter-tabs" style="border-bottom:1px solid var(--border2);padding-bottom:12px;width:100%">
            <a href="?status=" class="filter-tab <?= !$statusFilter ? 'active' : '' ?>">সব (<?= $allCount ?>)</a>
            <a href="?status=pending" class="filter-tab <?= $statusFilter==='pending' ? 'active' : '' ?>">পেন্ডিং (<?= $statusCount['pending'] ?>)</a>
            <a href="?status=processing" class="filter-tab <?= $statusFilter==='processing' ? 'active' : '' ?>">প্রসেসিং (<?= $statusCount['processing'] ?>)</a>
            <a href="?status=shipped" class="filter-tab <?= $statusFilter==='shipped' ? 'active' : '' ?>">শিপড (<?= $statusCount['shipped'] ?>)</a>
            <a href="?status=delivered" class="filter-tab <?= $statusFilter==='delivered' ? 'active' : '' ?>">ডেলিভার্ড (<?= $statusCount['delivered'] ?>)</a>
            <a href="?status=cancelled" class="filter-tab <?= $statusFilter==='cancelled' ? 'active' : '' ?>">বাতিল (<?= $statusCount['cancelled'] ?>)</a>
        </div>
        
        <!-- Search -->
        <form method="GET" style="display:flex;gap:10px;width:100%;max-width:400px">
            <?php if ($statusFilter): ?><input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>"><?php endif; ?>
            <input type="text" name="search" class="filter-input flex-1" placeholder="অর্ডার আইডি বা ফোন নম্বর..." value="<?= htmlspecialchars($searchFilter) ?>">
            <button type="submit" class="btn-admin btn-admin-primary"><i class="fa fa-search"></i></button>
            <?php if ($searchFilter): ?>
            <a href="?status=<?= htmlspecialchars($statusFilter) ?>" class="btn-admin btn-admin-outline"><i class="fa fa-times"></i></a>
            <?php endif; ?>
        </form>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>অর্ডার আইডি</th>
                    <th>গ্রাহক</th>
                    <th>আইটেম</th>
                    <th>টোটাল</th>
                    <th>স্ট্যাটাস</th>
                    <th>তারিখ</th>
                    <th>অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center text-muted" style="padding:40px">কোনো অর্ডার পাওয়া যায়নি</td></tr>
                <?php else: ?>
                <?php foreach ($orders as $order): 
                    $lbl = orderStatusLabel($order['status']);
                    $items = json_decode($order['items_json'], true) ?: [];
                    $itemCount = array_reduce($items, fn($s, $i) => $s + ($i['qty'] ?? 1), 0);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($order['tracking_code']) ?></strong></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        <span class="text-sm text-muted"><?= htmlspecialchars($order['customer_phone']) ?></span>
                    </td>
                    <td><?= $itemCount ?> টি পণ্য</td>
                    <td class="fw-700 text-primary">৳<?= number_format($order['total_amount']) ?></td>
                    <td>
                        <select class="form-control" style="padding:6px;font-size:0.8rem;width:auto;background:var(--bg2)" 
                                onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)">
                            <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>পেন্ডিং</option>
                            <option value="processing" <?= $order['status']==='processing'?'selected':'' ?>>প্রসেসিং</option>
                            <option value="shipped" <?= $order['status']==='shipped'?'selected':'' ?>>শিপড</option>
                            <option value="delivered" <?= $order['status']==='delivered'?'selected':'' ?>>ডেলিভার্ড</option>
                            <option value="returned" <?= $order['status']==='returned'?'selected':'' ?>>রিটার্ন</option>
                            <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>বাতিল</option>
                        </select>
                    </td>
                    <td class="text-sm text-muted"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="/admin/order-detail.php?id=<?= $order['id'] ?>" class="btn-icon view" title="বিস্তারিত"><i class="fa fa-eye"></i></a>
                            <a href="/admin/order-detail.php?id=<?= $order['id'] ?>&print=1" target="_blank" class="btn-icon print" title="ইনভয়েস প্রিন্ট"><i class="fa fa-print"></i></a>
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

<script>
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>

            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
