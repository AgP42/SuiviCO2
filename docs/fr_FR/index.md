Présentation
============

Ce plugin suiviCO2 pour Jeedom a 2 fonctions principales : 
- Visualiser ses émissions de CO2 liées a sa consommation électrique
- Disposer de la valeur actuelle de gCO2 par kWh produit, émis en France en temps réel. De facon a pouvoir conditionner ses équipements facultatifs (retarder un peu le chauffe-eau en HC par exemple)


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

Il faut assigner l'équipement à un objet parent et cocher la case "Activer" pour qu'elle soit visible dans le panneau desktop. 

Vous devez ensuite définir la commande Jeedom renvoyant l'index (fixe ou HP), et éventuellement l'index HC si vous en avez un. 

Et définir vos coûts d'électricité, en €. 


Onglet Commandes
-----------------

Les commandes sont automatiquement créées à la sauvegarde de l'équipement. Il n'y a rien a configurer ici. 


Onglet Historique
--------------

![](https://raw.githubusercontent.com/AgP42/suiviCO2/dev/docs/assets/images/OngletHistorique.png)

Le plugin chargera les données de l'API ainsi que le calcul de nos consommations toutes les heures, toutefois vous pouvez récuperer ici les données passés : 

- Données temps réel CO2 par kWh en France : cette commande va chercher les données de l'API "temps réel", c'est à dire les données prévisionnelles, par 15 min, qui sont mises à jour toutes les heures. Cette commande permet de recuperer la totalité des données présentes sur le serveur, c'est à dire environ 1,5 mois de données. Le temps de chargement prend environ 1 min avec un RPI3 et une connection internet correcte. 

Utilisation du panneau desktop
======================

Personaliser un champ pour une commande en particulier
------------------------------------------------------

