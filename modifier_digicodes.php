<?php
// =============================================================================
// modifier_digicodes.php - Regeneration des digicodes de toutes les salles (V2)
// CU : Modifier les digicodes
// Role : l'administrateur peut regenerer automatiquement les digicodes de
//        TOUTES les salles en un seul clic (bouton OK)
// Acces : reserve aux administrateurs (requireAdmin)
// =============================================================================

require_once 'config.php';
require_once 'session.php';
require_once 'include/fonctions_digicode.php';

// Seul l'administrateur peut acceder a cette page
requireAdmin();

$pdo    = getPDO();
$salles = [];

// -----------------------------------------------------------------------------
// Lecture de toutes les salles avec leur digicode actuel
// LEFT JOIN pour inclure les salles sans digicode (nouvelles salles)
// -----------------------------------------------------------------------------
$stmt = $pdo->query("
    SELECT r.id, r.room_name, d.digicode
    FROM   mrbs_room AS r
    LEFT JOIN mrbs_room_digicode AS d ON d.id = r.id
    ORDER BY r.room_name
");
$salles = $stmt->fetchAll();

// -----------------------------------------------------------------------------
// Traitement du formulaire POST
// L'administrateur clique sur OK : generation et enregistrement des nouveaux codes
// Scenario nominal (CU) :
//   1. L'administrateur demande a modifier les digicodes (GET)
//   2. Le systeme affiche le bouton de validation
//   3. L'utilisateur clique sur OK (POST)
//   4. Le systeme genere et enregistre les nouveaux digicodes
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['action'])
    && $_POST['action'] === 'regenerer') {

    // Pour chaque salle, generer un code unique puis l'inserer ou le mettre a jour
    foreach ($salles as $salle) {

        $nouveauCode = getDigicodeUnique($pdo);

        // Verification de l'existence d'un enregistrement pour cette salle
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM mrbs_room_digicode WHERE id = :id");
        $stmtCheck->execute([':id' => $salle['id']]);
        $existe = (int)$stmtCheck->fetchColumn();

        if ($existe > 0) {
            // La salle a deja un digicode : on le met a jour
            $stmtUpd = $pdo->prepare("UPDATE mrbs_room_digicode SET digicode = :code WHERE id = :id");
            $stmtUpd->execute([':code' => $nouveauCode, ':id' => $salle['id']]);
        } else {
            // Nouvelle salle sans digicode : on insere
            // Note : pas d'AUTO_INCREMENT, l'id correspond a l'id de mrbs_room
            $stmtIns = $pdo->prepare("INSERT INTO mrbs_room_digicode (id, digicode) VALUES (:id, :code)");
            $stmtIns->execute([':id' => $salle['id'], ':code' => $nouveauCode]);
        }
    }

    // Redirection vers l'accueil avec message de confirmation (flash GET)
    header('Location: accueil.php?success=digicodes_modifies');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>M2L Digicode - Regenerer les digicodes</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<div class="container">

    <h1>Regenerer les digicodes des salles</h1>

    <p>
        Cette action remplace <strong>automatiquement</strong> le digicode de toutes les salles
        par un nouveau code hexadecimal aleatoire de 6 caracteres.
    </p>

    <!-- Tableau recapitulatif : salles et digicodes actuels -->
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
                    <?php if ($salle['digicode']): ?>
                        <?= htmlspecialchars(strtoupper($salle['digicode'])) ?>
                    <?php else: ?>
                        <em>Non defini</em>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulaire de confirmation - bouton OK avec alerte JavaScript -->
    <form method="post" action="modifier_digicodes.php"
          onsubmit="return confirm('Voulez-vous vraiment regenerer tous les digicodes ?');">
        <input type="hidden" name="action" value="regenerer">
        <button type="submit" class="btn-danger">OK - Regenerer tous les digicodes</button>
        <a href="accueil.php" class="btn-retour">Annuler</a>
    </form>

</div>

</body>
</html>
