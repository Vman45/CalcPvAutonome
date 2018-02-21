<?php
//							CODE			LOCALE (locale -a)
$langueEtLocalDispo=array(	'aa' 		=> 'aa_ER',
							'fr'		=> 'fr_FR', 
							'es-ES' 	=> 'es_ES', 
							'pt' 		=> 'pt_PT', 
							'pt-BR' 	=> 'pt_BR', 
							'eo' 		=> 'eo', 
							'nl' 		=> 'nl_NL', 
							'tr' 		=> 'tr_TR', 
							'uk' 		=> 'uk_UA', 
							'id' 		=> 'id_ID', 
							'af' 		=> 'af_ZA', 
							'ru' 		=> 'ru_RU', 
							'it' 		=> 'it_IT', 
							'ja'		=> 'ja_JP', 
							'en'		=> 'en_US', 
							'pl'		=> 'pl_PL', 
							);

function lang2locale($langue) {
	global $langueEtLocalDispo;
	if ($langueEtLocalDispo[$langue] != '') {
		return $langueEtLocalDispo[$langue];
	} else {
		// par défaut
		return 'en_US';
	}
}
function locale2lang($localeRecherche) {
	global $langueEtLocalDispo;
	foreach($langueEtLocalDispo as $code=>$locale) {
		if ($locale == $localeRecherche) {
			return $code; 
			break;
		}
	}
	// par défaut
	return 'en';
}
?>
