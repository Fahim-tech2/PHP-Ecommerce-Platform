<?php
// =============================================
// Staff & Users Management
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

if (adminRole() !== 'admin') {
    echo "<script>alert('আপনার এই পেজে প্রবেশের অনুমতি নেই'); window.location='/admin/dashboard.php';</script>";
    exit;
}

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'delete') {
        $id = (int)$_POST['id'];
        // Prevent deleting oneself
        if ($id === adminId()) {
            echo json_encode(['success' => false, 'message' => 'নিজেকে ডিলিট করা যাবে না']);
            exit;
        }
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'ডিলিট ব্যর্থ']);
        }
        exit;
    }

    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $pass = trim($_POST['password']);

    // Check email uniqueness
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        echo "<script>alert('এই ইমেইলটি আগে থেকেই ব্যবহৃত হচ্ছে'); window.location='/admin/users.php';</script>";
        exit;
    }

    if ($id) {
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $hash, $role, $id]);
        } else {
            $stmt = $db->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
            $stmt->execute([$name, $email, $role, $id]);
        }
        $msg = 'ইউজার আপডেট হয়েছে';
    } else {
        if (!$pass) $pass = '123456';
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)");
        $stmt->execute([$name, $email, $hash, $role]);
        $msg = 'নতুন ইউজার যোগ হয়েছে';
    }
    echo "<script>alert('$msg'); window.location='/admin/users.php';</script>";
    exit;
}

$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
$editId = (int)($_GET['edit'] ?? 0);
$editUser = null;
if ($editId) {
    foreach ($users as $u) {
        if ($u['id'] == $editId) { $editUser = $u; break; }
    }
}
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <h1 class="admin-page-title">স্টাফ ও ইউজার</h1>
        <p class="admin-page-subtitle">যারা এই অ্যাডমিন প্যানেলটি ব্যবহার করতে পারবে।</p>
    </div>
</div>

<div class="form-grid form-grid-2" style="align-items:start">
    <!-- List -->
    <div class="admin-card">
        <div class="admin-card-head">
            <h3 class="admin-card-title"><i class="fa fa-users"></i> ইউজার লিস্ট</h3>
        </div>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>নাম ও ইমেইল</th>
                        <th>রোল</th>
                        <th>অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr <?= $editId == $u['id'] ? 'style="background:rgba(108,99,255,0.1)"' : '' ?>>
                        <td>
                            <strong><?= htmlspecialchars($u['name']) ?></strong><br>
                            <span class="text-sm text-muted"><?= htmlspecialchars($u['email']) ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $u['role'] === 'admin' ? 'badge-primary' : 'badge-warning' ?>">
                                <?= $u['role'] === 'admin' ? 'অ্যাডমিন' : 'ম্যানেজার' ?>
                            </span>
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?= $u['id'] ?>" class="btn-icon edit" title="এডিট"><i class="fa fa-edit"></i></a>
                                <?php if ($u['id'] !== adminId()): ?>
                                <button onclick="deleteUser(<?= $u['id'] ?>, '<?= addslashes($u['name']) ?>')" class="btn-icon del" title="ডিলিট"><i class="fa fa-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Form -->
    <div class="admin-form-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;border-bottom:1px solid var(--border);padding-bottom:16px">
            <h3 style="font-size:1.1rem;font-weight:700;color:var(--white);display:flex;align-items:center;gap:8px">
                <i class="fa <?= $editId ? 'fa-edit' : 'fa-user-plus' ?>"></i> 
                <?= $editId ? 'ইউজার এডিট' : 'নতুন ইউজার যোগ' ?>
            </h3>
            <?php if ($editId): ?>
                <a href="/admin/users.php" class="btn-admin btn-admin-outline btn-admin-sm">নতুন যোগ করুন</a>
            <?php endif; ?>
        </div>

        <form method="POST">
            <?php if ($editId): ?><input type="hidden" name="id" value="<?= $editId ?>"><?php endif; ?>
            
            <div class="form-group mb-4">
                <label class="form-label">নাম <span class="req">*</span></label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($editUser['name'] ?? '') ?>">
            </div>
            
            <div class="form-group mb-4">
                <label class="form-label">ইমেইল (লগইনের জন্য) <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($editUser['email'] ?? '') ?>">
            </div>

            <div class="form-group mb-4">
                <label class="form-label">রোল <span class="req">*</span></label>
                <select name="role" class="form-control">
                    <option value="manager" <?= ($editUser['role'] ?? '') === 'manager' ? 'selected' : '' ?>>ম্যানেজার (সব দেখতে পাবে, কিন্তু সেটিংস/ইউজার এডিট করতে পারবে না)</option>
                    <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>অ্যাডমিন (ফুল এক্সেস)</option>
                </select>
            </div>

            <div class="form-group mb-6">
                <label class="form-label">পাসওয়ার্ড <?= $editId ? '(পরিবর্তন করতে চাইলে লিখুন)' : '<span class="req">*</span>' ?></label>
                <input type="password" name="password" class="form-control" <?= $editId ? '' : 'required' ?>>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;padding:12px">
                <i class="fa fa-save"></i> <?= $editId ? 'আপডেট করুন' : 'সেভ করুন' ?>
            </button>
        </form>
    </div>
</div>

<script>
function deleteUser(id, name) {
    if (!confirm(`"${name}" কে ডিলিট করতে চান?`)) return;
    fetch('/admin/users.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=delete&id=${id}`
    }).then(r => r.json()).then(d => {
        if (d.success) { location.reload(); }
        else { alert(d.message || 'ডিলিট ব্যর্থ'); }
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
