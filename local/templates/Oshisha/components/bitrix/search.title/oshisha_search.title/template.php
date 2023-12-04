<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
    <div id="<?= $CONTAINER_ID ?>" class="bx-searchtitle">
        <form action="<?= $arResult["FORM_ACTION"] ?>" class="d-flex flex-row max-w-2xl relative">
            <a href="javascript:void(0)" name="s" type="submit" class="btn_search absolute left-3 h-full flex items-center">
                <svg width="20" height="20" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.2583 14.075L12.425 11.25C13.3392 10.0854 13.8352 8.64722 13.8333 7.16667C13.8333 5.84813 13.4423 4.5592 12.7098 3.46287C11.9773 2.36654 10.9361 1.51206 9.71789 1.00747C8.49972 0.502889 7.15927 0.370866 5.86607 0.628101C4.57286 0.885336 3.38497 1.52027 2.45262 2.45262C1.52027 3.38497 0.885336 4.57286 0.628101 5.86607C0.370866 7.15927 0.502889 8.49972 1.00747 9.71789C1.51206 10.9361 2.36654 11.9773 3.46287 12.7098C4.5592 13.4423 5.84813 13.8333 7.16667 13.8333C8.64722 13.8352 10.0854 13.3392 11.25 12.425L14.075 15.2583C14.1525 15.3364 14.2446 15.3984 14.3462 15.4407C14.4477 15.4831 14.5567 15.5048 14.6667 15.5048C14.7767 15.5048 14.8856 15.4831 14.9872 15.4407C15.0887 15.3984 15.1809 15.3364 15.2583 15.2583C15.3364 15.1809 15.3984 15.0887 15.4407 14.9872C15.4831 14.8856 15.5048 14.7767 15.5048 14.6667C15.5048 14.5567 15.4831 14.4477 15.4407 14.3462C15.3984 14.2446 15.3364 14.1525 15.2583 14.075ZM2.16667 7.16667C2.16667 6.17776 2.45991 5.21106 3.00932 4.38882C3.55873 3.56657 4.33962 2.92571 5.25325 2.54727C6.16688 2.16883 7.17222 2.06982 8.14212 2.26274C9.11203 2.45567 10.0029 2.93187 10.7022 3.63114C11.4015 4.3304 11.8777 5.22131 12.0706 6.19122C12.2635 7.16112 12.1645 8.16646 11.7861 9.08009C11.4076 9.99372 10.7668 10.7746 9.94452 11.324C9.12228 11.8734 8.15558 12.1667 7.16667 12.1667C5.84059 12.1667 4.56882 11.6399 3.63114 10.7022C2.69345 9.76452 2.16667 8.49275 2.16667 7.16667Z"
                          fill="black" class="fill-lightGrayBg dark:fill-textDarkLightGray"/>
                </svg>
            </a>
            <input id="<?= $INPUT_ID ?>" type="text" name="q" value="" size="50" maxlength="50"
                   class="search_input dark:bg-gray-box-dark max-w-2xl w-full bg-textDark border-none outline-none
                   dark:text-grayIconLights py-3 px-10 rounded-7 text-textLight text-sm"
                   placeholder="Искать на Oshisha" autocomplete="off"/>
        </form>
    </div>
<?php endif ?>
<?php
global $USER;
if ($USER->IsAuthorized()) { ?>
    <script>
        BX.ready(function () {
            new JCTitleSearch({
                'AJAX_PAGE': '<?= CUtil::JSEscape(POST_FORM_ACTION_URI)?>',
                'CONTAINER_ID': '<?= $CONTAINER_ID?>',
                'INPUT_ID': '<?= $INPUT_ID?>',
                'MIN_QUERY_LEN': 1
            });
        });
    </script>
<?php } ?>
