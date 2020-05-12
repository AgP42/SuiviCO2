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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function suiviCO2_install() {
  $cron = cron::byClassAndFunction('suiviCO2', 'getAndRecordDataCo2'); // cherche le cron qui correspond exactement à "ce plugin, cette fonction"

  if (!is_object($cron)) {
      $cron = new cron();
      $cron->setClass('suiviCO2');
      $cron->setFunction('getAndRecordDataCo2');
      $cron->setEnable(1);
      $cron->setTimeout(5); //minutes
      $cron->setSchedule('1 * * * *');
      $cron->save();
  }

}

function suiviCO2_update() {

  $cron = cron::byClassAndFunction('suiviCO2', 'getAndRecordDataCo2'); // cherche le cron qui correspond exactement à "ce plugin, cette fonction"

  if (!is_object($cron)) {
      $cron = new cron();
      $cron->setClass('suiviCO2');
      $cron->setFunction('getAndRecordDataCo2');
      $cron->setEnable(1);
      $cron->setTimeout(5); //minutes
      $cron->setSchedule('1 * * * *');
      $cron->save();
  }

}

function suiviCO2_remove() {

  $cron = cron::byClassAndFunction('suiviCO2', 'getAndRecordDataCo2');
  if (is_object($cron)) {
      $cron->remove();
  }

}

?>





