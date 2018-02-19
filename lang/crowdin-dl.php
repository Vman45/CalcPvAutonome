#!/usr/bin/php
<?php

// Script pour les traductions sur crowdin
// - Compilation sur crowdin
// - Téléchargement
// - Compilation en .mo
// - Mise à jour du lang.ini pour dire merci au contributeur

$projectIdentifier='calcpvautonome';
$projectKey=rtrim(file_get_contents('.crowdin-key'));
$tradDir='./';

ini_set('auto_detect_line_endings', 1);
ini_set('default_socket_timeout', 5); // socket timeout, just in case

// Build
$request_url = 'https://api.crowdin.com/api/project/'.$projectIdentifier.'/export?key='.$projectKey;
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
// CHECK si le résultat dit <success status="built"/> ?
echo $result;

// On liste les langues qui on un début de traduction :
$request_url = 'https://api.crowdin.com/api/project/'.$projectIdentifier.'/status?key='.$projectKey.'&json';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$result = curl_exec($ch);
curl_close($ch);
$jsonContenu=json_decode($result, true);
$lang_ini_content='';
foreach ($jsonContenu as $lang) {
	if ($lang['translated_progress'] != 0) {
				
		echo $lang['code'].' '.$lang['translated_progress'].'%';
		if (!is_dir($tradDir.'./'.$lang['code'])) {
			echo "\n \t Un nouvelle langue a été traduite, il faut créer le répertoire et vérifier son existance dans le locale (dpkg-reconfigures locales)";
		} else {
			file_put_contents($tradDir.'/'.$lang['code'].'/'.$lang['code'].'.zip', file_get_contents('https://api.crowdin.com/api/project/'.$projectIdentifier.'/download/'.$lang['code'].'.zip?key='.$projectKey));
			exec('unzip -o '.$tradDir.'/'.$lang['code'].'/'.$lang['code'].'.zip -d '.$tradDir.'/'.$lang['code'], $output, $return_var);
			if ($return_var != 0) {
				exit('Erreur à l\'extraction du zip : '.$return_var);
			}
			unlink($tradDir.'/'.$lang['code'].'/'.$lang['code'].'.zip');
			exec('msgfmt '.$tradDir.'/'.$lang['code'].'/LC_MESSAGES/messages-'.$lang['code'].'.po -o '.$tradDir.'/'.$lang['code'].'/LC_MESSAGES/messages.mo', $output, $return_var);
			if ($return_var != 0) {
				exit('Erreur à la mise à jour du .mo : '.$return_var);
			}
			
			// On récupère les traducteurs : 
			$request_url = 'https://api.crowdin.com/api/project/'.$projectIdentifier.'/reports/top-members/export?key='.$projectKey.'&language='.$lang['code'].'&format=csv&json=1';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $request_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($ch);
			curl_close($ch);
			$json_array=json_decode($result, true);
			file_put_contents($tradDir.'/'.$lang['code'].'/'.$lang['code'].'-top-members.csv', file_get_contents('https://api.crowdin.com/api/project/'.$projectIdentifier.'/reports/top-members/download?key='.$projectKey.'&hash='.$json_array['hash']));
			// Analyse CSV
			if (($handle = fopen($tradDir.'/'.$lang['code'].'/'.$lang['code'].'-top-members.csv', "r")) !== FALSE) {
				$row=0;
				$nb=0;
				$traducteurs=array();
				while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
					$row++;
					// On supprime la première ligne
					if ($row != 1) {
						// On ne considère que ceux qui on traduit dans cette langue
						if ($data[2] > 0) {
							$nb++;
							$traducteurs[$nb]['name']=$data[0];
							$traducteurs[$nb]['translated']=$data[2];
						}
					}
				}
				fclose($handle);
			}
			
			// Exeption pour l'espagnol (qui n'est pas en "es" mais en "es-ES"
			if ($lang['code'] == 'es-ES') {
				if (!is_link($tradDir.'/es')) {
					symlink($tradDir.'/'.$lang['code'], $tradDir.'/es');
				}
				$lang['code']='es';
			}
			
			// Création du fichier ini
			$lang_ini_content.="[".$lang['code']."]\n";
			$lang_ini_content.="code='".$lang['code']."'\n";
			$lang_ini_content.="translated_progress=".$lang['translated_progress']."\n";
			$nb_traslator=0;
			foreach ($traducteurs as $traducteur) {
				$nb_traslator++;
				$lang_ini_content.="translator".$nb_traslator."[name]='".$traducteur['name']."'\n";
				$lang_ini_content.="translator".$nb_traslator."[translated]=".$traducteur['translated']."\n";
			}
		}
		echo "\n";
	}
}

$lang_ini_content.="[aa]\n";
$lang_ini_content.="code='aa'\n";
$lang_ini_content.="translated_progress=100\n";
$lang_ini_content.="translator1[translated]=500\n";
$lang_ini_content.="translator1[name]='name1'\n";
$lang_ini_content.="translator2[translated]=100\n";
$lang_ini_content.="translator2[name]='name2'\n";

$lang_ini_content.="[en]\n";
$lang_ini_content.="code='en'\n";
$lang_ini_content.="translated_progress=100\n";
$lang_ini_content.="translator1[translated]=1000\n";
$lang_ini_content.="translator1[name]='nednet'\n";
$lang_ini_content.="translator2[translated]=300\n";
$lang_ini_content.="translator2[name]='coucou39'\n";
$lang_ini_content.="translator2[translated]=100\n";
$lang_ini_content.="translator2[name]='guillerette'\n";
$lang_ini_content.="translator2[translated]=100\n";
$lang_ini_content.="translator2[name]='mirrim'\n";
$lang_ini_content.="translator2[translated]=100\n";
$lang_ini_content.="translator2[name]='ppmt'\n";

file_put_contents($tradDir.'lang.ini', $lang_ini_content);

if (is_file('/etc/init.d/php7.0-fpm')) {
	exec('/etc/init.d/php7.0-fpm restart', $output, $return_var);
	if ($return_var != 0) {
		exit('Erreur au redémarrage de php : '.$return_var);
	} 
}

?>
