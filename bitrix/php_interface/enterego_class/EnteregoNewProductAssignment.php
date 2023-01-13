<?php
const COUNT_NEW_PRODUCTS = 50;
function monthAgo() {
     $nowDate = date("d.m.Y H:i:s");
     $date = new DateTime($nowDate);
     return $date->sub(new DateInterval('P0Y1M0DT0H0M0S'))->format('d.m.Y H:i:s');
}

function newProductAssignment(): string
{
    $rs = CIBlockElement::GetList(
        ['DATE_CREATE' => 'DESC'],
        ['IBLOCK_ID' => CATALOG_IBLOCK_ID, '>DATE_CREATE'=>monthAgo()],
        false, [],
        ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE', 'PROPERTY_NEW']
    );

    $property_enums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"),
        array("IBLOCK_ID" => CATALOG_IBLOCK_ID, "CODE" => "NEW"));
    while ($enum_fields = $property_enums->GetNext()) {
        if ($enum_fields["VALUE"] == "Да") {
            $arPropertyNewTrue = array(
                PROP_NEW => $enum_fields["ID"],
            );
        } elseif ($enum_fields["VALUE"] == "Нет") {
            $arPropertyNewFalse = array(
                PROP_NEW => $enum_fields["ID"],
            );
        }
    }

    $arNewProduct = [];
    while ($ar = $rs->Fetch()) {
        $arNewProduct[] = $ar['ID'];
        CIBlockElement::SetPropertyValuesEx($ar["ID"], false, $arPropertyNewTrue);
    }
    $rs = CIBlockElement::GetList(
        ['DATE_CREATE' => 'DESC'],
        ['!ID' => $arNewProduct, 'IBLOCK_ID' => CATALOG_IBLOCK_ID, 'PROPERTY_NEW_VALUE' => "Да"],
        false, false,
        ['ID', 'IBLOCK_ID', 'NAME', 'DATE_CREATE', 'PROPERTY_NEW']
    );
    while ($ar = $rs->Fetch()) {
        $arNewProduct[] = $ar['ID'];
        if(count($arNewProduct) >= COUNT_NEW_PRODUCTS) {
            CIBlockElement::SetPropertyValuesEx($ar["ID"], false, $arPropertyNewFalse);
        }
    }

    return "newProductAssignment();";
}