<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Enterego\EnteregoCompany;


/** @var CUser $USER
 * @var  CAllMain|CMain $APPLICATION
 */
$seeUser = false;
$UserData = [];

if ($USER->IsAuthorized()) {

    $user_id = $USER->GetId();
    $getUsers = EnteregoCompany::GetWorkers($user_id);

    $str = explode('/', $_SERVER['REQUEST_URI']);

    $content404 = '<div class="d-flex flex-column section_404 justify-content-center align-items-center mb-5 mt-5">
                        <div class="box_with_404 mb-5"></div>
                        <h5 class="text-center"><b>Такого пользователя нет в списке ваших сотрудников, 
                        <br>вы можете вернуться в <a href="/personal/workers/" class="red_text"> список </a>
                        для просмотра</b></h5>
                    </div>';
    if (!empty($getUsers['WORKERS'])) {
        foreach ($getUsers['WORKERS'] as $items) {
            if ($items['USER_ID_WORKER'] == $str[4]) {
                $seeUser = true;
                $UserData = $items;
            }
        }

        ?>
        <div class="mobile_lk mb-5">
            <div class="sidebar_lk col-md-3">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "",
                    array(
                        "ALLOW_MULTI_SELECT" => "N",
                        "CHILD_MENU_TYPE" => "left",
                        "DELAY" => "N",
                        "MAX_LEVEL" => "1",
                        "MENU_CACHE_GET_VARS" => array(""),
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "ROOT_MENU_TYPE" => "personal",
                        "USE_EXT" => "N"
                    )
                ); ?>
            </div>
            <div class="col-md-9 mb-5" id="worker_pages">
                <?php
                if ($seeUser) {
                    $getAccessFromWorkers = EnteregoCompany::GetAccessFromWorkers($user_id, $UserData['USER_ID_WORKER']);
                    ?>
                    <div class="d-flex column_section mb-4 justify-content-between mt-3">
                        <div class="mobile">
                            <h5><b>Сотрудники</b></h5>
                        </div>
                        <a href="/personal/workers/" class="color_black text_font_13 link_work"> К сотрудникам </a>
                    </div>

                    <div class="name_worker d-flex flex-row mb-5" data-worker-id="<?= $UserData['USER_ID_WORKER'] ?>">
                        <h4 class="mb-3 d-flex flex-row align-items-center">
                            <span class="avatar mr-3"><span class="name"><b><?= $UserData['LOGIN'] ?></b></span></span>
                            <div class="d-flex flex-column">
                                <span class="name_mobile mb-1"><b><?= $UserData['LOGIN'] ?></b></span>
                                <div class="d-flex flex-row">
                                    <span class="color_blue mr-3"><?= $UserData['EMAIL'] ?></span>
                                    <?php if (!empty($UserData['PERSONAL_PHONE'])) { ?>
                                        <span class="color_blue"><?= $UserData['PERSONAL_PHONE'] ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </h4>
                        <span class="message d-flex width_100 justify-content-end text_font_13"></span>
                    </div>
                    <section class="mb-5 d-flex flex-column box_section_workers_info">
                        <h6 class="box_text_container"><b>Доступ к компаниям</b></h6>
                        <div class="d-flex flex-column workers_company">
                            <?php if (!empty($getAccessFromWorkers)) {
                                foreach ($getAccessFromWorkers['ADMIN_DATA']['COMPANY'] as $company) {
                                    $checkCompanyControls = '';
                                    if (!empty($getAccessFromWorkers['WORKER_DATA']['COMPANY'])) {
                                        foreach ($getAccessFromWorkers['WORKER_DATA']['COMPANY'] as $key => $item) {
                                            if ($key == $company['COMPANY_ID']) {
                                                $checkCompanyControls = 'checked="checked"';
                                            }
                                        }
                                    } ?>
                                    <div class="line_info d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-row align-items-center">
                                            <span class="icons_worker_comp mr-2"></span>
                                            <span data-company-id="<?= $company['COMPANY_ID'] ?>"
                                                  class="mr-3 name_box"><?= $company['NAME_COMP'] ?></span>
                                            <?php if (!empty($company['PHONE_COMPANY'])) { ?>
                                                <span class="color_blue"><?= $company['PHONE_COMPANY'] ?></span>
                                            <?php } ?>
                                        </div>
                                        <input type="checkbox"
                                               class="check_input form-check-input" <?= $checkCompanyControls ?>
                                               data-method-for-workers="company"
                                               data-company-id="<?= $company['COMPANY_ID'] ?>"/>
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </section>
                    <section class="mb-5 d-flex flex-column box_section_workers_info">
                        <h6 class="box_text_container"><b>Доступ к контрагентам</b></h6>
                        <div class="d-flex flex-column workers_contragents">
                            <?php if (!empty($getAccessFromWorkers)) {
                                foreach ($getAccessFromWorkers['ADMIN_DATA']['CONTR_AGENT'] as $contr_agent) {
                                    $checkCompanyControls = '';
                                    if (!empty($getAccessFromWorkers['WORKER_DATA']['userContrAgentID'])) {
                                        foreach ($getAccessFromWorkers['WORKER_DATA']['userContrAgentID'] as $key => $item) {
                                            if ($key == $contr_agent['CONTR_AGENT_ID']) {
                                                $checkCompanyControls = 'checked="checked"';
                                            }
                                        }
                                    } ?>
                                    <div class="line_info d-flex flex-row justify-content-between">
                                        <div class="d-flex flex-row align-items-center">
                                            <span class="icons_worker_contr mr-2"></span>
                                            <span data-contragent-id="<?= $contr_agent['CONTR_AGENT_ID'] ?>"
                                                  class="mr-3 name_box"><?= $contr_agent['NAME_CONT'] ?></span>
                                            <?php if (!empty($contr_agent['INN'])) { ?>
                                                <span class="color_blue"><?= $contr_agent['INN'] ?></span>
                                            <?php } ?>
                                        </div>
                                        <input type="checkbox" class="check_input form-check-input"
                                               data-method-for-workers="contragent" <?= $checkCompanyControls ?>
                                               data-contragent-id="<?= $contr_agent['CONTR_AGENT_ID'] ?>"/>
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </section>
                    <?php if (!empty($getAccessFromWorkers['WORKER_DATA']['ORDERS'])) { ?>
                        <section class="mb-5 d-flex flex-column box_section_workers_info">
                            <h6 class="box_text_container"><b>Заказы сотрудника</b></h6>
                            <div class="d-flex flex-column">
                                <?php if (!empty($getAccessFromWorkers)) {
                                    foreach ($getAccessFromWorkers['WORKER_DATA']['ORDERS'] as $order) { ?>
                                        <div class="line_info d-flex flex-row justify-content-between">
                                            <div class="d-flex flex-row align-items-center">
                                            <span data-company-id="<?= $order['ORDER_ID'] ?>"
                                                  class="mr-3 name_box">№ <?= $order['ORDER_ID'] ?></span>
                                            </div>
                                            <a href="/personal/orders/<?=$order['ORDER_ID']?>/" class="link_repeat_orders">
                                                Подробности заказа</a>
                                        </div>
                                    <?php }
                                } ?>
                            </div>
                        </section>
                    <?php } ?>
                    <a href="javascript:void(0)" class="btn btn_company link_save"
                       id="SaveParamsWorker">Сохранить</a>
                    <?php
                } else {
                    echo $content404;
                } ?>
            </div>
        </div>
        <?php
    } else {
        echo $content404;
    }
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
