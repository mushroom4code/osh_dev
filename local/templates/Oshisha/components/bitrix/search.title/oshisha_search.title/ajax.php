<?php
use Enterego\EnteregoSettings;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

if (empty($arResult["CATEGORIES"])) {
	return;
}?>
<div tabindex="0" id="search_results_container" class="bx_searche">
	<?php foreach ($arResult["CATEGORIES"] as $category_id => $arCategory) {
		foreach ($arCategory["ITEMS"] as $i => $arItem) {
			/**@var $arItem */
			if ($category_id === "all") { ?>
				<div class="bx_item_block all_result" onclick="window.location='<? echo $arItem["URL"] ?>';">
					<div class="bx_item_element">
                        <span class="all_result_title">
                            <a href="<? echo $arItem["URL"] ?>"><? echo $arItem["NAME"] ?></a>
                        </span>
					</div>
					<div style="clear:both;"></div>
				</div>
			<?php } elseif (isset($arResult["ELEMENTS"]['PRODUCT'][$arItem["ITEM_ID"]])) {
				$arElement = $arResult["ELEMENTS"]['PRODUCT'][$arItem["ITEM_ID"]]['INFO'];
				$itemOffers = $arResult["ELEMENTS"]['PRODUCT'][$arItem["ITEM_ID"]]['OFFERS'];
				$propsForOffers = EnteregoSettings::getDataPropOffers();
				if (!empty($itemOffers)) {
					require(__DIR__ . '/product/product_offers.php');
				} else {
					require(__DIR__ . '/product/product.php');
				}
			}
		}
	}
	if (!empty($arResult['popularSearches'])) {
		require_once(__DIR__ . '/popularSearches.php');
	} ?>
</div>
<script>
    $('#search_results_container').focusout(function () {
        if (!event.relatedTarget
            || ((event.relatedTarget.getAttribute('id') != 'input_search_desktop')
                && ($('#search_results_container').find(event.relatedTarget).length != 1))) {
            setTimeout(function () {
                $('#search_results_container').parent().css("display", "none");
            }, 250);
        }
    })

    tasteInit();
</script>
