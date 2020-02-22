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

class suiviCO2 extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    //*
    // * Fonction exécutée automatiquement toutes les minutes par Jeedom
 /*     public static function cron() {
        //appel de l'api et stock des données en base
        self::getAndRecordDataCo2();
      }
     //*/

/*          ce morceau de code va chercher tout l'historique de la commande et le loggue
            $previous = $cmd->getHistory();
            foreach ($previous as $value) {
              log::add('suiviCO2', 'debug', ' previous : ' . $value->getValue());
            }*/

      public function calculConso($_type = 'HP', $suiviCO2){

          //on va chercher l'info index_HP ou HC via la conf utilisateur
          $index = jeedom::evaluateExpression($suiviCO2->getConfiguration('index_' . $_type));

          //on recupere la precedente valeur stockée, selon HP ou HC
          $lastValue = $suiviCO2->getConfiguration('lastValue' . $_type);
          //on sauvegarde la valeur actuelle pour le prochain tour
          $suiviCO2->setConfiguration('lastValue' . $_type, $index);
          $suiviCO2->save();

          log::add('suiviCO2', 'debug', 'lastIndex' . $_type . ' : ' . $lastValue . ' Index'  . $_type . ' : ' . $index);

          //on calcule la consommation entre les 2 derniers index
          $consumption = $index - $lastValue;

          //si cette consommation est >0, on va la stocker en base - NON, il faut stocker les 0 sinon l'archivage de l'historique fait n'importe quoi... dommage de stocker des 0... // TODO a ameliorer...
  //        if ($consumptionHP > 0) {
            $cmd = $suiviCO2->getCmd(null, 'consumption' . $_type);
            if (is_object($cmd)) {
              $cmd->setCollectDate($datetime);
              log::add('suiviCO2', 'debug', 'conso ' . $_type . ' (Wh) : ' . $consumption);
              $cmd->event($consumption);
            }
    //      }

      }

      public function getAndRecordDataCo2($_nbRecordsAPI = 220, $_nbRecordsATraiterDB = 4, $_eqLogic_id = NULL){

        //on va chercher les _nbRecordsAPI dernieres data. 96 données par jours,
        // on est obligé d'en demander bcp car les champs vide du lendemain voir surlendemain sont crées dans le json
        $url = 'https://opendata.reseaux-energies.fr/api/records/1.0/search/?dataset=eco2mix-national-tr&rows=' . $_nbRecordsAPI . '&sort=date_heure';
      //  $url = 'https://opendata.reseaux-energies.fr/api/records/1.0/search/?dataset=eco2mix-national-tr&rows=100&sort=date_heure&refine.date=' . $date;
        log::add('suiviCO2', 'debug', 'Appel CO2 URL ' . $url);
        $request_http = new com_http($url);
        $content = $request_http->exec(30);
        if ($content === false) {
          log::add('suiviCO2', 'erreur', 'Erreur lors de l appel CO2 URL : ' . $url);
          return;
        }

        //on decode le retour de l'API pour en faire un tableau
        $json = json_decode($content, true);

        //on va chercher dans le tableau les infos qui nous interessent et on les traite
        $apirecords = $json['records'];

        $nbRecordsTraites = 0;

        foreach ($apirecords as $position => $record) {// pour chaque position dans 'records' on prend le noeud et on cherche taux_co2
          if (isset($record['fields']['taux_co2'])) {// quand on a un noeud avec le taux_co2, on choppe les infos

            $record_time = $record['fields']['heure'];

            //on ne veux que les heures piles (échantillonnage sinon la fonction historisation de jeedom fait n importe quoi...)
            //TODO - si on confirme que ca resoud le pb historisation, faire une jolie fonction test au lieu de cette horreur...
            if($record_time == "00:00" || $record_time == "01:00" || $record_time == "02:00" || $record_time == "03:00" || $record_time == "04:00" || $record_time == "05:00" || $record_time == "06:00" || $record_time == "07:00" || $record_time == "08:00" || $record_time == "09:00" || $record_time == "10:00" || $record_time == "11:00" || $record_time == "12:00" || $record_time == "13:00" || $record_time == "14:00" || $record_time == "15:00" || $record_time == "16:00" || $record_time == "17:00" || $record_time == "18:00" || $record_time == "19:00" || $record_time == "20:00" || $record_time == "21:00" || $record_time == "22:00" || $record_time == "23:00"){
              log::add('suiviCO2', 'debug', 'On a trouvé une heure pile, il est : ' . $record_time);


              $record_date = $record['fields']['date'];
              $record_tauxco2 = $record['fields']['taux_co2'];

              // pour pas traiter inutilement des milliers de datas par boucle on coupe quand on a atteint le quota defini en parametre
              $nbRecordsTraites++;
              if ($nbRecordsTraites > $_nbRecordsATraiterDB){
                log::add('suiviCO2', 'debug', 'Quota de: ' . $_nbRecordsATraiterDB . ' atteint, on break la boucle');
                break;
              }

              //pour chaque equipement declaré par l'utilisateur
              foreach (self::byType('suiviCO2',true) as $suiviCO2) {

                // on regarde si on a limité à un equipement ou s'il faut tous les traiter
                $suiviCO2_id = $suiviCO2->getId();
                if(!isset($_eqLogic_id) || $_eqLogic_id == $suiviCO2_id){

                  log::add('suiviCO2', 'debug', 'Id de l équipement dans lequel on va enregistrer : ' . $suiviCO2_id);

                  // on enregistre les infos dans la DB history avec la date donnéee dans le json
                  //pas besoin de verifier que la valeur existe pas encore, la DB gere unicité paire datetime/cmd
                  $cmd = $suiviCO2->getCmd(null, 'co2kwhfromApi');
                  if (is_object($cmd)) {
                    $cmd->addHistoryValue($record_tauxco2, $record_date . ' ' . $record_time . ':00');
                    log::add('suiviCO2', 'debug', $nbRecordsTraites . ' - Taux_Co2 : ' . $record_tauxco2 . ' à : ' . $record_date . ' ' . $record_time . ':00');
                  }

                } //fin boucle verification on veut ecrire les datas pour cet equipement
              } // fin foreach equipement
            } // fin on a trouvé une heure entiere
          } // fin if on est dans un noeud avec un taux co2
        } //fin boucle dans toutes les datas recuperées
      } //fin fonction

      public static function cronHourly() {
        $datetime = date('Y-m-d H:i:00');

        log::add('suiviCO2', 'debug', '#################### CRON Hourly ###################');

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
        sleep(60);//attend 1 min, si execution à l'heure pile on recoit pas les datas (due a la mise à jour de l'API ?)
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
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('consumptionHP');
    //    $cmd->setTemplate('dashboard', 'tile');
      }
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'max');
      $cmd->setConfiguration('historizeRound', 0);
      $cmd->setName(__('Consommation HP', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('Wh');
      $cmd->setIsVisible(0);
      $cmd->save();


      $cmd = $this->getCmd(null, 'consumptionHC');
      if (!is_object($cmd)) {
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('consumptionHC');
  //      $cmd->setTemplate('dashboard', 'tile');
      }
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'max');
      $cmd->setConfiguration('historizeRound', 0);
      $cmd->setName(__('Consommation HC', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('Wh');
      $cmd->setIsVisible(1);
      $cmd->save();


      $cmd = $this->getCmd(null, 'co2kwhfromApi');
      if (!is_object($cmd)) {
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('co2kwhfromApi');
    //    $cmd->setTemplate('dashboard', 'tile');
      }
      $cmd->setIsHistorized(1);
      $cmd->setConfiguration('historizeMode', 'max'); //max, avg, none ?
      $cmd->setConfiguration('historizeRound', 0);
      $cmd->setName(__('Valeur CO2 par kWh', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('gCO2');
      $cmd->setIsVisible(1);
      $cmd->save();

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


