<?php
// envoyer_mail.php — Envoi de mail (CU : Envoyer un mail) — ADMIN
require_once 'config.php';
require_once 'session.php';
requireAdmin();
$pdo   = getPDO();
$users = $pdo->query("SELECT id, name, email FROM mrbs_users ORDER BY name")->fetchAll();
$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $destinataires = $_POST['destinataires'] ?? [];
    $objet         = trim($_POST['objet']    ?? '');
    $contenu       = trim($_POST['contenu']  ?? '');
    if (empty($destinataires) || $objet === '' || $contenu === '') {
        $erreur = "Tous les champs sont obligatoires (destinataire, objet, contenu).";
    } else {
        $headers = "From: admin@m2l.fr
Content-Type: text/plain; charset=UTF-8";
        $echecs  = [];
        foreach ($destinataires as $userId) {
            $userId = (int)$userId;
            $stmt   = $pdo->prepare("SELECT email, name FROM mrbsusers WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $dest = $stmt->fetch();
            if ($dest && filter_var($dest['email'], FILTER_VALIDATE_EMAIL)) {
                if (!mail($dest['email'], $objet, $contenu, $headers)) $echecs[] = $dest['name'];
            }
        }
        if (empty($echecs)) { header('Location: accueil.php?success=mail_envoye'); exit; }
        else $erreur = "Impossible d'envoyer le mail à : " . implode(', ', $echecs);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>M2L Digicode — Envoyer un mail</title><link rel="stylesheet" href="styles/style.css"></head>
<body><div class="container">
<h1>Envoyer un mail aux utilisateurs</h1>
<?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
<form method="post" action="envoyer_mail.php">
  <label>Destinataires :</label>
  <div class="checkbox-list">
    <?php foreach ($users as $user): ?>
      <label class="checkbox-item">
        <input type="checkbox" name="destinataires[]" value="<?= (int)$user['id'] ?>"
               <?= in_array($user['id'], $_POST['destinataires'] ?? []) ? 'checked' : '' ?>>
        <?= htmlspecialchars($user['name']) ?> — <?= htmlspecialchars($user['email']) ?>
      </label>
    <?php endforeach; ?>
  </div>
  <label for="objet">Objet :</label>
  <input type="text" id="objet" name="objet" required value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>">
  <label for="contenu">Contenu :</label>
  <textarea id="contenu" name="contenu" rows="8" required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
  <button type="submit">Envoyer</button>
  <a href="accueil.php" class="btn-retour">Retour</a>
</form>
</div></body></html>
