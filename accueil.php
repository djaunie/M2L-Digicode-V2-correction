<?php
// accueil.php — V2 : Consultation des digicodes par salle réservée ce jour
require_once 'config.php';
require_once 'session.php';
requireLogin();

$pdo = getPDO();
$aujourdhui_debut = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
$aujourdhui_fin   = mktime(23, 59, 59, date('n'), date('j'), date('Y'));

// Récupère les salles réservées aujourd'hui par l'utilisateur connecté
$stmt = $pdo->prepare("
    SELECT DISTINCT r.id AS room_id, r.room_name, rd.digicode
    FROM mrbs_entry e
    JOIN mrbs_room r  ON e.room_id = r.id
    LEFT JOIN mrbs_room_digicode rd ON r.id = rd.id
    WHERE e.create_by = :login
      AND e.start_time >= :debut
      AND e.start_time <= :fin
    ORDER BY e.start_time ASC
");
$stmt->execute([
    ':login' => $_SESSION['user_name'],
    ':debut' => $aujourdhui_debut,
    ':fin'   => $aujourdhui_fin,
]);
$salles = $stmt->fetchAll();

$msgs_erreur = ['acces_refuse' => "Accès refusé : droits insuffisants."];
$msgs_succes = [
    'mdp_change'        => "Mot de passe modifié avec succès.",
    'digicodes_changes' => "Digicodes régénérés avec succès.",
    'mail_envoye'       => "Mail envoyé avec succès.",
];
$messageErreur = $msgs_erreur[$_GET['error']   ?? ''] ?? '';
$messageSucces = $msgs_succes[$_GET['success'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode — Accueil</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<div class="container">
    <h1>Maison des Ligues — Digicode</h1>
    <p>Bienvenue, <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong>
       (<?= isAdmin() ? 'Administrateur' : 'Utilisateur' ?>)</p>

    <?php if ($messageErreur): ?>
        <p class="erreur"><?= htmlspecialchars($messageErreur) ?></p>
    <?php endif; ?>
    <?php if ($messageSucces): ?>
        <p class="succes"><?= htmlspecialchars($messageSucces) ?></p>
    <?php endif; ?>

    <h2>Vos réservations du jour — <?= date('d/m/Y') ?></h2>

    <?php if (empty($salles)): ?>
        <p class="info">Aucune réservation pour vous aujourd'hui.</p>
    <?php else: ?>
        <table class="table-salles">
            <thead>
                <tr>
                    <th>Salle</th>
                    <th>Code d'accès</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($salles as $salle): ?>
                <tr>
                    <td><?= htmlspecialchars($salle['room_name']) ?></td>
                    <td class="digicode-sm">
                        <?= $salle['digicode']
                            ? strtoupper(htmlspecialchars($salle['digicode']))
                            : '<em>Non défini</em>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <nav class="menu">
        <a href="changer_mdp.php">Changer mon mot de passe</a>
        <?php if (isAdmin()): ?>
            <a href="modifier_digicode.php">Modifier les digicodes</a>
            <a href="envoyer_mail.php">Envoyer un mail</a>
        <?php endif; ?>
        <a href="logout.php">Se déconnecter</a>
    </nav>
</div>
</body>
</html>
