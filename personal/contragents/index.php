<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Enterego\EnteregoCompany;
use Bitrix\Sale\Exchange\EnteregoUserExchange;


/** @var CUser $USER
 * @var CAllMain|CMain $APPLICATION
 */

if ($USER->IsAuthorized()) {
    $user_id = $USER->GetId();
    $user_object = new EnteregoUserExchange();
    $user_object->USER_ID = $user_id;
    $user_object->GetContragentsUser();
    $user_object->GetCompanyForUser();
    $workers_admin = EnteregoCompany::GetWorkersInfo($user_id);
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
        <script>
            function changeTypeInn(elem) {
                if (elem.value==='Юр.лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "9999999999"});
                    return;
                }

                if (elem.value==='ИП') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                    return;
                }

                if (elem.value==='Физ. лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                }
            }
        </script>
        <div class="col-md-9 mb-5" id="content_box">
            <div class="hides" id="personal_contr_agents" data-user-id="<?= $user_id ?>">
                <h5 class="mb-5"><b>Контрагенты</b></h5>
                <input type="hidden" value='<?php if (!empty($user_object->contragents_user)) {
                    echo(json_encode($user_object->contragents_user));
                } ?>' id="personal_contr_agent">
                <input type="hidden" value='<?php if (!empty($user_object->company_user['ADMIN'])) {
                    echo(json_encode($user_object->company_user));
                } ?>' id="company_user"/>
                <input type="hidden" value='<?php if (!empty($workers_admin['WORKERS'])) {
                    echo(json_encode($workers_admin['WORKERS']));
                } ?>' id="workersForContragentAdmin"/>
                <div class="mb-3">
                    <form class="form_company_many mb-5">
                        <div class="form-group mb-2">
                            <div class="width_100 d-flex flex-row justify-content-between mb-4">
                                <label class="label_company" id="nameBlock">Создайте своего первого контрагента</label>
                                <h4 class="font_weight_500"><span id="stepNumber">1</span>/3</h4>
                            </div>
                        </div>
                        <div class="check_form" id="step_create_contragent">
                            <p class="mess_danger hide_class"></p>
                            <div class="col-12 col-md-10 d-flex align-items-center mb-4">
                                <input class="input_lk form-check-input input_check" onchange="changeTypeInn(this);" type="radio" checked name="check"
                                       maxlength="50"
                                       id="URIC" value="Юр.лицо"/>
                                <label class="radio_input main-profile-form-label"
                                       for="main-profile-radio_uric">Юр.лицо</label>
                                <input class="input_lk form-check-input input_check" onchange="changeTypeInn(this);" type="radio" name="check"
                                       maxlength="50"
                                       id="IP" value="ИП"/>
                                <label class="radio_input   main-profile-form-label"
                                       for="main-profile-radio_IP">ИП</label>
                                <input class="input_lk form-check-input input_check" onchange="changeTypeInn(this);" type="radio" name="check"
                                       maxlength="50"
                                       id="FIZ" value="Физ. лицо"/>
                                <label class="radio_input main-profile-form-label"
                                       for="main-profile-radio_fiz">Физ. лицо</label>
                            </div>
                            <div class="form-group mb-3 col-md-8 col-lg-8 col-12">
                                <input type="text" class="form-control input_lk" id="NameCont" autocomplete="off"
                                       placeholder="Название">
                            </div>
                            <div class="form-group mb-3 col-md-8 col-lg-8 col-12">
                                <input data-name="INN" type="text" class="form-control input_lk" id="INN" autocomplete="off"
                                       placeholder="ИНН">
                            </div>
                            <div class="form-group mb-3 col-md-8 col-lg-8 col-12">
                                <input type="text" class="form-control input_lk" id="UrAddress" autocomplete="off"
                                       placeholder="Юридический адрес">
                            </div>
                            <div class="form-group">
                                <div class="col-md-5 col-lg-5 col-12">
                                    <a href="javascript:void(0)" class="btn btn_red btn_padding_8 radius_10 width_100
                                    text_font_13" id="SaveStepContrAgent">Далее</a>
                                </div>
                            </div>
                        </div>
                        <?php if (!empty($workers_admin['WORKERS'])) { ?>
                            <div class="check_form hide_class" id="step_company_contragent_connection">
                                <div class="d-flex flex-column" id="boxWithCompanyChecked"></div>
                                <div class="form-group mt-4">
                                    <div class="d-flex flex-row align-items-center justify-content-start">
                                        <a href="javascript:void(0)" class="btn btn_red btn_popup text_font_13 mr-3
                                    font_weight_500" id="SaveContragentCompanyConnection">Далее</a>
                                        <a href="javascript:void(0)" class="btn_gray btn_popup exit_company_connection
                                    text_font_13 font_weight_500">Назад</a>
                                    </div>
                                </div>
                            </div>
                            <div class="check_form hide_class" id="step_contragent_worker">
                                <div id="boxWithWorkerChecked"></div>
                                <div class="form-group mt-4">
                                    <div class="d-flex flex-row align-items-center justify-content-start">
                                        <a href="javascript:void(0)" class="btn btn_red btn_popup text_font_13 mr-3
                                    font_weight_500" id="CreateContrAgent">Создать контрагента</a>
                                        <a href="javascript:void(0)" class="btn_gray btn_popup exit_contragent_connection
                                    text_font_13 font_weight_500">Назад</a>
                                    </div>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="check_form hide_class" id="step_company_contragent_connection">
                                <div class="d-flex flex-column" id="boxWithCompanyChecked"></div>
                                <div class="form-group mt-4">
                                    <div class="d-flex flex-row align-items-center justify-content-start">
                                        <a href="javascript:void(0)" class="btn btn_red btn_popup text_font_13 mr-3
                                    font_weight_500" id="CreateContrAgent">Создать контрагента</a>
                                        <a href="javascript:void(0)" class="btn_gray btn_popup exit_company_connection
                                    text_font_13 font_weight_500">Назад</a>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                    </form>
                    <div class="d-flex row_section flex-wrap justify-content-between mb-5 mt-3" id="boxWithContrAgents">
                    </div>
                </div>

                <div class="column margin_box">
                    <h5 class="mb-5"><b>Часто задаваемые вопросы</b></h5>
                    <div class="accordion" id="accordionExample">
                        <div class="box">
                            <div class="card-header" id="headingOne">
                                <h2 class="mb-0">
                                    <button class="btn btn-link btn-block text-left btn_questions" type="button"
                                            data-toggle="collapse"
                                            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        <span>Как начать покупать как представитель компании?</span><i
                                                class="fa fa-angle-down" aria-hidden="true"></i>
                                    </button>
                                </h2>
                            </div>

                            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne"
                                 data-parent="#accordionExample">
                                <div class="card-body">
                                    При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части
                                    заказа
                                    и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                        доставки</a>,
                                    мы вернём
                                    деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                    При возврате товаров после получения мы вернём все деньги и баллы, если возврат был
                                    правильно оформлен. Деньги за каждый из товаров и доставку возвращаются отдельно.
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
                                    При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части
                                    заказа
                                    и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                        доставки</a>,
                                    мы вернём
                                    деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                    При возврате товаров после получения мы вернём все деньги и баллы, если возврат был
                                    правильно оформлен. Деньги за каждый из товаров и доставку возвращаются отдельно.
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
                                    При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части
                                    заказа
                                    и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной
                                        доставки</a>,
                                    мы вернём
                                    деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                                    При возврате товаров после получения мы вернём все деньги и баллы, если возврат был
                                    правильно оформлен. Деньги за каждый из товаров и доставку возвращаются отдельно.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
