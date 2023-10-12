<?php use Bitrix\Sale\Exchange\EnteregoUserExchange;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$this->setFrameMode(true);
$templateData = array(
    'TEMPLATE_CLASS' => 'bx-' . $arParams['TEMPLATE_THEME']
);

if (isset($templateData['TEMPLATE_THEME'])) {
    $this->addExternalCss($templateData['TEMPLATE_THEME']);
}

?>
<div class="smart-filter <?= $templateData["TEMPLATE_CLASS"] ?> <?php if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL") echo "smart-filter-horizontal" ?>">
    <div class="smart-filter-section">

        <form name="<?php echo $arResult["FILTER_NAME"] . "_form" ?>" action="<?php echo $arResult["FORM_ACTION"] ?>"
              method="get" class="smart-filter-form">

            <?php foreach ($arResult["HIDDEN"] as $arItem): ?>
                <input type="hidden" name="<?php echo $arItem["CONTROL_NAME"] ?>"
                       id="<?php echo $arItem["CONTROL_ID"] ?>"
                       value="<?php echo $arItem["HTML_VALUE"] ?>"/>
            <?php endforeach; ?>

            <div class="row filter_class">
                <?php
                foreach ($arResult["ITEMS"] as $key => $arItem)//prices
                {

                    if (((int)$arItem['ID'] !== B2B_PRICE) && (($arItem['PROPERTY_TYPE'] != "N") || ($arItem['DISPLAY_TYPE'] != "A"))) {
                        continue;
                    }
                    if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                        continue;

                    if ((($arItem['PROPERTY_TYPE'] == "N") && ($arItem['DISPLAY_TYPE'] == "A")) && ((int)$arItem['ID'] !== B2B_PRICE)) {
                        $key = md5($arItem['ID']);
                    } else {
                        $key = $arItem["ENCODED_ID"];
                    }

                    if (isset($arItem["PRICE"])):
                        $step_num = 4;
                        $step = ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"]) / $step_num;
                        $prices = array();
                        if (Bitrix\Main\Loader::includeModule("currency")) {
                            for ($i = 0; $i < $step_num; $i++) {
                                $prices[$i] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MIN"]["VALUE"] + $step * $i, $arItem["VALUES"]["MIN"]["CURRENCY"], false);
                            }
                            $prices[$step_num] = CCurrencyLang::CurrencyFormat($arItem["VALUES"]["MAX"]["VALUE"], $arItem["VALUES"]["MAX"]["CURRENCY"], false);
                        } else {
                            $precision = $arItem["DECIMALS"] ? $arItem["DECIMALS"] : 0;
                            for ($i = 0; $i < $step_num; $i++) {
                                $prices[$i] = number_format($arItem["VALUES"]["MIN"]["VALUE"] + $step * $i, $precision, ".", "");
                            }
                            $prices[$step_num] = number_format($arItem["VALUES"]["MAX"]["VALUE"], $precision, ".", "");
                        }

                    endif; ?>

                    <div class="<?php if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?>col-sm-6 col-md-4<?php else: ?><?php endif ?>  mb-4 smart-filter-parameters-box bx-active">
                        <span class="smart-filter-container-modef"></span>

                        <div class="smart-filter-parameters-box-title mb-2"
                             onclick="smartFilter.hideFilterProps(this)">
                                <span class="smart-filter-parameters-box-title-text  text-sm font-medium text-dark
                                dark:text-textDarkLightGray"><?= (int)$arItem['ID'] === B2B_PRICE ? 'Цена' : $arItem['NAME'] ?>
                                </span>
                                <span data-role="prop_angle" class="smart-filter-angle smart-filter-angle-up">
                                        <i class="fa fa-angle-right smart-filter-angles" aria-hidden="true"></i>
                                </span>
                        </div>
                        <div class="smart-filter-block filter_id" data-role="bx_filter_block"
                             id="<?= $arItem['CODE'] ?>">
                            <div class="smart-filter-parameters-box-container">
                                <div class="smart-filter-input-group-number">
                                    <div class="flex flex-row items-center justify-between mb-4">
                                        <div class="form-group w-24">
                                            <div class="smart-filter-input-container w-full">
                                                <input
                                                        class="min-price px-3.5 py-3 mr-3 text-center dark:bg-grayButton
                                                         checked:hover:bg-grayButton border-iconGray dark:border-none
                                                        dark:text-white cursor-pointer font-normal rounded-lg
                                                        text-light-red checked:focus:bg-grayButton w-full"
                                                        type="number"
                                                        name="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                        id="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                        value="<?php echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                                        size="5"
                                                        placeholder="<?= $arItem["VALUES"]["MIN"]["VALUE"] ?>"
                                                        onkeyup="smartFilter.keyup(this)"
                                                />
                                            </div>
                                        </div>
                                        -
                                        <div class="form-group w-24">
                                            <div class="smart-filter-input-container w-full">
                                                <input
                                                        class="max-price px-3.5 text-center py-3 mr-3 w-full dark:bg-grayButton
                                                         checked:hover:bg-grayButton border-iconGray dark:border-none
                                                        dark:text-white cursor-pointer font-normal rounded-lg
                                                        text-light-red checked:focus:bg-grayButton"
                                                        type="number"
                                                        name="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                        id="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                        value="<?php echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                                        size="5"
                                                        placeholder="<?= $arItem["VALUES"]["MAX"]["VALUE"] ?>"
                                                        onkeyup="smartFilter.keyup(this)"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="smart-filter-slider-track-container">
                                        <div class="smart-filter-slider-track" id="drag_track_<?= $key ?>">
                                            <div class="smart-filter-slider-price-bar-vd"
                                                 style="left: 0;right: 0;"
                                                 id="colorUnavailableActive_<?= $key ?>"></div>
                                            <div class="smart-filter-slider-price-bar-vn"
                                                 style="left: 0;right: 0;"
                                                 id="colorAvailableInactive_<?= $key ?>"></div>
                                            <div class="smart-filter-slider-price-bar-v"
                                                 style="left: 0;right: 0;"
                                                 id="colorAvailableActive_<?= $key ?>"></div>
                                            <div class="smart-filter-slider-range" id="drag_tracker_<?= $key ?>"
                                                 style="left: 0; right: 0;">
                                                <a class="smart-filter-slider-handle left" style=""
                                                   href="javascript:void(0)" id="left_slider_<?= $key ?>"></a>
                                                <a class="smart-filter-slider-handle right" style=""
                                                   href="javascript:void(0)" id="right_slider_<?= $key ?>"></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="<?php if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?>col-sm-6 col-md-4<?php else: ?><?php endif ?> hide_not_available_container
                 smart-filter-parameters-box bx-active mb-5">
                        <span class="smart-filter-container-modef"></span>
                        <div class="smart-filter-block filter_id" data-role="bx_filter_block"
                             id="hide_not_available_container_id">
                            <div class="smart-filter-parameters-box-container">
                                <div class="form-group form-check hide_not_available flex flex-row items-center">
                                    <input
                                            type="checkbox"
                                            value="<?php echo $arParams["HIDE_NOT_AVAILABLE"] ?>"
                                            name="hide_not_available"
                                            id="hide_not_available_id"
                                            class="check_input form-check-input xs:p-5 p-3.5 mr-3 dark:bg-grayButton
                                                         checked:hover:bg-grayButton border-iconGray dark:border-none
                                                        dark:text-white cursor-pointer font-normal rounded-lg
                                                        text-light-red checked:focus:bg-grayButton"
                                        <?php echo $arParams["HIDE_NOT_AVAILABLE"] == 'Y' ? 'checked="checked"' : '' ?>
                                            onclick="smartFilter.click(this)"
                                    />
                                    <label data-role="label_in_stoсk"
                                           class="smart-filter-checkbox-text text-sm font-medium text-dark
                                           dark:text-textDarkLightGray hide_not_available"
                                           id="hide_not_available_label" for="<?php echo $ar["CONTROL_ID"] ?>">
                                        В наличии
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                $arJsParams = array(
                    "leftSlider" => 'left_slider_' . $key,
                    "rightSlider" => 'right_slider_' . $key,
                    "tracker" => "drag_tracker_" . $key,
                    "trackerWrap" => "drag_track_" . $key,
                    "minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
                    "maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
                    "minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
                    "maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
                    "curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
                    "curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
                    "fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"],
                    "fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
                    "precision" => $precision,
                    "colorUnavailableActive" => 'colorUnavailableActive_' . $key,
                    "colorAvailableActive" => 'colorAvailableActive_' . $key,
                    "colorAvailableInactive" => 'colorAvailableInactive_' . $key,
                );
                ?>
                    <script type="text/javascript">
                        BX.ready(function () {
                            window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
                        });
                    </script>
                    <?
                }


                //not price

                foreach ($arResult["ITEMS"] as $key => $arItem) {
                    if (empty($arItem["VALUES"]) || isset($arItem["PRICE"]) || (($arItem['PROPERTY_TYPE'] == "N") && ($arItem['DISPLAY_TYPE'] == "A")))
                        continue;

                    if ($arItem["DISPLAY_TYPE"] == "A" && ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0))
                        continue;
                    ?>

                    <div class="<?= $arItem['CODE'] == 'MODEL_KALYANA' ? 'model_kalyana hidden' : '' ?>
                    <?php if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?> col-sm-6 col-md-4 <?php else: ?>
                    <?php endif ?> mb-4 smart-filter-parameters-box
                    <?php if ($arItem["DISPLAY_EXPANDED"] == "Y") { ?>bx-active<?php } ?>">
                        <span class="smart-filter-container-modef"></span>
                        <div class="smart-filter-parameters-box-title mb-2 flex flex-row items-center cursor-pointer"
                             onclick="smartFilter.hideFilterProps(this)">
                            <span data-role="prop_angle" class="smart-filter-angle smart-filter-angle-down mr-3">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                     xmlns="http://www.w3.org/2000/svg" class="smart-filter-angles">
                                     <path d="M1.89089 4.49273C1.50382 4.88766 1.50382 5.52803 1.89089 5.92295L6.73974 10.8657C7.51402 11.6549 8.76861 11.6546 9.54249 10.8651L14.3895 5.91931C14.7766 5.52439 14.7766 4.88402 14.3895 4.48908C14.0024 4.09413 13.3748 4.09413 12.9878 4.48908L8.83927 8.72208C8.45223 9.1171 7.82464 9.117 7.4376 8.72208L3.29257 4.49273C2.90551 4.09778 2.27795 4.09778 1.89089 4.49273Z"
                                           fill="#838383"/>
                                </svg>
                            </span>
                            <span class="smart-filter-parameters-box-title-text
                            text-sm font-medium text-dark  dark:text-textDarkLightGray"><?= $arItem["NAME"] ?></span>
                            <?php if ($arItem["FILTER_HINT"] <> ""): ?>
                                <span class="smart-filter-hint">
									<span class="smart-filter-hint-icon">?</span>
									<span class="smart-filter-hint-popup">
										<span class="smart-filter-hint-popup-angle"></span>
										<span class="smart-filter-hint-popup-content">

										</span>	<?= $arItem["FILTER_HINT"] ?></span>
								</span>
                            <?php endif ?>
                        </div>
                        <div class="smart-filter-block filter_id" data-role="bx_filter_block"
                             id="<?= $arItem['CODE'] ?>">
                            <div class="smart-filter-parameters-box-container">
                                <?
                                $arCur = current($arItem["VALUES"]);
                                switch ($arItem["DISPLAY_TYPE"]) {
                                    //region NUMBERS_WITH_SLIDER +
                                case "A":
                                    ?>
                                    <div class="smart-filter-input-group-number">
                                        <div class="d-flex justify-content-between align-items-center">

                                            <div class="form-group" style="width: calc(50% - 10px);">
                                                <div class="smart-filter-input-container">
                                                    <input class="min-price form-control form-control-sm"
                                                           type="number"
                                                           name="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                           id="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                           value="<?php echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                                           size="5"
                                                           placeholder="<?= $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                                           onkeyup="smartFilter.keyup(this)"
                                                    />
                                                </div>
                                            </div>
                                            -
                                            <div class="form-group" style="width: calc(50% - 10px);">
                                                <div class="smart-filter-input-container">
                                                    <input
                                                            class="max-price form-control form-control-sm"
                                                            type="number"
                                                            name="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                            id="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                            value="<?php echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                                            size="5"
                                                            placeholder="<?= $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                                            onkeyup="smartFilter.keyup(this)"
                                                    />
                                                </div>
                                            </div>

                                        </div>

                                        <div class="smart-filter-slider-track-container">
                                            <div class="smart-filter-slider-track" id="drag_track_<?= $key ?>">
                                                <div class="smart-filter-slider-price-bar-vd" style="left: 0;right: 0;"
                                                     id="colorUnavailableActive_<?= $key ?>"></div>
                                                <div class="smart-filter-slider-price-bar-vn" style="left: 0;right: 0;"
                                                     id="colorAvailableInactive_<?= $key ?>"></div>
                                                <div class="smart-filter-slider-price-bar-v" style="left: 0;right: 0;"
                                                     id="colorAvailableActive_<?= $key ?>"></div>
                                                <div class="smart-filter-slider-range" id="drag_tracker_<?= $key ?>"
                                                     style="left: 0;right: 0;">
                                                    <a class="smart-filter-slider-handle left" style=""
                                                       href="javascript:void(0)" id="left_slider_<?= $key ?>"></a>
                                                    <a class="smart-filter-slider-handle right" style=""
                                                       href="javascript:void(0)" id="right_slider_<?= $key ?>"></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                <?
                                $arJsParams = array(
                                    "leftSlider" => 'left_slider_' . $key,
                                    "rightSlider" => 'right_slider_' . $key,
                                    "tracker" => "drag_tracker_" . $key,
                                    "trackerWrap" => "drag_track_" . $key,
                                    "minInputId" => $arItem["VALUES"]["MIN"]["CONTROL_ID"],
                                    "maxInputId" => $arItem["VALUES"]["MAX"]["CONTROL_ID"],
                                    "minPrice" => $arItem["VALUES"]["MIN"]["VALUE"],
                                    "maxPrice" => $arItem["VALUES"]["MAX"]["VALUE"],
                                    "curMinPrice" => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
                                    "curMaxPrice" => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
                                    "fltMinPrice" => intval($arItem["VALUES"]["MIN"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MIN"]["FILTERED_VALUE"] : $arItem["VALUES"]["MIN"]["VALUE"],
                                    "fltMaxPrice" => intval($arItem["VALUES"]["MAX"]["FILTERED_VALUE"]) ? $arItem["VALUES"]["MAX"]["FILTERED_VALUE"] : $arItem["VALUES"]["MAX"]["VALUE"],
                                    "precision" => $arItem["DECIMALS"] ? $arItem["DECIMALS"] : 0,
                                    "colorUnavailableActive" => 'colorUnavailableActive_' . $key,
                                    "colorAvailableActive" => 'colorAvailableActive_' . $key,
                                    "colorAvailableInactive" => 'colorAvailableInactive_' . $key,
                                );
                                die();
                                ?>
                                    <script type="text/javascript">
                                        BX.ready(function () {
                                            window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
                                        });
                                    </script>
                                <?

                                break;

                                //endregion

                                //region NUMBERS +
                                case "B":
                                ?>
                                    <div class="smart-filter-input-group-number">
                                        <div class="d-flex justify-content-between">
                                            <div class="form-group" style="width: calc(50% - 10px);">
                                                <div class="smart-filter-input-container">
                                                    <input
                                                            class="min-price form-control form-control-sm"
                                                            type="number"
                                                            name="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                            id="<?php echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                            value="<?php echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                                            size="5"
                                                            placeholder="<?= $arItem["VALUES"]["MIN"]["VALUE"] ?>"
                                                            onkeyup="smartFilter.keyup(this)"
                                                    />
                                                </div>
                                            </div>

                                            <div class="form-group" style="width: calc(50% - 10px);">
                                                <div class="smart-filter-input-container">
                                                    <input
                                                            class="max-price form-control form-control-sm"
                                                            type="number"
                                                            name="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                            id="<?php echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                            value="<?php echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
                                                            size="5"
                                                            placeholder="<?= $arItem["VALUES"]["MAX"]["VALUE"] ?>"
                                                            onkeyup="smartFilter.keyup(this)"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region CHECKBOXES_WITH_PICTURES +
                                case "G":
                                ?>
                                    <div class="smart-filter-input-group-checkbox-pictures">
                                        <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <input
                                                    style="display: none"
                                                    type="checkbox"
                                                    name="<?= $ar["CONTROL_NAME"] ?>"
                                                    id="<?= $ar["CONTROL_ID"] ?>"
                                                    value="<?= $ar["HTML_VALUE"] ?>"
                                                <?php echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                            />
                                            <?
                                            $class = "";
                                            if ($ar["CHECKED"])
                                                $class .= " bx-active";
                                            if ($ar["DISABLED"])
                                                $class .= " disabled";
                                            ?>
                                            <label for="<?= $ar["CONTROL_ID"] ?>"
                                                   data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                   class="smart-filter-checkbox-label<?= $class ?>"
                                                   onclick="smartFilter.keyup(BX('<?= CUtil::JSEscape($ar["CONTROL_ID"]) ?>')); BX.toggleClass(this, 'bx-active');">
												<span class="smart-filter-checkbox-btn bx-color-sl">
													<?php if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                        <span class="smart-filter-checkbox-btn-image"
                                                              style="background-image: url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                    <?php endif ?>
												</span>
                                            </label>
                                        <?php endforeach ?>
                                        <div style="clear: both;"></div>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region CHECKBOXES_WITH_PICTURES_AND_LABELS +
                                case "H":
                                ?>
                                    <div class="smart-filter-input-group-checkbox-pictures-text">
                                        <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <input
                                                    style="display: none"
                                                    type="checkbox"
                                                    name="<?= $ar["CONTROL_NAME"] ?>"
                                                    id="<?= $ar["CONTROL_ID"] ?>"
                                                    value="<?= $ar["HTML_VALUE"] ?>"
                                                <?php echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                            />
                                            <?
                                            $class = "";
                                            if ($ar["CHECKED"])
                                                $class .= " bx-active";
                                            if ($ar["DISABLED"])
                                                $class .= " disabled";
                                            ?>
                                            <label for="<?= $ar["CONTROL_ID"] ?>"
                                                   data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                   class="smart-filter-checkbox-label<?= $class ?>"
                                                   onclick="smartFilter.keyup(BX('<?= CUtil::JSEscape($ar["CONTROL_ID"]) ?>')); BX.toggleClass(this, 'bx-active');">
											<span class="smart-filter-checkbox-btn">
												<?php if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                    <span class="smart-filter-checkbox-btn-image"
                                                          style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                <?php endif ?>
											</span>
                                                <span class="smart-filter-checkbox-text" title="<?= $ar["VALUE"]; ?>">
												<?= $ar["VALUE"]; ?>
											</span>
                                            </label>
                                        <?php endforeach ?>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region DROPDOWN +
                                case "P":
                                ?>
                                <?php $checkedItemExist = false; ?>
                                    <div class="smart-filter-input-group-dropdown">
                                        <div class="smart-filter-dropdown-block"
                                             onclick="smartFilter.showDropDownPopup(this, '<?= CUtil::JSEscape($key) ?>')">
                                            <div class="smart-filter-dropdown-text" data-role="currentOption">
                                                <?php foreach ($arItem["VALUES"] as $val => $ar) {
                                                    if ($ar["CHECKED"]) {
                                                        echo $ar["VALUE"];
                                                        $checkedItemExist = true;
                                                    }
                                                }
                                                if (!$checkedItemExist) {
                                                    echo GetMessage("CT_BCSF_FILTER_ALL");
                                                }
                                                ?>
                                            </div>
                                            <div class="smart-filter-dropdown-arrow"></div>
                                            <input
                                                    style="display: none"
                                                    type="radio"
                                                    name="<?= $arCur["CONTROL_NAME_ALT"] ?>"
                                                    id="<?php echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                    value=""
                                            />
                                            <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                                <input
                                                        style="display: none"
                                                        type="radio"
                                                        name="<?= $ar["CONTROL_NAME_ALT"] ?>"
                                                        id="<?= $ar["CONTROL_ID"] ?>"
                                                        value="<?php echo $ar["HTML_VALUE_ALT"] ?>"
                                                    <?php echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                />
                                            <?php endforeach ?>

                                            <div class="smart-filter-dropdown-popup" data-role="dropdownContent"
                                                 style="display: none;">
                                                <ul>
                                                    <li>
                                                        <label for="<?= "all_" . $arCur["CONTROL_ID"] ?>"
                                                               class="smart-filter-dropdown-label"
                                                               data-role="label_<?= "all_" . $arCur["CONTROL_ID"] ?>"
                                                               onclick="smartFilter.selectDropDownItem(this, '<?= CUtil::JSEscape("all_" . $arCur["CONTROL_ID"]) ?>')">
                                                            <?= GetMessage("CT_BCSF_FILTER_ALL"); ?>
                                                        </label>
                                                    </li>
                                                    <?php foreach ($arItem["VALUES"] as $val => $ar):
                                                        $class = "";
                                                        if ($ar["CHECKED"])
                                                            $class .= " selected";
                                                        if ($ar["DISABLED"])
                                                            $class .= " disabled";
                                                        ?>
                                                        <li>
                                                            <label for="<?= $ar["CONTROL_ID"] ?>"
                                                                   class="smart-filter-dropdown-label<?= $class ?>"
                                                                   data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                                   onclick="smartFilter.selectDropDownItem(this, '<?= CUtil::JSEscape($ar["CONTROL_ID"]) ?>')">
                                                                <?= $ar["VALUE"] ?>
                                                            </label>
                                                        </li>
                                                    <?php endforeach ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region DROPDOWN_WITH_PICTURES_AND_LABELS
                                case "R":
                                ?>
                                    <div class="smart-filter-input-group-dropdown">
                                        <div class="smart-filter-dropdown-block"
                                             onclick="smartFilter.showDropDownPopup(this, '<?= CUtil::JSEscape($key) ?>')">
                                            <div class="smart-filter-input-group-dropdown-flex"
                                                 data-role="currentOption">
                                                <?
                                                $checkedItemExist = false;
                                                foreach ($arItem["VALUES"] as $val => $ar):
                                                    if ($ar["CHECKED"]) {
                                                        ?>
                                                        <?php if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                            <span class="smart-filter-checkbox-btn-image"
                                                                  style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                        <?php endif ?>
                                                        <span class="smart-filter-dropdown-text"><?= $ar["VALUE"] ?></span>
                                                        <?
                                                        $checkedItemExist = true;
                                                    }
                                                endforeach;
                                                if (!$checkedItemExist) {
                                                    ?>
                                                    <span class="smart-filter-checkbox-btn-image all"></span>
                                                    <span class="smart-filter-dropdown-text"><?= GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
                                                    <?
                                                }
                                                ?>
                                            </div>

                                            <div class="smart-filter-dropdown-arrow"></div>

                                            <input
                                                    style="display: none"
                                                    type="radio"
                                                    name="<?= $arCur["CONTROL_NAME_ALT"] ?>"
                                                    id="<?php echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                    value=""
                                            />
                                            <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                                <input
                                                        style="display: none"
                                                        type="radio"
                                                        name="<?= $ar["CONTROL_NAME_ALT"] ?>"
                                                        id="<?= $ar["CONTROL_ID"] ?>"
                                                        value="<?= $ar["HTML_VALUE_ALT"] ?>"
                                                    <?php echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                />
                                            <?php endforeach ?>

                                            <div class="smart-filter-dropdown-popup" data-role="dropdownContent"
                                                 style="display: none">
                                                <ul>
                                                    <li style="border-bottom: 1px solid #e5e5e5;padding-bottom: 5px;margin-bottom: 5px;">
                                                        <label for="<?= "all_" . $arCur["CONTROL_ID"] ?>"
                                                               class="smart-filter-param-label"
                                                               data-role="label_<?= "all_" . $arCur["CONTROL_ID"] ?>"
                                                               onclick="smartFilter.selectDropDownItem(this, '<?= CUtil::JSEscape("all_" . $arCur["CONTROL_ID"]) ?>')">
                                                            <span class="smart-filter-checkbox-btn-image all"></span>
                                                            <span class="smart-filter-dropdown-text"><?= GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
                                                        </label>
                                                    </li>
                                                    <?
                                                    foreach ($arItem["VALUES"] as $val => $ar):
                                                        $class = "";
                                                        if ($ar["CHECKED"])
                                                            $class .= " selected";
                                                        if ($ar["DISABLED"])
                                                            $class .= " disabled";
                                                        ?>
                                                        <li>
                                                            <label for="<?= $ar["CONTROL_ID"] ?>"
                                                                   data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                                   class="smart-filter-param-label<?= $class ?>"
                                                                   onclick="smartFilter.selectDropDownItem(this, '<?= CUtil::JSEscape($ar["CONTROL_ID"]) ?>')">
                                                                <?php if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                                    <span class="smart-filter-checkbox-btn-image"
                                                                          style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                                <?php endif ?>
                                                                <span class="smart-filter-dropdown-text"><?= $ar["VALUE"] ?></span>
                                                            </label>
                                                        </li>
                                                    <?php endforeach ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region RADIO_BUTTONS
                                case "K":
                                ?>
                                    <div class="col">
                                        <div class="radio">
                                            <label class="smart-filter-param-label"
                                                   for="<?php echo "all_" . $arCur["CONTROL_ID"] ?>">
												<span class="smart-filter-input-checkbox">
													<input
                                                            type="radio"
                                                            value=""
                                                            name="<?php echo $arCur["CONTROL_NAME_ALT"] ?>"
                                                            id="<?php echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                            onclick="smartFilter.click(this)"
                                                    />
													<span class="smart-filter-param-text"><?php echo GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
												</span>
                                            </label>
                                        </div>
                                        <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <div class="radio">
                                                <label data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                       class="smart-filter-param-label"
                                                       for="<?php echo $ar["CONTROL_ID"] ?>">
													<span class="smart-filter-input-checkbox <?php echo $ar["DISABLED"] ? 'disabled' : '' ?>">
														<input
                                                                type="radio"
                                                                value="<?php echo $ar["HTML_VALUE_ALT"] ?>"
                                                                name="<?php echo $ar["CONTROL_NAME_ALT"] ?>"
                                                                id="<?php echo $ar["CONTROL_ID"] ?>"
															<?php echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
															onclick="smartFilter.click(this)"
                                                        />
														<span class="smart-filter-param-text"
                                                              title="<?= $ar["VALUE"]; ?>"><?= $ar["VALUE"]; ?><?
                                                            if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])):
                                                                ?><?
                                                            endif; ?></span>
													</span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="w-100"></div>
                                <?
                                break;

                                //endregion

                                //region CALENDAR
                                case "U":
                                ?>
                                    <div class="col">
                                        <div class="">
                                            <div class="smart-filter-input-container smart-filter-calendar-container">
                                                <?php $APPLICATION->IncludeComponent(
                                                    'bitrix:main.calendar',
                                                    '',
                                                    array(
                                                        'FORM_NAME' => $arResult["FILTER_NAME"] . "_form",
                                                        'SHOW_INPUT' => 'Y',
                                                        'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="' . FormatDate("SHORT", $arItem["VALUES"]["MIN"]["VALUE"]) . '" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
                                                        'INPUT_NAME' => $arItem["VALUES"]["MIN"]["CONTROL_NAME"],
                                                        'INPUT_VALUE' => $arItem["VALUES"]["MIN"]["HTML_VALUE"],
                                                        'SHOW_TIME' => 'N',
                                                        'HIDE_TIMEBAR' => 'Y',
                                                    ),
                                                    null,
                                                    array('HIDE_ICONS' => 'Y')
                                                ); ?>
                                            </div>
                                        </div>
                                        <div class="">
                                            <div class="smart-filter-input-container smart-filter-calendar-container">
                                                <?php $APPLICATION->IncludeComponent(
                                                    'bitrix:main.calendar',
                                                    '',
                                                    array(
                                                        'FORM_NAME' => $arResult["FILTER_NAME"] . "_form",
                                                        'SHOW_INPUT' => 'Y',
                                                        'INPUT_ADDITIONAL_ATTR' => 'class="calendar" placeholder="' . FormatDate("SHORT", $arItem["VALUES"]["MAX"]["VALUE"]) . '" onkeyup="smartFilter.keyup(this)" onchange="smartFilter.keyup(this)"',
                                                        'INPUT_NAME' => $arItem["VALUES"]["MAX"]["CONTROL_NAME"],
                                                        'INPUT_VALUE' => $arItem["VALUES"]["MAX"]["HTML_VALUE"],
                                                        'SHOW_TIME' => 'N',
                                                        'HIDE_TIMEBAR' => 'Y',
                                                    ),
                                                    null,
                                                    array('HIDE_ICONS' => 'Y')
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-100"></div>
                                <?
                                break;
                                //endregion

                                //region CHECKBOXES +
                                default:
                                usort(
                                    $arItem["VALUES"],
                                    function ($a, $b) {
                                        if (is_string($a['VALUE']) && is_string($a['VALUE'])) {
                                            $a['VALUE'] = str_replace(['Ё', 'ё'], ['Е', 'е'], $a['VALUE']);
                                            $b['VALUE'] = str_replace(['Ё', 'ё'], ['Е', 'е'], $b['VALUE']);
                                        }

                                        return $a["VALUE"] <=> $b["VALUE"];
                                    }
                                );
                                ?>
                                    <div class="smart-filter-input-group-checkbox-list overflow-auto max-h-64 <?php if ($arItem["DISPLAY_EXPANDED"] !== "Y") { ?> hidden <?php } ?>">
                                        <?php foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <div class="form-group form-check mb-2">
                                                <input
                                                        type="checkbox"
                                                        value="<?= $ar["HTML_VALUE"] ?>"
                                                        name="<?= $ar["CONTROL_NAME"] ?>"
                                                        id="<?= $ar["CONTROL_ID"] ?>"
                                                        class="check_input xs:p-5 p-3.5 dark:bg-grayButton
                                                         checked:hover:bg-grayButton border-iconGray dark:border-none
                                                        dark:text-white cursor-pointer font-normal rounded-lg
                                                        text-light-red checked:focus:bg-grayButton
                                                        <?= $arItem['CODE'] == 'BREND' ? 'brends_checkbox' : '' ?>
                                                        form-check-input"
                                                    <?= $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                        onclick="smartFilter.click(this)"
                                                />
                                                <label data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                       class="smart-filter-checkbox-text form-check-label font-light
                                                        text-dark dark:text-textDarkLightGray text-sm ml-2"
                                                       id="<?= $ar["VALUE"]; ?>" for="<?php echo $ar["CONTROL_ID"] ?>">
                                                    <?= $ar["VALUE"]; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                <?php
                                    //endregion
                                }
                                ?>
                            </div>
                        </div>

                    </div>
                    <?
                }
                ?>
                <!--                sus-->
                <div class="button_box">
                    <div class="smart-filter-button-box">
                        <div class="smart-filter-block">
                            <div class="smart-filter-parameters-box-container">
                                <div class="smart-filter-popup-result <?php if ($arParams["FILTER_VIEW_MODE"] == "VERTICAL") echo $arParams["POPUP_POSITION"] ?>"
                                     id="modef" <?php if (!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"'; ?>
                                     style="display: inline-block;">
                                    <?php echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num" style="display: none">' . intval($arResult["ELEMENT_COUNT"]) . '</span>')); ?>
                                    <span class="arrow"></span>
                                    <a href="javascript:;"
                                       target=""><?php echo GetMessage("CT_BCSF_FILTER_SHOW") ?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--//row-->


        </form>

    </div>
</div>

<script type="text/javascript">
    var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
    window.smartFilter = smartFilter
    $(document).ready(function () {
        $('input.check_input.form-check-input:checked').each(function () {
            smartFilter.addHorizontalFilter(this);
        });
    });
</script>
