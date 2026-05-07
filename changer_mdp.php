<?php
// changer_mdp.php — Modification du mot de passe (CU : Changer mdp)
require_once 'config.php';
require_once 'session.php';
requireLogin();
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ancien  = trim($_POST['ancien_mdp']  ?? '');
    $nouveau = trim($_POST['nouveau_mdp'] ?? '');
    $confirm = trim($_POST['confirm_mdp'] ?? '');
    if ($ancien === '' || $nouveau === '' || $confirm === '') {
        $erreur = "Tous les champs sont obligatoires.";
    } elseif ($nouveau !== $confirm) {
        $erreur = "Les deux nouveaux mots de passe ne correspondent pas.";
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT password FROM mrbs_users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch();
        $ancienOk = $user && (password_verify($ancien, $user['password']) || md5($ancien) === $user['password']);
        if (!$ancienOk) {
            $erreur = "L'ancien mot de passe est incorrect.";
        } elseif (password_verify($nouveau, $user['password']) || md5($nouveau) === $user['password']) {
            $erreur = "Le nouveau mot de passe doit être différent de l'ancien.";
        } else {
            $hash = password_hash($nouveau, PASSWORD_BCRYPT);
            $upd  = $pdo->prepare("UPDATE mrbsusers SET password = :h WHERE id = :id");
            $upd->execute([':h' => $hash, ':id' => $_SESSION['user_id']]);
            header('Location: accueil.php?success=mdp_change');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>M2L Digicode — Changer mon mot de passe</title><link rel="stylesheet" href="styles/style.css"></head>
<body><div class="container">
<h1>Changer mon mot de passe</h1>
<?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<form method="post" action="changer_mdp.php">
  <label for="ancien_mdp">Ancien mot de passe :</label>
  <input type="password" id="ancien_mdp" name="ancien_mdp" required>
  <label for="nouveau_mdp">Nouveau mot de passe :</label>
  <input type="password" id="nouveau_mdp" name="nouveau_mdp" required>
  <label for="confirm_mdp">Confirmer le nouveau mot de passe :</label>
  <input type="password" id="confirm_mdp" name="confirm_mdp" required>
  <button type="submit">Valider</button>
  <a href="accueil.php" class="btn-retour">Retour</a>
</form>
</div></body></html>
