<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\EnteregoUserExchange;

/**
 * @var CAllMain|CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

$daDataToken = Bitrix\Main\Config\Option::get('osh.shipping', "osh_da_data_token");
$jsDaDataParam = <<<JS
                    <script type="text/javascript">
                        BX.ready(function(){
                            window.daDataParam = {token: '{$daDataToken}'};
                        });
                    </script>
                JS;
$cAsset = Bitrix\Main\Page\Asset::getInstance()->addString($jsDaDataParam);

$this->addExternalJs('/bitrix/js/osh.shipping/jquery.suggestions.min.js');
$this->addExternalCss('/bitrix/modules/osh.shipping/install/css/suggestions.css');

if ($arParams['SHOW_PROFILE_PAGE'] !== 'Y') {
    LocalRedirect($arParams['SEF_FOLDER']);
}

global $USER;
if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized()) {
    LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
}

if ($arParams["MAIN_CHAIN_NAME"] <> '') {
    $APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_PROFILE"));
$user_id = $USER->GetId();

$resultQueryCompanyUser = new EnteregoUserExchange();
$resultQueryCompanyUser->USER_ID = $user_id;
$resultQueryCompanyUser->GetCompanyForUser(); ?>
<div class="hides" id="personal_company" data-user-id="<?= $user_id ?>">
    <h5 class="mb-4 font-m-21 mt-lg-0 mt-md-0 mt-3"><b>Мои компании</b></h5>
    <input type="hidden" value='<?php if (!empty($resultQueryCompanyUser->company_user)) {
        echo json_encode($resultQueryCompanyUser->company_user);
    } ?>' id="company_user" data-user-id="<?= $user_id ?>"
           data-init="<?= count($resultQueryCompanyUser->company_user['ADMIN']); ?>"/>
    <div class="mb-5 company_box">
        <form class="form_company_many mb-5">
            <div class="form-group mb-2">
                <label class="label_company font-m-14 font-w-m-600">Добавьте свою компанию</label>
            </div>
            <div class="form-group mb-3 col-md-9 col-lg-9 col-12">
                <input type="text" class="form-control input_lk" id="CompanyName" autocomplete="off"
                       placeholder="Название компании">
            </div>
            <div class="form-group mb-3 col-md-9 col-lg-9 col-12">
                <input type="text" class="form-control input_lk" id="CompanyAddress" autocomplete="off"
                       placeholder="Адрес доставки">
            </div>
            <div class="form-group mb-3 col-md-9 col-lg-9 col-12">
                <input type="text" class="form-control input_lk" id="CompanyTime" autocomplete="off"
                       placeholder="Время работы">
            </div>
            <div class="form-group mb-4 col-md-9 col-lg-9 col-12">
                <input type="text" class="form-control input_lk" id="CompanyTelephone" autocomplete="off"
                       placeholder="Телефон">
            </div>
            <div class="form-group">
                <div class="col-md-4 col-lg-4 col-12">
                    <a href="javascript:void(0)" class="btn btn_company" id="CreateCompany">Добавить компанию</a>
                </div>
            </div>
        </form>
        <?php if (!empty($resultQueryCompanyUser->company_user['ADMIN'])) { ?> <h5 class="mb-4"><b>Мои компании</b></h5>
            <div class="d-flex row_section flex-wrap justify-content-between" id="boxWithCompany"></div>

            <?php if (count($resultQueryCompanyUser->company_user['ADMIN']) > 2) { ?>
                <a href="javascript:void(0) " class="link_red_button link_menu_catalog" id="showCompanyAdmin">
                    Показать все</a>
            <?php }
        } ?>
        <?php if (!empty($resultQueryCompanyUser->company_user['USER'])) { ?> <h5 class="mb-4"><b>Доступные компании</b>
        </h5>
            <div class="d-flex row_section flex-wrap justify-content-between mt-3" id="boxWithCompanyUser"></div>
            <?php if (count($resultQueryCompanyUser->company_user['USER']) > 2) { ?>
                <a href="javascript:void(0) " class="link_red_button link_menu_catalog" id="showCompanyAdmin">
                    Показать все</a>
            <?php }
        } ?>
    </div>

    <div class="column mb-5">
        <h5 class="font-m-21"><b>Часто задаваемые вопросы</b></h5>
        <div class="accordion" id="accordionExample">
            <div class="box">
                <div class="card-header" id="headingOne">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left btn_questions" type="button"
                                data-toggle="collapse"
                                data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <span>Как начать покупать как представитель компании?</span><i
                                    class="fa fa-angle-down" aria-hidden="false"></i>
                        </button>
                    </h2>
                </div>

                <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                     data-parent="#accordionExample">
                    <div class="card-body">
                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части заказа
                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной доставки</a>,
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
                                data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <span>Как начать покупать как представитель компании?</span><i
                                    class="fa fa-angle-down" aria-hidden="false"></i>
                        </button>
                    </h2>
                </div>
                <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                     data-parent="#accordionExample">
                    <div class="card-body">
                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части заказа
                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной доставки</a>,
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
                        При отмене всего заказа мы вернём все деньги и баллы. Если вы отказались от части заказа
                        и стоимость оставшихся товаров ниже необходимой для <a href="#">бесплатной доставки</a>,
                        мы вернём
                        деньги за отменённые товары, но вычтем из этой суммы стоимость доставки.
                        При возврате товаров после получения мы вернём все деньги и баллы, если возврат был
                        правильно оформлен. Деньги за каждый из товаров и доставку возвращаются отдельно.
                    </div>
                </div>
            </div>
        </div>
    </div>
    <a href="javascript:history.go(-1)" class="d-lg-none d-md-none d-block btn_company width_100 text-center">Назад</a>
</div>
