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

      ///// Creation de la commande index_hp, (obligatoire, verifiée dans preUpdate)
      $index_hp = $this->getCmd(null, 'index_HP');
      if (!is_object($index_hp)) {
        $index_hp = new suiviCO2Cmd();
    //    $index_hp->setTemplate('dashboard', 'line');
    //    $index_hp->setTemplate('mobile', 'line');
        $index_hp->setIsVisible(1);
        $index_hp->setIsHistorized(1);
        $index_hp->setName(__('Index HP', __FILE__));
      }
      $index_hp->setEqLogic_id($this->getId());
      $index_hp->setType('info');
      $index_hp->setSubType('numeric');
      $index_hp->setLogicalId('index_hp');
      $index_hp->setUnite('kWh');

      $value = '';
      preg_match_all("/#([0-9]*)#/", $this->getConfiguration('index_HP'), $matches);
      foreach ($matches[1] as $cmd_id) {
        if (is_numeric($cmd_id)) {
          $cmd = cmd::byId($cmd_id);
          if (is_object($cmd) && $cmd->getType() == 'info') {
            $value .= '#' . $cmd_id . '#';
            break;
          }
        }
      }
      $index_hp->setValue($value);
      $index_hp->setGeneric_type( 'GENERIC_INFO');
      $index_hp->save();

      ///// Creation de la commande index_hc, dans tous les cas, sinon on sait pas gerer le fait qu'on a rempli qqch puis on le vide //a ameliorer...
//      if ($this->getConfiguration('index_HC') != '') {
        $index_hc = $this->getCmd(null, 'index_HC');
        if (!is_object($index_hc)) {
          $index_hc = new suiviCO2Cmd();
      //    $index_hc->setTemplate('dashboard', 'line');
      //    $index_hc->setTemplate('mobile', 'line');
          $index_hc->setIsVisible(1);
          $index_hc->setIsHistorized(1);
          $index_hc->setName(__('Index HC', __FILE__));
        }
        $index_hc->setEqLogic_id($this->getId());
        $index_hc->setType('info');
        $index_hc->setSubType('numeric');
        $index_hc->setLogicalId('index_hc');
        $index_hc->setUnite('kWh');

        $value = '';
        preg_match_all("/#([0-9]*)#/", $this->getConfiguration('index_HC'), $matches);
        foreach ($matches[1] as $cmd_id) {
          if (is_numeric($cmd_id)) {
            $cmd = cmd::byId($cmd_id);
            if (is_object($cmd) && $cmd->getType() == 'info') {
              $value .= '#' . $cmd_id . '#';
              break;
            }
          }
        }
        $index_hc->setValue($value);
        $index_hc->setGeneric_type( 'GENERIC_INFO');
        $index_hc->save();
 //     }
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


