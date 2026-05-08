<?php
// =============================================================================
// envoyer_mail.php — Envoi de mail (CU : Envoyer un mail)
// Rôle : permet à un administrateur d'envoyer un mail à un ou plusieurs utilisateurs
// Envoi : via Outils::envoyerMail() — service web OVH du Lycée de la Salle
// Accès : réservé aux administrateurs (requireAdmin)
// =============================================================================

require_once 'config.php';
require_once 'session.php';

// Classe utilitaire : fournit la méthode statique envoyerMail()
// Elle passe par le service web sio.lyceedelasalle.fr pour envoyer le mail
include_once 'include/Outils.class.php';

// Vérification que l'utilisateur est administrateur, sinon redirection
requireAdmin();

// -----------------------------------------------------------------------------
// Chargement de la liste des utilisateurs pour le menu déroulant
// -----------------------------------------------------------------------------
$pdo   = getPDO();
$users = $pdo->query("SELECT id, name, email FROM mrbs_users ORDER BY name")->fetchAll();

$erreur = '';

// -----------------------------------------------------------------------------
// Traitement du formulaire (soumission POST)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération des champs du formulaire
    $destinataires = $_POST['destinataires'] ?? [];       // tableau d'IDs sélectionnés
    $objet         = trim($_POST['objet']    ?? '');
    $contenu       = trim($_POST['contenu']  ?? '');

    // Validation : au moins un destinataire, objet et contenu obligatoires
    if (empty($destinataires) || $objet === '' || $contenu === '') {
        $erreur = "Tous les champs sont obligatoires (destinataire, objet, contenu).";

    } else {

        // Adresse émettrice utilisée par le service web
        $emetteur = "admin@m2l.fr";

        // Liste des destinataires en échec (pour affichage d'erreur éventuel)
        $echecs = [];

        // Envoi du mail à chaque destinataire sélectionné
        foreach ($destinataires as $userId) {

            // Cast en entier pour éviter toute injection SQL
            $userId = (int) $userId;

            // Récupération de l'adresse e-mail de l'utilisateur en BDD
            $stmt = $pdo->prepare("SELECT email, name FROM mrbs_users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $dest = $stmt->fetch();

            // Vérification que l'adresse e-mail est valide avant l'envoi
            if ($dest && filter_var($dest['email'], FILTER_VALIDATE_EMAIL)) {
                // Envoi via le service web OVH (retourne true si succès, false sinon)
                $ok = Outils::envoyerMail($dest['email'], $objet, $contenu, $emetteur);
                if (!$ok) {
                    // En cas d'échec, on note le nom de l'utilisateur concerné
                    $echecs[] = $dest['name'];
                }
            }
        }

        // Si aucun échec : redirection vers l'accueil avec message de succès
        if (empty($echecs)) {
            header('Location: accueil.php?success=mail_envoye');
            exit;
        } else {
            // Sinon : affichage des noms des destinataires en échec
            $erreur = "Impossible d'envoyer le mail à : " . implode(', ', $echecs);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode — Envoyer un mail</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Envoyer un mail aux utilisateurs</h1>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <form method="post" action="envoyer_mail.php">

        <!-- Menu déroulant multiple : Ctrl (ou Cmd) pour sélectionner plusieurs utilisateurs -->
        <label for="destinataires">Destinataires :</label>
        <select id="destinataires"
                name="destinataires[]"
                multiple
                size="6"
                required>
            <option value="" disabled>-- Sélectionnez un ou plusieurs utilisateurs --</option>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int) $user['id'] ?>"
                    <?= in_array($user['id'], $_POST['destinataires'] ?? []) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?> — <?= htmlspecialchars($user['email']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Maintenez <kbd>Ctrl</kbd> (ou <kbd>Cmd</kbd> sur Mac) pour sélectionner plusieurs destinataires.</small>

        <label for="objet">Objet :</label>
        <input type="text"
               id="objet"
               name="objet"
               required
               value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>">

        <label for="contenu">Contenu :</label>
        <textarea id="contenu"
                  name="contenu"
                  rows="8"
                  required><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>

        <button type="submit">Envoyer</button>
        <a href="accueil.php" class="btn-retour">Retour</a>

    </form>

</div>

</body>
</html>