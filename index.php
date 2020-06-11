<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>Réservation Golf - SUAPS</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, shrink-to-fit=no">
	<link type="text/css" rel="stylesheet" href="css/styles.css" />
	<script type="text/javascript" src="librairie/jquery.js"></script>
	<script type="text/javascript" src="sources/monjquery.js"></script>
	<link rel="icon" href="golfeur.ico" />
</head>


<body>
<?php
if(isset($_SESSION['userConnecte']))
	require_once('sources/principal.php');
else
	require_once('sources/login.php');
?>
</body>
</html>