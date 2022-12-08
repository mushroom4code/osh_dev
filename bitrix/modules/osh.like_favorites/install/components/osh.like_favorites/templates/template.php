<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/**
 * @var $arParams
 * @var CAllMain|CMain $APPLICATION
 * @var CBitrixComponentTemplate $this
 * @var string $templateFolder
 * @global $NAME_MODULE = 'osh.like_favorites'
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
$this->addExternalCss($templateFolder . "/css/style.css");
?>

<div class="box_with_like" data-product-id="<?= $arParams['ID_PROD'] ?>" data-user-id="<?= $arParams['F_USER_ID'] ?>">
    <?php if ($arParams['LOOK_LIKE'] === true) { ?>
        <a class="icon_like method" title="Нравится"
            <?php if ($arParams['COUNT_LIKE'] === 1 || $arParams['COUNT_LIKE'] === '1') { ?> style="color:red"
                data-like-controls="true"<?php } ?> href="javascript:void(0);" data-method="like">
            <i class="fa fa-heart-o" aria-hidden="true"></i>
            <article class="like_span" id="likes">
                <?php if (!empty($arParams['COUNT_LIKES'])) {
                    echo $arParams['COUNT_LIKES'];
                } else {
                    echo '0';
                } ?></article>
        </a>
    <?php } ?>
    <?php if ($arParams['LOOK_FAVORITE'] === true) { ?>
        <a class="product-item__favorite-star method"
            <?php if ($arParams['COUNT_FAV'] === 1 || $arParams['COUNT_FAV'] === '1'){ ?> style="color:red"
           data-fav-controls="true" <?php } ?>href="javascript:void(0);"
           title="Добавить в избранное" data-method="favorite">
            <i class="fa fa-star-o" aria-hidden="true"></i>
        </a>
    <?php } ?>
</div>
