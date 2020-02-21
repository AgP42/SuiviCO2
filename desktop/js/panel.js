
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
//$('.objectName').empty().append('coucou from JS');

$(".in_datepicker").datepicker();


$('#bt_validChangeDateSuiviCO2').on('click', function () {
 // $('.objectName').empty().append('Date voulues : ' + $('#in_startDate').value() + ' au ' + $('#in_endDate').value());

  jeedom.history.chart = [];
  $('#div_chartCO2parkWh').packery('destroy');
  $('#div_chartConsokWh').packery('destroy');
  $('#div_chartConsoCO2').packery('destroy');
  displayGraphsCO2(object_id, $('#in_startDate').value(), $('#in_endDate').value());
});

/* Fonctions pour afficher les graphs */
displayGraphsCO2(object_id,'',''); // appelle la fonction ci-dessous au lancement de la page. object_id vient du php

function displayGraphsCO2(object_id,_dateStart,_dateEnd) {
  // l'appel ajax pour aller chercher les données
  $.ajax({
    type: 'POST',
    url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
    data: {
      action: 'getSuiviCO2Data',
      object_id: object_id,
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
      // recupere l'icone et le nom de l'objet pour l'affichage en haut
      var icon = '';
      if (isset(data.result.object.display) && isset(data.result.object.display.icon)) {
        icon = data.result.object.display.icon;
      }
      $('.objectName').empty().append(icon + ' ' + data.result.object.name);

      // vide les div
      $('#div_chartCO2parkWh').empty();
      $('#div_chartConsokWh').empty();
      $('#div_chartConsoCO2').empty();


      // affiche les graphs
      var series = []
      for (var i in data.result.eqLogics) {

   //   $('#div_alert').showAlert({message: data.result.eqLogics[i].eqLogic.id, level: 'info'});

        // cree un nouveau div pour chaque courbe du graph en bas, dont l'ID contient l'id de l'equipement
        $('#div_chartCO2parkWh').append( '<div class="chartContainer" id="div_chartCO2parkWh' + data.result.eqLogics[i].eqLogic.id + '"></div>');

        // appel la construction du graphe en bas
        graphCO2(data.result.eqLogics[i].eqLogic.id);
      } //*/

    } // fin success
  }); //fin appel ajax
} //fin fct displayGraphsCO2


// pour construire le graph en bas avec toutes les differentes infos dessus
function graphCO2(_eqLogic_id) {
  jeedom.eqLogic.getCmd({
    id: _eqLogic_id,
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (cmds) {
      jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id] = null;
      var foundPower = false;
      for (var i  in cmds) {
        if (cmds[i].logicalId == 'co2kwhfromApi') {
          jeedom.history.drawChart({
            cmd_id: cmds[i].id,
            el: 'div_chartCO2parkWh' + _eqLogic_id,
            dateStart: $('#in_startDate').value(),
            dateEnd: $('#in_endDate').value(),
            option: {
              graphColor: '#2E9AFE',
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
