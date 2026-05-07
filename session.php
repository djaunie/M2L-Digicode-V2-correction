<?php
// session.php — Gestion des sessions et des rôles
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) session_start();
}
function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']);
}
function isAdmin(): bool {
    startSecureSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}
function requireLogin(): void {
    if (!isLoggedIn()) { header('Location: index.php'); exit; }
}
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) { header('Location: accueil.php?error=acces_refuse'); exit; }
}
function logout(): void {
    startSecureSession();
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
