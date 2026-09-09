<?php
// =============================================
// Admin Dashboard
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';
$db = getDB();

// KPI Data
$today = date('Y-m-d');
$month = date('Y-m');

$stats = [
    'total_sales' => $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0,
    'today_sales' => $db->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled' AND date(created_at) = '$today'")->fetchColumn() ?: 0,
    'net_profit'  => $db->query("SELECT SUM(net_profit) FROM orders WHERE status = 'delivered'")->fetchColumn() ?: 0,
    'total_orders'=> $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending_orders'=> $db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn(),
    'total_products'=> $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
];

// Recent Orders
$recentOrders = $db->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Chart Data (Last 7 Days)
$labels = [];
$salesData = [];
$profitData = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($d));
    $s = $db->query("SELECT SUM(total_amount) FROM orders WHERE date(created_at) = '$d' AND status != 'cancelled'")->fetchColumn() ?: 0;
    $p = $db->query("SELECT SUM(net_profit) FROM orders WHERE date(created_at) = '$d' AND status = 'delivered'")->fetchColumn() ?: 0;
    $salesData[] = $s;
    $profitData[] = $p;
}

// Status Distribution
$statusCounts = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
$statusMap = [];
foreach ($statusCounts as $sc) { $statusMap[$sc['status']] = $sc['cnt']; }
$statusLabels = ['পেন্ডিং', 'প্রসেসিং', 'শিপড', 'ডেলিভার্ড', 'বাতিল'];
$statusData = [
    $statusMap['pending'] ?? 0,
    $statusMap['processing'] ?? 0,
    $statusMap['shipped'] ?? 0,
    $statusMap['delivered'] ?? 0,
    $statusMap['cancelled'] ?? 0,
];
?>

<h1 class="admin-page-title">ওভারভিউ ড্যাশবোর্ড</h1>
<p class="admin-page-subtitle">আপনার ব্যবসার আজকের অবস্থা এক নজরে দেখুন।</p>

<!-- KPIs -->
<div class="kpi-grid">
    <div class="kpi-card blue">
        <div class="kpi-icon"><i class="fa fa-shopping-cart"></i></div>
        <div class="kpi-label">আজকের বিক্রি</div>
        <div class="kpi-value">৳<?= number_format($stats['today_sales']) ?></div>
    </div>
    <div class="kpi-card purple">
        <div class="kpi-icon"><i class="fa fa-wallet"></i></div>
        <div class="kpi-label">মোট বিক্রি</div>
        <div class="kpi-value">৳<?= number_format($stats['total_sales']) ?></div>
    </div>
    <div class="kpi-card green">
        <div class="kpi-icon"><i class="fa fa-chart-line"></i></div>
        <div class="kpi-label">মোট প্রফিট (ডেলিভার্ড)</div>
        <div class="kpi-value">৳<?= number_format($stats['net_profit']) ?></div>
    </div>
    <div class="kpi-card orange">
        <div class="kpi-icon"><i class="fa fa-clock"></i></div>
        <div class="kpi-label">পেন্ডিং অর্ডার</div>
        <div class="kpi-value"><?= $stats['pending_orders'] ?></div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="chart-card">
        <div class="chart-card-head">
            <h3 class="chart-title">বিক্রি ও প্রফিট (শেষ ৭ দিন)</h3>
        </div>
        <canvas id="salesChart" height="100"></canvas>
    </div>
    <div class="chart-card">
        <div class="chart-card-head">
            <h3 class="chart-title">অর্ডার স্ট্যাটাস</h3>
        </div>
        <div style="max-width:220px;margin:0 auto">
            <canvas id="statusChart" height="220"></canvas>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="admin-card">
    <div class="admin-card-head">
        <h3 class="admin-card-title"><i class="fa fa-list-alt"></i> সাম্প্রতিক অর্ডার</h3>
        <a href="/admin/orders.php" class="btn-admin btn-admin-outline btn-admin-sm">সব দেখুন</a>
    </div>
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>অর্ডার আইডি</th>
                    <th>গ্রাহক</th>
                    <th>টোটাল</th>
                    <th>স্ট্যাটাস</th>
                    <th>তারিখ</th>
                    <th>অ্যাকশন</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentOrders)): ?>
                <tr><td colspan="6" class="text-center text-muted" style="padding:40px">কোনো অর্ডার নেই</td></tr>
                <?php else: ?>
                <?php foreach ($recentOrders as $order): 
                    $lbl = orderStatusLabel($order['status']);
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($order['tracking_code']) ?></strong></td>
                    <td>
                        <?= htmlspecialchars($order['customer_name']) ?><br>
                        <span class="text-muted text-sm"><?= htmlspecialchars($order['customer_phone']) ?></span>
                    </td>
                    <td class="fw-700 text-primary">৳<?= number_format($order['total_amount']) ?></td>
                    <td><span class="<?= $lbl['class'] ?>"><?= $lbl['label'] ?></span></td>
                    <td class="text-sm text-muted"><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
                    <td>
                        <a href="/admin/order-detail.php?id=<?= $order['id'] ?>" class="btn-icon view" title="বিস্তারিত"><i class="fa fa-eye"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
window.statusChartData = {
    labels: <?= json_encode($statusLabels) ?>,
    data: <?= json_encode($statusData) ?>
};
document.addEventListener('DOMContentLoaded', () => {
    if (typeof initDashboardCharts === 'function') {
        initDashboardCharts(<?= json_encode($salesData) ?>, <?= json_encode($profitData) ?>, <?= json_encode($labels) ?>);
    }
});
// Show mobile menu btn
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>

            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
