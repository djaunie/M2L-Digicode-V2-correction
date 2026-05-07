<?php
// mdp_oublie.php — Mot de passe oublié (CU : Envoi mail nouveau MDP)
require_once 'config.php';
require_once 'session.php';
startSecureSession();
$erreur = '';
$succes = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $erreur = "Veuillez saisir votre adresse mail.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse mail n'est pas valide.";
    } else {
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM mrbsusers WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();
        if (!$user) {
            $erreur = "Aucun compte n'est associé à cette adresse mail.";
        } else {
            $nouveauMdp = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#'), 0, 10);
            $hash       = password_hash($nouveauMdp, PASSWORD_BCRYPT);
            $upd = $pdo->prepare("UPDATE mrbsusers SET password = :h WHERE id = :id");
            $upd->execute([':h' => $hash, ':id' => $user['id']]);
            $sujet   = "M2L Digicode — Votre nouveau mot de passe";
            $corps   = "Bonjour {$user['name']},

Votre nouveau mot de passe : {$nouveauMdp}

Changez-le dès que possible.

Cordialement,
L'équipe M2L";
            $headers = "From: noreply@m2l.fr
Content-Type: text/plain; charset=UTF-8";
            if (mail($email, $sujet, $corps, $headers)) {
                $succes = "Un nouveau mot de passe a été envoyé à votre adresse mail.";
            } else {
                $erreur = "Impossible d'envoyer le mail. Contactez l'administrateur.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>M2L Digicode — Mot de passe oublié</title><link rel="stylesheet" href="assets/style.css"></head>
<body><div class="container">
<h1>Mot de passe oublié</h1>
<?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<?php if ($succes): ?><p class="succes"><?= htmlspecialchars($succes) ?></p><?php endif; ?>
<?php if (!$succes): ?>
<form method="post" action="mdp_oublie.php">
  <label for="email">Votre adresse mail :</label>
  <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
  <button type="submit">Envoyer un nouveau mot de passe</button>
  <a href="index.php" class="btn-retour">Retour</a>
</form>
<?php else: ?>
  <p><a href="index.php">Retour à la connexion</a></p>
<?php endif; ?>
</div></body></html>
