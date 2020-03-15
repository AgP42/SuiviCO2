
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

// $('#div_alert').showAlert({message: 'suiviConsoActif : ' + suiviConsoActif, level: 'success'});

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

$('.eqLogicAttr[data-l1key=configuration][data-l2key=conso_type]').change(function () {
  if($('.eqLogicAttr[data-l1key=configuration][data-l2key=conso_type]').value() == "gaz" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=conso_type]').value() == "fioul" || $('.eqLogicAttr[data-l1key=configuration][data-l2key=conso_type]').value() == "other"){
    $('.type_gaz_fioul_autre').show();
    $('.type_elec').hide();
  } else if($('.eqLogicAttr[data-l1key=configuration][data-l2key=conso_type]').value() == "elec"){
    $('.type_gaz_fioul_autre').hide();
    $('.type_elec').show();
  } else {
    $('.type_elec').hide();
    $('.type_gaz_fioul_autre').hide();
  }
});

$('.eqLogicAttr[data-l1key=configuration][data-l2key=suiviconso_eqLogic_id]').change(function () {
  let suiviconso_eqLogic_id = $('.eqLogicAttr[data-l1key=configuration][data-l2key=suiviconso_eqLogic_id]').value();

  if(suiviconso_eqLogic_id != ''){

    $.ajax({
          type: 'POST',
          url: 'plugins/conso/core/ajax/api.ajax.php',
          data: {
            action: 'GetEquipement',
            type: null
          },
          dataType: 'json',
          error: function (request, status, error) {
            handleAjaxError(request, status, error, $('#div_DashboardAlert'));
          },
          success: function (data) {

            console.log(data.result[suiviconso_eqLogic_id]);

        //    $('#div_alert').showAlert({message: 'Suivi Conso type : ' + data.result[suiviconso_eqLogic_id].type, level: 'success'});

            if(data.result[suiviconso_eqLogic_id].type == "electricity"){

            //  $('#div_alert').showAlert({message: 'Configuration chargée, merci de vérifier !', level: 'success'});
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HP]').value(data.result[suiviconso_eqLogic_id].hchp_human);
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HC]').value(data.result[suiviconso_eqLogic_id].hchc_human);
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=coef_thermique]').value(data.result[suiviconso_eqLogic_id].hchp_unity);

            } else {
              $('#div_alert').showAlert({message: 'Merci de choisir un équipement de type \'Electricité\'', level: 'danger'});
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HP]').value('');
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HC]').value('');
              $('.eqLogicAttr[data-l1key=configuration][data-l2key=coef_thermique]').value('');
            }
          }// fin success ajax
    });

  } else { // on choisi de ne pas reprendre la conf suivi conso, on remet les valeurs a blanc
    $('#div_alert').hide();
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HP]').value('');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=index_HC]').value('');
    $('.eqLogicAttr[data-l1key=configuration][data-l2key=coef_thermique]').value('');
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

$('#bt_importSuiviConso').on('click', function () {

  if(isset($('.eqLogicAttr[data-l1key=suiviconso_eqLogic_id]').value())) {

    $.ajax({
      type: 'POST',
      url: 'plugins/conso/core/ajax/api.ajax.php',
      data: {
        action: 'getHistory',
        suiviconso_eqLogic_id: $('.eqLogicAttr[data-l1key=suiviconso_eqLogic_id]').value(),
        startTime: $('#in_startDateSuiviConso').value(),
        endTime: $('#in_endDateSuiviConso').value()
      },
      dataType: 'json',
      error: function (request, status, error) {
        handleAjaxError(request, status, error, $('#div_DashboardAlert'));
      },
      success: function (data) {
        if (data.state != 'ok') {
          $('#div_alert').showAlert({message: data.result, level: 'danger'});
          return;
        }

        // on a fini l'appel a suivi conso, maintenant on passe les datas a notre ajax pour les traiter et enregistrer
        $.ajax({
            type: 'POST',
            url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
            data: {
                action: 'recordHistoryFromSuiviConso',
                id: $('.eqLogicAttr[data-l1key=id]').value(),
                datas : data.result,
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
                $('#div_alert').showAlert({message: 'Historique du plugin SuiviConso chargé avec succès', level: 'success'});
            }
        });

      }
    });

  }else{
    $('#div_alert').showAlert({message: 'Vous devez choisir l\'équipement Suivi Conso à utiliser', level: 'danger'});
  }


});

$('#bt_testSuiviConso').on('click', function () {

    $.ajax({
      type: 'POST',
      url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
      data: {
          action: 'getCO2DataForSuiviConso',
          id: $('.eqLogicAttr[data-l1key=id]').value(),
          dateStart : $('#in_startDateTest').value(),
          dateEnd : $('#in_endDateTest').value(),
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
            $('#div_alert').showAlert({message: 'Dates : ' + data.result.date.start + ' - ' + data.result.date.end + ', valeur : ' + data.result.datas, level: 'success'});
          }
    }); //*/

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

    bootbox.confirm('{{L\'opération peut être longue selon la durée sélectionnée}}', function (result) {
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

//    tr += '<td>';
//    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
//    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
//    tr += '</td>';

    tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    tr += '</td>';

    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="margin-top : 5px;"> ';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="margin-top : 5px;">';

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
