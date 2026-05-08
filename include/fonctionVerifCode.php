<?php

// Fonction à disposition qui génére un code aléatoire   -------------------------------------------------------------------
function getCodeAlea()
{
	// création du code aléatoire
	$code=""; // initialisation de la variable qui contiendra le code

	$lettre="abcdefghijklmnopqrstuvwxyz"; //constante contenant toutes les lettres qui constituront le code aléatoire

	$position=0;

	for ($i=0;$i<=9 ;$i++)
	{
		$position=rand(0,25); // on tire une position aléatoire	
	
		if($i==3||$i==6 ||$i==9) // en cas de nombre impair un chiffre sera ajouté
		{
			$code=$code.$position;
		}
	
		else //sinon on ajoute une lettre au code, tirée au hasard grâce à la fonction rand();	
		{
			$code=$code.substr( $lettre , $position,1);
		}
	}
	return $code; // on retourne le code généré
}

// Fonction à disposition qui vérifie l'existence d'un code passé en paramètres ---------------------------------------------		
function verifExistCode($code)
{
	global $cnx;
	// requête de vérification de l'existence du code aléatoire
	$req_pre = $cnx->prepare("SELECT * FROM mrbs_users WHERE password = :code");
	// liaison de la variable à la requête préparée
	$req_pre->bindValue(':code', $code, PDO::PARAM_STR);
	$req_pre->execute();
	//le résultat est récupéré sous forme d'objet
	$resultVerif=$req_pre->fetch(PDO::FETCH_OBJ);
	
	// si le code existe déjà
	if($resultVerif == true)
	{
		return true;
	}
	// si au contraire le code n'existe pas
	else
	{
		return false;
	}
}

// Fonction à disposition appellant la création et la vérification d'un code aléatoire ---------------------------
function getCode()
{
	$code=getCodeAlea(); 				// on génère un code aléatoire
	while (verifExistCode($code)==true) // tant que le code créé existe déjà alors on en génère un nouveau
	{
		$code=getCodeAlea(); // génération d'un nouveau code		
	}
	return $code; // on retourne donc un code aléatoire unique
}

// ATTENTION : on ne met pas de balise de fin de script pour ne pas prendre le risque
// d'enregistrer d'espaces après la balise de fin de script !!!!!!!!!!!!