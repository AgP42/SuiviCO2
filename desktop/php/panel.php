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

sendVarToJs('object_id', $object->getId());
sendVarToJs('energyType', init('energyType', 'electricity'));
sendVarToJs('groupBy', init('groupBy', 'day'));
if (init('groupBy', 'day') == 'day') {
  $date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-31 days ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d')),
  );
}
if (init('groupBy', 'day') == 'month') {
  $date = array(
    'start' => init('startDate', date('Y-m-d', strtotime('-1 year ' . date('Y-m-d')))),
    'end' => init('endDate', date('Y-m-d', strtotime('+1 days' . date('Y-m-d')))),
  );
}
?>








<?php include_file('desktop', 'panel', 'js', 'suiviCO2');?>
