<?php
function redirect($url, $time=3)
{
	//On vérifie si aucune entête n'a déjà été envoyée
	if (!headers_sent())  {
		header("refresh: $time;url=$url"); // on redirige avec header si une entête a déjà été envoyée
		exit;
	}
	else
	{
		echo '<meta http-equiv="refresh" content="',$time,';url=',$url,'">'; // sinon on redirige avec un entête
	}
}
?>