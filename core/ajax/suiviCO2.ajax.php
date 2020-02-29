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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    if (init('action') == 'getSuiviCO2Data') {

      // initialise les variables locales avec les infos de la conf from le JS
      $date = array(
        'start' => init('dateStart'),
        'end' => init('dateEnd'),
      );
      $eqLogic_id = init('eqLogic_id');

  //    log::add('suiviCO2', 'debug', 'Dans ajax, eqLogic_id : ' . $eqLogic_id);

      if ($date['start'] == '') {
        $date['start'] = date('Y-m-d', strtotime('-1 months ' . date('Y-m-d')));
      }
      if ($date['end'] == '') {
        $date['end'] = date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')));
      }
      $return['date'] = $date; // je vois pas ou c'est utilisé apres, à virer ?

   //   log::add('suiviCO2', 'debug', 'Dans ajax : ' . utils::o2a($eqLogic_id);

      $eqLogic = eqLogic::byId($eqLogic_id); // on recupere l'eqLogic à partir de son ID
      if($eqLogic->getConfiguration('index_HC')!=''){ //si on a une commande HC definie
    //    log::add('suiviCO2', 'debug', 'Dans ajax, on a une HC a afficher !');
        $HCtoDisplay = true;
      } else {
        log::add('suiviCO2', 'debug', 'Dans ajax, on a PAS de HC a afficher !');
    //    $HCtoDisplay = false;
      }

      $return = array(
        'eqLogic' => utils::o2a($eqLogic),
        'HCtoDisplay' => $HCtoDisplay,
        'datas' => $eqLogic->getGraphsDatasSuiviCO2($date['start'], $date['end'])
      );

 //     log::add('suiviCO2', 'debug', 'Dans ajax, consowh : ' . $return['consowh'][0]);

      ajax::success($return);
    } // end getSuiviCO2Data

    if (init('action') == 'getAPICO2Data') {

      $eqLogic = eqLogic::byId(init('id'));
      if (!is_object($eqLogic)) {
        throw new Exception(__('Equipement introuvable : ', __FILE__) . init('id'));
      }

      $_nbRecordsAPI = init('nbRecordsAPI');
      $_nbRecordsATraiterDB = init('nbRecordsATraiterDB');

  //    log::add('suiviCO2', 'debug', 'Recu dans ajax : ' . $_nbRecordsAPI . ' - ' . $_nbRecordsATraiterDB);

      $eqLogic->getAndRecordDataCo2($_nbRecordsAPI, $_nbRecordsATraiterDB, init('id'));

      ajax::success($return);
    }

    if (init('action') == 'getHistoriqueCo2Def') {

      $eqLogic = eqLogic::byId(init('id'));
      if (!is_object($eqLogic)) {
        throw new Exception(__('Equipement introuvable : ', __FILE__) . init('id'));
      }

      $date = init('date');

      $_nbRecordsAPI = 3000;

      log::add('suiviCO2', 'debug', 'Recu dans ajax : ' . $date . ' - pour l eqLogic '  . init('id'));

      $eqLogic->getAndRecordDataCo2Definitives($_nbRecordsAPI, $date);

      ajax::success($return);
    }

    if (init('action') == 'getHistoriqueConso') {

      // initialise les variables locales avec les infos de la conf from le JS
      $date = array(
        'start' => init('dateStart'),
        'end' => init('dateEnd'),
      );

      $eqLogic = eqLogic::byId(init('id')); // on recupere l'eqLogic à partir de son ID

  //    log::add('suiviCO2', 'debug', 'Dans ajax, eqLogic_id : ' . $eqLogic_id);

      if ($date['start'] == '') {
        $date['start'] = date('Y-m-d', strtotime('-1 months ' . date('Y-m-d')));
      }
      if ($date['end'] == '') {
        $date['end'] = date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')));
      }
      $return['date'] = $date; // je vois pas ou c'est utilisé apres, à virer ?

  //    log::add('suiviCO2', 'debug', 'Dans ajax on a recu : ' . $eqLogic->getHumanName() . ' - ' . $date['start'] . ' - ' . $date['end']);


      $eqLogic->getAndRecordHistoriqueConso($date['start'], $date['end']);

      ajax::success($return);
    } // end getSuiviCO2Data

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}




