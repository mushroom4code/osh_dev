<?php
namespace PickPoint\DeliveryService\Admin;

use \PickPoint\DeliveryService\Bitrix\Adapter;
use \PickPoint\DeliveryService\Bitrix\Tools;
use \PickPoint\DeliveryService\RegistryHandler;
use \PickPoint\DeliveryService\Option;
use \Bitrix\Main\Localization\Loc;

/**
 * Class RegistryForm
 * @package PickPoint\DeliveryService\Admin
 */
class RegistryForm
{
	protected static $MODULE_ID  = 'pickpoint.deliveryservice';
	protected static $MODULE_LBL = 'PICKPOINT_DELIVERYSERVICE_';
		
	protected static $ikn = false;	
	protected static $city = false;
	protected static $cities = [];	
	protected static $gettingType = false;
	
	/**
     * Make registry create form "window"
     */
	public static function makeFormWindow($gettingType)
	{        
        \CJSCore::Init(array('jquery'));

		self::$ikn = Option::get('pp_ikn_number');
		self::$city = RegistryHandler::getRegistryCityByName(Option::get('pp_from_city'));
		self::$cities = RegistryHandler::getRegistryCityList();
		self::$gettingType = $gettingType;
		
        self::generateFormHtml();
        self::loadFormCSS();
        self::loadFormJS();
    }
	
	/**
     * Generate HTML for registry create form 
     */
    protected static function generateFormHtml()
    {
		$gettingTypes = Adapter::getGettingTypes();		
        ?>
        <div id="<?=self::$MODULE_LBL?>FORMCONTAINER">			
			<table class="adm-detail-content-table edit-table" id="registry_create_edit_table" style="opacity: 1;">							
				<tr>
					<td width='170px;' class='adm-detail-content-cell-r'><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_LBL_registry_ikn')?></td>
					<td class='adm-detail-content-cell-r'><?=self::$ikn?><input type='hidden' id='<?=self::$MODULE_LBL?>registry_ikn' value='<?=self::$ikn?>'></td>
				</tr>
				<tr>
					<td width='170px;' class='adm-detail-content-cell-r'><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_LBL_registry_getting_type')?></td>
					<td class='adm-detail-content-cell-r'><?=$gettingTypes[self::$gettingType]?><input type='hidden' id='<?=self::$MODULE_LBL?>registry_getting_type' value='<?=self::$gettingType?>'></td>
				</tr>
				<tr>
					<td width='170px;' class='adm-detail-content-cell-r'><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_LBL_registry_transfer_city')?></td>
					<td class='adm-detail-content-cell-r'><?=Tools::makeSelect(self::$MODULE_LBL.'registry_transfer_city', self::$cities, self::$city);?></td>
				</tr>
				<tr>
					<td colspan='2'>
						<?php Tools::placeFAQ('REGISTRY_ABOUT');?>
					</td>
				</tr>
			</table>			
        </div>
        <?php
    }
	
	/**
     * Registry create form JS. Pretty Mebiys scripts inside.
     */
    protected static function loadFormJS()
    {
		?>
		<script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
		<script type="text/javascript">
			var <?=self::$MODULE_LBL?>export = new pickpoint_deliveryservice_adminInterface({
				'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
				'label'    : '<?=self::$MODULE_ID?>',
				'logging'  : true
			});
			
			<?=self::$MODULE_LBL?>export.addPage('orders',{
				init: function(){
					this.onMakeRegistry(this);			
				},               
				
				makeRegistry: function(orders){
					var data = this.getInputs();
					
					if (data.success) {
						<?=self::$MODULE_LBL?>export.ajax({
							data: this.self.concatObj(data.inputs, {<?=self::$MODULE_LBL?>action: 'makeRegistryRequest',
								orders: orders
							}),
							dataType: 'json',
							success: this.onMakeRegistry
						});
					}
					else {
						// but how?
						alert('Not all form fields filled');						
					}
				},
				
				getInputs: function (giveAnyway) {
					var depths = this.dependences();

					var data = {
						inputs: {},
						errors: {}
					};

					for (var i in depths) {
						if (typeof(depths[i].need) !== 'undefined') {
							var preVal = $('#<?=self::$MODULE_LBL?>' + i).val();
							if ($('#<?=self::$MODULE_LBL?>' + i).attr('type') === 'checkbox')
								preVal = ($('#<?=self::$MODULE_LBL?>' + i).attr('checked')) ? true : false;
							if (typeof(depths[i].link) !== 'undefined') {
								var checkVal = $('#<?=self::$MODULE_LBL?>' + depths[i].link).val();
								if ($('#<?=self::$MODULE_LBL?>' + depths[i].link).attr('type') === 'checkbox')
									checkVal = ($('#<?=self::$MODULE_LBL?>' + i).attr('checked')) ? true : false;
							}
							switch (depths[i].need) {
								case 'dep' :
									if (preVal)
										data.inputs[i] = preVal;
									else if (!checkVal)
										data.errors[i] = i;
									break;
								case 'sub' :
									if (checkVal) {
										if (preVal)
											data.inputs[i] = preVal;
										else
											data.errors[i] = i;
									}
									break;
								case true :
									if (preVal)
										data.inputs[i] = preVal;
									else
										data.errors[i] = i;
									break;
								case false :
									if (preVal)
										data.inputs[i] = preVal;
									break;
							}
						}
					}           

					if (this.self.isEmpty(data.errors) || (typeof(giveAnyway) !== 'undefined' && giveAnyway))
						return {success: true, inputs: data.inputs};
					else
						return {success: false, errors: data.errors};
				}, 
				
				onMakeRegistry: (function (self) {
					self.onMakeRegistry = function (data) {
						if (data.success === 'Y') {
							window.open(data.url);
							alert('<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_REGISTRY_CREATED')?>' + data.registryNumber);							
							window.location.reload();
						}
						else {
							var str = '<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_REGISTRY_NOTCREATED')?>' + "\n";
							if (typeof(data.error) !== 'undefined') {
								for (var i in data.error)
									str += "\n" + data.error[i];
							}

							alert(str);
						}
					};
				}),
				
				dependences: function () {
					var reqs = {
						registry_ikn           : {need: true},
						registry_getting_type  : {need: true},
						registry_transfer_city : {need: true},						
					};            

					return reqs;
				},
			});
			
			$(document).ready(<?=self::$MODULE_LBL?>export.init);
		</script>		
        <?php
    }

	/**
     * Registry create form form CSS
     */
    protected static function loadFormCSS()
	{
        Tools::getOptionsCss();
        ?>
        <style>
			#<?=self::$MODULE_LBL?>FORMCONTAINER {
				text-align: left;
				padding: 13px; 
				opacity: 1; 
				background: white;
			}			
			
			#<?=self::$MODULE_LBL?>FORMCONTAINER select {
				width: 260px;
			}			
        </style>
        <?php
    }	
}