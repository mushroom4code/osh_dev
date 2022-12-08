<?php

namespace Enterego\Userprice;

use Bitrix\Main\Application;

/**
 * ������ ��� ������������� ������� �������������� ���� ������������
 * @package Enterego\Userprice
 */
class Special
{
    /** @var float $price ���� ���� */
    public $price = 0.0;

    /** @var int $priceId CATALOG_PRICE_# - ID ���� ���� */
    public $priceId = 0;

    /** @var string $priceName �������� ���� ���� */
    public $priceName = '';

    /** @var int $PRODUCT_ID ID �������� */
    public $PRODUCT_ID = 0;

    /** @var int $IBLOCK_SECTION_ID ID ��������� */
    public $IBLOCK_SECTION_ID = 0;
}