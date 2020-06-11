<div id="panneau_haut">
	<img class="logo" src="images/suaps_logo.jpg">
	<p class="titre">Réservations golf de la Wantzenau</p>
	<img class="golfeur" src="images/golfeur.png">
	<p id="connecte" <?php ($_SESSION['adminOk'] == 1 ? print 'admin="1"' : '') ?> idUserConnecte="<?php print $_SESSION['idUserConnecte'] ?>">Connecté : <strong><?php print $_SESSION['userConnecte'] ?></strong> <input type="button" id="deconnexion" value="Déconnexion"></p>
</div>
<div id="panneau_gauche">
	<div id="menu">
		<input type="button" id="lienReservation" value="Réservations">
		<input type="button" id="lienAdherent" value="Adhérents">
		<div id="statistiques">
			<span id="titre">Statistiques</span>
			<p><span>Réservations : </span><span id="nbReservations">0</span></p>
			<p><span>Annulations : </span><span id="nbAnnulations">0</span></p>
			<p><span>Parcours : </span><span id="nbParcours">0</span></p>
		</div>
	</div>
</div>
<div id="panneau_central">
<?php
require_once('reservation_entete.php');
?>
</div>