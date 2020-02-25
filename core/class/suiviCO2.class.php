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

        */

/*          ce morceau de code va chercher tout l'historique de la commande et le loggue
            $previous = $cmd->getHistory();
            foreach ($previous as $value) {
              log::add('suiviCO2', 'debug', ' previous : ' . $value->getValue());
            }*/

//*/

class suiviCO2 extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    //*
    // * Fonction exécutée automatiquement toutes les minutes par Jeedom
 /*     public static function cron() {

            $test = date('Y-m-d H:i', strtotime('-15 min ' . date('Y-m-d H:00')));
            log::add('suiviCO2', 'debug', '15 min avant lheure courante pleine ? : ' . $test);

      }
     //*/


      public function calculConso($_type = 'HP', $suiviCO2){

          //on va chercher l'info index_HP ou HC via la conf utilisateur
          $index = jeedom::evaluateExpression($suiviCO2->getConfiguration('index_' . $_type));

          //on recupere la precedente valeur stockée, selon HP ou HC
          $lastValue = $suiviCO2->getConfiguration('lastValue' . $_type);
          //on sauvegarde la valeur actuelle pour le prochain tour
          $suiviCO2->setConfiguration('lastValue' . $_type, $index);
          $suiviCO2->save();

     //     log::add('suiviCO2', 'debug', 'lastIndex' . $_type . ' : ' . $lastValue . ' Index'  . $_type . ' : ' . $index);

          //on calcule la consommation entre les 2 derniers index
          $consumption = $index - $lastValue;

          //si cette consommation est >0, on va la stocker en base - NON, il faut stocker les 0 sinon l'archivage de l'historique fait n'importe quoi... dommage de stocker des 0... // TODO a ameliorer...
  //        if ($consumptionHP > 0) {
            $cmd = $suiviCO2->getCmd(null, 'consumption' . $_type);
            if (is_object($cmd)) {
              $cmd->setCollectDate($datetime);
              log::add('suiviCO2', 'debug', 'eqLogic_id : ' . $suiviCO2->getId() . ' - Index now ' . $_type . ' : ' . $index . ' - Prev Index '  . $_type . ' : ' . $lastValue . ' = conso ' . $_type . ' (Wh) : ' . $consumption);
              $cmd->event($consumption);
            }
    //      }

      }

      public function getHistoriqueConso($_startDate, $_endDate, $_eqLogic_id){

        // on cherche l'eqLogic de cet id
        $eqLogic = eqLogic::byId($_eqLogic_id);

        //on va chercher la valeur actuelle index_HP ou HC via la conf utilisateur
     //   $index = jeedom::evaluateExpression($eqLogic->getConfiguration('index_' . $_type));

        $_type = 'HP';

        //on va chercher l'id de la CMD contenant index_HP ou HC via la conf utilisateur, format #10#
        $index_cmd_id = $eqLogic->getConfiguration('index_' . $_type);

   //     $cmdIndex_id = str_replace("#", "", $indexCMDlu); // on vire les #, on a maintenant l'ID d'une commande d'un autre objet

        $cmdIndexHP = cmd::byId(str_replace('#', '', $index_cmd_id));

        // on recupere la cmd HP
   //     $cmdConsoHP = $eqLogic->getCmd(null, 'consumptionHP');
        if (!is_object($cmdIndexHP)) {
          return array();
        }

        // on boucle dans toutes les valeurs de l'historique de la cmd index HP
        foreach ($cmdIndexHP->getHistory($_startDate, $_endDate) as $history) {

          $valueDateTime = $history->getDatetime();
          $value = $history->getValue();

          // on retourne plusieurs tableaux avec en index la datetime et en valeurs le couple timestamp, valeur
          if($value != 0){
           $return['consoHP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000));
           $return['cost']['HP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000 * $costHP));
           $return['cost']['Abo'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($costAbo / 30.5 / 24)); //pour toutes les dates on balance le cout de l'abo a l'heure // TODO : ameliorer ce calcul tout pourri ! Il faudrait boucler dans toutes les heures entre startdate et enddate et ajouter une valeur selon le nombre de jours dans le mois...
           $return['total']['consokWh'] += floatval($value / 1000);
           $return['total']['cost'] += floatval($value / 1000 * $costHP); // TODO ajouter le cout de l'abo sans faire de doublon pour les heures qui ont du HP et du HC
          }

        } //*/


        log::add('suiviCO2', 'debug', 'Bien arrivé jusqua la class, cmdIndex_id : ' . $cmdIndex_id);


      }

      public function getAndRecordDataCo2($_nbRecordsAPI = 220, $_nbRecordsATraiterDB = 10, $_eqLogic_id = NULL){

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
          log::add('suiviCO2', 'erreur', 'Erreur lors de l appel API CO2, URL : ' . $url);
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

            /************ Enregistrement des datas heures fixe en base de donnee ************/

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

      public static function cronHourly() {
        $datetime = date('Y-m-d H:i:00');

        log::add('suiviCO2', 'debug', '#################### CRON Hourly à ' . $datetime . ' ###################');

        //pour chaque equipement declaré par l'utilisateur
        foreach (self::byType('suiviCO2',true) as $suiviCO2) {

          /* Traitement HP */
          $suiviCO2->calculConso('HP', $suiviCO2);

          /* Traitement HC */
          if($suiviCO2->getConfiguration('index_HC')!=''){ //si on a un index HC
            $suiviCO2->calculConso('HC', $suiviCO2);

          }

        } // fin foreach equipement

        //appel de l'api et stock des données en base
        sleep(60);//attend 1 min, si execution à l'heure pile on recoit pas les datas (due a la mise à jour de l'API)
        self::getAndRecordDataCo2();

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

    /*ce morceau de code va chercher tout l'historique de la commande et le loggue
            $previous = $cmd->getHistory();
            foreach ($previous as $value) {
              log::add('suiviCO2', 'debug', ' previous : ' . $value->getValue());*/

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

      /******** Aller chercher et formater les infos de cout EDF *********/
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

      // on boucle dans toutes les valeurs de l'historique de la cmd HP
      foreach ($cmdConsoHP->getHistory($_startDate, $_endDate) as $history) {

        $valueDateTime = $history->getDatetime();
        $value = $history->getValue();

        // on retourne plusieurs tableaux avec en index la datetime et en valeurs le couple timestamp, valeur
        if($value != 0){
         $return['consoHP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000));
         $return['cost']['HP'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000 * $costHP));
         $return['cost']['Abo'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($costAbo / 30.5 / 24)); //pour toutes les dates on balance le cout de l'abo a l'heure // TODO : ameliorer ce calcul tout pourri ! Il faudrait boucler dans toutes les heures entre startdate et enddate et ajouter une valeur selon le nombre de jours dans le mois...
         $return['total']['consokWh'] += floatval($value / 1000);
         $return['total']['cost'] += floatval($value / 1000 * $costHP); // TODO ajouter le cout de l'abo sans faire de doublon pour les heures qui ont du HP et du HC
        }

      }

      if (isset($return['consoHP'])) {
        sort($return['consoHP']);
        $return['consoHP'] = array_values($return['consoHP']);
      }

      if (isset($return['cost']['HP'])) {
        sort($return['cost']['HP']);
        $return['cost']['HP'] = array_values($return['cost']['HP']);
      }

       /********************* Calculs pour conso HC ********************/
       // TODO ne faire que si HC est defini
       // on recupere la cmd HC
       $cmdConsoHC = $this->getCmd(null, 'consumptionHC');
       if (!is_object($cmdConsoHC)) {
         return array();
       }

       // on boucle dans toutes les valeurs de l'historique de la cmd HC
       foreach ($cmdConsoHC->getHistory($_startDate, $_endDate) as $history) {

          $valueDateTime = $history->getDatetime();
          $value = $history->getValue();

          // on retourne un tableau avec en index la datetime et en valeurs le couple timestamp, valeur
          // TODO checker cette histoire de UTC, ca decalle pas tout le bordel ?
          if($value != 0){
            $return['consoHC'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000));
            $return['cost']['HC'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value / 1000 * $costHC));
            $return['cost']['Abo'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($costAbo / 30.5 / 24));
            $return['total']['consokWh'] += floatval($value / 1000);
            $return['total']['cost'] += floatval($value / 1000 * $costHC);
          }
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

        /********************* Calculs pour les valeurs CO2 from API ********************/
        // on recupere la cmd
        $cmdCO2API = $this->getCmd(null, 'co2kwhfromApi');
        if (!is_object($cmdCO2API)) {
          return array();
        }

        // on boucle dans toutes les valeurs de l'historique de la cmd
        foreach ($cmdCO2API->getHistory($_startDate, $_endDate) as $history) {

          $valueDateTime = $history->getDatetime();
          $value = $history->getValue();

          // on retourne un tableau avec en index la datetime et en valeurs le couple timestamp, valeur
         if($value != 0){
           $return['CO2API'][$valueDateTime] = array(floatval(strtotime($valueDateTime . " UTC")) * 1000, floatval($value));
         }

          // on log tout ce petit bordel
       //   log::add('suiviCO2', 'debug', 'Fct consowh dans class.php, $valueDateTime : ' . $valueDateTime . ' - $return[$valueDateTime] : ' . $return[$valueDateTime][0] . ' - ' . $return[$valueDateTime][1]);
        }

        if (isset($return['CO2API'])) {
          sort($return['CO2API']);
          $return['CO2API'] = array_values($return['CO2API']);
        }

        /********************* Calculs pour la conso CO2 selon conso HP et HC ********************/

  /*    $return['CO2API']
        $return['consoHP']
        $return['consoHC']
*/
        // on va commencer par additionner nos datas HP et HC

        // on stocke toutes les datas HP dans un nouveau tableau formaté pour le return
        foreach ($return['consoHP'] as $consoHP) {
          $return['consoCO2'][$consoHP[0]] = $consoHP;
        }

        // on ajoute toutes les datas HC
        foreach ($return['consoHC'] as $consoHC) {

          if(!isset($return['consoCO2'][$consoHC[0]])){
            $return['consoCO2'][$consoHC[0]] = $consoHC;
          } else { // on a deja une valeur, donc il y a a cette date des valeurs HC et HP, on additionne HC avec la valeur deja existante (HP donc)
            $return['consoCO2'][$consoHC[0]] = array($consoHC[0], $return['consoCO2'][$consoHC[0]][1] + $consoHC[1]);
      //      log::add('suiviCO2', 'debug', 'On a un timestamp avec des data HP et HC : ' . $consoHC[0] . ' = ' . date('Y-m-d H:i:00', $consoHC[0]/1000));
          }
        }

        // pour chacune de ces conso on cherche si on a un timestamp identique avec une valeur CO2API, si oui on multiple, si non on vire la valeur de conso qu'on aura pas reussi a multiplier...
 /*       foreach ($return['consoCO2'] as $returnConsoCO2) {
          foreach ($return['CO2API'] as $co2API) {

            if($returnConsoCO2[0] == $co2API[0]){
              log::add('suiviCO2', 'debug', 'On a un timestamp avec de la conso et du CO2 : ' . $co2API[0] . ' = ' . date('Y-m-d H:i:00', $co2API[0]/1000));
              $returnConsoCO2 = array($co2API[0], $returnConsoCO2[1] * $co2API[1]);
              log::add('suiviCO2', 'debug', 'apres calculs, on veut retourner : ' . $returnConsoCO2[1] . ' à ' . date('Y-m-d H:i:00', $returnConsoCO2[0]/1000));
            }
          }
              # code...
        }*/

        // pour chacune des valeur de l'API CO2 on cherche la conso associée pour la multiplier
        foreach ($return['CO2API'] as $co2API) {

          if(isset($return['consoCO2'][$co2API[0]])){
            $value = $return['consoCO2'][$co2API[0]][1] * $co2API[1];
            $return['consoCO2'][$co2API[0]] = array($co2API[0], $value);
            $return['total']['co2'] += floatval($value / 1000);

          }

        }

        // TODO : virer les valeurs de conso enregistrées qui n'avaient pas de CO2 api associé

        if (isset($return['consoCO2'])) {
          sort($return['consoCO2']);
          $return['consoCO2'] = array_values($return['consoCO2']);
        }
        /*******************/

        $return['total']['cost'] = round($return['total']['cost'], 2);
        $return['total']['co2'] = round($return['total']['co2'], 2);
        $return['total']['consokWh'] = round($return['total']['consokWh'], 2);

        return $return;
    }

    public function consoco2($_startDate = null, $_endDate = null) {

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
      $cmd->setName(__('gCO2/kWh produit - Fr', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->save();

      // cmd qui va permettre d'afficher et de rendre dispo la derniere valeur de CO2/kWh. A chaque heure fixe on prendra la derniere valeur dispo, donc xx:45 (l'API ne s'actualise que toutes les heures)
  /*    $cmd = $this->getCmd(null, 'co2kwhfromApi_lastvalue');
      if (!is_object($cmd)) {
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('co2kwhfromApi_lastvalue');
    //    $cmd->setTemplate('dashboard', 'tile');
        $cmd->setIsHistorized(0); // on historize pas celle la
        $cmd->setName(__('gCO2 par kWh produit en France', __FILE__));
      }
      $cmd->setIsVisible(1);
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->save();//*/

    }

  // preUpdate ⇒ Méthode appellée avant la mise à jour de votre objet
  // ici on vérifie la présence de nos champs de config obligatoire
  public function preUpdate() {

      if ($this->getConfiguration('index_HP') == '') {
          throw new Exception(__('Le champs Index fixe ou HP ne peut être vide',__FILE__));
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


