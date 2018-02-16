# CalcPvAutonome

Outil web pour aider à calculer/dimensionner son installation photovoltaïque en site isolé (autonome). 
 
### Démonstration

La démonstration se trouve ici : http://calcpv.net

### Appel à l'entraide

Pour perfectionner ce logiciel j'ai besoin de vous. donc n'hésitez par à émettre des suggestions sur la méthode de calcul, des idées sur de nouvelles fonctionnalités, des réserves sur les estimations de prix... Tout est bon à prendre, n'[hésitez pas c'est par là](http://david.mercereau.info/contact/).

### Fonctionnalité 

Pour l'utilisateur de base :

  - 3 mode au formulaire (Débutant, Eclairé, Expert)
  - Le niveau d'ensoleillement est issus des données PVGIS5 (http://re.jrc.ec.europa.eu/PVGIS5-release.html) 
  - En mode expert, ajuster le degré de décharge, les valeurs de rendement électrique des batteries ou du reste de l'installation, capacité de courant charge/décharge max...
  - Déduction automatique du nombre de panneaux, batteries et régulateur nessésaire (possibilité de forcer un modèle type ou de personnaliser ces caractéristiques)
  - Hypothèse de câblage panneaux / régulateur (exemple : 3 panneaux en série sur 2 paralèles branché sur un régulateur)
  - Schéma de câblage
  - Calcul des sections de câblage
  - Estimation d'une fourchette du coût du matériel (panneau, batterie, régulateur, convertisseur, câble...)
  - Déduction automatique de la tension du parc de batteries à utiliser (possibilité de forcer une valeur en mode expert)
  - Explication détailé du calcul pour fait pour parvenir au résultat

Pour les utilisateurs avancés : 

  - Intégration sur votre site web
  - Modifier le fichier config.ini pour changer
	- Les valeurs par défaut du formulaire
	- Les valeurs d'irradiation de la carte par zone
	- La fourchette de prix
	- Les modèles de matériel type (batterie, panneau, régulateur, convertisseur) possible pour la détermination d'une configuration

### Installation de l'outil sur mon site :

#### Requis pour le fonctionnement / l'installation du 

  * PHP (5.6 minimum) + lib gd + GeoIP + CURL + Gettext
  * Apache/Nginx (ou autre serveur web, service d'hébergement mutualisé...) avec URL Rewriting

#### Installation

Télécharger et décompresser le fichier zip du master : https://github.com/kepon85/CalcPvAutonome/archive/master.zip

Le rendre accessible depuis votre serveur http et personnaliser les valeur du fichier config.ini

### Changelog

 - 4.1.2
     - #4 Prise en compte des boîtiers de jonctions
     - Correcion bug 
       - #20 "lancer le calcul" ne se déverrouille pas en mode manuel
       - le graphique "La production estimé de votre système" ne s'affichait pas
 - 4.0.1
	 - Ajout début traduction NL & TR
     - #21 Campagne de don
     - URL rewriting pour lang
 - 4.0
	- Internationalisation : https://crwd.in/calcpvautonome
		- Traduction FR, EN
	- 3 graph avec les données PVGIS 5 "PERFORMANCE OF OFF-GRID PV SYSTEMS" (http://re.jrc.ec.europa.eu/pvg_tools/en/tools.html#SA)
	- BUG : suppression de la tension PV (aucunement util voir faux)
 - 3.1
	- Ajout d'un graph avec les données de rayonnement moyen quotidien
 - 3.0
	- Ines Solaire à laisser place à PVGIS5 (http://re.jrc.ec.europa.eu/PVGIS5-release.html) pour les données d'ensoleillement
		- Utilisation des bases PVGIS-CMSAF, PVGIS-SARAH & PVGIS-NSRDB de PVGIS
		- Mise en cache des données
		- Intégration d'une carte OpenStreetMap pour sélectionner sa position
	- Prise en compte des traqueur solaire
	- Design Responsive (pour le calcpvautonome seulement)
 - 2.2
	- Mode transparent/débug pour mieux comprendre le fonctionnement du logiciel (uniquement en mode expert)
	- Prise en compte de l'autonomie partielle (pour les utilisations saisonnières type camping car...) 
		- Nouvelle base IGP avec, cette fois ci, toutes les valeurs d'Ines Solaire : http://ines.solaire.free.fr/gisesol.php
 - 2.0
	- Utilisation des valeurs IGP (iradiation global sur plan) de Ines Solaire : http://ines.solaire.free.fr/gisesol.php
		- Choix de l'orientation, l'inclinaison et de l'albédo (en expert) en fonction de la ville
		- Possibilité de faire déterminer l'orientation et l'inclinaison la plus optimum en fonction de la ville
		- Une base local à été aspiré, plus de détail voir https://github.com/kepon85/CalcPvAutonome/blob/master/ines.solaire/README.md
 - 1.2
	- Estimation de budget groupé et totalisé
	- Ajout du contrôleur de batterie (voltmètre pour installation < à 100Ah)
	- Pédagogie : 
		- Aide à la simulation PGVIS
		- Conseil pour les débutant (lecture base de l'électricité)
 - 1.1.1
	- Petit bug sur la tension du convertisseur qui n'était pas la bonne
	- Modification des sections de câble pour arriver "au plus prêt" et non pas "au plus haut" : http://forum.apper-solaire.org/viewtopic.php?f=16&t=9242&start=15#p122113
 - 1.1
	- Batteries : 
		- Ajout de modèles types OPvS (en 2, 4, 6V)
		- Découpage des modèles type de batteries par technologie
	- Câblage, suite à : http://forum.apper-solaire.org/viewtopic.php?f=16&t=9242&p=122109#p122098
		- Passage de la tolérance de chute de tension à 1% par défaut
		- Ajout de la règle 6A/mm² (la valeur est personnalisable en mode expert)
		- Rhô à 0,019 pour prendre le "pire" (cuivre à 50°) 
 - 1.0
	- Prise en compte de la puissance électrique maximum nécessaire 
		- Ajout de la contrainte 0,2C maximum du courant de la batterie
	- Déduction automatique d'un convertisseur / onduleur avec estimation du prix
	Calcconso : 
		- Amélioration des modèles 24/24, possibilité d'entrée une valeur Wh/j manuelement 
		- Déterminer la puissance électrique maximum nécessaire à votre installation
 - 0.5
	- Calcul des sections de câble partant du régulateur
		http://solarsud.blogspot.fr/2014/11/calcul-de-la-section-du-cable.html
		http://www.plaisance-pratique.com/calcul-de-la-section-des-cables?lang=fr
		http://www.sigma-tec.fr/textes/texte_cables.html
	- Popup d'affichage des modèles
 - 0.4.1
	- Bug FIX plusieurs "auto" appariasse dans le select de tension de batterie en manipulant les régulateurs...
	- Pas plus de 2 bat en paralèle : 
		http://forum-photovoltaique.fr/viewtopic.php?f=84&t=36009#p411019
 - 0.4
	- Schéma de câblage
	- Batteries : prise en compte et personnalisation du courant de charge max (0,2 C)
	- Panneaux : recommandation de pose de boîtier de raccordement au delas de 2 parallèles ( http://forum-photovoltaique.fr/viewtopic.php?p=409170&sid=1ac1384c932b26d382144e0d5c558d04#p409170 )
 - 0.3.2
    - Personnalisation des caractéristiques des batteries de travails
 - 0.3.1
	- Prise en compte du courant de charge max des batteries (dans le régulateur)
	- Ajout de l'angle 0° dans la carte d'iradiation solaire
 - 0.3
	- Déduction automatique du régulateur nécessaire (possibilité de forcer un modèle type ou de personnaliser ces caractéristiques)
	- Déduction automatique du câblage des panneaux (série/parallèle) 
 - 0.2
	- Déduction automatique du nombre de panneaux (possibilité de forcer un modèle de travail)
    - Possibilité de privilégier la technologie monocristalin ou polycristalin 
    - BUG fix CalcConsommation : il y a une virgule dans le tableau de consommation, à l'import dans le calcpvautonome ça passe pas...
    - Vérification valeur du formulaire
 - 0.1
   - 3 mode au formulaire (Débutant, Eclairé, Expert)
   - Pour déterminer l'ensoleillement : 
	 - (simple) Carte par zone
	 - (précis) Valeur du site http://inécessairenes.solaire.free.fr/gisesol_1.php (kWh/m²/j)
   - En mode expert, ajuster le degré de décharge, les valeurs de rendement électrique des batteries ou du reste de l'installation, 
   - Déduction automatique de la tension du parc de batteries à utiliser (possibilité de forcer une valeur en mode expert)
   - Déduction automatique de câblage du parc des batteries et du modèle à utiliser (exemple : "2 batteries 220Ah 12V en série") (possibilité de forcer un modèle de travail en mode expert)
   - Estimation d'une fourchette du coût du parc de batterie & photovoltaïque 
   - Explication détailé du calcul pour fait pour parvenir au résultat
   - Intégration sur votre site web
   - Modifier le fichier config.ini pour changer
 	 - Les valeurs par défaut du formulaire
	 - Les valeurs d'irradiation de la carte par zone
	 - La fourchette de prix des panneaux photovoltaïque et des batteries
	 - Les modèles de batteries possible pour la détermination d'une configuration

### Auteur / contributeur

  - David Mercereau [david #arobase# mercereau #point# info](http://david.mercereau.info/contact/) (auteur)
	  - Largement inspiré du [tableur posté par lr83](http://forum-photovoltaique.fr/viewtopic.php?p=403856#p403837)
  - Guillaume Piton de [SolisION-event](http://solision-event.centerblog.net) (contribution technique)

### License

Le code est sous licence BEERWARE : Tant que vous conservez cet avertissement, vous pouvez faire ce que vous voulez de ce truc. Si on se rencontre un jour et que vous pensez que ce truc vaut le coup, vous pouvez me payer une bière en retour. 

L'image de la France : Creative Commons paternité – partage à l’identique 3.0 (non transposée). SolarGIS © 2011 GeoModel Solar s.r.o.

> Written with [StackEdit](https://stackedit.io/).



