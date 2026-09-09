<?php
// =============================================
// Shipping Zones Management
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        $stmt = $db->prepare("DELETE FROM shipping_zones WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ডিলিট ব্যর্থ']);
        }
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['zone_name']);
    $key = trim($_POST['zone_key']);
    $fee = (float)$_POST['delivery_fee'];
    $freeMin = (float)$_POST['free_shipping_min_amount'];

    if ($id) {
        $stmt = $db->prepare("UPDATE shipping_zones SET zone_name=?, zone_key=?, delivery_fee=?, free_shipping_min_amount=? WHERE id=?");
        $stmt->execute([$name, $key, $fee, $freeMin, $id]);
        $msg = 'জোন আপডেট হয়েছে';
    } else {
        $stmt = $db->prepare("INSERT INTO shipping_zones (zone_name, zone_key, delivery_fee, free_shipping_min_amount) VALUES (?,?,?,?)");
        $stmt->execute([$name, $key, $fee, $freeMin]);
        $msg = 'নতুন জোন যোগ হয়েছে';
    }
    echo "<script>alert('$msg'); window.location='/admin/shipping.php';</script>";
    exit;
}

$zones = getShippingZones();
$editId = (int)($_GET['edit'] ?? 0);
$editZone = null;
if ($editId) {
    foreach ($zones as $z) {
        if ($z['id'] == $editId) { $editZone = $z; break; }
    }
}
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <h1 class="admin-page-title">শিপিং ও ডেলিভারি চার্জ</h1>
        <p class="admin-page-subtitle">বিভিন্ন এরিয়ার জন্য ডেলিভারি চার্জ নির্ধারণ করুন।</p>
    </div>
</div>

<div class="form-grid form-grid-2" style="align-items:start">
    <!-- List -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3 class="admin-card-title"><i class="fa fa-truck"></i> ডেলিভারি জোন লিস্ট</h3>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>জোন/এরিয়া</th>
                        <th>ডেলিভারি চার্জ</th>
                        <th>ফ্রি শিপিং (মিনিমাম)</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($zones)): ?>
                    <tr><td colspan="4" class="text-center text-muted">কোনো জোন নেই</td></tr>
                    <?php else: ?>
                    <?php foreach ($zones as $z): ?>
                    <tr <?= $editId == $z['id'] ? 'style="background:rgba(108,99,255,0.1)"' : '' ?>>
                        <td>
                            <strong><?= htmlspecialchars($z['zone_name']) ?></strong><br>
                            <span class="text-sm text-muted">Key: <?= htmlspecialchars($z['zone_key']) ?></span>
                        </td>
                        <td class="fw-700 text-primary">৳<?= number_format($z['delivery_fee']) ?></td>
                        <td>
                            <?php if ($z['free_shipping_min_amount'] > 0): ?>
                                <span class="badge badge-success">৳<?= number_format($z['free_shipping_min_amount']) ?>+</span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?= $z['id'] ?>" class="btn-icon edit" title="এডিট"><i class="fa fa-edit"></i></a>
                                <button onclick="deleteZone(<?= $z['id'] ?>, '<?= addslashes($z['zone_name']) ?>')" class="btn-icon del" title="ডিলিট"><i class="fa fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form -->
    <div class="admin-form-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:16px">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--white);display:flex;align-items:center;gap:8px">
                <i class="fa <?= $editId ? 'fa-edit' : 'fa-plus' ?>"></i> 
                <?= $editId ? 'জোন এডিট' : 'নতুন জোন যোগ' ?>
            </h3>
            <?php if ($editId): ?>
                <a href="/admin/shipping.php" class="btn-admin btn-admin-outline btn-admin-sm">নতুন যোগ করুন</a>
            <?php endif; ?>
        </div>

        <form method="POST">
            <?php if ($editId): ?><input type="hidden" name="id" value="<?= $editId ?>"><?php endif; ?>
            
            <div class="form-group mb-4">
                <label class="form-label">জোনের নাম (গ্রাহক দেখবে) <span class="req">*</span></label>
                <input type="text" name="zone_name" class="form-control" placeholder="যেমন: ঢাকার ভেতরে" required value="<?= htmlspecialchars($editZone['zone_name'] ?? '') ?>">
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">জোন Key (সিস্টেমের জন্য) <span class="req">*</span></label>
                <input type="text" name="zone_key" class="form-control" placeholder="যেমন: inside_dhaka" required value="<?= htmlspecialchars($editZone['zone_key'] ?? '') ?>">
                <div class="form-hint">ইংরেজিতে লিখুন, স্পেস ছাড়া (e.g. outside_dhaka)।</div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">ডেলিভারি চার্জ (৳) <span class="req">*</span></label>
                <input type="number" name="delivery_fee" class="form-control" required value="<?= $editZone['delivery_fee'] ?? 0 ?>">
            </div>

            <div class="form-group mb-6">
                <label class="form-label">ফ্রি শিপিং মিনিমাম অর্ডার (৳)</label>
                <input type="number" name="free_shipping_min_amount" class="form-control" value="<?= $editZone['free_shipping_min_amount'] ?? 0 ?>">
                <div class="form-hint">কত টাকার বেশি কিনলে ডেলিভারি ফ্রি হবে? ০ রাখলে ফ্রি শিপিং অফ থাকবে।</div>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;padding:12px">
                <i class="fa fa-save"></i> <?= $editId ? 'আপডেট করুন' : 'সেভ করুন' ?>
            </button>
        </form>
    </div>
</div>

<script>
function deleteZone(id, name) {
    if (!confirm(`"${name}" জোন ডিলিট করতে চান?`)) return;
    fetch('/admin/shipping.php', {
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
