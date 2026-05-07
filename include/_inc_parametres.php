<?php
// ce fichier est destiné à être inclus dans les pages PHP qui ont besoin de se connecter à une base de données
// 2 possibilités pour inclure ce fichier :
//     include_once ('_inc_parametres.php');
//     require_once ('_inc_parametres.php');
// Modifié le 26/1/2021

// paramètres de connexion de l'application cliente au SGBD MySQL
$PARAM_HOTE = "localhost";		// nom du serveur de données : localhost 
$PARAM_port = '3306';
$PARAM_USER = "root";			// nom de l'utilisateur
$PARAM_PWD  = "";				// son mot de passe					
$PARAM_BDD  = '2026_mrbs_99';			// nom de la base de données	

// Autres paramètres -----------------------------------------------------------------------------------------
global $DELAI_DIGICODE, $ADR_MAIL_EMETTEUR;
// valeur du délai (en secondes) pendant lequel le digicode est accepté avant l'heure de début de réservation
// ou après l'heure de fin de réservation
$DELAI_DIGICODE = 3600;			// 3600 sec ou 1 h
// adresse de l'émetteur lors d'un envoi de courriel
$ADR_MAIL_EMETTEUR = 'delasalle.sio.crib@gmail.com';
// sio1sio2
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!