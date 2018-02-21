<?php
$CalcPvAutonomeVersion='4.2';
include_once('./lib/Fonction.php');
$config_ini = parse_ini_file('./config.ini', true); 
$lang_ini = parse_ini_file('./lang/lang.ini',true);

// Dans les URL on utilisera les codes langues https://support.crowdin.com/api/language-codes/
// On a une fonction pour retrouve le local à partir (et vis et versa)

// Fonction de langue : 
include('./lang/lang-dispo.php');

// Détection et redirection (langue toujours)
if (isset($_GET['langue'])) {
	$locale = lang2locale($_GET['langue']);
	$localeshort=locale2lang($locale);
	if ($_COOKIE['langue'] != $localeshort) {
		setcookie("langue",$localeshort,strtotime( '+1 year' ));
	}
} elseif (isset($_COOKIE['langue'])) {
	$locale = lang2locale($_COOKIE['langue']);
	$localeshort=locale2lang($_COOKIE['langue']);
	header('Location: '.addLang2url($locale));
} else {
	$locale = lang2locale($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$localeshort=locale2lang($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	header('Location: '.addLang2url($locale));
	exit();
}

// Définition de la langue :
$results=putenv("LC_ALL=$locale.utf8");
if (!$results) {
    exit ('putenv failed');
}
$results=putenv("LC_LANG=$locale.utf8");
if (!$results) {
    exit ('putenv failed');
}
$results=putenv("LC_LANGUAGE=$locale.utf8");
if (!$results) {
    exit ('putenv failed');
}
$results=setlocale(LC_ALL, "$locale.utf8");
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
	<title>[CalcPvAutonome] <?= _('Calculate/size photovoltaic stand-alone (autonomous) set') ?></title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link href="./lib/style.css" media="screen" rel="stylesheet" type="text/css" />	
	<meta http-equiv="Pragma" content="no-cache">
	<meta http-equiv="Expires" content="-1">
	<?php if ($localeshort == 'aa') { ?>
		<script type="text/javascript">
			var _jipt = [];
			_jipt.push(['project', 'calcpvautonome']);
		</script>
		<script type="text/javascript" src="//cdn.crowdin.com/jipt/jipt.js"></script>
	<?php } ?>
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
		<div id="langues">
			<?php 
			ksort($lang_ini);
			foreach($lang_ini as $lang) {
				$flag='';
				if ($localeshort == $lang['code']) {
					$flag=' drapeauActif';
				}
				echo '<a id="href'.$lang['code'].'" href="'.replaceLang2url($lang['code']).'"><img class="drapeau'.$flag.'" src="./lib/'.$lang['code'].'.png" alt="'.$lang['code'].'" width="23" height="15" /></a>';
				echo '<script type="text/javascript">
					$( "#href'.$lang['code'].'" ) .mouseover(function() {
						$("#languesLegende").show();';
						if ($lang['code'] == 'aa') {
							echo '$("#languesLegende").html("<b>Contribute to translation</b> (in-context)");';
						} else if ($lang['translated_progress'] < 90) {
							echo '$("#languesLegende").html("<b>'.$lang['code'].'</b> '._('partial translation').' ('.$lang['translated_progress'].'%)");';
						} else {
							echo '$("#languesLegende").html("<b>'.$lang['code'].'</b> ('.$lang['translated_progress'].'%)");';
						}
				echo '})
					.mouseout(function() {
						$("#languesLegende").hide();
					});
					</script>';
			}
			?>
			<!--<a href="https://crwd.in/calcpvautonome"><img class="drapeau" src="./lib/trad.png" alt="Help to translate" /></a>-->
		</div>
		<div id="languesLegende" style="display: none"></div>
	<div id="page-wrap">
		<?php
		$footer=true;
		if (isset($_GET['p']) && $_GET['p'] == 'CalcConsommation'
		 || isset($_GET['p']) && $_GET['p'] == 'CalcConso'
		 || $_SERVER['HTTP_HOST'] == 'conso.calcpv.net'
		 || $_SERVER['HTTP_HOST'] == 'calconso.zici.fr'
		 || $_SERVER['HTTP_HOST'] == 'calcconso.zici.fr') {
			echo '<h1>'._('Calculate daily electric needs').'</h1>';
			@include_once('./header.php');
			echo _('<p>Go to the <a href="https://crwd.in/calcpvautonome" target="_blank">colaborative translation platform</a> to help us translate this free software.</p>');
			include('./CalcConsommation.php'); 
			@include_once('./bottom.php'); 
		} elseif (isset($_GET['p']) && $_GET['p'] == 'Modeles') {
			include('./Modeles.php'); 
			$footer=false;
		} else {
			echo '<h1>'._('Calculate/size photovoltaic stand-alone (autonomous) set').'</h1>';
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
				$nb_translator=0;
				foreach($lang_ini[$localeshort] as $key => $value) {
					if (substr($key, 0, 10) == 'translator') {
						$nb_translator++;
						if ($nb_translator != 1) {
							echo ', ';
						}
						echo $value['name'];
					}
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
