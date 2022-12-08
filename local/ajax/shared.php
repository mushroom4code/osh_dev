<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Context,
	Bitrix\Main\Loader,
	Bitrix\Iblock;

\Bitrix\Main\Loader::includeModule("iblock");

	$ELEMENT_ID = intval($_GET['ELEMENT_ID']);
	if( $ELEMENT_ID > 0 )
	{
		$arFilter = array('ID' => $ELEMENT_ID);
		$dbItems = \Bitrix\Iblock\ElementTable::getList(array(
			'order' => array('ID'=>'DESC'), 
			'select' => array('ID', 'NAME', 'IBLOCK_ID', 'CODE', 'IBLOCK_SECTION_ID', 'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL'), 
			'filter' => $arFilter, 
			'cache' => array( 
				'ttl' => 360, 
				'cache_joins' => true 
			),
		));			
					
		while( $arFields = $dbItems->fetch() )
		{
			$DATA_TITLE = $arFields['NAME'];
			$DATA_URL = CIBlock::ReplaceDetailUrl($arFields['DETAIL_PAGE_URL'], $arFields, false, 'E');

		}				
?>
<?$APPLICATION->IncludeComponent(
	"arturgolubev:yandex.share",
	"",
	Array(
		"DATA_IMAGE" => "",
		"DATA_RESCRIPTION" => "",
		"DATA_TITLE" => $DATA_TITLE,
		"DATA_URL" => $DATA_URL,
		"OLD_BROWSERS" => "N",
		"SERVISE_LIST" => array("collections", "vkontakte", "odnoklassniki", "telegram"),
		"TEXT_ALIGN" => "ar_al_left",
		"TEXT_BEFORE" => "",
		"VISUAL_STYLE" => "icons"
	)
);
	}
?>