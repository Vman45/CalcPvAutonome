<?php 
$config_ini = parse_ini_file('./config.ini', true); 

echo '<table>';
switch ($_GET['data']) {
    case 'pv':
		echo '<tr>';
			echo '<th>'._('Type').'</th>';
			echo '<th>'._('Max power (Pmax)').'</th>';
			echo '<th>'._('Open circuit voltage (Voc)').'</th>';
			echo '<th>'._('Short-circuit current (Isc)').'</th>';
			echo '<th>'._('Estimate price').'</th>';
		echo '</tr>';
		foreach ($config_ini['pv'] as $pvModele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['type']).'</td>';
				echo '<td>'.$valeur['W'].' W</td>';
				echo '<td>'.$valeur['Vdoc'].' V</td>';
				echo '<td>'.$valeur['Isc'].' A</td>';
				echo '<td>'.round($config_ini['prix']['pv_bas']*$valeur['W']).'
				 - '.round($config_ini['prix']['pv_haut']*$valeur['W']).' &euro;</td>';
			echo '</tr>';
		}	
	break;
	case 'batterie':
		echo '<tr>';
			echo '<th>'._('Name').'</th>';
			echo '<th>'._('Type').'</th>';
			echo '<th>'._('Capacity (C10)').'</th>';
			echo '<th>'._('Voltage').'</th>';
			echo '<th>'._('Estimate price').'</th>';
		echo '</tr>';
		foreach ($config_ini['batterie'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['type'].'</td>';
				echo '<td>'.$valeur['Ah'].' Ah</td>';
				echo '<td>'.$valeur['V'].' V</td>';
				echo '<td>'.round($config_ini['prix']['bat_'.$valeur['type'].'_bas']*$valeur['Ah']*$valeur['V']).'
				 - '.round($config_ini['prix']['bat_'.$valeur['type'].'_haut']*$valeur['Ah']*$valeur['V']).' &euro;</td>';
			echo '</tr>';
		}	
	break;
	case 'regulateur':
		echo '<tr>';
			echo '<th>'._('Name').'</th>';
			echo '<th>'._('Battery plant voltage').'</th>';
			echo '<th>'._('Max panel power').'</th>';
			echo '<th>'._('Max panel voltage').'</th>';
			echo '<th>'._('Max panel current').'</th>';
			echo '<th>'._('Estimate price').'</th>';
		echo '</tr>';
		foreach ($config_ini['regulateur'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['Vbat'].' V</td>';
				echo '<td>'.$valeur['PmaxPv'].' W</td>';
				echo '<td>'.$valeur['VmaxPv'].' V</td>';
				echo '<td>'.$valeur['ImaxPv'].' A</td>';
				echo '<td>~'.$valeur['Prix'].' &euro;</td>';
			echo '</tr>';
		}	
	break;
	case 'convertisseur':
		echo '<tr>';
			echo '<th>'._('Name').'</th>';
			echo '<th>'._('Battery park voltage').'</th>';
			echo '<th>'._('Max power (at 25 °C)').'</th>';
			echo '<th>'._('Peak power').'</th>';
			echo '<th>'._('Estimate price').'</th>';
		echo '</tr>';
		foreach ($config_ini['convertisseur'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['Vbat'].' V</td>';
				echo '<td>'.$valeur['Pmax'].' W</td>';
				echo '<td>'.$valeur['Ppointe'].' W</td>';
				echo '<td>'.round($config_ini['prix']['conv_bas']*$valeur['VA']).'
				 - '.round($config_ini['prix']['conv_haut']*$valeur['VA']).' &euro;</td>';
			echo '</tr>';
		}	
	break;
	case 'cable':
		echo '<tr>';
			echo '<th>'._('Section').'</th>';
			echo '<th>'._('Estimate price').'</th>';
		echo '</tr>';
		foreach ($config_ini['cablage'] as $modele => $valeur) {
			echo '<tr>';
				echo '<td>'.ucfirst($valeur['nom']).'</td>';
				echo '<td>'.$valeur['prix'].' &euro;/m</td>';
			echo '</tr>';
		}	
	break;
	default:
       echo 'no hack';
}
echo '</table>';
?>
