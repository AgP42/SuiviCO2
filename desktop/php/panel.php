<?php
if (!isConnect()) {
  throw new Exception('{{401 - Accès non autorisé}}');
}

if (init('object_id') == '') {
  $_GET['object_id'] = $_SESSION['user']->getOptions('defaultDashboardObject');
}
$object = jeeObject::byId(init('object_id'));
if (!is_object($object)) {
  $object = jeeObject::rootObject();
}
if (!is_object($object)) {
  throw new Exception('{{Aucun objet racine trouvé}}');
}
if (is_object($object)) {
  $_GET['object_id'] = $object->getId();
}


if (init('eqLogic_id') == '') { // on cherche le 1er equipement a afficher quand on arrive sur le panel, quand &eqLogic_id n'est pas dans l'URL

  //TODO - ca marche, mais ya pas plus simple ??
  $allObject = jeeObject::buildTree();
  foreach ($allObject as $object_li) {
    if ($object_li->getIsVisible() == 1) {
      foreach ($object_li->getEqLogic(true, false, 'suiviCO2') as $eqLogic) {
        if ($eqLogic->getIsEnable() == 1) {
          $eqLogic_id = $eqLogic->getId();
          break 2; // sort des 2 boucles foreach des qu'on a trouvé notre 1er equipement
        }
      }
    }
  }

} else { //si on a &eqLogic_id dans l'URL

  $eqLogic_id = init('eqLogic_id');

}

sendVarToJs('eqLogic_id', $eqLogic_id);

// initialise les dates du datepicker quand on arrive sur la page : debut 1 mois avant now et fin demain
$date = array(
  'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
  'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);//*/

// initialise le regrouper par à "jour" par defaut
sendVarToJs('groupBy', init('groupBy', 'day'));

// changement des dates demandées quand on passe en mois ou année pour chopper des range particulier // sauf que ca marche que pour l'affichage sur le datepicker et pas dans les datas renvoyées...

/*if (init('groupBy', 'day') == 'hour') { // quand on selectionne "heure", on va changer le date picker pour selectionner de la veille à demain
  $date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-1 day ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d', strtotime('+1 day ' . date('Y-m-d')))),
  );
}
if (init('groupBy', 'day') == 'day') { // quand on selectionne "jour", on prend de -1 mois à demain
  $date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
  );
}
if (init('groupBy', 'day') == 'week') { // quand on selectionne "semaine", on prend les 2 derniers mois entiers à la fin du mois courant
  $date = array(
    'start' => init('startDate', date('Y-m-01', strtotime('-2 month ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-t', strtotime(date('Y-m-d')))), // le t dans la fct date() permet de donner le dernier jour du mois
  );
}
if (init('groupBy', 'day') == 'month') { // quand on selectionne "month", on prend depuis le debut de l'année courante à la fin du mois courant
  $date = array(
    'start' => init('startDate', date('Y-01-01', strtotime('-1 year ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-t', strtotime(date('Y-m-d')))), // le t dans la fct date() permet de donner le dernier jour du mois
  );
}
if (init('groupBy', 'day') == 'year') { // quand on selectionne "year", on prend les 10 dernieres années
  $date = array(
    'start' => init('startDate', date('Y-01-01', strtotime('-10 year ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-12-31', strtotime('+1 days' . date('Y-m-d')))),
  );
} //*/

?>

<div class="row row-overflow" id="div_suiviCO2">

  <!-- Liste objet à gauche -->
  <div class="col-lg-2" id="sd_objectList" style="z-index:999">
    <div class="bs-sidebar">
      <ul id="ul_object" class="nav nav-list bs-sidenav">
        <li class="nav-header">{{Liste objets}}</li>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        $allObject = jeeObject::buildTree();
        foreach ($allObject as $object_li) {
          if ($object_li->getIsVisible() == 1) {
            $margin = 15 * $object_li->parentNumber();
            foreach ($object_li->getEqLogic(true, false, 'suiviCO2') as $eqLogic) {
              if ($eqLogic->getId() == $eqLogic_id) {
                echo '<li class="cursor li_object active" ><a href="index.php?v=d&m=suiviCO2&p=panel&eqLogic_id=' . $eqLogic->getId() . '" style="position:relative;left:' . $margin*2 . 'px;">' . $eqLogic->getHumanName(true) . '</a></li>';
              } else {
                echo '<li class="cursor li_object" ><a href="index.php?v=d&m=suiviCO2&p=panel&eqLogic_id=' . $eqLogic->getId() . '" style="position:relative;left:' . $margin*2 . 'px;">' . $eqLogic->getHumanName(true) . '</a></li>';
              }
            }
          }
        }
        ?>
      </ul>
    </div>
  </div>

  <!-- div principal -->
  <div class="col-lg-10">

    <!-- barre en haut avec le titre et selecteur de dates -->
    <div>
      <legend style="height: 35px;">
        <span class="objectName"></span>

          <span class='label label-default' style="font-size: 0.9em;"><span class="suiviCO2Attr" data-l1key="total" data-l2key="co2"></span> kg CO2</span>
          <span class='label label-primary' style="font-size: 0.9em;"><span class="suiviCO2Attr" data-l1key="total" data-l2key="consokWh"></span> kWh</span>
          <span class='label label-info' style="font-size: 0.9em;"><span class="suiviCO2Attr" data-l1key="total" data-l2key="cost"> </span> €</span>

          <span class="pull-right">

            <div>
              {{Période du}} <input class="form-control input-sm in_datepicker" id='in_startDate' style="display : inline-block; width: 150px;" value='<?php echo $date['start'] ?>'/> {{au}}
              <input class="form-control input-sm in_datepicker" id='in_endDate' style="display : inline-block; width: 150px;" value='<?php echo $date['end'] ?>'/>
              <a class="btn btn-success btn-sm tooltips" id='bt_validChangeDateSuiviCO2' title="{{Attention une trop grande plage de date peut mettre très longtemps a être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
            </div>

            <div>
              {{Grouper par : }}

              <!--i class="fas fa-calendar-alt"></i-->

              <!-- TODO a gerer en JS plutot d'en lien, pour pas tout recharger et perdre les dates demandées par l'user...-->
              <?php
              if (init('groupBy', 'day') == 'hour') {
                echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=hour&eqLogic_id=' . $eqLogic_id . '">{{Heure}}</a> ';
              } else {
                echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=hour&eqLogic_id=' . $eqLogic_id . '">{{Heure}}</a> ';
              }
              if (init('groupBy', 'day') == 'day') {
                echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=day&eqLogic_id=' . $eqLogic_id . '">{{Jour}}</a> ';
              } else {
                echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=day&eqLogic_id=' . $eqLogic_id . '">{{Jour}}</a> ';
              }
              if (init('groupBy', 'day') == 'week') {
                echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=week&eqLogic_id=' . $eqLogic_id . '">{{Semaine}}</a> ';
              } else {
                echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=week&eqLogic_id=' . $eqLogic_id . '">{{Semaine}}</a> ';
              }
              if (init('groupBy', 'day') == 'month') {
                echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=month&eqLogic_id=' . $eqLogic_id . '">{{Mois}}</a> ';
              } else {
                echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=month&eqLogic_id=' . $eqLogic_id . '">{{Mois}}</a> ';
              }
              if (init('groupBy', 'day') == 'year') {
                echo '<a class="btn btn-primary btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=year&eqLogic_id=' . $eqLogic_id . '">{{Année}}</a> ';
              } else {
                echo '<a class="btn btn-default btn-sm" href="index.php?v=d&m=suiviCO2&p=panel&groupBy=year&eqLogic_id=' . $eqLogic_id . '">{{Année}}</a> ';
              }
              ?>
            </div>

          </span>

      </legend>
    </div>

    <?php
    $eqLogic = eqLogic::byId($eqLogic_id);
    $conso_type = $eqLogic->getConfiguration('conso_type');

    $costAbo = str_replace(',', '.', $eqLogic->getConfiguration('costAbo')); // si on a une , au lieu d'un . on va la remplacer
    $costHP = str_replace(',', '.', $eqLogic->getConfiguration('costHP'));
    $costHC = str_replace(',', '.', $eqLogic->getConfiguration('costHC'));

    if($costAbo == '' && $costHP == '' && $costHC == ''){
      $no_cost = true;
    } else {
      $no_cost = false;
    }

    sendVarToJs('conso_type', $conso_type);
    sendVarToJs('cost_to_display', !$no_cost);

    if($conso_type == 'elec' && !$no_cost) { // type elec et on a des couts
      echo '
      <!-- moitier haute avec 2 graphs en ligne -->
      <div class="row">
        <div class="col-lg-6">
          <legend><i class="fas fa-bolt"></i>  {{Mes émissions gCO2}}</legend>
          <div id="div_chartConsoCO2"></div>
        </div>
        <div class="col-lg-6">
          <legend><i class="fas fa-euro-sign"></i>  {{Mes coûts €}}</legend>
          <div id="div_chartCost"></div>
        </div>
      </div>

      <!-- moitier basse avec 2 graphs en ligne -->
      <div class="row">
        <div class="col-lg-6">
          <legend><i class="fas fa-leaf"></i>  {{gCO2 émis par kWh en France}}</legend>
          <div id="div_chartCO2parkWh"></div>
        </div>
        <div class="col-lg-6">
          <legend><i class="fas fa-bolt"></i>  {{Ma conso kWh}}</legend>
          <div id="div_chartConsokWh"></div>
        </div>
      </div>
      ';
    } elseif ($conso_type == 'elec' && $no_cost) { // type elect mais pas de couts
      echo '
      <!-- moitier haute avec 1 graph en ligne -->
      <div class="row">
        <div class="col-lg-12">
          <legend><i class="fas fa-bolt"></i>  {{Mes émissions gCO2}}</legend>
          <div id="div_chartConsoCO2"></div>
        </div>
      </div>

      <!-- moitier basse avec 1 graph en ligne -->
      <div class="row">
      <div class="col-lg-6">
        <legend><i class="fas fa-leaf"></i>  {{gCO2 émis par kWh en France}}</legend>
        <div id="div_chartCO2parkWh"></div>
      </div>
        <div class="col-lg-6">
          <legend><i class="fas fa-bolt"></i>  {{Ma conso kWh}}</legend>
          <div id="div_chartConsokWh"></div>
        </div>
      </div>
      ';
    } elseif ($conso_type != 'elec' && !$no_cost) { // pas elec mais des couts
      echo '
      <!-- moitier haute avec 1 graph en ligne -->
      <div class="row">
        <div class="col-lg-12">
          <legend><i class="fas fa-bolt"></i>  {{Mes émissions gCO2}}</legend>
          <div id="div_chartConsoCO2"></div>
        </div>
      </div>

      <!-- moitier basse avec 2 graphs en ligne -->
      <div class="row">
        <div class="col-lg-6">
          <legend><i class="fas fa-euro-sign"></i>  {{Mes coûts €}}</legend>
          <div id="div_chartCost"></div>
        </div>
        <div class="col-lg-6">
          <legend><i class="fas fa-bolt"></i>  {{Ma conso kWh}}</legend>
          <div id="div_chartConsokWh"></div>
        </div>
      </div>
      ';
    } elseif ($conso_type != 'elec' && $no_cost) { // pas elec et pas de couts
      echo '
      <!-- moitier haute avec 1 graph en ligne -->
      <div class="row">
        <div class="col-lg-12">
          <legend><i class="fas fa-bolt"></i>  {{Mes émissions gCO2}}</legend>
          <div id="div_chartConsoCO2"></div>
        </div>
      </div>

      <!-- moitier basse avec 1 graph en ligne -->
      <div class="row">
        <div class="col-lg-12">
          <legend><i class="fas fa-bolt"></i>  {{Ma conso kWh}}</legend>
          <div id="div_chartConsokWh"></div>
        </div>
      </div>
      ';
    }
    ?>


    <br/>

  </div>

</div>




<?php include_file('desktop', 'panel', 'js', 'suiviCO2');?>
