Présentation
============

Ce plugin suiviCO2 pour Jeedom a 2 fonctions principales : 
- Visualiser ses émissions de CO2 liées a sa consommation électrique
- Disposer de la valeur actuelle de gCO2 par kWh produit, émis en France en temps réel. De facon a pouvoir conditionner ses équipements facultatifs (retarder un peu le chauffe-eau en HC par exemple)

Les données de CO2 par kWh produit sont celles fournies par RTE, plus d'infos ici : <a href="https://www.rte-france.com/fr/eco2mix/eco2mix-co2" target="_blank">eco2mix-co2</a>


![](https://raw.githubusercontent.com/AgP42/Jeedom-AutoRemote/master/docs/assets/images/Notif_1.jpg)
![](https://raw.githubusercontent.com/AgP42/Jeedom-AutoRemote/master/docs/assets/images/Notif_2.jpg)


Configuration du plugin
========================

Après téléchargement du plugin, il vous faut l’activer, et cocher la case "Afficher le panneau desktop". 

Configuration des équipements (Mes sources d'émission CO2)
=================================================

Une fois le plugin activé, il est visible dans le menu "plugin"/"energie".

Vous pouvez alors définir plusieurs "sources d'émission CO2". Chacune est indépendante. Il faut l'assigner à un objet parent pour qu'elle soit visible dans le panneau desktop. 


Onglet Equipement
-----------------

![](https://raw.githubusercontent.com/AgP42/Jeedom-AutoRemote/master/docs/assets/images/Equipement.png)

Pour trouver la clef API il vous suffit de naviguer vers l'URL donné par AutoRemote sur votre équipement Android.


Onglet Commandes
-----------------

Les commandes sont automatiquement créées à la sauvegarde de l'équipement. 


Onglet Historique
--------------

Il s'agit des options pour les 2 types de commandes, sauf pour le champ "cible".

![](https://raw.githubusercontent.com/AgP42/Jeedom-AutoRemote/master/docs/assets/images/Opt_msg.png)


Utilisation du panneau desktop
======================

Personaliser un champ pour une commande en particulier
------------------------------------------------------

