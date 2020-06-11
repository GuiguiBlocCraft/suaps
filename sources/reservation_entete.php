<p id="titre">Réservations</p>
<div id="navigation">
	<input type="button" id="precedent" value="Précédent">
	<input type="button" id="suivant" value="Suivant">
</div>
<table id="reservation">
	<thead>
		<tr>
			<th>Mois</th>
			<th>Joueur 1</th>
			<th>Joueur 2</th>
			<th>Joueur 3</th>
			<th>Joueur 4</th>
			<?php
			if($_SESSION['adminOk'] == 1)
				print '<th>Réserver</th>';
			?>
		</tr>
	</thead>
	<tbody>
		<?php
		require_once('calendrier.php');
		$dateCourante = new DateTime();
		print construitCalendrier($dateCourante->format('Y-m-d'));
		?>
	</tbody>
</table>