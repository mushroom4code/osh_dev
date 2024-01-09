<?php

use Bitrix\Iblock\IblockSiteTable;
use Bitrix\Main\ArgumentException;
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

    return true;
}