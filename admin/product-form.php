<?php
// =============================================
// Product Add/Edit Form
// =============================================
require_once __DIR__ . '/../includes/admin-layout.php';

$db = getDB();
$id = (int)($_GET['id'] ?? 0);
$product = $id ? getProduct($id) : null;
$categories = getCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $desc  = trim($_POST['description']);
    $buy   = (float)$_POST['buying_price'];
    $sell  = (float)$_POST['selling_price'];
    $old   = (float)$_POST['old_price'];
    $stock = (int)$_POST['stock_qty'];
    $catId = (int)$_POST['category_id'];
    $sku   = trim($_POST['sku']);
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;

    // Handle Images
    $images = $product ? json_decode($product['images'] ?? '[]', true) ?: [] : [];
    
    // Remove selected images
    if (!empty($_POST['remove_images'])) {
        foreach ($_POST['remove_images'] as $imgToRemove) {
            $idx = array_search($imgToRemove, $images);
            if ($idx !== false) {
                unset($images[$idx]);
                @unlink(__DIR__ . '/../' . $imgToRemove);
            }
        }
        $images = array_values($images);
    }

    // Upload new images
    $newImages = handleMultipleImageUpload('new_images');
    $images = array_merge($images, $newImages);
    $imagesJson = json_encode($images);

    if ($id) {
        $stmt = $db->prepare("UPDATE products SET title=?, description=?, buying_price=?, selling_price=?, old_price=?, stock_qty=?, category_id=?, sku=?, is_featured=?, images=? WHERE id=?");
        $stmt->execute([$title, $desc, $buy, $sell, $old, $stock, $catId, $sku, $isFeatured, $imagesJson, $id]);
        $msg = 'প্রোডাক্ট আপডেট হয়েছে';
    } else {
        $stmt = $db->prepare("INSERT INTO products (title, description, buying_price, selling_price, old_price, stock_qty, category_id, sku, is_featured, images) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$title, $desc, $buy, $sell, $old, $stock, $catId, $sku, $isFeatured, $imagesJson]);
        $id = $db->lastInsertId();
        $msg = 'নতুন প্রোডাক্ট যোগ হয়েছে';
    }
    
    echo "<script>alert('$msg'); window.location='/admin/products.php';</script>";
    exit;
}

$images = $product ? json_decode($product['images'] ?? '[]', true) ?: [] : [];
?>

<div class="admin-card-head" style="padding:0 0 20px 0; border:none; background:transparent">
    <div>
        <a href="/admin/products.php" class="btn-admin btn-admin-outline btn-admin-sm mb-4"><i class="fa fa-arrow-left"></i> ফিরে যান</a>
        <h1 class="admin-page-title"><?= $id ? 'প্রোডাক্ট এডিট করুন' : 'নতুন প্রোডাক্ট যোগ করুন' ?></h1>
    </div>
</div>

<form method="POST" enctype="multipart/form-data">
    <div class="form-grid" style="grid-template-columns: 2fr 1fr; align-items: start;">
        
        <!-- Main Info -->
        <div>
            <div class="admin-form-card">
                <h3 class="admin-form-title"><i class="fa fa-info-circle"></i> সাধারণ তথ্য</h3>
                <div class="form-group mb-4">
                    <label class="form-label">প্রোডাক্টের নাম <span class="req">*</span></label>
                    <input type="text" name="title" class="form-control" required value="<?= htmlspecialchars($product['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">বিবরণ</label>
                    <textarea name="description" class="form-control" rows="8"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Images -->
            <div class="admin-form-card">
                <h3 class="admin-form-title"><i class="fa fa-images"></i> ছবি</h3>
                <div class="image-upload-area mb-4">
                    <div class="upload-icon"><i class="fa fa-cloud-upload-alt"></i></div>
                    <div class="upload-text">নতুন ছবি আপলোড করতে ক্লিক করুন</div>
                    <div class="upload-hint">একাধিক ছবি সিলেক্ট করতে পারেন (Max 5MB each)</div>
                    <input type="file" name="new_images[]" id="productImages" multiple accept="image/*">
                </div>
                <div class="image-previews" id="productImagePreviews"></div>

                <?php if (!empty($images)): ?>
                <div style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px">
                    <label class="form-label">বর্তমান ছবিসমূহ (ডিলিট করতে টিক দিন)</label>
                    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:8px">
                        <?php foreach ($images as $img): ?>
                        <div style="position:relative;width:80px;height:80px">
                            <img src="/<?= htmlspecialchars($img) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-sm);border:1px solid var(--border)">
                            <label style="position:absolute;top:-8px;right:-8px;background:var(--bg2);border-radius:50%;padding:2px;cursor:pointer">
                                <input type="checkbox" name="remove_images[]" value="<?= htmlspecialchars($img) ?>" style="accent-color:var(--danger)">
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="admin-form-card">
                <h3 class="admin-form-title"><i class="fa fa-money-bill"></i> মূল্য ও স্টক</h3>
                <div class="form-group mb-4">
                    <label class="form-label">বিক্রয়মূল্য (Selling Price) <span class="req">*</span></label>
                    <input type="number" name="selling_price" class="form-control" required value="<?= $product['selling_price'] ?? '' ?>">
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">ক্রয়মূল্য (Buying Price) <span class="req">*</span></label>
                    <input type="number" name="buying_price" class="form-control" required value="<?= $product['buying_price'] ?? 0 ?>">
                    <div class="form-hint">প্রফিট হিসাব করার জন্য এটি প্রয়োজন। গ্রাহক এটি দেখতে পাবে না।</div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">আগের মূল্য (Old Price)</label>
                    <input type="number" name="old_price" class="form-control" value="<?= $product['old_price'] ?? 0 ?>">
                    <div class="form-hint">ডিসকাউন্ট দেখানোর জন্য। ০ রাখলে কিছু দেখাবে না।</div>
                </div>
                <div class="form-group mb-4">
                    <label class="form-label">স্টক পরিমাণ <span class="req">*</span></label>
                    <input type="number" name="stock_qty" class="form-control" required value="<?= $product['stock_qty'] ?? 10 ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">SKU (ঐচ্ছিক)</label>
                    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku'] ?? '') ?>">
                </div>
            </div>

            <div class="admin-form-card">
                <h3 class="admin-form-title"><i class="fa fa-tags"></i> ক্যাটাগরি ও অন্যান্য</h3>
                <div class="form-group mb-4">
                    <label class="form-label">ক্যাটাগরি</label>
                    <select name="category_id" class="form-control">
                        <option value="">ক্যাটাগরি সিলেক্ট করুন</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <div class="toggle-wrap">
                        <div class="toggle-label" style="flex:1">ফিচার্ড প্রোডাক্ট? (হোম পেজে দেখাবে)</div>
                        <label class="toggle">
                            <input type="checkbox" name="is_featured" <?= !empty($product['is_featured']) ? 'checked' : '' ?>>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;padding:14px;font-size:1rem">
                <i class="fa fa-save"></i> <?= $id ? 'আপডেট করুন' : 'সেভ করুন' ?>
            </button>
        </div>
    </div>
</form>

<script>
if (window.innerWidth <= 1024) document.getElementById('adminMenuBtn').style.display = 'flex';
</script>

            </div><!-- .admin-content -->
        </main>
    </div><!-- .admin-wrapper -->
    <script src="/assets/js/admin.js"></script>
</body>
</html>
