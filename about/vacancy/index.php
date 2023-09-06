<?php
define('STATIC_P', 'static_wrap');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Вакансии");?><div class="mt-4 mb-5">
	<h1 class="mb-4">Вакансии</h1>
	<div class="col-12 p-0 mb-5 banner-vacancy">
		 <?$APPLICATION->IncludeComponent(
	"bitrix:advertising.banner",
	"oshisha_banners",
	Array(
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
);?>
	</div>
	<div class="mb-4">
		<div class="p-lg-5 p-md-5 p-3 bg-gray-white br-10 font-16">
 <b>Компания OSHISHA</b> один из крупнейших дистрибьюторов кальянной продукции и ЭСДН в России. В портфеле компании сегодня: кальянные смеси, электронные системы доставки никотина, жевательный табак, кальяны и комплектующие.<br>
 <br>
 <b>Компания OSHISHA</b> осуществляет свою деятельность на рынке кальянной индустрии с 2016 года и насчитывает клиентскую базу более чем 3 500 постоянных клиентов в сегментах B2С и B2B (Wholesale, HoReCa, Retail) Ответственно задаем тренды качественного Service Level в городах присутствия, поэтому <b>гарантируем</b> нашим клиентам качественное взаимодействие на выгодных условиях.<br>
 <br>
 <b>Команда OSHISHA </b>– Большой коллектив профессионалов. Мы рады тем, кто разделяет наши ценности и готов развиваться вместе с нами! Сегодня <b>команде OSHISHA необходимы</b> результативные сотрудники, down-middle-top менеджмент. <br>
 <br>
 <b>Мы ценим в команде</b>: Профессионализм. Желание развиваться. Ответственность за результат. Позитивный настрой. Осознанную инициативу. <br>
 <br>
 <b><i>Работать в OSHISHA - надежно и интересно!</i></b>
		</div>
	</div>
 <br>
	<div class="mb-5">
		<h5 class="mb-4 font-weight-600">Отдел HR</h5>
		<p class="mb-3">
			 Телефон : +7 (903) 118-17-25
		</p>
		<p class="mb-3">
			 Почта :
		</p>
		<p>
			<span class="red_text mb-3"><a href="mailto: ikovalenko@oshisha.net"> ikovalenko@oshisha.net</a></span>
		</p>
		<p>
			<span class="red_text mb-3"><a href="mailto: ssavostianova@oshisha.net">ssavostianova@oshisha.net</a></span>
		</p>
	</div>
	<div class="mb-5 font-14">
		 <?php
			$k = 0;
			$SectionRes = CIBlockSection::GetList(array(),
				array('ACTIVE' => 'Y', 'IBLOCK_CODE' => VACANCY),
				false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID', 'DESCRIPTION', 'SORT')
			);
			if ($SectionRes) {
				while ($arSection = $SectionRes->GetNext()) { ?>
		<h5 class="mb-4 font-weight-600"><?= $arSection['NAME'] ?></h5>
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
			<div class="mb-4 p-4 br-10" style="border: 2px solid #F0F0F0;">
				<h6 class="mb-3"><b><?= $rowFaq['NAME']; ?></b></h6>
				<p class="mb-4">
					 <?= $rowFaq['PREVIEW_TEXT'] ?>
				</p>
				 <?php if (!empty($rowFaq['PROPERTY_LINK_IN_SPACE_VALUE'])) { ?> <a href="<?= $rowFaq['PROPERTY_LINK_IN_SPACE_VALUE']; ?>" target="_blank" class="red_button_cart color-white p-2 mb-3">Подробнее</a>
				<?php } ?>
			</div>
			 <?php } ?>
		</div>
		 <?php }
			} ?>
	</div>
</div>
 <br><?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>