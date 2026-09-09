<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/functions.php';
$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode(['products' => []]); exit; }
$products = getProducts(['search' => $q], 8);
$result = array_map(fn($p) => [
    'id' => $p['id'],
    'title' => $p['title'],
    'selling_price' => $p['selling_price'],
    'image' => getFirstImage($p),
], $products);
echo json_encode(['products' => $result]);
