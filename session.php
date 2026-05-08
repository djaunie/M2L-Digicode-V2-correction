<?php
// =============================================================================
// session.php — Gestion des sessions et des rôles
// Rôle : fournit les fonctions utilitaires liées à l'authentification
//         et au contrôle d'accès par rôle (user / admin)
// Inclus dans : tous les fichiers PHP du projet via require_once
// =============================================================================


/**
 * Démarre la session PHP de façon sécurisée.
 * Vérifie que la session n'est pas déjà active avant de la lancer
 * pour éviter l'erreur "session already started".
 */
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}


/**
 * Vérifie si un utilisateur est connecté.
 * Retourne true si la variable de session 'user_id' est définie.
 */
function isLoggedIn(): bool {
    startSecureSession();
    return isset($_SESSION['user_id']);
}


/**
 * Vérifie si l'utilisateur connecté est un administrateur.
 * Retourne true si le rôle en session est exactement 'admin'.
 */
function isAdmin(): bool {
    startSecureSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


/**
 * Protège une page : redirige vers index.php si l'utilisateur n'est pas connecté.
 * À appeler en haut de chaque page réservée aux utilisateurs authentifiés.
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}


/**
 * Protège une page : redirige si l'utilisateur n'est pas connecté ou n'est pas admin.
 * - Si non connecté : redirection vers index.php (via requireLogin)
 * - Si connecté mais pas admin : redirection vers accueil.php avec erreur
 * À appeler en haut de chaque page réservée aux administrateurs.
 */
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        header('Location: accueil.php?error=acces_refuse');
        exit;
    }
}


/**
 * Déconnecte l'utilisateur.
 * Vide le tableau de session, détruit la session, puis redirige vers index.php.
 */
function logout(): void {
    startSecureSession();
    $_SESSION = [];       // Suppression de toutes les variables de session
    session_destroy();    // Destruction de la session côté serveur
    header('Location: index.php');
    exit;
}