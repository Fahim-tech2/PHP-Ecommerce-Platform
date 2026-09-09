<?php
// =============================================
// Order Detail & Invoice Print
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<script>alert('অর্ডার পাওয়া যায়নি'); window.location='/admin/orders.php';</script>";
    exit;
}

$items = json_decode($order['items_json'], true) ?: [];
$settings = getAllSettings();
$isPrint = isset($_GET['print']);
$lbl = orderStatusLabel($order['status']);

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $newStatus = $_POST['status'];
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$newStatus, $id])) {
        echo "<script>window.location='/admin/order-detail.php?id=$id';</script>";
        exit;
    }
}
?>

<?php if (!$isPrint): ?>
<div class="admin-card-head no-print" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <a href="/admin/orders.php" class="btn-admin btn-admin-outline btn-admin-sm mb-4"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
        <h1 class="admin-page-title">অর্ডার বিস্তারিত</h1>
        <p class="admin-page-subtitle">অর্ডার আইডি: <?= htmlspecialchars($order['tracking_code']) ?></p>
    </div>
    <div style="display:flex;gap:10px">
        <button onclick="window.print()" class="btn-admin btn-admin-success"><i class="fa fa-print"></i> ইনভয়েস প্রিন্ট</button>
    </div>
</div>

<div class="order-detail-grid no-print">
    <!-- Order Info & Items -->
    <div>
        <div class="admin-card mb-4">
            <div class="admin-card-head">
                <h3 class="admin-card-title"><i class="fa fa-shopping-bag"></i> অর্ডার আইটেম</h3>
                <span class="<?= $lbl['class'] ?>"><?= $lbl['label'] ?></span>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>পণ্য</th>
                            <th>পরিমাণ</th>
                            <th>মূল্য</th>
                            <th>মোট</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($item['title']) ?></strong><br>
                                <span class="text-sm text-muted">ID: <?= $item['id'] ?></span>
                            </td>
                            <td><?= $item['qty'] ?></td>
                            <td>৳<?= number_format($item['price']) ?></td>
                            <td class="fw-700 text-primary">৳<?= number_format($item['price'] * $item['qty']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding:20px;border-top:1px solid var(--border);display:flex;justify-content:flex-end">
                <table style="width:280px;text-align:right">
                    <tr>
                        <td style="padding:6px;color:var(--text2)">সাবটোটাল:</td>
                        <td style="padding:6px;color:var(--white)">৳<?= number_format($order['subtotal']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:6px;color:var(--text2)">ডেলিভারি চার্জ:</td>
                        <td style="padding:6px;color:var(--white)">৳<?= number_format($order['shipping_fee']) ?></td>
                    </tr>
                    <?php if ($order['discount'] > 0): ?>
                    <tr>
                        <td style="padding:6px;color:var(--text2)">ডিসকাউন্ট:</td>
                        <td style="padding:6px;color:var(--success)">-৳<?= number_format($order['discount']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding:10px 6px;color:var(--white);font-weight:700;font-size:1.1rem;border-top:1px solid var(--border)">মোট বিল:</td>
                        <td style="padding:10px 6px;color:var(--primary-light);font-weight:700;font-size:1.1rem;border-top:1px solid var(--border)">৳<?= number_format($order['total_amount']) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (adminRole() === 'admin'): ?>
        <div class="admin-card">
            <div class="admin-card-head">
                <h3 class="admin-card-title"><i class="fa fa-chart-pie"></i> প্রফিট সামারি (শুধু অ্যাডমিন)</h3>
            </div>
            <div style="padding:20px">
                <div class="info-grid">
                    <div class="info-item">
                        <label>মোট ক্রয়মূল্য (Cost)</label>
                        <span>৳<?= number_format($order['cost_total']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>নেট প্রফিট</label>
                        <span class="text-success fw-700">৳<?= number_format($order['net_profit']) ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Customer & Status -->
    <div>
        <div class="admin-card mb-4">
            <div class="admin-card-head">
                <h3 class="admin-card-title"><i class="fa fa-user"></i> গ্রাহকের তথ্য</h3>
            </div>
            <div style="padding:20px">
                <div class="info-grid" style="grid-template-columns:1fr">
                    <div class="info-item">
                        <label>নাম</label>
                        <span><?= htmlspecialchars($order['customer_name']) ?></span>
                    </div>
                    <div class="info-item">
                        <label>মোবাইল নম্বর</label>
                        <span style="display:flex;align-items:center;gap:8px">
                            <?= htmlspecialchars($order['customer_phone']) ?>
                            <button class="btn-icon view" onclick="copyText('<?= htmlspecialchars($order['customer_phone']) ?>')" style="width:24px;height:24px;font-size:0.7rem"><i class="fa fa-copy"></i></button>
                        </span>
                    </div>
                    <div class="info-item">
                        <label>ডেলিভারি ঠিকানা</label>
                        <span><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></span>
                    </div>
                    <div class="info-item">
                        <label>পেমেন্ট পদ্ধতি</label>
                        <span><?= $order['payment_method'] === 'cod' ? 'ক্যাশ অন ডেলিভারি' : 'মোবাইল ব্যাংকিং' ?></span>
                    </div>
                    <div class="info-item">
                        <label>অর্ডারের সময়</label>
                        <span><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card-head">
                <h3 class="admin-card-title"><i class="fa fa-sync-alt"></i> স্ট্যাটাস আপডেট</h3>
            </div>
            <div style="padding:20px">
                <form method="POST">
                    <div class="form-group mb-4">
                        <select name="status" class="form-control">
                            <option value="pending" <?= $order['status']==='pending'?'selected':'' ?>>পেন্ডিং</option>
                            <option value="processing" <?= $order['status']==='processing'?'selected':'' ?>>প্রসেসিং</option>
                            <option value="shipped" <?= $order['status']==='shipped'?'selected':'' ?>>শিপড (কুরিয়ারে)</option>
                            <option value="delivered" <?= $order['status']==='delivered'?'selected':'' ?>>ডেলিভার্ড</option>
                            <option value="returned" <?= $order['status']==='returned'?'selected':'' ?>>রিটার্ন</option>
                            <option value="cancelled" <?= $order['status']==='cancelled'?'selected':'' ?>>বাতিল</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-admin btn-admin-primary btn-block"><i class="fa fa-save"></i> আপডেট করুন</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Printable Invoice -->
<div class="invoice-box" <?= !$isPrint ? 'style="display:none"' : '' ?>>
    <div class="invoice-header">
        <div class="invoice-logo">
            <h2><?= htmlspecialchars($settings['site_title'] ?? 'বাংলা শপ') ?></h2>
            <p><?= htmlspecialchars($settings['address'] ?? 'ঢাকা, বাংলাদেশ') ?></p>
            <p>ফোন: <?= htmlspecialchars($settings['contact_phone'] ?? '') ?></p>
        </div>
        <div class="invoice-id">
            <h3>INVOICE</h3>
            <div class="id">#<?= htmlspecialchars($order['tracking_code']) ?></div>
            <p>তারিখ: <?= date('d/m/Y', strtotime($order['created_at'])) ?></p>
        </div>
    </div>

    <div class="invoice-info-grid">
        <div class="invoice-info-block">
            <h4>Billed To / ডেলিভারি ঠিকানা:</h4>
            <p><strong><?= htmlspecialchars($order['customer_name']) ?></strong></p>
            <p>ফোন: <?= htmlspecialchars($order['customer_phone']) ?></p>
            <p><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
        </div>
        <div class="invoice-info-block" style="text-align:right">
            <h4>পেমেন্ট মেথড:</h4>
            <p><strong><?= $order['payment_method'] === 'cod' ? 'Cash on Delivery (COD)' : 'Mobile Banking' ?></strong></p>
            <p>স্ট্যাটাস: <?= $lbl['label'] ?></p>
        </div>
    </div>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>বিবরণ</th>
                <th style="text-align:center">পরিমাণ</th>
                <th style="text-align:right">মূল্য</th>
                <th style="text-align:right">মোট</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td style="text-align:center"><?= $item['qty'] ?></td>
                <td style="text-align:right">Tk <?= number_format($item['price']) ?></td>
                <td style="text-align:right">Tk <?= number_format($item['price'] * $item['qty']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="invoice-totals">
        <table class="invoice-totals-table">
            <tr>
                <td>Subtotal:</td>
                <td style="text-align:right">Tk <?= number_format($order['subtotal']) ?></td>
            </tr>
            <tr>
                <td>Delivery Charge:</td>
                <td style="text-align:right">Tk <?= number_format($order['shipping_fee']) ?></td>
            </tr>
            <?php if ($order['discount'] > 0): ?>
            <tr>
                <td>Discount:</td>
                <td style="text-align:right">-Tk <?= number_format($order['discount']) ?></td>
            </tr>
            <?php endif; ?>
            <tr class="grand">
                <td>Total Amount:</td>
                <td style="text-align:right">Tk <?= number_format($order['total_amount']) ?></td>
            </tr>
        </table>
    </div>
    
    <div style="margin-top:40px;text-align:center;color:#666;font-size:0.85rem;border-top:1px solid #eee;padding-top:20px">
        আমাদের সাথে কেনাকাটা করার জন্য ধন্যবাদ! <br>
        Thank you for shopping with us!
    </div>
</div>

<?php if ($isPrint): ?>
<script>
window.onload = function() { window.print(); }
</script>
<?php endif; ?>

<?php if (!$isPrint): ?>
<script>
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>
            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
<?php endif; ?>
