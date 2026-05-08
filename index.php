<?php
// =============================================================================
// index.php — Authentification (CU : Se connecter)
// Rôle : vérifie les identifiants de l'utilisateur et ouvre sa session
// Méthode : comparaison du mot de passe saisi (hashé en MD5) avec la BDD
// =============================================================================

require_once 'config.php';
require_once 'session.php';

// Démarrage sécurisé de la session
startSecureSession();

// Si l'utilisateur est déjà connecté, redirection directe vers l'accueil
if (isLoggedIn()) {
    header('Location: accueil.php');
    exit;
}

$erreur = '';

// -----------------------------------------------------------------------------
// Traitement du formulaire de connexion (soumission POST)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage des champs du formulaire
    $login = trim($_POST['login'] ?? '');
    $mdp   = trim($_POST['mdp']   ?? '');

    // Validation : champs obligatoires
    if ($login === '' || $mdp === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {

        // Recherche de l'utilisateur en BDD par son identifiant (name)
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM mrbs_users WHERE name = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();

        // Vérification du mot de passe : comparaison hash MD5
        $mdpOk = false;
        if ($user && md5($mdp) === $user['password']) {
            $mdpOk = true;
        }

        // Authentification échouée : utilisateur introuvable ou mauvais mot de passe
        if (!$user || !$mdpOk) {
            $erreur = "Nom ou mot de passe incorrect.";
        } else {
            // Authentification réussie : enregistrement des données en session
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            // Rôle : 'admin' si level >= 2, sinon 'user'
            $_SESSION['role'] = ($user['level'] >= 2) ? 'admin' : 'user';

            // Redirection vers la page d'accueil
            header('Location: accueil.php');
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
    <title>M2L Digicode — Connexion</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Maison des Ligues — Digicode</h1>
    <h2>Connexion</h2>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="index.php">

        <label for="login">Identifiant :</label>
        <input type="text"
               id="login"
               name="login"
               required
               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">

        <label for="mdp">Mot de passe :</label>
        <input type="password"
               id="mdp"
               name="mdp"
               required>

        <button type="submit">Se connecter</button>

    </form>

    <p style="margin-top: 1rem">
        <a href="mdp_oublie.php">Mot de passe oublié ?</a>
    </p>

</div>

</body>
</html>