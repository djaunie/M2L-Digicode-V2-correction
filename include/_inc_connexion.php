<?php
// Essai de connexion avec les paramètres ----------------------------------------------------------------------------------------
try
{
	$cnx = new PDO('mysql:host='.$PARAM_HOTE.';port='.$PARAM_port.';dbname='.$PARAM_BDD,$PARAM_USER,$PARAM_PWD);
}
catch (Exception $e)
{
	// Envoi d'un message en cas de problème de connexion avec les paramètres ----------------------------------------------------------------------------------------
	echo 'Erreur : '.$e->getMessage().'</br/>';
	echo 'N° : '.$e->getCode();
}	
					
// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!