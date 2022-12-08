<?php
namespace Osh\Delivery\Helpers;

use Osh\Delivery\Options\Config,
    Osh\Delivery\COshAPI,
    Bitrix\Main\ORM\Query,
    Bitrix\Main\Entity;

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

/**
 * Ware operations
 */
class Ware{
    /**
     * API Osh getProducts page size
     */
    const PAGE_SIZE = 20;
    const PAGE_SIZE_SMALL = 5;
    /**
     * update quantity entry point
     * @param int $page
     * @return mixed boolean/array
     */
    public static function getProductsQuantity($page){
        $arParams = array(
            'page' => intval($page),
            'per_page' => self::PAGE_SIZE
        );

        $api = COshAPI::getInstance();
        $wareData = $api->Request('getProducts', $arParams);
        if(empty($wareData) || empty($wareData['result'])){
            return false;
        }
        $arWares = array();
        foreach($wareData['result'] as $ware){
            $arWares[$ware['shopArticle']] = $ware['fulfilment'];
        }
        $result = self::updateQuantity($arWares);
        return $result;
    }
    /**
     * update quantity
     * @param array $arWares osh wares
     * @return array bitrix wares
     */
    public static function updateQuantity($arWares){
        $arWares = self::getWaresByArticle($arWares);

        $isOverrideQuantity = Config::isQuantityOverride();
        $bitrixStock = Config::getBitrixStock();

        if(!$isOverrideQuantity && $bitrixStock){
            $arBitrixStock = Stock::getById($bitrixStock);
        }
        if($arBitrixStock){
            Stock::updateWareQuantity($arBitrixStock, $arWares);
        }else{
            foreach($arWares as $id => $ware){
                \CCatalogProduct::Update($id, array('QUANTITY' => $ware['total']));
            }
        }
        return $arWares;
    }
    /**
     * Get wares and ware offers by property option
     * @param array $arWares osh wares
     * @return array bitrix wares
     */
    public static function getWaresByArticle($arWares){
        $arArticles = array_keys($arWares);
        $arWaresIds = self::getWaresByArticleOption($arArticles, false) +
                self::getWaresByArticleOption($arArticles, true);
        $arNewWares = array();
        foreach($arWares as $article => $ware){
            if(!empty($arWaresIds[$article])){
                $ware['shop_article'] = $article;
                $arNewWares[$arWaresIds[$article]] = $ware;
            }
        }
        return $arNewWares;
    }
    /**
     * Get ware/ware offers by property option
     * @param array $arArticles shop articles
     * @param boolean $isOffer is ware offer
     * @return array bitrix ware list
     */
    public static function getWaresByArticleOption($arArticles, $isOffer = false){
        $arWares = array();
        if($isOffer){
            $articleOption = Config::getDataValue('productOfferArticle');
            $articleProp = Config::getDataValue('offerArticleProperty');
        }else{
            $articleOption = Config::getDataValue('productArticle');
            $articleProp = Config::getDataValue('articleProperty');
        }
        if(!empty($articleProp) && $articleOption == 'PROP'){
            $arProperty = \COshDeliveryHelper::getIblockPropertyData($articleProp);
        }
        switch($articleOption){
            case 'PROP':
                if(!empty($arProperty)){
                    $arWares = $arWares + self::getWaresByProperty($arArticles, $arProperty);
                    break;
                }
            case 'ID':default:
                $arWares = self::getWaresById($arArticles);
                break;
            case 'XML_ID':
                $arWares = self::getWaresByXmlId($arArticles);
                break;
        }
        return $arWares;
    }
    /**
     * Get wares by property
     * @param array $arArticles shop articles
     * @param array $arProperty property data
     * @return array
     */
    public static function getWaresByProperty($arArticles, $arProperty){
        return self::getWaresByField($arArticles, $arProperty);
    }
    /**
     * Get wares by id
     * @param array $arArticles shop articles
     * @return array
     */
    public static function getWaresById($arArticles){
        return self::getWaresByField($arArticles, 'ID');
    }
    /**
     * Get wares by xml_id
     * @param array $arArticles shop articles
     * @return array
     */
    public static function getWaresByXmlId($arArticles){
        return self::getWaresByField($arArticles, 'XML_ID');
    }
    /**
     * Common get wars by property method
     * @param array $arArticles shop articles
     * @param mixed $property array of property data or field name as string
     * @return array
     */
    public static function getWaresByField($arArticles, $property){
        $arWares = array();
        $isProperty = is_array($property);
        $arFilter = array();
        if($isProperty){
            $sPropCode = 'PROPERTY_'.$property['CODE'];
            $arFilter['IBLOCK_ID'] = $property['IBLOCK_ID'];
        }else{
            $sPropCode = $property;
        }
        $arFilter[$sPropCode] = $arArticles;
        $arSelect = array('ID','NAME','IBLOCK_ID', $sPropCode);
        $dbWares = \CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false,false, $arSelect);
        while($arWare = $dbWares->Fetch()){
            $arWares[$arWare[$sPropCode .($isProperty? '_VALUE':'')]] = $arWare['ID'];
        }
        return $arWares;
    }
    public static function uploadWares($page){
        $arIblocks = array_merge(self::getIblockIds(), self::getOfferIblockIds());
        $arWares = self::getCatalogProducts($page, $arIblocks);
        if(!empty($arWares)){
            return self::updateWares($arWares);
        }else{
            return false;
        }
    }
    public static function getIblockIds($isOffers = false){
        $arIblocks = array();
        if($isOffers){
            $articleOption = Config::getDataValue('productOfferArticle');
            $articleProp = Config::getDataValue('offerArticleProperty');
        }else{
            $articleProp = Config::getDataValue('articleProperty');
            $articleOption = Config::getDataValue('productArticle');
        }
        switch($articleOption){
            case 'PROP':
                $arProperty = \COshDeliveryHelper::getIblockPropertyData($articleProp);
                $arIblocks[] = $arProperty['IBLOCK_ID'];
                break;
            case 'ID':default:case 'XML_ID':
                $arParams = array(
                    'filter' => array(($isOffers? '!': '').'PRODUCT_IBLOCK_ID' => 0),
                    'select' => array('IBLOCK_ID')
                );
                $arRes = \Bitrix\Catalog\CatalogIblockTable::getList($arParams)->fetchAll();
                foreach($arRes as $res){
                    $arIblocks[] = $res['IBLOCK_ID'];
                }
                break;
        }
        return $arIblocks;
    }
    public static function getOfferIblockIds(){
        return self::getIblockIds(true);
    }
    public static function getCatalogProducts($page, $iblocks){
        $arParams = array(
            'filter' => array('IBLOCK_ELEMENT.IBLOCK_ID' => $iblocks, 'IBLOCK_ELEMENT.ACTIVE' => 'Y', 
                'PRICES.CURRENCY' => 'RUB'),
            'select' => array('ID', 'NAME' => 'IBLOCK_ELEMENT.NAME', 'WIDTH', 'LENGTH', 'WEIGHT',
                'HEIGHT', 'DESCRIPTION' => 'IBLOCK_ELEMENT.PREVIEW_TEXT', 'XML_ID' => 'IBLOCK_ELEMENT.XML_ID',
                'PRICE' => 'PRICES.PRICE', 'CURRENCY' => 'PRICES.CURRENCY'
                ),
            'limit' => self::PAGE_SIZE_SMALL,
            'offset' => ($page - 1) * self::PAGE_SIZE_SMALL,
            'runtime' => array(
                new Entity\ReferenceField(
                'PRICES',
                \Bitrix\Catalog\PriceTable::class,
                Query\Join::on('this.ID', 'ref.PRODUCT_ID')
                )
            )
        );
        $arWares = \Bitrix\Catalog\ProductTable::getList($arParams)->fetchAll();
        if(!empty($arWares)){
            foreach($arWares as $key => $ware){
                $arWares[$key]['ID'] = self::getWareArticleByOption($ware['ID']);
            }
        }
        return $arWares;
    }
    public static function getWareArticleByOption($productId){
        if(\CCatalogSku::GetProductInfo($productId)){
            $articleOption = Config::getDataValue('productOfferArticle');
            $articleProp = Config::getDataValue('offerArticleProperty');
            $compleXMLIDoption = (bool)Config::getDataValue('offerXmlIdComplex');
        }else{
            $articleOption = Config::getDataValue('productArticle');
            $articleProp = Config::getDataValue('articleProperty');
            $compleXMLIDoption = (bool)Config::getDataValue('xmlIdComplex');
        }
        switch($articleOption){
            case 'PROP':
                $arProperty = \COshDeliveryHelper::getIblockPropertyData($articleProp);
                if(!empty($arProperty)){
                    $arWare = self::getPropertyValue($productId, $arProperty['CODE']);
                    if(!empty($arWare[$productId])){
                        $article = $arWare[$productId];
                        break;
                    }
                }
            case 'ID':default:
                $article = $productId;
                break;
            case 'XML_ID':
                $sPropCode = 'XML_ID';
                $arFilter = array("!$sPropCode" => false, 'ACTIVE' => 'Y', 'ID' => $productId);
                $arSelect = array('ID','NAME','IBLOCK_ID', $sPropCode);
                $arWares = \CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, false, false, $arSelect)->Fetch();
                if(!empty($arWares[$sPropCode.'_VALUE'])){
                    $article = $arWares[$sPropCode];
                    if($compleXMLIDoption && strpos($article,'#') !== false){
                        $arArticle = explode('#',$article);
                        $article = $arArticle[1];
                    }
                }
                break;
        }
        return $article;
    }
    public static function getPropertyValue($ids, $sProperty){
        $arWares = array();
        $sPropCode = 'PROPERTY_'.$sProperty;
        $arFilter = array("!$sPropCode" => false, 'ACTIVE' => 'Y',
            'ID' => $ids);
        $arSelect = array('ID','NAME','IBLOCK_ID', $sPropCode);
        $dbWares = \CIBlockElement::GetList(array('ID' => 'ASC'),$arFilter,false,false,$arSelect);
        while($arWare = $dbWares->Fetch()){
            $arWares[$arWare['ID']] = $arWare[$sPropCode.'_VALUE'];
        }
        return $arWares;
    }
    public static function updateWares($arWares){
        $api = COshAPI::getInstance();
        $total = array(
            'added' => 0,
            'updated' => 0,
            'errors' => array()
        );
        foreach($arWares as $ware){
            $arItem = array(
                'shopArticle' => $ware["ID"],
                'name' => $ware["NAME"],
                'retailPrice' => $ware["PRICE"],
                'fragile' => false,
                'danger' => false,
                'perishable' => false,
                'needBox' => false
            );
            if($ware["WEIGHT"] > 0){
                $arItem["weight"] = Calc::weightToKg($ware["WEIGHT"]);
            }
            foreach(['WIDTH', 'HEIGHT', 'LENGTH'] as $field){
                if(!empty($ware[$field])){
                    $arItem[strtolower($field)] = round($ware[$field]/10);
                }
            }
            if(!empty($ware["URL"])){
                $arItem["url"] = $ware["URL"];
            }
            $arItem['englishName'] = \CUtil::translit($ware["NAME"], 'ru', array('replace_space' => ' '));

            $getWare = $api->Request('getProducts', array('shopArticle' => $arItem['shopArticle'], 'per_page' => 1, 'page' => 1));
            $isUpdate = !empty($getWare['result']);
            if(!$isUpdate){
                $arItem['price'] = $ware["PRICE"];
                if(!empty($ware['BRAND'])){
                    $arItem['brand'] = $ware["BRAND"];
                }
            }
            $wareResult = $api->Request($isUpdate ? 'editProduct': 'addProduct', $arItem);
            if(!empty($wareResult['result'])){
                $total[$isUpdate ? 'updated' : 'added']++;
            }else{
                $total['errors'][$arItem['shopArticle']] = $wareResult['error']['message'];
            }
        }
        return $total;
    }
}