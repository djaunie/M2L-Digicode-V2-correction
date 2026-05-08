<?php
// =============================================================================
// accueil.php - Page d'accueil V2 (CU : Consulter le digicode)
// Role : affiche le digicode propre a la salle reservee pour aujourd'hui
//        ou indique qu'aucune reservation n'existe pour ce jour
// Acces : reserve aux utilisateurs connectes (requireLogin)
// =============================================================================

require_once 'config.php';
require_once 'session.php';
require_once 'include/fonctions_digicode.php';

// Verification de la connexion - redirige vers index.php si non connecte
requireLogin();

$pdo = getPDO();

// -----------------------------------------------------------------------------
// Recherche d'une reservation valide pour aujourd'hui
// Le login de l'utilisateur est stocke en session sous la cle 'user_login'
// -----------------------------------------------------------------------------
$reservation = getReservationAujourdhui($pdo, $_SESSION['user_login']);

// -----------------------------------------------------------------------------
// Messages flash (parametres GET) - affiches apres une redirection
// -----------------------------------------------------------------------------
$msgs_erreur = [
    'acces_refuse' => "Acces refuse : droits insuffisants.",
];
$msgs_succes = [
    'mdp_change'         => "Mot de passe modifie avec succes.",
    'digicodes_modifies' => "Digicodes regeneres avec succes.",
    'mail_envoye'        => "Mail envoye avec succes.",
];

$messageErreur = $msgs_erreur[$_GET['error']   ?? ''] ?? '';
$messageSucces = $msgs_succes[$_GET['success'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode - Accueil</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Maison des Ligues - Digicode</h1>

    <p>
        Bienvenue, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
        (<?= isAdmin() ? 'Administrateur' : 'Utilisateur' ?>)
        &nbsp;|&nbsp;
        <a href="logout.php">Se deconnecter</a>
    </p>

    <!-- Messages flash -->
    <?php if ($messageErreur): ?>
        <p class="erreur"><?= htmlspecialchars($messageErreur) ?></p>
    <?php endif; ?>
    <?php if ($messageSucces): ?>
        <p class="succes"><?= htmlspecialchars($messageSucces) ?></p>
    <?php endif; ?>

    <!-- ------------------------------------------------------------------ -->
    <!-- Bloc digicode : affiche uniquement si une reservation existe        -->
    <!-- ------------------------------------------------------------------ -->
    <?php if ($reservation): ?>
        <div class="digicode-box">
            <h2>Code d'acces - <?= htmlspecialchars($reservation['room_name']) ?></h2>
            <p class="digicode"><?= htmlspecialchars(strtoupper($reservation['digicode'])) ?></p>
            <p class="info">Votre reservation du <?= date('d/m/Y') ?> est confirmee.</p>
        </div>
    <?php else: ?>
        <div class="digicode-box digicode-box--vide">
            <h2>Aucune reservation aujourd'hui</h2>
            <p class="info">
                Vous n'avez pas de salle reservee pour le <?= date('d/m/Y') ?>.
                Le code d'acces est disponible uniquement en cas de reservation valide.
            </p>
        </div>
    <?php endif; ?>

    <!-- ------------------------------------------------------------------ -->
    <!-- Navigation selon le role                                            -->
    <!-- ------------------------------------------------------------------ -->
    <nav class="menu">
        <ul>
            <li><a href="changer_mdp.php">Modifier mon mot de passe</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="modifier_digicodes.php">Regenerer les digicodes</a></li>
                <li><a href="envoyer_mail.php">Envoyer un mail</a></li>
            <?php endif; ?>
        </ul>
    </nav>

</div>

</body>
</html>
