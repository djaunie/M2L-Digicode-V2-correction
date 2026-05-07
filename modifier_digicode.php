<?php
// modifier_digicode.php — V2 : Régénération automatique des digicodes par salle (Admin)
require_once 'config.php';
require_once 'session.php';
require_once 'include/fonctionVerifCode.php';
requireAdmin();

$pdo = getPDO();
$messageSucces = '';
$messageErreur = '';

// Action : regénérer tous les digicodes
if (isset($_POST['regenerer'])) {
    try {
        // Récupère toutes les salles actives
        $stmt = $pdo->query("SELECT id, room_name FROM mrbs_room WHERE disabled = 0 ORDER BY room_name");
        $salles = $stmt->fetchAll();

        foreach ($salles as $salle) {
            $nouveauCode = genererDigicodeHex();
            // INSERT ou UPDATE (UPSERT) — la table peut déjà contenir une entrée
            $upsert = $pdo->prepare("
                INSERT INTO mrbs_room_digicode (id, digicode)
                VALUES (:id, :code)
                ON DUPLICATE KEY UPDATE digicode = :code2
            ");
            $upsert->execute([
                ':id'    => $salle['id'],
                ':code'  => $nouveauCode,
                ':code2' => $nouveauCode,
            ]);
        }
        header('Location: accueil.php?success=digicodes_changes');
        exit;
    } catch (PDOException $e) {
        $messageErreur = "Erreur lors de la régénération : " . $e->getMessage();
    }
}

// Affichage : liste de toutes les salles avec leurs digicodes actuels
$stmt = $pdo->query("
    SELECT r.id, r.room_name, rd.digicode
    FROM mrbs_room r
    LEFT JOIN mrbs_room_digicode rd ON r.id = rd.id
    WHERE r.disabled = 0
    ORDER BY r.room_name
");
$salles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode — Modifier les digicodes</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<div class="container">
    <h1>Maison des Ligues — Digicode</h1>
    <h2>Modifier les digicodes des salles</h2>

    <?php if ($messageErreur): ?>
        <p class="erreur"><?= htmlspecialchars($messageErreur) ?></p>
    <?php endif; ?>

    <table class="table-salles">
        <thead>
            <tr>
                <th>Salle</th>
                <th>Digicode actuel</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($salles as $salle): ?>
            <tr>
                <td><?= htmlspecialchars($salle['room_name']) ?></td>
                <td class="digicode-sm">
                    <?= $salle['digicode']
                        ? strtoupper(htmlspecialchars($salle['digicode']))
                        : '<em>Aucun</em>' ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <form method="post" action="modifier_digicode.php"
          onsubmit="return confirm('Voulez-vous vraiment régénérer tous les digicodes ?');">
        <button type="submit" name="regenerer">Régénérer tous les digicodes</button>
    </form>

    <a href="accueil.php" class="btn-retour">← Retour à l'accueil</a>
</div>
</body>
</html>
