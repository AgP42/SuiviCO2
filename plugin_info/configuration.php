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
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>

      <legend><i class="fa fa-bolt"></i> {{Coût électricité}}</legend>

      <div class="electricityConfig">
        <div class="form-group">
            <label class="col-sm-4 control-label">{{Tarification}}</label>
            <div class="col-sm-4">
                <select class="form-control configKey input-sm" data-l1key="rateMode">
                    <option value="fixed">Fixe</option>
                    <option value="variable">Heures pleines/heures creuses</option>
                </select>
            </div>
        </div>
        <div class="rate fixed">
          <div class="form-group">
              <label class="col-sm-4 control-label">{{Coût abonnement (€ TTC/mois)}}</label>
              <div class="col-sm-2">
                  <input class="form-control configKey input-sm" data-l1key="coutabo"/>
              </div>
          </div>
        </div>
        <div class="rate fixed">
          <div class="form-group">
              <label class="col-sm-4 control-label">{{Prix du kWh (€/kWh)}}</label>
              <div class="col-sm-2">
                  <input class="form-control configKey input-sm" data-l1key="rateHp"/>
              </div>
          </div>
        </div>

        <div class="rate variable" style="display : none;">
          <div class="form-group">
              <label class="col-sm-4 control-label">{{Coût abonnement (€ TTC/mois)}}</label>
              <div class="col-sm-2">
                  <input class="form-control configKey input-sm" data-l1key="coutabo"/>
              </div>
          </div>

          <div class="form-group">
              <label class="col-sm-4 control-label">{{Tarif heure pleine (€/kWh)}}</label>
              <div class="col-sm-2">
                  <input class="form-control configKey input-sm" data-l1key="rateHp"/>
              </div>
          </div>
          <div class="form-group">
              <label class="col-sm-4 control-label">{{Tarif heure creuse (€/kWh)}}</label>
              <div class="col-sm-2">
                  <input class="form-control configKey input-sm" data-l1key="rateHc"/>
              </div>
          </div>

      </div>
    </div>
  </fieldset>
</form>

<script>


 $('.configKey[data-l1key=rateMode]').on('change', function () {
    $('.rate').hide();
    $('.rate.' + $(this).value()).show();
  });

  $('.timepicker').datetimepicker({
    lang: 'fr',
    datepicker: false,
    format: 'H:i',
    step: 15
  });


</script>
