<?php

use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Для бизнеса");
Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js"); ?>
    <div id="content_box_business " class="static">
        <h1>Для бизнеса</h1>
        <div class="d-flex flex-column">
            <div class="mb-6">
                <h4 class="mb-5 bussiness-start-text">Компания OSHISHA - официальный дистрибьютор табачной<br> и бестабачной
                        продукции, оборудования, аксессуаров<br>
                        и комплектующих для кальянов.
                </h4>
                <h4 class="d-flex flex-row align-items-center h4-for_bussines">
                    <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines">
                    Наши ценности
                </h4>
                <div class="d-flex row  flex-row justify-content-between">
                    <div class="d-flex col-4 col-lg-4 col-md-4 flex-column mb-3 align-items-center">
                        <span class="box_honesty"></span>
                        <span class="text_for_bussiness text_for_bussiness_icon color_black d-flex align-items-center justify-content-center mt-3">
                        Деятельность в рамках законодательства
                    </span>
                    </div>
                    <div class="d-flex col-4 col-lg-4 col-md-4 flex-column mb-3 align-items-center">
                        <span class="box_automation"></span>
                        <span class="text_for_bussiness text_for_bussiness_icon  color_black d-flex align-items-center justify-content-center mt-3">
                            Автоматизация бизнес-процессов</span>
                    </div>
                    <div class="d-flex col-4 col-lg-4 col-md-4 flex-column mb-3 align-items-center">
                        <span class="box_display"></span>
                        <span class="text_for_bussiness text_for_bussiness_icon  color_black d-flex align-items-center  justify-content-center mt-3">
                            Прозрачное партнёрство</span>
                    </div>
                </div>
            </div>
            <div class="mb-6">
                <h4 class="d-flex flex-row align-items-center h4-for_bussines">
                    <img src="/local/assets/images/icon_location.svg" class="icon_for_bussines">
                   Мы обеспечиваем
                </h4>
                <div class="row d-flex justify-content-between box_text">
                    <div class="col-obesp col-lg-4 col-md-4 d-flex flex-column justify-content-between">
                        <p class="box_text_business mb-4 first_for_bussiness">
                            <strong>Активные продажи </strong>&nbsp через торговых представителей с постановкой и
                            контролем задач через приложение “Мобильная торговля”.
                        </p>
                        <div class="d-flex justify-content-center box_picture_info mb-3 align-items-center">
                        <p class="box_text_business text_for_bussiness second_for_bussines">
                        <strong> Количественную и качественную дистрибьюцию.</strong>
                        </p>
                        </div>
                    </div>
                    <div class="col-obesp col-lg-4 col-md-4  d-flex flex-column justify-content-between ">
                        <p class="box_text_business mb-4 first_for_bussiness">
                            <strong>Работу товаропроводящей цепочки</strong>&nbsp через каналы сбыта (кальянные заведения,
                            специализированные магазины, торговые сети HoReCa и магазины, оптовые клиенты, табачный
                            retail,
                            торговая площадка для розничных клиентов).
                        </p>
                        <div class="d-flex justify-content-center box_picture_info mb-3 align-items-center">
                        <p class="box_text_business text_for_bussiness second_for_bussines">
                        <strong>  Проведение трейд-маркетинговых мероприятий.</strong>
                        </p>
                        </div>
                    </div>
                    <div class="col-obesp col-lg-4 col-md-4  d-flex flex-column justify-content-between">
                        <p class="box_text_business  first_for_bussiness mb-4">
                           <strong>Систему мотивации</strong>&nbsp для торговых точек, продавцов, кальянных мастеров, розничных
                            клиентов.
                            Масштабирование уже отлаженных бизнес-процессов.
                        </p>
                        <div class="d-flex justify-content-center box_picture_info mb-3 align-items-center">
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
                        <?php foreach ($resGetHLB as $item) {?>
                            <div class="box_with_brands col-3 col-lg-2 col-md-3 ">
                                <a href="<?php echo $item['UF_LINK']; ?>" class="link_brands"><img
                                            src="<?php echo $item['UF_FILE']; ?>"  alt="Partners"/></a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
			 </div>

       
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
