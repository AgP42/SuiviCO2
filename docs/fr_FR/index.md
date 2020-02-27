Présentation
============

Ce plugin suiviCO2 pour Jeedom a 2 fonctions principales :
- Disposer de la valeur actuelle de gCO2 par kWh produit, émis en France, en temps réel. De facon à pouvoir conditionner ses équipements facultatifs (retarder un peu le chauffe-eau en HC par exemple)
- Visualiser ses émissions de CO2 liées à sa consommation électrique (ainsi que la consommation, coût associé et les émissions globales de la production en France) :

![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/PanneauDesktop.png)

Les données de CO2 par kWh produit sont celles fournies par RTE, plus d'infos ici : <a href="https://www.rte-france.com/fr/eco2mix/eco2mix-co2" target="_blank">eco2mix-co2</a>


Configuration du plugin
========================

Après téléchargement du plugin, il vous faut l’activer, et cocher la case "Afficher le panneau desktop".

Configuration des équipements (Mes sources d'émission CO2)
=================================================

Une fois le plugin activé, il est visible dans le menu "plugin"/"energie".

Vous pouvez alors définir plusieurs "sources d'émission CO2". Chacune est indépendante.

Onglet Equipement
-----------------

![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/OngletEquipement.png)

Pour que cet équipement soit visible dans le panneau desktop, il faut l'assigner à un objet parent et cocher la case "Activer".
La case "Visible" permet de définir la visibilité du widget sur le dashboard Jeedom :
![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/widget.png)
Cliquer sur la commande permet de visualiser son historique :
![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/historique.png)


Dans l'onglet "Equipement", vous devez ensuite définir la commande Jeedom renvoyant l'index (fixe ou HP) à utiliser, et éventuellement l'index HC si vous en avez un.

Et définir vos coûts d'électricité, en €.

Onglet Commandes
-----------------

Les commandes sont automatiquement créées à la sauvegarde de l'équipement. Il n'y a rien a configurer ici. Vous pouvez éventuellement aller dans les paramétres de chaque commande pour définir leur visibilité sur le dashboard.

Onglet Historique
--------------

![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/OngletHistorique.png)

Le plugin chargera les données de l'API ainsi que le calcul de nos consommations toutes les heures, toutefois vous pouvez récuperer ici les données passés :

- Données temps réel CO2 par kWh en France : cette commande va chercher les données de l'API "temps réel", c'est à dire les données prévisionnelles, par 15 min, qui sont mises à jour toutes les heures. Cette commande permet de recuperer la totalité des données présentes sur le serveur, c'est à dire environ 1,5 mois de données. Le temps de chargement prend environ 1 min avec un RPI3 et une connection internet correcte.

- Ma conso kWh : uniquement si les commandes contenant les index étaient déjà historisées dans jeedom. Cette commande permet avec les données d'index historisées de calculer et d'enregistrer vos conso HP et HC pour les visualiser dans le panneau desktop. Il est possible de choisir la période voulue, attention, les données peuvent être longues à charger.
Pour infos voici quelques durées avec un RPI3 :
     - 1 mois, HP et HC : 3s
     - 2 mois : 7s
     - 6 mois : 20s
     - 1 an : 77s
Eviter de charger plus d'1 an. Timeout aprés 10min.

Il est possible de relancer l'historique sur des dates déjà enregistrées.

Lors de la création de l'équipement, il est possible que la 1ere valeur de consommation HP/HC soit manquante, vous pouvez alors relancer l'historisation des données sur cette journée pour la récuperer.

Utilisation du panneau desktop
======================
![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/PanneauDesktop.png)

Vous pouvez sélectionner en haut à droite la période a visualiser ainsi que le regroupement des infos à faire sur les graphs.

API
======

Ce plugin utilise les données nationales fournies par RTE : <a href="https://opendata.reseaux-energies.fr/explore/dataset/eco2mix-national-tr/information/?disjunctive.nature" target="_blank">https://opendata.reseaux-energies.fr/</a>

