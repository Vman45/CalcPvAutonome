<?php
$CalcPvAutonomeVersion='4.0.1';
include_once('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 

// Langues disponibles : 
$localeDispo=array('fr', 'en', 'nl', 'tr');
// Fonction de langue : 
function langue2locale($langue) {
	switch ($langue) {
		case 'fr':
			return 'fr_FR.utf8';
			break;
		case 'es':
			return 'es_ES.utf8';
			break;
		case 'pt':
			return 'pt_PT.utf8';
			break;
		case 'eo':
			return 'eo.utf8';
			break;
		case 'nl':
			return 'nl_NL.utf8';
			break;
		case 'tr':
			return 'tr_TR.utf8';
			break;
		default:
		   return 'en_US.utf8';
	}
}

// Détection et redirection (langue toujours)
if (isset($_GET['langue']) && in_array(substr($_GET['langue'], 0, 2), $localeDispo)) {
	$locale = langue2locale($_GET['langue']);
	if ($_COOKIE['langue'] != $_GET['langue']) {
		setcookie("langue",$_GET['langue'],strtotime( '+1 year' ));
	}
} elseif (isset($_GET['langue']) && !in_array(substr($_GET['langue'], 0, 2), $localeDispo)) {
	$locale = langue2locale('en'); // Erreur ou hack : 
	header('Location: '.replaceLang2url($locale));
} elseif (isset($_COOKIE['langue'])) {
	$locale = langue2locale($_COOKIE['langue']);
	header('Location: '.addLang2url($locale));
} else {
	$locale = langue2locale(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
	header('Location: '.addLang2url($locale));
	exit();
}

$localeshort=substr($locale, 0, 2);

// Définition de la langue :
$results=putenv("LC_ALL=$locale");
if (!$results) {
    exit ('putenv failed');
}
$results=putenv("LC_LANG=$locale");
if (!$results) {
    exit ('putenv failed');
}
$results=putenv("LC_LANGUAGE=$locale");
if (!$results) {
    exit ('putenv failed');
}
$results=setlocale(LC_ALL, $locale);
if (!$results) {
    exit ('setlocale failed: locale function is not available on this platform, or the given local does not exist in this environment');
}
bindtextdomain("messages", "./lang");
textdomain("messages");

// Définition du pays (selon l'IP
$country = @geoip_country_code_by_name(get_ip());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<title>[CalcPvAutonome] <?= _('Caculate/size photovoltaic stand-alone (autonomous) set') ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link href="./lib/style.css" media="screen" rel="stylesheet" type="text/css" />	
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<script> 
	<!-- https://www.browser-update.org/ -->
	var $buoop = {vs:{i:10,f:40,o:-8,s:8,c:50},api:4}; 
	function $buo_f(){ 
	 var e = document.createElement("script"); 
	 e.src = "//browser-update.org/update.min.js"; 
	 document.body.appendChild(e);
	};
	try {document.addEventListener("DOMContentLoaded", $buo_f,false)}
	catch(e){window.attachEvent("onload", $buo_f)}
	</script>
	<script src="./lib/jquery-3.1.1.slim.min.js"></script> 
</head>
<body>
	<div id="page-wrap">
		<div id="langues">
			<?php 
			foreach($localeDispo as $langPossible) {
				$flag='';
				if (substr($locale, 0, 2) == $langPossible) {
					$flag=' drapeauActif';
				}
				echo '<a href="'.replaceLang2url($langPossible).'"><img class="drapeau'.$flag.'" src="./lib/'.$langPossible.'.png" alt="'.$langPossible.'" /></a>';
			}
			?>
			<a href="https://crwd.in/calcpvautonome"><img class="drapeau" src="./lib/trad.png" alt="Help to translate" /></a>
		</div>
		<?php
		$footer=true;
		if (isset($_GET['p']) && $_GET['p'] == 'CalcConsommation'
		 || isset($_GET['p']) && $_GET['p'] == 'CalcConso'
		 || $_SERVER['HTTP_HOST'] == 'conso.calcpv.net'
		 || $_SERVER['HTTP_HOST'] == 'calconso.zici.fr'
		 || $_SERVER['HTTP_HOST'] == 'calcconso.zici.fr') {
			echo '<h1>'._('Caculate daily electric needs').'</h1>';
			@include_once('./header.php');
			echo _('<p>Go to the <a href="https://crwd.in/calcpvautonome" target="_blank">colaborative translation platform</a> to help us translate this free software.</p>');
			include('./CalcConsommation.php'); 
			@include_once('./bottom.php'); 
		} elseif (isset($_GET['p']) && $_GET['p'] == 'Modeles') {
			include('./Modeles.php'); 
			$footer=false;
		} else {
			echo '<h1>'._('Caculate/size photovoltaic stand-alone (autonomous) set').'</h1>';
			@include_once('./header.php'); 
			echo _('<p>Go to the <a href="https://crwd.in/calcpvautonome" target="_blank">colaborative translation platform</a> to help us translate this free software.</p>');
			include('./CalcPvAutonome.php'); 
			@include_once('./bottom.php'); 
		}
		if ($footer == true) {
		?>
		<div id="footer">
			<?php if ($localeshort != 'fr') { ?>
            <p class="footer_translator"><?= _('Thanks to') ?> : 
            <?php 
            if ($localeshort == 'en') { 
				echo 'nednet, coucou39, guillerette, mirrim, ppmt';
			}
			if ($localeshort == 'nl') { 
				echo 'StarlightF';
			}
			if ($localeshort == 'tr') { 
				echo 'Harun Demirel';
			}
            ?>
            <?= _('for this <a target="_blank" href="https://crwd.in/calcpvautonome">translation</a>') ?></p>
            <?php } ?>
            <?=  _('<p>Go to the <a href="https://crwd.in/calcpvautonome" target="_blank">colaborative translation platform</a> to help us translate this free software.</p>'); ?>
            <p class="footer_right"><?= _('By') ?> <a href="http://david.mercereau.info/">David Mercereau</a> (<a href="https://framagit.org/kepon/CalcPvAutonome"><?= _('Git repository') ?></a>)</p>
            <p class="footer_left">CalcPvAutonome <?= _('version') ?> <?= $CalcPvAutonomeVersion ?> <?= _('is an open software licensed <a href="https://en.wikipedia.org/wiki/Beerware">Beerware</a>') ?></p>
        </div>
        <?php 
		}
		?>
	</div>
	<div id="bg">
		<img src="./lib/solar-panel-1393880_1280.png" alt="">
	</div>
	<?php @include_once('./footer.php'); ?>

<script type="text/javascript">
$(document).ready(function() {	
	/* infobulles http://javascript.developpez.com/tutoriels/javascript/creer-info-bulles-css-et-javascript-simplement-avec-jquery/ */
    // Sélectionner tous les liens ayant l'attribut rel valant tooltip
    $('a[rel=tooltip]').mouseover(function(e) {
		// Récupérer la valeur de l'attribut title et l'assigner à une variable
		var tip = $(this).attr('title');   
		// Supprimer la valeur de l'attribut title pour éviter l'infobulle native
		$(this).attr('title','');
		// Insérer notre infobulle avec son texte dans la page
		$(this).append('<div id="tooltip"><div class="tipBody">' + tip + '</div></div>');    
		// Ajuster les coordonnées de l'infobulle
		$('#tooltip').css('top', e.pageY - 30 );
		$('#tooltip').css('left', e.pageX - 145 );
		// Faire apparaitre l'infobulle avec un effet fadeIn
	}).mousemove(function(e) {
		// Ajuster la position de l'infobulle au déplacement de la souris
		$('#tooltip').css('top', e.pageY - 30 );
		$('#tooltip').css('left', e.pageX - 145 );
	}).mouseout(function() {
		// Réaffecter la valeur de l'attribut title
		$(this).attr('title',$('.tipBody').html());
		// Supprimer notre infobulle
		$(this).children('div#tooltip').remove();
	});
}); 
</script>
</body>
</html>
