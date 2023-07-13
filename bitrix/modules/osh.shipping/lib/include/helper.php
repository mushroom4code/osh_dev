<?
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Type,
    Bitrix\Main\Entity,
    Bitrix\Main\IO\File,
    Bitrix\Main\Application,
    Bitrix\Main\FileTable,
    Bitrix\Sale\Location\LocationTable,
    Bitrix\Sale\Internals\PersonTypeTable,
    Bitrix\Sale\Internals\OrderPropsTable,
    Bitrix\Sale\Delivery\Services\Table as DST,
    Osh\Delivery\Cache\Cache,
    Osh\Delivery\Options\Config,
//    Osh\Delivery\OshService,
    Osh\Delivery\OshHandler,
    Osh\Delivery\COshAPI;

//Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/osh.shipping/lib/include.php');
//Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/osh.shipping/admin/unload.php');

class COshDeliveryHelper {
    const MODULE_ID = 'osh.shipping';
//    const LOGO_DESCRIPTION_BB = 'osh.shipping.bb logo';
//    const LOGO_DESCRIPTION_CDEK = 'osh.shipping.cdek logo';
//    const LOGO_DESCRIPTION_DPD = 'osh.shipping.dpd logo';
//    const LOGO_DESCRIPTION_IML = 'osh.shipping.iml logo';
//    const LOGO_DESCRIPTION_PP = 'osh.shipping.pp logo';
//    const LOGO_DESCRIPTION_RP = 'osh.shipping.rp logo';
//    const LOGO_DESCRIPTION_PEC = 'osh.shipping.pec logo';
//    const LOGO_DESCRIPTION_SB = 'osh.shipping.sb logo';
    const LOGO_DESCRIPTION = 'osh.shipping logo';
//    const LOGO_BB = '/bitrix/images/osh.shipping/boxberry.png';
//    const LOGO_CDEK = '/bitrix/images/osh.shipping/cdek.png';
//    const LOGO_DPD = '/bitrix/images/osh.shipping/dpd.png';
//    const LOGO_IML = '/bitrix/images/osh.shipping/iml.png';
//    const LOGO_PP = '/bitrix/images/osh.shipping/picpoint.png';
//    const LOGO_RP = '/bitrix/images/osh.shipping/russian-post.png';
//    const LOGO_PEC = '/bitrix/images/osh.shipping/pek.png';
//    const LOGO_SB = '/bitrix/images/osh.shipping/sb.png';
    const LOGO_PATH = '/bitrix/images/osh.shipping/osh_logo.png';
    public static function getLogoId($path = null,$desc = null){
        if(empty($path) && empty($desc)){
            $path = self::LOGO_PATH;
            $desc = self::LOGO_DESCRIPTION;
        }
        $sDocumentRoot = Application::getDocumentRoot();
        $logo = new File($sDocumentRoot . $path);
        if($logo->isExists()){
            $fileId = FileTable::getList(array("filter" => array("DESCRIPTION" => $desc), "select" => array("ID")))->fetch();
            if(empty($fileId)){
                $logoArray = \CFile::MakeFileArray($sDocumentRoot . $path);
                $logoArray["description"] = $desc;
                $logoArray["MODULE_ID"] = self::MODULE_ID;
                $fileId = intval(\CFile::SaveFile($logoArray,self::MODULE_ID));
            }else{
                $fileId = $fileId["ID"];
            }
        }
        return $fileId;
    }
    public static function getDefaultLogo($courier){
//        $arCouriers = array(
//            'boxberry' => array(self::LOGO_BB, self::LOGO_DESCRIPTION_BB),
//            'cdek' => array(self::LOGO_CDEK, self::LOGO_DESCRIPTION_CDEK),
//            'dpd' => array(self::LOGO_DPD, self::LOGO_DESCRIPTION_DPD),
//            'iml' => array(self::LOGO_IML, self::LOGO_DESCRIPTION_IML),
//            'pickpoint' => array(self::LOGO_PP, self::LOGO_DESCRIPTION_PP),
//            'russian-post' => array(self::LOGO_RP, self::LOGO_DESCRIPTION_RP),
//            'pec' => array(self::LOGO_PEC, self::LOGO_DESCRIPTION_PEC),
//            'sberlogistics' => array(self::LOGO_SB, self::LOGO_DESCRIPTION_SB),
//            'sber_courier' => array(self::LOGO_SB, self::LOGO_DESCRIPTION_SB)
//        );
//        if(!empty($arCouriers[$courier])){
//            return self::getLogoId($arCouriers[$courier][0], $arCouriers[$courier][1]);
//        }else{
            return self::getLogoId();
//        }
    }
//    public static function getDeliveries($filter = array()){
//        $arFilter = array(
//            'PARENT.CLASS_NAME' => "%OshHandler",
//            'ACTIVE' => "Y",
//            '>PARENT_ID' => 0
//        );
//        if(!empty($filter)){
//            $arFilter = array_merge($arFilter,$filter);
//        }
//        $arDeliveries = DST::GetList(array(
//                'filter' => $arFilter,
//                'select' => array(
//                    "ID"
//                )
//            ))->fetchAll();
//        foreach($arDeliveries as $key => $item){
//            $arDeliveries[$key] = $item["ID"];
//        }
//        return $arDeliveries;
//    }
//    public static function getCommonDeliveries(){
//        return self::getDeliveries(array("!CONFIG" => "%DIRECT%"));
//    }
//    public static function getDirectDeliveries(){
//        return self::getDeliveries(array("CONFIG" => "%DIRECT%"));
//    }
//    public static function getExportDeliveries(){
//        return self::getDeliveries(array("CONFIG" => "%osh-international%"));
//    }
    public static function getLocationByCode($locationCode){
        if(empty($locationCode)) return false;
        $arLocation = Cache::getLocationData($locationCode);
        if(empty($arLocation)){
            $arLocation = array("CODE" => $locationCode);
            $dbLocation = LocationTable::getList(array(
                    'filter' => array(
                        '=CODE' => $locationCode,
                        '=PARENTS.NAME.LANGUAGE_ID' => array('RU', 'EN'),
                        '=PARENTS.TYPE.CODE' => array("COUNTRY","REGION","CITY","VILLAGE","SUBREGION"),
                    ),
                    'select' => array(
                        'I_NAME' => 'PARENTS.NAME.NAME',
                        'I_LANG' => 'PARENTS.NAME.LANGUAGE_ID',
                        'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
                    ),
                    'order' => array(
                        'PARENTS.DEPTH_LEVEL' => 'asc'
                    )
            ));
            while($arLoc = $dbLocation->fetch()){
                if($arLoc['I_LANG'] !== 'ru'){
                    $arLocation[$arLoc["I_TYPE_CODE"].'_'.$arLoc['I_LANG']] = $arLoc["I_NAME"];
                }else{
                    if($arLoc["I_TYPE_CODE"] == "CITY" && !empty($arLocation["CITY"])){
                        $arLocation["REGION"] = $arLocation["CITY"];
                    }
                    $arLocation[$arLoc["I_TYPE_CODE"]] = $arLoc["I_NAME"];
                    if(!empty($arLoc["KLADR"])){
                        $arLocation["KLADR"] = $arLoc["KLADR"];
                    }
                }
            }
            if(empty($arLocation["CITY"])){
                $arLocation["CITY"] = $arLocation["VILLAGE"];
                $arLocation["NOT_CITY"] = true;
            }
//            $arLocation['COUNTRY_CODE'] = self::getCountryCode($arLocation['COUNTRY']);
//            $arLocation["KLADR"] = self::getKladr($arLocation);
//            if(!empty($arLocation["KLADR"])){
//                Cache::setLocationData($locationCode, $arLocation);
//            }
        }
        return $arLocation;
    }
//    public static function getCountryCode($country){
//        if(strpos(Loc::getMessage('OSH_COUNTRY_RU'),$country) !== false){
//            return Osh\Delivery\ProfileHandler::COUNTRY_RU;
//        }
//        if($country == Loc::getMessage('OSH_COUNTRY_KZ')){
//            return Osh\Delivery\ProfileHandler::COUNTRY_KZ;
//        }
//        if(strpos(Loc::getMessage('OSH_COUNTRY_BY'),$country) !== false){
//            return Osh\Delivery\ProfileHandler::COUNTRY_BY;
//        }
//        foreach(self::getCountries() as $arCountry){
//            if($arCountry['name'] == $country){
//                return $arCountry['code'];
//            }
//        }
//        return Osh\Delivery\ProfileHandler::COUNTRY_RU;
//    }
    public static function getPersonTypes(){
        $arSelectPt = array("ID","NAME_WS");
        $arOrderPt = array("SORT" => "ASC");
        $arFilter = array("ACTIVE" => "Y");
        $arRuntime = array(new Entity\ExpressionField('NAME_WS', "CONCAT('(',%s,') ',%s)",array("LID","NAME")));
        $dbPersonTypes = PersonTypeTable::getList(array("select" => $arSelectPt, "order" => $arOrderPt,
                "filter" => $arFilter, "runtime" => $arRuntime));
        $arPersonTypes = array();
        while($arPersonType = $dbPersonTypes->fetch()){
            $arPersonTypes[$arPersonType["ID"]] = array("NAME" => $arPersonType["NAME_WS"]);
        }
        return $arPersonTypes;
    }
    public static function getOrderProps(){
        $arPersonTypes = self::getPersonTypes();
        $arSelect = array("ID","PERSON_TYPE_ID","NAME");
        $arOrder = array("ID" => "ASC");
        $arFilter["PERSON_TYPE_ID"] = array_keys($arPersonTypes);
        $arFilter['!TYPE'] = 'LOCATION';
        $dbOrderProps = OrderPropsTable::getList(array("select" => $arSelect, "filter" => $arFilter,
                "order" => $arOrder));
        while($arProp = $dbOrderProps->fetch()){
            $arProp["NAME"] = "[{$arProp["ID"]}] ".$arProp["NAME"];
            $arPersonTypes[$arProp["PERSON_TYPE_ID"]]["PROPS"][$arProp["ID"]] = $arProp["NAME"];
        }
        return $arPersonTypes;
    }
    public static function isPVZProfile($oDelivery){
        if(!is_callable(array($oDelivery,"isPvz"))){
            return false;
        }
        return $oDelivery->isPvz();
    }

    /**
     * @return array[]
     */
    public static function getShippingMethods(): array
    {
        if(empty($arResult)){
            $arResult = [
                [
                    "id" => 1,
                    "name" => "Доставка курьером",
                    "category" => "delivery-point",
                    "group" => "osh_pvz",
                    "courier" => "osh_pvz",
                    "comment" => ""
                ],
                [
                    "id" => 2,
                    "name" => "Самовывоз со склада",
                    "category" => "delivery-point",
                    "group" => "osh_pvz_pickup",
                    "courier" => false,
                    "comment" => ""
                ]
            ];
        }
        return $arResult;
    }

    public static function getDeliveryTime(){
        $arDeliveryTimes = Cache::getDeliveryTimes();
        if(empty($arDeliveryTimes)){
            $oshApi = COshAPI::getInstance();
            if($oshApi){
            $arRes = $oshApi->Request("getDeliveryTime",array());
            $arDeliveryTimes = $arRes['result'];
            Cache::setDeliveryTimes($arDeliveryTimes);}
        }
        return $arDeliveryTimes;
    }
//    public static function getCountries(){
//        $arCountries = Cache::getCountries();
//        if(empty($arCountries)){
//            $apiOsh = COshAPI::getInstance();
//            $arResult = $apiOsh->Request("getCountries");
//            if(!empty($arResult['error']['message'])){
//                throw new \Exception($arResult['error']['message']);
//            }
//            $arCountries = $arResult['result'];
//            Cache::setCountries($arCountries);
//        }
//        return $arCountries;
//    }
//    public static function getStocks(){
////        $arStocks = Cache::getStocks();
////        if(empty($arStocks)){
////            $apiOsh = COshAPI::getInstance();
////            $arResult = $apiOsh->Request("getStocks");
////            if(!empty($arResult['error']['message'])){
////                throw new \Exception($arResult['error']['message']);
////            }
//            $arStocks = $arResult['result'];
////            Cache::setStocks($arStocks);
////        }
//        return $arStocks;
//    }
//    public static function getStockById($stockId){
//        foreach(self::getStocks() as $stock){
//            if($stock['id'] == $stockId){
//                return $stock;
//            }
//        }
//        return false;
//    }
//    public static function getStocksAsOptionsList(){
//        $arOptionsList = array(
//            Config::STOCK_NONE => Loc::getMessage('OSH_DEFAULT_STOCK_NONE')
//        );
//        $arRoles = [
//            'logistic' => Loc::getMessage('OSH_STOCK_ROLE_logistic'),
//            'fulfilment' => Loc::getMessage('OSH_STOCK_ROLE_fulfilment')
//        ];
//        foreach(self::getStocks() as $stock){
//            $sRoles = array();
//            foreach($stock['roles'] as $role){
//                $sRoles[] = $arRoles[$role];
//            }
//            $arOptionsList[$stock['id']] = $stock['address']. ' ('.implode($sRoles,', ').')';
//        }
//        return $arOptionsList;
//    }
//    public static function getIblockPropertyData($articleProp){
//        Loader::includeModule('iblock');
//        Loader::includeModule('catalog');
//        $arPropExploded = explode('|',$articleProp);
//        $arFilter = array('ID' => $arPropExploded[0], 'VERSION' => $arPropExploded[1], 'MULTIPLE' => $arPropExploded[2]);
//        $arProperty = \CIBlockProperty::GetList(array('ID' => 'ASC'),$arFilter)->Fetch();
//        if(!empty($arProperty)){
//            $iblockInfo = \CIBlock::GetByID($arProperty['IBLOCK_ID'])->Fetch();
//            $arProperty['IBLOCK_ID'] = $iblockInfo['ID'];
//            $arProperty['IBLOCK_CODE'] = $iblockInfo['IBLOCK_TYPE_ID'];
//            $arProperty['IS_CATALOG'] = false;
//            if(Loader::includeModule('catalog')){
//                if(\CCatalog::GetByID($iblockInfo['ID'])){
//                    $arProperty['IS_CATALOG'] = true;
//                }
//            }
//        }
//        return $arProperty;
//    }
//    public static function getCatalogIblocks(){
//        \Bitrix\Main\Loader::includeModule('catalog');
//        $iblockIds = array();
//        $resDb = CCatalog::getList(array('ID' => 'ASC'), array('ACTIVE' => 'Y'), false, false, array('ID'));
//        while($res = $resDb->Fetch()){
//            $iblockIds[] = $res['ID'];
//        }
//        return $iblockIds;
//    }
//    public static function getCatalogIblock(){
//        $iblockIds = array();
//        foreach(self::getCatalogIblocks() as $id){
//            if(!\CCatalogSKU::GetInfoByOfferIBlock($id)){
//                $iblockIds[] = $id;
//            }
//        }
//        return $iblockIds;
//    }
//    public static function getCatalogOfferIblock(){
//        $iblockIds = array();
//        foreach(self::getCatalogIblocks() as $id){
//            if(\CCatalogSKU::GetInfoByOfferIBlock($id)){
//                $iblockIds[] = $id;
//            }
//        }
//        return $iblockIds;
//    }
}