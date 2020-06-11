$(document).ready(function() {
	calculStat();

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#precedent', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'date=' + $('#reservation tbody tr:first').attr('date-bdd') + '&type=precedent'
		});

		ajax.done(function(data) {
			$('#reservation tbody').html(data);
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#suivant', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'date=' + $('#reservation tbody tr:first').attr('date-bdd') + '&type=suivant'
		});

		ajax.done(function(data) {
			$('#reservation tbody').html(data);
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', 'tr #acces_reservation', function(event) {
		if($(this).attr('src') == 'images/unlocked.png') {
			$(this).attr('src', 'images/locked.png');

			$(this).closest('tr').find('td').each(function(index) {
				if(index > 0 && index < 5) {
					var nom = $(this).find('select option:selected').text();
					var id = $(this).find('select').val();

					$(this).find('select').remove();
					$(this).text(nom);
					$(this).attr('id_adherent', id);

					if($('#connecte').attr('idUserConnecte') == id)
						$(this).addClass('userConnecte');
					else
						$(this).removeClass('userConnecte');
				}
			});
		} else {
			$(this).attr('src', 'images/unlocked.png');

			var tr = $(this).closest('tr');
			var td = tr.find('td');

			var ajax = $.ajax({
				url: 'sources/ajax.php',
				type: 'POST',
				data: 'type=saisie_reservation'
			});

			var self = $(this);

			ajax.done(function(data) {
				data = JSON.parse(data);
				noJoueur = 1;

				self.closest('tr').find('td').each(function(index) {
					if(index > 0 && index < 5) {
						// Création de la combo
						var html = '<select no-joueur="' + (noJoueur++) + '" style="width:100%;">';

						html += '<option></option>';

						for(var i = 0; i < data.datas.length; i++) {
							html += '<option value="' + data.datas[i].id_adherents + '">' + data.datas[i].nom.toUpperCase() + ' ' + data.datas[i].prenom + '</option>';
						}

						html +=  '</select>';

						$(this).empty();
						$(this).append(html);
						// Sélectionne dans le select l'id_adherent
						$(this).find('select').val($(this).attr('id_adherent'));
					}
				});
			});
		}
	});

//-----------------------------------------------------------------------------
	$(document).on('change', '#reservation select', function(event) {
		var self = $(this);

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=modification_joueur&date=' + self.closest('tr').attr('date-bdd') + '&no_joueur=' + self.attr('no-joueur') + '&id_adherent=' + self.val()
		});

		ajax.done(function(data) {
			console.log(data);
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#page_connexion #valider', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=login&login=' + $('#page_connexion #login').val() + '&pswd=' + $('#page_connexion #password').val()
		});

		ajax.done(function(data) {
			if(data == 'ok')
				window.location = 'index.php';
			else {
				$('#page_connexion').find('p').remove();
				$('#page_connexion').append('<p style="color:red;">Login ou mot de passe erroné.</p>');
			}
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#deconnexion', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=deconnexion'
		});

		ajax.done(function(data) {
			if(data == 'ok')
				window.location = 'index.php';
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('mouseenter', '#reservation tbody td', function(event) {
		if(typeof($('#connecte').attr('admin')) != 'undefined')
			return;

		var dateReservation = $(this).closest('tr').attr('date-bdd');

		var d = new Date();
		var month = '' + (d.getMonth() + 1);
		var day = '' + d.getDate();
		var year = '' + d.getFullYear();

		if(month.length < 2)
			month = '0' + month;
		if(day.length < 2)
			day = '0' + day;

		var dateCourante = year + '-' + month + '-' + day;

		if(dateReservation <= dateCourante)
			return;

		if(typeof($(this).attr('id_adherent')) != 'undefined' && $(this).attr('id_adherent').length == 0)
			$(this).append('<img id="ajouteReservation" src="images/plus.png" />');
		else if($(this).attr('id_adherent') == $('#connecte').attr('idUserConnecte'))
			$(this).append('<img id="supprimeReservation" src="images/trash.png" />');
	});

//-----------------------------------------------------------------------------
	$(document).on('mouseleave', '#reservation tbody td', function(event) {
		$(this).find('#ajouteReservation').remove();
		$(this).find('#supprimeReservation').remove();
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#ajouteReservation', function(event) {
		var self = $(this);
		var firstDateDisplayed = self.closest('#reservation').find('tbody tr:first').attr('date-bdd');

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=ajout_reservation&date=' + self.closest('tr').attr('date-bdd') + '&no_joueur=' + self.closest('td').attr('no-joueur') + '&firstDateDisplayed=' + firstDateDisplayed
		});

		ajax.done(function(data) {
			if(data == 'ERREUR1')
				formMessage('Maximum deux réservations autorisées à l\'avance.');
			else if(data == 'ERREUR2')
				formMessage('Votre crédit parcours est épuisé.');
			else if(data.length > 0) {
				$('#reservation tbody').html(data);
				calculStat();
			}
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#supprimeReservation', function(event) {
		var self = $(this);
		var firstDateDisplayed = self.closest('#reservation').find('tbody tr:first').attr('date-bdd');

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=suppression_reservation&date=' + self.closest('tr').attr('date-bdd') + '&no_joueur=' + self.closest('td').attr('no-joueur') + '&firstDateDisplayed=' + firstDateDisplayed
		});

		ajax.done(function(data) {
			$('#reservation tbody').html(data);
			calculStat();
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#lienReservation', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=affichage&contenu=reservation'
		});

		ajax.done(function(data) {
			$('#panneau_central').html(data);
			$('#panneau_central').focus();
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#lienAdherent', function(event) {
		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=affichage&contenu=adherent'
		});

		ajax.done(function(data) {
			$('#panneau_central').html(data);
			$('#panneau_central').focus();
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#liste_adherents tr #supprimer', function(event) {
		var self = $(this).closest('tr');

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=suppression_adherent&id=' + self.attr('idFiche')
		});

		ajax.done(function(data) {
			if(data == 'ok')
				self.remove();
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#liste_adherents tr #modifier', function(event) {
		var self = $(this).closest('tr');

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=selectionne_adherent&id=' + self.attr('idFiche')
		});

		ajax.done(function(data) {
			var obj = JSON.parse(data);
			formAdherent();
			$('#formAdherent').attr('idFiche', self.attr('idFiche'));
			$('#formAdherent #nom').val(obj.nom);
			$('#formAdherent #prenom').val(obj.prenom);
			$('#formAdherent #membre').val(obj.membre_suaps_ok);
			$('#formAdherent #cotisation').val(obj.cotisation_payee_ok);
			$('#formAdherent #parcours').val(obj.parcours_achetes);
			$('#formAdherent #login').val(obj.login);
			$('#formAdherent #mdp').val(obj.mdp);
			$('#formAdherent #administrateur').val(obj.admin_ok);
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#ajouterAdherent', function(event) {
		formAdherent();
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#formAdherent #ok', function(event) {
		var data = '';
		var parcours = $('#parcours').val() % 20;

		if(parcours > 0) { // Check si le montant entré n'est pas un multiple de 20
			let objErreur = $('#formAdherent #erreur');
			objErreur.text('Le montant renseigné n\'est pas un multiple de 20.');
			return;
		}

		$(this).closest('#formAdherent').find('table :input').each(function() {
			if(data.length > 0)
				data += '&';

			data += $(this).attr('id') + '=' + encodeURI($(this).val());
		});

		if(typeof($(this).closest('#formAdherent').attr('idFiche') != 'undefined')) {
			data += '&id=' + $(this).closest('#formAdherent').attr('idFiche');
			var updateOk = true;
		}

		var ajax = $.ajax({
			url: 'sources/ajax.php',
			type: 'POST',
			data: 'type=' + ((typeof(updateOk) != 'undefined') ? 'modifier_adherent' : 'ajouter_adherent') + '&' + data
		});

		ajax.done(function(data) {
			$('#lienAdherent').trigger('click');
			calculStat();
		});
	});

//-----------------------------------------------------------------------------
	$(document).on('click touchstart', '#formAdherent #annuler', function(event) {
		$('#formAdherent').remove();
	});

//-----------------------------------------------------------------------------
	$(document).on('input', '#formAdherent #parcours', function(event) {
		$('#formAdherent #erreur').text('');
	});
});

function formAdherent() {
	if($('#formAdherent').length > 0)
		return;

	var html = '<div id="formAdherent">';
	html += '<table>';
	html += '<tr><td>Nom : </td><td><input type="text" id="nom" /></td></tr>';
	html += '<tr><td>Prénom : </td><td><input type="text" id="prenom" /></td></tr>';
	html += '<tr><td>Membre du club :</td><td><select id="membre"><option value="1">OUI</option><option value="0" selected="selected">NON</option></select></td></tr>';
	html += '<tr><td>Cotisation payée :</td><td><select id="cotisation"><option value="1">OUI</option><option value="0" selected="selected">NON</option></select></td></tr>';
	html += '<tr><td>Parcours achetés : </td><td><input type="text" id="parcours" value="0" /></td></tr>';
	html += '<tr><td>Login : </td><td><input type="text" id="login" /></td></tr>';
	html += '<tr><td>Mot de passe : </td><td><input type="password" id="mdp" /></td></tr>';
	html += '<tr><td>Administrateur :</td><td><select id="administrateur"><option value="1">OUI</option><option value="0" selected="selected">NON</option></select></td></tr>';
	html += '</table>';
	html += '<div id="erreur"></div>';
	html += '<input type="button" value="OK" id="ok" />';
	html += '<input type="button" value="Annuler" id="annuler" />';
	html += '</div>';
	$('#panneau_central').append(html);
}

function formMessage(message) {
	if($('#formMessage').length > 0)
		return;

	var html = '<div id="formMessage">';
	html += '<p>' + message + '</p>';
	html += '</div>';
	$('#panneau_central').append(html);

	setTimeout(function() {
		$('#formMessage').fadeOut(1000, function() {
			$('#formMessage').remove();
		});
	}, 3000);
}

function calculStat() {
	var ajax = $.ajax({
		url: 'sources/ajax.php',
		type: 'POST',
		data: 'type=calcul_stat'
	});

	ajax.done(function(data) {
		var obj = JSON.parse(data);
		$('#nbReservations').text((obj.nbReservations == null ? 0 : obj.nbReservations));
		$('#nbAnnulations').text((obj.nbAnnulations == null ? 0 : obj.nbAnnulations));
		$('#nbParcours').text((obj.nbParcoursAchetes == null ? 0 : obj.nbParcoursAchetes));
	});
}