<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");

use Bitrix\Conversion\Internals\MobileDetect;

$mobile = new MobileDetect();
?>
    <link rel="preconnect" href="//api-maps.yandex.ru">
    <link rel="dns-prefetch" href="//api-maps.yandex.ru">
    <div class="mb-5 static" id="box_contacts">
        <h1>Контакты</h1>
        <div class="row mb-5 ">
            <div class="column-contacts">
                <div class="col-12 d-flex flex-column justify-content-between three-column ">
                    <div class="box_text_business mb-3">
                        <h5 class="mb-3">Режим работы</h5>
                        <div class="d-flex flex-column justify-content-between align-content-between">
                <span class="mb-3">
                   Мы работаем ежедневно<br>
                   с 10:00 до 20:00.
               </span>
                            <span class="mb-3">  Самовывоз со склада доступен<br>
                с 11:00 до 19:00.
                    </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex flex-column justify-content-between three-column">
                    <div class="box_text_business mb-3">
                        <h5 class="mb-3">Телефоны</h5>
                        <div class="d-flex flex-column justify-content-between align-content-between">
                    <span class="mb-3">
                        Общий: <br>
                        <span class="red_text mb-3"><a href="tel:+74993506201">+7 (499) 350-62-01</a></span>
                    </span>
                            <span class="mb-3">
                        Для связи с менеджером: <br>
                        <span class="red_text"><a href="tel:+79268895090">+7 (926) 889-50-90</a></b>
                    </span>
                        </div>
                    </div>
                </div>
                <div class="col-12 d-flex flex-column justify-content-between three-column">
                    <div class="box_text_business mb-3">
                        <h5 class="mb-3">Email</h5>
                        <div class="d-flex flex-column justify-content-between align-content-between">
                    <span class="mb-3">
                        По вопросам сотрудничества и всего остального:<br>
                        <span class="red_text mb-3"><a href="mailto:info@oshisha.net">info@oshisha.net</a></span>
                    </span>
                            <!--<span class="mb-3">
                                Для всего остального: <br>
                                <span class="red_text"><a href="mailto:info@oshisha.net">info@oshisha.net</a></span>
                            </span>-->
                        </div>
                    </div>
                </div>
            </div>

            <h5 class="mb-4 mt-5"><b>Адрес склада</b></h5>
            <div class="row_section d-flex mb-3">
                <div class="d-flex row_section mb-3 width_50 mr-3 box_row">
                            <span class="d-flex align-items-center mr-3 flex-row">
                               <img src="/local/assets/images/icon_location.svg" class="icon_location">
                            </span>
                    <b class="text_delivery text_delivery_next">г. Москва, ул. Краснобогатырская, 2с2 <span
                                class="red_text">(для пеших курьеров)</span><br/>(вход со стороны Краснобогатырской)</b>
                </div>
                <div class="d-flex row_section width_50 mb-3 box_row">
                            <span class="d-flex align-items-center mr-3 flex-row">
                                <img src="/local/assets/images/icon_location.svg" class="icon_location">
                            </span>
                    <b class="text_delivery text_delivery_next">г. Москва, ул. Краснобогатырская, 2с64 <span
                                class="red_text"> (для курьеров на машине)</span><br/>(въезд со стороны проспекта
                        Ветеранов) </b>
                </div>
            </div>
            <?php if (!$mobile->isMobile()) { ?>
                <section class="box_map mb-5">
                    <script type="text/javascript" charset="utf-8"
                            src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A149de29607a13c15d0cddf04f2230c7b147fd3f62f69e7b9461ec5ac48550769&amp;width=100%&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
                </section>
            <?php } else { ?>
                <section class="box_map_mobile mb-5">
                    <script type="text/javascript" charset="utf-8"
                            src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A149de29607a13c15d0cddf04f2230c7b147fd3f62f69e7b9461ec5ac48550769&amp;width=320&amp;height=320&amp;lang=ru_RU&amp;scroll=false"></script>
                </section>
            <?php } ?>
            <h5 class="mb-3" id="form"></h5>
            <div class="mb-5">
                <form class="form_company form-form " id="support">
                    <div class="form-form-wrap">
                        <input type="hidden" name="recaptcha_token" value="">
                        <?php echo bitrix_sessid_post(); ?>
                        <input type="hidden" name="captcha_sid" value="<?= $arResult["CAPTCHA_CODE"] ?>"/>
                        <div class="form-group">
                            <?= GetMessage("CAPTCHA_REGF_PROMT") ?>
                            <div class="form-group">
                                <div class="bx-captcha">
                                    <img src="/bitrix/tools/captcha.php?captcha_sid=<?= $arResult["CAPTCHA_CODE"] ?>"
                                         width="180" height="40" alt="CAPTCHA"/></div>
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="captcha_word" maxlength="50"
                                       value="" autocomplete="off"/>
                            </div>
                        </div>
                        <input type="hidden" name="recaptcha_token" value="">
                        <div class="form-group mb-3">
                            <label class="label_company">Вопросы, замечания, жалобы? Свяжитесь с нами:</label>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" class="form-control input_lk" id="Name" name="NAME"
                                   placeholder="Пожалуйста, представьтесь*">
                            <div class="er_FORM_NAME error_field"></div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" data-name="PHONE-FORM" name="PHONE" class="form-control input_lk"
                                   id="phoneNumber" placeholder="Мобильный телефон, чтобы связаться с вами*">
                            <div class="er_FORM_PHONE error_field"></div>
                        </div>
                        <div class="form-group mb-3">
                            <input type="text" data-name="EMAIL" name="EMAIL" class="form-control input_lk"
                                   id="phoneNumber" placeholder="E-mail если хотите получить ответ на почту">
                        </div>
                        <div class="form-group mb-5">
                            <textarea class="form-control input_lk" name="MESSAGE" id="text"
                                      placeholder="Сообщение*"></textarea>
                            <div class="er_FORM_MESSAGE error_field"></div>
                        </div>
                        <div class="form-group mb-2">
                            <div class="col-sm-10">
                                <input class="btn link_menu_catalog get_code_button"
                                       type="submit"
                                       value="Отправить"
                                       onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                            </div>
                            <div class="error_form error_field"></div>
                        </div>
                    </div>
                    <div class="form_block_ok">
                        Сообщение отправлено.<br>
                        Мы свяжемся с вами в самое ближайшее время!
                    </div>
                </form>

            </div>


            <h5 class="mb-3">Социальные сети и мессенджеры</h5>
            <div class="width_50 mb-5">
                <div class="box_with_icons">
                    <a href="https://www.instagram.com/oshisha.cc/" class="mr-5 fa fa-instagram icons_theme"></a>
                    <a href="https://t.me/oshishanet" class="mr-5 telegram_icons">
                    </a>
                    <a href="https://vk.com/oshishacc" class="mr-5 vk_icons">
                    </a>
                    <a href="https://api.whatsapp.com/send?phone=79031182939" class="mr-4"> <i
                                class="fa fa-whatsapp icons_theme" aria-hidden="true"></i></a>

                    <a href="https://dzen.ru/id/6125150216123a2f95667201">
                        <img class="dzen_contacts" src="<?= SITE_TEMPLATE_PATH ?>/images/dzen.svg">
                    </a>
                </div>
            </div>
            <h5 class="mb-3">Реквизиты</h5>
            <p>ООО "СМАК-СУЛТАНА", ИНН 771544140</p>
        </div>

    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php") ?>