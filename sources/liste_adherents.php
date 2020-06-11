<p id="titre">Liste des adhérents</p>
<table id="liste_adherents">
	<thead>
		<tr>
			<th>Nom</th>
			<th>Prénom</th>
			<th>Membre</th>
			<th>Cottisation payée</th>
			<th><img src="images/trash.png" /></th>
			<th><img src="images/modifier.png" /></th>
		</tr>
	</thead>
	<tbody>
		<input type="button" id="ajouterAdherent" value="Ajouter adhérent">
		<?php
		// Connection à la BDD
		$link = bddConnect($msgErreur);
		if($link == false) {
			print $msgErreur;
			exit;
		}

		$link->query('SET NAMES UTF8');

		$html = '';

		$sql = "SELECT * FROM adherents ORDER BY nom, prenom";
		if($result = $link->query($sql)) {
			while($row = $result->fetch_assoc()) {
				if($row['nom'] == 'admin') // N'affiche pas le compte admin
					continue;
				$html .= '<tr idFiche="' . $row['id_adherents'] . '">';
				$html .= '<td>' . strtoupper($row['nom']) . '</td>';
				$html .= '<td>' . $row['prenom'] . '</td>';
				$html .= '<td style="text-align:center;">' . (($row['membre_suaps_ok'] == 1) ? '<img src="images/check.png" />' : '') . '</td>';
				$html .= '<td style="text-align:center;">' . (($row['cotisation_payee_ok'] == 1) ? '<img src="images/check.png" />' : '') . '</td>';
				$html .= '<td style="text-align:center;"><img id="supprimer" src="images/trash.png" /></td>';
				$html .= '<td style="text-align:center;"><img id="modifier" src="images/modifier.png" /></td>';
				$html .= '</tr>';
			}
		}

		$link->close();

		print $html;
		?>
	</tbody>
</table>