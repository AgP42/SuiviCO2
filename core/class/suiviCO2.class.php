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

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */
        public static function cron() {
        $datetime = date('Y-m-d H:i:00');

        $url = 'https://opendata.reseaux-energies.fr/api/records/1.0/search/?dataset=eco2mix-national-tr&rows=96&sort=date_heure&refine.date=2020-02-18';
        log::add('suiviCO2', 'debug', 'CO2 URL ' . $url);
        $request_http = new com_http($url);
        $content = $request_http->exec(30);
        //$content = file_get_contents($url);
        if ($content === false) {
          return;
        }
        $json = json_decode($content, true);
     /*   if (!isset($json['data']['aqi'])) {
          log::add('suiviCO2', 'error', 'Error in API call ' . $url);
          return;
        }*/
        log::add('suiviCO2', 'debug', 'Date et heure : ' . $json['records']['0']['fields']['date'] . ' - '. $json['records']['0']['fields']['heure'] . ' co2 : ' . $json['records']['0']['fields']['taux_co2']);
   /*     if ($json['data']['aqi'] <= 50) {
          $color = 'green';
        } else if ($json['data']['aqi'] <= 100) {
          $color = 'yellow';
        } else if ($json['data']['aqi'] <= 150) {
          $color = 'orange';
        } else if ($json['data']['aqi'] <= 200) {
          $color = 'red';     // 204 0 51
        } else if ($json['data']['aqi'] <= 300) {
          $color = 'magenta'; // 102 0 53
        } else {
          $color = 'brown';
        }*/

        //pour chaque equipement declaré par l'utilisateur
        foreach (self::byType('suiviCO2',true) as $suiviCO2) {

          } // fin foreach equipement
        } //fin fonction cron

      public static function cron5() {
        $datetime = date('Y-m-d H:i:00');

        //pour chaque equipement declaré par l'utilisateur
        foreach (self::byType('suiviCO2',true) as $suiviCO2) {

          /* Traitement HP */
          //on va chercher l'info index_HP via la conf utilisateur
          $indexHP = jeedom::evaluateExpression($suiviCO2->getConfiguration('index_HP'));

          //on recupere la precedente valeur stockée
          $lastValue = $suiviCO2->getConfiguration('lastValueHP');
          //on sauvegarde la valeur actuelle pour le prochain tour
          $suiviCO2->setConfiguration('lastValueHP', $indexHP);
          $suiviCO2->save();

          log::add('suiviCO2', 'debug', 'lastIndexHP : ' . $lastValue . ' IndexHP : ' . $indexHP);

          //on calcule la consommation entre les 2 derniers index
          $consumptionHP = $indexHP - $lastValue;

          //si cette consommation est >0, on va la stocker en base
          if ($consumptionHP > 0) {
            $cmd = $suiviCO2->getCmd(null, 'consumptionHp');
            if (is_object($cmd)) {
              $cmd->setCollectDate($datetime);
              log::add('suiviCO2', 'debug', 'conso HP (Wh) : ' . $consumptionHP);
              $cmd->event($consumptionHP);
            }
          }

          /* Traitement HC */
          if($suiviCO2->getConfiguration('index_HC')!=''){ //si on a un index HC

            //on va chercher l'info index_HP via la conf utilisateur
            $indexHC = jeedom::evaluateExpression($suiviCO2->getConfiguration('index_HC'));

            //on recupere la precedente valeur stockée
            $lastValue = $suiviCO2->getConfiguration('lastValueHC');
            //on sauvegarde la valeur actuelle pour le prochain tour
            $suiviCO2->setConfiguration('lastValueHC', $indexHC);
            $suiviCO2->save();

            log::add('suiviCO2', 'debug', 'lastIndexHC : ' . $lastValue . ' IndexHC : ' . $indexHC);

            //on calcule la consommation entre les 2 derniers index
            $consumptionHC = $indexHC - $lastValue;

            //si cette consommation est >0, on va la stocker en base
            if ($consumptionHC > 0) {
              $cmd = $suiviCO2->getCmd(null, 'consumptionHc');
              if (is_object($cmd)) {
                $cmd->setCollectDate($datetime);
                log::add('suiviCO2', 'debug', 'conso HC (Wh) : ' . $consumptionHC);
                $cmd->event($consumptionHC);
              }
            }
          }

/*          ce morceau de code va chercher tout l'historique de la commande et le loggue
            $previous = $cmd->getHistory();
            foreach ($previous as $value) {
              log::add('suiviCO2', 'debug', ' previous : ' . $value->getValue());
            }*/

        } // fin foreach equipement



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

      $cmd = $this->getCmd(null, 'consumptionHp');
      if (!is_object($cmd)) {
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('consumptionHP');
        $cmd->setTemplate('dashboard', 'tile');
        $cmd->setConfiguration('historizeMode', 'max');
        $cmd->setIsHistorized(1);
      }
      $cmd->setName(__('Consommation HP', __FILE__));
      $cmd->setEqLogic_id($this->getId());
      $cmd->setType('info');
      $cmd->setSubType('numeric');
      $cmd->setUnite('Wh');
      $cmd->setIsVisible(1);
      $cmd->save();


      $cmd = $this->getCmd(null, 'consumptionHc');
      if (!is_object($cmd)) {
        $cmd = new suiviCO2Cmd();
        $cmd->setLogicalId('consumptionHC');
        $cmd->setTemplate('dashboard', 'tile');
        $cmd->setConfiguration('historizeMode', 'max');
        $cmd->setIsHistorized(1);
      }
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
        $cmd->setTemplate('dashboard', 'tile');
        $cmd->setConfiguration('historizeMode', 'max');
        $cmd->setIsHistorized(1);
      }
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


