<?php
// =============================================================================
// include/fonctions_digicode.php - Fonctions metier pour la V2 du digicode
// Role : generation d'un digicode hexadecimal unique, verification de reservation
// =============================================================================

/**
 * Genere un digicode aleatoire de 6 caracteres hexadecimaux (0-9, A-F).
 * Inspire de getCodeAlea() de la V1, adapte au format hexadecimal.
 *
 * @return string  Digicode en majuscules (ex: "4F2A1B")
 */
function getDigicodeAlea(): string {
    $hex = '';
    for ($i = 0; $i < 6; $i++) {
        // On tire aleatoirement un caractere parmi les 16 valeurs hexadecimales
        $hex .= strtoupper(dechex(rand(0, 15)));
    }
    return $hex;
}

/**
 * Verifie si un digicode est deja utilise dans mrbs_room_digicode.
 *
 * @param  PDO    $pdo   Instance PDO
 * @param  string $code  Digicode a verifier
 * @return bool          true si le code existe deja
 */
function digicodeExisteDeja(PDO $pdo, string $code): bool {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mrbs_room_digicode WHERE digicode = :code");
    $stmt->execute([':code' => $code]);
    return (int)$stmt->fetchColumn() > 0;
}

/**
 * Genere un digicode unique (non present dans mrbs_room_digicode).
 *
 * @param  PDO    $pdo  Instance PDO
 * @return string       Digicode de 6 caracteres hexa garanti unique
 */
function getDigicodeUnique(PDO $pdo): string {
    do {
        $code = getDigicodeAlea();
    } while (digicodeExisteDeja($pdo, $code));
    return $code;
}

/**
 * Verifie si l'utilisateur connecte a une reservation valide AUJOURD'HUI
 * dans l'une des salles de la M2L, et retourne la salle + son digicode.
 *
 * La reservation est valide si elle couvre une partie de la journee en cours.
 *
 * @param  PDO    $pdo    Instance PDO
 * @param  string $login  Login de l'utilisateur (mrbs_users.name)
 * @return array|false    Tableau ['room_id', 'room_name', 'digicode'] ou false
 */
function getReservationAujourdhui(PDO $pdo, string $login): array|false {
    // Timestamps de debut et fin de la journee courante
    $debutJour = mktime(0,  0,  0,  (int)date('n'), (int)date('j'), (int)date('Y'));
    $finJour   = mktime(23, 59, 59, (int)date('n'), (int)date('j'), (int)date('Y'));

    $sql = "
        SELECT e.room_id, r.room_name, d.digicode
        FROM   mrbs_entry          AS e
        JOIN   mrbs_room           AS r ON r.id = e.room_id
        JOIN   mrbs_room_digicode  AS d ON d.id = r.id
        WHERE  e.create_by  = :login
          AND  e.start_time <= :fin_jour
          AND  e.end_time   >= :debut_jour
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':login'      => $login,
        ':fin_jour'   => $finJour,
        ':debut_jour' => $debutJour,
    ]);
    $result = $stmt->fetch();
    return $result ?: false;
}
