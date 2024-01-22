<?php
define('STATIC_P', 'static_wrap');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Вакансии"); ?>
    <div class="mt-4 mb-5">
        <h4 class="flex flex-row items-center mb-8 mt-5 text-3xl font-bold dark:font-medium text-textLight
        dark:text-textDarkLightGray">Вакансии</h4>
        <div class="w-full p-0 mb-10 banner-vacancy">
            <?php $APPLICATION->IncludeComponent(
                "bitrix:advertising.banner",
                "oshisha_banners",
                array(
                    "BS_ARROW_NAV" => "N",
                    "BS_BULLET_NAV" => "N",
                    "BS_CYCLING" => "N",
                    "BS_EFFECT" => "fade",
                    "BS_HIDE_FOR_PHONES" => "Y",
                    "BS_HIDE_FOR_TABLETS" => "N",
                    "BS_KEYBOARD" => "Y",
                    "BS_WRAP" => "N",
                    "CACHE_TIME" => "36000000",
                    "CACHE_TYPE" => "Y",
                    "COMPONENT_TEMPLATE" => "oshisha_banners",
                    "DEFAULT_TEMPLATE" => "-",
                    "NOINDEX" => "N",
                    "QUANTITY" => "2",
                    "TYPE" => "VACANCY"
                )
            ); ?>
        </div>
        <div class="mb-10">
            <div class="bg-textDark dark:bg-darkBox text-light dark:text-textDarkLightGray p-12 rounded-lg text-md font-normal dark:font-light">
                <b>Компания OSHISHA</b> один из крупнейших дистрибьюторов кальянной продукции и ЭСДН в России. В
                портфеле компании сегодня: кальянные смеси, электронные системы доставки никотина, жевательный табак,
                кальяны и комплектующие.<br>
                <br>
                <b>Компания OSHISHA</b> осуществляет свою деятельность на рынке кальянной индустрии с 2016 года и
                насчитывает клиентскую базу более чем 3 500 постоянных клиентов в сегментах B2С и B2B (Wholesale,
                HoReCa, Retail) Ответственно задаем тренды качественного Service Level в городах присутствия, поэтому
                <b>гарантируем</b> нашим клиентам качественное взаимодействие на выгодных условиях.<br>
                <br>
                <b>Команда OSHISHA </b>– Большой коллектив профессионалов. Мы рады тем, кто разделяет наши ценности и
                готов развиваться вместе с нами! Сегодня <b>команде OSHISHA необходимы</b> результативные сотрудники,
                down-middle-top менеджмент. <br>
                <br>
                <b>Мы ценим в команде</b>: Профессионализм. Желание развиваться. Ответственность за результат.
                Позитивный настрой. Осознанную инициативу. <br>
                <br>
                <b><i>Работать в OSHISHA - надежно и интересно!</i></b>
            </div>
        </div>
        <br>
        <div class="mb-14">
            <h5 class="text-2xl font-semibold dark:font-medium text-textLight mb-5 dark:text-textDarkLightGray">Отдел HR</h5>
            <p class="mb-3 font-medium dark:font-light text-textLight dark:text-textDarkLightGray">
                Телефон : +7 (903) 118-17-25
            </p>
            <p class="mb-3 font-medium dark:font-light text-textLight dark:text-textDarkLightGray">
                Почта :
                <span class="mb-3 font-medium dark:font-light text-hover-red underline">
                    <a href="mailto: ikovalenko@oshisha.net"> ikovalenko@oshisha.net</a>
                </span>
                ,
                <span class="mb-3 font-medium dark:font-light text-hover-red underline">
                    <a href="mailto: ssavostianova@oshisha.net">ssavostianova@oshisha.net</a></span>
            </p>
        </div>
        <div class="mb-10">
            <?php
            $k = 0;
            $SectionRes = CIBlockSection::GetList(array(),
                array('ACTIVE' => 'Y', 'IBLOCK_CODE' => VACANCY),
                false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID', 'DESCRIPTION', 'SORT')
            );
            if ($SectionRes) {
                while ($arSection = $SectionRes->GetNext()) { ?>
                    <h5 class="text-2xl font-semibold dark:font-medium text-textLight mb-8 dark:text-textDarkLightGray"><?= $arSection['NAME'] ?></h5>
                    <div class="mb-5">
                        <?php
                        $arFilter = array(
                            'IBLOCK_CODE' => 'vacancy',
                            'ACTIVE' => 'Y',
                            'SECTION_ID' => $arSection['ID']
                        );
                        $resU = CIBlockElement::GetList(array(), $arFilter, false, false,
                            ['NAME', 'PREVIEW_TEXT', 'PROPERTY_LINK_IN_SPACE']);
                        while ($rowFaq = $resU->Fetch()) {
                            $k++;
                            if (empty($rowFaq['NAME'])) {
                                continue;
                            } ?>
                            <div class="mb-4 p-7 border-2 border-textDark rounded-lg dark:border-darkBox bg-white dark:bg-darkBox">
                                <h6 class="mb-2 text-textLight dark:text-textDarkLightGray font-semibold text-xl"><?= $rowFaq['NAME']; ?></h6>
                                <p class="mb-5 text-textLight dark:text-textDarkLightGray dark:font-light font-normal text-md">
                                    <?= $rowFaq['PREVIEW_TEXT'] ?>
                                </p>
                                <?php if (!empty($rowFaq['PROPERTY_LINK_IN_SPACE_VALUE'])) { ?> <a
                                        href="<?= $rowFaq['PROPERTY_LINK_IN_SPACE_VALUE']; ?>" target="_blank"
                                        class="shadow-md text-white dark:bg-dark-red bg-light-red text-sm font-medium
                                         py-2 px-4 rounded-5">Подробнее</a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
    <br><?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>