<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Enterego\EnteregoCompany;


/** @var CUser $USER
 * @var  CAllMain|CMain $APPLICATION
 */

if ($USER->IsAuthorized()) {
    $user_id = $USER->GetId();
    $getUsers = EnteregoCompany::GetWorkers($user_id);
    ?>
    <script src="https://cdn.jsdelivr.net/npm/jquery.maskedinput@1.4.1/src/jquery.maskedinput.min.js" type="text/javascript"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
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
        <div class="col-md-9 mb-5" id="content_box">
            <div class="hides" id="personal_worker" data-user-id="<?= $user_id ?>">
                <h5 class="mb-5"><b>Сотрудники</b></h5>
                <input type="hidden" value='<?php if (!empty($getUsers['WORKERS'])) {
                    echo(json_encode($getUsers));
                } ?>' id="personal_worker">
                <input type="hidden" value='<?php if (!empty($getUsers['COMPANY'])) {
                    echo(json_encode($getUsers['COMPANY']));
                } ?>' id="companyArrayForSelected">
                <input type="hidden" value='<?php if (!empty($getUsers['CONTR_AGENT'])) {
                    echo(json_encode($getUsers['CONTR_AGENT']));
                } ?>' id="contrAgentArrayForSelected">
                <?php if (empty($getUsers['WORKERS'])) { ?>
                    <div class="row mb-5 ">
                        <form class="form_company_many mb-5">
                            <div class="form-group mb-2">
                                <label class="label_company">Создайте своего первого сотрудника</label>
                            </div>
                            <div class="form-group mb-3 col-md-7 col-lg-7 col-12">
                                <span style="color: red" class="FIOError"></span>
                                <input required type="text" class="form-control input_lk" id="FIOWorker" autocomplete="off"
                                       placeholder="ФИО">
                            </div>
                            <div class="form-group mb-4 col-md-7 col-lg-7 col-12">
                                <span style="color: red" class="email_error"></span>
                                <input type="text" class="form-control input_lk" id="EmailWorker" autocomplete="off"
                                       placeholder="Адрдес эл. почты">
                            </div>
                            <div class="form-group mb-4 col-md-7 col-lg-7 col-12">
                                <input type="text" class="form-control input_lk" id="PhoneWorker" autocomplete="off"
                                       placeholder="Телефон"><span style="color: red" class="error"></span>
                            </div>
                            <div class="form-group">
                                <div class="col-md-4 col-lg-4 col-12">
                                    <a href="javascript:void(0)" class="btn btn_company" id="CreateWorker">Создать
                                        сотрудника</a>
                                </div>
                            </div>
                        </form>

                    </div>
                    <div class="column margin_box">
                        <h5 class="mb-5"><b>Часто задаваемые вопросы</b></h5>
                        <div class="accordion" id="accordionExample">
                            <div class="box">
                                <div class="card-header" id="headingOne">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left btn_questions" type="button"
                                                data-toggle="collapse"
                                                data-target="#collapseOne" aria-expanded="false"
                                                aria-controls="collapseOne">
                                            <span>Как начать покупать как представитель компании?</span><i
                                                    class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </h2>
                                </div>

                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                                     data-parent="#accordionExample">
                                    <div class="card-body">
                                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от
                                        части
                                        заказа
                                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                            доставки</a>,
                                        мы вернём
                                        деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                        При возврате товаров после получения мы вернём все деньги и баллы, если возврат
                                        был
                                        правильно оформлен. Деньги за каждый из товаров и доставку возвращаются
                                        отдельно.
                                    </div>
                                </div>
                            </div>
                            <div class="box">
                                <div class="card-header" id="headingTwo">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left btn_questions" type="button"
                                                data-toggle="collapse"
                                                data-target="#collapseTwo" aria-expanded="false"
                                                aria-controls="collapseTwo">
                                            <span>Как начать покупать как представитель компании?</span><i
                                                    class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                                     data-parent="#accordionExample">
                                    <div class="card-body">
                                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от
                                        части
                                        заказа
                                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                            доставки</a>,
                                        мы вернём
                                        деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                        При возврате товаров после получения мы вернём все деньги и баллы, если возврат
                                        был
                                        правильно оформлен. Деньги за каждый из товаров и доставку возвращаются
                                        отдельно.
                                    </div>
                                </div>
                            </div>
                            <div class="box">
                                <div class="card-header" id="headingThree">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left btn_questions" type="button"
                                                data-toggle="collapse"
                                                data-target="#collapseThree" aria-expanded="false"
                                                aria-controls="collapseThree">
                                            <span>Как начать покупать как представитель компании?</span><i
                                                    class="fa fa-angle-down" aria-hidden="true"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseThree" class="collapse" aria-labelledby="headingThree"
                                     data-parent="#accordionExample">
                                    <div class="card-body">
                                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от
                                        части
                                        заказа
                                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                            доставки</a>,
                                        мы вернём
                                        деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                        При возврате товаров после получения мы вернём все деньги и баллы, если возврат
                                        был
                                        правильно оформлен. Деньги за каждый из товаров и доставку возвращаются
                                        отдельно.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php } else { ?>
                    <div class="d-flex justify-content-between flex-row">

                    </div>
                    <table id="TableWorkers" class="mb-5">
                        <thead class="bc-gray">
                        <tr>
                            <th>Сотрудники</th>
                            <th>Телефон</th>
                            <th>Компания</th>
                            <th>Контрагент</th>
                            <th class="no-sorting"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($getUsers['WORKERS'] as $item) {
                            ?>
                            <tr data-user-id-worker="<?= $item['USER_ID_WORKER']?>">
                                <?php if($item['ACTIVE'] === 'N'){ ?>
                                <td class="d-flex flex-row align-items-center archived_user" activity = 'N'>
                                    <span class="avatar mr-3">
                                          <span class="name"><?= $item['LOGIN'] ?></span>
                                    </span>
                                    <div>
                                        <a href="/personal/workers/user/<?= $item['USER_ID_WORKER'] ?>"
                                           class="d-flex flex-column">
                                        <span class="name_user"><?= $item['NAME'] ?></span>
                                        <span class="email_user"> <?= $item['EMAIL'] ?></span>
                                        </a>
                                    </div>
                                </td>
                                <td class="phone_user archived_user"><?=$item['PERSONAL_PHONE']?></td>
                                <?php }else{ ?>
                                    <td class="d-flex flex-row align-items-center" activity = 'N'>
                                    <span class="avatar mr-3">
                                          <span class="name"><?= $item['LOGIN'] ?></span>
                                    </span>
                                        <div>
                                            <a href="/personal/workers/user/<?= $item['USER_ID_WORKER'] ?>"
                                               class="d-flex flex-column">
                                                <span class="name_user"><?= $item['NAME'] ?></span>
                                                <span class="email_user"> <?= $item['EMAIL'] ?></span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="phone_user"><?=$item['PERSONAL_PHONE']?></td>
                                <?php } ?>
                                <td <?= $item['COMPANY_ID'] ?>><?= $item['NAME_COMP'] ?></td>
                                <td <?= $item['CONTR_AGENT_ID'] ?>><?= $item['NAME_CONT'] ?></td>
                                <td><span class="icon_edit_lk">
                                        <div class="box_edit" style="display: none;">
                                        <span class="EDIT_INFO_USER">Редактировать</span>
                                        <span class="ARCHIVE_USER">Архивировать</span>
                                    </div>
                                    </span></td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                <?php } ?>
                <div class="d-flex row_section flex-wrap justify-content-between mb-5 mt-3"
                     id="boxWithContrPeople"></div>
            </div>
        </div>
    </div>
    <?php
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>

