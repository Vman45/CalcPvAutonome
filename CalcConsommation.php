<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
<p><?= _('In order to know the electric consumption (in Watts) of your devices you may') ?> : 
<ul>
	<li><?= _('Check on the device\'s specification sheet or label') ?> ;</li>
	<li><?= _('You may get a Wattmeter (~10&euro;) that will show you the exact consumption if plugged between power socket and the applience') ?> ;</li>
</ul></p>
<p><?= _('To calculate the dimensions of an autonomous solar installation, consider that it is wintertime (longer lighting time needed, but the fridge might be unplugged due to low temperature outside...)') ?> :</p>
<ul>
	<li><a href="http://conso.calcpv.net/?EquiNom1=Ordinateur%20Portable&EquiPuis1=45&EquiNb1=1&EquiUti1=6&EquiPmax1=1&EquiNom2=Aspirateur%20Classe%20A&EquiPuis2=700&EquiNb2=1&EquiUti2=0.25&EquiNom3=Ampoule%20Led&EquiPuis3=7&EquiNb3=4&EquiUti3=7&EquiNom4=Machine%20%C3%A0%20coudre&EquiPuis4=100&EquiNb4=1&EquiUti4=0.5&EquiNom5=Mini%20cha%C3%AEne%20(musique)&EquiPuis5=16&EquiNb5=1&EquiUti5=6&EquiPmax5=1&EquiNom6=Recharge%20t%C3%A9l%C3%A9phone%20portable&EquiPuis6=6&EquiNb6=1&EquiUti6=2&EquiPmax6=1&EquiNom7=Pompe%20immerg%C3%A9e&EquiPuis7=400&EquiNb7=1&EquiUti7=0.5&EquiPmax7=1&&p=CalcConsommation&equiIncrement=7"><?= _('First sober example') ?></a></li>
	<li><a href="http://conso.calcpv.net/?EquiNom1=Aspirateur%20Classe%20A&EquiPuis1=700&EquiNb1=1&EquiUti1=0.25&EquiPmax1=1&EquiNom2=Cong%C3%A9lateur%20Bahut%20200L%20Classe%20A&EquiPuis2=370&EquiNb2=1&EquiUti2=24&EquiTotalInput2=800&EquiPmax2=1&EquiNom3=Ampoule%20%C3%A0%20incandescence&EquiPuis3=75&EquiNb3=3&EquiUti3=8&EquiPmax3=1&EquiNom4=Ordinateur%20de%20bureau%20+%20%C3%A9cran&EquiPuis4=140&EquiNb4=1&EquiUti4=0&EquiNom5=T%C3%A9l%C3%A9phone%20fixe&EquiPuis5=2&EquiNb5=1&EquiUti5=24&EquiTotalInput5=48&EquiPmax5=1&EquiNom6=Box%20Internet&EquiPuis6=20&EquiNb6=1&EquiUti6=24&EquiTotalInput6=480&EquiPmax6=1&EquiNom7=T%C3%A9l%C3%A9&EquiPuis7=70&EquiNb7=1&EquiUti7=0&EquiPmax7=1&EquiNom8=Rasoir&EquiPuis8=5&EquiNb8=1&EquiUti8=0.08&&p=CalcConsommation&from=CalcPvAutonome&equiIncrement=8"><?= _('Second example') ?></a></li>
</ul>
<form>
<table>
	<tr>
		<th><?= _('Equipment') ?></th>
		<th><?= _('Power (Watt)') ?></th>
		<th><?= _('Turn on<br />simultaneously') ?></th>
		<th><?= _('Number') ?></th>
		<th><?= _('Daily use time') ?></th>
		<th><?= _('Automatic consumption<br />calculation') ?></th>
		<th><?= _('Daily<br />consumption') ?></th>
		<th>.</th>
	</tr>
	
</table>

<p id="resultatConsoTotal"><?= _('Your daily electrical needs') ?> : <b><span id="ConsoTotal">0</span> Wh/j</b>
<br /><?= _('Your need in maximum electrical power') ?> : <b><span id="PmaxTotal">0</span> W</b> '<a rel="tooltip" class="bulles" title="<?= _('Total addition of power required by devices that are likely to be plugged and on at the same time OR consumption of the most power demanding appliance in case it is not plugged simultaneously with the rest') ?>">?</a>
<br /><a href="" id="hrefCalcPvAutonome"><?= _('Click here to introduce those values to calculate <br/> the dimension of your autonomous solar installation') ?></a></p>

<p><input type="button" class="add" value="<?= _('Add an empty line') ?>" /> 
<select id="addEquiModele" name="addEquiModele">
<option value="0"><?= _('Add a line according to a template...') ?></option>
<?php 
foreach ($config_ini['equipement'] as $equipement) {	
	echo '<option value="'.$equipement['conso'].'|'.$equipement['uti'].'|'.$equipement['consoJ'].'">'.$equipement['nom'].'</option>';
}
?>
</select> <a rel="tooltip" class="bulles" title="<?= _('The models\' values are approximate estimations, if you need more precise measurement consider using Wattmeter to determine the exact consumption of each of yours devices') ?>">?</a></p>

<!-- hidden -->
<input type="hidden" value="0" name="equiIncrement" id="equiIncrement" />

<p>
	<input type="button" value="<?= _('Share / Save this table') ?>" id="share" />
	<!--<input type="submit" value="Sauvegarder ce tableau" id="save" />-->
</p>
</form>

<script type="text/javascript">
function ajoutUneLigne() {
	$('#equiIncrement').val(parseInt($('#equiIncrement').val(),10)+1);
	$('table').append( 
        [
		'\n<tr>', 
			'<td>', 
				'<input type="text" value="Equipement ' + $('#equiIncrement').val() + '" name="EquiNom' + $('#equiIncrement').val() + '" id="EquiNom' + $('#equiIncrement').val() + '" />', 
			'</td>', 
			'<td>', 
				'<input class="Puis" onChange="calcTableau();" type="number"  style="width: 80px;" value="0" min="0" max="99999" name="EquiPuis' + $('#equiIncrement').val() + '" id="EquiPuis' + $('#equiIncrement').val() + '" />W', 
			'</td>', 
			'<td>', 
				'<input class="EquiPmax" onChange="calcTableau();" type="checkbox" name="EquiPmax' + $('#equiIncrement').val() + '" id="EquiPmax' + $('#equiIncrement').val() + '" checked="checked" />',
				'<a rel="tooltip" class="bulles" title="<?= _('Tick all the devices that are likely to be switched on at the same time (for example: Pc, fridge, lightbulb in the livingroom). At the other hand, you might rather want to unplug your Pc when you are using a drill') ?>">?</a>',
			'</td>', 
			'<td>', 
				'<input class="Nb" onChange="calcTableau();"  type="number" style="width: 60px;" value="1"  min="1" max="99" name="EquiNb' + $('#equiIncrement').val() + '" id="EquiNb' + $('#equiIncrement').val() + '" />', 
			'</td>', 
			'<td>', 
				'<select class="Uti" onChange="calcTableau();"  id="EquiUti' + $('#equiIncrement').val() + '" name="EquiUti' + $('#equiIncrement').val() + '">', 
					'<option value="0">0</option>',
					'<option value="0.08">5 m</option>', 
					'<option value="0.25">15 m</option>', 
					'<option value="0.5">30 m</option>', 
					'<option value="0.75">45 m</option>', 
					'<option value="1">1 H</option>', 
					'<option value="1.5">1 H 30</option>', 
					'<option value="2">2 H</option>', 
					'<option value="2.5">2 H 30</option>', 
					'<option value="3">3 H</option>', 
					'<option value="4">4 H</option>', 
					'<option value="5">5 H</option>', 
					'<option value="6">6 H</option>', 
					'<option value="7">7 H</option>', 
					'<option value="8">8 H</option>', 
					'<option value="9">9 H</option>', 
					'<option value="10">10 H</option>', 
					'<option value="11">11 H</option>', 
					'<option value="12">12 H</option>', 
					'<option value="16">16 H</option>', 
					'<option value="20">20 H</option>', 
					'<option value="24">24 H</option>', 
				'</select>', 
			'</td>', 
			'<td>', 
				'<input onChange="calcTableau();" class="AutoEquiTotal" type="checkbox" name="AutoEquiTotal' + $('#equiIncrement').val() + '" id="AutoEquiTotal' + $('#equiIncrement').val() + '" checked="checked" />',
				'<a rel="tooltip" class="bulles" title="<?= _('Ticked: your daily consumption will be estimated automatically<br/>Unticked: you shall specifiy your daily consumption (useful in case your fridge is plugged 24/7 yet it goes on only if room temperature rises') ?>">?</a>',
			'</td>', 
			'<td>', 
				'<p>',
					'<input onChange="calcTableau();"  class="EquiTotal" step="0.01" type="number"  style="width: 80px;" value="0" min="0,01" max="99999" name="EquiTotalInput' + $('#equiIncrement').val() + '" id="EquiTotalInput' + $('#equiIncrement').val() + '" />',
					'<span id="EquiTotal' + $('#equiIncrement').val() + '">0</span>', 
					' Wh/j <a rel="tooltip" class="bulles" title="<?= _('The calculation is: Power (W) * Time (in hours) * Number = Wh/j (Watt Hour Day)') ?>">?</a>',
				'</p>', 
			'</td>', 
			'<td>', 
				'<img src="./lib/trash.png" width="30" class="remove" />', 
			'</td>', 
		'</tr>'
        ].join('') //un seul append pour limiter les manipulations directes du DOM
    );  
    
}

// Ajout d'une ligne dans le tableau
$('.add').on('click', function() {    
	ajoutUneLigne();
	calcTableau();
});
// Suppression d'une ligne dans le tableau
$('table').on('click', '.remove', function() {
	var $this = $(this);
	$this.closest('tr').remove();   
	calcTableau(); 
});

// Ajout d'un modèle d'équipement
$('#addEquiModele').change(function() {
	if ($('#addEquiModele').val() != 0) {
		ajoutUneLigne();
		// Split les data du select
		var ModeleData = $('#addEquiModele').val().split('|');
		// Nom
		$( '#EquiNom'+$('#equiIncrement').val()).val($('#addEquiModele option:selected').html());
		// Conso
		$( '#EquiPuis'+$('#equiIncrement').val()).val(ModeleData[0]);
		// Utilisation
		if (ModeleData[1] != '') {
			$( '#EquiUti'+$('#equiIncrement').val()).val(ModeleData[1]);
		}
		if (ModeleData[2] != '') {
			$('#AutoEquiTotal'+$('#equiIncrement').val()).prop('checked', false)
			$('#EquiTotalInput'+$('#equiIncrement').val()).val(ModeleData[2])
		}
		
		$('#addEquiModele').val(0);
		calcTableau();
	}
});


// Re-calcule le tableau
function calcTableau() {
	var ConsoTotal = 0;
	var PmaxTotal = 0;
	var PmaxNbEqui = 0;
	var PmaxEquiRecord = 0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		var ConsoEqui = 0;
		if ($( '#EquiNom'+idEqui).length) {
			// Consommation 
			ConsoEqui=parseInt($('#EquiPuis'+idEqui).val(),10)*parseInt($('#EquiNb'+idEqui).val(),10)*$('#EquiUti'+idEqui).val(),10;
			// Automatique
			if ($('#AutoEquiTotal'+idEqui).is(':checked')) {
				$( '#EquiTotalInput'+idEqui).hide();
				$( '#EquiTotal'+idEqui).show();
				$( '#EquiTotalInput'+idEqui).val(ConsoEqui);
				$( '#EquiTotal'+idEqui).text(ConsoEqui);
				$( '#EquiNb'+idEqui).prop('disabled',false);
				$( '#EquiUti'+idEqui).prop('disabled',false);
			// Manuel
			} else {
				//console.log('Equipement ' + idEqui + ' Mode Manuel');
				$('#EquiTotalInput'+idEqui).show();
				$('#EquiTotal'+idEqui).hide();
				$('#EquiTotal'+idEqui).text(ConsoEqui);
				$('#EquiNb'+idEqui).prop('disabled',true);
				$('#EquiUti'+idEqui).prop('disabled',true);
				ConsoEqui=parseInt($('#EquiTotalInput'+idEqui).val());
			}
			//console.log('Equipement ' + idEqui + ' conso = ' + ConsoEqui);
			ConsoTotal = ConsoTotal + ConsoEqui;
			// Si 24/24, on coche alumage simultané
			if ($('#EquiUti'+idEqui).val() == '24') {
				$('#EquiPmax'+idEqui).prop('checked', true);
			}
			// alumage simultané coché : : 
			if ($('#EquiPmax'+idEqui).is(':checked')) {
				//console.log('Puissance Max coché !');
				PmaxTotal = PmaxTotal + parseInt($('#EquiPuis'+idEqui).val(),10);
				PmaxNbEqui++;
			}
			// Nouveau record de consommation ?
			if (PmaxEquiRecord < parseInt($('#EquiPuis'+idEqui).val(),10)) {
				PmaxEquiRecord = parseInt($('#EquiPuis'+idEqui).val(),10)
			}
		}
	}
	//console.log('Conso total : ' + ConsoTotal);
	//console.log('Puissance Max total : ' + PmaxTotal);
	$( '#ConsoTotal').text(ConsoTotal);
	if (PmaxNbEqui == 0 || PmaxTotal < PmaxEquiRecord) {
		PmaxTotal = PmaxEquiRecord
	}
	$( '#PmaxTotal').text(PmaxTotal);
	$('#hrefCalcPvAutonome').attr('href', '<?= $config_ini['formulaire']['UrlCalcPvAutonome'] ?>'+'?Bj='+Math.round(ConsoTotal)+'&Pmax='+Math.round(PmaxTotal));
}

// Bouton de partage
$('#share').on('click', function() {
	// Liste le formulaire 
	var URLconstruction = '?';
	var nbPourDeVrai=0;
	for (var idEqui = 1; idEqui <= parseInt($('#equiIncrement').val(),10); idEqui++) {
		if ($('#EquiNom'+idEqui).length) {
			nbPourDeVrai++;
			URLconstruction=URLconstruction + 'EquiNom'+nbPourDeVrai+'=' + $('#EquiNom'+idEqui).val()+'&EquiPuis'+nbPourDeVrai+'=' + $('#EquiPuis'+idEqui).val()+'&EquiNb'+nbPourDeVrai+'=' + $('#EquiNb'+idEqui).val()+'&EquiUti'+nbPourDeVrai+'=' + $('#EquiUti'+idEqui).val()+'&';
			if (!$('#AutoEquiTotal'+idEqui).is(':checked')) {
				URLconstruction=URLconstruction + 'EquiTotalInput'+nbPourDeVrai+'=' + $('#EquiTotalInput'+idEqui).val()+'&';
			}
			if (!$('#EquiPmax'+idEqui).is(':checked')) {
				URLconstruction=URLconstruction + 'EquiPmax'+nbPourDeVrai+'=0&';
			}
		}
	}
	
	<?php
	if (isset($_GET['p'])) {
		echo 'URLconstruction=URLconstruction+\'&p='.$_GET['p'].'\';';
	}
	if (isset($_GET['from']) && $_GET['from'] == 'CalcPvAutonome') {
		echo 'URLconstruction=URLconstruction+\'&from=CalcPvAutonome\';';
	}
	?>
	URLconstruction=URLconstruction+'&equiIncrement='+nbPourDeVrai;
	
	prompt('<?= _('Copy the internet address below keep it there or share it here...') ?>', window.location.protocol+'//'+window.location.hostname+window.location.pathname+encodeURI(URLconstruction));
});

// init
$(document).ready(function() {
	// Ajout de la première ligne
	ajoutUneLigne();
	
	<?php
	// Si il y a du get...
	if (isset($_GET['equiIncrement'])) {
		for ($i = 1; $i < $_GET['equiIncrement']; $i++) {
			echo 'ajoutUneLigne();';
		}
		foreach ($_GET as $getkey => $getval) {
			if (preg_match('#^Equi#', $getkey)) {
				echo '$("#'.$getkey.'").val("'.$getval.'");';
				if (preg_match('#^EquiTotalInput#', $getkey)) {
					// On cherche l'ID de l'équipement
					preg_match("/[0-9]+$/", $getkey, $idEqui);
					echo '$("#AutoEquiTotal'.$idEqui[0].'").prop(\'checked\', false);';
				}
				if (preg_match('#^EquiPmax#', $getkey)) {
					// On cherche l'ID de l'équipement
					preg_match("/[0-9]+$/", $getkey, $idEqui);
					echo '$("#EquiPmax'.$idEqui[0].'").prop(\'checked\', false);';
				}
			}
		}
	}
	?>
	calcTableau();

}); 



</script>


