<?php use enterego\EnteregoUser;
use Bitrix\Conversion\Internals\MobileDetect;

$mobile = new MobileDetect();

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var  CAllMain|CMain $APPLICATION
 ** @var  CAllUser $USER
 */
global $option_site;
if (!$USER->IsAuthorized() && strripos($_SERVER['REQUEST_URI'], 'catalog')) {
    ?>
    </div><?php }
$option = $option_site; ?>
</div>
<!--end .container.bx-content-section-->
</div><!--end .workarea-->

<?php $userData = EnteregoUser::getInstance(); ?>
<footer class="flex flew-row justify-center dark:bg-dark dark:text-textDark w-full border-t border-white mb-4">
    <div class="xl:container container ls:lg:md:p-0 p-4 foot_container mt-10">
        <div class="flex flex-row xs:flex-col mb-8 justify-between flex-wrap">
            <div class="columns-3 flex flex-col">
                <div class="w-44 mb-3">
                    <a href="/">
                        <img src="/local/templates/Oshisha/images/logo/osh.svg"
                             srcset="/local/templates/Oshisha/images/logo/osh.svg"/>
                    </a>
                </div>
                <div class="flex flex-col dark:text-textDarkLightGray">
                    <span class="text-sm font-light mb-5">Оптовый портал<br>товаров для кальяна</span>
                    <a class="text-sm font-light mb-2 underline hover:text-hover-red"
                       href="tel:<?= $option->PHONE ?>"><?= $option->PHONE ?></a>
                    <a class="text-sm font-light mb-5 underline hover:text-hover-red" href="tel:88006004424">8-800-600-44-24</a>
                    <span class="text-sm font-light mb-5">Время работы <br>10:00 - 20:00, ежедневно.</span>
                    <a class="text-sm font-light underline hover:text-hover-red" href="mailto:info@oshisha.net">info@oshisha.net </a>
                </div>
            </div>
            <div class="columns-3 flex flex-col dark:text-textDarkLightGray">
                <span class="text-sm font-medium dark:text-textDarkLightGray js__collapse-list mb-3">Каталог</span>
                <ul class="text-sm font-light">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "bottom_menu",
                        array(
                            "ROOT_MENU_TYPE" => "left",
                            "MENU_CACHE_TYPE" => "A",
                            "MENU_CACHE_TIME" => "36000000",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(),
                            "CACHE_SELECTED_ITEMS" => "N",
                            "MAX_LEVEL" => "1",
                            "USE_EXT" => "Y",
                            "DELAY" => "N",
                            "ALLOW_MULTI_SELECT" => "N"
                        ),
                        false
                    ); ?>
                </ul>
            </div>
            <div class="columns-3 flex flex-col dark:text-textDarkLightGray">
                <span class="text-sm font-semibold js__collapse-list mb-3">Покупателям</span>
                <ul class="text-sm font-light">
                    <li class="mb-2">
                        <a class="hover:text-hover-red" href="/about/contacts/">Контакты</a>
                    </li>
                    <li class="mb-2">
                        <a class="hover:text-hover-red" href="/about/feedback/">Обратная связь</a>
                    </li>
                    <li class="mb-2">
                        <a class="hover:text-hover-red js__callback" href="javascript:void(0)">Обратный звонок</a>
                    </li>
                    <?php if ($USER->IsAuthorized()): ?>
                        <li class="mb-2">
                            <a class="hover:text-hover-red" href="/about/FAQ/">FAQ</a>
                        </li>
                    <?php endif; ?>
                    <li class="mb-2">
                        <a class="hover:text-hover-red" href="/about/users_rules/">Пользовательское соглашение</a>
                    </li>
                    <li class="mb-2">
                        <a class="hover:text-hover-red" href="/about/politics/">Политика конфиденциальности</a>
                    </li>
                    <li class="mb-2">
                        <a class="hover:text-hover-red" href="/about/cookie/">Политика обработки Cookie</a>
                    </li>
                </ul>
            </div>
            <div class="columns-3 flex flex-col dark:text-textDarkLightGray">
                <span class="text-sm font-semibold js__collapse-list mb-3">О компании</span>
                <ul class="text-sm font-light">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "bottom_menu",
                        [
                            "ROOT_MENU_TYPE" => "bottom",
                            "MAX_LEVEL" => "1",
                            "MENU_CACHE_TYPE" => "A",
                            "CACHE_SELECTED_ITEMS" => "N",
                            "MENU_CACHE_TIME" => "36000000",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => [],
                        ],
                        false
                    ); ?>
                    <li class="mb-2">
                        <?php $href = $USER->IsAuthorized() ? $option->price_list_link : '/login/' ?>
                        <a class="col-menu-link hover:text-hover-red" href="<?= $href ?>">Прайс-лист</a>
                    </li>
                    <li class="mb-2">
                        <a href="/about/vacancy/" class="col-menu-link hover:text-hover-red">Вакансии</a>
                    </li>
                </ul>
                <div class="mt-5">
                    <div class="text-sm font-semibold js__collapse-list mb-3">Социальные сети</div>
                    <nav class="flex flex-row">
                        <a href="<?= $option->TG; ?>" target="_blank">
                            <img class="tg" src="<?= SITE_TEMPLATE_PATH . '/images/tg.svg' ?>">
                        </a>
                        <a href="<?= 'https://api.whatsapp.com/send?phone=' . $option->PHONE_WTS ?>" target="_blank">
                            <img class="ws" src="<?= SITE_TEMPLATE_PATH . '/images/ws.svg' ?>">
                        </a>

                        <a href="<?= $option->VK_LINK; ?>" target="_blank">
                            <img class="vk" src="<?= SITE_TEMPLATE_PATH . '/images/vk.svg' ?>">
                        </a>
                        <a href="<?= $option->DZEN; ?>" target="_blank">
                            <img class="dzen" src="<?= SITE_TEMPLATE_PATH . '/images/dzen.svg' ?>">
                        </a>
                    </nav>
                </div>
            </div>
        </div>
        <?php if (!empty($option->text_rospetrebnadzor_row)): ?>
            <p class="text-xs mb-2 font-thin dark:text-textDarkLightGray"><?= $option->text_rospetrebnadzor_row; ?></p>
            <p class="text-xs mb-7 font-thin dark:text-textDarkLightGray"><?= $option->text_rospetrebnadzor_column; ?></p>
        <?php endif; ?>
        <div class="text-xs font-light dark:text-textDarkLightGray flex flex-col mb-2">
            <span class="year">© 2014-<?= date('Y'); ?> <?= $option->COMPANY ?>.</span>
            <span>Все права защищены</span>
        </div>
        <div class="text-sm flex font-medium dark:text-textDarkLightGray mb-3">
            <p class="text-xs text-white">powered by
                <a href="https://enterego.ru" class="text-hover-red text-sm underline">ENTEREGO</a>
            </p>
        </div>
    </div>
    <!--     MODALS -->
    <div class="modal fade hidden" id="placeModal" tabindex="-1" role="dialog" aria-labelledby="placeModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header d-flex align-items-center">
                    <h4 class="modal-title font_weight_500" id="placeModalLabel"><?= GetMessage('CITY_CHOOSE_TITLE') ?>
                        <i class="fa fa-map-marker ml-2" aria-hidden="true"></i></h4>
                    <button type="button" class="close close_button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <nav class="navbar navbar-light">
                        <div class="cities js_city_chooser"
                             data-param-url="<?= urlencode($APPLICATION->GetCurUri()); ?>"
                             data-param-form_id="city_chooser">
                            <form id="formofcity" name="formofcity" method="POST" class="form-inline">
                                <div class="w-100" id="locations">
                                    <input id="city-search" class="form-control search" type="text" name="cityother"
                                           placeholder="
    <?= GetMessage('CITY_CHOOSE_PLACEHOLDER') ?>" value=""
                                           autocomplete="off" required>
                                    <div class="cities-list-wrap mb-3">
                                        <ul id="big-cities-list">
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$moskow] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$st_petersburg] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$nizhny_novgorod] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$yekaterinburg] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$permian] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$novosibirsk] ?></span>
                                            </li>
                                            <li>
                                                    <span class="city-item">
    <?= $runames[$kazan] ?></span>
                                            </li>
                                        </ul>
                                        <ul id="cities-list" class="list" style="display: none">
                                            <? //
                                            $i = 0;
                                            foreach ($runames as $name) { ?>
                                                <li>
                                                    <span class="city-item"><?= $name ?></span>
                                                </li>
                                                <? // $i++;
                                            } ?>
                                        </ul>
                                    </div>
                                </div>
                                <input id="choose-city-btn" name="submitcity" class="btn btnok btn-region" type="submit"
                                       value="Выбрать" disabled/>
                            </form>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <div style="display:none;">
        <div id="callbackModal" class="box-modal-white">
            <div class="box-modal_close arcticmodal-close"></div>

            <div class="flex_block_cols">
                <h4>Заказать обратный звонок</h4>
                <div class="block_text">Оставьте ваш номер и мы перезвоним вам в ближайшее рабочее время</div>
                <div class="block_text_sub">Отдел поддержки работает с 10:00 до 20:00, ежедневно</div>
                <form method="POST" class="callback_form">
                    <input type="hidden" name="recaptcha_token" value="">

                    <div class="form-group mb-3">
                        <input
                                type="text"
                                name="PHONE"
                                class="PHONE callback_PHONE"
                                placeholder="Ваш номер"
                                value="<?= $userData->getPhone() ?>"
                        >
                        <div class="er_CALLBACK_PHONE error_field js__error_field"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-control input_lk" style="height: auto">
                            <input name="confirm" type="checkbox" checked="checked">
                            <span class="custom__title">Подтверждаю свое согласие с
                                <a href="/about/politics/"
                                   target="_blank">положением об обработке персональных данных</a>
                            </span>
                        </label>
                        <div class="er_FORM_CONFIRM error_field js__error_field"></div>
                    </div>

                    <div class="submit-wrap">
                        <input type=submit class="btn btn-submit" value="Отправить"
                               onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                    </div>
                </form>
                <div class="result-callback" style="display:none;">Ваша заявка отправлена</div>
            </div>
        </div>
    </div>
    <?php if (!$USER->IsAuthorized() && !$_SESSION['age_access']) { ?>
        <div style="display:none;">
            <div id="trueModal" class="box-modal">
                <div class="box-modal_close arcticmodal-close" style="display:none;"></div>
                <div class="flex_block">
                    <div class="age-access-inner">
                        <div class="age-access__text">
                            <div class="age-access__text-part1">
                                <?= $option->ATTENT_TEXT ?>
                            </div>
                            <div class="age-access__text-part2">
                                <?= $option->ATTENT_TEXT2 ?>
                            </div>
                        </div>
                        <div class="age-access__buttons">
                            <a href="#" class="age-access__button age-access__yes link_red_button arcticmodal-close"
                               data-option="1" data-auth="false">Да, мне больше 18 лет</a>
                            <a href="<?= $option->ATTENT_NOT ?>" class="age-access__button link_red_button"
                               data-option="2" rel="nofollow">Нет</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function () {

                let ageAccess = sessionStorage.getItem("age_access");
                if (!ageAccess) {
                    $("#trueModal").arcticmodal(
                        {
                            closeOnOverlayClick: false,
                            afterClose: function (data, el) {
                                sessionStorage.setItem("age_access", "1");
                            }
                        });
                }
            });

            // age access
        </script>
    <? } ?>

    <?php require($_SERVER['DOCUMENT_ROOT'].'/local/templates/Oshisha/include/cookie.php')?>
</footer>
</div>
</div>
<!-- //bx-wrapper -->
<div class="overlay"></div>
<div class="page-scroller">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20"
         height="20" viewBox="0 0 20 20" class="page-scroller__ctrl">
        <path d="M2.582 13.891c-0.272 0.268-0.709 0.268-0.979 0s-0.271-0.701 0-0.969l7.908-7.83c0.27-0.268 0.707-0.268 0.979 0l7.908 7.83c0.27 0.268 0.27 0.701 0 0.969s-0.709 0.268-0.978 0l-7.42-7.141-7.418 7.141z"></path>
    </svg>
</div>
</body>
</html>