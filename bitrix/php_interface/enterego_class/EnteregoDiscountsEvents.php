<?php

function createInfoblockOnDiscountAdd($event)
{
    $discountParameters = $event->getParameters();
    $arAccess = array(
        "2" => "R",
    );
    $ib = new CIBlock();
    $result = $ib->Add(
        array(
            "ACTIVE" => "Y",
            "NAME" => "Каталог для акции - " . $discountParameters['fields']['NAME'],
            "CODE" => "catalog_for_d" . $discountParameters['id'],
            "IBLOCK_TYPE_ID" => "discounts",
            "SITE_ID" => array('N2', 's1'),
            "SORT" => "5",
            "GROUP_ID" => $arAccess,
            "DESCRIPTION" => '',

            "LIST_PAGE_URL" => "#SITE_DIR#/discounts/catalog_for_d" . $discountParameters['id'],
            "SECTION_PAGE_URL" => "#SITE_DIR#/discounts/" . $discountParameters['id'] . "/#SECTION_CODE#/",
            "DETAIL_PAGE_URL" => "#SITE_DIR#/discounts/" . $discountParameters['id'] . "/#SECTION_CODE#/#ELEMENT_CODE#/",

            "INDEX_SECTION" => "Y",
            "INDEX_ELEMENT" => "Y",

            "VERSION" => 1,

            "ELEMENT_NAME" => "Товар",
            "ELEMENTS_NAME" => "Товары",
            "ELEMENT_ADD" => "Добавить товар",
            "ELEMENT_EDIT" => "Изменить товар",
            "ELEMENT_DELETE" => "Удалить товар",
            "SECTION_NAME" => "Категория",
            "SECTIONS_NAME" => "Категории",
            "SECTION_ADD" => "Добавить категорию",
            "SECTION_EDIT" => "Изменить категорию",
            "SECTION_DELETE" => "Удалить категорию",

            "SECTION_PROPERTY" => "Y",
        )
    );
}

\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', '\Bitrix\Sale\Internals\Discount::OnAfterAdd', 'createInfoblockOnDiscountAdd');