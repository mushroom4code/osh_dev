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
    <div class="mobile_lk mb-5 flex flex-col xs:bg-white md:flex-row">
        <div class="sidebar_lk">
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
                if (elem.value === 'Юр.лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "9999999999"});
                    return;
                }

                if (elem.value === 'ИП') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                    return;
                }

                if (elem.value === 'Физ. лицо') {
                    $('input[data-name="INN"]').inputmask({"mask": "999999999999"});
                }
            }
        </script>
        <div class="mb-5 w-full" id="content_box">
            <div class="hides" id="personal_contr_agents" data-user-id="<?= $user_id ?>">
                <input type="hidden" value='<?php if (!empty($user_object->contragents_user)) {
                    echo(json_encode($user_object->contragents_user));
                } ?>' id="personal_contr_agent">
                <input type="hidden" value='<?php if (!empty($user_object->company_user['ADMIN'])) {
                    echo(json_encode($user_object->company_user));
                } ?>' id="company_user"/>
                <input type="hidden" value='<?php if (!empty($workers_admin['WORKERS'])) {
                    echo(json_encode($workers_admin['WORKERS']));
                } ?>' id="workersForContragentAdmin"/>
                <form class="form_company_many dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 rounded-xl p-8 mb-5 w-4/5">
                    <div class="form-group mb-2">
                        <div class="width_100 d-flex flex-row justify-content-between mb-5">
                            <div class="label_company text-xl font-medium dark:text-textDarkLightGray text-textLight"
                                 id="nameBlock">Создайте своего первого контрагента
                            </div>
                        </div>
                    </div>
                    <div class="check_form mb-8" id="step_create_contragent">
                        <p class="mess_danger hide_class"></p>
                        <div class="col-12 col-md-10 flex flex-row align-items-center mb-4">
                            <div class="mr-7">
                                <input class="input_check" onchange="changeTypeInn(this);"
                                       type="radio" checked name="check" maxlength="50"
                                       id="URIC" value="Юр.лицо"/>
                                <label class="text-sm font-light "
                                       for="main-profile-radio_uric">Юр.лицо</label>
                            </div>
                            <div class="mr-7">
                                <input class="input_lk form-check-input input_check" onchange="changeTypeInn(this);"
                                       type="radio" name="check"
                                       maxlength="50"
                                       id="IP" value="ИП"/>
                                <label class="radio_input main-profile-form-label"
                                       for="main-profile-radio_IP">ИП</label>
                            </div>
                            <div class="mr-7">
                                <input class="input_lk form-check-input input_check" onchange="changeTypeInn(this);"
                                       type="radio" name="check"
                                       maxlength="50"
                                       id="FIZ" value="Физ. лицо"/>
                                <label class="radio_input main-profile-form-label"
                                       for="main-profile-radio_fiz">Физ. лицо</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="text"
                                   class="dark:bg-grayButton bg-textDark w-4/5 border-none p-3 input_lk outline-none rounded-md"
                                   id="NameCont" autocomplete="off"
                                   placeholder="Наименование организации">
                        </div>
                        <div class="mb-3">
                            <input data-name="INN" type="text"
                                   class="dark:bg-grayButton bg-textDark input_lk w-4/5 p-3 border-none outline-none rounded-md"
                                   id="INN" autocomplete="off"
                                   placeholder="ИНН">
                        </div>
                        <div class="form-group mb-3 col-md-8 col-lg-8 col-12">
                            <input type="text"
                                   class="dark:bg-grayButton border-none outline-none p-3 w-4/5 rounded-md bg-textDark input_lk"
                                   id="UrAddress" autocomplete="off"
                                   placeholder="Юридический адрес">
                        </div>
                    </div>
                    <div class="form-group mt-4">
                        <div class="d-flex flex-row align-items-center justify-content-start">
                            <a href="javascript:void(0)"
                               class="dark:bg-dark-red rounded-md bg-light-red text-white px-7 py-3 dark:shadow-md shadow-shadowDark
                               dark:hover:bg-hoverRedDark"
                               id="CreateContrAgent">Создать контрагента</a>
                        </div>
                    </div>
                </form>
                <div class="d-flex row_section flex-wrap justify-content-between mb-5 mt-3" id="boxWithContrAgents">
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
