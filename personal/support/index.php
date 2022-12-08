<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Поддержка");
?>
<div class="mobile_lk mb-5 static">
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
        <div class="hides" id="about_contacts">
            <h5>Поддержка</h5>
            <div class="row">
                <div class="form_company box_chat d-flex justify-content-end p-0">
                    <div class="column ">
                        <div class="p-4 overflow-auto">
                            <div class="row align-items-end p-4 justify-content-between mb-1">
                                <div class="box_with_message message_who">Здравствуйте, чем мы можем вам помочь?</div>
                            </div>
                            <div class="row flex-row-reverse p-4 align-items-end justify-content-between mb-1">
                                <div class="box_with_message message_me">Как начать покупать как представитель
                                    компании?
                                </div>
                            </div>
                            <div class="row align-items-end p-4 justify-content-between mb-1">
                                <div class="box_with_message message_who">При отмене всего заказа мы вернём все деньги и
                                    баллы.
                                    Если
                                    вы отказались от части заказа и стоимость оставшихся товаров ниже необходимой для
                                    бесплатной доставки, мы вернём деньги за отменённые товары, но вычтем из этой суммы
                                    стоимость доставки.

                                    При возврате товаров после получения мы вернём все деньги и баллы, если возврат был
                                    правильно оформлен. Деньги за каждый из товаров и доставку возвращаются отдельно.

                                </div>
                            </div>
                        </div>
                        <div class="box_with_input">
                            <div class="photo_chat"></div>
                            <input type="text" class="form-control input_chat" placeholder="Написать сообщение"/>
                        </div>
                    </div>
                </div>
                <div class="cart_box_black max_height">
                    <span class="circle_white"></span>
                    <div><p class="lk_light_mini">
                            Если у вас есть вопросы, предложения или отзывы о нашей работе, то просим написать их
                            максимально детально, чтобы оператор мог быстрее разобраться и ответить</div>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
