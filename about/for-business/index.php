<?php

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Для бизнеса");
Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js"); ?>
<div id="content_box_business " class="static">
    <h1 class="text-3xl my-5 font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
        Для бизнеса
    </h1>
    <div class="flex flex-col">
        <div class="mb-6">
            <h4 class="mb-5 text-xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray ">
                Компания OSHISHA - официальный дистрибьютор табачной<br> и бестабачной
                продукции, оборудования, аксессуаров<br>
                и комплектующих для кальянов.
            </h4>
            <h4 class="flex flex-row items-center mb-5 text-lg font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines mr-4">
                Наши ценности
            </h4>
            <div class="flex flex-row justify-between">
                <div class="flex w-1/3 flex-col mb-3 items-center">
                    <img width="150" height="150" src="/local/templates/Oshisha/images/honesty.png" alt="oshisha">
                    <span class="flex items-center justify-center mt-3">
                        Деятельность в рамках законодательства
                    </span>
                </div>
                <div class="flex w-1/3 flex-col mb-3 items-center">
                    <img width="150" height="150" src="/local/templates/Oshisha/images/automation.png" alt="oshisha">
                    <span class="flex items-center justify-center mt-3">
                            Автоматизация бизнес-процессов</span>
                </div>
                <div class="flex w-1/3 flex-col mb-3 items-center">
                    <img width="150" height="150" src="/local/templates/Oshisha/images/display.png" alt="oshisha">
                    <span class="flex items-center justify-center mt-3">Прозрачное партнёрство</span>
                </div>
            </div>
        </div>
        <div class="mb-6">
            <h4 class="flex flex-row items-center mb-5 text-lg font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
                <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines mr-4">
                Мы обеспечиваем
            </h4>
            <div class="flex justify-between box_text">
                <div class="flex flex-col w-1/3 justify-between">
                    <p class="box_text_business mb-4 first_for_bussiness">
                        <strong>Активные продажи </strong>&nbsp через торговых представителей с постановкой и
                        контролем задач через приложение “Мобильная торговля”.
                    </p>
                    <div class="flex justify-center box_picture_info mb-3 items-center">
                        <p class="box_text_business text_for_bussiness second_for_bussines">
                            <strong> Количественную и качественную дистрибьюцию.</strong>
                        </p>
                    </div>
                </div>
                <div class="flex flex-col w-1/3 justify-between ">
                    <p class="box_text_business mb-4 first_for_bussiness">
                        <strong>Работу товаропроводящей цепочки</strong>&nbsp через каналы сбыта (кальянные заведения,
                        специализированные магазины, торговые сети HoReCa и магазины, оптовые клиенты, табачный
                        retail,
                        торговая площадка для розничных клиентов).
                    </p>
                    <div class="flex justify-center box_picture_info mb-3 items-center">
                        <p class="box_text_business text_for_bussiness second_for_bussines">
                            <strong> Проведение трейд-маркетинговых мероприятий.</strong>
                        </p>
                    </div>
                </div>
                <div class="w-1/3 flex flex-col justify-between">
                    <p class="box_text_business  first_for_bussiness mb-4">
                        <strong>Систему мотивации</strong>&nbsp для торговых точек, продавцов, кальянных мастеров,
                        розничных
                        клиентов.
                        Масштабирование уже отлаженных бизнес-процессов.
                    </p>
                    <div class="flex justify-center box_picture_info mb-3 items-center">
                        <p class="box_text_business text_for_bussiness second_for_bussines">
                            <strong> Территориальное покрытие по всей России.</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="delivery_payment" class="mb-6">
        <h4 class="h4-for_bussines">Эксклюзивные партнёры</h4>
        <div>
            <div class="brands_boxes">
                <?php
                $arParams = array(
                    'select' => array('ID', 'UF_FILE', 'UF_LINK'),
                    'limit' => '50',
                );

                $resGetHLB = Enterego\EnteregoHelper::getHeadBlock('BrandReference', $arParams); ?>
                <div class="box_with_brands_parents row">
                    <?php foreach ($resGetHLB as $item) { ?>
                        <div class="box_with_brands w-1/4 ">
                            <a href="<?php echo $item['UF_LINK']; ?>" class="link_brands"><img
                                        src="<?php echo $item['UF_FILE']; ?>" alt="Partners"/></a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>


</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
