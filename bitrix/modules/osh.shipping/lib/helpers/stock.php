<?php
namespace Osh\Delivery\Helpers;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');
/**
 * Bitrix stores
 */
class Stock{
    /**
     * Get list of Bitrix stores
     * @param boolean $isOptions as options
     * @return array list of stores
     */
    public static function getList($isOptions = false){
        $arBitrixStocks = [];
        $dbBitrixStocks = \CCatalogStore::GetList(array('ID' => 'ASC'), array('ACTIVE' => 'Y'));
        while($arStock = $dbBitrixStocks->Fetch()){
            if($isOptions){
                $arBitrixStocks[$arStock['ID']] = $arStock['TITLE'].' '.$arStock['ADDRESS'];
            }else{
                $arBitrixStocks[$arStock['ID']] = $arStock;
            }
        }
        return $arBitrixStocks;
    }
    /**
     * Get store data by its id
     * @param int $stockId
     * @return mixed array or false
     */
    public static function getById($stockId){
        $dbBitrixStocks = \CCatalogStore::GetList(array('ID' => 'ASC'), array('ACTIVE' => 'Y', 'ID' => $stockId));
        return $dbBitrixStocks->Fetch();
    }
    /**
     * Update wares quantity by store
     * @param array $arStock store data
     * @param array $wares list 
     */
    public static function updateWareQuantity($arStock, $wares){
        $ccsp = new \CCatalogStoreProduct();
        $arFilter = array('PRODUCT_ID' => array_keys($wares), 'STORE_ID' => $arStock['ID']);
        $arSelect = array('ID', 'STORE_ID', 'PRODUCT_ID');
        $dbStockData = $ccsp->GetList(array('ID' => 'DESC'), $arFilter, false, false, $arSelect);
        while($stockData = $dbStockData->Fetch()){
            $wares[$stockData['PRODUCT_ID']]['stock_pos_id'] = $stockData['ID'];
        }
        foreach($wares as $id => $ware){
            if(!empty($ware['stock_pos_id'])){
                $ccsp->Update($ware['stock_pos_id'], array('AMOUNT' => $ware['total']));
            }else{
                $ccsp->Add(array('AMOUNT' => $ware['total'], 'STORE_ID' => $arStock['ID'], 'PRODUCT_ID' => $id));
            }
        }
    }
}