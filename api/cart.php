<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? 'zones';

if ($action === 'coupon') {
    $code = trim($_GET['code'] ?? '');
    $subtotal = (float)($_GET['subtotal'] ?? 0);
    $coupon = validateCoupon($code, $subtotal);
    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'কুপন কোড বৈধ নয় বা মেয়াদ শেষ।']);
    } else {
        if ($coupon['discount_type'] === 'percent') {
            $discount = $subtotal * ($coupon['discount_value'] / 100);
            $msg = $coupon['discount_value'] . '% ছাড়';
        } else {
            $discount = $coupon['discount_value'];
            $msg = '৳' . $discount . ' ছাড়';
        }
        echo json_encode(['success' => true, 'discount' => $discount, 'message' => $msg]);
    }
} elseif ($action === 'shipping') {
    $zone = $_GET['zone'] ?? 'inside_dhaka';
    $subtotal = (float)($_GET['subtotal'] ?? 0);
    $fee = getShippingFee($zone, $subtotal);
    echo json_encode(['success' => true, 'fee' => $fee]);
} else {
    echo json_encode(['zones' => getShippingZones()]);
}
