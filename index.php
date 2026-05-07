<?php
// index.php — Authentification (CU : Se connecter)
require_once 'config.php';
require_once 'session.php';
startSecureSession();
if (isLoggedIn()) { header('Location: accueil.php'); exit; }
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp   = trim($_POST['mdp']   ?? '');
    if ($login === '' || $mdp === '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM mrbs_users WHERE name = :login LIMIT 1");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();
        $mdpOk = false;
        if ($user) {
            if (password_verify($mdp, $user['password'])) {
                $mdpOk = true;
            } elseif (md5($mdp) === $user['password']) {
                $hash = password_hash($mdp, PASSWORD_BCRYPT);
                $upd  = $pdo->prepare("UPDATE mrbs_users SET password = :h WHERE id = :id");
                $upd->execute([':h' => $hash, ':id' => $user['id']]);
                $mdpOk = true;
            }
        }
        if (!$user || !$mdpOk) {
            $erreur = "Nom ou mot de passe incorrect.";
        } else {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['role']       = ($user['level'] >= 2) ? 'admin' : 'user';
            header('Location: accueil.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>M2L Digicode — Connexion</title><link rel="stylesheet" href="styles/style.css"></head>
<body><div class="container">
<h1>Maison des Ligues — Digicode</h1><h2>Connexion</h2>
<?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<form method="post" action="index.php">
  <label for="login">Identifiant :</label>
  <input type="text" id="login" name="login" required value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
  <label for="mdp">Mot de passe :</label>
  <input type="password" id="mdp" name="mdp" required>
  <button type="submit">Se connecter</button>
</form>
<p style="margin-top:1rem"><a href="mdp_oublie.php">Mot de passe oublié ?</a></p>
</div></body></html>
