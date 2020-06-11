<?php
session_start();

require_once('mysql.php');

if($_POST['type'] == 'precedent' || $_POST['type'] == 'suivant') {
	require_once('calendrier.php');
	$dateTime = new DateTime($_POST['date']);
}

switch($_POST['type']) {
	case 'precedent': // Date précédente
		$dateTime->sub(new DateInterval('P7D'));
		break;
	case 'suivant': // Date suivante
		$dateTime->add(new DateInterval('P7D'));
		break;
	case 'saisie_reservation': // Lors de l'appui sur le bouton 'Réserver'
		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Sélectionne tous les adhérents
		$sql = "SELECT * FROM adherents ORDER BY nom, prenom";
		if($result = $link->query($sql)) {
			$json = array();
			$json['datas'] = array();
			while($row = $result->fetch_assoc()) {
				$json['datas'][] = $row;
			}
		}

		// Retourne en JSON
		print json_encode($json);

		$link->close();
		break;
	case 'modification_joueur': // Lors de la modification d'un joueur
		$date_reservation = $_POST['date'];
		$no_joueur = $_POST['no_joueur'];
		$id_adherent = $_POST['id_adherent'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "SELECT id_reservation FROM reservation WHERE `date` = '$date_reservation'";

		if($result = $link->query($sql)) {
			if($result->num_rows > 0)
				$sql = "UPDATE reservation SET joueur" . $no_joueur . " = " . (($id_adherent == '') ? "NULL" : "'" . $id_adherent . "'") . " WHERE date = '$date_reservation'";
			else
				$sql = "INSERT INTO reservation (date, joueur" . $no_joueur . ") VALUES ('$date_reservation', '$id_adherent')";

			$result = $link->query($sql);
		}

		$link->close();
		break;
	case 'login':
		$login = $_POST['login'];
		$pswd = $_POST['pswd'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		$sql = "SELECT * FROM adherents WHERE login = '$login' AND mdp = '$pswd' AND ((membre_suaps_ok = 1 AND cotisation_payee_ok = 1) OR admin_ok = 1)";
		$result = $link->query($sql);
		if($result === false) {
			$link->close();
			exit;
		}

		if($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$_SESSION['userConnecte'] = $row['prenom'] . ' ' . $row['nom'];
			$_SESSION['idUserConnecte'] = $row['id_adherents'];
			$_SESSION['adminOk'] = $row['admin_ok'];
			print 'ok';
		} else {
			print 'ko';
		}

		$link->close();
		break;
	case 'deconnexion':
		session_destroy();
		print 'ok';
		break;
	case 'ajout_reservation':
		$date_reservation = $_POST['date'];
		$no_joueur = $_POST['no_joueur'];
		$firstDateDisplayed = $_POST['firstDateDisplayed'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		$nbReservations = 0;
		$dateCourante = new DateTime();

		// Pas plus de 2 réservations à l'avance
		for($i = 1; $i <= 4; $i++) {
			$sql = "SELECT Count(*) AS nb FROM reservation WHERE joueur$i = " . $_SESSION['idUserConnecte'] . " AND date >= '" . $dateCourante->format('Y-m-d') . "'";

			if($result = $link->query($sql)) {
				$row = $result->fetch_assoc();
				$nbReservations += $row['nb'];

				$result->close();
			}
		}

		if($nbReservations >= 2) {
			print 'ERREUR1';
			$link->close();
			exit;
		}

		// Récupère le nombre d'argents de l'adhérent
		$sql = "SELECT parcours_achetes FROM adherents WHERE id_adherents = " . $_SESSION['idUserConnecte'];
		if($result = $link->query($sql)) {
			if($result->num_rows > 0) {
				$row = $result->fetch_assoc();
				$parcoursAchetes = $row['parcours_achetes'] - 20;

				// Si l'adherent a assez, fait un débit
				if($parcoursAchetes >= 0) {
					$sql = "UPDATE adherents SET parcours_achetes = $parcoursAchetes WHERE id_adherents = " . $_SESSION['idUserConnecte'];
					$link->query($sql);
				} else {
					print 'ERREUR2';
					$link->close();
					exit;
				}
			}

			$result->close();
		}

		// Check si l'ID existe
		$sql = "SELECT id_reservation FROM reservation WHERE `date` = '$date_reservation'";
		if($result = $link->query($sql)) {
			if($result->num_rows > 0)
				$sql = "UPDATE reservation SET joueur" . $no_joueur . " = " . $_SESSION['idUserConnecte'] . " WHERE date = '$date_reservation'";
			else
				$sql = "INSERT INTO reservation (date, joueur" . $no_joueur . ") VALUES ('$date_reservation', '" . $_SESSION['idUserConnecte'] . "')";

			$result = $link->query($sql);

			require_once('calendrier.php');
			print construitCalendrier($firstDateDisplayed);
		}

		$link->close();
		break;
	case 'suppression_reservation':
		$date_reservation = $_POST['date'];
		$no_joueur = $_POST['no_joueur'];
		$firstDateDisplayed = $_POST['firstDateDisplayed'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "SELECT id_reservation FROM reservation WHERE `date` = '$date_reservation'";
		if($result = $link->query($sql)) {
			if($result->num_rows > 0) {
				$sql = "UPDATE reservation SET joueur" . $no_joueur . " = NULL WHERE date = '$date_reservation'";
				$result = $link->query($sql);
			}

			// Récupère le nombre d'annulations de l'adhérent
			$sql = "SELECT nb_annulation, parcours_achetes FROM adherents WHERE id_adherents = " . $_SESSION['idUserConnecte'];
			if($result = $link->query($sql)) {
				if($result->num_rows > 0) {
					$row = $result->fetch_assoc();

					$nbAnnulations = $row['nb_annulation'] + 1;
					$parcoursAchetes = $row['parcours_achetes'] + 20;

					$sql = "UPDATE adherents SET nb_annulation = $nbAnnulations WHERE id_adherents = " . $_SESSION['idUserConnecte'];
					$result = $link->query($sql);

					$sql = "UPDATE adherents SET parcours_achetes = $parcoursAchetes WHERE id_adherents = " . $_SESSION['idUserConnecte'];
					$result = $link->query($sql);
				}
			}

			require_once('calendrier.php');
			print construitCalendrier($firstDateDisplayed);
		}

		$link->close();
		break;
	case 'affichage':
		switch($_POST['contenu']) {
			case 'reservation':
				require_once('reservation_entete.php');
				break;
			case 'adherent':
				require_once('liste_adherents.php');
				break;
		}
		break;
	case 'suppression_adherent':
		$id = $_POST['id'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "DELETE FROM adherents WHERE id_adherents = $id";
		if($link->query($sql)) {
			print 'ok';
		}

		$link->close();
		break;
	case 'ajouter_adherent':
		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		$nom = $link->escape_string($_POST['nom']);
		$prenom = $link->escape_string($_POST['prenom']);
		$membre = $_POST['membre'];
		$cotisation = $_POST['cotisation'];
		$parcours = $_POST['parcours'];
		$administrateur = $_POST['administrateur'];
		$login = $_POST['login'];
		$mdp = $_POST['mdp'];

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "INSERT INTO adherents (nom, prenom, login, mdp, membre_suaps_ok, cotisation_payee_ok, parcours_achetes, admin_ok) VALUES ('$nom', '$prenom', '$login', '$mdp', '$membre', '$cotisation', '$parcours', '$administrateur')";
		if($link->query($sql)) {
			print 'ok';
		}

		$link->close();
		break;
	case 'modifier_adherent':
		$id = $_POST['id'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		$nom = $link->escape_string($_POST['nom']);
		$prenom = $link->escape_string($_POST['prenom']);
		$membre = $_POST['membre'];
		$cotisation = $_POST['cotisation'];
		$parcours = $_POST['parcours'];
		$administrateur = $_POST['administrateur'];
		$login = $_POST['login'];
		$mdp = $_POST['mdp'];

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "UPDATE adherents SET nom = '$nom', prenom = '$prenom', login = '$login', mdp = '$mdp', membre_suaps_ok = $membre, cotisation_payee_ok = $cotisation, parcours_achetes = $parcours, admin_ok = $administrateur WHERE id_adherents = $id";
		if($link->query($sql)) {
			print 'ok';
		}

		$link->close();
		break;
	case 'selectionne_adherent':
		$id = $_POST['id'];

		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$sql = "SELECT * FROM adherents WHERE id_adherents = $id";
		if($result = $link->query($sql)) {
			$row = $result->fetch_assoc();
			print json_encode($row);
		}

		$result->close();
		$link->close();
		break;
	case 'calcul_stat':
		$msgErreur = '';

		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		// Encode la BDD en UTF8
		$link->query('SET NAMES UTF8');

		// Check si l'ID existe
		$nbReservations = 0;
		$nbAnnulations = 0;
		$nbParcoursAchetes = 0;

		$dateCourante = new DateTime();

		for($i = 1; $i <= 4; $i++) {
			$sql = "SELECT Count(*) AS nb FROM reservation WHERE joueur$i = " . $_SESSION['idUserConnecte'] . " AND date >= '" . $dateCourante->format('Y') . "-01-01'";

			if($result = $link->query($sql)) {
				$row = $result->fetch_assoc();
				$nbReservations += $row['nb'];

				$result->close();
			}
		}

		$sql = "SELECT parcours_achetes, nb_annulation FROM adherents WHERE id_adherents = " . $_SESSION['idUserConnecte'];

		if($result = $link->query($sql)) {
			$row = $result->fetch_assoc();
			$nbAnnulations = $row['nb_annulation'];
			$nbParcoursAchetes = $row['parcours_achetes'] / 20;

			$result->close();
		}

		$data['nbReservations'] = $nbReservations;
		$data['nbAnnulations'] = $nbAnnulations;
		$data['nbParcoursAchetes'] = $nbParcoursAchetes;

		print json_encode($data);

		$link->close();
		break;
}

if($_POST['type'] == 'precedent' || $_POST['type'] == 'suivant')
	print construitCalendrier($dateTime->format('Y-m-d'));
?>