<?php
// include/fonctionVerifCode.php — V2
// Contient getCodeAlea() (V1), getCode(), verifExistCode() et la nouvelle genererDigicodeHex() (V2)

// Fonction de génération d'un code aléatoire (V1 — inchangée)
function getCodeAlea()
{
    $code    = "";
    $lettre  = "abcdefghijklmnopqrstuvwxyz";
    $position = 0;

    for ($i = 0; $i <= 9; $i++) {
        $position = rand(0, 25);
        if ($i == 3 || $i == 6 || $i == 9) {
            $code = $code . $position;
        } else {
            $code = $code . substr($lettre, $position, 1);
        }
    }
    return $code;
}

// Vérifie l'existence d'un code dans mrbs_users (V1 — inchangée)
function verifExistCode($code)
{
    global $cnx;
    $req_pre = $cnx->prepare("SELECT * FROM mrbs_users WHERE password = :code");
    $req_pre->bindValue(':code', $code, PDO::PARAM_STR);
    $req_pre->execute();
    $resultVerif = $req_pre->fetch(PDO::FETCH_OBJ);
    return ($resultVerif == true);
}

// Appelle getCodeAlea() jusqu'à obtenir un code unique (V1 — inchangée)
function getCode()
{
    $code = getCodeAlea();
    while (verifExistCode($code) == true) {
        $code = getCodeAlea();
    }
    return $code;
}

// NOUVEAU V2 — Génère un digicode hexadécimal aléatoire de 6 caractères (0-9, A-F)
// Inspirée de getCodeAlea() mais produit exclusivement des caractères hexadécimaux
function genererDigicodeHex()
{
    $hex = "0123456789ABCDEF";
    $digicode = "";
    for ($i = 0; $i < 6; $i++) {
        $digicode .= $hex[rand(0, 15)];
    }
    return $digicode;
}

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer des espaces après la balise de fin de script !!!!!!!!!!!!
