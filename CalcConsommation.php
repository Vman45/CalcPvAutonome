<?php
// Connect DB
$db_ini = parse_ini_file('./config-db.ini',true);
try {
	if (preg_match('/^sqlite/', $db_ini['config']['db'])) {
		$dbco = new PDO($db_ini['config']['db']);
	} else {
		$dbco = new PDO($db_ini['config']['db'], $db_ini['config']['dbuser'], $db_ini['config']['dbpass']);
	}
	$dbco->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch ( PDOException $e ) {
	die('DB connect error : '.$e->getMessage());
}

// Fonction de suppression des tab
function tabDelete($id) {
	global $dbco;
	$deletecmd = $dbco->prepare("DELETE FROM conso_equi WHERE conso_id = '".$id."'");
	$deletecmd->execute();
	$deletecmd = $dbco->prepare("DELETE FROM conso WHERE id = '".$id."' LIMIT 1");
	$deletecmd->execute();
}
// Fonction de suppression des tab expiré
function tabDeleteExpire() {
	global $dbco, $db_ini;
	try {
		$query = $dbco->prepare("SELECT id FROM conso WHERE date_lastaccess < DATE_SUB(NOW(), INTERVAL ".$db_ini['config']['dbexpire']." DAY)");
		$query->execute();
		$consos = $query->fetchAll();
		foreach ($consos as $conso) {
			tabDelete($conso['id']);
		}
	}
	catch(PDOException $e) {
	  echo $e->getMessage();
	}
}	
// Delete tab
if (isset($_GET['delete']) && isset($_GET['ad'])) {
	try {
		$query = $dbco->prepare("SELECT id, name FROM conso WHERE idadmin = :ad LIMIT 1");
		$query->bindParam('ad', $_GET['ad'], PDO::PARAM_INT);
		$query->execute();
		$conso = $query->fetch();
		if($conso !== false){
			tabDelete($conso['id']);
		}
		echo '<p class="highlight-3">'._('The consumption table has been deleted.').'</p>';
	}
	catch(PDOException $e) {
	  echo $e->getMessage();
	}
	?>
	<script type="text/javascript">
		function redir(){
			document.location.href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>"
		}
		setTimeout(redir,3000)
	</script>
	<?php
} else {
	
// Envoyer liste tableau par email (open)
if (isset($_POST['open'])) {
	$sql = "SELECT name, idpub, idadmin FROM conso WHERE email = :email";
	try {
		$query = $dbco->prepare($sql);
		$query->bindParam('email', $_POST['email'], PDO::PARAM_STR);
		$query->execute();
		$count=$query->rowCount();
		$open_conso = $query->fetchAll();
	  
		if($count > 0){
			$message=_('Hello')."
			
"._('Here are the consumption tables that you have saved :')."

";
			foreach($open_conso as $conso_tab) {
				$message.=$conso_tab['name']." 
  * "._('Public link (read only, to share)')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?pub='.$conso_tab['idpub']."
  * "._('Admin link')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$conso_tab['idadmin']."
  * "._('Delete link')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$conso_tab['idadmin']."&delete=1

";
			}
			$message.=_('Beautiful day');
			SendEmail($_POST['email'], _('Your consumption tables'), $message);
			echo '<p class="highlight-3">'._('An email has just been sent to you with the list of consumption tables that you have saved. Remember to check in your spam folder if you can not find it.').'</p>';
		} else {
			echo '<p class="highlight-1">'._('No table is stored for this email address. Or these have expired.').'</p>';
		}
	}
	catch(PDOException $e) {
	  echo $e->getMessage();
	}
}

// Sauvegarde d'un tableau 
if (isset($_POST['equiIncrement'])) {
	
	// On lance la tâche de ménage des tab expiré 
	tabDeleteExpire();
	
	// Pour le cas "save as" quand on est déjà dans un truc enregistré 
	// Revien à dupliquer et nomer un nouveau tableau...
	if (isset($_POST['saveAs'])) {
		unset($_POST['conso_id']);
	}
	
	// Si c'est pas un update : 
	if (empty($_POST['conso_id'])) {
		// Le tableau	
		$idpub=rand(1, 999999);
		$idadmin=rand(111111111, 999999999);
		try {
			$insertcmd = $dbco->prepare("INSERT INTO conso (date_create, date_lastaccess, email, name, idpub, idadmin) 
											VALUES (NOW(), NOW(), :email, :name, :idpub, :idadmin)");
			$insertcmd->bindParam('idpub', $idpub, PDO::PARAM_INT);
			$insertcmd->bindParam('idadmin', $idadmin, PDO::PARAM_INT);
			$insertcmd->bindParam('email', $_POST['email'], PDO::PARAM_STR);
			$insertcmd->bindParam('name', $_POST['name'], PDO::PARAM_STR);
			$insertcmd->execute();
			$conso_id = $dbco->lastInsertId();
		} catch ( PDOException $e ) {
			echo "DB error :  ", $e->getMessage();
			die();
		}
	} else {
		$conso_id=$_POST['conso_id'];
		$deletecmd = $dbco->prepare("DELETE FROM conso_equi WHERE conso_id = '".$conso_id."'");
		$deletecmd->execute();
	}
	
	
	// Les enregistrements : 
	for ($i = 1; $i <= $_POST['equiIncrement']; $i++) {
		if (isset($_POST['EquiNom'.$i])) {
			try {
				$null=null;
				$boolean1=1;
				$boolean0=0;
				$utilDefaut=24;
				$insertcmd = $dbco->prepare("INSERT INTO conso_equi (conso_id, name, p, pmax, nb, uti, calcauto, pj) 
												VALUES (:conso_id, :name, :p, :pmax, :nb, :uti, :calcauto, :pj)");
				$insertcmd->bindParam('conso_id', $conso_id, PDO::PARAM_INT);	
				$insertcmd->bindParam('name', $_POST['EquiNom'.$i], PDO::PARAM_STR);
				$insertcmd->bindParam('p', $_POST['EquiPuis'.$i], PDO::PARAM_INT);	
				if (isset($_POST['EquiPmax'.$i])) {
					$insertcmd->bindParam('pmax', $boolean1, PDO::PARAM_BOOL);	
				} else {
					$insertcmd->bindParam('pmax', $boolean0, PDO::PARAM_INT);	
				}
				$insertcmd->bindParam('uti', $_POST['EquiUti'.$i], PDO::PARAM_INT);
				if (isset($_POST['AutoEquiTotal'.$i])) {
					// Calcul auto
					$insertcmd->bindParam('nb', $_POST['EquiNb'.$i], PDO::PARAM_INT);
					$insertcmd->bindParam('uti', $_POST['EquiUti'.$i], PDO::PARAM_STR);
					$insertcmd->bindParam('calcauto', $boolean1, PDO::PARAM_INT);
					$insertcmd->bindParam('pj', $null, PDO::PARAM_NULL);
					
				} else {
					// Calcul manuel
					$insertcmd->bindParam('nb', $boolean1, PDO::PARAM_INT);
					$insertcmd->bindParam('uti', $utilDefaut, PDO::PARAM_STR);
					$insertcmd->bindParam('calcauto', $boolean0, PDO::PARAM_INT);
					$insertcmd->bindParam('pj', $_POST['EquiTotalInput'.$i], PDO::PARAM_INT);
				}
				$insertcmd->execute();
			} catch ( PDOException $e ) {
				echo "DB error :  ", $e->getMessage();
				die();
			}
		}
	}
	
	// Si c'est un "enregistrer sous" on redirige vers la page avec URL admin
	// Et on lui expédit un petit mail
	if (empty($_POST['conso_id'])) { 
		$message=_('Hello')."

"._('Your consumption chart has been saved. Follow these links to open it :')."

  * "._('Public link (read only, to share)')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?pub='.$idpub."
  * "._('Admin link')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$idadmin."
  * "._('Delete link')." : ".$config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$idadmin."&delete=1

"._('Beautiful day');
		SendEmail($_POST['email'], _('Your consumption table').' : '.$_POST['name'], $message)
		?>
		<div id="saveConfirmPopup" class="modal">
			<div class="modal-content">
				<h1><?= _('Confirmation') ?></h1>
				<p><?= _('Your data has been saved. You will be redirected to the saved table'); ?></p>
				<p><?= printf(_('Click <a href="%s">here</a> if the redirect does not work.'), $config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$idadmin); ?></p>
			</div>
			<script type="text/javascript">
				function redir(){
				document.location.href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>?ad=<?= $idadmin ?>"
				}
				setTimeout(redir,2000)
			</script>
		</div> 
		<script type="text/javascript">
			$( "#saveConfirmPopup" ).show();
		</script>
	<?php }
}


?>
<link rel="stylesheet" href="./lib/jquery-ui.css">
<script src="./lib/jquery-ui.js"></script> 

<?php if (empty($_GET['ad']) && empty($_GET['pub']) && empty($_GET['EquiNom1'])) { ?>
<p><?= _('In order to know the electric consumption (in Watts) of your devices you may') ?> : 
<ul>
	<li><?= _('Check on the device\'s specification sheet or label') ?> ;</li>
	<li><?= _('You may get a Wattmeter (~10&euro;) that will show you the exact consumption if plugged between power socket and the applience') ?> ;</li>
</ul></p>
<p><?= _('To calculate the dimensions of an autonomous solar installation, consider that it is wintertime (longer lighting time needed, but the fridge might be unplugged due to low temperature outside...)') ?> :</p>
<ul>
	<li><a href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>?EquiNom1=Ordinateur%20Portable&EquiPuis1=45&EquiNb1=1&EquiUti1=6&EquiPmax1=1&EquiNom2=Aspirateur%20Classe%20A&EquiPuis2=700&EquiNb2=1&EquiUti2=0.25&EquiNom3=Ampoule%20Led&EquiPuis3=7&EquiNb3=4&EquiUti3=7&EquiNom4=Machine%20%C3%A0%20coudre&EquiPuis4=100&EquiNb4=1&EquiUti4=0.5&EquiNom5=Mini%20cha%C3%AEne%20(musique)&EquiPuis5=16&EquiNb5=1&EquiUti5=6&EquiPmax5=1&EquiNom6=Recharge%20t%C3%A9l%C3%A9phone%20portable&EquiPuis6=6&EquiNb6=1&EquiUti6=2&EquiPmax6=1&EquiNom7=Pompe%20immerg%C3%A9e&EquiPuis7=400&EquiNb7=1&EquiUti7=0.5&EquiPmax7=1&&p=CalcConsommation&equiIncrement=7"><?= _('First sober example') ?></a></li>
	<li><a href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>?EquiNom1=Aspirateur%20Classe%20A&EquiPuis1=700&EquiNb1=1&EquiUti1=0.25&EquiPmax1=1&EquiNom2=Cong%C3%A9lateur%20Bahut%20200L%20Classe%20A&EquiPuis2=370&EquiNb2=1&EquiUti2=24&EquiTotalInput2=800&EquiPmax2=1&EquiNom3=Ampoule%20%C3%A0%20incandescence&EquiPuis3=75&EquiNb3=3&EquiUti3=8&EquiPmax3=1&EquiNom4=Ordinateur%20de%20bureau%20+%20%C3%A9cran&EquiPuis4=140&EquiNb4=1&EquiUti4=0&EquiNom5=T%C3%A9l%C3%A9phone%20fixe&EquiPuis5=2&EquiNb5=1&EquiUti5=24&EquiTotalInput5=48&EquiPmax5=1&EquiNom6=Box%20Internet&EquiPuis6=20&EquiNb6=1&EquiUti6=24&EquiTotalInput6=480&EquiPmax6=1&EquiNom7=T%C3%A9l%C3%A9&EquiPuis7=70&EquiNb7=1&EquiUti7=0&EquiPmax7=1&EquiNom8=Rasoir&EquiPuis8=5&EquiNb8=1&EquiUti8=0.08&&p=CalcConsommation&equiIncrement=8"><?= _('Second example') ?></a></li>
</ul>
<?php } ?>
<form id="formEqui" method="post" action="#">
<?php
if (isset($_GET['ad'])) {
	try {
		$query = $dbco->prepare("SELECT id, name, email, idpub, idadmin FROM conso WHERE idadmin = :ad LIMIT 1");
		$query->bindParam('ad', $_GET['ad'], PDO::PARAM_INT);
		$query->execute();
		$conso = $query->fetch();
	}
	catch(PDOException $e) {
	  echo $e->getMessage();
	}
	if ($conso['id'] == ''){
		$conso = null;
		echo '<p class="highlight-1">'._('Error: This table does not exist or has expired').'</p>';	
	}
} elseif (isset($_GET['pub'])) {
	try {
		$query = $dbco->prepare("SELECT id, name, email, idpub, idadmin FROM conso WHERE idpub = :pub LIMIT 1");
		$query->bindParam('pub', $_GET['pub'], PDO::PARAM_INT);
		$query->execute();
		$conso = $query->fetch();
	}
	catch(PDOException $e) {
	  echo $e->getMessage();
	}
	if ($conso['id'] == ''){
		$conso = null;
		echo '<p class="highlight-1">'._('Error: This table does not exist or has expired').'</p>';
	}
} else {
	$conso = null;
}
?>

<!-- Boutton -->
<div style="margin-bottom: 5px">
	<input id="new" type="button" value="<?= _('New') ?>" />
	<input id="open" type="button" value="<?= _('Open') ?>" />
	<?php 
	// Save boutton
	if (isset($_GET['ad'])) {
		echo '<input id="save" type="button" value="'._('Save').'" />';
	}
	echo '<input id="saveAs" type="button" value="'._('Save as...').'" />';
	if (isset($_GET['ad'])) {
		echo '<input type="button" value="'._('Delete').'" onclick="window.location=\''.$config_ini['formulaire']['UrlCalcConsommation'].'/?ad='.$_GET['ad'].'&delete=1\';" />';
	}
	?>
</div>

<?php 

if ($conso != null) {
	echo '<h2>'.$conso["name"].'</h2>';
	$consoEqui = $dbco->query("SELECT id, name, p, pmax, nb, uti, calcauto, pj FROM conso_equi WHERE conso_id = '".$conso['id']."'")->fetchAll() ;
	// Date last access update
	try {
		$updatecmd = $dbco->prepare("UPDATE conso SET date_lastaccess = NOW() WHERE id = :id");
		$updatecmd->bindParam('id', $conso['id'], PDO::PARAM_INT);
		$updatecmd->execute();
	} catch ( PDOException $e ) {
		echo "DB error :  ", $e->getMessage();
		die();
	}

	if (isset($_GET['ad'])) {
		echo '<p>'._('Public link (read only, to share)').' <input type="text" value="'.$config_ini['formulaire']['UrlCalcConsommation'].'?pub='.$conso['idpub'].'" id="idpub-to-copy" /><button id="idpub-copy" type="button">'._('Copy').'</button></p>';
		echo '<p>'._('Admin link (this interface)').' <input type="text" value="'.$config_ini['formulaire']['UrlCalcConsommation'].'?ad='.$conso['idadmin'].'" id="idadmin-to-copy" /><button id="idadmin-copy" type="button">'._('Copy').'</button></p>';
	}
}
?>

<input type="hidden" value="<?= $conso['id'] ?>" name="conso_id" />
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

<?php 
// Pour l'autocompletion
$i=0;
foreach ($config_ini['equipement'] as $equipement) {	
	$equipementAutocompletion[$i]['id'] = $i;
	$equipementAutocompletion[$i]['value'] = $equipement['nom'];
	$equipementAutocompletion[$i]['puissance'] = $equipement['conso'];
	// if ($equipement['consoJ'] != null) {
		$equipementAutocompletion[$i]['consommation_quotidienne'] = $equipement['consoJ'];
	// }
	// if ($equipement['uti'] != null) {
		$equipementAutocompletion[$i]['temps_utilisation'] = $equipement['uti'];
	// }
	$i++;
}
// echo json_encode($equipementAutocompletion);
?>

<!-- hidden -->
<input type="hidden" value="0" name="equiIncrement" id="equiIncrement" />

<!-- Fonction désactivé
<p>
	<input type="button" value="<?= _('Share / Save this table') ?>" id="share" />
</p>
-->

<!-- Save poup -->
<div id="savePopup" class="modal">
	<div class="modal-content">
	<span class="closeSavePopup close">&times;</span>
		<h1><?= _('Save') ?></h1>
		<p><?= printf(_('This data will be deleted after %d days without consultation'), $db_ini['config']['dbexpire']); ?></p>
		<p><label><?= _('Name for this table') ?> : </label><input type="name" name="name" value="" required />
		<p><label><?= _('Your email') ?> : </label><input type="email" name="email" value="<?php if (isset($_COOKIE['email'])) { echo $_COOKIE['email']; } ?>" required /></p>
		<p><input type="button" class="closeSavePopup" value="<?= _('Cancel') ?>"><input type="submit" name="saveAs" value="<?= _('Save') ?>" /></p>
	</div>
</div>

</form>

<!-- Open popup -->
<div id="openPopup" class="modal">
	<div class="modal-content">
	<span class="closeOpenPopup close">&times;</span>
		<form method="post" action="#">
			<h1><?= _('Open') ?></h1>
			<p><?= _('You will receive an email with links to the tables you have saved'); ?></p>
			<p><label><?= _('Your email') ?> : </label><input type="email" name="email" value="<?php if (isset($_COOKIE['email'])) { echo $_COOKIE['email']; } ?>" required /></p>
			<p><input type="button" class="closeOpenPopup" value="<?= _('Cancel') ?>"><input type="submit" name="open" value="<?= _('Send me') ?>" /></p>
		</form>
	</div>
</div>


<script type="text/javascript">

// Copy link   
$('#idpub-copy').on('click', function() {
	$('#idpub-to-copy').select();
	document.execCommand( 'copy' );
	return false;
} );
$('#idadmin-copy').on('click', function() {
	$('#idadmin-to-copy').select();
	document.execCommand( 'copy' );
	return false;
} );
$('#new').on('click', function() {
	document.location.href="<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>"
} );
	
function ajoutUneLigne() {
	$('#equiIncrement').val(parseInt($('#equiIncrement').val(),10)+1);
	$('table').append( 
        [
		'\n<tr>', 
			'<td>', 
				'<input class="nom" type="text" placeholder="Equipement ' + $('#equiIncrement').val() + '" name="EquiNom' + $('#equiIncrement').val() + '" id="EquiNom' + $('#equiIncrement').val() + '" />', 
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
					' <?= _('Wh/d') ?> <a rel="tooltip" class="bulles" title="<?= _('The calculation is: Power (W) * Time (in hours) * Number = Wh/d (Watt Hour Day)') ?>">?</a>',
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
	autocomplete();
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
		// Split les data du select
		var ModeleData = $('#addEquiModele').val().split('|');
		
		// Utilisation
		if (ModeleData[1] != '') {
			var uti = ModeleData[1];
		} else {
			var uti = 0;
		}
		if (ModeleData[2] != '') {
			var calcauto = 0;
			var pj = ModeleData[2];
		} else {
			var calcauto = 1;
			var pj = null
		}
		chargeEquipement($('#addEquiModele option:selected').html(), ModeleData[0], 1, 1, uti, calcauto, pj);
		
		$('#addEquiModele').val(0);

	}
});

// Contrib quentin@electree.eu
// récupère une liste d'équipement en bdd et propose une autocompletion lors de l'ajout d'un materiel
function autocomplete() {
	let availableTags = <?php echo json_encode($equipementAutocompletion); ?>;
	console.log(availableTags);
	$('input.nom').autocomplete({
		source: availableTags,
		select: function(event, ui) {
			console.log(ui);
			$(this).closest("tr").find("input.Puis").val(ui.item.puissance);
			if (ui.item.temps_utilisation != null) {
	            $(this).closest("tr").find("select.Uti").val(ui.item.temps_utilisation);
	        } else {
	            $(this).closest("tr").find("select.Uti").val(0);
	        }
	        if (ui.item.consommation_quotidienne != null) {
	            $(this).closest("tr").find("input.AutoEquiTotal").prop('checked', false);
	            $(this).closest("tr").find("input.EquiTotal").val(ui.item.consommation_quotidienne);
	            $(this).closest("tr").find("span.consobdd").text(ui.item.consommation_quotidienne);

	        } else {
	            $(this).closest("tr").find("input.AutoEquiTotal").prop('checked', true);
	        }
			calcTableau();
		}
	});
	};

// Charger une ligne d'équipement
function chargeEquipement (name, p, pmax, nb, uti, calcauto, pj) {
	ajoutUneLigne();
	$( '#EquiNom'+$('#equiIncrement').val()).val(name);
	$( '#EquiPuis'+$('#equiIncrement').val()).val(p);
	if (pmax == 0 || pmax == false) {
		$( '#EquiPmax'+$('#equiIncrement').val()).prop('checked', false);
	}  else {
		$( '#EquiPmax'+$('#equiIncrement').val()).prop('checked', true);
	}
	$( '#EquiNb'+$('#equiIncrement').val()).val(nb);
	if (uti != null || uti != '') {
		$( '#EquiUti'+$('#equiIncrement').val()).val(uti);
	}
	if (calcauto == 0 || calcauto == false) {
		$( '#AutoEquiTotal'+$('#equiIncrement').val()).prop('checked', false);
	} else {
		$( '#AutoEquiTotal'+$('#equiIncrement').val()).prop('checked', true);
	}
	if (pj != null || pj != '') {
		$( '#EquiTotalInput'+$('#equiIncrement').val()).val(pj);
	}
	autocomplete();
	calcTableau();
}


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
	
	<?php
	if (empty($_GET['pub'])) {
		?> shareSocial(); <?php
	}
	?>
}

// Bouton de partage
function shareSocial() {
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
	
	$( "#shareUrl" ).val('<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>'+URLconstruction);
	UrlForShare=encodeURIComponent("<?= $config_ini['formulaire']['UrlCalcConsommation'] ?>"+URLconstruction);
	
	// Social button link
	$('.resp-sharing-button__link.email').attr("href", "mailto:?subject=CalcPv&body="+UrlForShare);
	$('.resp-sharing-button__link.facebook').attr("href", "https://facebook.com/sharer/sharer.php?u="+UrlForShare);
	$('.resp-sharing-button__link.twitter').attr("href", "https://twitter.com/intent/tweet/?text=CalcPv&url="+UrlForShare);
	$('.resp-sharing-button__link.reddit').attr("href", "https://reddit.com/submit/?url="+UrlForShare);
	$('.resp-sharing-button__link.whatsapp').attr("href", "whatsapp://send?text=CalcPv="+UrlForShare);
	$('.resp-sharing-button__link.ycombinator').attr("href", "https://news.ycombinator.com/submitlink?t=CalcPv&u="+UrlForShare);
	$('.resp-sharing-button__link.telegram').attr("href", "https://telegram.me/share/url?text=CalcPv&url="+UrlForShare);

}


// init
$(document).ready(function() {
	
	<?php if ($conso == null) { ?>
		// Ajout de la première ligne si pas enregistré
		ajoutUneLigne();
	<?php } ?>
	
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
	autocomplete();

	// Check modification
	$( "#save" ).attr("disabled", "disabled");
	<?php if (empty($_GET['ad']) && empty($_GET['pub'])) { ?>
		$( "#new" ).attr("disabled", "disabled");
	<?php } ?>
	$( "input" ).change(function() {
		$( "#save" ).removeAttr("disabled");    
		$( "#new" ).removeAttr("disabled");
	}); 
}); 

$( "#save" ).click(function() {
	$( "#formEqui" ).submit();
});
$( "#saveAs" ).click(function() {
	savePopupOn();
});
$( "#open" ).click(function() {
	openPopupOn();
});
$( ".closeSavePopup" ).click(function() {
	savePopupOff();		
});
$( ".closeOpenPopup" ).click(function() {
	openPopupOff();		
});
function savePopupOn() {
	$( "#savePopup" ).show();
}
function savePopupOff() {
	$( "#savePopup" ).hide();
}
function openPopupOn() {
	$( "#openPopup" ).show();
}
function openPopupOff() {
	$( "#openPopup" ).hide();
}


</script>


<?php
	// Chargement des données en BD
	echo '<script type="text/javascript">';
	foreach ($consoEqui as $equi) {
		echo 'chargeEquipement(\''.$equi['name'].'\', \''.$equi['p'].'\', \''.$equi['pmax'].'\', \''.$equi['nb'].'\', \''.$equi['uti'].'\', \''.$equi['calcauto'].'\', \''.$equi['pj'].'\');';
	}
	echo '</script>';
?>


<?php 

}

// Close pdo 
$dbco = null; 

if (isset($_GET['pub'])) {
	// Social share 
	socialShare($config_ini['formulaire']['UrlCalcConsommation'].'?pub='.$_GET['pub']);
} else {
	socialShare('');
}
?>
