<?php
// =============================================================================
// changer_mdp.php — Modification du mot de passe (CU : Changer mdp)
// Rôle : permet à un utilisateur connecté de modifier son mot de passe
// Méthode : vérification de l'ancien MDP (MD5), puis mise à jour avec le nouveau (MD5)
// Accès : réservé aux utilisateurs connectés (requireLogin)
// =============================================================================

require_once 'config.php';
require_once 'session.php';

// Vérification que l'utilisateur est bien connecté, sinon redirection
requireLogin();

$erreur = '';

// -----------------------------------------------------------------------------
// Traitement du formulaire (soumission POST)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des trois champs
    $ancien  = trim($_POST['ancien_mdp']  ?? '');
    $nouveau = trim($_POST['nouveau_mdp'] ?? '');
    $confirm = trim($_POST['confirm_mdp'] ?? '');

    // Validation 1 : tous les champs doivent être remplis
    if ($ancien === '' || $nouveau === '' || $confirm === '') {
        $erreur = "Tous les champs sont obligatoires.";

    // Validation 2 : le nouveau mot de passe et sa confirmation doivent correspondre
    } elseif ($nouveau !== $confirm) {
        $erreur = "Les deux nouveaux mots de passe ne correspondent pas.";

    } else {

        // Récupération du mot de passe actuel en BDD (identifié par la session)
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT password FROM mrbs_users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();

        // Vérification de l'ancien mot de passe par comparaison MD5
        $ancienOk = $user && md5($ancien) === $user['password'];

        if (!$ancienOk) {
            // L'ancien mot de passe saisi ne correspond pas à celui en BDD
            $erreur = "L'ancien mot de passe est incorrect.";

        } elseif (md5($nouveau) === $user['password']) {
            // Le nouveau mot de passe est identique à l'ancien
            $erreur = "Le nouveau mot de passe doit être différent de l'ancien.";

        } else {
            // Hachage MD5 du nouveau mot de passe et mise à jour en BDD
            $hash = md5($nouveau);
            $upd  = $pdo->prepare("UPDATE mrbs_users SET password = :h WHERE id = :id");
            $upd->execute([':h' => $hash, ':id' => $_SESSION['user_id']]);

            // Redirection vers l'accueil avec paramètre de succès
            header('Location: accueil.php?success=mdp_change');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode — Changer mon mot de passe</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Changer mon mot de passe</h1>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="changer_mdp.php">

        <label for="ancien_mdp">Ancien mot de passe :</label>
        <input type="password"
               id="ancien_mdp"
               name="ancien_mdp"
               required>

        <label for="nouveau_mdp">Nouveau mot de passe :</label>
        <input type="password"
               id="nouveau_mdp"
               name="nouveau_mdp"
               required>

        <label for="confirm_mdp">Confirmer le nouveau mot de passe :</label>
        <input type="password"
               id="confirm_mdp"
               name="confirm_mdp"
               required>

        <button type="submit">Valider</button>
        <a href="accueil.php" class="btn-retour">Retour</a>

    </form>

</div>

</body>
</html>