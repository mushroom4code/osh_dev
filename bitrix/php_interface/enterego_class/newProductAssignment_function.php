<?php
function newProductAssignment() {

    $nowDate = date("d.m.Y H:i:s");
    $date = new DateTime($nowDate);
    $dateAMonthAgo = $date->sub(new DateInterval('P0Y1M0DT0H0M0S'))->format('d.m.Y H:i:s');

    $rs = CIBlockElement::GetList(
        ['DATE_CREATE' => 'DESC'],
        ['IBLOCK_ID' => CATALOG_IBLOCK_ID, '>DATE_CREATE'=>$dateAMonthAgo],
        false, [],
        ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE', 'PROPERTY_NEW']
    );
    $arNewProduct = [];
    while ($ar = $rs->Fetch()) {
        $arNewProduct[] = $ar['ID'];
        $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $ar["IBLOCK_ID"], "CODE" => "NEW"));
        while ($enum_fields = $property_enums->GetNext()) {
            if ($enum_fields["VALUE"] == "Да") {
                $arPropertyNew = array(
                    "NEW" => $enum_fields["ID"],
                );
            }
        }
        CIBlockElement::SetPropertyValuesEx($ar["ID"], false, $arPropertyNew);
    }

    $rs = CIBlockElement::GetList(
        ['DATE_CREATE' => 'DESC'],
        ['!ID' => $arNewProduct, 'IBLOCK_ID' => CATALOG_IBLOCK_ID, 'PROPERTY_NEW_VALUE' => "Да"],
        false, false,
        ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE', 'PROPERTY_NEW']
    );
    while ($ar = $rs->Fetch()) {
        $arNewProduct[] = $ar['ID'];
        if(count($arNewProduct) >= 50) {
            $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $ar["IBLOCK_ID"], "CODE" => "NEW"));
            while ($enum_fields = $property_enums->GetNext()) {
                if ($enum_fields["VALUE"] == "Нет") {
                    $arPropertyNew = array(
                        "NEW" => $enum_fields["ID"],
                    );
                }
            }
            CIBlockElement::SetPropertyValuesEx($ar["ID"], false, $arPropertyNew);
        }
    }

    return "newProductAssignment();";
}