<?php

use Shuchkin\SimpleXLSXGen;

/**
 * Обновление прайс листа на сайте
 *
 * Class PriceList
 * @package Enterego
 */
class PriceList
{

    private $arResult = [];
    private $categories_id;
    private $price_list_dir;
    private $expire_time;

    private $category_type = '';
    private $brand = '';


    public function __construct()
    {
        \CModule::IncludeModule("iblock") || die();

        $this->categories_id = json_decode(COption::getOptionString('priceList_xlsx', 'priceListArrayCustom'));

        $this->price_list_dir = $_SERVER['DOCUMENT_ROOT'] . '/price-list/';   // путь к файлам прайслистов
        $this->expire_time = 3600;                                         // (864000 сек = 10 дней), время жизни прайслистов в секундах.

        if (!is_dir($this->price_list_dir)) {
            mkdir($this->price_list_dir, 0777, true) || die('Не удалось создать директории...');
        }

        $this->update();

        $this->clearOldPriceList();
    }

    private function update()
    {
        $arStore = $storages = [];
        $StoragesQuery =
            CCatalogStore::GetList(
                array('PRODUCT_ID' => 'ASC', 'ID' => 'ASC'),
                array('ACTIVE' => 'Y', '@SITE_ID' => SUBSIDIARY_SITE_LIST),
                false,
                false,
                array("ID", "TITLE")
            );

        while ($ar = $StoragesQuery->GetNext()) {
            $storages[$ar['ID']] = $ar;
            $storages['TITLE'][] = 'Склад ' . $ar['TITLE'];
            $arStore[] = "CATALOG_STORE_AMOUNT_" . $ar['ID'];
        }

        $this->arResult[] = ['<b style="font-size: 25px;font-weight:bold; ">ПРАЙС-ЛИСТ OSHISHA.NET - 8 (499) 350-62-01</b>'];
        $this->arResult[] = ['<b>Дата формирования</b>', '<b>' . date('d.m.y') . '</b>'];
        $this->arResult[] = [];
        $this->arResult[] = array_merge([
            'Группа товара',
            'Бренд',
            'Наименование',
            'Заказ штук',
            'До 10 тыс. руб',
            'от 10 до 30 тыс.руб',
            'от 30 тыс. руб',
            'ссылка на продукт'], $storages['TITLE']);


        $tree = \CIBlockSection::GetTreeList(array('DEPTH_LEVEL' => 1, 'ACTIVE' => 'Y'), array('ID', 'NAME'));
        while ($category = $tree->GetNext()) {
            if (in_array($category['ID'], $this->categories_id)) {
                $category['child'] = $this->childrenCategory($category['ID'], $arStore);

                $this->category_type = $category['NAME'];
                if (!empty($category['child'])) {
                    foreach ($category['child'] as $key => $child_category) {
                        $this->createTree($child_category, $arStore);
                    }
                }
            }
        }
        $xlsx = SimpleXLSXGen::fromArray($this->arResult)->setDefaultFontSize(12);
        $xlsx->autoFilter('A4:J10000');
        $file_name = 'price-list-oshisha-' . date("d.m.Y") . '-' . date("H:i:s") . '.xlsx';
        $path_to_file = $this->price_list_dir . $file_name;
        $xlsx->saveAs($path_to_file);
        $option = json_decode(COption::GetOptionString("BBRAIN", 'SETTINGS_SITE', false, ''));
        $option->price_list_link = '/price-list/' . $file_name;
        COption::SetOptionString('BBRAIN', 'SETTINGS_SITE', json_encode($option), false, '');
    }


    private function clearOldPriceList()
    {
        if (is_dir($this->price_list_dir)) {
            if ($dh = opendir($this->price_list_dir)) {
                while (($file = readdir($dh)) !== false) {
                    $time_sec = time();
                    $time_file = filemtime($this->price_list_dir . $file);

                    $time = $time_sec - $time_file;

                    $unlink = $this->price_list_dir . $file;

                    if (is_file($unlink)) {
                        if ($time > $this->expire_time) {
                            if (!unlink($unlink)) {
                                $this->setLog('Ошибка при удалении файла ' . $unlink);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private function setLog($text)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/price-list_log.txt', date('d.m.Y')
            . $text . "\n", 8);
    }

    private function childrenCategory($section_id, $arStore = [])
    {
        $result = array();


        $tree = \CIBlockSection::GetList(
            array("NAME" => "ASC", "left_margin" => "asc"),
            array('ACTIVE' => 'Y', 'SECTION_ID' => $section_id),
            false,
            array('ID', 'NAME')
        );

        while ($section = $tree->GetNext()) {
            $prod = array();
            $section['child'] = $this->childrenCategory($section['ID'], $arStore);

            $arSelect = array_merge($arStore, array(
                "ID",
                'IBLOCK_ID',
                "NAME",
                "PRICE_" . RETAIL_PRICE,
                "PRICE_" . BASIC_PRICE,
                "PRICE_" . B2B_PRICE,
                "CODE",
                "PROPERTY_MARKIROVANNYY",
            ));

            $arFilter = [
                "IBLOCK_SECTION_ID" => IntVal($section['ID']),
                "ACTIVE" => "Y",
                "CATALOG_AVAILABLE" => "Y",
                ">=CATALOG_QUANTITY" => 1];

            $res = CIBlockElement::GetList(array("NAME" => "ASC"), $arFilter, false, array(), $arSelect);
            while ($arRes = $res->Fetch()) {
                $base_price = \CPrice::GetBasePrice($arRes['ID']);
                $base_price = !empty($base_price) ? $base_price['PRICE'] : '';

                $StoreAr = [];
                foreach ($arStore as $store) {
                    if (!empty($arRes[$store])) {
                        $StoreAr[$store] = $arRes[$store];
                    } else {
                        $StoreAr[$store] = 0;
                    }
                }

                $prod[] = array_merge(array(
                    'NAME' => $arRes['NAME'],
                    'PATH' => 'https://' . OSHISHA_DOMAIN . '/catalog/product/' . $arRes['CODE'] . '/'
                        ?? 'https://' . OSHISHA_DOMAIN,
                    'BASE' => $base_price,
                    'DO_10' => $arRes['PRICE_' . RETAIL_PRICE],
                    'OT_10_DO_30' => $arRes['PRICE_' . BASIC_PRICE],
                    'OT_30' => $arRes['PRICE_' . B2B_PRICE],
                    'USE_MARKING' => $arRes['PROPERTY_MARKIROVANNYY_VALUE'],

                ), $StoreAr);
                unset($section['LEFT_MARGIN'], $section['~LEFT_MARGIN'], $section['~NAME'], $section['~ID']);
            }
            $section['products'] = $prod;
            $result[] = $section;
        }

        return $result;
    }

    /**
     * @param $child
     * @param $arStore
     */
    private function createTree($child, $arStore)
    {
        if (!empty($child['products'])) {
            $this->brand = $child['NAME'];

            foreach ($child['products'] as $product_item) {
                if (empty($product_item['BASE']) || empty($product_item['DO_10']) ||
                    empty($product_item['OT_10_DO_30']) || empty($product_item['OT_30'])) continue;

                $product_item['BASE'] = (int)$product_item['BASE'];
                $product_item['DO_10'] = (int)$product_item['DO_10'];
                $product_item['OT_10_DO_30'] = (int)$product_item['OT_10_DO_30'];
                $product_item['OT_30'] = (int)$product_item['OT_30'];
                $product_name = $product_item['NAME'];
                if ($product_item['USE_MARKING'] === 'Да') {
                    $product_name = $product_item['NAME'] . ' МРК';
                }
                $storage = [];
                foreach ($product_item as $key => $value) {
                    if (in_array($key, $arStore)) {
                        $storage[] = $value;
                    }
                }

                $this->arResult[] = array_merge([
                    $this->category_type,
                    $this->brand,
                    $product_name,
                    ' ',
                    $product_item['DO_10'],
                    $product_item['OT_10_DO_30'],
                    $product_item['OT_30'],
                    '<a href="' . $product_item['PATH'] . '">Ссылка на товар</a>',
                ], $storage);
            }
        }

        if (!empty($child['child'])) {
            foreach ($child['child'] as $ch) {
                $this->createTree($ch, $arStore);
            }
        }
    }
}
