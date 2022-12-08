<?php
use Ipol\Fivepost\Bitrix\Tools as Tools;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

CModule::IncludeModule('ipol.fivepost');
CModule::IncludeModule('sale');
CJSCore::Init(array("jquery"));

$module_id = Ipol\Fivepost\AbstractGeneral::getMODULEID();
$LABEL     = Ipol\Fivepost\AbstractGeneral::getMODULELBL();

$arAllOptions = Ipol\Fivepost\Option::toOptions();

$authorized = \Ipol\Fivepost\AuthHandler::isAuthorized(); // если не авторизован - подключается страница с авторизацией

//Restore defaults
if ($USER->IsAdmin() && $_SERVER["REQUEST_METHOD"]=="GET" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
    COption::RemoveOption($module_id);

if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid()){
    if(strlen($RestoreDefaults)>0)
        COption::RemoveOption($module_id);
    else{
        \Ipol\Fivepost\OptionsHandler::clearCache(true); // всегда очищаем кэш, на всякий

        $arErrors = array();

        foreach($arAllOptions as $aOptGroup){
            foreach($aOptGroup as $option){
                $validate = \Ipol\Fivepost\Option::validate($option[0], $_REQUEST[$option[0]]);

                if($validate->isSuccess()){
                    if(\Ipol\Fivepost\Option::checkMultiple($option[0]))
                        $_REQUEST[$option[0]] = isset($_REQUEST[$option[0]]) ? serialize($_REQUEST[$option[0]]) : serialize([]);

                    if(in_array('image',$option[3])){
                        $arPICTURE = $_FILES[$option[0]];
                        $arPICTURE["del"] = ${$option[0]."_del"};
                        $arPICTURE["MODULE_ID"] = $module_id;

                        if ($old_fid = \Ipol\Fivepost\Option::get($option[0]));
                            $arPICTURE["old_file"] = $old_fid;
                        $checkRes = CFile::CheckImageFile($arPICTURE, 0, 0, 0);
                        if (strlen($checkRes) <= 0)
                        {
                            $fid = CFile::SaveFile($arPICTURE, $module_id);
                            if ($arPICTURE["del"] == "Y" || strlen($_FILES[$option[0]]["name"]) > 0){
                                \Ipol\Fivepost\Option::set($option[0],intval($fid));
                            }
                        }
                    } else {
                        __AdmSettingsSaveOption($module_id, $option);
                    }
                } else {
                    $arErrors []= Tools::getMessage('OPT_'.$option[0]).': '.$validate->getErrorText();
                }
            }
        }

        if(count($arErrors)){
            ?><table><?
            Tools::placeErrorLabel(implode('<br>',$arErrors),Tools::getMessage('ERROR_OPTSAVE_TITLE'));
            ?></table><?
        }
    }

    if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
        echo '<script type="text/javascript">window.location="'.CUtil::addslashes($_REQUEST["back_url_settings"]).'";</script>';
}

if($authorized){
    $arTabs = array(
            // файл с FAQ
        array(
            "DIV" => "edit1",
            "TAB" => Tools::getMessage("TAB_FAQ"),
            "TITLE" => Tools::getMessage("TAB_TITLE_FAQ"),
            "PATH" => Tools::defaultOptionPath() . "FAQ.php"
        ),
            // файл с основными опциями
        array(
            "DIV" => "edit2",
            "TAB" => GetMessage("MAIN_TAB_SET"),
            "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"),
            "PATH" => Tools::defaultOptionPath() . "setups.php"
        ),
            // файл с правами на доступ к модулю
        array(
            "DIV" => "edit3",
            "TAB" => Tools::getMessage('TAB_RIGHRTS'),
            "TITLE" => Tools::getMessage('TAB_TITLE_RIGHRTS'),
            "PATH" => Tools::defaultOptionPath() . "rights.php"
        )
    );

    if(\Ipol\Fivepost\Option::get('debug') === 'Y'){
        $arTabs []= array(
            "DIV" => "edit4",
            "TAB" => Tools::getMessage("TAB_DEBUG"),
            "TITLE" => Tools::getMessage("TAB_DEBUG_FAQ"),
            "PATH" => Tools::defaultOptionPath() . "debug.php"
        );
    }

    $_arTabs = array();
    // событие на сотворение табов, чтобы можно было расширять настройками
    foreach(GetModuleEvents($module_id,"onTabsBuild",true) as $arEvent)
        ExecuteModuleEventEx($arEvent,Array(&$_arTabs));

    $divId = count($arTabs);
    if(!empty($_arTabs))
        foreach($_arTabs as $tabName => $path)
            $arTabs[]=array("DIV" => "edit".(++$divId), "TAB" => $tabName, "TITLE" => $tabName, "PATH" => $path);

}else{
    $arTabs = array(
        array(
            "DIV"   => "edit1",
            "TAB"   => Tools::getMessage('TAB_AUTH'),
            "TITLE" => Tools::getMessage('TAB_TITLE_AUTH'),
            "PATH"  => Tools::defaultOptionPath() . "auth.php"
        ),
    );
}


function ShowParamsHTMLByArray($arParams,$isHidden = false){
    global $module_id;
    global $LABEL;
    if($isHidden){
        ob_start();
    }

    foreach($arParams as $Option){
        switch ($Option[3][0]){
            case 'selectbox' :
                // вывод селекта
                $optVal     = Ipol\Fivepost\Option::get($Option[0]);
                $selectVals = Ipol\Fivepost\Option::getSelectVals($Option[0]);
                $attrs      = '';
                $solo       = false;

                // если надо как-то по-особому выводить это дело
                switch($Option['0']){
                    case 'payNal'  :
                    case 'payCard' :
                        $attrs = "multiple='multiple' size='5'";
                        break;
                }

                if($solo)
                    Tools::placeOptionRow(false,(($selectVals) ? Tools::makeSelect($Option['0'],$selectVals,$optVal,$attrs) : $optVal));
                else
                    Tools::placeOptionRow($Option['1'],(($selectVals) ? Tools::makeSelect($Option['0'],$selectVals,$optVal,$attrs) : $optVal));
            break;
            case 'textbox' :
                Tools::placeOptionRow($Option[1],"<textarea name='".$Option['0']."' id='".$Option['0']."'>".Ipol\Fivepost\Option::get($Option['0'])."</textarea>");
            break;
            case 'sign' :
                Tools::placeOptionRow($Option[1],Ipol\Fivepost\Option::get($Option['0'])."<input name='".$Option['0']."' type='hidden' value='".Ipol\Fivepost\Option::get($Option['0'])."' id='".$Option['0']."'/>");
            break;
            case 'image' :
                $optVal     = Ipol\Fivepost\Option::get($Option[0]);
                $optValDisplay = CFile::InputFile(htmlspecialcharsbx($Option[0]), 20, $optVal).'<br>'.CFile::ShowImage($optVal, 100, 100, "border=0", "", true);
                Tools::placeOptionRow($Option[1],$optValDisplay);
            break;
            case 'special' :
                switch ($Option[0]){
                    case 'sync_data_lastdate' :
                        $optVal = Ipol\Fivepost\Option::get($Option[0]);
                        $text   = ((int)$optVal) ? date("H:i:s d.m.Y",(int)$optVal) : Tools::getMessage('LBL_NOSUNC');
                        Tools::placeOptionRow($Option[1], $text."<input type='hidden' id='".$Option[0]."' name='".$Option[0]."' value='".$optVal."'>");
                    break;
                }
            break;
            default :
                __AdmSettingsDrawRow($module_id, $Option);
            break;
        }

        if(
            $Option['0'] == 'status_error'
        ){
            echo '<tr><td colspan="2"><hr></td></tr>';
        }
    }

    if($isHidden){
        // если опция скрыта - не покажем ее, пока не кликнут по кой-чаму
        $DATAS = ob_get_contents();
        ob_end_clean();
        echo str_replace('<tr',"<tr class='{$GLOBALS['LABEL']}hidden'",$DATAS);
    }
}

$tabControl = new \CAdminTabControl("tabControl", $arTabs);
?>
<script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
<script>
    // инициализируем объект для работы с опциями
    var <?=$LABEL?>setups = new i5post_adminInterface({
        'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
        'label'    : '<?=$module_id?>',
        'logging'  : true
    });
    $(document).ready(<?=$LABEL?>setups.init);
</script>
<?Tools::getCommonCss();?>
<style>
     .ipol_header {
         font-size: 16px;
         cursor: pointer;
         display:block;
         color:#2E569C;
         text-decoration: underline;
     }
    .ipol_inst {
        display:none;
        margin-left:10px;
        margin-top: 10px;
        margin-bottom: 10px;
        color: #555;
    }
    .ipol_smallHeader{
        cursor: pointer;
        display:block;
        color:#2E569C;
    }
    .ipol_subFaq{
        margin-bottom:10px;
    }
    .<?=$LABEL?>subHeading td{
         padding: 8px 70px 10px !important;
         background-color: #EDF7F9;
         border-top: 11px solid #F5F9F9;
         border-bottom: 11px solid #F5F9F9;
         color: #4B6267;
         font-size: 14px;
         font-weight: bold;
         text-align: center !important;
         text-shadow: 0px 1px #FFF;
    }
     .ipol_borderBottom {
        border-bottom: 1px dotted black;
     }

    .<?=$LABEL?>headerLink{
        cursor: pointer;
        text-decoration: underline;
    }
    .<?=$LABEL?>WH_coords{
        width: 60px;
    }
    .<?=$LABEL?>closer{
        background-image: url('<?=Tools::getImagePath()?>closer.png');
        width  : 15px;
        height : 15px;
        background-position: 0px -15px;
        cursor:pointer;
    }
    .<?=$LABEL?>closer:hover{
        background-position: 0px 0px;
    }

    img{border: 1px dotted black;}

    .ipol_adminButtonPanel {
        text-align: left;
        padding: 13px;
        opacity: 1;
        background: white;
        margin-bottom: 10px;
    }
</style>

<?
// место для вывода глобальных ошибок в работемодуля
    if($authorized) {
        if (class_exists('\Bitrix\Main\UI\Extension'))
            \Bitrix\Main\UI\Extension::load("ui.buttons");
        else
            \CJSCore::Init("ui.buttons");

        $isSyncCompleted = (\Ipol\Fivepost\Option::get('sync_data_completed') == 'Y');

        if (false) {
            ?><table><?
            Tools::placeErrorLabel(Tools::getMessage('ERROR_NODELIVERY_DESCR'), Tools::getMessage('ERROR_NODELIVERY_TITLE'));
            ?></table><?
        }

        $buttonPanel = '<div class="ipol_adminButtonPanel">';

        if ($isSyncCompleted)
            $buttonPanel .= '<button onclick=\'window.open("/bitrix/admin/ipol_fivepost_orders.php?lang='.LANGUAGE_ID.'");\' class="ui-btn ui-btn-success">'.Tools::getMessage('LBL_toOrders').'</button>';

        $btnSyncClass = $isSyncCompleted ? 'ui-btn-secondary' : 'ui-btn-danger';
        $buttonPanel .= '<button onclick=\'window.open("/bitrix/admin/ipol_fivepost_sync_data.php?lang='.LANGUAGE_ID.'");\' class="ui-btn '.$btnSyncClass.'">'.Tools::getMessage('LBL_toSunc').'</button>';
        $buttonPanel .= '</div>';

        echo $buttonPanel;

        if (!$isSyncCompleted)
        {
            ?><table><?
            Tools::placeErrorLabel(Tools::getMessage('ERROR_SYNC_DATA_REQUIRED_DESCR'), Tools::getMessage('ERROR_SYNC_DATA_REQUIRED_TITLE'));
            ?></table><?
        }

        if (!\Ipol\Fivepost\Warhouses::getWHInfo()) {
            ?><table><?
            Tools::placeErrorLabel(Tools::getMessage('ERROR_NOWARHOUSES_DESCR'), Tools::getMessage('ERROR_NOWARHOUSES_TITLE'));
            ?></table><?
        }

        $checkEmptyDels = Ipol\Fivepost\Bitrix\Handler\Deliveries::getProfilesWithUnconfiguredRateType(false);
        if (!empty($checkEmptyDels)) {
            $strWarn = '';
            foreach ($checkEmptyDels as $arProile) {
                $strWarn .= $arProile['NAME'].' (<a href="'.$arProile['LINK'].'" target="_blank">'.$arProile['ID'].'</a>)<br>';
            }
            ?><table><?
            Tools::placeWarningLabel(Tools::getMessage('ERROR_NOTARIFSSETTED_DESCR').$strWarn, Tools::getMessage('ERROR_NOTARIFSSETTED_TITLE'));
            ?></table><?
        }
    }
?>

<form method="post" enctype="multipart/form-data" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?echo LANG?>">
<?
// подключаем табы
    $tabControl->Begin();
    foreach($arTabs as $arTab){
        $tabControl->BeginNextTab();
        include_once($_SERVER['DOCUMENT_ROOT'].$arTab["PATH"]);
    }

    $tabControl->Buttons();

// сохранение - только если авторизован
    if($authorized) {?>
        <div align="left">
            <input type="hidden" name="Update" value="Y">
            <input type="submit" <?if(!$USER->IsAdmin())echo " disabled ";?> name="Update" value="<?echo GetMessage("MAIN_SAVE")?>">
        </div>
    <?}?>
    <?$tabControl->End();?>
	<div style='text-align: right'>
		<?=Tools::getMessage('LBL_COPYRIGHT')?> <a href='https://ipol.ru/' target='_blank'><img style="border:none" src='<?=Tools::getImagePath()?>ipol.png'></a>
	<div>
    <?=bitrix_sessid_post();?>
</form>