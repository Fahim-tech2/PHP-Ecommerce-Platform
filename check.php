<?php
require 'includes/functions.php';
$db = getDB();
$products = $db->query("SELECT * FROM products")->fetchAll();
echo "Total products: " . count($products) . "\n";
