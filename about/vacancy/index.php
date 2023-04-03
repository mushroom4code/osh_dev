<?php
define('STATIC_P', 'static_wrap');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Вакансии");
?>
    <div class="mt-5 mb-5">
        <h1 class="mb-3">Вакансии</h1>
        <div class="col-12 p-0 mb-4 banner-vacancy">
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
		        ),
		        false
	        ); ?>
        </div>
        <div class="mb-5">
            <div class="p-5 bg-gray-white br-10 font-14">
                История компании <b>берёт начало в далёком 2014 году</b>. Двое молодых ребят сидели в кальянной,
                раздумывая о своем будущем и способах заработка.
                Начался непринуждённый разговор с кальянщиком,
                который рассказал ребятам, какой табак они курят и по какой цене его закупают в кальянную.
                Недолго думая, один из друзей предложил кальянщику поставлять этот табак дешевле,
                чем они закупают на данный момент. Ни связей, ни знаний в кальянной индустрии у молодых людей не было.
                Но ответ кальянщика «можно обсудить» - заставил парней всерьёз задуматься о реализации упомянутого
                предложения.
            </div>
        </div>
        <div class="mb-5 font-14">
			<?php
			$k = 0;
			$SectionRes = CIBlockSection::GetList(array(),
				array('ACTIVE' => 'Y', 'IBLOCK_CODE' => 'vacancy'),
				false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID', 'DESCRIPTION', 'SORT')
			);
			while ($arSection = $SectionRes->GetNext()) {
				if ($arSection['CODE'] === 'hr_place') { ?>
                    <h5 class="mb-4 font-weight-600"><?= $arSection['NAME'] ?></h5>
                    <p class="mb-4"><?= $arSection['DESCRIPTION'] ?></p>
				<?php } else { ?>
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
                                <p class="mb-4"><?= $rowFaq['PREVIEW_TEXT'] ?></p>
								<?php if (!empty($rowFaq['PROPERTY_LINK_IN_SPACE_VALUE'])) { ?>
                                    <a href="<?= $rowFaq['PROPERTY_LINK_IN_SPACE_VALUE']; ?>"
                                       class="red_button_cart color-white p-2 mb-3">Подробнее</a>
								<?php } ?>
                            </div>
						<?php } ?>
                    </div>
				<?php }
			} ?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>