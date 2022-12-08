<?php

namespace Enterego\Userprice;

use Bitrix\Main\Application;

/**
 * Хэлпер для представления свойств индивидуальной цены пользователя
 * @package Enterego\Userprice
 */
class Special
{
    /** @var float $price Сама цена */
    public $price = 0.0;

    /** @var int $priceId CATALOG_PRICE_# - ID типа цены */
    public $priceId = 0;

    /** @var string $priceName название типа цены */
    public $priceName = '';

    /** @var int $PRODUCT_ID ID продукта */
    public $PRODUCT_ID = 0;

    /** @var int $IBLOCK_SECTION_ID ID категории */
    public $IBLOCK_SECTION_ID = 0;
}