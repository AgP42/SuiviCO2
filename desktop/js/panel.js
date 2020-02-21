
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

/* Fonctions pour la barre en haut */
$(".in_datepicker").datepicker();

$('#bt_validChangeDateSuiviCO2').on('click', function () {
  jeedom.history.chart = [];
  $('#div_chartCO2parkWh').packery('destroy');
  $('#div_chartConsokWh').packery('destroy');
  $('#div_chartConsoCO2').packery('destroy');
  displayGraphsCO2(eqLogic_id, $('#in_startDate').value(), $('#in_endDate').value());
});

//$('#div_alert').showAlert({message: eqLogic_id, level: 'info'});

/* Fonctions pour afficher les graphs */
displayGraphsCO2(eqLogic_id,'',''); // appelle la fonction ci-dessous au lancement de la page. eqLogic_id vient du php

function displayGraphsCO2(eqLogic_id,_dateStart,_dateEnd) {
  // l'appel ajax pour aller chercher les données selon l'équipement selectionné
  $.ajax({
    type: 'POST',
    url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
    data: {
      action: 'getSuiviCO2Data',
      eqLogic_id: eqLogic_id,
      dateStart : _dateStart,
      dateEnd : _dateEnd,
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

      //$('#div_alert').showAlert({message: data.result.eqLogic.id, level: 'info'});

      // recupere le nom de l'equipement pour l'affichage en haut
       $('.objectName').empty().append(data.result.eqLogic.name);

      // vide les div
      $('#div_chartCO2parkWh').empty();
      $('#div_chartConsokWh').empty();
      $('#div_chartConsoCO2').empty();

      // affiche les graphs
      // appel la construction du graphe CO2 par kWh (en haut à gauche)
      graphCO2(data.result.eqLogic.id);

    } // fin success
  }); //fin appel ajax
} //fin fct displayGraphsCO2

// pour construire le graph CO2 par kWh en France
function graphCO2(_eqLogic_id) {
  jeedom.eqLogic.getCmd({
    id: _eqLogic_id,
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (cmds) {
      jeedom.history.chart['div_chartCO2parkWh'] = null;
      for (var i  in cmds) {
        if (cmds[i].logicalId == 'co2kwhfromApi') {
          jeedom.history.drawChart({
            cmd_id: cmds[i].id,
            el: 'div_chartCO2parkWh',
            dateStart: $('#in_startDate').value(),
            dateEnd: $('#in_endDate').value(),
            option: {
              graphColor: '#' + Math.floor(Math.random()*16777215).toString(16),
              derive : 0,
              graphZindex : 3
            }
          });
        } //fin if courbe
      } // fin for cmds

      setTimeout(function(){
        jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.xAxis[0].setExtremes(jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.navigator.xAxis.min,jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.navigator.xAxis.max)
      }, 1000);
    }// fin success
  }); // fin jeedom.eqLogic.getCmd
} // fin fct graphCO2
