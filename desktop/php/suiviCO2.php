<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('suiviCO2');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());

// initialise les dates du datepicker quand on arrive sur la page : debut 1 mois avant now et fin demain
$date = array(
  'start' => init('startDate', date('Y-m-d', strtotime('-1 month ' . date('Y-m-d')))),
  'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);//*/

// initialise les dates du datepicker Co2_def quand on arrive sur la page : 2 mois avant now
$dateCo2_def = array(
  'start' => init('startDate', date('Y-m', strtotime('-2 month ' . date('Y-m-d')))),
//  'end' => init('endDate', date('Y-m-d', strtotime('+1 days ' . date('Y-m-d')))),
);//*/

?>

<div class="row row-overflow">
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle"></i>
        <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>

    <legend><i class="fas fa-leaf"></i> {{Mes sources d'émission CO2}}</legend>

    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />

    <div class="eqLogicThumbnailContainer">
        <?php
          foreach ($eqLogics as $eqLogic) {
          	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
          	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
          	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
          	echo '<br>';
          	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
          	echo '</div>';
          }
        ?>
    </div>
  </div>

  <div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a><a class="btn btn-default btn-sm eqLogicAction" data-action="copy"><i class="fas fa-copy"></i> {{Dupliquer}}</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
    <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
    <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
    <li role="presentation"><a href="#historytab" aria-controls="history" role="tab" data-toggle="tab"><i class="fa fa-history"></i> {{Historique}}</a></li>
  </ul>
  <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
    <div role="tabpanel" class="tab-pane active" id="eqlogictab">
      <br/>
    <form class="form-horizontal">
      <fieldset>
        <legend><i class="fas fa-tachometer-alt"></i> {{Général}}</legend>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
              <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
            <div class="col-sm-3">
              <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                <option value="">{{Aucun}}</option>
                <?php
                  foreach (jeeObject::all() as $object) {
                  	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
              </select>
            </div>
          </div>

	        <div class="form-group">
            <label class="col-sm-3 control-label">{{Catégorie}}</label>
            <div class="col-sm-9">
                 <?php
                    foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                    echo '<label class="checkbox-inline">';
                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                    echo '</label>';
                    }
                  ?>
            </div>
          </div>

        	<div class="form-group">
        		<label class="col-sm-3 control-label"></label>
        		<div class="col-sm-9">
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
        			<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
        		</div>
        	</div>

        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bolt"></i> {{Type d'énergie}}</legend>

          <div class="form-group">
            <label class="col-sm-2 control-label">{{Type d'énergie}}
            </label>
            <div class="col-sm-2">
              <select class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="conso_type">
                <option value="elec">Electricité</option>
                <option value="gaz">Gaz</option>
                <option value="fioul">Fioul</option>
                <option value="other">Autre</option>
              </select>
            </div>
          </div>

          <div class="choix_taux_co2">
            <div class="form-group">
                <label class="col-sm-2 control-label">{{Emission gCO2 par kWh}}</label>
                <div class="col-sm-2">
                    <input class="eqLogicAttr form-control" data-l1key="configuration"  data-l2key="gCO2_kwh"/>
                </div>
            </div>
        </div>

        </fieldset>
      </form>

      <form class="form-horizontal">
        <fieldset>
          <legend><i class="fas fa-bolt"></i> {{Index consommation}}</legend>

          <div class="form-group">
            <label class="col-sm-2 control-label">{{Index total ou HP}}</label>
            <div class="col-sm-4">
              <div class="input-group">
                <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="index_HP"/>
                <span class="input-group-btn">
                  <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                </span>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label class="col-sm-2 control-label">{{Index HC (facultatif)}}</label>
              <div class="col-sm-4">
                <div class="input-group">
                  <input type="text" class="eqLogicAttr form-control tooltips roundedLeft" data-l1key="configuration" data-l2key="index_HC"/>
                  <span class="input-group-btn">
                    <a class="btn btn-default listCmdInfo roundedRight"><i class="fa fa-list-alt"></i></a>
                  </span>
                </div>
              </div>
            </div>

          </fieldset>
        </form>

        <br>

        <form class="form-horizontal">
          <fieldset>
            <legend><i class="fas fa-euro-sign"></i> {{Coût}}</legend>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Abonnement (€ TTC / mois)}}</label>
              <div class="col-sm-2">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="costAbo" />
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Tarif total ou HP (€ TTC / kWh)}}</label>
              <div class="col-sm-2">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="costHP" />
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-2 control-label">{{Tarif HC (€ TTC / kWh)}}</label>
              <div class="col-sm-2">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="costHC" />
              </div>
            </div>

            </fieldset>
          </form>


      </div>

      <div role="tabpanel" class="tab-pane" id="commandtab">
        <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;"><i class="fa fa-plus-circle"></i> {{Commandes}}</a><br/><br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
            <thead>
                <tr>
                    <th>{{Nom}}</th><th>{{Type}}</th><th>{{Action}}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
      </div>

      <div role="tabpanel" class="tab-pane" id="historytab">
        <br>
        <form class="form-horizontal">
        <fieldset>
          <legend class="historique_api_elec"><i class="fas fa-history"></i> {{Récupérer historique des données CO2 par kWh en France}}</legend>

          <div class="form-group historique_api_elec">
            <label class="col-sm-3 control-label">{{Données temps réel CO2 par kWh en France}}</label>
            <div class="col-sm-4">
              <a class="btn btn-success btn-sm" id="bt_historyCO2"><i class="fas fa-database"></i>{{ Récupérer historique}}</a>
            </div>
            <p class="col-sm-4">{{Il s'agit de 1 ou 2 mois des dernières données telles que publiées en temps réel par l'API}}</p>
          </div>

          <div class="form-group historique_api_elec">
            <label class="col-sm-3 control-label">{{Données consolidées et définitives CO2 par kWh en France}}</label>
            <div class="col-sm-4">
            <span>
              <div>
                {{Sélectionner un jour du mois voulu}} <input class="form-control input-sm in_datepicker_month_year" id='in_startDateCo2_def' style="display : inline-block; width: 150px;" value='<?php echo $dateCo2_def['start'] ?>'/>
                <a class="btn btn-success btn-sm tooltips" id='bt_historyCo2_def'><i class="fas fa-database"></i>{{ Récupérer historique}}</a>
              </div>
            </span>
            </div>
              <p class="col-sm-4">{{Il s'agit des donnés antérieures aux données Temps réel, aprés correction par les fournisseurs. Lancer cette fonction sur des données existantes dans Jeedom les écrasera avec les nouvelles valeurs. Fonction a lancer manuellement de temps à autre !}}</p>
          </div>

          <legend><i class="fas fa-history"></i> {{Récupérer historique de ma conso}}</legend>
          <div class="form-group">
            <label class="col-sm-3 control-label">{{Ma conso kWh}}</label>
            <div class="col-sm-4">
            <span>
              <div>
                {{Période du}} <input class="form-control input-sm in_datepicker" id='in_startDate' style="display : inline-block; width: 150px;" value='<?php echo $date['start'] ?>'/> {{au}}
                <input class="form-control input-sm in_datepicker" id='in_endDate' style="display : inline-block; width: 150px;" value='<?php echo $date['end'] ?>'/>
                <a class="btn btn-success btn-sm tooltips" id='bt_historykWh' title="{{Attention une trop grande plage de date peut mettre très longtemps a être calculée}}"><i class="fas fa-database"></i>{{ Récupérer historique}}</a>
              </div>
            </span>
            </div>
            <p class="col-sm-4">{{Uniquement disponible si les commandes contenant les index étaient déjà historisées dans jeedom !)}}</p>
          </div>

          </fieldset>
        </form>

      </div>


    </div>

  </div>
</div>

<?php include_file('desktop', 'suiviCO2', 'js', 'suiviCO2');?>
<?php include_file('core', 'plugin.template', 'js');?>
