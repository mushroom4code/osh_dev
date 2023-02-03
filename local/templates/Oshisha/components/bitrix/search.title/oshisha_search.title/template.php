<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
$INPUT_ID = trim($arParams["~INPUT_ID"]);
if ($INPUT_ID == '')
    $INPUT_ID = "title-search-input";
$INPUT_ID = CUtil::JSEscape($INPUT_ID);

$CONTAINER_ID = trim($arParams["~CONTAINER_ID"]);
if ($CONTAINER_ID == '')
    $CONTAINER_ID = "title-search";
$CONTAINER_ID = CUtil::JSEscape($CONTAINER_ID);

if ($arParams["SHOW_INPUT"] !== "N"):?>
    <div id="<? echo $CONTAINER_ID ?>" class="bx-searchtitle">
        <form action="<? echo $arResult["FORM_ACTION"] ?>" class="d-flex flex-row">
            <a href="javascript:void(0)" name="s" type="submit" class="btn_search"></a>
            <input id="<? echo $INPUT_ID ?>" type="text" name="q" value="" size="50" maxlength="50" class="search_input"
                   placeholder="Искать на Oshisha" autocomplete="off"/>
        </form>
    </div>
<? endif ?>
<?php
global $USER;
if ($USER->IsAuthorized()) {
?>
<script>
    BX.ready(function () {
        new JCTitleSearch({
            'AJAX_PAGE': '<?echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
            'CONTAINER_ID': '<?echo $CONTAINER_ID?>',
            'INPUT_ID': '<?echo $INPUT_ID?>',
            'MIN_QUERY_LEN': 1
        });
    });
</script>
<?}?>
