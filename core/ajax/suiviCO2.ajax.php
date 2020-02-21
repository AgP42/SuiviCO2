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
//      if (init('object_id') == '') {
//        $_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
//      }
/*      $object = jeeObject::byId(init('object_id'));
      if (!is_object($object)) {
        $object = jeeObject::rootObject();
      }
      if (!is_object($object)) {
        throw new Exception(__('Aucun objet racine trouvé', __FILE__));
      }
      if (count($object->getEqLogic(true, false, 'suiviCO2')) == 0) {
        $allObject = jeeObject::buildTree();
        foreach ($allObject as $object_sel) {
          if (count($object_sel->getEqLogic(true, false, 'suiviCO2')) > 0) {
            $object = $object_sel;
            break;
          }
        }
      }
      $return = array('object' => utils::o2a($object));
*/
      $date = array(
        'start' => init('dateStart'),
        'end' => init('dateEnd'),
      );
      $eqLogic_id = init('eqLogic_id');

      log::add('suiviCO2', 'debug', 'Dans ajax : ' . $eqLogic_id);

      if ($date['start'] == '') {
        $date['start'] = date('Y-m-d', strtotime('-1 months ' . date('Y-m-d')));
      }
      if ($date['end'] == '') {
        $date['end'] = date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')));
      }
      $return['date'] = $date;

   //   log::add('suiviCO2', 'debug', 'Dans ajax : ' . utils::o2a($eqLogic_id);

      //foreach ($object->getEqLogic(true, false, 'suiviCO2') as $eqLogic) {
      $eqLogic = eqLogic::byId($eqLogic_id);
  //    $eqLogic = byLogicalId($_logicalId,   $_eqType_name,   $_multiple = false)
        $return = array('eqLogic' => utils::o2a($eqLogic));
      //}
      ajax::success($return);
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayException($e), $e->getCode());
}


