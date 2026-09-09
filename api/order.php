<?php
// =============================================
// Order API — অর্ডার সাবমিট
// =============================================
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']); exit;
}

$name    = trim($_POST['customer_name'] ?? '');
$phone   = trim($_POST['customer_phone'] ?? '');
$address = trim($_POST['delivery_address'] ?? '');
$region  = trim($_POST['region'] ?? 'inside_dhaka');
$payment = trim($_POST['payment_method'] ?? 'cod');
$shippingFee = (float)($_POST['shipping_fee'] ?? 70);
$cartJson = $_POST['cart'] ?? '[]';
$couponDiscount = (float)($_POST['coupon_discount'] ?? 0);
$couponCode = trim($_POST['coupon_code'] ?? '');
$directProductId = (int)($_POST['direct_product_id'] ?? 0);
$directQty = max(1, (int)($_POST['direct_qty'] ?? 1));

// Validate
if (!$name || !$phone || !$address) {
    echo json_encode(['success' => false, 'message' => 'সব তথ্য পূরণ করুন']); exit;
}
if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'সঠিক মোবাইল নম্বর দিন']); exit;
}

$db = getDB();

// Build cart
if ($directProductId) {
    $product = getProduct($directProductId);
    if (!$product || $product['stock_qty'] < $directQty) {
        echo json_encode(['success' => false, 'message' => 'পণ্যটি স্টকে নেই']); exit;
    }
    $cartItems = [[
        'id' => $directProductId,
        'title' => $product['title'],
        'price' => $product['selling_price'],
        'buying_price' => $product['buying_price'],
        'qty' => $directQty,
    ]];
} else {
    $cartRaw = json_decode($cartJson, true);
    if (!$cartRaw || !is_array($cartRaw) || empty($cartRaw)) {
        echo json_encode(['success' => false, 'message' => 'কার্ট খালি']); exit;
    }
    $cartItems = [];
    foreach ($cartRaw as $item) {
        $product = getProduct((int)$item['id']);
        if (!$product) continue;
        $qty = max(1, (int)($item['qty'] ?? 1));
        if ($product['stock_qty'] < $qty) {
            echo json_encode(['success' => false, 'message' => '"' . $product['title'] . '" পণ্যে পর্যাপ্ত স্টক নেই']); exit;
        }
        $cartItems[] = [
            'id' => $product['id'],
            'title' => $product['title'],
            'price' => $product['selling_price'],
            'buying_price' => $product['buying_price'],
            'qty' => $qty,
        ];
    }
}

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'কার্টে কোনো বৈধ পণ্য নেই']); exit;
}

// Calculate
$subtotal = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cartItems));
$costTotal = array_sum(array_map(fn($i) => $i['buying_price'] * $i['qty'], $cartItems));

// Recalculate shipping from DB
$shippingFee = getShippingFee($region, $subtotal);

$totalAmount = $subtotal + $shippingFee - $couponDiscount;
$netProfit   = $totalAmount - $costTotal - $shippingFee;

$trackingCode = generateTrackingCode();

try {
    $db->beginTransaction();

    // Insert order
    $stmt = $db->prepare("INSERT INTO orders (tracking_code, customer_name, customer_phone, delivery_address, region, items_json, subtotal, shipping_fee, discount, coupon_code, total_amount, cost_total, net_profit, payment_method, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')");
    // Fix: correct column count
    $stmt = $db->prepare("INSERT INTO orders (tracking_code, customer_name, customer_phone, delivery_address, region, items_json, subtotal, shipping_fee, discount, coupon_code, total_amount, cost_total, net_profit, payment_method, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $trackingCode, $name, $phone, $address, $region,
        json_encode($cartItems, JSON_UNESCAPED_UNICODE),
        $subtotal, $shippingFee, $couponDiscount, $couponCode,
        $totalAmount, $costTotal, $netProfit, $payment, 'pending'
    ]);

    // Deduct stock
    foreach ($cartItems as $item) {
        $db->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id = ? AND stock_qty >= ?")->execute([$item['qty'], $item['id'], $item['qty']]);
    }

    // Update coupon usage
    if ($couponCode) {
        $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$couponCode]);
    }

    $db->commit();

    // Server-Side Tracking
    $serverTrackingEnabled = getSetting('server_tracking_enabled', '0') === '1';
    $serverSideToken = getSetting('server_side_token', '');
    $serverSideUrl   = getSetting('server_side_url', '');
    if ($serverTrackingEnabled && $serverSideToken && $serverSideUrl) {
        sendServerSideEvent('Purchase', [
            'order_id' => $trackingCode,
            'value'    => $totalAmount,
            'currency' => 'BDT',
            'phone'    => $phone,
        ], $serverSideUrl, $serverSideToken);
    }

    echo json_encode([
        'success' => true,
        'tracking_code' => $trackingCode,
        'total' => $totalAmount,
        'message' => 'অর্ডার সফল হয়েছে',
    ]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'অর্ডার সাবমিট করতে সমস্যা হয়েছে: ' . $e->getMessage()]);
}

function sendServerSideEvent(string $event, array $data, string $url, string $token): void {
    $payload = json_encode(array_merge($data, ['event' => $event, 'token' => $token, 'timestamp' => time()]));
    $ctx = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\nAuthorization: Bearer $token\r\n",
        'content' => $payload,
        'timeout' => 3,
    ]]);
    @file_get_contents($url, false, $ctx);
}
