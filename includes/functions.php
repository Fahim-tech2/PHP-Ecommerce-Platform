<?php
// =============================================
// Helper Functions
// =============================================
require_once __DIR__ . '/db.php';

// --- Settings ---
function getSetting(string $key, string $default = ''): string {
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function updateSetting(string $key, string $value): void {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value=excluded.setting_value");
    $stmt->execute([$key, $value]);
}

function getAllSettings(): array {
    $db = getDB();
    $rows = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll();
    $settings = [];
    foreach ($rows as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// --- Format ---
function taka(float $amount): string {
    $symbol = getSetting('currency_symbol', '৳');
    return $symbol . number_format($amount, 0);
}

function generateTrackingCode(): string {
    $db = getDB();
    do {
        $code = 'ORD-' . rand(10000, 99999);
        $exists = $db->prepare("SELECT id FROM orders WHERE tracking_code = ?");
        $exists->execute([$code]);
    } while ($exists->fetch());
    return $code;
}

// --- Products ---
function getProducts(array $filters = [], int $limit = 20, int $offset = 0): array {
    $db = getDB();
    $where = ['1=1'];
    $params = [];

    if (!empty($filters['category_id'])) {
        $where[] = 'p.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['search'])) {
        $where[] = 'p.title LIKE ?';
        $params[] = '%' . $filters['search'] . '%';
    }
    if (isset($filters['in_stock']) && $filters['in_stock']) {
        $where[] = 'p.stock_qty > 0';
    }
    if (!empty($filters['min_price'])) {
        $where[] = 'p.selling_price >= ?';
        $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = 'p.selling_price <= ?';
        $params[] = $filters['max_price'];
    }
    if (!empty($filters['is_featured'])) {
        $where[] = 'p.is_featured = 1';
    }

    $sort = match($filters['sort'] ?? '') {
        'price_asc' => 'p.selling_price ASC',
        'price_desc' => 'p.selling_price DESC',
        'popular' => 'p.is_featured DESC',
        default => 'p.created_at DESC',
    };

    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE " . implode(' AND ', $where) . " ORDER BY $sort LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProduct(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getProductImages(array $product): array {
    $imgs = json_decode($product['images'] ?? '[]', true) ?: [];
    return $imgs;
}

function getFirstImage(array $product, string $placeholder = 'https://placehold.co/400x400/1a1a2e/6b6b8a?text=No+Image'): string {
    $imgs = getProductImages($product);
    return !empty($imgs) ? '/' . ltrim($imgs[0], '/') : $placeholder;
}

// --- Categories ---
function getCategories(): array {
    $db = getDB();
    return $db->query("SELECT * FROM categories ORDER BY sort_order ASC, id ASC")->fetchAll();
}

function getCategory(int $id): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// --- Orders ---
function getOrderByTracking(string $code, string $phone = ''): ?array {
    $db = getDB();
    $sql = "SELECT * FROM orders WHERE tracking_code = ?";
    $params = [$code];
    if ($phone) {
        $sql .= " AND customer_phone = ?";
        $params[] = $phone;
    }
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

function getOrders(string $status = '', int $limit = 50, int $offset = 0): array {
    $db = getDB();
    if ($status) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE status = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$status, $limit, $offset]);
    } else {
        $stmt = $db->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$limit, $offset]);
    }
    return $stmt->fetchAll();
}

// --- Shipping ---
function getShippingZones(): array {
    $db = getDB();
    return $db->query("SELECT * FROM shipping_zones ORDER BY id ASC")->fetchAll();
}

function getShippingFee(string $zone_key, float $subtotal): float {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM shipping_zones WHERE zone_key = ?");
    $stmt->execute([$zone_key]);
    $zone = $stmt->fetch();
    if (!$zone) return 70;
    if ($zone['free_shipping_min_amount'] > 0 && $subtotal >= $zone['free_shipping_min_amount']) return 0;
    return (float)$zone['delivery_fee'];
}

// --- Coupon ---
function validateCoupon(string $code, float $subtotal): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (expires_at IS NULL OR expires_at > datetime('now')) AND (max_uses = 0 OR used_count < max_uses)");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();
    if (!$coupon) return null;
    if ($subtotal < $coupon['min_order_amount']) return null;
    return $coupon;
}

// --- Image Upload ---
function handleImageUpload(string $fileKey, string $folder = 'products'): ?string {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) return null;
    $file = $_FILES[$fileKey];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > 5 * 1024 * 1024) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $dest = $uploadDir . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/' . $folder . '/' . $filename;
    }
    return null;
}

function handleMultipleImageUpload(string $fileKey, string $folder = 'products'): array {
    $paths = [];
    if (!isset($_FILES[$fileKey])) return $paths;
    $files = $_FILES[$fileKey];
    if (!is_array($files['name'])) {
        $path = handleImageUpload($fileKey, $folder);
        if ($path) $paths[] = $path;
        return $paths;
    }
    foreach ($files['name'] as $i => $name) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
        $tmp = ['name' => $name, 'type' => $files['type'][$i], 'tmp_name' => $files['tmp_name'][$i], 'error' => $files['error'][$i], 'size' => $files['size'][$i]];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($tmp['type'], $allowed)) continue;
        if ($tmp['size'] > 5 * 1024 * 1024) continue;
        $ext = pathinfo($tmp['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '_' . $i . '.' . $ext;
        $uploadDir = __DIR__ . '/../uploads/' . $folder . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $dest = $uploadDir . $filename;
        if (move_uploaded_file($tmp['tmp_name'], $dest)) {
            $paths[] = 'uploads/' . $folder . '/' . $filename;
        }
    }
    return $paths;
}

// --- Status Labels ---
function orderStatusLabel(string $status): array {
    return match($status) {
        'pending'    => ['label' => 'পেন্ডিং', 'class' => 'status-pending'],
        'processing' => ['label' => 'প্রসেসিং', 'class' => 'status-processing'],
        'shipped'    => ['label' => 'শিপড', 'class' => 'status-shipped'],
        'delivered'  => ['label' => 'ডেলিভার্ড', 'class' => 'status-delivered'],
        'returned'   => ['label' => 'রিটার্ন', 'class' => 'status-returned'],
        'cancelled'  => ['label' => 'বাতিল', 'class' => 'status-cancelled'],
        default      => ['label' => $status, 'class' => 'status-pending'],
    };
}

// --- Pagination ---
function paginate(int $total, int $perPage, int $current): array {
    $totalPages = (int)ceil($total / $perPage);
    return ['total' => $total, 'per_page' => $perPage, 'current' => $current, 'total_pages' => $totalPages, 'has_prev' => $current > 1, 'has_next' => $current < $totalPages];
}

// --- Reviews ---
function getProductReviews(int $productId): array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM reviews WHERE product_id = ? ORDER BY created_at DESC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getAverageRating(int $productId): float {
    $db = getDB();
    $stmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM reviews WHERE product_id = ?");
    $stmt->execute([$productId]);
    $row = $stmt->fetch();
    return round((float)($row['avg_rating'] ?? 0), 1);
}
