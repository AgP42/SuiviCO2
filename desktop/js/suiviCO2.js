
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

$(".in_datepicker").datepicker({
    changeMonth: true,
    changeYear: true,
});

$(".in_datepicker_month_year").datepicker({
  changeMonth: true,
  changeYear: true,
  dateFormat: 'yy-mm',
  onClose: function(dateText, inst) {
      $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
  }

});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').change(function () {
  if($('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == "gaz" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == "fioul" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=type]').value() == "other"){
    $('.choix_taux_co2').show();
    $('.historique_api_elec').hide();
  } else {
    $('.choix_taux_co2').hide();
    $('.historique_api_elec').show();
  }
});

$(".eqLogic").off('click','.listCmdInfo').on('click','.listCmdInfo', function () {
  var el = $(this).closest('.form-group').find('.eqLogicAttr');
  jeedom.cmd.getSelectModal({cmd: {type: 'info'}}, function (result) {
    if (el.attr('data-concat') == 1) {
      el.atCaret('insert', result.human);
    } else {
      el.value(result.human);
    }
  });
});


$('#bt_historyCO2').on('click', function () {
    bootbox.confirm('{{Environ 1,5 mois de données, l\'opération peut prendre plusieurs minutes}}', function (result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
                data: {
                    action: 'getAPICO2Data',
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    nbRecordsAPI: 6000,
                    nbRecordsATraiterDB: 6000,
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'Historique chargé avec succès', level: 'success'});
                }
            });
        }
    });
});

$('#bt_historyCo2_def').on('click', function () {

    bootbox.confirm('{{L\'opération peut être longue, environ 20s sur un RPI3}}', function (result) {
        if (result) {

//          $('#div_alert').showAlert({message: 'Mois demandé ' + $('#in_startDateCo2_def').value(), level: 'success'});

            $.ajax({
                type: 'POST',
                url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
                data: {
                    action: 'getHistoriqueCo2Def',
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    date : $('#in_startDateCo2_def').value(),
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'Historique du ' + $('#in_startDateCo2_def').value() + 'chargé avec succès', level: 'success'});
                }
            }); //*/
        } // fin if result (bouton du pop up)
    });
});


$('#bt_historykWh').on('click', function () {

    bootbox.confirm('{{Attention : L\'opération est exponentiellement longue selon la durée sélectionnée. Environ 3s pour 1 mois, 20s pour 6 mois et 80s pour 1 an (RPI3). }}', function (result) {
        if (result) {
            $.ajax({
                type: 'POST',
                url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
                data: {
                    action: 'getHistoriqueConso',
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    dateStart : $('#in_startDate').value(),
                    dateEnd : $('#in_endDate').value(),
                },
                dataType: 'json',
                error: function (request, status, error) {
                    handleAjaxError(request, status, error);
                },
                success: function (data) {
                    if (data.state != 'ok') {
                        $('#div_alert').showAlert({message: data.result, level: 'danger'});
                        return;
                    }
                    $('#div_alert').showAlert({message: 'Historique chargé avec succès', level: 'success'});
                }
            });
        }
    });
});


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}
