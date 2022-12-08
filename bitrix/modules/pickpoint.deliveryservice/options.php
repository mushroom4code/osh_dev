<?php
use \Bitrix\Main\Localization\Loc;
use \PickPoint\DeliveryService\Bitrix\Tools;
use \PickPoint\DeliveryService\Option;

Loc::loadMessages(__FILE__);

CJSCore::Init(array("jquery"));

$module_id = 'pickpoint.deliveryservice';
require $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/constants.php';
/** @global array $arServiceTypes - from constants */

// Shine new options array
$arAllOptions = Option::makeOptions();

/** @global CMain $APPLICATION */
/** @global string $mid */
$CAT_RIGHT = $APPLICATION->GetGroupRight($module_id);
if ($CAT_RIGHT >= 'R'):
    include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/fields.php';
    global $MESS;
    global $arOptions;    
    include_once $GLOBALS['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/include.php';

	//echo '<pre>'.print_r($arOptions, true).'</pre>';
	//echo '<pre>'.print_r($_REQUEST, true).'</pre>';	
	
	/*$ar = [];
	$oc = Option::collection();
	foreach ($oc as $name => $data)
	{
		$ar['OPTIONS'][$name] = Option::get($name);
	}
	echo '<pre>'.print_r($ar, true).'</pre>';
	*/
	
	// Check required options
	if (!Option::isRequiredOptionsSet())
	{
		$optionsNotSetMessage = new CAdminMessage([
			'MESSAGE' => GetMessage("PP_MODULE_OPTIONS_NOT_SET"), 
			'TYPE' => 'ERROR', 
			'DETAILS' => GetMessage("PP_MODULE_OPTIONS_NOT_SET_TEXT"), 
			'HTML' => true
			]);
		echo $optionsNotSetMessage->Show();
	}	
	
    $REQUEST_METHOD  = $_SERVER['REQUEST_METHOD'];
    $Update          = (array_key_exists('Update', $_REQUEST)) ? $_REQUEST['Update'] : false;
    $RestoreDefaults = (array_key_exists('RestoreDefaults', $_REQUEST)) ? $_REQUEST['RestoreDefaults'] : false;
	
    if ($CAT_RIGHT >= 'W' && check_bitrix_sessid()) 
	{
        if ($REQUEST_METHOD == 'GET' && strlen($RestoreDefaults) > 0) 
		{
            COption::RemoveOption($module_id);
            $z = CGroup::GetList($v1 = 'id', $v2 = 'asc', array('ACTIVE' => 'Y', 'ADMIN' => 'N'));
            while ($zr = $z->Fetch()) {
                $APPLICATION->DelGroupRight($module_id, array($zr['ID']));
            }
        }
        if ($REQUEST_METHOD == 'POST' && strlen($Update) > 0) 
		{
            ob_start();
            require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/admin/group_rights.php');
            ob_end_clean();
			
			if (($errorsDetected = Option::validateRequiredOptions($_REQUEST)) !== false)
			{
				$APPLICATION->ThrowException(Loc::getMessage('PP_ERROR_OPTONS_DONT_SAVED').'<br><br>'.$errorsDetected);
					
            //if (!CheckPickpointLicense($_REQUEST['pp_ikn_number'])) {
            //   $APPLICATION->ThrowException(Loc::getMessage('PP_WRONG_KEY'));
            } 
			else 
			{
				foreach ($arAllOptions as $key => $aOptGroup)
				{
					// Handle only shine new options
					if (!in_array($key, ['statuses', 'orderSending', 'common']))
						continue;
					
					foreach($aOptGroup as $option)
					{
						// Handle multiple selects
						if (Option::checkMultiple($option[0]))
							$_REQUEST[$option[0]] = serialize($_REQUEST[$option[0]]);
						
						__AdmSettingsSaveOption($module_id, $option);
					}
				}				
				
                foreach ($_REQUEST as $sCode => $Value) {
                    if (in_array($sCode, array_keys($arOptions['OPTIONS']))) {
                        if ($Value) {
                            if (is_array($Value)) {
                                $Value = serialize($Value);
                            }

                            if (!COption::SetOptionString($module_id, $sCode, $Value)) {
                                $arOptions['OPTIONS'][$sCode] = $Value;
                            }
                        } else {
                            COption::SetOptionString($module_id, $sCode, '');
                        }
                    }
                }
                if ($_REQUEST['pp_add_info']) {
                    COption::SetOptionString($module_id, 'pp_add_info', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_add_info', 0);
                }

                if ($_REQUEST['pp_order_phone']) {
                    COption::SetOptionString($module_id, 'pp_order_phone', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_order_phone', 0);
                }

                if ($_REQUEST['pp_order_city_status']) {
                    COption::SetOptionString($module_id, 'pp_order_city_status', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_order_city_status', 0);
                }

                if ($_REQUEST['pp_city_location']) {
                    COption::SetOptionString($module_id, 'pp_city_location', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_city_location', 0);
                }

                if ($_REQUEST['pp_use_coeff']) {
                    COption::SetOptionString($module_id, 'pp_use_coeff', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_use_coeff', 0);
                }

                if ($_REQUEST['pp_test_mode']) {
                    COption::SetOptionString($module_id, 'pp_test_mode', 1);
                } else {
                    COption::SetOptionString($module_id, 'pp_test_mode', 0);
                }

                $arTableOptions = $_REQUEST['OPTIONS'];

                foreach ($arTableOptions as $iPT => $arPersonTypeValues) {
                    foreach ($arPersonTypeValues as $sValueCode => $arValues) {
                        foreach ($arValues as $iKey => $arValueList) {
                            if ($arValueList['TYPE'] == 'ANOTHER') {
                                $arTableOptions[$iPT][$sValueCode][$iKey]['VALUE'] = $arValueList['VALUE_ANOTHER'];
                            }
                            unset($arTableOptions[$iPT][$sValueCode][$iKey]['VALUE_ANOTHER']);
                        }
                    }
                }
                COption::SetOptionString($module_id, 'OPTIONS', serialize($arTableOptions));

                // looks useless
				if (!empty($_REQUEST['CITIES'])) {
                    foreach ($_REQUEST['CITIES'] as $arCityFields) {
                        if (intval($arCityFields['BX_ID'])) {
                            $arCityFields['PRICE'] = floatval($arCityFields['PRICE']);
                            if (!isset($arCityFields['ACTIVE'])) {
                                $arCityFields['ACTIVE'] = 'N';
                            }
                            CPickpoint::SetPPCity($arCityFields['PP_ID'], $arCityFields);
                        }
                    }
                }
				// --

                if (!empty($_REQUEST['ZONES'])) {
                    foreach ($_REQUEST['ZONES'] as $zoneId => $arZoneFields) {
                        $arZoneFields['PRICE'] = floatval($arZoneFields['PRICE']);
                        CPickpoint::SetPPZone($zoneId, $arZoneFields);
                    }
                }
                $arOptions = array();
                if (!(COption::GetOptionString($module_id, 'pp_service_types_all', ''))) {
                    COption::SetOptionString($module_id, 'pp_service_types_all', serialize($arServiceTypes));
                }
                
                $iTimestamp = COption::GetOptionInt($module_id, 'pp_city_download_timestamp', 0);

                if (time() > $iTimestamp
                    || !file_exists(
                        $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pickpoint.deliveryservice/cities.csv'
                    )
                ) {
                    CPickpoint::GetCitiesCSV();
                }

                $arOptions = array();
                $arOptions['OPTIONS']['pp_add_info'] = COption::GetOptionString($module_id, 'pp_add_info', '1');
                $arOptions['OPTIONS']['pp_order_phone'] = COption::GetOptionString($module_id, 'pp_order_phone', '1');
                $arOptions['OPTIONS']['pp_order_city_status'] = COption::GetOptionString($module_id, 'pp_order_city_status', '1');
                $arOptions['OPTIONS']['pp_city_location'] = COption::GetOptionString($module_id, 'pp_city_location', '1');
                $arOptions['OPTIONS']['pp_ikn_number'] = COption::GetOptionString($module_id, 'pp_ikn_number', '');
                $arOptions['OPTIONS']['pp_enclosure'] = COption::GetOptionString($module_id, 'pp_enclosure', '');
                $arOptions['OPTIONS']['pp_service_types_selected'] = COption::GetOptionString(
                    $module_id,
                    'pp_service_types_selected'
                );
                $arOptions['OPTIONS']['pp_service_types_all'] = COption::GetOptionString(
                    $module_id,
                    'pp_service_types_all'
                );                			
				
				$arOptions['OPTIONS']['pp_term_inc'] = Option::get('pp_term_inc');
				$arOptions['OPTIONS']['pp_postamat_picker'] = Option::get('pp_postamat_picker');
				
                $arOptions['OPTIONS']['pp_zone_count'] = COption::GetOptionString($module_id, 'pp_zone_count', 10);
                $arOptions['OPTIONS']['pp_from_city'] = COption::GetOptionString($module_id, 'pp_from_city', '');
                
                $arOptions['OPTIONS']['pp_use_coeff'] = COption::GetOptionString($module_id, 'pp_use_coeff', '');
                $arOptions['OPTIONS']['pp_custom_coeff'] = COption::GetOptionString($module_id, 'pp_custom_coeff', '');
                $arOptions['OPTIONS']['pp_api_login'] = COption::GetOptionString($module_id, 'pp_api_login', '');
                $arOptions['OPTIONS']['pp_api_password'] = COption::GetOptionString($module_id, 'pp_api_password', '');
                $arOptions['OPTIONS']['pp_test_mode'] = COption::GetOptionString($module_id, 'pp_test_mode', '');
                $arOptions['OPTIONS']['pp_free_delivery_price'] = COption::GetOptionString(
                    $module_id,
                    'pp_free_delivery_price',
                    ''
                );
                LocalRedirect($APPLICATION->GetCurPageParam());
            }
        }
    }
	
	$arServiceTypes = strlen($arOptions['OPTIONS']['pp_service_types_all']) ? unserialize(
        $arOptions['OPTIONS']['pp_service_types_all']
    ) : $arServiceTypes;	
    $arSelectedST = strlen($arOptions['OPTIONS']['pp_service_types_selected']) ? unserialize(
        $arOptions['OPTIONS']['pp_service_types_selected']
    ) : array();	    
			
    $arTableOptions = (unserialize(COption::GetOptionString($module_id, 'OPTIONS')));
	if (!$arTableOptions)
		$arTableOptions = array();
	
    if (isset($_REQUEST['OPTIONS'])) {
        $arTableOptions = $_REQUEST['OPTIONS'];
    }
    foreach ($arOptions['OPTIONS'] as $sKey => $sValue) {
        if (isset($_REQUEST[$sKey])) {
            $arOptions['OPTIONS'][$sKey] = (is_array($_REQUEST[$sKey])) ? serialize($_REQUEST[$sKey])
                : $_REQUEST[$sKey];
        }
    }		
	
	// Tabs in module options
    $arTabs = array(
		array(
            'DIV'   => 'edit1',
            'TAB'   => Loc::getMessage('PPOINT_FAQ_TAB'),            
			'ICON'  => 'support_settings',
            'TITLE' => Loc::getMessage('PPOINT_FAQ_TAB_TITLE'),
			'PATH'  => Tools::defaultOptionPath()."FAQ.php",
        ),
        array(
            'DIV'   => 'edit2',
            'TAB'   => Loc::getMessage('MAIN_TAB_SET'),
            'ICON'  => 'support_settings',
            'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET'),
			'PATH'  => Tools::defaultOptionPath()."setup.php",
        ),
        array(
            'DIV'   => 'edit3',
            'TAB'   => Loc::getMessage('PPOINT_ZONES_TAB'),
            'ICON'  => 'support_settings',
            'TITLE' => Loc::getMessage('PPOINT_ZONES_TAB_TITLE'),
			'PATH'  => Tools::defaultOptionPath()."zones.php",
        ),
		array(
            'DIV'   => 'edit4',
            'TAB'   => Loc::getMessage('PPOINT_STATUS_TAB'),
            'ICON'  => 'support_settings',
            'TITLE' => Loc::getMessage('PPOINT_STATUS_TAB_TITLE'),
			'PATH'  => Tools::defaultOptionPath()."status.php",
        ),
        array(
            'DIV'   => 'edit5',
            'TAB'   => Loc::getMessage('MAIN_TAB_RIGHTS'),
            'ICON'  => 'pickpoint_settings',
            'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_RIGHTS'),
			'PATH'  => Tools::defaultOptionPath()."rights.php",
        ),
    );
	
	// Allows creating custom tabs using the onTabsBuild module event
	$_arTabs = array();
    foreach (GetModuleEvents($module_id, "onTabsBuild", true) as $arEvent)	
        ExecuteModuleEventEx($arEvent, Array(&$_arTabs));

    $divId = count($arTabs);
    if (!empty($_arTabs))
	{
        foreach ($_arTabs as $tabName => $path)
            $arTabs[] = array("DIV" => "edit".(++$divId), "TAB" => $tabName, "ICON" => "support_settings", "TITLE" => $tabName, "PATH" => $path);
	}
	// --
	
	// Draw option row
	function ShowParamsHTMLByArray($arParams, $isHidden = false)
	{
		global $module_id;
		
		if ($isHidden)
			ob_start();		
		
		foreach ($arParams as $Option)
		{	
			$required = Option::checkRequired($Option[0]);
		
			switch ($Option[3][0])
			{
				case 'selectbox':
					$optVal     = Option::get($Option[0]);
					$selectVals = Option::getVariants($Option[0]);
					$attrs      = '';
					$solo       = false;			
					
					if (Option::checkMultiple($Option[0]))
						$attrs = "multiple='multiple' size='4'";
					
					if ($solo)
						Tools::placeOptionRow(false, (($selectVals) ? Tools::makeSelect($Option[0], $selectVals, $optVal, $attrs) : $optVal), $required);
					else
						Tools::placeOptionRow($Option[1], (($selectVals) ? Tools::makeSelect($Option[0], $selectVals, $optVal, $attrs) : $optVal), $required);
					
					break;
				case 'textbox':
					Tools::placeOptionRow($Option[1], "<textarea name='".$Option[0]."' id='".$Option[0]."'>".Option::get($Option[0])."</textarea>", $required);
					
					break;
				case 'special':					
					// Handle status last sync 
					if ($Option[0] == 'status_last_sync')
					{
						$optVal = Option::get($Option[0]);
						$optVal = ($optVal > 0) ? date("d.m.Y H:i:s", $optVal) : '-';
						
						Tools::placeOptionRow($Option[1], $optVal);
					}					
					break;

				default:
					__AdmSettingsDrawRow($module_id, $Option); 					
					
					break;				
			}			
		}

		if ($isHidden)
		{
			$DATAS = ob_get_contents();
			ob_end_clean();
			echo str_replace("<tr", "<tr class='{$GLOBALS['LABEL']}hidden'", $DATAS);
		}
	}	

	// this looks useless
    ShowNote(COption::GetOptionString('pickpoint', 'comment'), COption::GetOptionString('pickpoint', 'comment_type'));
		
    $tabControl = new CAdminTabControl('tabControl', $arTabs);
    Tools::getOptionsCss();
	?>
	<script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
	<script type="text/javascript">
		var <?=PICKPOINT_DELIVERYSERVICE_LBL?>setups = new pickpoint_deliveryservice_adminInterface({
			'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
			'label'    : '<?=PICKPOINT_DELIVERYSERVICE?>',
			'logging'  : true
		});    
		
		$(document).ready(<?=PICKPOINT_DELIVERYSERVICE_LBL?>setups.init);
	</script>
	<?php
    if ($ex = $APPLICATION->GetException()) {
		CAdminMessage::ShowOldStyleError($ex->GetString());    
	}	
	?>		
    <form method="POST" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANG?>" name="ara">
        <?=bitrix_sessid_post();?>
		<?php
		$tabControl->Begin();
		foreach ($arTabs as $arTab) {
			$tabControl->BeginNextTab();
			include_once($_SERVER['DOCUMENT_ROOT'].$arTab["PATH"]);
		}        
        $tabControl->Buttons();
		?>
		
        <script>
            function RestoreDefaults() {
                if (confirm('<?=addslashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>'))
                    window.location = "<?=$APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?=LANG?>&mid=<?=urlencode($mid)?>&<?=bitrix_sessid_get()?>";
            }
        </script>
        <input type="submit" <?php if ($CAT_RIGHT < 'W') { echo 'disabled'; }?> name="Update" value="<?=Loc::getMessage('MAIN_SAVE');?>" />
        <input type="hidden" name="Update" value="Y" />
        <input type="reset" name="reset" value="<?=Loc::getMessage('MAIN_RESET');?>" />
        <?php $tabControl->End();?>
    </form>
<?php endif;