<?php
// =============================================================================
// mdp_oublie.php — Mot de passe oublié (CU : Envoi mail nouveau MDP)
// Rôle : génère un nouveau mot de passe aléatoire et l'envoie par mail à l'utilisateur
// Méthode : hachage MD5 du nouveau mot de passe avant enregistrement en BDD
// Envoi : via Outils::envoyerMail() — service web OVH du Lycée de la Salle
// Accès : public (non connecté)
// =============================================================================

require_once 'config.php';
require_once 'session.php';

// Classe utilitaire : fournit la méthode statique envoyerMail()
// Elle passe par le service web sio.lyceedelasalle.fr pour envoyer le mail
include_once 'include/Outils.class.php';

// Démarrage sécurisé de la session
startSecureSession();

$erreur = '';
$succes = '';

// -----------------------------------------------------------------------------
// Traitement du formulaire (soumission POST)
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Récupération et nettoyage de l'adresse mail saisie
    $email = trim($_POST['email'] ?? '');

    // Validation 1 : champ obligatoire
    if ($email === '') {
        $erreur = "Veuillez saisir votre adresse mail.";

    // Validation 2 : format e-mail valide
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "L'adresse mail n'est pas valide.";

    } else {

        // Recherche de l'utilisateur en BDD par son adresse mail
        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM mrbs_users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Aucun compte trouvé pour cette adresse
        if (!$user) {
            $erreur = "Aucun compte n'est associé à cette adresse mail.";

        } else {

            // Génération d'un nouveau mot de passe aléatoire de 10 caractères
            // (lettres, chiffres et caractères spéciaux, sans caractères ambigus)
            $nouveauMdp = substr(str_shuffle('ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#'), 0, 10);

            // Hachage MD5 du nouveau mot de passe avant enregistrement
            $hash = md5($nouveauMdp);

            // Mise à jour du mot de passe en BDD
            $upd = $pdo->prepare("UPDATE mrbs_users SET password = :h WHERE id = :id");
            $upd->execute([':h' => $hash, ':id' => $user['id']]);

            // Préparation du mail à envoyer à l'utilisateur
            $sujet = "M2L Digicode — Votre nouveau mot de passe";
            $corps = "Bonjour {$user['name']},\n\nVotre nouveau mot de passe : {$nouveauMdp}\n\nChangez-le dès que possible.\n\nCordialement,\nL'équipe M2L";

            // Adresse émettrice utilisée par le service web
            $emetteur = "noreply@m2l.fr";

            // Envoi via le service web OVH (retourne true si succès, false sinon)
            $ok = Outils::envoyerMail($email, $sujet, $corps, $emetteur);

            if ($ok) {
                $succes = "Un nouveau mot de passe a été envoyé à votre adresse mail.";
            } else {
                // Échec du service web OVH
                $erreur = "Impossible d'envoyer le mail. Contactez l'administrateur.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode — Mot de passe oublié</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Mot de passe oublié</h1>

    <?php if ($erreur): ?>
        <p class="erreur"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <?php if ($succes): ?>
        <p class="succes"><?= htmlspecialchars($succes) ?></p>
    <?php endif; ?>

    <?php if (!$succes): ?>
        <!-- Formulaire affiché tant que le mail n'a pas été envoyé avec succès -->
        <form method="post" action="mdp_oublie.php">

            <label for="email">Votre adresse mail :</label>
            <input type="email"
                   id="email"
                   name="email"
                   required
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <button type="submit">Envoyer un nouveau mot de passe</button>
            <a href="index.php" class="btn-retour">Retour</a>

        </form>
    <?php else: ?>
        <!-- Succès : formulaire masqué, lien de retour vers la connexion -->
        <p><a href="index.php">Retour à la connexion</a></p>
    <?php endif; ?>

</div>

</body>
</html>