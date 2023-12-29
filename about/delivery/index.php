<?php


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "доставка, условия, стоимость, самовывоз");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Доставка и оплата товаров для кальяна в компании Ошиша");

global $USER;
if ($USER->IsAuthorized()) {

    ?>
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <h2 class="md:text-3xl text-2xl my-5 font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
            Условия доставки и способы оплаты
        </h2>
        <p class="mb-10 text-md font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
            Доставка кальянов, табачной и никотиносодержащей продукции для физических лиц - не осуществляется,<br>
            в отношении иной продукции применяются следующие условия о доставке:
        </p>
        <div class="d-flex flex-column mt-3" id="delivery_method">
            <h5 class="mb-5 md:text-2xl text-xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                Способы оплаты заказов
            </h5>
            <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-8">
                <span class="text-hover-red font-medium dark:text-white dark:text-white">Наличный расчет / картой</span>
                – оплачиваете курьеру при
                получении заказа,
                актуально только для Москвы и Московской области.<br>
                <br>
            </p>
            <div>
                <h5 class="md:text-2xl text-xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                    Cамовывоз со склада в Москве
                </h5>
                <div class="flex md:flex-row flex-col items-center mb-10">
                    <div class="md:w-3/5 w-full">
                        <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-5">
                            Самовывоз со склада доступен с понедельника по субботу (
                            <a href="/about/contacts/" class="text-hover-red underline font-medium">схема и часы
                                работы</a>).
                        </p>
                    </div>
                    <div class="md:w-2/5 w-full">
                        <div class="flex items-center bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_door.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">
                            Бесплатный самовывоз пешком или на машине
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h5 class="mb-5 md:text-2xl text-xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                    Доставка заказов по Москве и МО
                </h5>
                <div class="md:flex-row flex-col mb-10 flex">
                    <div class="md:w-3/5 w-full">
                        <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-5">
                            Доставка в тот же день осуществляется с понедельника по пятницу, доставка на следующий день
                            с
                            понедельника по субботу. В воскресенье доставка <span
                                    class="text-hover-red font-medium dark:text-white">не работает.</span>
                        </p>
                        <div class="flex-col flex mb-3">
                            <div class="flex flex-row mb-4">
                            <span class="flex items-center mr-3">
                                <div class="bg-hover-red p-1 w-2 h-2 mr-0.5 rounded-full"></div>
                            </span>
                                <div class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-xs">
                                    Сделай <span
                                            class="text-hover-red font-medium dark:text-white">заказ до 17:00</span>,
                                    доставим с 21
                                    до
                                    02 в этот же день
                                    (кроме сб и вс)<br>
                                    доставка от 299 руб,
                                    <span class="text-hover-red font-medium dark:text-white">бесплатно для заказов от 4000 руб</span>.
                                </div>
                            </div>
                            <div class="flex flex-row mb-4">
                           <span class="flex items-center mr-3">
                                <div class="bg-hover-red p-1 w-2 h-2 mr-0.5 rounded-full"></div>
                            </span>
                                <div class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-xs">
                                    Сделай <span
                                            class="text-hover-red font-medium dark:text-white">заказ до 19:00</span>,
                                    доставим на
                                    следующий день с 11 до
                                    22 (кроме вс)<br>
                                    доставка от 299 руб,
                                    <span class="text-hover-red font-medium dark:text-white">бесплатно для заказов от 4000 руб</span>.
                                </div>
                            </div>
                            <div class="flex flex-row mb-4">
                           <span class="flex items-center mr-3">
                                <div class="bg-hover-red p-1 w-2 h-2 mr-0.5 rounded-full"></div>
                            </span>
                                <div class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-xs">
                                    Доставка через постаматы 5post.
                                    <span class="text-hover-red font-medium dark:text-white">Самая недорогая доставка</span>
                                    .<br>
                                    Стоимость рассчитается автоматически в корзине.
                                </div>
                            </div>
                        </div>
                        <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-5">
                            А еще можем доставить через СДЭК и постаматы 5post (Пятерочка). Расчет стоимости доставки и
                            выбор постамат/ПВЗ доступны при оформлении заказа.
                        </p>
                    </div>
                    <div class="md:w-2/5 w-full">
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_door.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">Курьером до двери - точно в срок!</span>
                        </div>
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_car.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">
                            Бесконтактная доставка - мы заботимся о вас!
                        </span>
                        </div>
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_car.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">
                            Собственная курьерская служба - оригинальный товар напрямую со склада!
                        </span>
                        </div>
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_box.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">
                            Доставка по Москве и МО до пункта выдачи или постамата CDEK, 5post.
                        </span>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <h5 class="mb-5 md:text-2xl text-xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                    Доставка заказов по России
                </h5>
                <div class="md:flex-row flex-col mb-10 flex">
                    <div class="md:w-3/5 w-full">
                        <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-5">
                            Доставка по России осуществляется всеми удобными для Вас транспортными компаниями, а также
                            через
                            пункты выдачи или постаматы 5post, Почтоматы, CDEK.
                        </p>
                        <p class="text-textLight dark:text-textDarkLightGray font-normal dark:font-light text-sm mb-5">
                            При подтверждении заказа согласуем с Вами удобную ТК или способ доставки<br>
                            <span class="text-hover-red font-medium dark:text-white dark:text-white">
                                ПЭК, Деловые линии, Байкал Сервис, Кит, Энергия, CDEK и другие.
                            </span>
                            <br><br>
                            Стоимость доставки рассчитывается по тарифам ТК или курьерской службы при оформлении заказа
                            на
                            сайте или менеджером при подтверждении заказа.
                        </p>
                    </div>
                    <div class="md:w-2/5 w-full">
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_door.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">Курьером до двери от службы доставки</span>
                        </div>
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_car.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">
                                Доставка до пункта выдачи транспортной компании
                            </span>
                        </div>
                        <div class="flex items-center mb-3 bg-textDark rounded-lg dark:bg-darkBox px-5 py-4">
                            <img src="/local/templates/Oshisha/images/box_delivery_box.png" width="20"
                                 class="mr-4 invert-0 dark:invert"
                                 height="20">
                            <span class="ml-2 flex items-center text-xs">Доставка до постамата или пункта выдачи</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <p class="mb-2 mt-5 font-20 font-weight-bolder text-center">
            Для ознакомления с информацией необходимо
            <a href="javascript:void(0)" class="link_header_box underline text-hover-red">авторизоваться.</a>
        </p>
    </div>
    <br><?php }
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>