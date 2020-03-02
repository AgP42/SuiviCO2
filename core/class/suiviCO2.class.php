<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

/***************NOTES***************

        /*
        date ( string $format [, int $timestamp = time() ] ) : string

        Retourne une date sous forme d'une chaîne, au format donné par le paramètre format, fournie par le paramètre timestamp ou la date et l'heure courantes si aucun timestamp n'est fourni. En d'autres termes, le paramètre timestamp est optionnel et vaut par défaut la valeur de la fonction time().

        strtotime ( string $time [, int $now = time() ] ) : int

        La fonction strtotime() essaye de lire une date au format anglais fournie par le paramètre time, et de la transformer en timestamp Unix (le nombre de secondes depuis le 1er Janvier 1970 à 00:00:00 UTC), relativement au timestamp now, ou à la date courante si ce dernier est omis.

        exemple :
        $test2 = date('H:i', strtotime($record_time . ' -15 min')); // on prend le $record_time au format H:i, on le converti en timestamp, on lui applique -15min et on le reconverti en format H:i
        log::add('suiviCO2', 'debug', 'Test dates : ' . $test2);

        Tous les formats : https://www.php.net/manual/fr/function.date.php

        */

/*          ce morceau de code va chercher tout l'historique de la commande et le loggue
            $previous = $cmd->getHistory();
            foreach ($previous as $value) {
              log::add('suiviCO2', 'debug', ' previous : ' . $value->getValue());
            }*/

/*

        $abo = 18;
        $valueDateTime = '2020-02-20 12:50:00';

        $nbJourCeMois = date('t', strtotime($valueDateTime));
        log::add('suiviCO2', 'debug', 'Nb jours ci mois ci : ' . $nbJourCeMois . ' - Cout abo par heure : ' . $abo/$nbJourCeMois/24);
*/



//*/

class suiviCO2 extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    //*
    // * Fonction exécutée automatiquement toutes les minutes par Jeedom
  /*    public static function cron() {


      }
     //*/


      public function calculConso($_type = 'HP'){

          //on va chercher l'info index_HP ou HC via la conf utilisateur
          $index = jeedom::evaluateExpression($this->getConfiguration('index_' . $_type));


          //on recupere la precedente valeur stockée, selon HP ou HC
          $lastValue = $this->getConfiguration('lastValue' . $_type);

           //on sauvegarde la valeur actuelle pour le prochain tour
          $this->setConfiguration('lastValue' . $_type, $index);
          $this->save();

     //     log::add('suiviCO2', 'debug', 'lastIndex' . $_type . ' : ' . $lastValue . ' Index'  . $_type . ' : ' . $index);

          //on calcule la consommation entre les 2 derniers index
          $consumption = $index - $lastValue;

          //si on a un coef declaré et numerique, on va l'utiliser
          $coef_thermique = $this->getConfiguration('coef_thermique');
          if($coef_thermique != '' && is_numeric($coef_thermique)){
            $consumption = $consumption*$coef_thermique;
          }

          //si cette consommation est >0, on va la stocker en base - NON, il faut stocker les 0 sinon l'archivage de l'historique fait n'importe quoi... dommage de stocker des 0...
          // TODO a ameliorer...

          if ($consumption < 1000) { //1000 kWh c'est environ 170€, si on consomme ca par heure c'est qu'on a un gros probleme... Ce test permet de ne pas sauvegarder l'index entier lors de la 1ere boucle apres la creation de l'objet.
            $cmd = $this->getCmd(null, 'consumption' . $_type);
            if (is_object($cmd)) {
              $cmd->setCollectDate($datetime);
              log::add('suiviCO2', 'debug', 'eqLogic_id : ' . $this->getId() . ' - Index now ' . $_type . ' : ' . $index . ' - Prev Index '  . $_type . ' : ' . $lastValue . ' = conso ' . $_type . ' (Wh) : ' . $consumption);
              $cmd->event($consumption);
            }
          }

      }

      public function calculTotauxCo2Periode($startDate, $endDate){

        log::add('suiviCO2', 'debug', 'calculTotauxCo2Periode pour : ' . $startDate . ' - ' . $endDate);

        $totalCO2 = 0;

        if($this->getConfiguration('conso_type') == 'elec'){ //si on est en conso de type elec, on va utiliser les infos de l'API

          // on recupere la cmd API
          $cmdCO2API = $this->getCmd(null, 'co2kwhfromApi');
          if (!is_object($cmdCO2API)) { // si elle existe pas, on coupe tout
            return array();
          }

          $cmdConsoHP = $this->getCmd(null, 'consumptionHP');
          if (!is_object($cmdConsoHP)) { // si elle existe pas, on coupe tout
            return array();
          }
          $consoHP = $cmdConsoHP->getHistory($startDate, $endDate);


          $cmdConsoHC = $this->getCmd(null, 'consumptionHC');
          if (is_object($cmdConsoHC)) { // si elle existe, on choppe son historique, sinon on fait rien
            $consoHC = $cmdConsoHC->getHistory($startDate, $endDate);
          }

          // on boucle dans toutes les valeurs de l'historique de la cmd API
          foreach ($cmdCO2API->getHistory($startDate, $endDate) as $historyAPI) {

            $valueDateTimeAPI = $historyAPI->getDatetime();
            $valueAPI = $historyAPI->getValue();

            // on filtre sur les heures piles
            if(date('i', strtotime($valueDateTimeAPI)) == "00"){ // on extrait le champ min et on verifie qu'il vaut 00

                // on boucle dans toutes les valeurs de l'historique de la cmd HP pour recuperer les valeurs et on les additionne
                foreach ($consoHP as $historyHP) {

                  $valueHP = $historyHP->getValue();
                  $valueDateTimeHP = $historyHP->getDatetime();

                  if ($valueDateTimeHP == $valueDateTimeAPI){ // si on est a la meme heure, on multiplie
                    $totalCO2 += floatval($valueHP / 1000 * $valueAPI);
                  }
                }

                if (is_object($cmdConsoHC)) { // que si elle existe bien
                  // on boucle dans toutes les valeurs de l'historique de la cmd HC
                  foreach ($consoHC as $historyHC) {
                    $valueHC = $historyHC->getValue();
                    $valueDateTimeHC = $historyHC->getDatetime();

                    if ($valueDateTimeHC == $valueDateTimeAPI){ // si on est a la meme heure, on multiplie
                      $totalCO2 += floatval($valueHC / 1000 * $valueAPI);
                    }
                  }
                } // fin on a bien une HC

            } // fin on est sur une heure pleine

          } // fin foreach API

          log::add('suiviCO2', 'debug', 'totalCO2 - ELEC : ' . $totalCO2);

        } else { // on est en gaz ou fioul ou autre, on prend la valeur donnée dans la configuration

          $totalConso = 0;

          $gCO2_kwh = str_replace(',', '.', $this->getConfiguration('gCO2_kwh')); // on choppe la valeur donnée par l'utilisateur

          // on recupere la cmd HP
          $cmdConsoHP = $this->getCmd(null, 'consumptionHP');
          if (!is_object($cmdConsoHP)) {
            return array();
          }
          // on boucle dans toutes les valeurs de l'historique de la cmd HP pour recuperer les valeurs et on les additionne
          foreach ($cmdConsoHP->getHistory($startDate, $endDate) as $history) {
            $value = $history->getValue();
            $totalConso += floatval($value / 1000);
          }

          $cmdConsoHC = $this->getCmd(null, 'consumptionHC');
          if (is_object($cmdConsoHC)) { // si elle est définie, on fait les calculs, sinon on passe

            // on boucle dans toutes les valeurs de l'historique de la cmd HC
            foreach ($cmdConsoHC->getHistory($startDate, $endDate) as $history) {
              $value = $history->getValue();
              $totalConso += floatval($value / 1000);
            }
          } // fin si consumptionHC est une cmd valide

          $totalCO2 = $totalConso * $gCO2_kwh; // on multiple par notre taux de gCO2/kWh et on a notre valeur
          log::add('suiviCO2', 'debug', 'totalCO2 - non ELEC : ' . $totalCO2);

        } // fin else : on est pas en type elec

        return $totalCO2;

      }

       public function calculTotauxCo2(){ // appellé par le cron Hourly, pour les calculs de totaux sur le dashboard

  //      $calculstarttime = date('H:i:s');

        $cmdTotalDay = $this->getCmd(null, 'totalCO2jour');
        $cmdTotalWeek = $this->getCmd(null, 'totalCO2semaine');
        $cmdTotalMonth = $this->getCmd(null, 'totalCO2mois');

        if (is_object($cmdTotalDay) && $cmdTotalDay->getIsVisible()){ // si cette commande existe et qu'elle est visible

          $startDate = date('Y-m-d 00:00:00'); // 00:00 ce matin
          $endDate = date('Y-m-d H:i:00'); // now sans les minutes

          $totalCO2 = $this->calculTotauxCo2Periode($startDate, $endDate);

          $cmdTotalDay->setCollectDate($datetime);
          $cmdTotalDay->event($totalCO2);

        } // fin calcul jour

        if ($cmdTotalWeek->getIsVisible()){

          $startDate = date('Y-m-d 00:00:00', strtotime('Monday ' . date('Y-m-d H:00:00'))); // 00:00 lundi de cette semaine
          $endDate = date('Y-m-d H:i:00');

          $totalCO2 = $this->calculTotauxCo2Periode($startDate, $endDate);

          $cmdTotalWeek->setCollectDate($datetime);
          $cmdTotalWeek->event($totalCO2);

        }

        if ($cmdTotalMonth->getIsVisible()){

          $startDate = date('Y-m-01 00:00:00');
          $endDate = date('Y-m-d H:i:00');

          $totalCO2 = $this->calculTotauxCo2Periode($startDate, $endDate);

          $cmdTotalMonth->setCollectDate($datetime);
          $cmdTotalMonth->event($totalCO2);
        }

    //    log::add('suiviCO2', 'debug', 'Calculs totaux CO2 pour le dashboard, start à ' . $calculstarttime . ' fin à : ' . date('H:i:s')); // ca va, tout en moins d'1s, acceptable

       }

      public function getAndRecordHistoriqueConso($_startDate, $_endDate){ // fct appelée par l'AJAX

        if($this->getConfiguration('index_HC')!=''){
          $_typeConso = array('HP', 'HC');
        } else {
          $_typeConso = array('HP');
        }

        foreach ($_typeConso as $_type) {

      //    $nbcalculs = 0;
          $nbdataimportees = 0;
          $calculstarttime = date('H:i:s');

          // on recupere la cmd contenant l'index
          $index_cmd_id = $this->getConfiguration('index_' . $_type); //on va chercher l'id de la CMD contenant index_HP ou HC via la conf utilisateur, format #10#

          $cmdIndex = cmd::byId(str_replace('#', '', $index_cmd_id)); // on vire les # et on prend cette commande (d'un autre objet !)
          if (!is_object($cmdIndex)) {
            log::add('suiviCO2', 'warning', 'Pas de commande dans le champs ' . $_type . ' ou ' . $index_cmd_id . ' n est pas une commande valide - Fin de l import');
            return array();
          }

          $cmd = $this->getCmd(null, 'consumption' . $_type); // on prend la commande dans laquelle on va ecrire notre resultat de calcul conso
          if (is_object($cmd)) {

            $historyIndex = $cmdIndex->getHistory($_startDate, $_endDate); // on choppe l'historique de notre index aux dates données par l'user

            // on boucle dans toutes les valeurs de l'historique de la cmd index
            foreach ($historyIndex as $history) {

              $valueDateTime = $history->getDatetime();
              $value = $history->getValue();

              //on ne veux enregistrer que les heures piles
              if(date('i', strtotime($valueDateTime)) == "00"){ // on extrait le champ min et on verifie qu'il vaut 00

        //        log::add('suiviCO2', 'debug', 'Voici notre historique heures piles :' . $valueDateTime . ' : ' . $value);

                foreach ($historyIndex as $key => $history_prev) { // on cherche dans le meme historique si on a une donnée 1h avant

                  $valueDateTime_prev = $history_prev->getDatetime();
                  $value_prev = $history_prev->getValue();

                  $datetimecherchee = date('Y-m-d H:i:00', strtotime('-1 hour ' . $valueDateTime));

              //    $nbcalculs++;

                  if ($valueDateTime_prev == $datetimecherchee){

                    //si on a un coef declaré et numerique, on va l'utiliser
                    $coef_thermique = $this->getConfiguration('coef_thermique');
                    if($coef_thermique != '' && is_numeric($coef_thermique)){
                      $conso = round($value*$coef_thermique - $value_prev*$coef_thermique, 0);
              //        log::add('suiviCO2', 'debug', 'coef_thermique :' . $coef_thermique . ' - conso calculée : ' . $conso);
                    } else {
                      $conso = round($value - $value_prev, 0);
                    }

                    $cmd->addHistoryValue($conso, $valueDateTime);
                //    log::add('suiviCO2', 'debug', 'Conso historique enregistrée en DB :' . $valueDateTime . ' : ' . $conso);

                    $nbdataimportees++;

                    unset($historyIndex[$key]); // on vire la valeur 1h avant du tableau pour éviter de boucler inutilement dedans apres. Super optimisation de temps de calcul ca, divise par 8 le temps de calcul !
                    break; // on a trouvé notre valeur 1h avant, donc on quitte la boucle. Bon gain de performance aussi. Permet de diviser le temps de calcul par 2 !
                    // avant les 2 optimisations ci-dessus, on avait 50s pour 1 mois de data, now : 3s !! ;-)

                  }

                } // fin foreach history_prev

              } // fin if heure pile

            } // fin foreach history

          } // fin if cmd

          log::add('suiviCO2', 'debug', 'Import data ' . $_type . ' de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s') . ', nb data importées : ' . $nbdataimportees);

        } // fin foreach HP puis HC

      } // fin fct

      public function getAndRecordDataCo2($_nbRecordsAPI = 220, $_nbRecordsATraiterDB = 14, $_eqLogic_id = NULL){ // fct appellée soit par l'AJAX, soit par le crouHourly

        /* *************** Infos sur l'API opendata.reseaux-energies.fr
        96 données par jours
        on est obligé d'en demander bcp car les champs vide du lendemain voir surlendemain sont crées dans le json
        actualisation toutes les heures à heure pile, à ce moment là l'API ne repond pas, il faut donc decaller sa requete (1min de decallage demandé ici)
        lors de l'actualisation, on recoit d'un coup les 4 données précédentes, donc a 21:00, on recoit 20:00, 20:15, 20:30 et 20:45
        */

        //on va chercher les $_nbRecordsAPI dernieres data.
        $url = 'https://opendata.reseaux-energies.fr/api/records/1.0/search/?dataset=eco2mix-national-tr&rows=' . $_nbRecordsAPI . '&sort=date_heure';
        log::add('suiviCO2', 'debug', 'Appel API CO2, URL : ' . $url);

        $request_http = new com_http($url);
        $content = $request_http->exec(30);

        if ($content === false) {
          log::add('suiviCO2', 'error', 'Erreur lors de l appel API CO2, URL : ' . $url);
          return;
        }

        //on decode le retour de l'API pour en faire un tableau
        $json = json_decode($content, true);

        //on va chercher dans le tableau les infos qui nous interessent (les 'records')
        $apirecords = $json['records'];

        $nbRecordsTraites = 0;

        foreach ($apirecords as $position => $record) {// pour chaque position dans 'records' on prend le noeud et on cherche taux_co2
          if (isset($record['fields']['taux_co2'])) {// quand on a un noeud avec le taux_co2, on choppe les infos

            $record_date = $record['fields']['date']; // recu au format Y-m-d, ce qui demande Jeedom, donc c'est parfait
            $record_time = $record['fields']['heure']; // recu au format H:i
            $record_tauxco2 = $record['fields']['taux_co2'];

            /************ Mise à jour de la derniere valeur dispo ************/

            // on cherche la valeur de l'heure courante -15min (parce que c'est la derniere dispo via l'API...)
            $datetimecherchee = date('Y-m-d H:i', strtotime('-15 min ' . date('Y-m-d H:00')));
            $datetimerecord = $record_date . ' ' . $record_time;
      //      log::add('suiviCO2', 'debug', 'datetimecherchee : ' . $datetimecherchee . 'datetimerecord : ' . $datetimerecord);

            if($datetimecherchee == $datetimerecord){

      //        log::add('suiviCO2', 'debug', 'Trouvee, on la garde : ' . $record_tauxco2);

              //pour chaque equipement declaré par l'utilisateur, on met a jour la cmd
              foreach (self::byType('suiviCO2',true) as $suiviCO2) {
                $cmd = $suiviCO2->getCmd(null, 'co2kwhfromApi');
                if (is_object($cmd)) {
                  $cmd->setCollectDate($datetime);
                  $cmd->event($record_tauxco2);
                  log::add('suiviCO2', 'debug', 'co2kwhfromApi : ' . $record_tauxco2);
                }
              }
            }//*/

            /************ Enregistrement des datas en base de donnee ************/

            //on ne veux enregistrer que les heures piles (échantillonnage malheuresement sinon la fonction historisation de jeedom fait n importe quoi...)
     //       if(date('i', strtotime($record_time)) == "00"){ // on extrait le champ min et on verifie qu'il vaut 00
          //    log::add('suiviCO2', 'debug', 'On a trouvé une heure pile, il est : ' . $record_time);

              // pour pas traiter inutilement, on coupe apres x valeur API avec un taux_co2 traitées
              $nbRecordsTraites++;
              if ($nbRecordsTraites > $_nbRecordsATraiterDB){
          //      log::add('suiviCO2', 'debug', 'Quota de: ' . $_nbRecordsATraiterDB . ' atteint, on break la boucle');
                break;
              }

              //pour chaque equipement declaré par l'utilisateur
              foreach (self::byType('suiviCO2',true) as $suiviCO2) {

                // on regarde si on a limité à un equipement ou s'il faut tous les traiter (selon que cette fct est appelée par le cron ou par la commande d'historisation)
                $suiviCO2_id = $suiviCO2->getId();
                if(!isset($_eqLogic_id) || $_eqLogic_id == $suiviCO2_id){

       //           log::add('suiviCO2', 'debug', 'Id de l équipement dans lequel on va enregistrer : ' . $suiviCO2_id);

                  // on enregistre les infos dans la DB history avec la date donnéee dans le json
                  // pas besoin de verifier que la valeur existe pas encore, la DB gere unicité paire datetime/cmd
                  $cmd = $suiviCO2->getCmd(null, 'co2kwhfromApi');
                  if (is_object($cmd)) {
                    $cmd->addHistoryValue($record_tauxco2, $record_date . ' ' . $record_time . ':00');
                    log::add('suiviCO2', 'debug', 'eqLogic_id : ' . $suiviCO2_id . ' - Taux_Co2 : ' . $record_tauxco2 . ' à : ' . $record_date . ' ' . $record_time . ':00');
                  }

                } //fin boucle verification on veut ecrire les datas pour cet equipement
              } // fin foreach equipement
         //   } // fin on a trouvé une heure entiere
          } // fin if on est dans un noeud avec un taux co2
        } //fin boucle dans toutes les datas recuperées
      } //fin fonction

      public function getAndRecordDataCo2Definitives($_nbRecordsAPI = 3000, $_date = ''){ // fct appellée par l'AJAX

        $nbdataimportees = 0;
        $calculstarttime = date('H:i:s');

        //on va chercher les $_nbRecordsAPI dernieres data.
        $url = 'https://opendata.reseaux-energies.fr/api/records/1.0/search/?dataset=eco2mix-national-cons-def&rows=' . $_nbRecordsAPI . '&sort=date_heure&refine.date_heure=' . $_date;
        log::add('suiviCO2', 'debug', 'Appel API CO2, URL : ' . $url);

        $request_http = new com_http($url);
        $content = $request_http->exec(30);

        if ($content === false) {
          log::add('suiviCO2', 'error', 'Erreur lors de l appel API CO2, URL : ' . $url);
          return;
        }

        //on decode le retour de l'API pour en faire un tableau
        $json = json_decode($content, true);

        //on va chercher dans le tableau les infos qui nous interessent (les 'records')
        $apirecords = $json['records'];

        $nbRecordsTraites = 0;

        foreach ($apirecords as $position => $record) {// pour chaque position dans 'records' on prend le noeud et on cherche taux_co2
          if (isset($record['fields']['taux_co2'])) {// quand on a un noeud avec le taux_co2, on choppe les infos

            $record_date = $record['fields']['date']; // recu au format Y-m-d, ce qui demande Jeedom, donc c'est parfait
            $record_time = $record['fields']['heure']; // recu au format H:i
            $record_tauxco2 = $record['fields']['taux_co2'];

            /************ Enregistrement des datas en base de donnee ************/

         // on enregistre les infos dans la DB history avec la date donnéee dans le json
         // pas besoin de verifier que la valeur existe pas encore, la DB gere unicité paire datetime/cmd
            $cmd = $this->getCmd(null, 'co2kwhfromApi');
            if (is_object($cmd)) {
              $cmd->addHistoryValue($record_tauxco2, $record_date . ' ' . $record_time . ':00');
              log::add('suiviCO2', 'debug', 'eqLogic_id : ' . $suiviCO2_id . ' - Taux_Co2 : ' . $record_tauxco2 . ' à : ' . $record_date . ' ' . $record_time . ':00');
              $nbdataimportees++;
            }

          } // fin if on est dans un noeud avec un taux co2
        } //fin boucle dans toutes les datas recuperées

        log::add('suiviCO2', 'debug', 'Import data, date : ' . $_date . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s') . ', nb data importées : ' . $nbdataimportees);
      } //fin fonction

      // Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {
        $datetime = date('Y-m-d H:i:00');

        log::add('suiviCO2', 'debug', '#################### CRON Hourly à ' . $datetime . ' ###################');

        //pour chaque equipement declaré par l'utilisateur
        foreach (self::byType('suiviCO2',true) as $suiviCO2) {

          /* Traitement HP */
          $suiviCO2->calculConso('HP');

          /* Traitement HC */
          if($suiviCO2->getConfiguration('index_HC')!=''){ //si on a un index HC
            $suiviCO2->calculConso('HC');
          }

          /* Calculs des totaux pour le dashboard */
          $suiviCO2->calculTotauxCo2();

        } // fin foreach equipement

        //appel de l'api et stock des données en base, si on est en type elec
        if($suiviCO2->getConfiguration('conso_type') == 'elec'){ //si on est en conso de type elec
          sleep(60);//attend 1 min, si execution à l'heure pile on recoit pas les datas (due a la mise à jour de l'API)
          self::getAndRecordDataCo2();
        }

      } //fin fonction cron

    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    // fonction appellée via l'AJAX pour l'affichage du panel
    public function getGraphsDatasSuiviCO2($_startDate = null, $_endDate = null) {

      $return = array(
        'consoHP' => array(), // conso kWh HP
        'consoHC' => array(), // conso kWh HP
        'CO2API' => array(), // emissions CO2 par kWh en France
        'consoCO2' => array(), // conso CO2 de la maison : (HP+HC)*CO2API
        'total' => array('co2' => 0, 'consokWh' => 0, 'cost' => 0),
        'cost' => array(
            'HP' => array(),
            'HC' => array(),
            'Abo' => array(),
        ),
      );

      /******** Aller chercher et formater les infos de cout elec ou autre *********/
      $costAbo = str_replace(',', '.', $this->getConfiguration('costAbo')); // si on a une , au lieu d'un . on va la remplacer
      $costHP = str_replace(',', '.', $this->getConfiguration('costHP'));
      $costHC = str_replace(',', '.', $this->getConfiguration('costHC'));

   //   log::add('suiviCO2', 'debug', 'Config Abo lue : ' . $costAbo . ' - HP : ' . $costHP . ' - HC : ' . $costHC);

      /********************* Calculs pour conso HP et cost HP ********************/
      // on recupere la cmd HP
      $cmdConsoHP = $this->getCmd(null, 'consumptionHP');
      if (!is_object($cmdConsoHP)) {
        return array();
      }

      $calculstarttime = date('H:i:s'); // pour debug only

      // on boucle dans toutes les valeurs de l'historique de la cmd HP
      foreach ($cmdConsoHP->getHistory($_startDate, $_endDate) as $history) {

        $valueDateTime = $history->getDatetime();
        $value = $history->getValue();

        // on retourne plusieurs tableaux avec en index la datetime et en valeurs le couple timestamp, valeur
        if($value != 0){
          $return['consoHP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000));
          $return['cost']['HP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000 * $costHP));
          $nbJourCeMois = date('t', strtotime($valueDateTime));
          $costAboHeure = $costAbo / $nbJourCeMois / 24;
   //       log::add('suiviCO2', 'debug', 'Nb jours ci mois ci : ' . $nbJourCeMois . ' - Cout abo par heure : ' . $costAboHeure);
          $return['cost']['Abo'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($costAboHeure)); //pour toutes les dates on balance le cout de l'abo a l'heure.
          $return['total']['consokWh'] += floatval($value / 1000);
          $return['total']['cost'] += floatval($value / 1000 * $costHP + $costAboHeure); // on ajoute le cout de l'abo toutes les HP, on l'ajoutera ci-dessous toutes les HC puis on le soustraira aux heures ayants du HP et HC pour virer les doublons
        }

      }

  //    log::add('suiviCO2', 'debug', 'Calculs pour conso HP et cost HP de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));

       /********************* Calculs pour conso et cost HC ********************/

       // on recupere la cmd HC
       $cmdConsoHC = $this->getCmd(null, 'consumptionHC');
       if (is_object($cmdConsoHC)) { // si elle est définie, on fait les calculs, sinon on passe

  //      $calculstarttime = date('H:i:s');

         // on boucle dans toutes les valeurs de l'historique de la cmd HC
         foreach ($cmdConsoHC->getHistory($_startDate, $_endDate) as $history) {

            $valueDateTime = $history->getDatetime();
            $value = $history->getValue();

            // on retourne un tableau avec en index la datetime et en valeurs le couple timestamp, valeur
            if($value != 0){
              $return['consoHC'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000));
              $return['cost']['HC'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000 * $costHC));
              $nbJourCeMois = date('t', strtotime($valueDateTime));
              $costAboHeure = $costAbo / $nbJourCeMois / 24;
       //       log::add('suiviCO2', 'debug', 'Nb jours ci mois ci : ' . $nbJourCeMois . ' - Cout abo par heure : ' . $costAboHeure);
              $return['cost']['Abo'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($costAboHeure)); // s'il était deja dedans, on l'écrase, donc pas de doublons !
              $return['total']['consokWh'] += floatval($value / 1000);
              $return['total']['cost'] += floatval($value / 1000 * $costHC + $costAboHeure);
            }
         }
       } // fin si consumptionHC est une cmd valide

    //   log::add('suiviCO2', 'debug', 'Calculs pour conso HC et cost HC de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));

        /********************* Calculs pour les valeurs CO2 from API ********************/

        if($this->getConfiguration('conso_type') == 'elec'){ //si on est en conso de type elec, on va chercher les infos de l'API pour afficher le graph, sinon on passe

          // on recupere la cmd
          $cmdCO2API = $this->getCmd(null, 'co2kwhfromApi');
          if (!is_object($cmdCO2API)) {
            return array();
          }

    //      $calculstarttime = date('H:i:s');

          // on boucle dans toutes les valeurs de l'historique de la cmd
          foreach ($cmdCO2API->getHistory($_startDate, $_endDate) as $history) {

            $valueDateTime = $history->getDatetime();
            $value = $history->getValue();

            // on retourne un tableau avec en index la datetime et en valeurs le couple timestamp, valeur
            if($value != 0){
              $return['CO2API'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value));
            }
          }
        }

  //      log::add('suiviCO2', 'debug', 'Calculs pour les valeurs CO2 from API de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));

        /********************* Calculs pour la conso CO2 selon conso HP et HC ********************/

  /*    $return['CO2API']
        $return['consoHP']
        $return['consoHC']


*/
  //      $calculstarttime = date('H:i:s');

    //    log::add('suiviCO2', 'debug', '###################### Calculs pour la conso CO2 ##########################');
        if($this->getConfiguration('conso_type') == 'elec'){ //si on est en conso de type elec, on va utiliser les infos de l'API

          // pour chacune des valeur de l'API CO2 on cherche la conso associée pour la multiplier
          foreach ($return['CO2API'] as $co2API) {

            foreach ($return['consoHP'] as $consoHP) { // on tourne maintenant dans les HP
              if($co2API[0] == $consoHP[0]){ // on est au meme timestamp sur la boucle CO2API et ConsoHP

                $value = $consoHP[1] * $co2API[1];
                $return['consoCO2'][$co2API[0]] = array($co2API[0], $value);
                $return['total']['co2'] += floatval($value / 1000);
            //    log::add('suiviCO2', 'debug', 'Ds la boucle API puis boucle Conso HP : ' . date('Y-m-d H:i:00', $consoHP[0]/1000) . ' valeur Conso HP : ' . $consoHP[1] . ' resultat co2*conso : ' . $value);
                break;
              }
            }

            if($this->getConfiguration('index_HC')!=''){ //si on a une commande HC definie
              // on ajoute toutes les datas HC
              foreach ($return['consoHC'] as $consoHC) {
                if($co2API[0] == $consoHC[0]){ // on est au meme timestamp sur la boucle CO2API et ConsoHC

                  $value = $consoHC[1] * $co2API[1];

                  if(!isset($return['consoCO2'][$consoHC[0]])){ // si c'est vide, on a donc que des HC a ce timestamp

             //       log::add('suiviCO2', 'debug', 'Ds la boucle API puis boucle Conso   HC - uniquement du HC now : ' . date('Y-m-d H:i:00', $consoHC[0]/1000) . ' valeur conso HC : ' . $consoHC[1] . ' resultat co2*conso : ' . $value);

                    $return['consoCO2'][$co2API[0]] = array($co2API[0], $value);

                  } else { // on avait deja une valeur, il faut ajouter la nouvelle

                    $return['consoCO2'][$co2API[0]] = array($co2API[0], $return['consoCO2'][$co2API[0]][1] + $value);
                    $return['total']['cost'] -= floatval($costAboHeure); // et on retire 1 fois l'abo cout heure qui a deja ete compté pour les HP ET les HC
               //     log::add('suiviCO2', 'debug', 'Ds la boucle API puis boucle Conso   HC - avec deja du HP present : ' . date('Y-m-d H:i:00', $consoHC[0]/1000) . ' valeur conso HC : ' . $consoHC[1] . ' resultat co2*conso : ' . $value . ' total : ' . $return['consoCO2'][$co2API[0]][1] + $value);
                  }

                  $return['total']['co2'] += floatval($value / 1000);
                  break;
                }
              } // fin foreach HC
            } // fin if on a des HC
          } // fin foreach API co2
        } else { // on est en gaz ou fioul ou autre, on prend la valeur donnée dans la configuration

          $gCO2_kwh = str_replace(',', '.', $this->getConfiguration('gCO2_kwh')); // on choppe la valeur donnée par l'utilisateur

          foreach ($return['consoHP'] as $consoHP) { // on tourne dans les HP

            $value = $consoHP[1] * $gCO2_kwh;
            $return['consoCO2'][$consoHP[0]] = array($consoHP[0], $value);
            $return['total']['co2'] += floatval($value / 1000);
      //      log::add('suiviCO2', 'debug', 'Ds la boucle Conso HP : ' . date('Y-m-d H:i:00', $consoHP[0]/1000) . ' valeur Conso HP : ' . $consoHP[1] . ' resultat co2*conso : ' . $value);

          }

          if($this->getConfiguration('index_HC')!=''){ //si on a une commande HC definie
            // on ajoute toutes les datas HC
            foreach ($return['consoHC'] as $consoHC) {

              $value = $consoHC[1] * $gCO2_kwh;

              if(!isset($return['consoCO2'][$consoHC[0]])){ // si c'est vide, on a donc que des HC a ce timestamp

      //         log::add('suiviCO2', 'debug', 'Ds la boucle Conso   HC - uniquement du HC now : ' . date('Y-m-d H:i:00', $consoHC[0]/1000) . ' valeur conso HC : ' . $consoHC[1] . ' resultat co2*conso : ' . $value);

                $return['consoCO2'][$consoHC[0]] = array($consoHC[0], $value);

              } else { // on avait deja une valeur, il faut ajouter la nouvelle et deduire le doublons de l'abo dans le cout global

                $return['consoCO2'][$consoHC[0]] = array($consoHC[0], $return['consoCO2'][$consoHC[0]][1] + $value);
                $return['total']['cost'] -= floatval($costAboHeure); // et on retire 1 fois l'abo cout heure qui a deja ete compté pour les HP ET les HC
      //          log::add('suiviCO2', 'debug', 'Ds la boucle Conso   HC - avec deja du HP present : ' . date('Y-m-d H:i:00', $consoHC[0]/1000) . ' valeur conso HC : ' . $consoHC[1] . ' resultat co2*conso : ' . $value . ' total : ' . $return['consoCO2'][$consoHC[0]][1] + $value);
              }

              $return['total']['co2'] += floatval($value / 1000);

            } // fin foreach HC
          } // fin if on a des HC
        } // fin else, on est pas en type "elec"


  //      log::add('suiviCO2', 'debug', 'Calculs pour la conso CO2 selon conso HP et HC de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));

        /********************* On formate tous nos tableaux avant de les renvoyer ********************/

  //      $calculstarttime = date('H:i:s');

        if (isset($return['consoHP'])) {
          sort($return['consoHP']);
          $return['consoHP'] = array_values($return['consoHP']);
        }
        if (isset($return['cost']['HP'])) {
          sort($return['cost']['HP']);
          $return['cost']['HP'] = array_values($return['cost']['HP']);
        }
        if (isset($return['CO2API'])) {
          sort($return['CO2API']);
          $return['CO2API'] = array_values($return['CO2API']);
        }
        if (isset($return['consoCO2'])) {
          sort($return['consoCO2']);
          $return['consoCO2'] = array_values($return['consoCO2']);
        }
        if (isset($return['consoHC'])) {
          sort($return['consoHC']);
          $return['consoHC'] = array_values($return['consoHC']);
        }
        if (isset($return['cost']['HC'])) {
          sort($return['cost']['HC']);
          $return['cost']['HC'] = array_values($return['cost']['HC']);
        }
         if (isset($return['cost']['Abo'])) {
          sort($return['cost']['Abo']);
          $return['cost']['Abo'] = array_values($return['cost']['Abo']);
        }

        $return['total']['cost'] = round($return['total']['cost'], 2);
        $return['total']['co2'] = round($return['total']['co2'], 2);
        $return['total']['consokWh'] = round($return['total']['consokWh'], 2);

   //     log::add('suiviCO2', 'debug', 'Formatage final de ' . $_startDate . ' à ' . $_endDate . ', start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));
        log::add('suiviCO2', 'debug', 'Affichage du panel de ' . $_startDate . ' à ' . $_endDate . ', temps de calcul affichage - start à ' . $calculstarttime . ' fin à : ' . date('H:i:s'));


        return $return;
    }


    public function preInsert() {

    }

    public function postInsert() {

    }

    public function preSave() {

    }

    public function postSave() {

      // creation des cmd à la sauvegarde de l'équipement

      $cmd = $this->getCmd(null, 'consumptionHP');
      if (!is_object($cmd)) {
        //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('consumptionHP');
    //    $cmd->setTemplate('dashboard', 'tile');
        $cmd->setIsVisible(0);
        $cmd->setEqLogic_id($this->getId());
      }
      //ici apres, jeedom va utiliser ces infos a chaque fois que l'equipement est sauvegardé, si l'utilisateur le change, ces valeurs là re-écraseront les choix utilisateurs.
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'max');
      $cmd->setConfiguration('historizeRound', 0);
      $cmd->setName(__('Consommation HP', __FILE__));
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('Wh');
      $cmd->save();

      if($this->getConfiguration('index_HC')!=''){ //si on a une commande HC definie

        $cmd = $this->getCmd(null, 'consumptionHC');
        if (!is_object($cmd)) {
          $cmd = new suiviCO2Cmd();
          $cmd->setLogicalId('consumptionHC');
    //      $cmd->setTemplate('dashboard', 'tile');
          $cmd->setIsVisible(0);
          $cmd->setEqLogic_id($this->getId());
        }
        $cmd->setIsHistorized(1);
        $cmd->setConfiguration('historizeMode', 'max');
        $cmd->setConfiguration('historizeRound', 0);
        $cmd->setName(__('Consommation HC', __FILE__));
        $cmd->setType('info');
        $cmd->setSubType('numeric');
        $cmd->setUnite('Wh');
        $cmd->save();

      } else {
        log::add('suiviCO2', 'warning', 'Pas de commande dans le champs HC');
      }

      if($this->getConfiguration('conso_type')== 'elec'){ //si on est en conso de type elec
        // cmd qui va historiser les valeurs de l'API
        $cmd = $this->getCmd(null, 'co2kwhfromApi');
        if (!is_object($cmd)) {
          $cmd = new suiviCO2Cmd();
          $cmd->setLogicalId('co2kwhfromApi');
      //    $cmd->setTemplate('dashboard', 'tile');
          $cmd->setIsVisible(1);
        }
        $cmd->setIsHistorized(1);
        $cmd->setConfiguration('historizeMode', 'avg'); //max, min, avg, none. a 16:00 il min, max ou avg sur toutes les valeurs de 16:xx. None : il supprime juste les doublons aux dates successives
        $cmd->setConfiguration('historizeRound', 2);
        $cmd->setName(__('gCO2/kWh électricité', __FILE__));
        $cmd->setEqLogic_id($this->getId());
        $cmd->setType('info');
        $cmd->setSubType('numeric');
        $cmd->setUnite('gCO2');
        $cmd->save();
      } else { // sinon et si la commande existait deja, on va la supprimer
          $cmd = $this->getCmd(null, 'co2kwhfromApi');
          if (is_object($cmd)) {
            $cmd->remove();
          }
      }

      $cmd = $this->getCmd(null, 'totalCO2jour');
      if (!is_object($cmd)) {
        //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('totalCO2jour');
    //    $cmd->setTemplate('dashboard', 'tile');
        $cmd->setIsVisible(1);
        $cmd->setEqLogic_id($this->getId());
        $cmd->setName(__('Total CO2 jour', __FILE__));
      }
      $cmd->setIsHistorized(0);
      $cmd->setOrder(0);
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->save();

      $cmd = $this->getCmd(null, 'totalCO2semaine');
      if (!is_object($cmd)) {
        //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('totalCO2semaine');
    //    $cmd->setTemplate('dashboard', 'tile');
        $cmd->setIsVisible(1);
        $cmd->setEqLogic_id($this->getId());
        $cmd->setName(__('Total CO2 semaine', __FILE__));
      }
      $cmd->setIsHistorized(0);
      $cmd->setOrder(1);
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->save();

      $cmd = $this->getCmd(null, 'totalCO2mois');
      if (!is_object($cmd)) {
        //ce qui est ici est declaré à la 1ere creation de l'objet seulement et donc peut etre changé par l'utilisateur par la suite
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('totalCO2mois');
    //    $cmd->setTemplate('dashboard', 'tile');
        $cmd->setIsVisible(1);
        $cmd->setEqLogic_id($this->getId());
        $cmd->setName(__('Total CO2 mois', __FILE__));
      }
      $cmd->setIsHistorized(0);
      $cmd->setOrder(2);
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->save();

  } //fin postSave

  // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
  // ici on vérifie la présence de nos champs de config obligatoire
  public function preUpdate() {

      if ($this->getConfiguration('index_HP') == '') {
          throw new Exception(__('Le champs Index fixe ou HP ne peut être vide',__FILE__));
      }

      if ($this->getConfiguration('conso_type') == '') {
          throw new Exception(__('Merci de choisir un type d\'énergie',__FILE__));
      }

  }

    public function postUpdate() {

    }

    public function preRemove() {

    }

    public function postRemove() {

    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class suiviCO2Cmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {

    }

    /*     * **********************Getteur Setteur*************************** */
}


