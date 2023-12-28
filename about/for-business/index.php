<?php

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Для бизнеса");
Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js"); ?>
<div id="content_box_business " class="static">
    <h1 class="md:text-3xl text-2xl my-5 font-bold dark:font-medium text-textLight dark:text-textDarkLightGray">
        Для бизнеса
    </h1>
    <div class="flex flex-col mt-10">
        <h4 class="mb-16 text-2xl font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
            Компания OSHISHA - официальный дистрибьютор табачной<br> и бестабачной
            продукции, оборудования, аксессуаров<br>
            и комплектующих для кальянов.
        </h4>
        <div class="mb-14">
            <h4 class="flex flex-row items-center mb-8 text-2xl font-semibold dark:font-medium text-textLight
            dark:text-textDarkLightGray">
                <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines mr-4">
                Наши ценности
            </h4>
            <div class="flex md:flex-row flex-col justify-between">
                <div class="flex md:w-1/3 w-full flex-col mb-3 items-center">
                    <img width="150" height="150" src="/local/templates/Oshisha/images/honesty.png"
                         class="invert-0 dark:invert" alt="oshisha">
                    <span class="flex items-center justify-center mt-3 text-md font-normal text-textLight
                     dark:text-textDarkLightGray">
                        Деятельность в рамках законодательства
                    </span>
                </div>
                <div class="flex md:w-1/3 w-full flex-col mb-3 items-center">
                    <img width="150" height="150" class="invert-0 dark:invert"
                         src="/local/templates/Oshisha/images/automation.png"
                         alt="oshisha">
                    <span class="flex items-center justify-center mt-3 text-md text-textLight font-normal
                    dark:text-textDarkLightGray">
                            Автоматизация бизнес-процессов</span>
                </div>
                <div class="flex md:w-1/3 w-full flex-col mb-3 items-center">
                    <img width="150" height="150" src="/local/templates/Oshisha/images/display.png"
                         class="invert-0 dark:invert" alt="oshisha">
                    <span class="flex items-center justify-center mt-3 text-md text-textLight font-normal
                     dark:text-textDarkLightGray">Прозрачное партнёрство</span>
                </div>
            </div>
        </div>
        <div class="mb-14">
            <h4 class="flex flex-row items-center mb-8 text-2xl font-semibold dark:font-medium text-textLight
            dark:text-textDarkLightGray">
                <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines mr-4">
                Мы обеспечиваем
            </h4>
            <div class="flex justify-between md:flex-row flex-col">
                <div class="flex flex-col md:w-1/3 w-full justify-between">
                    <p class="p-5 rounded-lg bg-textDarkLightGray dark:bg-darkBox mr-5 mb-5 h-full font-normal dark:font-light">
                        <span class="font-semibold dark:font-medium">Активные продажи </span>
                        &nbsp через торговых представителей с постановкой и
                        контролем задач через приложение “Мобильная торговля”.
                    </p>
                    <div class="flex justify-center items-center p-5 rounded-lg bg-textDarkLightGray
                    dark:bg-darkBox mr-5 mb-5">
                        <p class="text-sm font-semibold dark:font-medium">
                            Количественную и качественную дистрибьюцию.
                        </p>
                    </div>
                </div>
                <div class="flex flex-col md:w-1/3 w-full justify-between">
                    <p class="p-5 rounded-lg bg-textDarkLightGray dark:bg-darkBox mr-5 mb-5 h-full font-normal dark:font-light">
                        <span class="font-semibold dark:font-medium">Работу товаропроводящей цепочки</span>
                        &nbsp через каналы сбыта (кальянные заведения,
                        специализированные магазины, торговые сети HoReCa и магазины, оптовые клиенты, табачный
                        retail,
                        торговая площадка для розничных клиентов).
                    </p>
                    <div class="flex justify-center items-center p-5 rounded-lg bg-textDarkLightGray
                    dark:bg-darkBox mr-5 mb-5">
                        <p class="text-sm font-semibold dark:font-medium">Проведение трейд-маркетинговых мероприятий.</p>
                    </div>
                </div>
                <div class="md:w-1/3 w-full flex flex-col justify-between">
                    <p class="p-5 rounded-lg bg-textDarkLightGray dark:bg-darkBox mr-5 mb-5 h-full font-normal dark:font-light">
                        <span class="font-semibold dark:font-medium">Систему мотивации</span>
                        &nbsp для торговых точек, продавцов, кальянных мастеров,
                        розничных
                        клиентов.
                        Масштабирование уже отлаженных бизнес-процессов.
                    </p>
                    <div class="flex justify-center items-center p-5 rounded-lg bg-textDarkLightGray
                    dark:bg-darkBox mr-5 mb-5">
                        <p class="text-sm font-semibold dark:font-medium"> Территориальное покрытие по всей России.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="delivery_payment" class="mb-6">
        <h4 class="md:text-3xl text-2xl my-5 font-semibold dark:font-medium text-textLight dark:text-textDarkLightGray">
            Эксклюзивные партнёры
        </h4>
        <div>
            <div class="brands_boxes">
                <?php
                $arParams = array(
                    'select' => array('ID', 'UF_FILE', 'UF_LINK'),
                    'limit' => '50',
                );
                $resGetHLB = Enterego\EnteregoHelper::getHeadBlock('BrandReference', $arParams); ?>
                <div class="box_with_brands_parents flex md:flex-row flex-col flex-wrap">
                    <?php foreach ($resGetHLB as $item) { ?>
                        <div class="box_with_brands p-5 mr-3 mb-3 md:w-48 md:h-32 h-auto w-full rounded-lg
                         bg-textDarkLightGray dark:bg-darkBox">
                            <a href="<?php echo $item['UF_LINK']; ?>"
                               class="link_brands">
                                <img src="<?php echo $item['UF_FILE']; ?>" class="rounded-lg w-full h-full"
                                     alt="Partners"/>
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>


</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
