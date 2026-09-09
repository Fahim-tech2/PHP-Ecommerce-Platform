<?php
// =============================================
// Admin Authentication
// =============================================
require_once __DIR__ . '/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function adminLogin(string $email, string $password): bool {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE (email = ? OR name = ?) AND role IN ('admin','manager') AND status = 'active'");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_name'] = $user['name'];
    $_SESSION['admin_role'] = $user['role'];
    return true;
}

function adminLogout(): void {
    session_destroy();
    header('Location: /admin/index.php');
    exit;
}

function requireAdmin(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (empty($_SESSION['admin_id'])) {
        header('Location: /admin/index.php');
        exit;
    }
}

function isAdmin(): bool {
    return !empty($_SESSION['admin_id']);
}

function adminName(): string {
    return $_SESSION['admin_name'] ?? 'Admin';
}

function adminRole(): string {
    return $_SESSION['admin_role'] ?? '';
}
