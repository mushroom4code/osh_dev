<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @global CMain $APPLICATION */
/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$uiFilter = isset($arParams["UI_FILTER"]) && $arParams["UI_FILTER"];
if ($uiFilter)
{
	$arParams["USE_POPUP"] = true;
}

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

if(!empty($arResult['ERRORS']['FATAL'])):
	foreach($arResult['ERRORS']['FATAL'] as $error):
		ShowError($error);
	endforeach;
else:
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_widget.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_etc.js');
	$APPLICATION->AddHeadScript('/bitrix/js/sale/core_ui_autocomplete.js');
	?>
	<div id="sls-<?=$arResult['RANDOM_TAG']?>" class="bx-sls <?= ($arResult['MODE_CLASSES'] !== '' ? $arResult['MODE_CLASSES'] : ''); ?>">

		<?php
		if (!empty($arResult['DEFAULT_LOCATIONS']) && is_array($arResult['DEFAULT_LOCATIONS'])):
		?>
			<div class="bx-ui-sls-quick-locations quick-locations">
				<?php
				foreach($arResult['DEFAULT_LOCATIONS'] as $lid => $loc):
				?>
					<a href="javascript:void(0)" data-id="<?=intval($loc['ID'])?>" class="quick-location-tag"><?=htmlspecialcharsbx($loc['NAME'])?></a>
				<?php
				endforeach;
				?>
			</div>
		<?php
		endif;

		$dropDownBlock = $uiFilter ? "dropdown-block-ui" : "dropdown-block"; ?>
		<div class="<?=$dropDownBlock?> bx-ui-sls-input-block border-solid relative bg-white shadow-none border-[1px]
		    pl-[30px] pr-[22px] pt-[2px] min-h-[38px] lg:h-[45px] md:h-[45px] h-[50px] rounded-lg border-grey-line-order">

			<span class="dropdown-icon">
                <div class="flex items-center justify-center h-full w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="20px" height="20px" viewBox="0 0 100 100"
                         style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
                         xmlns:xlink="http://www.w3.org/1999/xlink">
                        <path style="opacity:0.843" class="dark:fill-white fill-grey-line-order"
                              d="M 33.5,5.5 C 52.4653,3.06168 65.9653,10.395 74,27.5C 77.3658,39.7426 75.5325,51.076 68.5,61.5C 76.4855,69.9858 84.6522,78.3191 93,86.5C 92.4535,89.0933 90.9535,91.2599 88.5,93C 87.8333,93.6667 87.1667,93.6667 86.5,93C 78.5,85 70.5,77 62.5,69C 58.1643,69.9827 53.8309,71.316 49.5,73C 28.4094,76.0481 14.5761,67.5481 8,47.5C 5.07436,26.3225 13.5744,12.3225 33.5,5.5 Z M 34.5,10.5 C 52.4135,8.27962 64.2469,15.613 70,32.5C 72.7123,50.4302 65.5457,62.2635 48.5,68C 30.5698,70.7123 18.7365,63.5457 13,46.5C 10.4994,28.5416 17.666,16.5416 34.5,10.5 Z"/>
                    </svg>
                </div>
            </span>
			<input type="text" autocomplete="off" name="<?=$arParams['INPUT_NAME']?>" value="<?=$arResult['VALUE']?>"
                   class="dropdown-field cursor-text" placeholder="<?=Loc::getMessage('SALE_SLS_INPUT_SOME')?> ..." />

			<div class="dropdown-fade2white"></div>
			<div class="bx-ui-sls-loader">
                <div class="flex items-center justify-center h-full w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: transparent; display: block; shape-rendering: auto;" width="20px" height="20px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                        <g transform="rotate(0 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.9090909090909091s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(32.72727272727273 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.8181818181818182s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(65.45454545454545 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.7272727272727273s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(98.18181818181819 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.6363636363636364s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(130.9090909090909 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.5454545454545454s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(163.63636363636363 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.45454545454545453s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(196.36363636363637 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.36363636363636365s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(229.0909090909091 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.2727272727272727s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(261.8181818181818 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.18181818181818182s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(294.54545454545456 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="-0.09090909090909091s" repeatCount="indefinite"></animate>
                            </rect>
                        </g><g transform="rotate(327.27272727272725 50 50)">
                            <rect x="48.5" y="19.5" rx="0" ry="0" width="3" height="21"
                                  class="dark:fill-white fill-grey-line-order">
                                <animate attributeName="opacity" values="1;0" keyTimes="0;1" dur="1s" begin="0s" repeatCount="indefinite"></animate>
                            </rect>
                        </g>
                </div>
            </div>
			<div class="bx-ui-sls-clear" title="<?=Loc::getMessage('SALE_SLS_CLEAR_SELECTION')?>">
                <div class="flex items-center justify-center h-full w-full">
                    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="20px" height="20px" viewBox="0 0 100 100"
                         style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd"
                         xmlns:xlink="http://www.w3.org/1999/xlink">
                            <path style="opacity:0.955" class="dark:fill-white fill-grey-line-order"
                                  d="M 40.5,9.5 C 63.3294,6.54685 79.1628,15.5469 88,36.5C 93.7177,60.8973 85.2177,78.064 62.5,88C 38.1027,93.7177 20.936,85.2177 11,62.5C 5.42409,35.977 15.2574,18.3104 40.5,9.5 Z M 34.5,33.5 C 35.552,33.3505 36.552,33.5172 37.5,34C 41.3484,38.0156 45.3484,41.8489 49.5,45.5C 53.6516,41.8489 57.6516,38.0156 61.5,34C 64.6667,33.1667 65.8333,34.3333 65,37.5C 60.9844,41.3484 57.1511,45.3484 53.5,49.5C 57.1511,53.6516 60.9844,57.6516 65,61.5C 65.8333,64.6667 64.6667,65.8333 61.5,65C 57.6516,60.9844 53.6516,57.1511 49.5,53.5C 45.3484,57.1511 41.3484,60.9844 37.5,65C 34.3333,65.8333 33.1667,64.6667 34,61.5C 38.0156,57.6516 41.8489,53.6516 45.5,49.5C 41.8489,45.3484 38.0156,41.3484 34,37.5C 33.4209,36.0715 33.5876,34.7382 34.5,33.5 Z"/>
                    </svg>
                </div>
            </div>
			<div class="bx-ui-sls-pane"></div>
		</div>

		<script type="text/html" data-template-id="bx-ui-sls-error">
			<div class="bx-ui-sls-error">
				<div></div>
				{{message}}
			</div>
		</script>

		<script type="text/html" data-template-id="bx-ui-sls-dropdown-item">
			<div class="dropdown-item bx-ui-sls-variant">
				<span class="dropdown-item-text">{{display_wrapped}}</span>
				<?php
				if($arResult['ADMIN_MODE']):?>
					[{{id}}]
				<?php
				endif;
				?>
			</div>
		</script>

		<div class="bx-ui-sls-error-message">
			<?php
			if (!isset($arParams['SUPPRESS_ERRORS']) || !$arParams['SUPPRESS_ERRORS']):
				if(!empty($arResult['ERRORS']['NONFATAL'])):
					foreach($arResult['ERRORS']['NONFATAL'] as $error):
						ShowError($error);
					endforeach;
				endif;
			endif;
			?>
		</div>

	</div>

	<script>

		if (!window.BX && top.BX)
			window.BX = top.BX;

		<?php
		if($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):
		?>
		if (typeof window.BX.locationsDeferred == 'undefined') window.BX.locationsDeferred = {};
		window.BX.locationsDeferred['<?=$arParams['JS_CONTROL_DEFERRED_INIT']?>'] = function () {
		<?php
		endif;

			if($arParams['JS_CONTROL_GLOBAL_ID'] <> ''):
			?>
			if (typeof window.BX.locationSelectors == 'undefined') window.BX.locationSelectors = {};
			window.BX.locationSelectors['<?=$arParams['JS_CONTROL_GLOBAL_ID']?>'] =
			<?php
			endif;
			?>

			new BX.Sale.component.location.selector.search(<?=CUtil::PhpToJSObject(array(

				// common
				'scope' => 'sls-'.$arResult['RANDOM_TAG'],
				'source' => $this->__component->getPath().'/get.php',
				'query' => array(
					'FILTER' => array(
						'EXCLUDE_ID' => intval($arParams['EXCLUDE_SUBTREE']),
						'SITE_ID' => $arParams['FILTER_BY_SITE'] && !empty($arParams['FILTER_SITE_ID']) ? $arParams['FILTER_SITE_ID'] : ''
					),
					'BEHAVIOUR' => array(
						'SEARCH_BY_PRIMARY' => $arParams['SEARCH_BY_PRIMARY'] ? '1' : '0',
						'LANGUAGE_ID' => LANGUAGE_ID
					),
				),

				'selectedItem' => !empty($arResult['LOCATION']) ? $arResult['LOCATION']['VALUE'] : false,
				'knownItems' => $arResult['KNOWN_ITEMS'],
				'provideLinkBy' => $arParams['PROVIDE_LINK_BY'],

				'messages' => array(
					'nothingFound' => Loc::getMessage('SALE_SLS_NOTHING_FOUND'),
					'error' => Loc::getMessage('SALE_SLS_ERROR_OCCURED'),
				),

				// "js logic"-related part
				'callback' => $arParams['JS_CALLBACK'],
				'useSpawn' => $arParams['USE_JS_SPAWN'] == 'Y',
				'usePopup' => (bool)$arParams["USE_POPUP"],
				'initializeByGlobalEvent' => $arParams['INITIALIZE_BY_GLOBAL_EVENT'],
				'globalEventScope' => $arParams['GLOBAL_EVENT_SCOPE'],

				// specific
				'pathNames' => $arResult['PATH_NAMES'], // deprecated
				'types' => $arResult['TYPES'],

			), false, false, true)?>);

		<?php
		if ($arParams['JS_CONTROL_DEFERRED_INIT'] <> ''):
		?>
		};
		<?php
		endif;
		?>

	</script>

<?php
endif;
