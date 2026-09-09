<?php
require 'includes/db.php';
$db = getDB();
$hash = password_hash('admin123', PASSWORD_DEFAULT);
$db->exec("UPDATE users SET password_hash = '$hash' WHERE email = 'admin@banglashop.com'");
echo "Password reset to admin123";
