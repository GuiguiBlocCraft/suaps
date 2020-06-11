<?php
function construitCalendrier($dateCourante) {
	require_once('mysql.php');

	// Connection à la BDD
	$link = bddConnect($msgErreur);
	if($link == false) {
		print $msgErreur;
		exit;
	}

	$link->query('SET NAMES UTF8');

	$html = '';

	$listeJours = rendJoursCalendrier($dateCourante);
	$listeJours = json_decode($listeJours);

	$sql = "SELECT * FROM adherents";
	$result = $link->query($sql);

	if($result === false) {
		$link->close();
		exit;
	}

	$adherents = array();
	while($row = $result->fetch_assoc()) {

		$adherents[$row['id_adherents']] = strtoupper($row['nom']) . ' ' . $row['prenom'];
	}


	$dateDebut = $listeJours[0]->dateBdd;
	$dateFin = $listeJours[count($listeJours) - 1]->dateBdd;

	$sql = "SELECT * FROM reservation WHERE date >= '$dateDebut' AND date <= '$dateFin'";
	$result = $link->query($sql);

	if($result === false) {
		$link->close();
		exit;
	}

	$datas = array();

	while($row = $result->fetch_assoc()) {
		$datas[$row['date']] = array(
			'id_joueur1' => (isset($row['joueur1'])) ? $row['joueur1'] : '',
			'id_joueur2' => (isset($row['joueur2'])) ? $row['joueur2'] : '',
			'id_joueur3' => (isset($row['joueur3'])) ? $row['joueur3'] : '',
			'id_joueur4' => (isset($row['joueur4'])) ? $row['joueur4'] : ''
		);
	}

	$result->close();

	foreach($listeJours as $objDate) {
		$html .= '<tr date-bdd="' . $objDate->dateBdd . '"';
		if($objDate->jourSemaine == 0 || $objDate->jourSemaine == 6)
			$html .= ' class="weekend"';
		$html .= '>';
		$html .= '<td>' . $objDate->dateAffichee . '</td>';

		for($i = 1; $i <= 4; $i++) {
			$classe = '';
			$nom = '';
			if(isset($datas[$objDate->dateBdd]) && $datas[$objDate->dateBdd]['id_joueur' . $i] != '' && isset($adherents[$datas[$objDate->dateBdd]['id_joueur' . $i]])) {
				$nom =  $adherents[$datas[$objDate->dateBdd]['id_joueur' . $i]];
				if($datas[$objDate->dateBdd]['id_joueur' . $i] == $_SESSION['idUserConnecte'])
					$classe = ' class="userConnecte"';
			}

			$html .= '<td no-joueur="' . $i . '"' . $classe . ' id_adherent="' . ((isset($datas[$objDate->dateBdd])) ? $datas[$objDate->dateBdd]['id_joueur' . $i] : '') . '"><span style="width:80%; display:block; float:left;">'. $nom . '</span></td>';
		}

		if($_SESSION['adminOk'] == 1)
			$html .= '<td><img id="acces_reservation" src="images/locked.png" /></td>';

		$html .= '</tr>';
	}

	$link->close();
	print $html;
}

function rendJoursCalendrier($date) {
	$jourCourant = rendSamediPrecedent($date);
	$jourSemaine = array('Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi');

	$listeJours = array();
	$listeJours[0]['dateBdd'] = $jourCourant->format('Y-m-d');
	$listeJours[0]['jourSemaine'] = 0;
	$listeJours[0]['dateAffichee'] = $jourSemaine[0] . ' ' . $jourCourant->format('j') . '/' . $jourCourant->format('n');

	for($i = 1; $i <= 13; $i++) {
		$jourCourant->add(new DateInterval('P1D'));
		$strDate = getdate(strtotime($jourCourant->format('Y-m-d')));

		$listeJours[$i]['dateBdd'] = $jourCourant->format('Y-m-d');
		$listeJours[$i]['jourSemaine'] = $strDate['wday'];
		$listeJours[$i]['dateAffichee'] = $jourSemaine[$strDate['wday']] . ' ' . $jourCourant->format('j') . '/' . $jourCourant->format('n');
	}

	return json_encode($listeJours);
}

function rendSamediPrecedent($date) {
	$strDate = getdate(strtotime($date));
	if($strDate['wday'] == 0) {
		return new DateTime($date);
	} else {
		// Nous donne l'écart
		$ecartJour = $strDate['wday'];
		$dateTime = new DateTime($date);
		// Donne un interval de l'écart de jours
		$dateTime->sub(new DateInterval('P' . $ecartJour . 'D'));
		return $dateTime;
	}
}
?>