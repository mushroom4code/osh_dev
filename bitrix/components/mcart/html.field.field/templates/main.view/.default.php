<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 */

?>
<span class="fields html">
<?
$first = true;
foreach ($arResult["value"] as $res)
{
	if (!$first)
	{
		?><span class="fields separator"></span><?
	}
	else
	{
		$first = false;
	}

	if (StrLen($arParams["userField"]["PROPERTY_VALUE_LINK"]) > 0)
	{
		$res = '<a href="'.html_entity_decode(htmlspecialcharsbx(str_replace('#VALUE#', urlencode($res), $arParams['userField']['PROPERTY_VALUE_LINK']))).'">'.$res.'</a>';
	}

?><span class="fields html"><?=html_entity_decode(htmlspecialcharsback($res))?></span><?

}
?>
</span>
