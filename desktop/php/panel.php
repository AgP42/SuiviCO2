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

$date = array(
  'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
  'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);

if (is_object($object)) {
  $_GET['object_id'] = $object->getId();
}

sendVarToJs('eqLogic_id', init('28'));
?>

<div class="row row-overflow">

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
            if ($object_li->getId() == $object->getId()) {
              echo '<li style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</li>';
              foreach ($object_li->getEqLogic(true, false, 'suiviCO2') as $eqLogic) {
                echo '<li class="cursor li_object active" ><a href="index.php?v=d&m=suiviCO2&p=panel&eqLogic_id=' . $eqLogic->getId() . '" style="position:relative;left:' . $margin*2 . 'px;">' . $eqLogic->getHumanName(true) . '</a></li>';
              }
            } else {
              echo '<li style="position:relative;left:' . $margin . 'px;">' . $object_li->getHumanName(true) . '</li>';
              foreach ($object_li->getEqLogic(true, false, 'suiviCO2') as $eqLogic) {
                echo '<li class="cursor li_object active" ><a href="index.php?v=d&m=suiviCO2&p=panel&eqLogic_id=' . $eqLogic->getId() . '" style="position:relative;left:' . $margin*2 . 'px;">' . $eqLogic->getHumanName(true) . '</a></li>';
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
        <span class="pull-right">
          {{Du}} <input class="form-control input-sm in_datepicker" id='in_startDate' style="display : inline-block; width: 150px;" value='<?php echo $date['start'] ?>'/> {{au}}
          <input class="form-control input-sm in_datepicker" id='in_endDate' style="display : inline-block; width: 150px;" value='<?php echo $date['end'] ?>'/>
          <a class="btn btn-success btn-sm tooltips" id='bt_validChangeDateSuiviCO2' title="{{Attention une trop grande plage de date peut mettre très longtemps a être calculée ou même ne pas s'afficher}}">{{Ok}}</a>
        </span>
      </legend>
    </div>

    <!-- premiere moitier haute avec 2 graphs en ligne -->
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

    <br/>
    <!-- seconde moitier basse avec 1 graphs -->
    <legend><i class="fas fa-bolt"></i>  {{Mes émissions gCO2}}</legend>
    <div id="div_chartConsoCO2"></div>

  </div>

</div>




<?php include_file('desktop', 'panel', 'js', 'suiviCO2');?>
