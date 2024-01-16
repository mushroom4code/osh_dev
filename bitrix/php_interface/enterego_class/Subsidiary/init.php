<?php

use Bitrix\Iblock\IblockSiteTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

require_once __DIR__ . '/conf.php';
if ($_SERVER['SCRIPT_NAME'] === '/bitrix/admin/public_file_edit.php') {
    return;
}

if (defined('SUBSIDIARY_ENABLE') && SUBSIDIARY_ENABLE && !defined('SITE_ID'))
{
    session_start();
    $_siteId = $_SESSION['subsidiary'] ?? 'N2';
    if (!empty($_siteId && in_array($_siteId, SUBSIDIARY_SITE_LIST))) {
        define('SITE_ID', $_siteId);
    }
}

/** set relation for new subsidiary (iblock)
 * execute from bitrix php console
 *
 * @param $mainSite string
 * @param $subSite string
 * @return bool
 * @throws ArgumentException
 * @throws ObjectPropertyException
 * @throws SystemException
 */
function addNewSubsidiary(string $mainSite, string $subSite): bool
{
    if (!CModule::IncludeModule('iblock')) {
        return false;
    }

    //block
    $arMainBlock = [];
    $rsMainBlock =  IblockSiteTable::getList(['filter'=> ['SITE_ID'=>$mainSite]]);
    while ($iBlock = $rsMainBlock->fetch()) {
        $arMainBlock[] =$iBlock['IBLOCK_ID'];
    }

    $arSubBlock = [];
    $rsSubBlock =  IblockSiteTable::getList(['filter'=> ['SITE_ID'=>$subSite]]);
    while ($iBlock = $rsSubBlock->fetch()) {
        $arSubBlock[] =$iBlock['IBLOCK_ID'];
    }

    foreach ($arMainBlock as $itemMainBlock) {
        if (!in_array($itemMainBlock, $arSubBlock)) {
            IblockSiteTable::add(['IBLOCK_ID'=>$itemMainBlock, 'SITE_ID'=>$subSite]);
        }
    }

    //contract
    copyRecordsForSubsidiary($mainSite, $subSite, 'b_adv_contract_2_site', 'CONTRACT_ID');

    //banners
    copyRecordsForSubsidiary($mainSite, $subSite, 'b_adv_banner_2_site', 'BANNER_ID');

    return true;
}

/** copy records with site id relations with native sql
 *
 * @param string $mainSite
 * @param string $subSite
 * @param string $tableName
 * @param string $fieldName
 * @return void
 * @throws SqlQueryException
 */
function copyRecordsForSubsidiary(string $mainSite, string $subSite, string $tableName, string $fieldName): void
{
    $connection = Application::getConnection();

    $arMain = [];
    $result = $connection->query("SELECT * FROM $tableName WHERE SITE_ID = '$mainSite'");
    while ($arResult = $result->fetch()) {
        $arMain[] =$arResult[$fieldName];
    }

    $arSub = [];
    $result = $connection->query("SELECT * FROM $tableName WHERE SITE_ID = '$subSite'");
    while ($arResult = $result->fetch()) {
        $arSub[] =$arResult[$fieldName];
    }

    foreach ($arMain as $item) {
        if (!in_array($item, $arSub)) {

            $connection->query("INSERT INTO $tableName ($fieldName, SITE_ID) 
                VALUES ($item, '$subSite')");
        }
    }
}