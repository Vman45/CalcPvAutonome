<?php
// Retir les accents
// http://www.weirdog.com/blog/php/supprimer-les-accents-des-caracteres-accentues.html
function wd_remove_accents($str)
{
    $str = htmlentities($str, ENT_NOQUOTES); 
    $str = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
    $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
    $str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères
    return $str;
}
// Ajoute le ° si c'est une valeur chiffré
function ajoutDegSiAngleChiffre($deg) {
	if (preg_match('#[0-9]+$#',$deg)) {
		return $deg.'°';
	} else {
		return $deg;
	}
}
// Transforme 'sud' en 0°
function nomEnAngle($val) {
	if (!preg_match('#^[0-9]+$#',$val)) {
		switch($val) {
			case 'Sud':
			case 'sud':
				$val='0°';
			break;
			case 'Est':
			case 'est':
				$val='-90°';
			break;
			case 'Ouest':
			case 'ouest':
				$val='90°';
			break;
			case 'Nord':
			case 'nord':
				$val='180°';
			break;
		} 
	}
	return $val;
}
// Formulaire afficher ce qui est en get ou ce qui est dans la config
function valeurRecup($nom) {
	global $config_ini;
	if (isset($_GET[$nom])) {
		echo $_GET[$nom]; 
	} else if (isset($config_ini['formulaire'][$nom])) {
		echo $config_ini['formulaire'][$nom];
	} else {
		echo '';
	}
}
function valeurRecupCookie($nom) {
	global $config_ini;
	if (isset($_GET[$nom])) {
		echo $_GET[$nom]; 
	} else if (isset($_COOKIE[$nom])) {
		echo $_COOKIE[$nom];
	} else if (isset($config_ini['formulaire'][$nom])) {
		echo $config_ini['formulaire'][$nom];
	} else {
		echo '';
	}
}
function valeurRecupCookieSansConfig($nom) {
	global $config_ini;
	if (isset($_GET[$nom])) {
		echo $_GET[$nom]; 
	} else if (isset($_COOKIE[$nom])) {
		echo $_COOKIE[$nom];
	} else {
		echo '';
	}
}

// Forumaire sur les select mettre le "selected" au bon endroit selon le get ou la config
function valeurRecupSelect($nom, $valeur) {
	global $config_ini;
	if ($_GET[$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else if (empty($_GET[$nom]) && $config_ini['formulaire'][$nom] == $valeur) {
		echo ' selected="selected"'; 
	} else {
		echo '';
	}
}
// Convertie les nombre pour l'affichage ou pour les calculs
function convertNumber($number, $to = null) {
	if ($to == 'print') {
		return number_format($number, 0, ',', '');
	} else {
		return number_format($number, 3, '.', '');
	}
}

// Affichage du debug
function debug($msg, $balise=null) {
	if (isset($_GET['debug'])) {
		if (isset($balise)) {
			echo '<'.$balise.' class="debug">'.$msg.'</'.$balise.'>';
		} else {
			echo $msg;
		}
	}
}


// Recherche bonne config régulateur
function chercherRegulateur() {
	
	global $nbRegulateur,$parcPvW,$parcPvV,$parcPvI,$config_ini,$U,$meilleurParcBatterie,$_GET,$batICharge;
	
	$meilleurRegulateur['nom'] = null;
	$meilleurRegulateur['diffRegulateurParcPvW'] = 99999;
	$meilleurRegulateur['diffRegulateurParcPvV'] = 99999;
	$meilleurRegulateur['diffRegulateurParcPvA'] = 99999	;
	
	debug('<ul type="1" class="debug">');
	
	if ($_GET['ModRegu'] == 'perso') {
		// Mode perso
		
		debug('<li>');
		debug('Avec le régulateur perso indiqué');


		debug($parcPvW.'&lt;'.$_GET['PersoReguPmaxPv'].'W, ');
		debug($parcPvV.'&lt;'.$_GET['PersoReguVmaxPv'].'V, ');
		debug($parcPvI.'&lt;'.$_GET['PersoReguImaxPv'].'A');

		if ($parcPvW < $_GET['PersoReguPmaxPv']
		&& $parcPvV < $_GET['PersoReguVmaxPv']
		&& $parcPvI < $_GET['PersoReguImaxPv']) {
			debug(' | ** ça fonctionne ** ');
			$meilleurRegulateur['nom'] = '(personnalisé)';
			$meilleurRegulateur['Vbat'] = $_GET['U'];
			$meilleurRegulateur['PmaxPv'] = $_GET['PersoReguPmaxPv'];
			$meilleurRegulateur['VmaxPv'] = $_GET['PersoReguVmaxPv'];
			$meilleurRegulateur['ImaxPv'] = $_GET['PersoReguImaxPv'];
			$meilleurRegulateur['Prix'] = $regulateur['Prix'];
		} 
	
	debug('</li>');
	
	} else {
		// Mode auto ou choisie
		foreach ($config_ini['regulateur'] as $idRegulateur => $regulateur) {
			
			// Si un modèle de régulateur à été choisie, on est plus en mode automatique
			if ($_GET['ModRegu'] != 'auto' && $_GET['ModRegu'] != substr($idRegulateur, 0, -3)) {
				continue;
			}
			
			// On conserve uniquement les références supportant la même tension Vbat
			if ($U != $regulateur['Vbat']) {
				continue;
			}
			
			// Debug
			debug('<li>');
			debug('Le régulateur type '.$regulateur['nom'].' à les caractéristiques suivantes : ');
			debug('<ul>');
			debug('Puissance maximum de panneaux accepté '.$regulateur['PmaxPv'].'W, le parc envisagé est à '.$parcPvW, 'li');
			debug('Tension PV maximum de circuit ouvert accepté '.$regulateur['VmaxPv'].'V, le parc envisagé est à '.$parcPvV, 'li');
			debug('Courant de court-circuit PV maximal accepté '.$regulateur['ImaxPv'].'A, le parc envisagé est à '.$parcPvI, 'li');
			debug('</ul>');
			if ($parcPvW < $regulateur['PmaxPv']
			&& $parcPvV < $regulateur['VmaxPv']
			&& $parcPvI < $regulateur['ImaxPv']) {
				debug('<li>Donc ça fonctionne !</li>', 'ul');
			} else {
				continue;
			}
			
			// Différence avec la capacité souhauté
			$diffRegulateurParcPvW=$regulateur['PmaxPv']-$parcPvW;
			$diffRegulateurParcPvV=$regulateur['VmaxPv']-$parcPvV;
			$diffRegulateurParcPvI=$regulateur['ImaxPv']-$parcPvI;
			
			if ($diffRegulateurParcPvW < $meilleurRegulateur['diffRegulateurParcPvW']
			|| $diffRegulateurParcPvV < $meilleurRegulateur['diffRegulateurParcPvV']
			|| $diffRegulateurParcPvI < $meilleurRegulateur['diffRegulateurParcPvA']) {
				debug('<li><b>Meilleur configuration</b> jusqu\'à présent car le parc est au plus prêt des caractéristiques de notre régulateur</li>', 'ul');
				$meilleurRegulateur['diffRegulateurParcPvW'] = $diffRegulateurParcPvW;
				$meilleurRegulateur['diffRegulateurParcPvV'] = $diffRegulateurParcPvV;
				$meilleurRegulateur['diffRegulateurParcPvA'] = $diffRegulateurParcPvI;
				$meilleurRegulateur['nom'] = $regulateur['nom'];
				$meilleurRegulateur['Vbat'] = $regulateur['Vbat'];
				$meilleurRegulateur['PmaxPv'] = $regulateur['PmaxPv'];
				$meilleurRegulateur['VmaxPv'] = $regulateur['VmaxPv'];
				$meilleurRegulateur['ImaxPv'] = $regulateur['ImaxPv'];
				$meilleurRegulateur['Prix'] = $regulateur['Prix'];
			}
			debug('</li>');
		}
	}
	debug('</ul>');

	return $meilleurRegulateur;
	
}
// On cherche le bon câble
function chercherCable_SecionAudessus($sectionMinimum) {
	global $config_ini;
	debug('<ul class="debug">');
	foreach ($config_ini['cablage'] as $idCable => $cable) {
		debug('Pour une section minimum de '.$sectionMinimum.', on test '.$cable['diametre'], 'li');
		if ($sectionMinimum < $cable['diametre']) {
			debug('<li>C\'est bon car '.$sectionMinimum.' < '.$cable['diametre'].'</li>', 'ul');
			$meilleurCable['nom']=$cable['nom'];
			$meilleurCable['diametre']=$cable['diametre'];
			$meilleurCable['prix']=$cable['prix'];
			break;
		}
	}
	debug('</ul>');
	return $meilleurCable;
}
function chercherCable_SecionPlusProche($sectionMinimum) {
	global $config_ini;
	debug('<ul class="debug">');
	$meilleurCable['diffSection']=9999;
	foreach ($config_ini['cablage'] as $idCable => $cable) {
		$diffSection=$sectionMinimum-$cable['diametre'];
		// Si la différence est négative on la met positive pour pouvoir la comparer
		if ($diffSection < 0) {
			$diffSection=$diffSection*-1;
		}
		debug('Pour une section la plus proche de '.$sectionMinimum.', on test '.$cable['diametre'].', il y a une différence de '.$diffSection.', li');
		if ($diffSection <= $meilleurCable['diffSection']) {
			$meilleurCable['nom']=$cable['nom'];
			$meilleurCable['diametre']=$cable['diametre'];
			$meilleurCable['prix']=$cable['prix'];
			$meilleurCable['diffSection']=$diffSection;
			debug('<li>La différence est la plus faible, c\'est la meilleur bonne configuration</li>', 'ul');
		}
	}
	debug('</ul>');

	return $meilleurCable;
	
}

// On cherche le bon convertisseur
function chercherConvertisseur($U,$Pmax) {
	global $config_ini;
	debug('<ul class="debug">');
	foreach ($config_ini['convertisseur'] as $convertisseur) {
		if ($U == $convertisseur['Vbat']) {
			debug('Test pour le convertisseur '.$convertisseur['nom'], 'li');
			if ($Pmax <= $convertisseur['Pmax']) {
				debug('<li>Il est capable de délivrer '.$convertisseur['Pmax'].'W, c\'est le bon !</li>', 'ul');
				$meilleurConvertisseur['nom']=$convertisseur['nom'];
				$meilleurConvertisseur['Pmax']=$convertisseur['Pmax'];
				$meilleurConvertisseur['Ppointe']=$convertisseur['Ppointe'];
				$meilleurConvertisseur['VA']=$convertisseur['VA'];
				break;
			}
		}
	}
	debug('</ul>');
	return $meilleurConvertisseur;
}

// Pour les erreurs dans le formulaire
function erreurDansLeFormulaireValue0($id, $msg) {
	global $erreurDansLeFormulaire, $_GET;
	if (empty($_GET[$id]) || $_GET[$id] < 0) {
		$erreurDansLeFormulaire['nb']++;
		$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint($id, $msg);
	}
	return $erreurDansLeFormulaire;
}

// Affichage des erreurs du formulaire
function erreurPrint($id, $msg) {
	return '<li>'.$msg.'</li>';
}

// Récupérer l'IP
function get_ip() {
	// IP si internet partagé
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	}
	// IP derrière un proxy
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	// Sinon : IP normale
	else {
		return (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '');
	}
}

function pgvisGetSHScalc($FichierDataCsv, $raddatabase) {
	// Documentation PVGIS : 
	// http://re.jrc.ec.europa.eu/pvg_static/web_service.html#SA
	global $config_ini;
	global $Pc;
	global $Cap;
	global $U;
	// Vide le cache trop vieux
	if (file_exists($FichierDataCsv) && filemtime($FichierDataCsv)+$config_ini['pvgis']['cacheTime'] < time()) {
		debug('Le fichier de cache de PGVIS '.$FichierDataCsv.' est trop vieux, on le supprime !','p');
		unlink($FichierDataCsv);
	}
	if (!file_exists($FichierDataCsv)) {
		$url=$config_ini['pvgis']['urlSHScalc'].'?raddatabase='.$raddatabase.'&lat='.$_GET['lat'].'&lon='.$_GET['lon'].'&angle='.$_GET['inclinaison'].'&aspect='.$_GET['orientation'].'&peakpower='.convertNumber($Pc, 'print').'&batterysize='.convertNumber($Cap, 'print')*$U.'&cutoff='.$config_ini['pvgis']['cutoff'].'&consumptionday='.$_GET['Bj'].'&usehorizon=1outputformat=csv&browser=0';
		debug('On télécharge les données depuis PGVIS avec l\'URL '.$url, 'p');
		$fp = fopen($FichierDataCsv, 'w+');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1000);     
		curl_setopt($ch, CURLOPT_USERAGENT, 'any');
		curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		fclose($fp);
		if ($curl_errno > 0) {
			debug('Erreur de téléchargement cURL Error ('.$curl_errno.'): '.$curl_error.'\n', 'p');
			unlink($FichierDataCsv);
			return false;
		} else {
			return true;
		}
	} else {
		debug('On utilise le cache de PGVIS '.$FichierDataCsv, 'p');
		return true;
	}
}

function pgvisGetDRcalc($FichierDataCsv, $raddatabase) {
	// Documentation PVGIS : 
	// http://re.jrc.ec.europa.eu/pvg_static/web_service.html#DR
	global $config_ini;
	// Vide le cache trop vieux
	if (file_exists($FichierDataCsv) && filemtime($FichierDataCsv)+$config_ini['pvgis']['cacheTime'] < time()) {
		debug('Le fichier de cache de PGVIS '.$FichierDataCsv.' est trop vieux, on le supprime !','p');
		unlink($FichierDataCsv);
	}
	if (!file_exists($FichierDataCsv)) {
		if (isset($_GET['tracking'])) {
			$url=$config_ini['pvgis']['urlDRcalc'].'?raddatabase='.$raddatabase.'&lat='.$_GET['lat'].'&lon='.$_GET['lon'].'&month=0&global=0&usehorizon=1&glob_2axis=1&clearsky=0&clearsky_2axis=0&showtemperatures=0&localtime=0&outputformat=csv&browser=0';
		} else {
			$url=$config_ini['pvgis']['urlDRcalc'].'?raddatabase='.$raddatabase.'&lat='.$_GET['lat'].'&lon='.$_GET['lon'].'&angle='.$_GET['inclinaison'].'&aspect='.$_GET['orientation'].'&month=0&global=1&usehorizon=1&glob_2axis=0&clearsky=0&clearsky_2axis=0&showtemperatures=0&localtime=0&outputformat=csv&browser=0';
		}
		debug('On télécharge les données depuis PGVIS avec l\'URL '.$url, 'p');
		$fp = fopen($FichierDataCsv, 'w+');
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1000);     
		curl_setopt($ch, CURLOPT_USERAGENT, 'any');
		curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		fclose($fp);
		if ($curl_errno > 0) {
			debug('Erreur de téléchargement cURL Error ('.$curl_errno.'): '.$curl_error.'\n', 'p');
			unlink($FichierDataCsv);
			return false;
		} else {
			return true;
		}
	} else {
		debug('On utilise le cache de PGVIS '.$FichierDataCsv, 'p');
		return true;
	}
}

function pgvisParseDataSHScalc($FichierDataCsv) {
	$mois=0;
	$debutCollecte=false;
	$finCollecte=false;
	$debutCollecteState=false;
	$finCollecteState=false;
	foreach(file($FichierDataCsv) as $line) {
		//echo $line."\n";
		$Datas = explode("\t\t", $line);
		//print_r($Datas);
		
		// Mois :
		// Une ligne blanche signifie la fin des données
		if ($debutCollecte==true && empty($Datas[1])) {
			$finCollecte=true;
			//echo "fin";
		}
		if ($debutCollecte==true && $finCollecte==false) {
			//echo "collecte";
			$mois++;
			$SHScalc['DataMonth'][$mois]['Ed']=$Datas[1];
			$SHScalc['DataMonth'][$mois]['El']=$Datas[2];
			$SHScalc['DataMonth'][$mois]['Ff']=$Datas[3];
			$SHScalc['DataMonth'][$mois]['Fe']=$Datas[4];
		}
		// on trouve 'time" c'est que c'est le début des données
		if ($Datas[0] == 'Month') {
			//echo "début de collecte";
			$debutCollecte=true;
		}
		
		// % état charge :		
		// Une ligne blanche signifie la fin des données
		if ($debutCollecteState==true && empty($Datas[1])) {
			$finCollecteState=true;
			//echo "fin";
		}
		if ($debutCollecteState==true && $finCollecteState==false) {
			//echo "collecte";
			$SHScalc['DataState'][$Datas[0]]=$Datas[1];
		}
		// on trouve 'time" c'est que c'est le début des données
		if ($Datas[0] == 'Cs') {
			//echo "début de collecte";
			$debutCollecteState=true;
		}
	}
	return $SHScalc;
}

function pgvisParseData($FichierDataCsv) {
	$csv = array_map('str_getcsv', file($FichierDataCsv));
	$mois=0;
	$debutCollecte=false;
	$finCollecte=false;
	foreach($csv as $csvLigne) {
		//echo $csvLigne[0]."\n";
		if ($debutCollecte==true && $finCollecte==false) {
			$Datas = explode("\t\t", $csvLigne[0]);
			if (substr($Datas[0], 0, 3) == '00:') {
				$mois++;
				$GlobalIradiation[$mois]=0;
			}
			$GlobalIradiation[$mois]=$GlobalIradiation[$mois]+$Datas[1]/1000;
		}
		// on trouve 'time" c'est que c'est le début des données
		if ($csvLigne[0] == 'Time') {
			$debutCollecte=true;
		}
		// Une ligne blanche signifie la fin des données
		if ($debutCollecte==true && $csvLigne[0] == '') {
			$finCollecte=true;
		}
	}
	return $GlobalIradiation;
}


// Ajoute la langue à une URL qui n'en a pas
function addLang2url($lang) {
	global $_SERVER;
	$URIexplode=explode('?', $_SERVER['REQUEST_URI']);
	if ($URIexplode[1] != '') {
		return $URIexplode[0].$lang.'?'.$URIexplode[1];
	} else {
		return $URIexplode[0].$lang;
	}
}
function replaceLang2url($lang) {
	global $_SERVER;
	$URIexplode=explode('?', $_SERVER['REQUEST_URI']);
	$debutUrl=substr($URIexplode[0], 0, -langCountChar($URIexplode[0]));
	if ($URIexplode[1] != '') {
		return $debutUrl.$lang.'?'.$URIexplode[1];
	} else {
		return $debutUrl.$lang;
	}
}
function langCountChar($url) {
	// $url reçu c'est l'URL avant la query : ?machin=1
	if (preg_match('#/sr-Cyrl-ME$#',$url)) {
		return 10;
	} elseif (preg_match('#/[a-z]{2}-[A-Z]{2}$#',$url)) {
		return 5;
	} elseif (preg_match('#/[a-z]{3}-[A-Z]{2}$#',$url)) {
		return 6;
	} elseif (preg_match('#/[a-z]{3}$#',$url)) {
		return 3;
	} elseif (preg_match('#/[a-z]{2}$#',$url)) {
		return 2;
	}
}


function SendEmail($recipient, $sujet, $message) {
	global $db_ini;
	$header = "From: ".$db_ini['config']['EmailFrom']."\n";
	$header.= "MIME-Version: 1.0\n";
	$message=$message;
	mail($recipient,'[calcpv] '.$sujet,$message,$header);
}

function socialShare($url) {
	if ($url == '') {
		$url='#';
	}
	echo '<div id="shareSocial">
        <!-- share https://sharingbuttons.io/ -->
        
		<!-- Sharingbutton E-Mail -->
		<a class="resp-sharing-button__link email" href="mailto:?subject=CalcPv&body='.urlencode($url).'" target="_self" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--email resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 0C5.38 0 0 5.38 0 12s5.38 12 12 12 12-5.38 12-12S18.62 0 12 0zm8 16c0 1.1-.9 2-2 2H6c-1.1 0-2-.9-2-2V8c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v8z"/><path d="M17.9 8.18c-.2-.2-.5-.24-.72-.07L12 12.38 6.82 8.1c-.22-.16-.53-.13-.7.08s-.15.53.06.7l3.62 2.97-3.57 2.23c-.23.14-.3.45-.15.7.1.14.25.22.42.22.1 0 .18-.02.27-.08l3.85-2.4 1.06.87c.1.04.2.1.32.1s.23-.06.32-.1l1.06-.9 3.86 2.4c.08.06.17.1.26.1.17 0 .33-.1.42-.25.15-.24.08-.55-.15-.7l-3.57-2.22 3.62-2.96c.2-.2.24-.5.07-.72z"/></svg>
			</div>
		  </div>
		</a>
		
        <!-- Sharingbutton Facebook -->
		<a class="resp-sharing-button__link facebook" href="https://facebook.com/sharer/sharer.php?u='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--facebook resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 0C5.38 0 0 5.38 0 12s5.38 12 12 12 12-5.38 12-12S18.62 0 12 0zm3.6 11.5h-2.1v7h-3v-7h-2v-2h2V8.34c0-1.1.35-2.82 2.65-2.82h2.35v2.3h-1.4c-.25 0-.6.13-.6.66V9.5h2.34l-.24 2z"/></svg>
			</div>
		  </div>
		</a>

		<!-- Sharingbutton Twitter -->
		<a class="resp-sharing-button__link twitter" href="https://twitter.com/intent/tweet/?text=CalcPv&url='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--twitter resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 0C5.38 0 0 5.38 0 12s5.38 12 12 12 12-5.38 12-12S18.62 0 12 0zm5.26 9.38v.34c0 3.48-2.64 7.5-7.48 7.5-1.48 0-2.87-.44-4.03-1.2 1.37.17 2.77-.2 3.9-1.08-1.16-.02-2.13-.78-2.46-1.83.38.1.8.07 1.17-.03-1.2-.24-2.1-1.3-2.1-2.58v-.05c.35.2.75.32 1.18.33-.7-.47-1.17-1.28-1.17-2.2 0-.47.13-.92.36-1.3C7.94 8.85 9.88 9.9 12.06 10c-.04-.2-.06-.4-.06-.6 0-1.46 1.18-2.63 2.63-2.63.76 0 1.44.3 1.92.82.6-.12 1.95-.27 1.95-.27-.35.53-.72 1.66-1.24 2.04z"/></svg>
			</div>
		  </div>
		</a>

		<!-- Sharingbutton Reddit -->
		<a class="resp-sharing-button__link reddit" href="https://reddit.com/submit/?url='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--reddit resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="9.391" cy="13.392" r=".978"/><path d="M14.057 15.814c-1.14.66-2.987.655-4.122-.004-.238-.138-.545-.058-.684.182-.13.24-.05.545.19.685.72.417 1.63.646 2.568.646.93 0 1.84-.228 2.558-.642.24-.13.32-.44.185-.68-.14-.24-.445-.32-.683-.18zM5 12.086c0 .41.23.78.568.978.27-.662.735-1.264 1.353-1.774-.2-.207-.48-.334-.79-.334-.62 0-1.13.507-1.13 1.13z"/><path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zm6.673 14.055c.01.104.022.208.022.314 0 2.61-3.004 4.73-6.695 4.73s-6.695-2.126-6.695-4.74c0-.105.013-.21.022-.313C4.537 13.73 4 12.97 4 12.08c0-1.173.956-2.13 2.13-2.13.63 0 1.218.29 1.618.757 1.04-.607 2.345-.99 3.77-1.063.057-.803.308-2.33 1.388-2.95.633-.366 1.417-.323 2.322.085.302-.81 1.076-1.397 1.99-1.397 1.174 0 2.13.96 2.13 2.13 0 1.177-.956 2.133-2.13 2.133-1.065 0-1.942-.79-2.098-1.81-.734-.4-1.315-.506-1.716-.276-.6.346-.818 1.395-.88 2.087 1.407.08 2.697.46 3.728 1.065.4-.468.987-.756 1.617-.756 1.17 0 2.13.953 2.13 2.13 0 .89-.54 1.65-1.33 1.97z"/><circle cx="14.609" cy="13.391" r=".978"/><path d="M17.87 10.956c-.302 0-.583.128-.79.334.616.51 1.082 1.112 1.352 1.774.34-.197.568-.566.568-.978 0-.623-.507-1.13-1.13-1.13z"/></svg>
			</div>
		  </div>
		</a>

		<!-- Sharingbutton WhatsApp -->
		<a class="resp-sharing-button__link watsapp" href="whatsapp://send?text=CalcPv='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--whatsapp resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" height="24" width="24" viewBox="0 0 24 24"><path d="m12 0c-6.6 0-12 5.4-12 12s5.4 12 12 12 12-5.4 12-12-5.4-12-12-12zm0 3.8c2.2 0 4.2 0.9 5.7 2.4 1.6 1.5 2.4 3.6 2.5 5.7 0 4.5-3.6 8.1-8.1 8.1-1.4 0-2.7-0.4-3.9-1l-4.4 1.1 1.2-4.2c-0.8-1.2-1.1-2.6-1.1-4 0-4.5 3.6-8.1 8.1-8.1zm0.1 1.5c-3.7 0-6.7 3-6.7 6.7 0 1.3 0.3 2.5 1 3.6l0.1 0.3-0.7 2.4 2.5-0.7 0.3 0.099c1 0.7 2.2 1 3.4 1 3.7 0 6.8-3 6.9-6.6 0-1.8-0.7-3.5-2-4.8s-3-2-4.8-2zm-3 2.9h0.4c0.2 0 0.4-0.099 0.5 0.3s0.5 1.5 0.6 1.7 0.1 0.2 0 0.3-0.1 0.2-0.2 0.3l-0.3 0.3c-0.1 0.1-0.2 0.2-0.1 0.4 0.2 0.2 0.6 0.9 1.2 1.4 0.7 0.7 1.4 0.9 1.6 1 0.2 0 0.3 0.001 0.4-0.099s0.5-0.6 0.6-0.8c0.2-0.2 0.3-0.2 0.5-0.1l1.4 0.7c0.2 0.1 0.3 0.2 0.5 0.3 0 0.1 0.1 0.5-0.099 1s-1 0.9-1.4 1c-0.3 0-0.8 0.001-1.3-0.099-0.3-0.1-0.7-0.2-1.2-0.4-2.1-0.9-3.4-3-3.5-3.1s-0.8-1.1-0.8-2.1c0-1 0.5-1.5 0.7-1.7s0.4-0.3 0.5-0.3z"/></svg>
			</div>
		  </div>
		</a>

		<!-- Sharingbutton Hacker News -->
		<a class="resp-sharing-button__link ycombinator" href="https://news.ycombinator.com/submitlink?t=CalcPv&u='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--hackernews resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256"><path fill-rule="evenodd" d="M128 256c70.692 0 128-57.308 128-128C256 57.308 198.692 0 128 0 57.308 0 0 57.308 0 128c0 70.692 57.308 128 128 128zm-9.06-113.686L75 60h20.08l25.85 52.093c.397.927.86 1.888 1.39 2.883.53.994.995 2.02 1.393 3.08.265.4.463.764.596 1.095.13.334.262.63.395.898.662 1.325 1.26 2.618 1.79 3.877.53 1.26.993 2.42 1.39 3.48 1.06-2.254 2.22-4.673 3.48-7.258 1.26-2.585 2.552-5.27 3.877-8.052L161.49 60h18.69l-44.34 83.308v53.087h-16.9v-54.08z"/></svg>
			</div>
		  </div>
		</a>

		<!-- Sharingbutton Telegram -->
		<a class="resp-sharing-button__link telegram" href="https://telegram.me/share/url?text=CalcPv&url='.urlencode($url).'" target="_blank" aria-label="">
		  <div class="resp-sharing-button resp-sharing-button--telegram resp-sharing-button--small"><div aria-hidden="true" class="resp-sharing-button__icon resp-sharing-button__icon--solidcircle">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 23.5c6.35 0 11.5-5.15 11.5-11.5S18.35.5 12 .5.5 5.65.5 12 5.65 23.5 12 23.5zM2.505 11.053c-.31.118-.505.738-.505.738s.203.62.513.737l3.636 1.355 1.417 4.557a.787.787 0 0 0 1.25.375l2.115-1.72a.29.29 0 0 1 .353-.01L15.1 19.85a.786.786 0 0 0 .746.095.786.786 0 0 0 .487-.573l2.793-13.426a.787.787 0 0 0-1.054-.893l-15.568 6z" fill-rule="evenodd"/></svg>
			</div>
		  </div>
		</a>
	</div>';
}
?>
