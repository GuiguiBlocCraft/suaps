<?php
function bddConnect(&$msgErreur) {
	require_once('config.php');
	$link = new mysqli(DB_HOST, DB_USER, DB_PSWD, DB_BASE, DB_PORT);

	if($link === false)
		return 'Could not connect : ' . mysqli_connect_error();
	// Retourne le pointer mysqli
	return $link;
}
?>