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
<form class="form-horizontal" id="form_configEnergy">
    <fieldset>
        <legend><i class="fa fa-eur"></i> {{Tarification}}</legend>
        <div class="form-group">
            <label class="col-lg-2 control-label">{{Devise}}</label>
            <div class="col-lg-2">
                <input class="configKey form-control" data-l1key="currency" value="€"/>
            </div>
        </div>

        <legend><i class="fa fa-bolt"></i> {{Electricité}}</legend>

      <div class="electricityConfig">
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Tarification}}</label>
            <div class="col-sm-2">
                <select class="form-control configKey input-sm" data-l1key="rateMode">
                    <option value="fixed">Fixe</option>
                    <option value="variable">Heures pleines/heures creuses</option>
                </select>
            </div>
        </div>
        <div class="rate fixed">
            <div class="form-group">
                <label class="col-sm-2 control-label">{{Tarif}}</label>
                <div class="col-sm-4">
                    <div class="input-group">
                        <input class="form-control configKey input-sm" data-l1key="rate"/>
                        <span class="input-group-btn">
                          <a class="btn btn-default btn-sm listCmdInfo btn-warning"  data-input="rate"><i class="fa fa-list-alt"></i></a>
                      </span>
                  </div>
              </div>
          </div>
      </div>
      <div class="rate variable" style="display : none;">
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Tarif heure pleine}}</label>
            <div class="col-sm-2">
                <input class="form-control configKey input-sm" data-l1key="rateHp"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Tarif heure creuse}}</label>
            <div class="col-sm-2">
                <input class="form-control configKey input-sm" data-l1key="rateHc"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Début heure creuse}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="startHc"/>
            </div>
            <label class="col-sm-2 control-label">{{Fin heure creuse}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="endHc"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Début heure creuse 2}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="startHc2"/>
            </div>
            <label class="col-sm-2 control-label">{{Fin heure creuse 2}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="endHc2"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">{{Début heure creuse 3}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="startHc3"/>
            </div>
            <label class="col-sm-2 control-label">{{Fin heure creuse 3}}</label>
            <div class="col-sm-1">
                <input class="form-control configKey input-sm timepicker" data-l1key="endHc3"/>
            </div>
        </div>
    </div>
  </div>
  </fieldset>
</form>

<script>

   $('#form_configEnergy').delegate('.enableType','change', function () {
    if($(this).value() == 1){
        $('.'+$(this).attr('data-type')+'Config').show();
    }else{
       $('.'+$(this).attr('data-type')+'Config').hide();
   }
});

 $('.configKey[data-l1key=rateMode]').on('change', function () {
    $('.rate').hide();
    $('.rate.' + $(this).value()).show();
});

  $("#form_configEnergy").delegate(".listCmdInfo", 'click', function () {
    var el = $('.configKey[data-l1key=' + $(this).attr('data-input') + ']');
    jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
        el.atCaret('insert', result.human);
    });
});

</script>
