
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
  $('#div_chartCost').packery('destroy');
  displayGraphsCO2(eqLogic_id, $('#in_startDate').value(), $('#in_endDate').value());

//  $('#div_alert').showAlert({message: $('#in_startDate').value(), level: 'info'});
});


//setTimeout(function(){ displayGraphsCO2(eqLogic_id); }, 1); //TODO a tester

/* Fonctions pour afficher les graphs */
displayGraphsCO2(eqLogic_id,'',''); // appelle la fonction ci-dessous au lancement de la page. eqLogic_id vient du php

function displayGraphsCO2(_eqLogic_id, _dateStart, _dateEnd) {
  // l'appel ajax pour aller chercher les données selon l'équipement selectionné
  $.ajax({
    type: 'POST',
    url: 'plugins/suiviCO2/core/ajax/suiviCO2.ajax.php',
    data: {
      action: 'getSuiviCO2Data',
      eqLogic_id: _eqLogic_id,
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

      // recupere le nom de l'equipement pour l'affichage dans la barre en haut
      $('.objectName').empty().append(data.result.eqLogic.name);

      $('#div_suiviCO2').setValues(data.result.datas, '.suiviCO2Attr'); // ??
  //    $('#div_suiviCO2').setValues(data.result, '.suiviCO2Attr'); // ??

      // vide les div
      $('#div_chartConsoCO2').empty();
      $('#div_chartCO2parkWh').empty();
      $('#div_chartConsokWh').empty();
      $('#div_chartCost').empty();

      // affiche les graphs

      // pour le graph conso CO2
      var series = []

      series.push({
        step: true,
        name: 'Mes émissions gCO2',
        data: data.result.datas.consoCO2,
        type: 'column',
        color: '#4572A7',
        stack : 1,
    //    unite : 'gCO2',
        stacking : 'normal',
        dataGrouping: {
            approximation: "sum",
            enabled: true,
            forced: true,
            units: [[groupBy,[1]]]
        },
        tooltip: {
    //        valueDecimals: 2,
            pointFormat: '<span style="color:{series.color}"></span><b>{point.y}</b><br/>',
            valueSuffix: ' gCO2'
        },
      });

       drawSimpleGraph('div_chartConsoCO2', series, ' gCO2');


      // pour le graph cout
      var series = []

      series.push({
        step: true,
        name: 'HP ',
        data: data.result.datas.cost.HP,
        type: 'column',
        color: '#4572A7',
        stack : 1,
     //   unite : 'kWh',
        stacking : 'normal',
        dataGrouping: {
            approximation: "sum",
            enabled: true,
            forced: true,
            units: [[groupBy,[1]]]
        },
        tooltip: {
  //          valueDecimals: 2,
            valueSuffix: ' €'
        },
      });

      if(data.result.HCtoDisplay){
        series.push({
          step: true,
          name: 'HC ',
          data: data.result.datas.cost.HC,
          type: 'column',
          color: '#AA4643',
          stack : 1,
      //    unite : 'kWh',
          stacking : 'normal',
          dataGrouping: {
              approximation: "sum",
              enabled: true,
              forced: true,
              units: [[groupBy,[1]]]
          },
          tooltip: {
      //        valueDecimals: 2,
              valueSuffix: ' €'
          },
        });
      }

      series.push({
        step: true,
        name: 'Abo ',
        data: data.result.datas.cost.Abo,
        type: 'column',
        color: '#000000',
        stack : 1,
    //    unite : 'kWh',
        stacking : 'normal',
        dataGrouping: {
            approximation: "sum",
            enabled: true,
            forced: true,
            units: [[groupBy,[1]]]
        },
        tooltip: {
 //           valueDecimals: 2,
            valueSuffix: ' €'
        },
      });

      drawSimpleGraph('div_chartCost', series, ' €');


      // pour le graph CO2 API
      var series = []

      series.push({
        step: true,
        name: 'Emission CO2 par kWh produit en France',
        data: data.result.datas.CO2API,
        type: 'spline',
        color: '#558000',
        stack : 1,
  //      unite : 'gCO2',
        stacking : 'normal',
        dataGrouping: {
            approximation: "average",
            enabled: true,
            forced: true,
            units: [[groupBy,[1]]]
        },
        tooltip: {
  //          valueDecimals: 2,
            pointFormat: '<span style="color:{series.color}"></span><b>{point.y}</b><br/>',
            valueSuffix: ' gCO2/kWh'
        },
      });

      drawSimpleGraph('div_chartCO2parkWh', series); // données brut API


      // pour le graph conso kWh HP et HC
      var series = []

      series.push({
        step: true,
        name: 'HP ',
        data: data.result.datas.consoHP,
        type: 'column',
        color: '#4572A7',
        stack : 1,
      //  unite : 'kWh',
        stacking : 'normal',
        dataGrouping: {
            approximation: "sum",
            enabled: true,
            forced: true,
            units: [[groupBy,[1]]]
        },
        tooltip: {
     //       valueDecimals: 2,
            valueSuffix: ' kWh'
        },
      });

      if(data.result.HCtoDisplay){
        series.push({
          step: true,
          name: 'HC ',
          data: data.result.datas.consoHC,
          type: 'column',
          color: '#AA4643',
          stack : 1,
      //    unite : 'kWh',
          stacking : 'normal',
          dataGrouping: {
              approximation: "sum",
              enabled: true,
              forced: true,
              units: [[groupBy,[1]]]
          },
          tooltip: {
       //       valueDecimals: 2,
              valueSuffix: ' kWh'
          },
        });
      }

      drawSimpleGraph('div_chartConsokWh', series, ' kWh');


    } // fin success
  }); //fin appel ajax
} //fin fct displayGraphsCO2

// pour construire le graph CO2 par kWh en France
/*function graphCO2(_eqLogic_id) {
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
              graphColor: '#27ae60'
            //  graphColor: '#' + Math.floor(Math.random()*16777215).toString(16),
            //  derive : 0,
            //  graphStep: 1,
            //  graphScale : 1,
            //  graphType : 'area',
            //  graphZindex :1
            }
          });
        } //fin if courbe
      } // fin for cmds

      setTimeout(function(){
        jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.xAxis[0].setExtremes(jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.navigator.xAxis.min,jeedom.history.chart['div_chartCO2parkWh' + _eqLogic_id].chart.navigator.xAxis.max)
      }, 1000);
    }// fin success
  }); // fin jeedom.eqLogic.getCmd
} // fin fct graphCO2 */

function drawSimpleGraph(_el, _serie, _unite = '') {
  new Highcharts.StockChart({
    chart: {
      zoomType: 'x',
      renderTo: _el,
      height: 340,
      spacingTop: 0,
      spacingLeft: 0,
      spacingRight: 0,
      spacingBottom: 0
    },
    credits: {
      text: '',
      href: '',
    },
    navigator: {
      enabled: false
    },
    rangeSelector: {
      buttons: [{
        type: 'day',
        count: 1,
        text: 'J'
      }, {
        type: 'week',
        count: 1,
        text: 'S'
      }, {
        type: 'month',
        count: 1,
        text: 'M'
      }, {
        type: 'year',
        count: 1,
        text: 'A'
      }, {
        type: 'all',
        count: 1,
        text: 'Tous'
      }],
      selected: 4,
      inputEnabled: false
    },
    legend: {
      enabled: true
    },
    tooltip: {
  //    pointFormat: '<span style="color:{series.color}"></span>{series.name}<b>{point.y}</b><br/>',
      valueDecimals: 2
    },
    plotOptions: {
            column: {
                stacking: 'normal',
                dataLabels: {
                    enabled: false
                }
            }
        },
    yAxis: {
      format: '{value}',
      showEmpty: false,
      showLastLabel: true,
      min: 0,
      stackLabels: {
        enabled: true,
        formatter: function() {
          return  Highcharts.numberFormat(this.total, 2, ',') + _unite;
        },
        style: {
          fontWeight: 'bold',
          color: ( // theme
            Highcharts.defaultOptions.title.style &&
            Highcharts.defaultOptions.title.style.color
            ) || 'gray'
          }
        },
      labels: {
        align: 'right',
        x: -5
      }
    },
    xAxis: {
        type: 'datetime',
        dateTimeLabelFormats: {
            hour: '%d-%m-%Y<br/>%H:%M',
            day: '%a %d-%m-%Y',
            week: '%d-%m-%Y',
            month: '%m-%Y',
            year: '%Y'
        }
    },
    scrollbar: {
      barBackgroundColor: 'gray',
      barBorderRadius: 7,
      barBorderWidth: 0,
      buttonBackgroundColor: 'gray',
      buttonBorderWidth: 0,
      buttonBorderRadius: 7,
      trackBackgroundColor: 'none', trackBorderWidth: 1,
      trackBorderRadius: 8,
      trackBorderColor: '#CCC'
    },
    series: _serie
  });
}
