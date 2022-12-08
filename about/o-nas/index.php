<?php
define('STATIC_P', 'static_wrap');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("О нас");
?>    <div id="o_nas" class="mb-5 static">
        <h1>О нас</h1>
        <div class="box_with_banner_dop mb-5">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:advertising.banner",
                "oshisha_banners",
                array(
                    "BS_ARROW_NAV" => "N",
                    "BS_BULLET_NAV" => "N",
                    "BS_CYCLING" => "N",
                    "BS_EFFECT" => "fade",
                    "BS_HIDE_FOR_PHONES" => "N",
                    "BS_HIDE_FOR_TABLETS" => "N",
                    "BS_KEYBOARD" => "N",
                    "BS_WRAP" => "N",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "N",
                    "DEFAULT_TEMPLATE" => "-",
                    "NOINDEX" => "N",
                    "QUANTITY" => "1",
                    "TYPE" => "O_NAS",
                    "COMPONENT_TEMPLATE" => "oshisha_banners"
                ),
                false
            ); ?>
        </div>
<p class="mb-5 text_lineHeight"><b>Компания OSHISHA </b> - официальный дистрибьютор табачной и бестабачной продукции, оборудования, аксессуаров и комплектующих для кальянов.</p>
<p class="mb-5 text_lineHeight"><b>Миссия </b> - Мы инновационная компания, вдохновленная на постоянное совершенствование и развитие бизнеса. Мы стремимся к предоставлению исключительного качества сервиса и услуг для наших партнёров и клиентов.</p>

<h5>Что ты можешь вместе с Oshisha</h5>
        <div class=" d-flex row_section mb-3 row_section_gray">
            <div class="box_text_business mb-4 ">
                <div class="d-flex row_section   box_row">
                    <b class="text_lineHeight text_font_19">01</b>
                </div>
                <div class="d-flex flex-column justify-content-between align-content-between">
                    <span class=" text_advantage">Выбрать то, что нужно тебе, среди 15 000 товаров</span>
                </div>
            </div>
            <div class="box_text_business mb-4 ">
                <div class="d-flex row_section   box_row">
                    <b class="text_lineHeight text_font_19">02</b>
                </div>
                <div class="d-flex flex-column justify-content-between align-content-between">
                    <span class=" text_advantage">Получить доступ к скидкам, акциям и постоянным бонусам</span>
                </div>
            </div>
            <div class="box_text_business mb-4 ">
                <div class="d-flex row_section  box_row">
                    <b class="text_lineHeight text_font_19">03</b>
                </div>
                <div class="d-flex flex-column justify-content-between align-content-between">
                        <span class="text_advantage">Cобрать полный комплект для классного
                                чилла на одном сайте</span>
                </div>
            </div>
            <div class="box_text_business mb-4 ">
                <div class="d-flex row_section  box_row">
                    <b class="text_lineHeight text_font_19">04</b>
                </div>
                <div class="d-flex flex-column justify-content-between align-content-between">
                    <span class="text_advantage">Узнавать первым о всех топовых новинках</span>
                </div>
            </div>
            <div class="box_text_business mb-4 ">
                <div class="d-flex row_section  box_row">
                    <b class="text_lineHeight text_font_19">05</b>
                </div>
                <div class="d-flex flex-column justify-content-between align-content-between">
                        <span class="text_advantage">Купить продукцию, получившую премии John Calliano,
                                Hookah Club Show</span>
                </div>
            </div>
            <div class="box_text_business mb-4 white">

            </div>			
        </div>
        <h5>Что еще мы предлагаем</h5>
        <div class="d-flex row  row_section justify-content-between row-icon-onas mb-5">
            <div class="d-flex col-12 col-lg-3 col-md-3  mb-3  text-center column_section column_section_onas box_background">
                <span class="box_block"></span>
                <span class="text_delivery_icon color_black mt-3">
                     Отсутствие минимальной суммы заказа
                </span>
            </div>
            <div class="d-flex col-12 col-lg-3 col-md-3 column_section column_section_onas mb-3 text-center box_background">
                <span class="box_present"></span>
                <span class="text_delivery_icon color_black mt-3">
                            Наличие только оригинальных товаров от производителей!
                </span>
            </div>
            <div class="d-flex col-12 col-lg-3 col-md-3 column_section column_section_onas mb-3  text-center box_background">
                <span class="box_help"></span>
                <span class="text_delivery_icon color_black  mt-3">
                        Помощь и поддержку в открытии и развитии   <b class="red_text">своего бизнеса!</b>
                </span>
            </div>
<!--            <div class="d-flex col-12 col-lg-3 col-md-3 column_section column_section_onas mb-3  text-center box_background">
                <span class="box_house"></span>
                <span class="text_delivery_icon color_black mt-3">
                        Возможность покупать также в наших розничных магазинах - <b class="red_text"> OSHISHA Store!</b>
                </span>
            </div>-->
        </div>
		
		       <div class="mb-5">
            <form class="form_company form-form " id="support">
			<div class="form-form-wrap">
<!--                <input type="hidden" name="recaptcha_token" value="">-->
<!--                --><?php //echo bitrix_sessid_post(); ?>
<!--                <input type="hidden" name="captcha_sid" value="--><?//= $arResult["CAPTCHA_CODE"] ?><!--"/>-->
<!--                <div class="form-group">-->
<!--                    --><?//= GetMessage("CAPTCHA_REGF_PROMT") ?>
<!--                    <div class="form-group">-->
<!--                        <div class="bx-captcha"><img-->
<!--                                    src="/bitrix/tools/captcha.php?captcha_sid=--><?//= $arResult["CAPTCHA_CODE"] ?><!--"-->
<!--                                    width="180" height="40" alt="CAPTCHA"/></div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <input type="text" class="form-control" name="captcha_word" maxlength="50"-->
<!--                               value="" autocomplete="off"/>-->
<!--                    </div>-->
<!--                </div>-->
<input type="hidden" name="recaptcha_token" value="">
                <div class="form-group mb-3">
                    <label class="label_company">Есть идеи? Предложите нам</label>
                </div>
                <div class="form-group mb-3">
                    <input type="text" class="form-control input_lk" id="Name" name="NAME" placeholder="Пожалуйста, представьтесь*">
					<div class="er_FORM_NAME error_field"></div>
                </div>
                <div class="form-group mb-3">
                    <input type="text" data-name="PHONE-FORM" name="PHONE" class="form-control input_lk" id="phoneNumber" placeholder="Телефон для связи*">
					<div class="er_FORM_PHONE error_field"></div>
				</div>
                <div class="form-group mb-3">
                    <input type="text" data-name="EMAIL" name="EMAIL" class="form-control input_lk" id="phoneNumber" placeholder="E-mail">
                </div>				
                <div class="form-group mb-5">
                    <textarea class="form-control input_lk" name="MESSAGE" id="text" placeholder="Сообщение*"></textarea>
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
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");