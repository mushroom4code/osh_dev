<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;
//use Osh\Delivery\Options\Config;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "доставка, условия, стоимость, самовывоз");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Доставка и оплата товаров для кальяна в компании Ошиша");

//CModule::IncludeModule('osh.shipping');
//$ymapsApikey = Config::getYMapsKey();
//$daDataToken = Config::getDaDataToken();
Asset::getInstance()->addJs("/bitrix/js/osh.shipping/pickup.js");
Asset::getInstance()->addJs("/bitrix/js/osh.shipping/async.js");
Asset::getInstance()->addJs("/bitrix/js/osh.shipping/jquery.suggestions.min.js");
Asset::getInstance()->addCss("/bitrix/css/osh.shipping/suggestions.css");
Asset::getInstance()->addJs('https://api-maps.yandex.ru/2.1.71/?lang=ru_RU&apikey=' . ($ymapsApikey ? '&apikey=' . $ymapsApikey : ''), true);
/*
$oshShippingParams = json_encode(array(
    'key' => $ymapsApikey,
    'cost' => Config::getCost(),
));*/

?>    <link rel="preconnect" href="//api-maps.yandex.ru">
    <link rel="dns-prefetch" href="//api-maps.yandex.ru">
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <h1>Условия доставки и способы оплаты</h1>
        <div class="d-flex flex-column" id="delivery_method">
<h5 style="margin:20px 0 15px">Способы оплаты заказов</h5>
<p class="delivery_description" style="margin:0 0 40px"><span class="red_text">Наличный расчет / картой</span> – оплачиваете курьеру при получении заказа, актуально только для Москвы и Московской области.<br/><br/>
<span class="red_text">Переводы по системе быстрых платежей</span> – вы можете оплатить свой заказ через любое мобильное приложение любого банка. Реквизиты предоставляются после подтверждения заказа менеджером.<br/><br/>
</p>



<div class="box_msk mb-5 d-flex">
                <div class="width_50">
                    <h5 style="margin-bottom:15px">Cамовывоз со склада в Москве</h5>
<p class="mb-4 delivery_description">Самовывоз со склада доступен с понедельника по субботу (<a href="/about/contacts/" style="color:#DD0602;">схема и часы работы</a>).</p>
                    <div class="flex-column d-flex">
                       <div class="d-flex row_section mb-3">
                            <span class="d-flex align-items-center mr-3 ">
                                <i class="fa fa-circle header_icon" aria-hidden="true"></i>
                            </span>
                            <div class="delivery_description">Закажи на сайте, <span class="red_text">забери самовывозом</span> и получи скидку 2%.<br/>Скидка за самовывоз действует для любых заказов на любую сумму.</span><br/></div>
                        </div>
                    </div>

                </div>
                <div class="width_50">
                    <div class="d-flex justify-content-between box_picture_info mb-3">
                        <span class="box_delivery_door"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">Бесплатный самовывоз пешком или на машине</span>
                    </div>
                </div>
            </div>


            <div class="box_msk mb-5 d-flex">
                <div class="width_50">
                    <h5 style="margin-bottom:15px">Доставка заказов по Москве и МО</h5>
<p class="mb-4 delivery_description">
Доставка в тот же день осуществляется с понедельника по пятницу, доставка на следующий день с понедельника по субботу. В воскресенье доставка <span class="red_text">не работает.</span>
</p>
                    <div class="flex-column d-flex">

                        <div class="d-flex row_section mb-3">
                            <span class="d-flex align-items-center mr-3 ">
                                <i class="fa fa-circle header_icon" aria-hidden="true"></i>
                            </span>
                            <div class="delivery_description">Сделай <span class="red_text">заказ до 15:00 (пнд-птн)</span>, доставим до 21:00 в этот же день<br /><span class="red_text">стоимость доставки 299 руб.</span><br/></div>
                        </div>
                        <div class="d-flex row_section mb-3">
                            <span class="d-flex align-items-center mr-3 ">
                                <i class="fa fa-circle header_icon" aria-hidden="true"></i>
                            </span>
                            <div class="delivery_description">Сделай <span class="red_text">заказ до 18:00</span>, доставим с 21 до 02 в этот же день (кроме сб и вс)<br />доставка от 299 руб, <span class="red_text">бесплатно для заказов от 4000 руб</span>.</div>
                        </div>
                        <div class="d-flex row_section mb-3">
                            <span class="d-flex align-items-center mr-3 ">
                                <i class="fa fa-circle header_icon" aria-hidden="true"></i>
                            </span>
                            <div class="delivery_description">Сделай <span class="red_text">заказ до 19:00</span>, доставим на следующий день с 11 до 22 (кроме вс)<br />доставка от 299 руб, <span class="red_text">бесплатно для заказов от 4000 руб</span>.</div>
                        </div>
                        <div class="d-flex row_section mb-3">
                            <span class="d-flex align-items-center mr-3 ">
                                <i class="fa fa-circle header_icon" aria-hidden="true"></i>
                            </span>
                            <div class="delivery_description">Доставка через постоматы Pickpoint. <span class="red_text">Самая недорогая доставка</span>.<br/>Стоимость рассчитается автоматически в корзине.</div>
                        </div>
                    </div>
<p class=" mb-4 delivery_description">А еще можем доставить через СДЭК и постоматы 5post (Пятерочка). Расчет стоимости доставки и выбор постомат/ПВЗ доступны при оформлении заказа.</p>
                </div>
                <div class="width_50">
                    <div class="d-flex justify-content-between box_picture_info mb-3">
                        <span class="box_delivery_door"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">Курьером до двери - точно в срок!</span>
                    </div>
                    <div class="d-flex justify-content-between box_picture_info  mb-3">
                        <span class="box_delivery_car"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">Бесконтактная доставка - мы заботимся о вас!</span>
                    </div>
                    <div class="d-flex justify-content-between box_picture_info  mb-3">
                        <span class="box_delivery_car"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">Собственная курьерская служба - оригинальный товар напрямую со склада!</span>
                    </div>
                    <div class="d-flex justify-content-between box_picture_info  mb-3">
                        <span class="box_delivery_box"></span>
                        <span class="ml-2 delivery-trigger   d-flex align-items-center">Доставка по Москве и МО до пункта выдачи или постомата CDEK, 5post, Pickpoint</span>
                    </div>
                </div>
            </div>
            <div class="box_reg mb-5 d-flex">
                <div class="width_50">
                    <h5 style="margin-bottom:15px">Доставка заказов по России</h5>
<p class="delivery_description">Доставка по России осуществляется всеми удобными для Вас транспортными компаниями, а также через пункты выдачи или постоматы 5post, Pickpoint, CDEK.</p>
<p class="mb-4 delivery_description">При подтверждении заказа согласуем с Вами удобную ТК или способ доставки<br/>
<span class="red_text ">ПЭК, Деловые линии, Байкал Сервис, Кит, Энергия, CDEK и другие.</span><br/><br/>Стоимость доставки рассчитывается по тарифам ТК или курьерской службы при оформлении заказа на сайте или менеджером при подтверждении заказа.
</p>
                </div>
                <div class="width_50">
                    <div class="d-flex justify-content-between box_picture_info mb-3">
                        <span class="box_delivery_door"></span>
                        <span class="ml-2 delivery-trigger   d-flex align-items-center">
                            Курьером до двери от службы доставки</span>
                    </div>
                    <div class="d-flex justify-content-between box_picture_info  mb-3">
                        <span class="box_delivery_car"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">
                            Доставка до пункта выдачи транспортной компании</span>
                    </div>
                    <div class="d-flex justify-content-between box_picture_info  mb-3">
                        <span class="box_delivery_box"></span>
                        <span class="ml-2 delivery-trigger  d-flex align-items-center">Доставка до постомата или пункта выдачи</span>
                    </div>
                </div>
            </div>
        </div>
 
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");