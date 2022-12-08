<?
namespace Ipol\Fivepost\Bitrix;
use Ipol\Fivepost\Admin\Logger;
use Ipol\Fivepost\Bitrix\Entity\Table;


/**
 * Class Tools
 * @package Ipol\Fivepost\Bitrix
 * Общие методы, упрощающие работу с html и прочими фишками модуля
 */
class Tools
{
	private static $MODULE_ID  = IPOL_FIVEPOST;
	private static $MODULE_LBL = IPOL_FIVEPOST_LBL;

    // RIGHTS

    protected static $skipAdminCheck = false;

    /**
     * @param string $min - минимальные права
     * @return bool
     * Проверка на админа
     */
    public static function isAdmin($min = 'W'){
        if(self::$skipAdminCheck) return true;
        $rights = \CMain::GetUserRight(self::$MODULE_ID);
        $DEPTH = array('D'=>1,'R'=>2,'W'=>3);
        return($DEPTH[$min] <= $DEPTH[$rights]);
    }

    /**
     * @return bool
     * Пропускать ли проверку на админа
     */
    public static function isSkipAdminCheck()
    {
        return self::$skipAdminCheck;
    }

    /**
     * @param bool $skipAdminCheck
     */
    public static function setSkipAdminCheck($skipAdminCheck)
    {
        self::$skipAdminCheck = $skipAdminCheck;
    }

    // COMMON

    /**
     * @param $code
     * @return string
     * Получение лэнговой строки
     */
    static function getMessage($code)
    {
        return GetMessage('IPOL_FIVEPOST_'.$code);
    }

    /**
     * @return string
     * Путь к файлам js-а
     */
    static function getJSPath()
    {
        return '/bitrix/js/'.self::$MODULE_ID.'/';
    }

    /**
     * @return string
     * Путь к файлам tools-а
     */
    static function getToolsPath()
    {
        return '/bitrix/tools/'.self::$MODULE_ID.'/';
    }

    /**
     * @return string
     * Путь к файлам с картинками
     */
    static function getImagePath()
    {
        return '/bitrix/images/'.self::$MODULE_ID.'/';
    }

    /**
     * @param $array
     * @return string
     * По факту то же, что и CUtil::PhpToJSObject, только хуже. Лучше не юзать
     */
    static function arrToJs($array){
        if(!is_array($array))
            return $array;
        else{
            $ret = '{';
            foreach($array as $key => $value){
                $ret .= $key.' : "'.self::arrToJs($value).'",';
            }
            $ret .= '}';
            return $ret;
        }
    }

    /**
     * @param $wat
     * @return string
     * Преобразовать данные в json (энкод + кодировка)
     */
    public static function jsonEncode($wat)
    {
        return json_encode(self::encodeToUTF8($wat));
    }

    /**
     * @param $handle
     * @return array
     * Преобразует данные из кодировки сайта в utf-8
     */
    public static function encodeToUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8') {
            if (is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeToUTF8($key);
                    $handle[$key] = self::encodeToUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array(); // why = because
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = ['utf_key' => self::encodeToUTF8($key), 'utf_val' => self::encodeToUTF8($val)];
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $utf_key = $new['utf_key'];
                    $handle->$utf_key = $new['utf_val'];
                }
            }else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, LANG_CHARSET, 'UTF-8');
            }
        }
        return $handle;
    }

    /**
     * @param $handle
     * @return array
     * Преобразует данные из utf-8 в кодировку сайта
     */
    public static function encodeFromUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8'){
            if(is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeFromUTF8($key);
                    $handle[$key] = self::encodeFromUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array();
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = ['site_encode_key' => self::encodeFromUTF8($key), 'site_encode_val' => self::encodeFromUTF8($val)];
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $site_encode_key = $new['site_encode_key'];
                    $handle->$site_encode_key = $new['site_encode_val'];
                }
            } else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, 'UTF-8', LANG_CHARSET);
            }
        }
        return $handle;
    }

    /**
     * @return bool
     * Проверяет, есть ли на хите запрос по аяксу от модуля
     */
    public static function isModuleAjaxRequest() {
        return (array_key_exists(self::$MODULE_LBL.'action',$_REQUEST) && $_REQUEST[self::$MODULE_LBL.'action']);
    }

    /**
     * Подключение общих стилей модуля (опции, админки, итп).
     * Это попапы дял хинтов, стили оповещений и ошибок
     */
    static function getCommonCss(){?>
        <style>
            .<?=self::$MODULE_LBL?>errInput{
                background-color: #ffb3b3 !important;
            }
            .<?=self::$MODULE_LBL?>PropHint, .<?=self::$MODULE_LBL?>PropHint:hover{
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/hint.gif") no-repeat transparent !important;
                text-decoration: none !important;
                display: inline-block;
                height: 12px;
                position: relative;
                width: 12px;
            }
            .<?=self::$MODULE_LBL?>b-popup {
                background-color: #FEFEFE;
                border: 1px solid #9A9B9B;
                box-shadow: 0px 0px 10px #B9B9B9;
                display: none;
                font-size: 12px;
                padding: 19px 13px 15px;
                position: absolute;
                top: 38px;
                width: 300px;
                z-index: 50;
            }
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>pop-text {
                margin-bottom: 10px;
                color:#000;
            }
            .<?=self::$MODULE_LBL?>pop-text i {color:#AC12B1;}
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>close {
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/popup_close.gif") no-repeat transparent;
                cursor: pointer;
                height: 10px;
                position: absolute;
                right: 4px;
                top: 4px;
                width: 10px;
            }
            .<?=self::$MODULE_LBL?>warning{
                color:red !important;
            }
            .<?=self::$MODULE_LBL?>hidden {
                display:none !important;
            }
        </style>
    <?}

    // OPTIONS
    /**
     * @param $code
     * Помещает FAQ с кодом code (то есть, лэновая приписка должна выглядеть как <общий лэнг модуля>_FAQ_$code_TITLE и _DESCR соответственно
     */
    static function placeFAQ($code){?>
        <a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=self::getMessage('FAQ_'.$code.'_TITLE')?></a>
        <div class="ipol_inst"><?=self::getMessage('FAQ_'.$code.'_DESCR')?></div>
    <?}

    /**
     * @param $code
     * Установка блока с подсказкой
     */
    static function placeHint($code){?>
        <div id="pop-<?=$code?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text"><?=self::getMessage("HELPER_".$code)?></div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?}

    /**
     * @param $id - id селекта
     * @param $vals - значения вида код => текст
     * @param bool $def - код значения по умолчанию
     * @param string $atrs - атрибуты селекта
     * @return string
     * Делает селект из предоставленных значений
     */
    static function makeSelect($id, $vals, $def=false, $atrs=''){
        $select = "<select ".(($id) ? "name='".((strpos($atrs,'multiple')===false)?$id:$id.'[]')."' id='{$id}' " : '' )." {$atrs}>";
			if(is_array($vals)){
				foreach($vals as $val => $sign)
					$select .= "<option value='{$val}' ".(((is_array($def) && in_array($val,$def)) || $def == $val )?'selected':'').">{$sign}</option>";
			}
        $select .= "</select>";

        return $select;
    }

    /**
     * @param $id - id и name радиокнопок
     * @param $vals - значения вида код => текст
     * @param bool $def - код значения по умолчанию
     * @param string $atrs - атрибуты радиокнопок
     * @return string
     *
     * Делает блок радиокнопок
     */
    static function makeRadio($id, $vals, $def=false, $atrs=''){
        $radio = "";
        if(is_array($vals)){
            foreach ($vals as $val => $sign){
                $checked = ($val == $def) ? 'checked' : '';
                $radio .= "<input type='radio' {$atrs} {$checked} name='{$id}' id='".$id.'_'.$val."' value='{$val}'>&nbsp;<label for='".$id.'_'.$val."'>{$sign}</label><br>";
            }
        }

        return $radio;
    }

    /**
     * @param $code
     * makes da heading, FAQ und send command to establish included options
     */
    static function placeOptionBlock($code,$isHidden=false)
    {
        global $arAllOptions;
        ?>
        <tr class="heading"><td colspan="2" valign="top" align="center" <?=($isHidden) ? "class='".self::$MODULE_LBL."headerLink' onclick='".self::$MODULE_LBL."setups.getPage(\"main\").showHidden($(this))'" : ''?>><?=self::getMessage("HDR_".$code)?></td></tr>
        <?if(self::getMessage('FAQ_'.$code.'_TITLE')){?>
            <tr><td colspan="2"><?self::placeFAQ($code)?></td></tr>
        <?}
        if(Logger::getLogInfo($code)){
            self::placeWarningLabel(Logger::toOptions($code),self::getMessage("WARNING_".$code),150,array('name'=>Tools::getMessage('LBL_CLEAR'),'action'=>'IPOL_FIVEPOST_setups.getPage("main").clearLog("'.$code.'")','id'=>'clear'.$code));
        }
        if(array_key_exists($code,$arAllOptions)) {
            ShowParamsHTMLByArray($arAllOptions[$code], $isHidden);

            $collection = \Ipol\Fivepost\Option::collection();
            foreach ($arAllOptions[$code] as $arOption){
                if(
                    array_key_exists($arOption[0],$collection) &&
                    $collection[$arOption[0]]['hasHint'] == 'Y'
                ){
                    \Ipol\Fivepost\Bitrix\Tools::placeHint($arOption[0]);
                }
            }
        }
    }

    /**
     * @param $name
     * @param $val
     * Draws tr-td. That's all. Bwahahahaha.
     */
    static function placeOptionRow($name, $val){
        if($name){?>
            <tr>
                <td width='50%' class='adm-detail-content-cell-l'><?=$name?></td>
                <td width='50%' class='adm-detail-content-cell-r'><?=$val?></td>
            </tr>
        <?}else{?>
            <tr><td colspan = '2' style='text-align: center'><?=$val?></td></tr>
        <?}?>
    <?}

    static function defaultOptionPath()
    {
        return "/bitrix/modules/".self::$MODULE_ID."/optionsInclude/";
    }

    /**
     * @param Table $obTable
     */
    static function placeOptionTable($obTable)
    {
        ?>
        <tr><td colspan="2">
            <?$obTable->placeHTML()?>
        </td></tr>
        <?
    }

    // SEND ORDER
    // ВНИМАНИЕ! Данные методы приведены для ПРИМЕРА, их можно удалить. Тут представлены методы для работы с формой отправки заказа

    static function placeSOHeaderRow($code,$link=false,$headerClass='')
    {?>
        <tr class="heading <?=(($headerClass) ? self::$MODULE_LBL.$headerClass : '')?>">
            <td colspan="2">
                <?=($link)?'<a href="javascript:void(0)" onclick="'.$link.'">':''?><?=self::getMessage('HDR_'.$code)?><?=($link)?'</a>':''?>
                <?if(self::getMessage('HELPER_'.$code)){?> <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this,"#<?=self::$MODULE_LBL?>wndOrder");'></a><?self::placeHint($code);}?>
            </td>
        </tr>
    <?}

    static function placeSORow($code,$type,$def=false,$vals=false,$attrs=false,$trClass = false){
        if($type !== 'select' && $type !== 'radio'){
            $attrs = "id='".self::$MODULE_LBL.$code."' name='".self::$MODULE_LBL.$code."' ".$attrs;
        }
        $class = '';
        if($trClass){
            $class = 'class="';
            if(is_array($trClass)){
                foreach($trClass as $className){
                    $class .= self::$MODULE_LBL.$className.' ';
                }
            }
            else{
                $class .= self::$MODULE_LBL.$trClass;
            }
            $class .= '"';
        }
        ?>
        <tr <?=$class?>>
            <td>
                <label for="<?=self::$MODULE_LBL?><?=$code?>"><?=self::getMessage('LBL_'.$code)?></label>
                <?if($hint = Tools::getMessage('HELPER_'.$code)){?>
                    <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this,"#<?=self::$MODULE_LBL?>wndOrder");'></a>
                <?  self::placeHint($code);
                }?>
            </td><td>
        <?
        switch($type){
            case 'text'     : ?><input type="text" <?=$attrs?> value="<?=htmlspecialchars($def)?>"/><? break;
            case 'radio'    : echo self::makeRadio(self::$MODULE_LBL.$code,$vals,$def,$attrs);break;
            case 'select'   : echo self::makeSelect(self::$MODULE_LBL.$code,$vals,$def,$attrs); break;
            case 'sign'     : echo $def; break;
            case 'checkbox' : ?><input type="checkbox" <?=$attrs?> value="Y" <?=($def)?'checked':''?>/><?break;
            case 'textbox'  : ?><textarea <?=$attrs?>><?=$def?></textarea><? break;
            case 'hidden'   : ?><input type="hidden" <?=$attrs?>  value="<?=$def?>"/><span id="<?=self::$MODULE_LBL?>hidLabel_<?=$code?>"><?=$def?></span><? break;
        }
        ?></td></tr><?
    }

    // == END методы работы с заказом

    /**
     * @param $content
     * @param bool $header
     *
     * Вывод окна с ошибкой
     */
    static function placeErrorLabel($content, $header=false)
    {?>
        <tr><td colspan='2'>
            <div class="adm-info-message-wrap adm-info-message-red">
                <div class="adm-info-message">
                    <?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
                    <?=$content?>
                    <div class="adm-info-message-icon"></div>
                </div>
            </div>
        </td></tr>
    <?}

    /**
     * @param $content
     * @param bool $header
     * @param bool $heghtLimit
     * @param bool $click
     *
     * Вывод окна с предупреждением
     */
    static function placeWarningLabel($content, $header=false, $heghtLimit=false, $click=false)
    {?>
        <tr><td colspan='2'>
            <div class="adm-info-message-wrap">
                <div class="adm-info-message" style='color: #000000'>
                    <?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
                    <?if($click){?><input type="button" <?=($click['id'] ? 'id="'.self::$MODULE_LBL.$click['id'].'"' : '')?> onclick='<?=$click['action']?>' value="<?=$click['name']?>"/><?}?>
                    <div <?if($heghtLimit){?>style="max-height: <?=$heghtLimit?>px; overflow: auto;"<?}?>>
                        <?=$content?>
                    </div>
                </div>
            </div>
        </td></tr>
    <?}

    // STUFF

    // Как правило, солянка для работы с СД

    static public function getB24URLs()
    {
        return array (
            'ORDER' => '/shop/orders/details/',
            'SHIPMENT' => '/shop/orders/shipment/details/',
        );
    }

    public static function getDeliveryIdHref($deliveryId){
        return "/bitrix/admin/sale_delivery_service_edit.php?PARENT_ID=0&ID={$deliveryId}";
    }

    public static function getProfileIdHref($profile_id,$deliveryId){
        return "/bitrix/admin/sale_delivery_service_edit.php?PARENT_ID={$deliveryId}&ID={$profile_id}";
    }

    public static function getOrderLink($id){
        return "/bitrix/admin/sale_order_view.php?ID={$id}";
    }

    public static function getShipmentLink($shipmentId,$orderId){
        return "/bitrix/admin/sale_order_shipment_edit.php?order_id={$orderId}&shipment_id={$shipmentId}";
    }

    public static function isConverted()
    {
        return (\COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
    }

    public static function isAdminSection(){
        if (class_exists('\\Bitrix\\Main\\Request') && method_exists('\\Bitrix\\Main\\Request','isAdminSection'))
        {
            $request = \Bitrix\Main\Context::getCurrent()->getRequest();
            $result = $request->isAdminSection();
        }
        else
            $result = defined('ADMIN_SECTION') && ADMIN_SECTION === true;

        return ($result || self::isB24Section());
    }

    public static function isB24Section()
    {
        return (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === "bitrix24");
    }

    public static function formatCurrency($val, $currency = 'RUB', $template=true)
    {
        if(\cmodule::includeModule('sale')){
            return \CCurrencyLang::CurrencyFormat($val, $currency, $template);
        } else {
            return $val;
        }
    }

    public static function makeSimpleGood($params = array())
    {
        $arGood = array(
            "MODULE"     => self::$MODULE_ID.'Delivery',
            "NAME"       => 'testGood',
            "CAN_BUY"    => 'Y',
            "DELAY"      => 'N',
            "SUBSCRIBE"  => 'N',
            "RESERVED"   => 'N',
            "QUANTITY"   => (array_key_exists("QUANTITY", $params)) ? $params["QUANTITY"] : 1,
            "LID"        => (array_key_exists("LID", $params)) ? $params["LID"] : SITE_ID,
            "CURRENCY"   => (array_key_exists("CURRENCY", $params)) ? $params['CURRENCY'] : 'RUB',
            "DIMENSIONS" => array(
                "WIDTH"  => (array_key_exists("WIDTH", $params))  ? $params["WIDTH"]  : 0,
                "HEIGHT" => (array_key_exists("HEIGHT", $params)) ? $params["HEIGHT"] : 0,
                "LENGTH" => (array_key_exists("LENGTH", $params)) ? $params["LENGTH"] : 0
            )
        );

        foreach(array('ID','PRODUCT_ID','SET_PARENT_ID','PRICE','WEIGHT','BASE_PRICE') as $key)
            $arGood[$key] = (array_key_exists($key, $params)) ? $params[$key] : 0;

        return $arGood;
    }

    public static function getDayEnd($day)
    {
        if(strpos($day,'-') !== false){
            $check = explode('-',$day);
            $day = intval(trim($check[1]));
        }
        if($day > 4 && $day < 21 || $day == 0)
            $label = Tools::getMessage('DELIV_DAYS');
        else{
            $lst = $day % 10;
            if($lst == 1)
                $label = Tools::getMessage('DELIV_DAY');
            elseif($lst < 5)
                $label = Tools::getMessage('DELIV_DAYA');
            else
                $label = Tools::getMessage('DELIV_DAYS');
        }

        return $label;
    }

    // service
    public static function getArrVal($key,$arr)
    {
        return (array_key_exists($key,$arr)) ? $arr[$key] : false;
    }
}