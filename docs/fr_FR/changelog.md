# 0.0.1 - 1ere Beta

- Inputs à donner par l'utilisateur : index HP (et HC) EDF (téléinfo), cout abo et HP/HC EDF
- Fonctionnalitées :
   - Visualisation des emissions gCO2/kWh produit en france (datas from https://opendata.reseaux-energies.fr)
   - Visualisation de la conso HP et HC de l'utilisateur
   - Visualisation des couts associés
   - Visualisation des emissions gCO2 de la maison
   - Selection par période et groupement par heure, jour, semaine, mois ou année
   - Mise à disposition de la valeur actuelle d'emission par kWh en France, pour conditionner ses consommations selon la valeur courante
   - Chargement possible de l'historique des valeurs gCO2/kWh en France (environ 1,5 mois dispo via l'API)
   - Chargement possible de l'historique des conso HP et HC, si les commandes d'index étaient historisées dans Jeedom

# 0.0.2 - 2eme Beta

- ajout de l'import des datas consolidées et définitives
- correction typo

# 0.0.3 - 3eme Beta

- ajout des datas type gaz, fioul ou autre avec valeur fixe pour gCO2/kWh et coef thermique

# 0.0.4 - 4eme Beta

- ajout des totaux jours, semaine et mois pour affichage dans le dashboard

# 0.0.5 - 5eme Beta --> stable

- nouvelle fonction optimisée pour l'import des historiques de conso Wh - merci @jpty
- passage en stable

# 1.0.1

- changement icone, merci @mich0111
- inversion des couleurs HP et HC pour avoir le HP en rouge

# 1.0.1 - beta 1

- ajout fct calculs totaux CO2 pour intégration au plugin suivi conso

# 1.0.2 - beta 2

- correction erreur 500 si aucun equipement actif
(https://community.jeedom.com/t/plugin-tiers-plugin-suivico2-new-plugin-cherche-beta-testeur/19069/55)

# 1.0.3 - beta 3

- Ajout possibilité d'importer les historiques de conso via le plugin SuiviConso, si présent

# 1.0.4 - beta 4

- Correction bug courbe en jeedom v4, merci @Titi_Titi
- Implementation de l'utilisation de la configuration du plugin Suivi Conso pour la configuration de SuiviCO2
(elec only pour l'instant)
- Update affichage page de configuration
- Correction bug sur calcul total CO2 semaine
- update doc

# 2.0.0

- Toutes les fonctions des beta précédentes, principalement les interfaces avec le plugin SuiviConso
- Debug cas plugin suivi conso inexistant
- Tests en v3 et v4 avec le plugin suivi conso stable du 16/03/2020

# 2.0.1

- Changement icône
- Vérif datas après le changement d'heure : all good !

# 2.0.2

- Changement calcul pour total par semaine

# 2.0.3 - avril 2020

- Update arborescence panel (ajout visibilité des équipements non liés à un objet)
- Debug page de configuration lorsque suiviconso est présent mais qu'on ne souhaite pas l'utiliser
- Update doc (dont nouveaux liens Jeedom)

# 2.0.4 - mai 2020

- Correction boucle de calcul ligne 103 class.php qui bridait les conso trop élevées

# 2.0.5 - 12 mai 2020

- Changement des dates d'enregistrement des calculs de conso pour enregistrer en début de période calculées et non en fin de période. Ceci permet d'avoir des totaux sur la journée correct, autrement la conso de 23h à minuit était prise en compte le lendemain. (Pour les fonctions cronHourly et les historiques). Merci @Francois pour l'analyse et la solution !
- Debug sur date d'enregistrement de la derniere valeur co2parkWh disponible. (à 13h00 on dispose de la valeur de 12h45, c'est donc 12h45 qui est maintenant enregistré et non plus 13h)
- Vérification des calculs manuellement
