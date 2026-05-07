<?php
// Projet TraceGPS
// fichier : modele/Outils.class.php
// Rôle : boite à outils de fonctions courantes proposées sous forme de méthodes statiques
// Dernière mise à jour : 6/11/2018 par JM CARTRON

// liste des méthodes statiques de cette classe (dans l'ordre alphabétique) :

// convertirEnDateFR($laDate) : reçoit une date US (aaaa-mm-jj) et fournit sa conversion au format Français (jj/mm/aaaa)
// convertirEnDateUS($laDate) : reçoit une date française (jj/mm/aaaa) et fournit sa conversion au format US (aaaa-mm-jj)
// corrigerDate($laDate) : reçoit une date et fournit cette date en remplaçant les "-" par des "/"
// corrigerPrenom($lePrenom) : reçoit un prénom et fournit ce prénom corrigé en mettant en majuscules le premier caractère, 
//    et le caractère qui suit un éventuel tiret, et en mettant en minuscules les autres caractères
// corrigerTelephone($leNumero) : reçoit un numéro et fournit ce numéro corrigé en le mettant sous la forme de 
//    5 groupes de 2 chiffres séparés par des points
// corrigerVille($laVille) : reçoit un nom de ville et fournit ce nom corrigé en le mettant en majuscules 
//    et en remplaçant les "SAINT" par "St"
// creerMdp() : génère et fournit un mot de passe aléatoire de 8 caractères (4 syllabes avec 1 consonne et 1 voyelle)
// envoyerMail ($adresseDestinataire, $sujet, $message, $adresseEmetteur) : envoie un mail à un destinataire
// estUnCodePostalValide($codePostalAvalider) : fournit true si le code postal reçu en paramètre est correct 
//    (il doit comporter 5 chiffres), false sinon
// estUneAdrMailValide($adrMailAvalider) : fournit true si l'adresse mail reçue en paramètre est correcte, false sinon
// estUneDateValide($laDateAvalider) : fournit true si la date reçue en paramètre est correcte 
//    (format jj/mm/aaaa ou bien jj-mm-aaaa), false sinon
// estUnNumTelValide($numTelAvalider) : fournit true si le n° de téléphone reçu en paramètre est correct 
//    (5 groupes de 2 chiffres éventuellement séparés), false sinon

// ce fichier est destiné à être inclus dans les pages PHP qui ont besoin des fonctions qu'il contient, avec l'instruction :
//     include_once ('Outils.class.php');               (si la page et la classe Outils sont dans le même dossier)
//     include_once ('../modele/Outils.class.php');     (si la page et la classe Outils sont dans des dossiers différents)

// ces méthodes statiques sont appelées avec la notation suivante :
//     Outils::methode(parametres);

// début de la classe Outils
class Outils
{
    // La fonction envoyerMail ($adresseDestinataire, $sujet, $message, $adresseEmetteur) envoie un mail à un destinataire
	// elle utilise le serveur le serveur OVH (sio.lyceedelassale.fr) pour envoyer le mail, en passant par un service web
	// retourne true si envoi correct, false en cas de problème d'envoi
	// Dernière mise à jour : 6/11/2018 par JM CARTRON
	public static function  envoyerMail ($adresseDestinataire, $sujet, $message, $adresseEmetteur)
	{
	    // transformation du message s'il contient des "&"
	    $message = str_replace("&", "$$", $message);
	    
	    // préparation de l'URL du service web avec ses paramètres
	    $urlService = "http://sio.lyceedelasalle.fr/tracegps/services/ServiceEnvoyerMail.php";
	    $urlService .= "?adresseDestinataire=" . $adresseDestinataire;
	    $urlService .= "&sujet=" . $sujet;
	    $urlService .= "&message=" . $message;
	    $urlService .= "&adresseEmetteur=" . $adresseEmetteur;
	    
	    // création d'un objet DOMDocument pour traiter un flux de données XML
	    $dom = new DOMDocument();
	    // chargement des données à partir de l'url du service web
	    if ( ! $dom->load($urlService )) return false;
	    // récupération du noeud XML correspondant à la balise <reponse>
	    $lesNoeuds = $dom->getElementsByTagName("reponse");
	    foreach ($lesNoeuds as $unNoeud)
	    {
	        if ($unNoeud->nodeValue == "true") return true;
            else return false;
	    }
	}
	
}