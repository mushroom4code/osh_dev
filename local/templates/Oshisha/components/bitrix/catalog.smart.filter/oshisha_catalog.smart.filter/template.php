<? use Bitrix\Sale\Exchange\EnteregoUserExchange;

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

PriceTypeANDStatusUser();

if (isset($templateData['TEMPLATE_THEME'])) {
    $this->addExternalCss($templateData['TEMPLATE_THEME']);
}

?>
<div class="smart-filter <?= $templateData["TEMPLATE_CLASS"] ?> <? if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL") echo "smart-filter-horizontal" ?>">
    <div class="smart-filter-section">

        <form name="<? echo $arResult["FILTER_NAME"] . "_form" ?>" action="<? echo $arResult["FORM_ACTION"] ?>"
              method="get" class="smart-filter-form">

            <? foreach ($arResult["HIDDEN"] as $arItem): ?>
                <input type="hidden" name="<? echo $arItem["CONTROL_NAME"] ?>" id="<? echo $arItem["CONTROL_ID"] ?>"
                       value="<? echo $arItem["HTML_VALUE"] ?>"/>
            <? endforeach; ?>

            <div class="row filter_class">
                <?php
                foreach ($arResult["ITEMS"] as $key => $arItem)//prices
                {

                    if (((int)$arItem['ID'] !== RETAIL_PRICE) && ((int)$arItem['ID'] !== KOLVO_ZATYAZHEK_ID)) {
                        continue;
                    }
                    if ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0)
                        continue;

                    $key = (int)$arItem['ID'] === KOLVO_ZATYAZHEK_ID ? md5($arItem['ID']) : $arItem["ENCODED_ID"];

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

                        endif;?>

                        <div class="<? if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?>col-sm-6 col-md-4<? else: ?><? endif ?>F  smart-filter-parameters-box bx-active">
                            <span class="smart-filter-container-modef"></span>

                            <div class="smart-filter-parameters-box-title"
                                 onclick="smartFilter.hideFilterProps(this)">
                                <span class="smart-filter-parameters-box-title-text">
                                    <?= (int)$arItem['ID'] === RETAIL_PRICE ? 'Цена' : 'Количество затяжек'?>
                                </span>
                                <span data-role="prop_angle" class="smart-filter-angle smart-filter-angle-up">
									<i class="fa fa-angle-right smart-filter-angles" aria-hidden="true"></i>
								</span>
                            </div>
                            <div class="smart-filter-block filter_id" data-role="bx_filter_block"
                                 id="<?= $arItem['CODE'] ?>">
                                <div class="smart-filter-parameters-box-container">
                                    <div class="smart-filter-input-group-number">
                                        <div class="d-flex justify-content-between  align-items-center">
                                            <div class="form-group" style="width: calc(50% - 10px);">
                                                <div class="smart-filter-input-container">
                                                    <input
                                                            class="min-price form-control form-control-sm"
                                                            type="number"
                                                            name="<? echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                            id="<? echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                            value="<? echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
                                                            size="5"
                                                            placeholder="<?= $arItem["VALUES"]["MIN"]["VALUE"] ?>"
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
                                                            name="<? echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                            id="<? echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                            value="<? echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
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
                            console.log('emmemememmem');
                            BX.ready(function () {
                                window['trackBar<?=$key?>'] = new BX.Iblock.SmartFilter(<?=CUtil::PhpToJSObject($arJsParams)?>);
                            });
                        </script>
                    <?
                }




                //not price

                foreach ($arResult["ITEMS"] as $key => $arItem) {
                    if (empty($arItem["VALUES"]) || isset($arItem["PRICE"]) || ((int)$arItem['ID'] === KOLVO_ZATYAZHEK_ID))
                        continue;

                    if ($arItem["DISPLAY_TYPE"] == "A" && ($arItem["VALUES"]["MAX"]["VALUE"] - $arItem["VALUES"]["MIN"]["VALUE"] <= 0))
                        continue;
                    ?>

                    <div class="<? if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?>col-sm-6 col-md-4<? else: ?><? endif ?> smart-filter-parameters-box <? if ($arItem["DISPLAY_EXPANDED"] == "Y"): ?>bx-active<? endif ?>">
                        <span class="smart-filter-container-modef"></span>

                        <div class="smart-filter-parameters-box-title" onclick="smartFilter.hideFilterProps(this)">

                            <span class="smart-filter-parameters-box-title-text"><?= $arItem["NAME"] ?></span>

                            <span data-role="prop_angle" class="smart-filter-angle smart-filter-angle-up">
									<i class="fa fa-angle-right smart-filter-angles" aria-hidden="true"></i>
								</span>

                            <? if ($arItem["FILTER_HINT"] <> ""): ?>
                                <span class="smart-filter-hint">
									<span class="smart-filter-hint-icon">?</span>
									<span class="smart-filter-hint-popup">
										<span class="smart-filter-hint-popup-angle"></span>
										<span class="smart-filter-hint-popup-content">

										</span>	<?= $arItem["FILTER_HINT"] ?></span>
								</span>
                            <? endif ?>
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
                                                           name="<? echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                           id="<? echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                           value="<? echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
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
                                                            name="<? echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                            id="<? echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                            value="<? echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
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
                                                            name="<? echo $arItem["VALUES"]["MIN"]["CONTROL_NAME"] ?>"
                                                            id="<? echo $arItem["VALUES"]["MIN"]["CONTROL_ID"] ?>"
                                                            value="<? echo $arItem["VALUES"]["MIN"]["HTML_VALUE"] ?>"
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
                                                            name="<? echo $arItem["VALUES"]["MAX"]["CONTROL_NAME"] ?>"
                                                            id="<? echo $arItem["VALUES"]["MAX"]["CONTROL_ID"] ?>"
                                                            value="<? echo $arItem["VALUES"]["MAX"]["HTML_VALUE"] ?>"
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
                                        <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <input
                                                    style="display: none"
                                                    type="checkbox"
                                                    name="<?= $ar["CONTROL_NAME"] ?>"
                                                    id="<?= $ar["CONTROL_ID"] ?>"
                                                    value="<?= $ar["HTML_VALUE"] ?>"
                                                <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
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
													<? if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                        <span class="smart-filter-checkbox-btn-image"
                                                              style="background-image: url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                    <? endif ?>
												</span>
                                            </label>
                                        <? endforeach ?>
                                        <div style="clear: both;"></div>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region CHECKBOXES_WITH_PICTURES_AND_LABELS +
                                case "H":
                                ?>
                                    <div class="smart-filter-input-group-checkbox-pictures-text">
                                        <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <input
                                                    style="display: none"
                                                    type="checkbox"
                                                    name="<?= $ar["CONTROL_NAME"] ?>"
                                                    id="<?= $ar["CONTROL_ID"] ?>"
                                                    value="<?= $ar["HTML_VALUE"] ?>"
                                                <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
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
												<? if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                    <span class="smart-filter-checkbox-btn-image"
                                                          style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                <? endif ?>
											</span>
                                                <span class="smart-filter-checkbox-text" title="<?= $ar["VALUE"]; ?>">
												<?= $ar["VALUE"]; ?>
											</span>
                                            </label>
                                        <? endforeach ?>
                                    </div>
                                <?
                                break;
                                //endregion

                                //region DROPDOWN +
                                case "P":
                                ?>
                                <? $checkedItemExist = false; ?>
                                    <div class="smart-filter-input-group-dropdown">
                                        <div class="smart-filter-dropdown-block"
                                             onclick="smartFilter.showDropDownPopup(this, '<?= CUtil::JSEscape($key) ?>')">
                                            <div class="smart-filter-dropdown-text" data-role="currentOption">
                                                <? foreach ($arItem["VALUES"] as $val => $ar) {
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
                                                    id="<? echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                    value=""
                                            />
                                            <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                                <input
                                                        style="display: none"
                                                        type="radio"
                                                        name="<?= $ar["CONTROL_NAME_ALT"] ?>"
                                                        id="<?= $ar["CONTROL_ID"] ?>"
                                                        value="<? echo $ar["HTML_VALUE_ALT"] ?>"
                                                    <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                />
                                            <? endforeach ?>

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
                                                    <? foreach ($arItem["VALUES"] as $val => $ar):
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
                                                    <? endforeach ?>
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
                                                        <? if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                            <span class="smart-filter-checkbox-btn-image"
                                                                  style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                        <? endif ?>
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
                                                    id="<? echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                    value=""
                                            />
                                            <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                                <input
                                                        style="display: none"
                                                        type="radio"
                                                        name="<?= $ar["CONTROL_NAME_ALT"] ?>"
                                                        id="<?= $ar["CONTROL_ID"] ?>"
                                                        value="<?= $ar["HTML_VALUE_ALT"] ?>"
                                                    <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                />
                                            <? endforeach ?>

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
                                                                <? if (isset($ar["FILE"]) && !empty($ar["FILE"]["SRC"])): ?>
                                                                    <span class="smart-filter-checkbox-btn-image"
                                                                          style="background-image:url('<?= $ar["FILE"]["SRC"] ?>');"></span>
                                                                <? endif ?>
                                                                <span class="smart-filter-dropdown-text"><?= $ar["VALUE"] ?></span>
                                                            </label>
                                                        </li>
                                                    <? endforeach ?>
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
                                                   for="<? echo "all_" . $arCur["CONTROL_ID"] ?>">
												<span class="smart-filter-input-checkbox">
													<input
                                                            type="radio"
                                                            value=""
                                                            name="<? echo $arCur["CONTROL_NAME_ALT"] ?>"
                                                            id="<? echo "all_" . $arCur["CONTROL_ID"] ?>"
                                                            onclick="smartFilter.click(this)"
                                                    />
													<span class="smart-filter-param-text"><? echo GetMessage("CT_BCSF_FILTER_ALL"); ?></span>
												</span>
                                            </label>
                                        </div>
                                        <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <div class="radio">
                                                <label data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                       class="smart-filter-param-label"
                                                       for="<? echo $ar["CONTROL_ID"] ?>">
													<span class="smart-filter-input-checkbox <? echo $ar["DISABLED"] ? 'disabled' : '' ?>">
														<input
                                                                type="radio"
                                                                value="<? echo $ar["HTML_VALUE_ALT"] ?>"
                                                                name="<? echo $ar["CONTROL_NAME_ALT"] ?>"
                                                                id="<? echo $ar["CONTROL_ID"] ?>"
															<? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
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
                                        <? endforeach; ?>
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
                                                <? $APPLICATION->IncludeComponent(
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
                                                <? $APPLICATION->IncludeComponent(
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
                                if ($arItem['PROPERTY_TYPE'] !== 'S') {
                                    uasort($arItem["VALUES"], 'sort_by_sort');
                                } else {
                                    ksort($arItem["VALUES"]);
                                }
                                ?>
                                    <div class="smart-filter-input-group-checkbox-list">
                                        <? foreach ($arItem["VALUES"] as $val => $ar): ?>
                                            <div class="form-group form-check">
                                                <label data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                                       class="smart-filter-checkbox-text form-check-label"
                                                       id="<?= $ar["VALUE"]; ?>" for="<? echo $ar["CONTROL_ID"] ?>">
                                                    <?= $ar["VALUE"]; ?>
                                                </label>
                                                <input
                                                        type="checkbox"
                                                        value="<? echo $ar["HTML_VALUE"] ?>"
                                                        name="<? echo $ar["CONTROL_NAME"] ?>"
                                                        id="<? echo $ar["CONTROL_ID"] ?>"
                                                        class="check_input form-check-input"
                                                    <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                                    <? echo $ar["DISABLED"] ? 'disabled' : '' ?>
                                                        onclick="smartFilter.click(this)"
                                                />
                                            </div>
                                        <? endforeach; ?>
                                    </div>

                                <?
                                    //endregion
                                }
                                ?>
                                <div class="form-group form-check">
                                    <label data-role="label_<?= $ar["CONTROL_ID"] ?>"
                                           class="smart-filter-checkbox-text form-check-label"
                                           id="<?= $ar["VALUE"]; ?>" for="<? echo $ar["CONTROL_ID"] ?>">
                                        <?= $ar["VALUE"]; ?>
                                    </label>
                                    <input
                                            type="checkbox"
                                            value="<? echo $ar["HTML_VALUE"] ?>"
                                            name="<? echo $ar["CONTROL_NAME"] ?>"
                                            id="<? echo $ar["CONTROL_ID"] ?>"
                                            class="check_input form-check-input"
                                        <? echo $ar["CHECKED"] ? 'checked="checked"' : '' ?>
                                        <? echo $ar["DISABLED"] ? 'disabled' : '' ?>
                                            onclick="smartFilter.click(this)"
                                    />
                                </div>
                            </div>
                        </div>

                    </div>
                    <?
                }
                ?>
<!--                sus-->
                <div class="<? if ($arParams["FILTER_VIEW_MODE"] == "HORIZONTAL"): ?>col-sm-6 col-md-4<? else: ?><? endif ?> smart-filter-parameters-box bx-active">
                    <span class="smart-filter-container-modef"></span>
                    <div class="smart-filter-block filter_id" data-role="bx_filter_block"
                         id="hide_not_available_container_id">
                        <div class="smart-filter-parameters-box-container">
                            <div class="form-group form-check hide_not_available">
                                <label data-role="label_in_stoсk"
                                       class="smart-filter-checkbox-text form-check-label hide_not_available"
                                       id="hide_not_available_label" for="<? echo $ar["CONTROL_ID"] ?>">
                                    Показать только товар в наличии
                                </label>
                                <input
                                        type="checkbox"
                                        value="<? echo $arParams["HIDE_NOT_AVAILABLE"] ?>"
                                        name="hide_not_available"
                                        id="hide_not_available_id"
                                        class="check_input form-check-input"
                                    <? echo $arParams["HIDE_NOT_AVAILABLE"] == 'Y' ? 'checked="checked"' : '' ?>
                                        onclick="smartFilter.click(this)"
                                />
                            </div>
                        </div>
                    </div>
                </div>











                <div class="button_box">
                    <div class="smart-filter-button-box">
                        <div class="smart-filter-block">
                            <div class="smart-filter-parameters-box-container">
                                <div class="smart-filter-popup-result <? if ($arParams["FILTER_VIEW_MODE"] == "VERTICAL") echo $arParams["POPUP_POSITION"] ?>"
                                     id="modef" <? if (!isset($arResult["ELEMENT_COUNT"])) echo 'style="display:none"'; ?>
                                     style="display: inline-block;">
                                    <? echo GetMessage("CT_BCSF_FILTER_COUNT", array("#ELEMENT_COUNT#" => '<span id="modef_num" style="display: none">' . intval($arResult["ELEMENT_COUNT"]) . '</span>')); ?>
                                    <span class="arrow"></span>
                                    <a href="javascript:;"
                                       target=""><? echo GetMessage("CT_BCSF_FILTER_SHOW") ?></a>
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
    console.log('ssssssssss');
    var smartFilter = new JCSmartFilter('<?echo CUtil::JSEscape($arResult["FORM_ACTION"])?>', '<?=CUtil::JSEscape($arParams["FILTER_VIEW_MODE"])?>', <?=CUtil::PhpToJSObject($arResult["JS_FILTER_PARAMS"])?>);
</script>
