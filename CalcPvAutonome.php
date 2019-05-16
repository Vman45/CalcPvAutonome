<link rel="stylesheet" href="./lib/leaflet.css" integrity="sha512-M2wvCLH6DSRazYeZRIm1JnYyh22purTM+FDB5CsyxtQJYeKq83arPe5wgbNmcFXGqiSH2XR8dT/fJISVA1r/zQ==" crossorigin=""/>
<script src="./lib/leaflet.js" integrity="sha512-lInM/apFSqyy1o6s89K4iQUKg6ppXEgsVxT35HbzUupEVRh2Eu9Wdl4tHj7dZO0s1uvplcYGmt3498TtHq+log==" crossorigin=""></script>
<script src="./lib/Chart.min.js"></script>
<?php 

// Mois
$mois = array (
	1 => _('January'),
	2 => _('February'),
	3 => _('March'),
	4 => _('April'),
	5 => _('May'),
	6 => _('June'),
	7 => _('July'),
	8 => _('August'),
	9 => _('September'),
	10 => _('October'),
	11 => _('November'),
	12 => _('December'),
);
// PGVIS databases
$raddatabases = array (
	'PVGIS-CMSAF',
	'PVGIS-SARAH',
	'PVGIS-NSRDB',
);
// Les bules d'aides 
$aideInclinaison='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/inclinaison.htm\',\'Z\',\'status=no ,scrollbars=no,width=350,height=350,top=50,left=50\')">?</a>)';
$aideOrientation='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/orientation.htm\',\'Z\',\'status=no ,scrollbars=no,width=350,height=350,top=50,left=50\')">?</a>)';
$aideAlbedo='(<a onClick="window.open(\'http://ines.solaire.free.fr/pages/albedo.htm\',\'Albedo\',\'status=no ,scrollbars=no,width=450,height=700,top=0,left=50\')">?</a>)';

/*
 * ####### Résultat #######
*/

if (isset($_GET['submit'])) {
	echo '<div class="part result">';
	debug('Cette couleur représente le mode transparent / debug','p');
	// Détection des erreurs de formulaires
	$erreurDansLeFormulaire['nb']=0;
	
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Bj', _('Daily need is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Pmax', _('Maximum power requirement is not correct because < 0'));
	if ($_GET['ModPv'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvW', _('Panel\'s custom power is not correct because < 0'));
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvVdoc', _('Panel\'s custom open circuit voltage (Vdoc) is not correct because < 0'));
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoPvIsc', _('Panel\'s custom short circuit current (Isc) is not correct because < 0'));
	}
	if ($_GET['ModBat'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoBatV', _('Custom battery voltage is not correct because < 0'));
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoBatAh', _('Custom battery capacity is not correct because < 0'));
	} elseif ($_GET['ModBat'] == 'auto') {
		// Assure la compatibilité avant cette fonctionnalitée
		if (empty($_GET['TypeBat'])) {
			$_GET['TypeBat'] = 'auto';
		}
	}
	if ($_GET['ModRegu'] == 'perso') {
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguVmaxPv', _('Custom charge controller voltage is incorrect because < 0'));
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguPmaxPv', _('Custom charge controller power is not correct because < 0'));
		$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('PersoReguImaxPv', _('Custom charge controller short-circuit current is incorrect because < 0'));
	}
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Aut', _('The autonomous days amount is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Rb', _('Electrical yield of batteries is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('Ri', _('Installation electrical efficiency is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('DD', _('Discharge level is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('reguMargeIcc', _('The safety margin Icc of the charge controller is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('distancePvRegu', _('Panels and charge controller in-between distance is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('distanceReguBat', _('Batteries and charge controller in-between distance is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('cablageRho', _('Conductor resistivity is not correct because < 0'));
	$erreurDansLeFormulaire=erreurDansLeFormulaireValue0('cablagePtPourcent', _('Tolerable voltage drop is not correct because < 0'));
	// Assure la compatibilité avant cette fonctionnalitée
	if (empty($_GET['cablageRegleAparMm'])) {
		$_GET['cablageRegleAparMm'] = $config_ini['formulaire']['cablageRegleAparMm'];
	}
	// S'il faut utilise PGVIS (en mode automatique)
	if (empty($_GET['Ej'])) {
		if ($_GET['orientation'] < -180 || $_GET['orientation'] > 180) {
			$erreurDansLeFormulaire['nb']++;
			$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('orientation', _('Panels orientation is not correct. '));
		} else if ($_GET['inclinaison'] < 0 || $_GET['inclinaison'] > 90) {
			$erreurDansLeFormulaire['nb']++;
			$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('inclinaison', _('Panels inclination is not correct, it must be between 0 (horizontal) and 90 (vertical)'));
		} else if (empty($_GET['lat']) || empty($_GET['lon'])) {
			$erreurDansLeFormulaire['nb']++;
			$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('LatLon', _('You must enter latitude and longitude to deduce solar irradiation with a click on the map of the world.'));
		} else {
			// On sauvegarde les coordonnées dans les cookies histoire de faciliter la vie de l'utilisateur
			setcookie('lat', $_GET['lat'], time() + 365*24*3600);
			setcookie('lon', $_GET['lon'], time() + 365*24*3600);
			foreach ($raddatabases as $RadDatabase) {
				debug('Importation des données d\'irradiation PVGIS, on test avec la raddatabase '.$RadDatabase, 'p');	
				if (isset($_GET['tracking'])) {
					$FichierDataCsv = $config_ini['pvgis']['cachePath'].'/pvgis5_DRcalc_'.$_GET['lat'].'_'.$_GET['lon'].'_'.$RadDatabase.'_tracking.csv';
				} else {
					$FichierDataCsv = $config_ini['pvgis']['cachePath'].'/pvgis5_DRcalc_'.$_GET['lat'].'_'.$_GET['lon'].'_'.$RadDatabase.'_'.$_GET['inclinaison'].'_'.$_GET['orientation'].'.csv';
				}
				if (!pgvisGetDRcalc($FichierDataCsv, $RadDatabase)) {
					$erreurDansLeFormulaire['nb']++;
					$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('pgvisGet', _('Unable to download solar irradiation data from <a href="http://re.jrc.ec.europa.eu/PVGIS5-release.html" target="_blank"> PVGIS </a>, switch to mode manual or use <a href="http://calcpvautonome.zici.fr/v2.2/"> the previous version </a> of CalcPvAutonome.'));
					break;
				} else {
					$GlobalIradiation = pgvisParseData($FichierDataCsv);
					if (isset($GlobalIradiation[12]) && $GlobalIradiation[12] != 0) {
						break;
					}
				}
			}
			if (empty($GlobalIradiation[12]) &&  $GlobalIradiation[12] == 0) {
				$erreurDansLeFormulaire['nb']++;
				$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('pgvisParse', _('Incorrect solar irradiation data. The position shown on the map may not be covered by <a href="http://re.jrc.ec.europa.eu/PVGIS5-release.html" target="_blank"> PVGIS </a>.'));	
			}

		}
	} else if ($_GET['Ej'] < 0) {
		$erreurDansLeFormulaire['nb']++;
		$erreurDansLeFormulaire['msg']=$erreurDansLeFormulaire['msg'].erreurPrint('Ej', _('Average daily radiation is not correct because < 0'));
	}
	if ($erreurDansLeFormulaire['nb'] != 0) {
		echo '<div class="erreurForm">';
		echo '<p>'._('Errors in the form prevent you from continuing, please correct :').'</p>';
		echo '<ul>'.$erreurDansLeFormulaire['msg'].'</ul>';
		echo '</div>';
	} else {
	// Pas d'erreur
	?>

	<h2 class="titre"><?= _('Result of the dimension calculation') ?></h2>
	<div style="float: right"><?php socialShare((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?></div>
	<p><?= _('<b>Warning</b>: Results are approximate, it is recommended to doublecheck with sales representative and validate your installation before buying materials.') ?> </p>
	<!-- 
		Les PV
	-->
	<h3 id="resultatPv"><?= _('Photovoltaic panel') ?></h3>
	<div id="resultCalcPv" class="calcul">
		<?php
		if (empty($_GET['Ej'])) {	
			// Graph 
			echo '<div class="chart-container" style="float: right; width:45%"><a target="_blank" href="'.$FichierDataCsv.'" rel="tooltip" class="bulles" title="Télécharger les données brutes" style="float: right"><img src="lib/dl.png" alt="DL" /></a><canvas id="myChart"></canvas></div>';
			echo '<script>
			var ctx = document.getElementById("myChart").getContext(\'2d\');
			var myChart = new Chart(ctx, {
				type: \'bar\',
				data: {
					labels: [';
			foreach ($mois as $key => $moi) {
				echo '"'.$moi.'"';
				if ($key != 12) {
					echo ', ';
				}
			}
			echo '],
					datasets: [{
						label: \''._('Average sunlight').' ('._('kWh/m&sup2;/d').')\',
						data: [';
			for ($GiNb = 1; $GiNb <= 12; $GiNb++) {
				echo str_replace(',', '.', $GlobalIradiation[$GiNb]);
				if ($GiNb != 12) {
					echo ', ';
				}
			}
			echo '],
						backgroundColor: \'rgba(255, 206, 86, 0.2)\',
						borderColor: \'rgba(255, 159, 64, 1)\',
						borderWidth: 1
					}]
				},
				options: {
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero: true
							}
						}]
					}
				}
			});
			</script>';
		}
		
		echo '<p>'._('Here we are looking for the power (peak, expressed in W) from solar panels to be mounted to fulfill your needs according to your geographical location. The formula is as follow : ').'</p>';
		echo '<p>'._('Pp = Dn / (Yb X Yi X Rd)').'</p>';
		echo '<ul>';
			echo '<li>'._('Pp (Wp) : Peak power (goal)').'</li>';
			echo '<li>'._('Dn (Wh/d) : Daily needs').' = '.$_GET['Bj'].'</li>';
			echo '<li>'._('Yb : electrical yield of batteries').' '.$_GET['Rb'].'</li>';
			echo '<li>'._('Yi : electrical yield for the remaining installation (charge controller, ...)').' = '.$_GET['Ri'].'</li>';
			echo '<li>'._('Rd : average daily radiation of the worst month in the plane of the panel (kWh/m&sup2;/d)').'</li>';
			
			// S'il faut utilise PGVIS
			if (empty($_GET['Ej'])) {	
				
				// Pour une période saisonnière sélectionné
				if (isset($_GET['periode']) && $_GET['periode'] == 'partielle') {
					$moiEnCours=0;
					while($moiEnCours != $_GET['periodeFin']) {
						if ($moiEnCours == 0){
							$moiEnCours = $_GET['periodeDebut'];
						} else {
							if ($moiEnCours == 12){
								$moiEnCours=1;
							} else {
								$moiEnCours++;
							}
						}
						$periode[]=$moiEnCours;
					}
				}
				$Ej=9999;
				debug('<ul>');
				debug('On ne gardera l\'irradiation du mois le plus défavorable pour la localisation '.$_GET['lat'].' '.$_GET['lon'].' :', 'li');
				for ($GiNb = 1; $GiNb <= 12; $GiNb++) {
					// pour l'autonomie partielle
					if (isset($_GET['periode']) && $_GET['periode'] == 'partielle' && !in_array($GiNb, $periode)) {
						continue;
					}
					debug('Au mois de '.utf8_encode($mois[$GiNb]).' l\'irradiation global est de '.$GlobalIradiation[$GiNb], 'li');
					if ($GlobalIradiation[$GiNb] < $Ej) {
						$Ej=$GlobalIradiation[$GiNb];
					}
				}
				debug('</ul>');

				echo '<ul>';
				printf('<li>'._('According to data from <a href="http://re.jrc.ec.europa.eu/PVGIS5-release.html" target="_blank">PVGIS</a>, the value for the selected location is %s kWh/m&sup2;/d').'</li>', $Ej);
				echo '</ul>';
			} else {
				$Ej = $Ej = $_GET['Ej'];
			}
			?>
		</ul>
		<p><?= _('In your case, result is') ?> : </p>
		<?php 
		$Pc = convertNumber($_GET['Bj'])/(convertNumber($_GET['Rb'])*convertNumber($_GET['Ri'])*convertNumber($Ej));
		?>
		<p><a class="more" id="resultCalcPvHide"><?= _('Hide the process') ?></a></p>
		<p><?= _('Pp') ?> = <?= $_GET['Bj'] ?> / (<?= $_GET['Rb'] ?> * <?= $_GET['Ri'] ?> * <?= $Ej ?>) = <b><?= convertNumber($Pc, 'print') ?> W</b></p>
	</div>
	
	<p><?= _('Photovoltaic panels produce electricity from sunlight (solar radiation).') ?></p>
	<p><?php printf(_('According entered data, <b>%sW</b> of solar panel are required to fulfill your daily needs of %sWh/d.'), convertNumber($Pc, 'print'), $_GET['Bj']); ?> </p>
	<p><a id="resultCalcPvShow"><?= _('See, understand the procedure, the calculation') ?></a></p>
	
	<?php	
	
	/*
	 * ####### Recherche d'une Config panneux : #######
	*/
	debug('<div>');
	debug('Recherche une hypothèse pour avoir le minimum de panneaux au plus proche des besoins de '.convertNumber($Pc,'print').' : ','span');
	/* Personnaliser */
	if ($_GET['ModPv'] == 'perso') {
		debug('Un panneaux personnalisé à été indiqué, nous allons travailler avec celui-ci uniquement.', 'span');
		// Combien de panneau ?
		$nbPv=intval($Pc / $_GET['PersoPvW'])+1;
		// Capacité déduite
		$PcParcPv=$_GET['PersoPvW']*$nbPv;
		// Différence avec la capacité souhauté
		$diffPcParc=$PcParcPv-$Pc;
		$meilleurParcPv['nbPv'] = $nbPv;
		$meilleurParcPv['diffPcParc'] = round($diffPcParc);
		$meilleurParcPv['W'] = $_GET['PersoPvW'];
		$meilleurParcPv['Vdoc'] = $_GET['PersoPvVdoc'];
		$meilleurParcPv['Isc'] = $_GET['PersoPvIsc'];
	/* Automatique selon les info's */
	} else {
		// Juste pour le debug :
		if ($_GET['TypePv'] != 'auto') {
			debug('Le type de panneau est forcé en '.$_GET['TypePv'],'p');
		}
		if ($_GET['ModPv'] == 'auto' ) {
			debug('On test tout les <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=pv\',\'Les modèles de panneaux\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">modèle de panneau</a> de ce type possibles', 'span');
		}
		if ($_GET['ModPv'] != 'auto') {
			debug('Il a été forcé le choix de travailler avec le modèle type '.$_GET['ModPv'].'W','p');
		}
		$meilleurParcPv['nbPv'] = 99999;
		$meilleurParcPv['diffPcParc'] = 99999;
		$meilleurParcPv['W'] = 0;
		debug('<ul type="1">');
		foreach ($config_ini['pv'] as $idPv => $pv) {
			// Gestion du mode automatique dans le type :
			if ($_GET['ModPv'] == 'auto' && $_GET['TypePv'] != 'auto') {
				if ($_GET['TypePv'] != $pv['type']) {
					continue;
				}
			}
			if ($_GET['ModPv'] != 'auto' && $_GET['ModPv'] != $idPv) {
				continue;
			}
			// Calcul du nombre de panneaux nessésaire 
			$nbPv=intval($Pc / $pv['W'])+1;
			// Capacité déduite
			$PcParcPv=$pv['W']*$nbPv;
			// Différence avec la capacité souhauté
			$diffPcParc=$PcParcPv-$Pc;
			// Debug
			debug('<li>');
			debug('Avec le panneau de '.$pv['W'].'W : il en faudrait '.$nbPv.' pour une puissance total de : '.$PcParcPv.'W. La différence avec le besoin est de '.convertNumber($diffPcParc,'print').'W', 'span');
			
			$savMeilleurParcPv = false;

			$diffNbPvAvecMeilleurParc=$meilleurParcPv['nbPv']-$nbPv;
			
			// Si la différence de puissance & que le nombre de PV est inférieur 
			if ($diffPcParc <= $meilleurParcPv['diffPcParc'] && $nbPv < $meilleurParcPv['nbPv']
			// Si la différence dans le nombre de panneaux avec la meilleur config n'est pas un (même critère que précédent)
			 || $diffNbPvAvecMeilleurParc != 1 && $diffPcParc <= $meilleurParcPv['diffPcParc']
			 || $diffNbPvAvecMeilleurParc != 1 && $nbPv < $meilleurParcPv['nbPv']) {
				$savMeilleurParcPv = true;
			}
			
			if ($savMeilleurParcPv) {
				# Nouvelle meilleur config
				// Debug
				debug('<ul>');
				debug('<b>Meilleur configuration</b> pour le moment (soit nb pv < à l\'hypothèse précédente, soit moins de différence avec le besoin','li');
				debug('</ul>');
				$meilleurParcPv['nbPv'] = $nbPv;
				$meilleurParcPv['diffPcParc'] = round($diffPcParc);
				$meilleurParcPv['W'] = $pv['W'];
				$meilleurParcPv['Vdoc'] = $pv['Vdoc'];
				$meilleurParcPv['Isc'] = $pv['Isc'];
				$meilleurParcPv['type'] = $pv['type'];
				$meilleurParcPv['nbPv'] = $nbPv;
			}
			debug('</li>');
		}
		debug('</ul>');
	}
	debug('</div>');
	if ($_GET['ModPv'] == 'auto') {
		printf('<p>'._('One possibility could be to have <b>%d panel(s)</b> %s of <b>%dW</b> each').' (<a rel="tooltip" class="bulles" title="'._('Specificity of the panel').' : <br />P = %dW<br />Vdoc =%sV<br />Isc = %sA">?</a>). '._('Which extend the unit capacity to %dW').'</p>', $meilleurParcPv['nbPv'], $meilleurParcPv['type'], $meilleurParcPv['W'], $meilleurParcPv['W'], $meilleurParcPv['Vdoc'], $meilleurParcPv['Isc'], $meilleurParcPv['W']*$meilleurParcPv['nbPv']);
	}elseif ($_GET['ModPv'] == 'perso') {	
		printf('<p>'._('With your personnalized solar panel the hypothesis would be to have <b>%d panel(s)</b> of <b>%dW</b> each, which could raise the plant up to %dW'), $meilleurParcPv['nbPv'], $meilleurParcPv['W'], $meilleurParcPv['W']*$meilleurParcPv['nbPv']);
	} else {
		printf('<p>'._('With %s selected panel of <b>%dW</b>, one hypothesis would be to have <b>%d of panel(s)</b>').' (<a rel="tooltip" class="bulles" title="'._('Specificity of the panel').' : <br />P = %dW<br />Vdoc =%sV<br />Isc = %sA">?</a>) '._('Which extend the unit capacity to %dW').'</p>', $meilleurParcPv['type'], $meilleurParcPv['W'], $meilleurParcPv['nbPv'], $meilleurParcPv['W'], $meilleurParcPv['Vdoc'], $meilleurParcPv['Isc'], $meilleurParcPv['W']*$meilleurParcPv['nbPv']);
	}
	?>
	<!-- 
		Les batteries
	-->
	<h3 id="resultatBat"><?= _('Batterie') ?></h3>
	<div id="resultCalcBat" class="calcul">
		<p><?= _('We are looking here for the nominal capacity expressed in ampere per hour (Ah, given in <a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">C10</a>)') ?></p>
		<?php 
		// Si la tension U à été mise en automatique ou que le niveau n'est pas expert
		if ($_GET['U'] == 0 || $_GET['Ni'] != 3) {
			debug('Pour la tension final du parc de batterie. Automatiquement : si Pc <500W on reste en 12V, entre 500 et 1500 on passe en 24V et au delas des 1500W on passe en 48V', 'span');
			if (convertNumber($Pc) < 500) {
				$U = 12;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est < à 500W', 'span');
			} elseif (convertNumber($Pc) > 1500) {
				$U = 48;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est > à 1500W', 'span');
			} else {	
				$U = 24;
				debug('Ici on est donc en '.$U.'V  car le besoin en panneaux est entre 500 et 1500W', 'span');
			}
		} else {
			$U = $_GET['U'];
			debug('La tension final du parc de batterie est forécé à '.$U.'V', 'span');
		}
		echo '<p>'._('Cap = (Dn x Aut) / (DD x U)').'</p>';
		echo '<ul>';
		echo '	<li>'._('Cap (Ah) : Nominal capacity of batteries (in <a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">C10</a>))').'</li>';
		echo '	<li>'._('Dn (Wh/d) : Daily needs').' = '.$_GET['Bj'].'</li>';
		echo '	<li>'._('Aut: Autonomous days amount (no sun)').' = '.$_GET['Aut'].'</li>';
		echo '	<li>'._('DD (%)').' : <a rel="tooltip" class="bulles" title="'._('With lead batteries, the critical threshold of 50% of discharge mustn\'t be reached, 20% being ideal').'">'._('Maximum discharge rate').'</a> = '.$_GET['DD'].'</li>';
		echo '	<li>'._('U (V)').' : <a rel="tooltip" class="bulles" title="'._('In automatic mode the batteries voltage will be deducted from the panel need <br />From 0 to 500W : 12V<br />From 500 to 1500 W : 24V<br />Above 1500 W : 48V').'">'._('Final battery plant voltage').'</a> = '.$U.'</li>';
		echo '</ul>';
		echo '<p>'._('In your case, result is').' : </p>';
		$Cap = (convertNumber($_GET['Bj'])*convertNumber($_GET['Aut']))/(convertNumber($_GET['DD'])*0.01*convertNumber($U));
		?>
		<p><a class="more" id="resultCalcBatHide"><?= _('Hide the process') ?></a></p>
		<p><?= _('Cap') ?> = (<?= $_GET['Bj'] ?> x <?= $_GET['Aut'] ?>) / (<?= $_GET['DD']*0.01 ?> x <?= $U ?>) = <b><?= convertNumber($Cap, 'print') ?> Ah</b></p>
	</div>
	<?php printf('<p>'._('Batteries are used to store electric energy produced by the panels. You will need a battery plant of <b>%d Ah at %d V</b>.').'</p>', convertNumber($Cap, 'print'), $U); ?>
	<p><a id="resultCalcBatShow"><?= _('See, understand the procedure, the calculation') ?></a></p>	
	
	<?php
	$CourantDechargBesoinPmax=$_GET['Pmax']/$U;
	$CourantDechargeMax = $Cap*$_GET['IbatDecharge']/100;
	// Si le courant de décharge n'est pas respecté par rapport à la taille de la batterie
	if ($CourantDechargBesoinPmax > $CourantDechargeMax) {
		printf('<p>'._('The discharge current of the battery plant must not exceed %d').'%%. '._('In our case it gives').' <a rel="tooltip" class="bulles" title="'.convertNumber($Cap, 'print').'Ah * '.$_GET['IbatDecharge'].'/100">'.number_format($CourantDechargeMax, 1, ',', ' ').'A</a>. ', $_GET['IbatDecharge']);
		printf(_('Yet with a max power need of %dW, the discharge current is').' <a rel="tooltip" class="bulles" title="'.$_GET['Pmax'].'W / '.$U.'V">'.number_format($CourantDechargBesoinPmax, 1, ',', ' ').'A</a>. ', $_GET['Pmax']);
		printf(_('In order to respond to a %dW max power need, you need to increase the battery plant by '), $_GET['Pmax']);
		$Cap=$CourantDechargBesoinPmax*100/$_GET['IbatDecharge'];
		echo '<b>'.convertNumber($Cap, 'print').'Ah</b>.</p>';
	} else {
		debug('On a testé que le courant de déchahrge du parc batterie et on ne dépasse pas les '.$_GET['IbatDecharge'].'%.','p');
	}
	$CourantChargeDesPanneaux=$meilleurParcPv['W']*$meilleurParcPv['nbPv']/$U;
	$CourantChargeMax = $Cap*$_GET['IbatCharge']/100;
	// Si le courant de charge n'est pas respecté par rapport à la taille de la batterie
	if ($CourantChargeDesPanneaux > $CourantChargeMax) {
		printf('<p>'._('The battery plant charging current musn\'t exceed %d').'%%. '._('In our case it gives').' <a rel="tooltip" class="bulles" title="'.convertNumber($Cap, 'print').'Ah * '.$_GET['IbatCharge'].'/100">'.number_format($CourantChargeMax, 1, ',', ' ').'A</a>. ', $_GET['IbatCharge']);
		printf(_('Yet with %dW panels the charge current is').' <a rel="tooltip" class="bulles" title="'.$meilleurParcPv['W']*$meilleurParcPv['nbPv'].'W / '.$U.'V">'.number_format($CourantChargeDesPanneaux, 1, ',', ' ').'A</a>. ', $meilleurParcPv['W']*$meilleurParcPv['nbPv']);
		printf(_('If your charge controller allows it you can limit it or increase your battery plant to').' ');
		$Cap=$CourantChargeDesPanneaux*100/$_GET['IbatCharge'];
		echo '<b>'.convertNumber($Cap, 'print').'Ah</b>. '._('We will state to increase the battery plant').'.</p>';
	} else {
		debug('On a testé que le courant de charge du parc batterie et on ne dépasse pas les '.$_GET['IbatCharge'].'%.','p');
	}
	?>

	<?php 
	/*
	 * ####### Recherche d'une Config batterie : #######
	*/
	debug('<div>');
	$meilleurParcBatterie['nbBatterieParalle'] = 99999;
	$meilleurParcBatterie['diffCap'] = 99999;
	$meilleurParcBatterie['nom'] = 'Impossible à déterminer';
	$meilleurParcBatterie['V'] = 0;
	$meilleurParcBatterie['Ah'] = 0;
	// Choix de la technologie en fonction de la capacitée (pour le mode automatique)
	if ($Cap < 150) {
		$BatType = 'AGM';
	} elseif ($Cap < 500) {
		$BatType = 'GEL';
	} else {
		$BatType = 'OPzS';
	}
	if ($_GET['TypeBat'] == 'auto') {
		debug('En mode automatique, la technologie choisie est AGM si Cap < à 50Ah, GEL si < à 500Ah & OPzS si > à 500Ah, ici on a donc choisi '.$BatType,'p');
	} else {
		debug('Vous avez choisie de privilégier la technologie '.$_GET['TypeBat']);
	}
	debug('Recherche une hypothèse parmis <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=batterie\',\'Les modèles de batteries\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles</a> sur le nombre batterie à mettre en paralèle (noté //). Il faut avoir le minimum de batterie au plus proche des besoins de '.convertNumber($Cap,'print').'Ah : ','span');
	debug('<ul type="1">');
	foreach ($config_ini['batterie'] as $idBat => $batterie) {
		// En mode personnalisé on force et on stop la boucle à la fin 
		if ($_GET['ModBat'] == 'perso') {
			debug('Une batterie personnalisée à été indiqué, nous allons travailler avec celle-ci uniquement.', 'span');
			// plus loin, la même condition avec un break
			$batterie['Ah'] = $_GET['PersoBatAh'];
			$batterie['V'] = $_GET['PersoBatV'];
		// En mode auto on utilise le type de batterie préféré (GEL par défaut)
		} else if ($_GET['ModBat'] == 'auto') {
			if ($_GET['TypeBat'] == 'auto') {
				if ($batterie['type'] != $BatType) {
					continue;
				}
			} elseif ($_GET['TypeBat'] != $batterie['type']) {
				continue;
			}
			
		// Si on est en mode manuel on fait le calcul uniquement sur le bon modèl 
		} else if ($_GET['ModBat'] != $idBat) {
			continue;
		}
		// Calcul du nombre de batterie nessésaire 
		// ENT(capacité recherché / capcité de la batterie)+1
		$nbBatterie=intval($Cap / $batterie['Ah'])+1;
		// Capacité déduite
		$capParcBatterie=$batterie['Ah']*$nbBatterie;
		// Différence avec la capacité souhauté
		$diffCap=$capParcBatterie-$Cap;
		// Debug
		debug('<li>');
		debug('Avec une batterie '.$batterie['nom'].' : il en faudrait '.$nbBatterie.' pour une capacité de '.$capParcBatterie.'Ah. La différence avec le besoin est de '.convertNumber($diffCap,'print').'Ah', 'span');
		if ($nbBatterie <= 2) {
			// + de 2 paralèles n'est pas souhaitable			
			if ($_GET['ModBat'] == 'perso' 
			|| $nbBatterie < $meilleurParcBatterie['nbBatterieParalle']
			|| $nbBatterie == $meilleurParcBatterie['nbBatterieParalle'] && $diffCap <= $meilleurParcBatterie['diffCap']) {
				# Nouvelle meilleur config
				// Debug
				debug('<ul>');
				debug('<b>Meilleur configuration</b> pour le moment car nb < 2 (pas plus de 2 //) & soit nb < hypothèse précédente, soit moins de différence avec le besoin','li');
				debug('</ul>');
				$meilleurParcBatterie['diffCap'] = round($diffCap);
				$meilleurParcBatterie['nom'] = $batterie['nom'];
				$meilleurParcBatterie['V'] = $batterie['V'];
				$meilleurParcBatterie['Ah'] = $batterie['Ah'];
				$meilleurParcBatterie['type'] = $batterie['type'];
				$meilleurParcBatterie['nbBatterieParalle'] = $nbBatterie;
				$meilleurParcBatterie['nbBatterieSerie'] = $U/$meilleurParcBatterie['V'];
				$meilleurParcBatterie['nbBatterieTotal'] = $meilleurParcBatterie['nbBatterieSerie'] * $meilleurParcBatterie['nbBatterieParalle'];
			}
		}
		debug('</li>');
		// En mode personnalisé stop la boucle après avoir forcé 
		if ($_GET['ModBat'] == 'perso') {
			break;
		}
	}
	debug('</ul>');
	debug('Le nombre de batterie à mettre en série est déterminé en fonction de la tension du parc (ici '.$U.'V) et de la tension de la batterie choisie (ici '.$meilleurParcBatterie['V'].'V), il faut donc '.$meilleurParcBatterie['nbBatterieSerie'].' batterie(s) '.$meilleurParcBatterie['nom'].' pour attendre cette tension de '.$U.'V','p');
	debug('</div>');
	if ($meilleurParcBatterie['nbBatterieParalle'] != 99999) {
		if ($_GET['ModBat'] == 'auto') {
			printf('<p>'._('A wiring hypothesis would be to have <b>%d></b> <b>%s</b> type batteries, which increases the plant capacity up to %dAh').'</p>', $meilleurParcBatterie['nbBatterieTotal'], $meilleurParcBatterie['nom'], $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle']);
		} else if ($_GET['ModBat'] == 'perso') {
			printf('<p>'._('You chose to use custom %dAh at %dV battery. Here is a wiring hypothesis with <b>%d</b> of these batteries which raise the plant capacity to %dAh').'.</p>', $meilleurParcBatterie['Ah'], $meilleurParcBatterie['V'], $meilleurParcBatterie['nbBatterieTotal'], $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle']);
		} else {
			printf('<p>'._('You chose to use <b>%s</b> type batteries. Here is a wiring hypothesis with <b>%d</b> of these batteries which raise the plant capacity to %dAh').'.</p>', $meilleurParcBatterie['nom'], $meilleurParcBatterie['nbBatterieTotal'], $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle']);
		}
			echo '<ul><li><b>'.$meilleurParcBatterie['nbBatterieSerie'].' '._('serialized batteries').'</b> (<a rel="tooltip" class="bulles" title="'._('Battery voltage').' ('.$meilleurParcBatterie['V'].'V) * '.$meilleurParcBatterie['nbBatterieSerie'].' '._('serie(s)').'">'._('for a voltage of').' '.$U.'V</a>) ';
			if ($meilleurParcBatterie['nbBatterieParalle'] != 1) {
				echo _('on').' <b>'.$meilleurParcBatterie['nbBatterieParalle'].' '._('parallel(s)').'</b> (<a rel="tooltip" class="bulles" title="'._('Battery capacity').' ('.$meilleurParcBatterie['Ah'].'Ah) * '.$meilleurParcBatterie['nbBatterieParalle'].' '._('parallel(s)').'">'._('for a capacity of').' '.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah</a>)';
			} 
			echo _('(<a rel="tooltip" class="bulles" target="_blank" title="To understand the battery connection click here" href="http://www.solarmad-nrj.com/cablagebatterie.html">?</a>)').'</li></ul>';
	} else {
		echo '<p>'._('Sorry we failed to make a wiring hypothesis for the batteries').'. </p>';
		if ($_GET['ModBat'] != 'auto') {
			echo '<p>'._('We advice you to switch back to automatic mode, a wiring isn\'t wise with this model').'.</p>';
		}
	}
	?>
	
	<!-- 
		Régulateur
	-->
	<h3 id="resultatRegu"><?= _('Charge controller') ?></h3>
	<p><?= _('The charge controller stands between batteries and panels, its role is to handle batteries charge depending on what the panels can provide') ?>. </p>
	<?php 
	/*
	 * ####### Recherche d'une Config régulateur : #######
	*/
	// Courant de charge max avec les batteries
	$batICharge = $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'] * $_GET['IbatCharge'] / 100;
	debug('On considère le conrant de charge max du parc de batterie  '.$meilleurParcBatterie['Ah'].'Ah à '.$_GET['IbatCharge'].'%. Ce qui nous fait '.number_format($batICharge, 2, ',', ' ').'A.','p');
	
	debug('Recherche une hypothèse de câblage des panneauux (au maximum en série pour éviter les pertes/grosses sections de câble) avec tous les <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=regulateur\',\'Les modèles de batteries\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles de régulateurs</a>, en '.$U.'V : ','span');
	// D'abord on test avec 1 régulateur
	// Ensuite on test tout en série
	// Si on trouve pas, on divise en parallèle
	// Si ça marche toujours pas on test avec plusieurs régulateur (10  max)
	for ($nbRegulateur = 1; $nbRegulateur <= 10; $nbRegulateur++) {	
		// On check toutes les possibilités en série puis en divisant en parallèles
		if ($meilleurParcPv['nbPv'] == 1) {
			$nbPvConfigFinal=1;
		} else {
			$nbPvConfigFinal=round($meilleurParcPv['nbPv']/$nbRegulateur);
		}
		$nbPvSerie = $nbPvConfigFinal;
		$nbPvParalele = 1;
		while ($nbPvSerie >= 1) {
			debug('En considérant '.$nbRegulateur.' régulateur, on test avec '.$nbPvSerie.' panneaux en série sur '.$nbPvParalele.' parallèle :', 'p');
			$VdocParcPv=$meilleurParcPv['Vdoc']*$nbPvSerie;
			$IscParcPv=$meilleurParcPv['Isc']*$nbPvParalele;
			$parcPvW = $nbPvSerie*$nbPvParalele * $meilleurParcPv['W'];
			$parcPvV = $VdocParcPv;
			$parcPvI = $IscParcPv*$_GET['reguMargeIcc']/100+$IscParcPv;
			
			$meilleurRegulateur = chercherRegulateur();
			
			// Solutaion trouvé
			if ($meilleurRegulateur['nom']) {
				break;
			}
			
			// Pour la suite 
			if ($nbPvSerie != 1) {
				$nbPvSerie=round($nbPvSerie/2);
				$nbPvParalele =round($nbPvConfigFinal / $nbPvSerie);
			} else {
				$nbPvSerie = 0;
			}
		}
		// Solutaion trouvé
		if ($meilleurRegulateur['nom']) {
			debug('Une hypothèse est émise, on s\'arrête là', 'p');
			break;
		}
	}

	if (!$meilleurRegulateur['nom']) {
		echo '<p>'._('Sorry, unable to establish a panel/charge controller wiring hypothesis').'. ';
		if ($_GET['ModRegu'] != 'auto') {
			echo _('We recommend to switch charge controller and/or panels to automatic mode').'. ';
		}
		echo '</p>';
	} else {
		if ($meilleurParcPv['nbPv'] != $nbPvSerie*$nbPvParalele*$nbRegulateur) {
			printf('<p><i>'._('Warning : for this hypothesis we switched to %d panels').'</i></p>', $nbPvSerie*$nbPvParalele*$nbRegulateur);
		}
		if ($_GET['ModRegu'] == 'perso') {
			echo '<p>'._('With your custom charge controller, a').' ';
		} else if ($_GET['ModRegu'] != 'auto') {
			printf('<p>'._('You force the %s charge controller selection, a').' ', $meilleurRegulateur['nom']);
		} else {
			echo '<p>'._('A').' ';
		}
		if ($nbRegulateur != 1) {
			printf(_('hypothesis would be to have <b>%d %s type charge controller</b>'), $nbRegulateur, $meilleurRegulateur['nom']);
			echo '(<a rel="tooltip" class="bulles" title="'._('With similar characteristics').' : <br />'._('Battery voltage').' : '.$meilleurRegulateur['Vbat'].'V<br />'._('Maximum power panel').' : '.$meilleurRegulateur['PmaxPv'].'W<br />'._('Open circuit voltage panel').' : '.$meilleurRegulateur['VmaxPv'].'V<br />'._('Current short circuit').' : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) ';
			printf(_('and on each connect serialized <b>%d panel(s) '), $nbPvSerie);
			if ($nbPvParalele != 1) {
				printf(_(' on %d parallel(s)'), $nbPvParalele);
			} 
			echo '</b></p>';
		} else {
			printf(_('wiring hypothesis would be to have a <b>%s type charge controller</b>'), $meilleurRegulateur['nom']);
			echo '(<a rel="tooltip" class="bulles" title="'._('With similar characteristics').' : <br />'._('Battery voltage').' : '.$meilleurRegulateur['Vbat'].'V<br />'._('Maximum power panel').' : '.$meilleurRegulateur['PmaxPv'].'W<br />'._('Open circuit voltage panel').' : '.$meilleurRegulateur['VmaxPv'].'V<br />'._('Current short circuit').' : '.$meilleurRegulateur['ImaxPv'].'A">?</a>) ';
			echo _('on which would be connected ');
			if ($nbPvSerie == 1 && $nbPvParalele == 1) {
				echo '<b>'.$nbPvSerie.' '._('panel');
			} else {
				echo '<b>'.$nbPvSerie.' '._('serialized panels');
				if ($nbPvParalele != 1) {
					printf(_(' on %d parallel(s)'), $nbPvParalele);
				} 
			}
			echo '</b></p>';
		}
		
		
		printf('<div id="resultCalcRegu" class="calcul">');
		printf('	<p>'._('A type %s charge controller, with a <b>%dV</b> battery plant, allows : ').'</p>',$meilleurRegulateur['nom'], $meilleurRegulateur['Vbat']);
		printf('	<ul>');
		printf('		<li>'._('<b>%dW</b> max panel power : ').'</li>',$meilleurRegulateur['PmaxPv']);
		printf('			<ul><li>'._('With a total of %d %dW panel(s), we reach <b>%dW</b>').' (<a rel="tooltip" class="bulles" title="'.$meilleurParcPv['W'].'W x '.$nbPvParalele*$nbPvSerie.' '._('panel(s)').' ">?</a>)</li></ul>', $nbPvSerie*$nbPvParalele, $meilleurParcPv['W'],$meilleurParcPv['W']*$nbPvParalele*$nbPvSerie);
		printf('		<li>'._('<b>%dV</b> max PV voltage of open circuit : ').'</li>',$meilleurRegulateur['VmaxPv']);
		printf('			<ul><li>'._('With %d serialized panel(s) of %dV of (Vdoc) voltage, we reach <b>%dV</b>').' (<a rel="tooltip" class="bulles" title="'.$meilleurParcPv['Vdoc'].'V (Vdoc) x '.$nbPvSerie.' '._('serialized panels').'">?</a>)</li></ul>', $nbPvSerie, $meilleurParcPv['Vdoc'], $nbPvSerie*$meilleurParcPv['Vdoc']);
		printf('		<li>'._('<b>%dA</b> max PV short-circuit current :').' </li>', $meilleurRegulateur['ImaxPv']);
		printf('			<ul><li>'._('With %d parallel panel(s) having %dA intensity (Isc) and a %d%% security margin, we reach <b>%dA</b>').' (<a rel="tooltip" class="bulles" title="('.$meilleurParcPv['Isc'].'A (Isc) * '.$_GET['reguMargeIcc'].'/100 + '.$meilleurParcPv['Isc'].'A (Isc)) x '.$nbPvParalele.' '._('parallel panel(s)').'">?</a>)</li></ul>', $nbPvParalele, $meilleurParcPv['Isc'], $_GET['reguMargeIcc'], $nbPvParalele*($meilleurParcPv['Isc']+$meilleurParcPv['Isc']*$_GET['reguMargeIcc']/100));
		echo '	</ul>';
		echo '	<p>'._('Note: serialization multiplies voltage (V) and paralleling multiplies the intensity (I)').'</p>';
		echo '	<p>'._('All these characteristics are available in the product\'s technical sheet. You can customize your charge controller characteristics in <i>Export</i> mode.').'</p>';
		echo '	<p><a class="more" id="resultCalcReguHide">'._('Hide process').'</a></p>';
		echo '	<p> </p>';
		echo '</div>';
		echo '<p><a id="resultCalcReguShow">'._('See, understand the process').'</a></p>';
	}
	?>
	<h3 id="resultatSchema"><?= _('Cable diagram') ?></h3>
	
	<?php 
	if (empty($meilleurRegulateur['nom']) || $meilleurParcBatterie['nbBatterieParalle'] == 99999) {
		echo '<p>'._('Some wiring hypothesis did not succeed, it is not possible to submit a wiring diagram').'.</p>';
	} else {
		$batType=2;
		if ($meilleurParcBatterie['V'] == 12) {
			$batType=1;
		}	
		$SchemaUrl='./lib/ImgSchemaCablage.php?nbPvS='.$nbPvSerie.'&nbPvP='.$nbPvParalele.'&batType='.$batType.'&nbBatS='.$meilleurParcBatterie['nbBatterieSerie'].'&nbBatP='.$meilleurParcBatterie['nbBatterieParalle'].'&nbRegu='.$nbRegulateur;
		$widthImage=20;
		if ($nbPvSerie > 1 || $meilleurParcBatterie['nbBatterieSerie'] > 1) {
			$widthImage=40;
		}
		if ($nbPvSerie > 3 || $meilleurParcBatterie['nbBatterieSerie'] > 3) {
			$widthImage=70;
		}
		if ($nbPvSerie > 5 || $meilleurParcBatterie['nbBatterieSerie'] > 5) {
			$widthImage=100;
		}
		echo '<p>'._('A wiring diagram was established according to panel/charge controller/battery hypothesis :').'</p>';
		echo '<p><a target="_blank" href="'.$SchemaUrl.'"><img width="'.$widthImage.'%"  src="'.$SchemaUrl.'" /></a></p>';
	}
	?>
	
	<?php
	if ($nbPvParalele > 2) {
		echo '<h3  id="resultatJonction">'._('Panel string boxe').'</h3>';
		echo '<p>'._('Beyond 2 string, it is strongly recommended to install a panel string boxe. Panel string boxe is collect DC power from panel strings with blocking diodes on each string for protecting panels from reverse current flow. The collected power is then transferred to charge controller.').'</p>';
	}
	?>
	
	<h3  id="resultatConv"><?= _('Converter') ?></h3>
	<?php
	printf('<p>'._('The converter goal is to transform batteries DC current (here %dV) in AC current usable for standard devices. You need a converter able to deliver the %dW max electric power you need').'.</p>', $U, $_GET['Pmax']);
	debug('On recherche, parmis <a onclick="window.open(\''.$config_ini['formulaire']['UrlModeles'].'&data=convertisseur\',\'Les modèles de Convertisseur\',\'directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no\');">les modèles de convertisseurs</a> un convertisseur supportant une tension d\'entrée de '.$U.' et qui soit capable de délivrer une puissance maximum de '.$_GET['Pmax'].'W  : ','p');
	$meilleurConvertisseur=chercherConvertisseur($U,$_GET['Pmax']);
	if ($meilleurConvertisseur['nom'] == '') {
		echo '<p>'._('Sorry, could\'nt find a converter for such power').'.</p> ';
	} else {
		// Annoncer limite batterie
		$CourantDechargeMaxParcBatterieHypothetique=$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle']*$_GET['IbatDecharge']/100;
		$PuissanceMaxDechargeBatterie=$CourantDechargeMaxParcBatterieHypothetique*$U;
		printf('<p>'._('An hypothesis would be to choose a <b>%s type converter</b> that goes up to %dW max power with possible peaks at %dW.'), $meilleurConvertisseur['nom'], $meilleurConvertisseur['Pmax'], $meilleurConvertisseur['Ppointe']);
		if ($PuissanceMaxDechargeBatterie < $meilleurConvertisseur['Pmax']) {
			printf(_('However, in order to avoid battery damage, you won\'t be able to go above %dW').' <a rel="tooltip" class="bulles" title="('.$meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'].'Ah bat * '.$_GET['IbatDecharge'].'/100) * '.$U.'V">?</a>', $PuissanceMaxDechargeBatterie);
		}
		echo '</p>';
	}
	?>
	
	<h3 id="resultatBatControleur"><?= _('Battery controler') ?></h3>
	<?php 
	echo '<p>'._('It is recommended to have a bettery controleur in order to check battery plant charge state').'.';
	if ($Cap > 100 || $meilleurParcBatterie['Ah']*$meilleurParcBatterie['nbBatterieParalle'] > 100) {  
		// type BMV
		$BudgetBatControleur = 150;
	 } else {  
		// type voltmètre
		$BudgetBatControleur = 15; 
		echo _('However, considering the small size of your system, a voltmeter would be more appropriate. With a <a href="https://www.solariflex.com/smartblog/19/comment-interpreter-voltage-batteries.html">correspondence table</a> you can approximately determine the charge percentage').' .';
	} ?>
	</p>
	<h3 id="resultatCablage"><?= _('The wiring') ?></h3>
	<?php
	$BudgetCable=0; 
	echo '<p>'._('Wire section choice ( <a href="http://solarsud.blogspot.fr/2014/11/calcul-de-la-section-du-cable.html" target="_blank">calculate</a>) is important in order to avoid electricity lossess').' :</p>';
	?>
	<ul>
		<?php
			$PT=($nbPvSerie*$meilleurParcPv['Vdoc'])*$_GET['cablagePtPourcent']/100;
			# formule de calcul avec la distance et la chute de tension
			$cableDistancePvRegu_Calc=round($_GET['cablageRho']*($_GET['distancePvRegu']*2)*($nbPvParalele*$meilleurParcPv['Isc'])/$PT,2);
			# règle des 5A par mm&sup2; 
			$cableDistancePvRegu_AparMm=round(($nbPvParalele*$meilleurParcPv['Isc'])/$_GET['cablageRegleAparMm'],2);
			if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
				$cableDistancePvRegu_Final=$cableDistancePvRegu_AparMm;
			} else {
				$cableDistancePvRegu_Final=$cableDistancePvRegu_Calc;
			}
		printf('<li>'._('Between panels and charge controller, for a distance of %sm, a %dmm&sup2; section cable is recommended'), $_GET['distancePvRegu'], $cableDistancePvRegu_Final);
		printf(' ');
		printf('<a id="resultCalcCablePvReguShow">('._('see, understand the procedure').')</a></li>');
		printf('<div id="resultCalcCablePvRegu" class="calcul">');
		printf('	<p><a class="more" id="resultCalcCablePvReguHide">'._('Hide process').'</a></p>');
		printf('	<p>'._('The formula to calculate the wire section in order to avoid loss is :').'</p>');
		printf('	<p>'._('S = Rho x L x I / VL').'</p>');
		printf('	<ul>');
		printf('		<li>'._('S (mm&sup2;) : Wire section').'</li>');
		printf('		<li>'._('Rho (ohm) : Wire <a href="https://en.wikipedia.org/wiki/Electrical_resistivity_and_conductivity" target="_blank">resistivity</a> (0,017ohm for copper)').'</li>');
		printf('		<li>'._('L (m) : back and forth wire length').'</li>');
		printf('		<li>'._('I (A): Intensity (here panel intensity multiplied by the number of parallel)').'</li>');
		printf('		<li>'._('VL (V) : admitted wire level voltage loss (%s%% of voltage)').'</li>', $_GET['cablagePtPourcent']);
		printf('			<ul><li>'._('(panels voltage * number of series) * %d/100').'</li></ul>', $_GET['cablagePtPourcent']);
		printf('	</ul>');
		printf('	<p>'._('In our case it gives : ').'</p>');
		printf('	<p>S = %s x (%dx2) x %s / %s = <b>%s</b>mm&sup2;</p>', $_GET['cablageRho'], $_GET['distancePvRegu'], $nbPvParalele*$meilleurParcPv['Isc'], $PT, $cableDistancePvRegu_Calc);
		
			if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
				printf('<p>'._('But that section doesn\'t respect the %dA/mm&sup2; rule which prevents heating.'), $_GET['cablageRegleAparMm']);
				printf(_('To respect the rule, we need to get close to a <b>%s</b>mm&sup2; section').' <a rel="tooltip" class="bulles" title="'.$nbPvParalele*$meilleurParcPv['Isc'].'A / '.$_GET['cablageRegleAparMm'].'A/mm&sup2; = '.$cableDistancePvRegu_Final.'mm&sup2;">?</a></p>', $cableDistancePvRegu_Final);
			}
			
		echo '</div>';
		echo '<ul>';
		
		if ($cableDistancePvRegu_Calc < $cableDistancePvRegu_AparMm) {
			$meilleurCable = chercherCable_SecionPlusProche($cableDistancePvRegu_Final); 
		} else {
			$meilleurCable = chercherCable_SecionAudessus($cableDistancePvRegu_Final); 
		}
		if (empty($meilleurCable)) {
			echo '<li>'._('There is no realistic wire section determinable. You should probably decrease distance between devices.').'</li>';
		} else { 
			$BudgetCable=$BudgetCable+$_GET['distancePvRegu']*$meilleurCable['prix'];
			printf('<li>'._('Nearest wire section suggested : <b>%s</b>, approximate cost %d&euro;').'</li>',$meilleurCable['nom'],$_GET['distancePvRegu']*$meilleurCable['prix']);
		 }
		echo '</ul>';
			$PT=$U*$_GET['cablagePtPourcent']/100;
			# formule de calcul avec la distance et la chute de tension
			$cableDistanceReguBat_Calc=round($_GET['cablageRho']*($_GET['distanceReguBat']*2)*($parcPvW/$U)/$PT,2);
			# règle des 5A par mm&sup2; 
			$cableDistanceReguBat_AparMm=round(($parcPvW/$U)/$_GET['cablageRegleAparMm'],2);
			if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
				$cableDistanceReguBat_Final=$cableDistanceReguBat_AparMm;
			} else {
				$cableDistanceReguBat_Final=$cableDistanceReguBat_Calc;
			}
		printf('<li>'._('Between charge controller and batteries, for a %sm distance, a wire section of %smm&sup2; is recommended'), $_GET['distanceReguBat'], $cableDistanceReguBat_Final);
		printf(' ');
		printf('<a id="resultCalcCableReguBatShow">('._('see, understand the procedure').')</a></li>');
		printf('<div id="resultCalcCableReguBat" class="calcul">');
		printf('	<p><a class="more" id="resultCalcCableReguBatHide">'.('Hide process').'</a></p>');
		printf('	<p>'._('The formula to calculate the wire section in order to avoid loss is :').'</p>');
		printf('	<p>'._('S = Rho x L x I / VL').'</p>');
		printf('	<ul>');
		printf('		<li>'._('S (mm&sup2;) : Wire section').'</li>');
		printf('		<li>'._('Rho (ohm) : Wire <a href="https://en.wikipedia.org/wiki/Electrical_resistivity_and_conductivity" target="_blank">resistivity</a> (0,017ohm for copper)').'</li>');
		printf('		<li>'._('L (m) : back and forth wire length').'</li>');
		printf('		<li>'._('I (A): Intensity (here the power of the panels / the voltage of the battery bank)').'</li>');
		printf('		<li>'._('VL (V) : admitted wire level voltage loss (%s%% of voltage)').'</li>', $_GET['cablagePtPourcent']);
		printf('			<ul><li>'._('Batteries voltage, i. e. %dV * %d/100').'</li></ul>', $U, $_GET['cablagePtPourcent']);
		printf('	</ul>');
		printf('	<p>'._('In our case it gives : ').'</p>');
		printf('	<p>S = %s x (%dx2) x (%s / %s) / %s = <b>%s</b>mm&sup2;</p>', $_GET['cablageRho'], $_GET['distanceReguBat'], $parcPvW, $U, $PT, $cableDistanceReguBat_Calc);
			if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
				printf('<p>'._('But that section doesn\'t respect the %dA/mm&sup2; rule which prevents heating.'), $_GET['cablageRegleAparMm']);
				printf(_('To respect the rule, we need to get close to a <b>%s</b>mm&sup2; section').' <a rel="tooltip" class="bulles" title="'.$parcPvW.'W / '.$U.'V = '.$parcPvW/$U.'A<br />'.$parcPvW/$U.'A  / '.$_GET['cablageRegleAparMm'].'A/mm&sup2; = '.$cableDistanceReguBat_Final.'mm&sup2;">?</a></p>', $cableDistanceReguBat_Final);
			}
		echo '</div>';
		echo '<ul>';
		if ($cableDistanceReguBat_Calc < $cableDistanceReguBat_AparMm) {
			$meilleurCable = chercherCable_SecionPlusProche($cableDistanceReguBat_Final); 
		} else {
			$meilleurCable = chercherCable_SecionAudessus($cableDistanceReguBat_Final); 
		}
		if (empty($meilleurCable)) {
			echo '<li>'._('There is no realistic wire section determinable. You should probably decrease distance between devices.').'</li>';
		} else { 
			$BudgetCable=$BudgetCable+$_GET['distanceReguBat']*$meilleurCable['prix'];
			printf('<li>'._('Nearest wire section suggested : <b>%s</b>, approximate cost %d&euro;').'</li>',$meilleurCable['nom'],$_GET['distanceReguBat']*$meilleurCable['prix']);
			} ?>
		</ul>
	</ul>
	<p><?= _('Another and more complete wire section calculator is avalaible at <a href="http://www.sigma-tec.fr/textes/texte_cables.html" target="_blank">sigma-tec</a>.') ?></p>
	<?php
		// Information Off-Grid
		if (empty($_GET['tracking']) && $_GET['periode'] == 'complete') {
			$SHScalc=null;
			foreach ($raddatabases as $RadDatabase) {
				debug('Importation des données Off-Grid PVGIS, on test avec la raddatabase '.$RadDatabase, 'p');	
				$FichierDataCsv = $config_ini['pvgis']['cachePath'].'/pvgis5_SHScalc_'.$_GET['lat'].'_'.$_GET['lon'].'_'.$RadDatabase.'_'.$_GET['inclinaison'].'_'.$_GET['orientation'].'_'.convertNumber($Pc, 'print').'_'.convertNumber($Cap, 'print')*$U.'_'.$config_ini['pvgis']['cutoff'].'_'.$_GET['Bj'].'.csv';
				if (!pgvisGetSHScalc($FichierDataCsv, $RadDatabase)) {
					break;
				} else {
					$SHScalcTest = pgvisParseDataSHScalc($FichierDataCsv);
					$DataStateValueTotal=0;
					foreach($SHScalcTest['DataState'] as $DataStateKey=>$DataStateValue) {
						$DataStateValueTotal=$DataStateValue+$DataStateValueTotal;
					}
					if ($DataStateValueTotal != 0) {
						$SHScalc=$SHScalcTest;
						break;
					} 
				}
			}
			if ($SHScalc != null) {
				
				echo '<h3  id="resultatConv">'._('PVGIS Simulation, performance prediction').'<a target="_blank" href="'.$FichierDataCsv.'" rel="tooltip" class="bulles" title="Télécharger les données brutes" style="float: right"><img src="lib/dl.png" alt="DL" /></a></h3>';
				echo '<p>'._('Data from photovoltaic geographical information system (<a href="http://re.jrc.ec.europa.eu/pvgis.html" target="_blank">PVGIS</a>) application ').'</p>';
				echo '<ul>';
				echo '<li><a id="TitreMyChartSHScalcMonthEnergy">'._('Power production estimate for off-grid PV system').'</a>';
				echo '<div style="display: none" id="ContainerMonthEnergy" class="chart-container pvgisSHScalc myChartSHScalcMonth energy myChartSHScalcMonthEnergy"><canvas id="myChartSHScalcMonthEnergy"></canvas></div>';
				echo '<script>
				var ctx = document.getElementById("myChartSHScalcMonthEnergy").getContext(\'2d\');
				var myChart = new Chart(ctx, {
					type: \'bar\',
					data: {
						labels: [';
				foreach ($mois as $key => $moi) {
					echo '"'.$moi.'"';
					if ($key != 12) {
						echo ', ';
					}
				}
				echo '],
						datasets: [{
							label: \''.addslashes(_('Average energy production per day')).' ('._('Wh/d').')\',
							data: [';
				for ($MoisNum = 1; $MoisNum <= 12; $MoisNum++) {
					echo $SHScalc['DataMonth'][$MoisNum]['Ed'];
					if ($MoisNum != 12) {
						echo ', ';
					}
				}
				echo '],
							backgroundColor: \'rgba(0, 0, 255, 1)\',
							borderColor: \'rgba(37, 37, 235, 0.2)\',
							borderWidth: 1
						},{
							label: \''.addslashes(_('Average energy not captured per day')).' ('._('Wh/d').')\',
							data: [';
				for ($MoisNum = 1; $MoisNum <= 12; $MoisNum++) {
					echo $SHScalc['DataMonth'][$MoisNum]['El'];
					if ($MoisNum != 12) {
						echo ', ';
					}
				}
				echo '],
							backgroundColor: \'rgba(37, 37, 235, 0.2)\',
							borderColor: \'rgba(0, 0, 255, 1)\',
							borderWidth: 1,
						}]
					},
					options: {
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true
								}
							}]
						}
					}
				});
				</script>';
				echo '</li>';
				echo '<li><a id="TitreMyChartSHScalcMonthBattery">'._('Battery performance for off-grid PV system').'</a>';
				echo '<div style="display: none" id="ContainerMonthBattery" class="chart-container pvgisSHScalc myChartSHScalcMonth battery"><canvas id="myChartSHScalcMonthBattery"></canvas></div>';
				echo '<script>
				var ctx = document.getElementById("myChartSHScalcMonthBattery").getContext(\'2d\');
				var myChart = new Chart(ctx, {
					type: \'bar\',
					data: {
						labels: [';
				foreach ($mois as $key => $moi) {
					echo '"'.$moi.'"';
					if ($key != 12) {
						echo ', ';
					}
				}
				echo '],
						datasets: [{
							label: \''.addslashes(_('Percentage of days when battery became full')).' (%)\',
							data: [';
				for ($MoisNum = 1; $MoisNum <= 12; $MoisNum++) {
					echo $SHScalc['DataMonth'][$MoisNum]['Ff'];
					if ($MoisNum != 12) {
						echo ', ';
					}
				}
				echo '],
							backgroundColor: \'rgba(0, 128, 0, 1)\',
						},{
							label: \''.addslashes(_('Percentage of days when battery became empty')).' (%)\',
							data: [';
				for ($MoisNum = 1; $MoisNum <= 12; $MoisNum++) {
					echo $SHScalc['DataMonth'][$MoisNum]['Fe'];
					if ($MoisNum != 12) {
						echo ', ';
					}
				}
				echo '],
							backgroundColor: \'rgba(200, 0, 0, 0.5)\',
						}]
					},
					options: {
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true
								}
							}]
						}
					}
				});
				</script>';
				echo '</li>';
				echo '<li><a id="TitreMyChartSHScalcState">'._('Probability of battery charge state at the end of the day').'</a>';
				echo '<div style="display: none" id="ContainerState" class="chart-container pvgisSHScalc myChartSHScalcState"><canvas id="myChartSHScalcState"></canvas></div>';
				echo '<script>
				var ctx = document.getElementById("myChartSHScalcState").getContext(\'2d\');
				var myChart = new Chart(ctx, {
					type: \'bar\',
					data: {
						labels: [';
				foreach($SHScalc['DataState'] as $DataStateKey=>$DataStateValue) {
					echo '\''.$DataStateKey.'%\'';
					echo ', ';
				}
				echo '],
						datasets: [{
							label: \''.addslashes(_('Percentage of days with this charge state')).' (%)\',
							data: [';
							foreach($SHScalc['DataState'] as $DataStateKey=>$DataStateValue) {
								echo $DataStateValue;
								echo ', ';
							}
				echo '],
							backgroundColor: \'rgba(128, 0, 128, 1)\',
						}]
					},
					options: {
						scales: {
							yAxes: [{
								ticks: {
									beginAtZero: true
								}
							}]
						}
					}
				});
				</script>';
				echo '</ul>';
				echo '<script>
					$( "#TitreMyChartSHScalcMonthEnergy" ).click(function() {
						$( "#ContainerMonthEnergy" ).show( "slow" );
						$( "#ContainerMonthBattery" ).hide( "slow" );
						$( "#ContainerState" ).hide( "slow" );
					});
					$( "#TitreMyChartSHScalcMonthBattery" ).click(function() {
						$( "#ContainerMonthEnergy" ).hide( "slow" );
						$( "#ContainerMonthBattery" ).show( "slow" );
						$( "#ContainerState" ).hide( "slow" );
					});
					$( "#TitreMyChartSHScalcState" ).click(function() {
						$( "#ContainerMonthEnergy" ).hide( "slow" );
						$( "#ContainerMonthBattery" ).hide( "slow" );
						$( "#ContainerState" ).show( "slow" );
					});
				</script>';
				echo '</li>';
			}
		}
	?>

		<h3 id="resultatBudget"><?= _('Budget') ?></h3>
		<p><? _('This is an approximate estimation for new equipment, it can\'t be considered as a quote.') ?>
		<ul>
			<?php
			$BudgetPvBas=$config_ini['prix']['pv_bas']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'];
			$BudgetPvHaut=$config_ini['prix']['pv_haut']*$meilleurParcPv['W']*$meilleurParcPv['nbPv'];
			echo '<li>'._('Photovoltaic panel').' : '._('between').' '.convertNumber($BudgetPvBas, 'print').'&euro; '._('and').' '.convertNumber($BudgetPvHaut, 'print').'&euro; ';
			printf('(<a rel="tooltip" class="bulles" title="'._('Estimated cost %s&euro;/W at low range cost and %s&euro;/W at high range cost').'\">?</a>)</li>', $config_ini['prix']['pv_bas'], $config_ini['prix']['pv_haut']);
			if ($meilleurParcBatterie['nbBatterieParalle'] != 99999) { 
				$BudgetBarBas=$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_bas']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['V']*$meilleurParcBatterie['nbBatterieParalle']*$meilleurParcBatterie['nbBatterieSerie'];
				$BudgetBarHaut=$config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_haut']*$meilleurParcBatterie['Ah']*$meilleurParcBatterie['V']*$meilleurParcBatterie['nbBatterieParalle']*$meilleurParcBatterie['nbBatterieSerie'];
				$BatType=$meilleurParcBatterie['type'];
			} else { 
				$BudgetBarBas=$config_ini['prix']['bat_'.$BatType.'_bas']*$Cap*$U;
				$BudgetBarHaut=$config_ini['prix']['bat_'.$BatType.'_haut']*$Cap*$U;
			} 
			echo '<li>'._('Batterie').' : '._('between').' '.convertNumber($BudgetBarBas, 'print').'&euro; '._('and').' '.convertNumber($BudgetBarHaut, 'print').'&euro; ';
			printf('(<a rel="tooltip" class="bulles" title="'.('Estimated cost %s&euro;/Ah at low range cost and %s&euro;/Ah at high range cost').'\">?</a>)</li>', $config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_bas']*$meilleurParcBatterie['V'], $config_ini['prix']['bat_'.$meilleurParcBatterie['type'].'_haut']*$meilleurParcBatterie['V']);
			if ($BatType == 'Litium') {
				echo '<li>'._('You will need a BMS for your batteries litium, it is not included in the price estimate<').'/li>';
			}
			if (!$meilleurRegulateur['nom']) {
				$budgetRegulateur=0;
				echo '<li>'._('Charge controller').' : '._('sorry, no hypothesis for charge controller').'.</li>';
			} else {
				$budgetRegulateur=$meilleurRegulateur['Prix']*$nbRegulateur;
				echo '<li>'._('Charge controller').' : '._('approximately').' '.convertNumber($budgetRegulateur, 'print') .'&euro;</li>';
			}
			if ($nbPvParalele > 2) {
				$budgetJonctionHaut=$config_ini['prix']['jonction_haut']*$nbPvParalele;
				$budgetJonctionBas=$config_ini['prix']['jonction_bas']*$nbPvParalele;
				printf('<li>'._('Panel string boxe').' : '._('between').' '.convertNumber($budgetJonctionBas, 'print').'&euro;  '._('and').' '.convertNumber($budgetJonctionHaut, 'print').'&euro; ');
				printf(' (<a rel="tooltip" class="bulles" title="'._('Estimated cost %s&euro;/string at low range cost and %s&euro;/string at high range cost').'\">?</a>)</li>', $config_ini['prix']['jonction_bas'], $config_ini['prix']['jonction_haut']);
				printf('</li>');
			} else {
				$budgetJonctionHaut=0;
				$budgetJonctionBas=0;
			}
			if ($meilleurConvertisseur['nom'] == '') {
				$budgetConvertisseurBas=0;
				$budgetConvertisseurHaut=0;
				echo '<li>'._('Converter').' : '.('désolé nous n\'avons pas réussi à faire une hypothèse pour un convertisseur').'.</li> ';
			} else {
				$budgetConvertisseurBas=$config_ini['prix']['conv_bas']*$meilleurConvertisseur['VA'];
				$budgetConvertisseurHaut=$config_ini['prix']['conv_haut']*$meilleurConvertisseur['VA'];
				echo '<li>'._('Converter').' : '._('between').' '.convertNumber($budgetConvertisseurBas, 'print').'&euro; '._('and').' '.convertNumber($budgetConvertisseurHaut, 'print').'&euro;';
				printf(' (<a rel="tooltip" class="bulles" title="'._('Estimated cost %s&euro;/VA at low range cost and %s&euro;/VA at high range cost').'">?</a>)</li>', $config_ini['prix']['conv_bas'], $config_ini['prix']['conv_haut']);
			}
			echo '<li>'._('Battery controler').' : '._('approximately').' '.convertNumber($BudgetBatControleur, 'print') .'&euro;</li>';
			echo '<li>'._('Wiring').' : '._('approximately').' '.convertNumber($BudgetCable, 'print') .'&euro;</li>';
			$budgetTotalBas=$BudgetPvBas+$BudgetBarBas+$budgetRegulateur+$budgetJonctionBas+$budgetConvertisseurBas+$BudgetCable+$BudgetBatControleur;
			$budgetTotalHaut=$BudgetPvHaut+$BudgetBarHaut+$budgetRegulateur+$budgetJonctionHaut+$budgetConvertisseurHaut+$BudgetCable+$BudgetBatControleur;	
		printf('</ul>');
		printf('<p>'._('Which brings to a total budget <b>between %s and %s&euro;</b>. Cost of panels support, wire, wire terminal and protection elements (fuse, battery cut-off, ...) is not included').'.</p>', convertNumber($budgetTotalBas, 'print'), convertNumber($budgetTotalHaut, 'print'));
	
	printf('<h3 id="resultatDon">'._('Support, contribute').'</h3>');
	printf('<p>'._('If this software was helpfull and/or you want to thank').' :  </p>');
	printf('<ul>');
	printf('	<li>'._('<a href="https://david.mercereau.info/soutenir/#respond"  target="_blank">Say thank you !</a> (it\'s always a pleasure)').'</li>');
	printf('	<li><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=MBDD2TG6D4TPC&lc=FR&item_name=CalcPvAutonome&item_number=calcpvautonome&currency_code=EUR&bn=PP%%2dDonationsBF%%3abtn_donate_SM%%2egif%%3aNonHosted"  target="_blank">'._('Support by making a secure donation').'</a></li>');
	printf('	<li><a href="https://framagit.org/kepon/CalcPvAutonome/" target="_blank">'._('Contribute / improve / translate this software').'</a></li>');
	printf('</ul>');
	?>
	<?php
	printf('<h3 id="resultatResume">'._('Summary').' (<a id="hrefResumeBrute">HTML</a>/<a id="hrefResumeBBCode">BBCode</a>)</h3>');
	printf('<div id="resumeBrute" style="display: none"><p>'._('<a href="%s">This simulation</a> estimates that with your consumption of %dWh/d you would need:').'</p>', 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], $_GET['Bj']);
	printf('<ul><li>'._('~%dW panel (%d"%dW for example)').'</li>', convertNumber($Pc, 'print'), $nbPv, $pv['W']);
	printf('<li>'._('~%dAh battery in %dV (%d"%dAh in %dV for example)').'</li>', convertNumber($Cap, 'print'), $U, $meilleurParcBatterie['nbBatterieTotal'], $meilleurParcBatterie['Ah'], $meilleurParcBatterie['V']);
	printf('</ul>');
	printf('<p>'._('The budget estimated between %s and %s&euro;').'</p></div>', convertNumber($budgetTotalBas, 'print'), convertNumber($budgetTotalHaut, 'print'));
	printf('<div id="resumeBBCode" style="display: none"><p>'._('[url=%s]This simulation[url] estimates that with your consumption of %dWh/d you would need:').'<br />', htmlspecialchars('http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']), $_GET['Bj']);
	printf('[list][*]'._('~%dW panel (%d"%dW for example)').'<br />', convertNumber($Pc, 'print'), $nbPv, $pv['W']);
	printf('[*]'._('~%dAh battery in %dV (%d"%dAh in %dV for example)').'[/list]</br />', convertNumber($Cap, 'print'), $U, $meilleurParcBatterie['nbBatterieTotal'], $meilleurParcBatterie['Ah'], $meilleurParcBatterie['V']);
	printf(_('The budget estimated between %s and %s&euro;').'</p></div>', convertNumber($budgetTotalBas, 'print'), convertNumber($budgetTotalHaut, 'print'));
	?>

	<!-- Afficher ou non les informations complémentaire du formulaire -->
	<script type="text/javascript">
		$( "#hrefResumeBBCode" ).click(function() {
			$( "#resumeBBCode" ).show( "slow" );
			$( "#resumeBrute" ).hide( "slow" );
		});
		$( "#hrefResumeBrute" ).click(function() {
			$( "#resumeBBCode" ).hide( "slow" );
			$( "#resumeBrute" ).show( "slow" );
		});
		$( "#aidePvgisShow" ).click(function() {
			$( "#aidePvgis" ).show( "slow" );
			$( "#aidePvgisShow" ).hide( "slow" );
		});
		$( "#aidePvgisHide" ).click(function() {
			$( "#aidePvgis" ).hide( "slow" );
			$( "#aidePvgisShow" ).show( "slow" );
		});
		$( "#resultCalcPvShow" ).click(function() {
			$( "#resultCalcPv" ).show( "slow" );
			$( "#resultCalcPvShow" ).hide( "slow" );
		});
		$( "#resultCalcPvHide" ).click(function() {
			$( "#resultCalcPv" ).hide( "slow" );
			$( "#resultCalcPvShow" ).show( "slow" );
		});
		$( "#resultCalcBatShow" ).click(function() {
			$( "#resultCalcBat" ).show( "slow" );
			$( "#resultCalcBatShow" ).hide( "slow" );
		});
		$( "#resultCalcBatHide" ).click(function() {
			$( "#resultCalcBat" ).hide( "slow" );
			$( "#resultCalcBatShow" ).show( "slow" );
		});
		$( "#resultCalcReguShow" ).click(function() {
			$( "#resultCalcRegu" ).show( "slow" );
			$( "#resultCalcReguShow" ).hide( "slow" );
		});
		$( "#resultCalcReguHide" ).click(function() {
			$( "#resultCalcRegu" ).hide( "slow" );
			$( "#resultCalcReguShow" ).show( "slow" );
		});
		$( "#resultCalcCablePvReguShow" ).click(function() {
			$( "#resultCalcCablePvRegu" ).show( "slow" );
			$( "#resultCalcCablePvReguShow" ).hide( "slow" );
		});
		$( "#resultCalcCablePvReguHide" ).click(function() {
			$( "#resultCalcCablePvRegu" ).hide( "slow" );
			$( "#resultCalcCablePvReguShow" ).show( "slow" );
		});
		$( "#resultCalcCableReguBatShow" ).click(function() {
			$( "#resultCalcCableReguBat" ).show( "slow" );
			$( "#resultCalcCableReguBatShow" ).hide( "slow" );
		});
		$( "#resultCalcCableReguBatHide" ).click(function() {
			$( "#resultCalcCableReguBat" ).hide( "slow" );
			$( "#resultCalcCableReguBatShow" ).show( "slow" );
		});
		$( "#aidePvgisHide" ).click();
		<?php if (empty($_GET['debug'])) { ?>
		$( "#resultCalcPvHide" ).click();
		$( "#resultCalcBatHide" ).click();
		$( "#resultCalcReguHide" ).click();
		$( "#resultCalcCablePvReguHide" ).click();
		$( "#resultCalcCableReguBatHide" ).click();
		<?php } ?>
	</script>
	<?php
	} 
	echo '</div>';
}

/*
 * ####### Formulaire #######
*/
?>
<form method="get" action="#" id="formulaireCalcPvAutonome">
	
	<div class="blocs" id="BlocIntro">
		<div class="form Ni">
			<label><?= _('Your degree of knowledge in photovoltaics') ?> : </label>
			<select id="Ni" name="Ni">
				<option value="1"<?php echo valeurRecupSelect('Ni', 1); ?>><?= _('Beginner') ?></option>
				<option value="2"<?php echo valeurRecupSelect('Ni', 2); ?>><?= _('Enlightened') ?></option>
				<option value="3"<?php echo valeurRecupSelect('Ni', 3); ?>><?= _('Expert') ?></option>
			</select>
		</div>
		
		<div class="conseil debutant">
			<?php if (substr($locale, 0, 2) == 'fr') { ?>
			<p><a href="http://david.mercereau.info/formation-pv/" target="_blank"><img style="float: right; padding: 10ppx" width="100	" src="./lib/FormationPv.png" alt="" /></a><b>Suggestion</b> : regarder la petite <a href="http://david.mercereau.info/formation-pv/" target="_blank">formation vidéo sur l'autonomie électrique photovoltaïque</a> pour un meilleur usage de ce calculateur.</p>
			<?php } ?>
		</div>

		<h2 class="titre vous"><?= _('Your consumption') ?> :</h2>	
				
			<p><?= _('This is the most important step for sizing.') ?> <?php printf(_('If you don\'t know this value go to <b><a href="%s?from=CalcPvAutonome" id="DemandeCalcPvAutonome">daily needs calculator</a></b>'), $config_ini['formulaire']['UrlCalcConsommation']) ?></p>
			
			<div class="form Bj">
				<label><?= _('Your daily electrical needs') ?> :</label>
				<input id="Bj" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Bj'); ?>" name="Bj" />  <?= _('Wh/d') ?>
			</div>
			
			<div class="form Pmax">
				<label><?= _('Your need in maximum electrical power') ?> :</label>
				<input id="Pmax" type="number" min="1" max="99999" style="width: 100px;" value="<?php echo valeurRecup('Pmax'); ?>" name="Pmax" />  W <a rel="tooltip" class="bulles" title="Il s'agit de la somme des puissances des appareils branché au même moment. <br />Par exemple si vous aviez un réfrégirateur de (70W), une scie sauteuse (500W) et une ampoule (7W) qui sont suceptibles d'être allumés en même temps votre besoin en puissance max est de 577W (70+500+7)">?</a>
			</div>
			
			<?php
			function ongletActif($id) {
				if ($_GET['Ej'] != '' && $id == 'valeur') {
					echo ' class="actif"';
				} elseif ($_GET['Ej'] == '' && $id == 'auto') {
					echo ' class="actif"';
				}
			}
			?>
	</div>
	
	<div id="BlocLocalisation" class="blocs part localisation">
		<h2 class="titre localisation"><?= _('Geographical location') ?>	</h2>

		<ul id="onglets">
			<li<?php echo ongletActif('auto'); ?>><?= _('Map') ?></li>
			<li<?php echo ongletActif('valeur'); ?> id="EjOnglet"><?= _('Manual') ?></li>
		</ul>
		<div id="contenu">
			
			<div class="modePvgis item">				
				<p><?= _('Click on the map to set your position and deduce solar resource') ?> : </p>

				<div id="mapid" style="width: 100%; height: 300px;"></div>
				<script>
					// Création de la carte
					<?php 
					// Exception pour la france on ce met sur la France (le public principal)
					$mapZoom=$config_ini['formulaire']['zoomDefaut'];
					if ($country == 'FR') {
						$config_ini['formulaire']['lat'] = 47;
						$config_ini['formulaire']['lon'] = 3;
						$mapZoom=5;
					}
					if (isset($_GET['lat']) || isset($_COOKIE['lat'])) {
						$mapZoom=$config_ini['formulaire']['zoom'];
					}
					
					?>
					var mymap = L.map('mapid').setView([<?php valeurRecupCookie('lat') ?>, <?php valeurRecupCookie('lon') ?>], <?= $mapZoom ?>);

					L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
						maxZoom: 18,
						attribution: '<a href="http://openstreetmap.org">OpenStreetMap</a>',
						id: 'mapbox.streets'
					}).addTo(mymap);

					var popup = L.popup();

					<?php 
					if (isset($_GET['lat'])) {
						echo "var Marker = L.marker([".$_GET['lat'].", ".$_GET['lon']."]);\n";
						echo "Marker.addTo(mymap);\n";
					} else if (isset($_COOKIE['lat'])) {
						echo "var Marker = L.marker([".$_COOKIE['lat'].", ".$_COOKIE['lon']."]);\n";
						echo "Marker.addTo(mymap);\n";
					}
					?>
					
					function onMapClick(e) {
						popup
							.setLatLng(e.latlng)
							.setContent("<?= _('Selected position') ?> : " + e.latlng.toString())
							.openOn(mymap);
					
						var split1 = e.latlng.toString().split("(")
						var split2 = split1[1].split(")")
						var split3 = split2[0].split(", ")
						var lat = split3[0];
						var lon = split3[1];
						$( "#lat" ).val(lat);	
						$( "#lon" ).val(lon);
						sumbitEnable();	 
						<?php 
						if (isset($_GET['lat']) || isset($_COOKIE['lat'])) {
							echo "Marker.remove(mymap);\n";
						}
						?>
					}
					
					mymap.on('click', onMapClick);
				</script>
				<div style="text-align: center">
					<?= _('Latitude') ?> : 
					<input type="number" min="-180" max="180" step="0.00001" style="width: 100px;"  name="lat" id="lat" value="<?= valeurRecupCookieSansConfig('lat'); ?>" />
					<?= _('Longitude') ?> : 
					<input type="number" min="-180" max="180" step="0.00001" style="width: 100px;" name="lon" id="lon" value="<?= valeurRecupCookieSansConfig('lon'); ?>" />
				</div>				
				<p><small><?= _('Solar irradiation data is collected on <a href="http://re.jrc.ec.europa.eu/PVGIS5-release.html" target="_blank"> PVGIS </a>') ?>.</small></p>
			</div>
			<div class="modeInput item">
				<div class="form Ej">
					<label><?= _('Enter the mid value of solar radiation for the worst month and panel plane (inclination)') ?> :</label>
					<input maxlength="4" size="4" id="Ej" type="number" step="0.01" min="0" max="50" style="width: 100px;" value="<?php echo valeurRecup('Ej'); ?>" name="Ej" /> kWh/m&sup2;/j
					<?php
					if ($locale == 'fr') {	
						echo '<p>Pour obtenir cette valeur rendez vous sur le site de <a href="http://ines.solaire.free.fr/gisesol_1.php" target="_blank">INES</a>, choisir votre ville, l\'inclinaison & l\'orientation des panneaux puis valider. Il s\'agit ensuite de prendre la plus basse valeur de la ligne "Globale (IGP)" (dernière ligne du second tableau) Plus d\'informations en bas de cette page : <a href="http://www.photovoltaique.guidenr.fr/cours-photovoltaique-autonome/VI_calcul-puissance-crete.php">Comment obtenir la valeur de Ei, Min sur le site de l\'INES ?</a></p>';
					}
					?>
				</div>
			</div>
			
		</div>
		
	</div>
	
	
	<div id="BlocPV" class="blocs part pv">
		<h2 class="titre pv"><?= _('Sizing of photovoltaic panels') ?></h2>
		
		<?php
		if ($country == 'FR') {
			// Pour la France on conseil 65° d'inclinaison par défaut
			$config_ini['formulaire']['inclinaison'] = 65;
			$config_ini['formulaire']['orientation'] = 0;
		}
		?>
		
		<div class="form inclinaison">
			<label><?= _('Incline of the panel') ?> :</label>
			<input name="inclinaison" id="inclinaison" type="number" min="-90" max="90" style="width: 100px;" value="<?= valeurRecup('inclinaison'); ?>" />
			<?= $aideInclinaison ?>

		</div>
		<div class="form orientation">
			<label><?= _('Orientation of the panels') ?> :</label>
			<input name="orientation" id="orientation" type="number" min="-180" max="180" style="width: 100px;" value="<?= valeurRecup('orientation'); ?>" />
			<?= $aideOrientation ?>
		</div>
		
		<p><input type="checkbox" id="tracking" name="tracking" <?php if (isset($_GET['tracking'])) echo 'checked="tracking"'; ?> /> <?= _('I use a solar tracker on the 2 axes') ?></p>
		
		<div class="form periode">
			<label><?= _('Desired autonomous') ?> :</label>
			<select name="periode" id="periode">
				<option value="complete"<?php echo valeurRecupSelect('periode', 'complete'); ?>><?= _('Annual / complete') ?></option>
				<option value="partielle"<?php echo valeurRecupSelect('periode', 'partielle'); ?>><?= _('Seasonal / partial') ?></option>
			</select>
			<div class="form periodeDebutFin">
				<label><?= _('Select period') ?> :</label>
				<select name="periodeDebut">
					<?php
					foreach ($mois as $moisId=>$moisNom) { 
						$moisNom=utf8_encode($moisNom);
						echo '<option value="'.$moisId.'"';
						valeurRecupSelect('periodeDebut', $moisId);
						echo '>'.$moisNom.'</option>';
					}
					?>
				</select>
				<select name="periodeFin">
					<?php
					foreach ($mois as $moisId=>$moisNom) { 
						$moisNom=utf8_encode($moisNom);
						echo '<option value="'.$moisId.'"';
						valeurRecupSelect('periodeFin', $moisId);
						echo '>'.$moisNom.'</option>';
					}
					?>
				</select>
			</div>
		</div>

		<div class="form ModPv">
			<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=pv','<?= _('Panel template') ?>','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no');"><?= _('Panel template') ?></a> : </label>
			<select id="ModPv" name="ModPv">
				<option value="auto"><?= _('Automatic') ?></option>
				<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModPv', 'perso'); ?>><?= _('Customize') ?></option>
				<?php 
				foreach ($config_ini['pv'] as $pvModele => $pvValeur) {
					echo '<option value="'.$pvModele.'"';
					echo valeurRecupSelect('ModPv', $pvModele);
					echo '>'.ucfirst($pvValeur['type']).' '.$pvValeur['W'].'W</option>';
					echo "\n";
				}
				?>
			</select> 
		</div>
		<div class="form TypePv">
			<label><?= _('Preferred panel technology') ?> : </label>
			<select id="TypePv" name="TypePv">
				<option value="monocristalin"<?php echo valeurRecupSelect('TypePv', 'monocristalin'); ?>>Monocristalin</option>
				<option value="polycristallin"<?php echo valeurRecupSelect('TypePv', 'polycristallin'); ?>>Polycristallin</option>
			</select> 
		</div>
		
		<div class="form PersoPv">
			<p><?= _('You can detail the technical specifications of your panel') ?> : </p>
			<ul>
				<li>
					<label><?= _('Max power (Pmax)') ?>  : </label>
					<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoPvW'); ?>"  name="PersoPvW" />W
				</li>
				<li>
					<label><?= _('Open circuit voltage (Voc)') ?> </label>
					<input type="number" step="0.01" min="1" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvVdoc'); ?>"  name="PersoPvVdoc" />V
				</li>
				<li>
					<label><?= _('Short-circuit current (Isc)') ?></label>
					<input type="number" step="0.01" min="0,01" max="99" style="width: 70px;" value="<?php echo valeurRecup('PersoPvIsc'); ?>"  name="PersoPvIsc" />A
				</li>
			</ul>
		</div>
			
		<div class="form Rb">
			<label><?= _('Electrical yield of batteries') ?> : </label>
			<input maxlength="4" size="4" id="Rb" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Rb'); ?>" name="Rb" />
		</div>
		<div class="form Ri">
			<label><?= _('Electrical yield for the remaining installation (charge controller,...)') ?> : </label>
			<input maxlength="4" size="4" id="Ri" type="number" step="0.01" min="0" max="1" style="width: 70px;" value="<?php echo valeurRecup('Ri'); ?>" name="Ri" />
		</div>
	</div>
	
	<div class="blocs" id="BlocBat">
		
		<div class="part bat">
			<h2 class="titre bat"><?= _('Battery plant sizing') ?></h2>
			<p><?= _('This software is preset for lead batteries') ?> (AGM/Gel/OPvS/OPzV)</p>
			<div class="form Aut">
				<label><?= _('Autonomous days amount') ?> : </label>
				<input maxlength="2" size="2" id="Aut" type="number" step="0.1" min="0" max="50" style="width: 50px" value="<?php echo valeurRecup('Aut'); ?>" name="Aut" />
			</div>
			<div class="form U">
				<label><?= _('Final battery plant voltage') ?> : </label>
				<select id="U" name="U">
					<option value="0"<?php echo valeurRecupSelect('U', 0); ?>>Auto</option>
					<option value="12"<?php echo valeurRecupSelect('U', 12); ?>>12</option>
					<option value="24"<?php echo valeurRecupSelect('U', 24); ?>>24</option>
					<option value="48"<?php echo valeurRecupSelect('U', 48); ?>>48</option>
				</select> V <a rel="tooltip" class="bulles" title="<?= _('In automatic mode the batteries voltage will be deducted from the panel need <br />From 0 to 500W : 12V<br />From 500 to 1500 W : 24V<br />Above 1500 W : 48V') ?>">(?)</a>
			</div>
			<div class="form DD">
				<label><?= _('Discharge level limit') ?> : </label>
				<input maxlength="2" size="2" id="DD" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('DD'); ?>" name="DD" /> %
			</div>
			<div class="form ModBat">
				<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=batterie','<?= _('Battery model') ?>','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=600,fullscreen=no');"><?= _('Battery model') ?></a> <?= _('(<a href="http://www.batterie-solaire.com/batterie-delestage-electrique.htm" target="_blank">expressed in C10</a>)') ?> : </label>
				<select id="ModBat" name="ModBat">
					<option value="auto"><?= _('Automatic') ?></option>
					<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModBat', 'perso'); ?>><?= _('Customize') ?></option>
					<?php 
					foreach ($config_ini['batterie'] as $batModele => $batValeur) {
						echo '<option value="'.$batModele.'"';
						echo valeurRecupSelect('ModBat', $batModele);
						echo '>'.$batValeur['nom'].'</option>';
						echo "\n";
					}
					?>
				</select> <a rel="tooltip" class="bulles" title="<?= _('In automatic mode, GEL & OPzS batteries will be used above 500A') ?>">(?)</a>
			</div>
			<div class="form TypeBat">
				<label><?= _('Preferred battery technology') ?> : </label>
				<select id="TypeBat" name="TypeBat">
					<option value="auto"<?php echo valeurRecupSelect('TypeBat', 'auto'); ?>>Auto.</option>
					<option value="AGM"<?php echo valeurRecupSelect('TypeBat', 'AGM'); ?>>AGM</option>
					<option value="GEL"<?php echo valeurRecupSelect('TypeBat', 'GEL'); ?>>Gel</option>
					<option value="OPzV"<?php echo valeurRecupSelect('TypeBat', 'OPzV'); ?>>OPzV</option>
					<option value="OPzS"<?php echo valeurRecupSelect('TypeBat', 'OPzS'); ?>>OPzS</option>
					<option value="Litium"<?php echo valeurRecupSelect('TypeBat', 'Litium'); ?>>Litium</option>
				</select> 
			</div>
			<script type="text/javascript">
				$( "#TypeBat" ).change(function () {
					if ($( "#TypeBat" ).val() == 'Litium') {
						$( "#DD" ).val(<?= $config_ini['formulaire']['DD_Litium'] ?>);
						$( "#IbatCharge" ).val(<?= $config_ini['formulaire']['IbatCharge_Litium'] ?>);
						$( "#IbatDecharge" ).val(<?= $config_ini['formulaire']['IbatDecharge_Litium'] ?>);
					} else {
						$( "#DD" ).val(<?= $config_ini['formulaire']['DD'] ?>);
						$( "#IbatCharge" ).val(<?= $config_ini['formulaire']['IbatCharge'] ?>);
						$( "#IbatDecharge" ).val(<?= $config_ini['formulaire']['IbatDecharge'] ?>);
					}
				});
				$( "#ModBat" ).change(function () {
					var MotBatSplit = $( "#ModBat" ).val().split("_");
					if (MotBatSplit[0] == 'LITIUM') {
						$( "#DD" ).val(<?= $config_ini['formulaire']['DD_Litium'] ?>);
						$( "#IbatCharge" ).val(<?= $config_ini['formulaire']['IbatCharge_Litium'] ?>);
						$( "#IbatDecharge" ).val(<?= $config_ini['formulaire']['IbatDecharge_Litium'] ?>);
					} else {
						$( "#DD" ).val(<?= $config_ini['formulaire']['DD'] ?>);
						$( "#IbatCharge" ).val(<?= $config_ini['formulaire']['IbatCharge'] ?>);
						$( "#IbatDecharge" ).val(<?= $config_ini['formulaire']['IbatDecharge'] ?>);
					}
				});
			</script>
			<div class="form PersoBat">
				<p><?= _('You can detail the technical characteristics of your battery') ?> : </p>
				<ul>
					<li>
						<label><?= _('Capacity (C10)') ?> : </label>
						<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoBatAh'); ?>"  name="PersoBatAh" />Ah
					</li>
					<li>
						<label><?= _('Voltage') ?> : </label>
						<select id="PersoBatV" name="PersoBatV">
							<option value="2"<?php echo valeurRecupSelect('PersoBatV', 2); ?>>2</option>
							<option value="4"<?php echo valeurRecupSelect('PersoBatV', 4); ?>>4</option>
							<option value="6"<?php echo valeurRecupSelect('PersoBatV', 6); ?>>6</option>
							<option value="12"<?php echo valeurRecupSelect('PersoBatV', 12); ?>>12</option>
						</select> V
					</li>
				</ul>
			</div>
			<div class="form IbatCharge">
				<label><?= _('Charge current max capacity') ?> : </label>
				<input maxlength="2" size="2" id="IbatCharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatCharge'); ?>" name="IbatCharge" /> %
			</div>
			<div class="form IbatDecharge">
				<label><?= _('Discharge current max capacity') ?> : </label>
				<input  maxlength="2" size="2" id="IbatDecharge" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('IbatDecharge'); ?>" name="IbatDecharge" /> %
			</div>
		</div>
		
		<div class="part regu">
			<h2 class="titre regu"><?= _('Charge controller') ?></h2>
			<div class="form ModRegu">
				<label><a onclick="window.open('<?= $config_ini['formulaire']['UrlModeles'] ?>&data=regulateur','<?= _('Charge controller model') ?>','directories=no,menubar=no,status=no,location=no,resizable=yes,scrollbars=yes,height=500,width=670,fullscreen=no');"><?= _('Charge controller model') ?></a> : </label>
				<select id="ModRegu" name="ModRegu">
					<option value="auto"><?= _('Automatic') ?></option>
					<option value="perso" style="font-weight: bold"<?php echo valeurRecupSelect('ModRegu', 'perso'); ?>><?= _('Customize') ?></option>
					<?php 
					$ReguModeleDoublonCheck[]=null;
					foreach ($config_ini['regulateur'] as $ReguModele => $ReguValeur) {
						if (!in_array(substr($ReguModele, 0, -3), $ReguModeleDoublonCheck)) {	
							echo '<option value="'.substr($ReguModele, 0, -3).'"';
							echo valeurRecupSelect('ModRegu', substr($ReguModele, 0, -3));
							echo '>'.$ReguValeur['nom'].'</option>';
							echo "\n";
							$ReguModeleDoublonCheck[]=substr($ReguModele, 0, -3);
						}
					}
					?>
				</select>
			</div>
			<div class="form PersoRegu">
				<p><?= _('You can detail the technical characteristics of your charge controller') ?> : </p>
				<ul>
					<li>
						<label><?= _('Batteries final voltage') ?> : <a rel="tooltip" class="bulles" title="<?= _('This value can be changed in \'battery plant sizing\'') ?>"><span id="PersoReguVbat"></span>V</a></label>
					</li>
					<li>
						<label><?= _('Maximum power panel') ?> : </label>
						<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguPmaxPv'); ?>"  name="PersoReguPmaxPv" />W
					</li>
					<li>
						<label><?= _('Open circuit max PV voltage') ?> : </label>
						<input type="number" min="1" max="9999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguVmaxPv'); ?>" name="PersoReguVmaxPv" />V
					</li>
					<li>
						<label><?= _('PV current max. (power / voltage)') ?> :</label>
						<input type="number" step="0.01" min="0,01" max="999" style="width: 70px;" value="<?php echo valeurRecup('PersoReguImaxPv'); ?>"  name="PersoReguImaxPv" />A
					</li>
				</ul>
			</div>
			<div class="form reguMargeIcc">
				<label><?= _('Panels Icc short-circuit current security margin') ?> : </label>
				<input maxlength="2" size="2" id="reguMargeIcc" type="number" step="1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('reguMargeIcc'); ?>" name="reguMargeIcc" /> %
			</div>
		</div>
		
		<div class="part cable">
			<h2 class="titre cable"><?= _('Wiring') ?></h2>
			<p><?= _('Considering solar flexible copper wiring') ?>.</p>
			<div class="form cablePvRegu">
				<label><?= _('One-way distance between panels and charge controller') ?> : </label>
				<input maxlength="2" size="2" id="distancePvRegu" type="number" step="0.5" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('distancePvRegu'); ?>" name="distancePvRegu" /> m
			</div>
			<div class="form cableReguBat">
				<label><?= _('One-way distance between charge controller and batteries') ?> : </label>
				<input maxlength="2" size="2" id="distanceReguBat" type="number" step="0.5" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('distanceReguBat'); ?>" name="distanceReguBat" /> m
			</div>
			<div class="form cablageRho">
				<label><?= _('Conductor resistivity (rho) mm&sup2;/m') ?>  : </label>
				<input maxlength="4" size="4" id="cablageRho" type="number" step="0.001" min="0" max="10" style="width: 70px" value="<?php echo valeurRecup('cablageRho'); ?>" name="cablageRho" /> ohm
			</div>
			<div class="form cablagePtPourcent">
				<label><?= _('Tolerable voltage drop') ?> : </label>
				<input maxlength="2" size="2" id="cablagePtPourcent" type="number" step="0.1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('cablagePtPourcent'); ?>" name="cablagePtPourcent" /> %
			</div>
			<div class="form cablageRegleAparMm">
				<label><?= _('Ratio in order to prevent wire heating') ?> : </label>
				<input maxlength="2" size="2" id="cablageRegleAparMm" type="number" step="0.1" min="0" max="100" style="width: 70px" value="<?php echo valeurRecup('cablageRegleAparMm'); ?>" name="cablageRegleAparMm" /> A/mm&sup2;
			</div>
		</div>
	</div>
	<div id="BlocSubmit"  class="form End">
		<input id="Reset" type="button" value="<?= _('Reset') ?>" name="reset" />
		<input id="donate" type="button" value="<?= _('Support, contribute') ?>" />
		<input id="Submit" type="submit" value="<?= _('Start the calculation') ?>" name="submit" />
		<a rel="tooltip" class="bulles"  title="<?= _('At a minimum you must : <br />* Indicate your daily electrical needs<br />* Indicate your need in maximum electrical power  <br />* Your position (click on the map)') ?>" id="SubmitBulles"><?= _('Why it\'s not possible to click on') ?> <?= _('Start the calculation') ?> ?</a>
	</div>
	<?php if (substr($locale, 0, 2) == 'fr') { ?>
	<div class="form ModeDebug"><input type="checkbox" name="debug" <?php if (isset($_GET['debug'])) echo 'checked="checked"'; ?> />Activer le mode transparent/debug pour mieux comprendre le fonctionnement</div>
	<?php } ?>
</form>

<?php socialShare((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]") ?>

<!-- Donate campagne -->
<div id="myModal" class="modal">
	<!-- Modal content -->
	<div class="modal-content">
	<span class="closeDonate close">&times;</span>
	<img id="DonateImg" src="./lib/reconnaissance.jpg" />
		<h1><?= _('Support CaclPvAutonome') ?></h1>
		<p style="font-size: 140%"><?= _('This software is <b>open</b>, <b>free</b>, collaborative and <b>independent</b> financially. <b>So that he can stay, we need your support</b>.') ?></p>
		<p><?= _('To help us, you can also <a href="https://framagit.org/kepon/CalcPvAutonome" target="_blank">contribute to its improvement</a>, its <a href="https://crwd.in/calcpvautonome" target="_blank">translation</a>.') ?></p>
		<p onclick="location.href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=MBDD2TG6D4TPC&lc=FR&item_name=CalcPvAutonome&item_number=calcpvautonome&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted';" class="DonateBouton" style="background-color: #4B82B8; color: #FFFFFF"><?= _('I want to make a secure one-time donation with Paypal') ?></p>
		<p onclick="location.href='https://fr.liberapay.com/DavidMercereau/donate';" class="DonateBouton" style="background-color: #E6D815; color: #FFFFFF"><?= _('I want to make a recurring, free and secure donation with Liberapay') ?></p>
		<p class="PasDonateButon closeDonate"><?= _('No thanks, I just want to <b>start the calculation</b>') ?></p>
		<p class="PasDonateButon closeDonate" ><?= _('I already support CalcPvAutonomous') ?></p>
	</div>
</div>
<script type="text/javascript">
	/* Donate Campagne */
	function setCookie(cname, cvalue, exdays) {
		var d = new Date();
		d.setTime(d.getTime() + (exdays*24*60*60*1000));
		var expires = "expires="+ d.toUTCString();
		document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
	}
	function getCookie(cname) {
		var name = cname + "=";
		var decodedCookie = decodeURIComponent(document.cookie);
		var ca = decodedCookie.split(';');
		for(var i = 0; i <ca.length; i++) {
			var c = ca[i];
			while (c.charAt(0) == ' ') {
				c = c.substring(1);
			}
			if (c.indexOf(name) == 0) {
				return c.substring(name.length, c.length);
			}
		}
		return "";
	}
	$( "#Submit" ).click(function( event ) {
		if (getCookie("donate") == "") {
			event.preventDefault();
			donatePopupOn();
			$(".PasDonateButon").show();
		} 
	});
	$( "#donate" ).click(function() {
		$('.PasDonateButon').hide();
		donatePopupOn();		
	});
	$( ".closeDonate" ).click(function() {
		donatePopupOff();		
	});
	function donatePopupOn() {
		$( "#myModal" ).show();
	}
	function donatePopupOff() {
		$( "#myModal" ).hide();
		setCookie('donate', 'off', 5)
	}
	/*window.onclick = function(event) {
		if (event.target == modal) {
			modal.style.display = "none";
		}
	}*/
</script>		 

<!-- Détection des changement dans le formulaire -->
<input type="hidden" value="0" id="ModificationDuFormulaire" />

<script type="text/javascript">
// Détection des changement dans le formulaire
$( "input" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$( "select" ).change(function () {
	if ($( "#ModificationDuFormulaire" ).val() == 0) {
		$( "#ModificationDuFormulaire" ).val(1);		
	}
});
$('#DemandeCalcPvAutonome').click(function() {
	if ($( "#ModificationDuFormulaire" ).val() == 1) {
		return confirm("Vous avez commencé à remplir ce formulaire, vous allez perdre ces informations en continuant.");
	}
});

$( "#periode" ).change(function () {
	periodeChange();
});
$( "#ModPv" ).change(function () {
	modPvChange();
});
$( "#ModBat" ).change(function () {
	modBatChange();
});
$( "#ModRegu" ).change(function () {
	modReguChange();
});
$( "#tracking" ).change(function () {
	trackingChange();
});
$( "#U" ).change(function () {
	$( "#PersoReguVbat" ).text($( "#U" ).val());
});

// Bouton Submit activation / désactivation
function sumbitEnable() {
	if (($( "#lat" ).val() != '' && $( "#lon" ).val() != '' && $( "#Bj" ).val() > 0 && $( "#Pmax" ).val() > 0)
	|| ($( "#Ej" ).val() != '' 	&& $( "#Bj" ).val() > 0 && $( "#Pmax" ).val() > 0)) {
		$( "#Submit" ).prop('disabled', false);
		$( "#SubmitBulles" ).hide();
	} else {
		$( "#Submit" ).prop('disabled', true);
		$( "#SubmitBulles" ).show();
	}
}
$( "#Bj" ).change(function() {
	sumbitEnable();
	<?php if ($country == 'FR') {  ?>
	if ($( "#Bj" ).val() <= 1500) {
		$("#Bj").css("color", "");
	} else if ($( "#Bj" ).val() > 6000) {
		$("#Bj").css("color", "#5F0000");
	} else if ($( "#Bj" ).val() > 3000) {
		$("#Bj").css("color", "#8E4100");
	} else if ($( "#Bj" ).val() > 1500) {
		$("#Bj").css("color", "#956204");
	} 
	<?php } ?>
});
$( "#Pmax" ).change(function() {
	sumbitEnable();
	<?php if ($country == 'FR') {  ?>
	if ($( "#Pmax" ).val() <= 700) {
		$("#Pmax").css("color", "");
	} else if ($( "#Pmax" ).val() > 5000) {
		$("#Pmax").css("color", "#5F0000");
	} else if ($( "#Pmax" ).val() > 1800) {
		$("#Pmax").css("color", "#8E4100");
	} else if ($( "#Pmax" ).val() > 700) {
		$("#Pmax").css("color", "#956204");
	} 
	<?php } ?>
});
$( "#lat" ).change(function() {
	sumbitEnable();
});
$( "#lon" ).change(function() {
	sumbitEnable();
});
$( "#Ej" ).change(function() {
	sumbitEnable();
});

// Période
function periodeChange() {
	if ($( "#periode" ).val() == 'partielle') {
		$( ".periodeDebutFin" ).show();
	} else {
		$( ".periodeDebutFin" ).hide();
	}
}
// Changement de modèle de PV
function modPvChange() {
	if ($( "#ModPv" ).val() == 'auto') {
		$( ".form.TypePv" ).show();
		$( ".form.PersoPv" ).hide();
	} else if ($( "#ModPv" ).val() == 'perso') {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).show();
	} else {
		$( ".form.TypePv" ).hide();
		$( ".form.PersoPv" ).hide();
	}
}
// Changement de modèle de batterie
function modBatChange() {
	if ($( "#ModBat" ).val() == 'auto') {
		$( ".form.TypeBat" ).show();
		$( ".form.PersoBat" ).hide();
	} else if ($( "#ModBat" ).val() == 'perso') {
		$( ".form.TypeBat" ).hide();
		$( ".form.PersoBat" ).show();
	} else {
		$( ".form.TypeBat" ).hide();
		$( ".form.PersoBat" ).hide();
	}
}
// Changement modèle régulateur
function modReguChange() {
	if ($( "#ModRegu" ).val() == 'auto') {
		$( ".form.TypeRegu" ).show();
		$( ".form.PersoRegu" ).hide();
		if ($("#U option").length == 3) {
			$("#U").append('<option value="0">Auto</option>');
		}
	} else if ($( "#ModRegu" ).val() == 'perso') {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).show();
		$("#U option[value='0']").remove();
		$( "#PersoReguVbat" ).text($( "#U" ).val());
	} else {
		$( ".form.TypeRegu" ).hide();
		$( ".form.PersoRegu" ).hide();
		if ($("#U option").length == 3) {
			$("#U").append('<option value="0">Auto</option>');
		}
	}
	
}
function trackingChange() {
	if ($('#tracking').is(':checked')) {
		$( ".form.orientation" ).hide();
		$( ".form.inclinaison" ).hide();
	} else {
		$( ".form.orientation" ).show();
		$( ".form.inclinaison" ).show();
	}
}
// Changement de niveau
$( "#Ni" ).change(function () {
	changeNiveau();
});
function changeNiveau() {
	// Debutant (1)
	if ($( "#Ni" ).val() == 1) {
		$( ".conseil.debutant" ).show();
		$( "#EjOnglet" ).hide();
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).hide();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).hide();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).hide();
		$( ".part.cable" ).hide();
		$( ".form.ModeDebug" ).hide();
	// Eclaire (2)
	} else if  ($( "#Ni" ).val() == 2) {
		$( ".conseil.debutant" ).show();
		$( "#EjOnglet" ).show();
		$( ".form.Ri" ).hide();
		$( ".form.Rb" ).hide();
		$( ".form.AUT" ).show();
		$( ".form.U" ).hide();
		$( ".form.DD" ).hide();
		$( ".part.bat" ).show();
		$( ".part.regu" ).hide();
		$( ".form.ModBat" ).hide();
		$( ".form.IbatCharge" ).hide();
		$( ".form.IbatDecharge" ).hide();
		$( ".form.ModPv" ).hide();
		$( ".form.TypePv" ).show();
		$( ".part.cable" ).show();
		$( ".form.cablageRho" ).hide();
		$( ".form.cablagePtPourcent" ).hide();
		$( ".form.cablageRegleAparMm" ).hide();
		$( ".form.ModeDebug" ).hide();
	// Expert (3)
	} else if ($( "#Ni" ).val() == 3) {
		$( ".conseil.debutant" ).hide();
		$( "#EjOnglet" ).show();
		$( ".form.Ri" ).show();
		$( ".form.Rb" ).show();
		$( ".form.AUT" ).show();
		$( ".form.U" ).show();
		$( ".form.DD" ).show();
		$( ".part.bat" ).show();
		$( ".part.regu" ).show();
		$( ".form.ModBat" ).show();
		$( ".form.IbatCharge" ).show();
		$( ".form.IbatDecharge" ).show();
		$( ".form.ModPv" ).show();
		$( ".form.TypePv" ).show();
		$( ".part.cable" ).show();
		$( ".form.cablageRho" ).show();
		$( ".form.cablagePtPourcent" ).show();
		$( ".form.cablageRegleAparMm" ).show();
		$( ".form.ModeDebug" ).show();
	}
}

// Onglet carte zone
// http://dmouronval.developpez.com/tutoriels/javascript/mise-place-navigation-par-onglets-avec-jquery/
$(function() {
	$('#onglets').css('display', 'block');
	$('#onglets').click(function(event) {
		var actuel = event.target;
		if (!/li/i.test(actuel.nodeName) || actuel.className.indexOf('actif') > -1) {
			//alert(actuel.nodeName)
			return;
		}
		$(actuel).addClass('actif').siblings().removeClass('actif');
		setDisplay();
		$( "#Ej" ).val('');
	});
	function setDisplay() {
		var modeAffichage;
		$('#onglets li').each(function(rang) {
			modeAffichage = $(this).hasClass('actif') ? '' : 'none';
			$('.item').eq(rang).css('display', modeAffichage);
		});
	}
	setDisplay();
});

// Reset form
$( "#Reset" ).click(function() {
	window.location = '<?= $config_ini['formulaire']['UrlCalcPvAutonome'] ?>';
});

$(document).ready(function() {
	// Init formulaire 
	changeNiveau();
	modPvChange(); 
	modBatChange();
	modReguChange(); 
	periodeChange();
	trackingChange();
	sumbitEnable();	
}); 


</script>

