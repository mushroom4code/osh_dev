<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $arParams
 * @var CAllMain|CMain $APPLICATION
 * @var CBitrixComponentTemplate $this
 * @var string $templateFolder
 * @global $NAME_MODULE = 'osh.like_favorites'
 * @var CUser $USER
 *
 * $arParams['COUNT_LIKES'], $arParams['COUNT_LIKE'], $arParams['COUNT_FAV']
 * - Параметры, передаваемые в компонент с элементом catalog.item
 * ( в компоненте catalog.section ведется получение списка всех параметров из бд
 * - выборка циклом нужного элемента из массива
 * - передача его параметров в компонент элемента )
 *
 */
global $NAME_MODULE;
$NAME_MODULE = 'osh.like_favorites';

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/$NAME_MODULE/include.php");
$this->addExternalJs($templateFolder . "/js/script.js");
?>

<div class="box_with_like" data-product-id="<?= $arParams['ID_PROD'] ?>" data-fuser-id="<?= $arParams['F_USER_ID'] ?>"
     data-user-id="<?= $USER->GetId(); ?>">
    <?php if ($arParams['LOOK_LIKE'] === true) { ?>
        <a class="icon_like method flex flex-col items-center" title="Нравится" data-method="like"
            <?php if ($arParams['COUNT_LIKE'] == 1 || $arParams['COUNT_LIKE'] == '1') { ?> data-like-controls="true"<?php } ?>
           href="javascript:void(0);">
            <svg width="25" height="24" viewBox="0 0 20 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.22011 17.249L9.22307 17.2508C9.46452 17.4032 9.74419 17.4851 10.0312 17.4851C10.3183 17.4851 10.598 17.4032 10.8394 17.2508L10.8394 17.2508L10.8424 17.249C14.3602 15.0066 16.3461 12.7447 17.4489 10.7609C18.5536 8.77384 18.75 7.10597 18.75 6.08998C18.75 3.43533 16.6483 1.25 14.0156 1.25C12.6451 1.25 11.4764 2.01156 10.7095 2.67058C10.4473 2.89598 10.2191 3.12053 10.0312 3.31989C9.84341 3.12053 9.61524 2.89598 9.35296 2.67058C8.58612 2.01156 7.41738 1.25 6.04688 1.25C3.41417 1.25 1.3125 3.43533 1.3125 6.08998C1.3125 7.10597 1.50891 8.77384 2.61355 10.7609C3.71642 12.7447 5.70226 15.0066 9.22011 17.249Z"
                      stroke-width="1"
                      class="<?php if ($arParams['COUNT_LIKE'] == 1 || $arParams['COUNT_LIKE'] == '1') { ?>
                      fill-light-red stroke-light-red dark:fill-white dark:stroke-white
                      <?php } else { ?> stroke-black dark:stroke-white <?php } ?>"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
                <article class="like_span mt-1 <?= empty($arParams['HIDE_LIKE_COUNT']) ? "" : "hidden" ?>" id="likes">
                    <?= !empty($arParams['COUNT_LIKES']) ? $arParams['COUNT_LIKES'] : '0'; ?>
                </article>
        </a>
    <?php }
    if ($arParams['LOOK_FAVORITE'] === true) {
        if ($USER->IsAuthorized()) { ?>
            <a class="product-item__favorite-star method"
                <?php if ($arParams['COUNT_FAV'] === 1 || $arParams['COUNT_FAV'] === '1'){ ?> style="color:red"
               data-fav-controls="true" <?php } ?>href="javascript:void(0);"
               title="Добавить в избранное" data-method="favorite">
                <svg width="28" height="27" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.1765 19.9412L5.05882 24L7.08823 15.8824L1 9.11765L9.79412 8.44118L13.1765 1M13.1765 1L16.5588 8.44118L25.3529 9.11765L19.2647 15.8824L21.2941 24L13.1765 19.9412"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="<?php if ($arParams['COUNT_FAV'] == 1 || $arParams['COUNT_FAV'] == '1') { ?>
                      fill-light-red stroke-light-red dark:fill-white dark:stroke-white
                      <?php } else { ?> stroke-black dark:stroke-white <?php } ?>"></path>
                </svg>
            </a>
        <?php } else { ?>
            <a class="product-item__favorite-star initial_auth_popup" href="javascript:void(0);" title="Авторизуйтесь, чтобы
        добавить в избранное">
                <svg width="28" height="27" viewBox="0 0 25 26" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13.1765 19.9412L5.05882 24L7.08823 15.8824L1 9.11765L9.79412 8.44118L13.1765 1M13.1765 1L16.5588 8.44118L25.3529 9.11765L19.2647 15.8824L21.2941 24L13.1765 19.9412"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          class="stroke-black dark:stroke-white"></path>
                </svg>
            </a>
        <?php }
    } ?>
</div>
