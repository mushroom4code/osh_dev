<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Fuser;

//use Bitrix\Sale\Exchange\EnteregoUserExchange;
//use Enterego\EnteregoCompany;


/** @var CUser $USER
 * @var CAllMain|CMain $APPLICATION
 */

if ($USER->IsAuthorized()) {

    try {
        $contr = Enterego\contagents\EnteregoContragents::get_contragents_by_user_id($USER->GetId());
    } catch (\Bitrix\Main\ObjectPropertyException $e) {
    } catch (\Bitrix\Main\ArgumentException $e) {
    } catch (\Bitrix\Main\SystemException $e) {
    }
//    echo '<pre>';
//    print_r($contr);
//    echo '</pre>';
    Asset::getInstance()->addJs("/personal/contragents/js/script.js");
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
                <div id="createContragent"></div>
                <div class="d-flex row_section flex-wrap justify-content-between mb-5 mt-3" id="boxWithContrAgents">
                </div>
            </div>
        </div>
    </div>
    <script src="/dist/app.generated.js" defer></script>
    <?php
} else {
    LocalRedirect('/login/?login=yes');
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
