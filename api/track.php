<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';
$code  = trim($_GET['code'] ?? '');
$phone = trim($_GET['phone'] ?? '');
if (!$code) { echo json_encode(['success' => false, 'message' => 'অর্ডার নম্বর দিন']); exit; }
$order = getOrderByTracking($code, $phone);
if (!$order) { echo json_encode(['success' => false, 'message' => 'অর্ডার পাওয়া যায়নি']); exit; }
// Remove sensitive data
unset($order['cost_total'], $order['net_profit']);
echo json_encode(['success' => true, 'order' => $order]);
