<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if ($request->getPost('action') == 'setLocationsListStorage' || $request->getPost('action') == 'locationsListSearch'
    || $request->getPost('action') == 'locationsListSubmit') {
    $locationsFilter = array('=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=TYPE.ID' => '5');
    if ($request->getPost('action') == 'locationsListSearch' || $request->getPost('action') == 'locationsListSubmit') {
        $locationsFilter['NAME_RU'] = '%'.$request->getPost('searchText').'%';
    }
    $res = \Bitrix\Sale\Location\LocationTable::getList(array(
        'select' => array('*', 'NAME_RU' => 'NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE'),
        'filter' => $locationsFilter
    ));

    if ($request->getPost('action') == 'locationsListSubmit') {
        $found = false;
        while ($itemcity = $res->fetch()) {
            if ($itemcity['NAME_RU'] == $request->getPost('searchText')) {
                $_SESSION["city_of_user"] = $itemcity['NAME_RU'];
                $_SESSION["id_region"] = $itemcity['CITY_ID'];
                $_SESSION["code_region"] = $itemcity['CODE'];
                $_SESSION["real_region"] = 'n';
                $found = true;
                exit(json_encode(array('status' => 'success')));
            }
        }
        if (!$found) {
            exit(json_encode(array('status' => 'failed to change the city')));
        }
    }

    $runames = array();
    while ($item = $res->fetch()) {
        $runames[] = $item["NAME_RU"];
    }
    sort($runames);

    echo exit(json_encode($runames));
}