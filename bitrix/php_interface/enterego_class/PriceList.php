<?php

/**
 * Обновление прайс листа на сайте
 *
 * Class PriceList
 * @package Enterego
 */
class PriceList {

    private $row_next;
    private $categories_id;
    private $price_list_dir;
    private $expire_time;

    private $category_type = '';
    private $brand = '';

    public function __construct()
    {
        \CModule::IncludeModule("iblock") || die();
        \CModule::IncludeModule("nkhost.phpexcel") || die();

        $this->categories_id = json_decode(COption::getOptionString('priceList_xlsx', 'priceListArrayCustom'));

        $this->price_list_dir = $_SERVER['DOCUMENT_ROOT'].'/price-list/';   // путь к файлам прайслистов
        $this->row_next = 7;                                                // начальная строка
        $this->expire_time = 3600;                                         // (864000 сек = 10 дней), время жизни прайслистов в секундах.

        if(!is_dir($this->price_list_dir)){
            mkdir($this->price_list_dir, 0777, true) || die('Не удалось создать директории...');
        }

        $this->update();

        $this->clearOldPriceList();
    }

    private function update(){
        global $PHPEXCELPATH;

        require_once($PHPEXCELPATH . '/PHPExcel.php');

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->setActiveSheetIndex(0);
        $active_sheet = $objPHPExcel->getActiveSheet();

        $active_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
        $active_sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $active_sheet->getPageMargins()->setTop(1);
        $active_sheet->getPageMargins()->setRight(0.75);
        $active_sheet->getPageMargins()->setLeft(0.75);
        $active_sheet->getPageMargins()->setBottom(1);

        $active_sheet->setTitle("Прайс-лист");

        $active_sheet->getHeaderFooter()->setOddHeader("&CШапка нашего прайс-листа");
        $active_sheet->getHeaderFooter()->setOddFooter('&L&B'.$active_sheet->getTitle().'&RСтраница &P из &N');

        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(10);

        $active_sheet->getColumnDimension('B')->setWidth(30);
        $active_sheet->getColumnDimension('C')->setWidth(30);
        $active_sheet->getColumnDimension('D')->setWidth(42);
        $active_sheet->getColumnDimension('E')->setWidth(30);
        $active_sheet->getColumnDimension('F')->setWidth(30);
        $active_sheet->getColumnDimension('G')->setWidth(35);
        $active_sheet->getColumnDimension('H')->setWidth(30);
        $active_sheet->getColumnDimension('I')->setWidth(30);

        $active_sheet->mergeCells('C1:K1');
        $active_sheet->getRowDimension('1')->setRowHeight(38);

        $active_sheet->setCellValue('C1','ПРАЙС-ЛИСТ OSHISHA.NET - 8 (499) 350-62-01');
        $active_sheet->getStyle("C1")->getFont()->setSize(30);
        $active_sheet->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $this->setBorderXls($active_sheet, 'C1:K1', PHPExcel_Style_Border::BORDER_THIN);
        $this->setBorderXls($active_sheet, 'C1:K1', PHPExcel_Style_Border::BORDER_THIN);

        $active_sheet->getRowDimension('3')->setRowHeight(21);

        $active_sheet->setCellValue('B3','Дата формирования');
        $active_sheet->setCellValue('C3', date('d.m.y'));
        $this->setBorderXls($active_sheet, 'B3:C3', PHPExcel_Style_Border::BORDER_THIN);
        $active_sheet->getStyle('B3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('B3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('B3')->getFont()->setBold(true);
        $active_sheet->getStyle('C3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('C3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('C3')->getFont()->setBold(true);


        $active_sheet->getRowDimension('3')->setRowHeight(31);
        $active_sheet->mergeCells('F5:H5');
        $active_sheet->setCellValue('F5','Цена отгрузки согласно объёму заказа');
        $active_sheet->getStyle('F5')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle('F5')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle('F5')->getFont()->setSize(14);
        $active_sheet->getStyle('F5')->getFont()->setBold(true);
        $this->setBorderXls($active_sheet, 'F5:L5', PHPExcel_Style_Border::BORDER_THIN);

        $active_sheet->getRowDimension('6')->setRowHeight(20);

        $this->createFilterTitle('B6','Группа товара', $active_sheet, 14);
        $this->createFilterTitle('C6','Бренд', $active_sheet, 14);
        $this->createFilterTitle('D6','Наименование', $active_sheet, 14);
        $this->createFilterTitle('E6','Заказ штук', $active_sheet, 14, 'b8f1ff');
        $this->createFilterTitle('F6','До 10 тыс. руб', $active_sheet, 12);
        $this->createFilterTitle('G6','от 10 до 30 тыс.руб', $active_sheet, 12);
        $this->createFilterTitle('H6','от 30 тыс. руб', $active_sheet, 12);
        $this->createFilterTitle('I6','ссылка на продукт', $active_sheet, 12);

        $tree = \CIBlockSection::GetTreeList($arFilter=Array('DEPTH_LEVEL' => 1, 'ACTIVE' => 'Y'), $arSelect=Array('ID', 'NAME'));
        while($category = $tree->GetNext()) {
            if(in_array($category['ID'], $this->categories_id)) {
                $category['child'] = $this->childrenCategory($category['ID']);

                $this->category_type = $category['NAME'];
                if(!empty($category['child'])){
                    foreach($category['child'] as $key => $child_category) {
                        $this->createTree($child_category,  $active_sheet);
                    }
                }
            }
        }

        $objPHPExcel->getActiveSheet()->setAutoFilter('B6:L'.($this->row_next-1));

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        if(!empty($_GET['debug'])) {
            header('Content-disposition: attachment; filename=file.xls');
            header('Content-Length: ' . filesize('file.xls'));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            $objWriter->save('php://output');
        }else {
            $file_name = 'price-list-oshisha-' . date("d.m.Y") . '-' . date("H:i:s") . '.xls';;
            $path_to_file = $this->price_list_dir . $file_name;
            $objWriter->save($path_to_file);
            $option = json_decode(COption::GetOptionString("BBRAIN",'SETTINGS_SITE'));
            $option->price_list_link =  '/price-list/' . $file_name;
            COption::SetOptionString('BBRAIN','SETTING_SITE',json_encode($option));
//            \CIBlockElement::SetPropertyValuesEx(324, false, array('link' => '/price-list/' . $file_name));
        }
    }

    private function createFilterTitle($coll, $name, $active_sheet, $font_size, $color = ''){
        $active_sheet->setCellValue($coll, $name);
        $active_sheet->getStyle($coll)->getFont()->setBold(true);
        $active_sheet->getStyle($coll)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $active_sheet->getStyle($coll)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $active_sheet->getStyle($coll)->getFont()->setSize($font_size);
        $this->setBorderXls($active_sheet, $coll, PHPExcel_Style_Border::BORDER_THIN);

        if(!empty($color)){
            $active_sheet->getStyle($coll)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);
        }
    }

    private function clearOldPriceList()
    {
        if(is_dir($this->price_list_dir)) {
            if($dh = opendir($this->price_list_dir)) {
                while(($file = readdir($dh)) !== false) {
                    $time_sec = time();
                    $time_file = filemtime($this->price_list_dir . $file);

                    $time = $time_sec - $time_file;

                    $unlink = $this->price_list_dir . $file;

                    if(is_file($unlink)) {
                        if($time > $this->expire_time) {
                            if(!unlink($unlink)) {
                                $this->setLog('Ошибка при удалении файла '. $unlink);
                            }
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    private function setLog($text){
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/price-list_log.txt', date('d.m.Y') . $text."\n", 8);
    }

    private function childrenCategory($section_id){
        $result = array();
//        $tree = \CIBlockSection::GetTreeList(Array('ACTIVE' => 'Y', 'SECTION_ID' => $section_id), array('ID', 'NAME'));
        $tree = \CIBlockSection::GetList(Array("NAME"=> "ASC", "left_margin" => "asc"), Array('ACTIVE' => 'Y', 'SECTION_ID' => $section_id), false, array('ID', 'NAME'));
        while($section = $tree->GetNext()) {
            $prod = array();
            $section['child'] = $this->childrenCategory($section['ID']);

            $arSelect = Array("ID", "NAME", "catalog_PRICE_".RETAIL_PRICE, "catalog_PRICE_".BASIC_PRICE,  "catalog_PRICE_".B2B_PRICE, "CODE");
            $arFilter = Array("IBLOCK_SECTION_ID"=>IntVal($section['ID']), "ACTIVE"=>"Y", "CATALOG_AVAILABLE" => "Y", ">=CATALOG_QUANTITY" => 1);
            $res = CIBlockElement::GetList(Array("NAME" => "ASC"), $arFilter, false, Array(), $arSelect);
            while($arRes = $res->Fetch()) {
                $base_price = \CPrice::GetBasePrice($arRes['ID']);
                $base_price = !empty($base_price) ? $base_price['PRICE'] : '';

                $prod[] = array(
                    'NAME' => $arRes['NAME'],
                    'PATH' => 'https://oshisha.net/catalog/product/'.$arRes['CODE'].'/',
                    'BASE' => $base_price,
                    'DO_10' => $arRes['CATALOG_PRICE_'.RETAIL_PRICE],
                    'OT_10_DO_30' => $arRes['CATALOG_PRICE_'.BASIC_PRICE],
                    'OT_30' => $arRes['CATALOG_PRICE_'.B2B_PRICE],
                );
                unset($section['LEFT_MARGIN'], $section['~LEFT_MARGIN'], $section['~NAME'], $section['~ID']);
            }
            $section['products'] = $prod;
            $result[] = $section;
        }

        return $result;
    }

    /**
     * @param $child
     * @param $active_sheet
     */
    private function createTree($child, $active_sheet){
        if(!empty($child['products'])) {
            $this->brand = $child['NAME'];

            foreach($child['products'] as $product_item) {
                if( empty($product_item['BASE']) ||
                    empty($product_item['DO_10']) ||
                    empty($product_item['OT_10_DO_30']) ||
                    empty($product_item['OT_30'])) continue;

                $product_item['BASE'] = (int)$product_item['BASE'];
                $product_item['DO_10'] = (int)$product_item['DO_10'];
                $product_item['OT_10_DO_30'] = (int)$product_item['OT_10_DO_30'];
                $product_item['OT_30'] = (int)$product_item['OT_30'];
                $product_name = $product_item['NAME'];
                if ($this->category_type === 'Кальянные смеси' && strripos($product_item['NAME'],'CHABACCO') === false && strripos($product_item['NAME'],'BRUSKO')  === false) {
                    $product_name = $product_item['NAME'] . ' МРК';
                }

                $active_sheet->setCellValue('B' . $this->row_next, $this->category_type);
                $active_sheet->getStyle('B' . $this->row_next)->getFont()->setBold(true);
                $this->setBorderXls($active_sheet, 'B' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('C' . $this->row_next, $this->brand);
                $active_sheet->getStyle('C' . $this->row_next)->getFont()->setBold(true);
                $this->setBorderXls($active_sheet, 'C' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('D' . $this->row_next, $product_name);
                $this->setBorderXls($active_sheet, 'E' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('E' . $this->row_next, ' ');
                $active_sheet->getStyle('E' . $this->row_next)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('b8f1ff');
                $this->setBorderXls($active_sheet, 'D' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('F' . $this->row_next, $product_item['DO_10']);
                $this->setBorderXls($active_sheet, 'F' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('G' . $this->row_next, $product_item['OT_10_DO_30']);
                $this->setBorderXls($active_sheet, 'G' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('H' . $this->row_next, $product_item['OT_30']);
                $this->setBorderXls($active_sheet, 'H' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->setCellValue('I' . $this->row_next, 'Ссылка на товар на сайте');
                $this->setBorderXls($active_sheet, 'I' . $this->row_next, PHPExcel_Style_Border::BORDER_THIN);
                $active_sheet->getStyle('I' . $this->row_next)->getFont()->getColor()->setRGB('0A279C');
                $active_sheet->getStyle('I' . $this->row_next)->getFont()->setUnderline(true);
                $active_sheet->getCell('I' . $this->row_next)->getHyperlink()->setUrl($product_item['PATH']);
                $this->row_next++;
            }
        }

        if(!empty($child['child'])) {
            foreach($child['child'] as $ch) {
                $this->createTree($ch, $active_sheet);
            }
        }
    }


    function setBorderXls($asheet, $row, $border) {
        $BStyle = array(
            'borders' => array(
                'allborders' => array(
                    'style' => $border
                ),
            )
        );
        $asheet->getStyle($row)->applyFromArray($BStyle);

    }
}