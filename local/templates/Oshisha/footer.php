<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

<footer class="box_footer">
    <div class="foot_container">
        <div class="d-flex flex-lg-row flex-md-row flex-column mb-3">
            <div class="col-11 col-lg-3 col-md-3 box_footer_with_boxes box_color order-lg-1">
                <div class="desktop">
                    <a class="bx-footer-logo" href="<?= SITE_DIR ?>">
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => SITE_DIR . "include/logo_footer.php"
                            ),
                            false
                        ); ?>
                    </a>
                    <div class="mb-3 d-flex align-items-center">
							<span class="text-white">

								<?php $APPLICATION->IncludeComponent(
                                    "bitrix:main.include",
                                    "", array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_DIR . "include/about_title.php"
                                ),
                                    false
                                ); ?>
								<span class="phone_footer phone_footer_first">
                                    <a href="tel:<?= $option->PHONE ?>"><?= $option->PHONE ?></a>
                                </span>
								<span class="phone_footer"><a href="tel:88006004424">8-800-600-44-24</a></span>
								<span class="work_time">Время работы <br>
                                    <div class="mt-2"> 10:00 - 20:00, ежедневно.</div>
                                </span>
								<span class="email_footer">
                                    <a href="mailto:info@oshisha.net">info@oshisha.net </a></span>
							</span>
                    </div>
                </div>
            </div>
            <div class="col-11 col-lg-3 col-md-3 mb-4 mb-lg-0 order-lg-2">
                <span class="text_footer_weight d-flex justify-content-between">
                    <span> Каталог</span>
                    <span class="icon_footer_menu">
                        <input type="checkbox" id="checkbox1" class="checkbox1 visuallyHidden">
                        <label for="checkbox1">
                            <div class="hamburger hamburger1">
                                <span class="bar bar2"></span>
                                <span class="bar bar3"></span>
                            </div>
                        </label>
                    </span>
                </span>
                <div data-id="checkbox1" data-hide="true" class="hides_box box_footer_js">
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
                </div>
            </div>
            <div class="col-11 col-lg-3 col-md-3 mb-4 mb-lg-0 order-lg-3">
                <span class="text_footer_weight desktop">
                   Поддержка
                </span>
                <span class="mobile">
                    <div class="d-flex justify-content-between flex-row text_footer_weight">
                        <span>Покупателям </span>
                         <span class="icon_footer_menu">
                            <input type="checkbox" checked id="checkbox3" class="checkbox3 visuallyHidden">
                            <label for="checkbox3">
                                <div class="hamburger hamburger3">
                                    <span class="bar bar5"></span>
                                    <span class="bar bar6"></span>
                                </div>
                            </label>
                        </span>
                    </div>
                </span>
                <nav class="li_link_footer box_with_link_footer hides_box box_footer_js" data-hide="false"
                     data-id="checkbox3">
                    <span class="li_link_footer">
                         <a href="/about/contacts/" class="text_link_footer">Контакты</a>
                    </span>

                    <span class="li_link_footer ">
                         <a href="/about/feedback/" class="text_link_footer">Обратная связь</a>
                    </span>
                    <span class="li_link_footer ">
                         <a href="javascript:void(0)" class="callback js__callback text_link_footer">Обратный звонок</a>
                    </span>
                    <?php if ($USER->IsAuthorized()) { ?>
                        <span class="li_link_footer">
                         <a href="/about/FAQ/" class="text_link_footer li_link_footer">FAQ</a>
                    </span>
                    <?php } ?>
                    <span class="li_link_footer ">
                         <a href="/about/users_rules/" class="text_link_footer">Пользовательское соглашение</a>
                    </span>
                    <span class="li_link_footer ">
                         <a href="/about/politics/" class="text_link_footer">Политика конфиденциальности</a>
                    </span>
                </nav>
            </div>
            <div class="col-lg-3 col-md-3 col-11 mb-4 mb-lg-0 order-lg-4">
                <div class="d-flex justify-content-between flex-row">
                <span class="text_footer_weight">
                О компании
                </span>
                    <span class="icon_footer_menu">
                    <input type="checkbox" id="checkbox4" class="checkbox4 visuallyHidden">
                    <label for="checkbox4">
                        <div class="hamburger hamburger4">
                            <span class="bar bar7"></span>
                            <span class="bar bar8"></span>
                        </div>
                    </label>
                </span>
                </div>
                <div data-id="checkbox4" class="hides_box box_footer_js li_link_footer box_with_link_footer"
                     data-hide="true">
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:menu",
                        "bottom_menu",
                        array(
                            "ROOT_MENU_TYPE" => "bottom",
                            "MAX_LEVEL" => "1",
                            "MENU_CACHE_TYPE" => "A",
                            "CACHE_SELECTED_ITEMS" => "N",
                            "MENU_CACHE_TIME" => "36000000",
                            "MENU_CACHE_USE_GROUPS" => "Y",
                            "MENU_CACHE_GET_VARS" => array(),
                        ),
                        false
                    ); ?>
                    <li class="nav-item li_link_footer">
                        <?php if ($USER->IsAuthorized()) { ?>
                            <a href="<?= $option->price_list_link; ?>" class="text_link_footer ">Прайс-лист</a>
                        <?php } else { ?> <a href="/login/" class="text_link_footer ">Прайс-лист</a><?php } ?>
                    </li>
                </div>
                <span class="text_footer_weight desktop social_block">
                  Социальные сети
                </span>
                <div class="box_with_icons_new">
                    <div class="social-line-1">
                        <a href="<?= $option->TG; ?>" target="_blank">
                            <img class="tg" src="<?= SITE_TEMPLATE_PATH ?>/images/tg.svg">
                        </a>
                        <a href="https://api.whatsapp.com/send?phone=<?= $option->PHONE_WTS; ?>" target="_blank">
                            <img class="ws" src="<?= SITE_TEMPLATE_PATH ?>/images/ws.svg">
                        </a>
                        <a href="<?= $option->VK_LINK; ?>" target="_blank">
                            <img class="vk" src="<?= SITE_TEMPLATE_PATH ?>/images/vk.svg">
                        </a>
                        <a href="<?= $option->DZEN; ?>" target="_blank">
                            <img class="dzen" src="<?= SITE_TEMPLATE_PATH ?>/images/dzen.svg">
                        </a>
                    </div>
                </div>
            </div>
            <div class="mobile width_100 p-0 mb-4">
                <p class="m-3 mail_footer col-11"><a href="tel:<?= $option->PHONE ?>"><?= $option->PHONE ?></a>
                </p>
                <div class="box_with_contact pl-3">
                    <span><i class="fa fa-circle header_icon" aria-hidden="true"></i></span>
                    <span> <i class="fa fa-circle header_icon" aria-hidden="true"></i></span>
                    <a href="javascript:void(0)" class="link_menu_top " data-toggle="modal"
                       data-target="#placeModal"><span class="mail_footer not_weight">Москва, Россия</span>
                    </a>
                </div>
            </div>
        </div>
        <?php if (!empty($option->text_rospetrebnadzor_row)) { ?>
            <div class="text_footer_mini d-flex flex-column p-3">
                <p class="font-12 color-white"><?= $option->text_rospetrebnadzor_row; ?></p>
                <p class="font-12 color-white"><?= $option->text_rospetrebnadzor_column; ?></p>
            </div>
        <?php } ?>
        <div class="text_footer_mini d-flex column_section p-3">
            <span class="mr-2">© 2014-<?= date('Y'); ?> <?=  $option->COMPANY ?>.</span><span>Все права защищены</span>
        </div>
    </div>
    <!--FOOTER END-->
    <!-- MODALS -->
    <div class="modal fade" id="placeModal" tabindex="-1" role="dialog" aria-labelledby="placeModalLabel"
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
                                           placeholder="<?= GetMessage('CITY_CHOOSE_PLACEHOLDER') ?>" value=""
                                           autocomplete="off" required>
                                    <div class="cities-list-wrap mb-3">
                                        <ul id="big-cities-list">
                                            <li>
                                                <span class="city-item"><?= $runames[$moskow] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$st_petersburg] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$nizhny_novgorod] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$yekaterinburg] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$permian] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$novosibirsk] ?></span>
                                            </li>
                                            <li>
                                                <span class="city-item"><?= $runames[$kazan] ?></span>
                                            </li>
                                        </ul>
                                        <ul id="cities-list" class="list" style="display: none">
                                            <?
                                            $i = 0;
                                            foreach ($runames as $name) { ?>
                                                <li>
                                                    <span class="city-item"><?= $name ?></span>
                                                </li>
                                                <? $i++;
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
                    <input type="hidden" name="recaptcha_response" id="recaptchaResponse">

                    <div class="form-group mb-3">
                        <input type="text" name="PHONE" class="PHONE callback_PHONE" placeholder="Ваш номер">
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
                               data-option="1" data-auth="false">Да, мне
                                больше 18 лет</a>
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
                                console.log('save');
                                sessionStorage.setItem("age_access", "1");

                            }
                        });
                }
            });

            // age access


        </script>
    <? } ?>
    <?php if (!$USER->IsAuthorized() && !$_SESSION['age_access']) { ?>

    <? } ?>
    <script>
        $(document).ready(function () {
            $(document).on('click', '.close_header_box', function () {
                $('.overlay').hide();
            });
        });
    </script>
</footer>
</div>
</div><!-- //bx-wrapper -->
<div class="overlay"></div>
<div class="page-scroller">
    <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="20"
         height="20" viewBox="0 0 20 20" class="page-scroller__ctrl">
        <path d="M2.582 13.891c-0.272 0.268-0.709 0.268-0.979 0s-0.271-0.701 0-0.969l7.908-7.83c0.27-0.268 0.707-0.268 0.979 0l7.908 7.83c0.27 0.268 0.27 0.701 0 0.969s-0.709 0.268-0.978 0l-7.42-7.141-7.418 7.141z"></path>
    </svg>
</div>
</body>
</html>
