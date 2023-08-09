<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);

$sTableID = "tbl_main_page_products";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
function CheckFilter()
{
    global $FilterArr, $lAdmin;
    foreach ($FilterArr as $f) global $$f;

    return count($lAdmin->arFilterErrors) == 0;
}

$FilterArr = array(
    "find_id",
    "find_name",
    "find_active",
    "find_used_on_main_page",
    "find_popularity"
);

$lAdmin->InitFilter($FilterArr);

if (CheckFilter()) {
    $arFilter = array(
        "IBLOCK_ID" => IBLOCK_CATALOG,
        "ID" => $find_id,
        "%NAME" => $find_name,
        "ACTIVE" => $find_active,
        "PROPERTY_" . SORT_POPULARITY => $find_popularity
    );
    if ($find_used_on_main_page == 'N') {
        $arFilter["!=PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE] = 'Y';
    } else {
        $arFilter["PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE] = $find_used_on_main_page;
    }
}

if ($lAdmin->EditAction()) {
    foreach ($FIELDS as $ID => $arFields) {
        if (!$lAdmin->IsUpdated($ID))
            continue;

        $DB->StartTransaction();
        $ID = IntVal($ID);
        if (($rsData = CIBlockElement::GetByID($ID)) && ($arData = $rsData->Fetch())) {
            if (!empty($arFields['PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE"])) {
                CIBlockElement::SetPropertyValueCode($ID, PROPERTY_USE_ON_MAIN_PAGE, $arFields['PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE"]);
                unset($arFields['PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE"]);
            }
            if (!empty($arFields['PROPERTY_' . SORT_POPULARITY . "_VALUE"])) {
                CIBlockElement::SetPropertyValueCode($ID, SORT_POPULARITY, $arFields['PROPERTY_' . SORT_POPULARITY . "_VALUE"]);
                unset($arFields['PROPERTY_' . SORT_POPULARITY . "_VALUE"]);
            }
            $el = new CIBlockElement;
            if (!$el->Update($ID, $arFields)) {
                $lAdmin->AddGroupError("Ошибка при сохранении изменений товара: " . $el->LAST_ERROR, $ID);
                $DB->Rollback();
            }
        } else {
            $lAdmin->AddGroupError("Ошибка при сохранении изменений: невозможно найти редактируемый элемент", $ID);
            $DB->Rollback();
        }
        $DB->Commit();
    }
}

$lAdmin->GroupAction();

$rsData = CIBlockElement::GetList(
        array($by => $order),
        $arFilter,
        false,
        false,
        ['ID', 'NAME', 'ACTIVE', 'PROPERTY_' . PROPERTY_USE_ON_MAIN_PAGE, 'PROPERTY_' . SORT_POPULARITY]
);

$rsData = new CAdminResult($rsData, $sTableID);

$rsData->NavStart();

$lAdmin->NavText($rsData->GetNavPrint('Страница'));

$lAdmin->AddHeaders(array(
    array("id" => "ID",
        "content" => "ID",
        "sort" => "id",
        "align" => "right",
        "default" => true,
    ),
    array("id" => "NAME",
        "content" => "Название",
        "sort" => "name",
        "default" => true,
    ),
    array("id" => "ACTIVE",
        "content" => "Активность",
        "sort" => "ACTIVE",
        "default" => true,
    ),
    array("id" => "PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE",
        "content" => "Использование на главной странице",
        "sort" => "PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE,
        "default" => true,
    ),
    array("id" => "PROPERTY_" . SORT_POPULARITY . "_VALUE",
        "content" => "Популярность",
        "sort" => "PROPERTY_" . SORT_POPULARITY,
        "default" => true,
    ),
));

while ($arRes = $rsData->NavNext(true, "f_")):
    $arRes["PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE] = $arRes["PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE"];
    $arRes["PROPERTY_" . SORT_POPULARITY] = $arRes["PROPERTY_" . SORT_POPULARITY . "_VALUE"];
    $row =& $lAdmin->AddRow($f_ID, $arRes);

    $row->AddInputField("NAME", array("size" => 20));
    $row->AddCheckField("ACTIVE");
    $row->AddCheckField("PROPERTY_" . PROPERTY_USE_ON_MAIN_PAGE . "_VALUE");
    $row->AddInputField("PROPERTY_" . SORT_POPULARITY . "_VALUE", array("size" => 20));
endwhile;

$lAdmin->CheckListMode();

$APPLICATION->SetTitle("Товары используемые на главной странице");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$oFilter = new CAdminFilter(
    $sTableID . "_filter",
    array(
        "ID",
        "Название",
        "Активность",
        "Использование на главной странице",
        "Популярность"
    )
);
?>
    <form name="find_form" method="get" action="<?php echo $APPLICATION->GetCurPage(); ?>">
        <?php $oFilter->Begin(); ?>
        <tr>
            <td><?= "ID" ?>:</td>
            <td>
                <input type="text" name="find_id" size="47" value="<?php echo htmlspecialchars($find_id) ?>">
            </td>
        </tr>
        <tr>
            <td>Название:</td>
            <td><input type="text" name="find_name" size="47" value="<?php echo htmlspecialchars($find_name) ?>"></td>
        </tr>
        <tr>
            <td>Активность:</td>
            <td>
                <?php
                $arr = array(
                    "reference" => array(
                        "Да",
                        "Нет",
                    ),
                    "reference_id" => array(
                        "Y",
                        "N",
                    )
                );
                echo SelectBoxFromArray("find_active", $arr, $find_active, GetMessage("POST_ALL"), "");
                ?>
            </td>
        </tr>
        <tr>
            <td>Используется на главной странице:</td>
            <td>
                <?php
                $arr = array(
                    "reference" => array(
                        "Да",
                        "Нет",
                    ),
                    "reference_id" => array(
                        "Y",
                        "N",
                    )
                );
                echo SelectBoxFromArray("find_used_on_main_page", $arr, $find_active, GetMessage("POST_ALL"), "");
                ?>
            </td>
        </tr>
        <tr>
            <td>Популярность:</td>
            <td><input type="text" name="find_popularity" size="47" value="<?php echo htmlspecialchars($find_popularity) ?>"></td>
        </tr>
        <?php
        $oFilter->Buttons(array("table_id" => $sTableID, "url" => $APPLICATION->GetCurPage(), "form" => "find_form"));
        $oFilter->End();
        ?>
    </form>

<?php
$lAdmin->DisplayList();
?>

<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>