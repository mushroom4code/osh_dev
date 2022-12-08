<?
namespace WD\Utilities;

use
	\WD\Utilities\Helper;

Helper::loadMessages();

/**
 * Class IBlockHelper
 * @package WD\Utilities
 */
class IBlockHelper {
	
	public static function addContextDetailLink(&$arMenuItems, $strValue=null) {
		$strUrl = $GLOBALS['APPLICATION']->getCurPage();
		$arUrl = [
			'/bitrix/admin/iblock_element_edit.php',
			'/bitrix/admin/cat_product_edit.php',
			'/shop/settings/iblock_element_edit.php',
		];
		if(in_array($strUrl, $arUrl) && $_GET['IBLOCK_ID'] > 0 && $_GET['ID'] > 0) {
			if(is_array($arMenuItems) && in_array($strValue, ['Y', 'S'])) {
				$bAdded = false;
				$arButton = static::getDetailLinkButton();
				if($arButton){
					if($strValue == 'Y'){
						foreach($arMenuItems as $key => $arMenuItem) {
							if(is_array($arMenuItem['MENU']) && $arMenuItem['ICON'] == 'btn_new') {
								if($arButton) {
									$arMenuItems[$key]['MENU'][] = $arButton;
									$bAdded = true;
								}
								break;
							}
						}
					}
					if(!$bAdded){
						$arMenuItems[] = $arButton;
					}
				}
			}
		}
	}
	
	protected static function getDetailLinkButton(){
		$arResult = null;
		if(\Bitrix\Main\Loader::includeModule('iblock')){
			$arFilter = [
				'IBLOCK_ID' => intVal($_GET['IBLOCK_ID']),
				'ID' => intVal($_GET['ID']),
			];
			$arSelect = ['DETAIL_PAGE_URL'];
			$resItem = \CIBlockElement::getList([], $arFilter, false, false, $arSelect);
			if($arItem = $resItem->getNext(false, false)) {
				if(strlen($strUrl = $arItem['DETAIL_PAGE_URL'])) {
					$strIBlockSiteId = static::getIBlockSite(intVal($_GET['IBLOCK_ID']));
					if(strlen($strIBlockSiteId)){
						$arItem['LID'] = $strIBlockSiteId;
					}
					$resSites = \CSite::getList($by='ID', $order='ASC', ['ID' => $arItem['LID']]);
					if($arSite = $resSites->fetch()){
						$strServerName = $arSite['SERVER_NAME'];
						$bHttps = false;
						if(!strlen($strServerName)){
							$strServerName = \Bitrix\Main\Config\Option::get('main', 'server_name');
							$bHttps = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? true : false;
						}
						if(!strlen($strServerName)){
							$strServerName = \Bitrix\Main\Context::getCurrent()->getServer()->getHttpHost();
							$bHttps = \Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? true : false;
						}
						if(strlen($strServerName)){
							$arResult = [
								'TEXT' => Helper::getMessage('WDU_SHOW_ON_SITE'),
								'ONCLICK' => 'window.open("'.($bHttps ? 'https' : 'http').'://'.$strServerName.$strUrl.'");',
								'ICON' => 'view',
							];
						}
					}
				}
			}
		}
		return $arResult;
	}
	
	public static function getIBlockSite($intIBlockId){
		$arSites = [];
		$resSites = \CIBlock::getSite($intIBlockId);
		while($arSite = $resSites->fetch()){
			$arSites[$arSite['LID']] = $arSite['LID'];
		}
		if(count($arSites) > 1){
			$strCrmSiteId = static::getCrmSiteId();
			if(strlen($strCrmSiteId) && isset($arSites[$strCrmSiteId])){
				unset($arSites[$strCrmSiteId]);
			}
		}
		return reset($arSites);
	}
	
	public static function getCrmSiteId(){
		$arSites = Helper::getSitesList(true, true);
		foreach($arSites as $strSiteId => $strSiteName){
			$resSiteTemplate = \CSite::getTemplateList($strSiteId);
			while($arSiteTemplate = $resSiteTemplate->fetch()){
				if($arSiteTemplate['TEMPLATE'] == 'bitrix24' && !strlen(trim($arSiteTemplate['CONDITION']))){
					return $strSiteId;
				}
			}
		}
		return false;
	}
	
	public static function displayElementIdInTabFoot(&$obTabControl) {
		$strUrl = $GLOBALS['APPLICATION']->getCurPage();
		$arUrl = ['/bitrix/admin/iblock_element_edit.php', '/bitrix/admin/cat_product_edit.php'];
		if(in_array($strUrl, $arUrl) && $_GET['IBLOCK_ID'] > 0 && $_GET['ID'] > 0) {
			$strContent = sprintf('<span>&nbsp;<b>ID</b>: %s</span>', $_GET['ID']);
			if(defined('BX_PUBLIC_MODE')) {
				?>
				<script>
				setTimeout(function(){
					let divButtons = document.getElementById('save_and_add').parentNode;
					console.log(divButtons);
					if(divButtons) {
						let tmpDiv = document.createElement('div');
						tmpDiv.innerHTML = '<?=$strContent;?>';
						divButtons.appendChild(tmpDiv.firstChild);
					}
				}, 500);
				</script>
				<?
			}
			elseif(preg_match('#form_element_(\d+)#', $obTabControl->name)) {
				$obTabControl->sButtonsContent .= $strContent;
			}
		}
	}
	
}
