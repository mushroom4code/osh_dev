<?php
function MyOnAdminTabControlBegin(&$form) {
    $cartRulesUrl = "/bitrix/admin/sale_discount_edit.php";
    if (($GLOBALS["APPLICATION"]->GetCurPage() == $cartRulesUrl)
        && (strpos($form->arParams['FORM_ACTION'], $cartRulesUrl) !== false)) {
        $htmlSelectOptions = '';
        $discountLIDs = [];
        if ($_REQUEST['ID']) {
            $discountRes = \Enterego\DiscountsSubsidiariesTable::getList(['filter' => [
                'DISCOUNT_ID' => $_REQUEST['ID']
            ]]);
            while ($discountRow = $discountRes->fetch()) {
                $discountLIDs[] = $discountRow['SITE_ID'];
            }
        }
        $siteList = CSite::GetList();
        while ($site = $siteList->fetch()) {
            $htmlSelectOptions .= '<option value="'. $site['ID'] .'" '
                . (in_array($site['ID'], $discountLIDs) ? 'selected' : '') .'>('. $site['ID'] .') '. $site['NAME']
                .'</option>';
        }
        $form->arFields["LIDS"] = [
            "id" => "LIDS",
            "required" => false,
            "content" => "Сайт или несколько сайтов:",
            "html" => '<td width="40%"><span class="adm-required-field">Сайт или несколько сайтов:</span></td>'
                .'<td><select name="LIDS[]" multiple>'. $htmlSelectOptions .'</select></td>',
            "custom_html" => "",
            "delimiter" => false,
            "valign" => ""
        ];
        $form->tabs[0]["FIELDS"]["LIDS"] = [
            "id" => "LIDS",
            "required" => false,
            "content" => "Сайт или несколько сайтов:",
            "html" => '<td width="40%"><span class="adm-required-field">Сайт или несколько сайтов:</span></td>'
                .'<td><select name="LIDS[]" multiple>'. $htmlSelectOptions .'</select></td>',
        ];
    }
}

function saveSubsidiariesOnDiscountChange($event)
{
    if ($_REQUEST['LID']) {
        if (!isset($_REQUEST['LIDS']) || !in_array($_REQUEST['LID'], $_REQUEST['LIDS'])) {
            $_REQUEST['LIDS'][] = $_REQUEST['LID'];
        }
        $discountLIDs = [];
        $discountRes = \Enterego\DiscountsSubsidiariesTable::getList(['filter' => ['DISCOUNT_ID' => $_REQUEST['ID']]]);
        while ($discountRow = $discountRes->fetch()) {
            if (!in_array($discountRow['SITE_ID'], $_REQUEST['LIDS'])) {
                \Enterego\DiscountsSubsidiariesTable::delete($discountRow['ID']);
                continue;
            }
            $discountLIDs[$discountRow['SITE_ID']] = $discountRow['ID'];
        }

        foreach ($_REQUEST['LIDS'] as $LID) {
            if (array_key_exists($LID, $discountLIDs)) {
                \Enterego\DiscountsSubsidiariesTable::update($discountLIDs[$LID], ['fields' => [
                    'DISCOUNT_ID' => $_REQUEST['ID'],
                    'SITE_ID' => $LID
                ]]);
            } else {
                \Enterego\DiscountsSubsidiariesTable::add(['fields' => [
                    'DISCOUNT_ID' => $_REQUEST['ID'],
                    'SITE_ID' => $LID
                ]]);
            }
        }
    }
}

AddEventHandler("main", "OnAdminTabControlBegin", "MyOnAdminTabControlBegin");
\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', '\Bitrix\Sale\Internals\Discount::OnAfterAdd', 'saveSubsidiariesOnDiscountChange');
\Bitrix\Main\EventManager::getInstance()->addEventHandler('sale', '\Bitrix\Sale\Internals\Discount::OnAfterUpdate', 'saveSubsidiariesOnDiscountChange');