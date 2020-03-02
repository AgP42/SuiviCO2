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
