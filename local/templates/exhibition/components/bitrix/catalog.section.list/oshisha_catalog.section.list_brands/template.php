<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);

$arViewModeList = $arResult['VIEW_MODE_LIST'];



$strSectionEdit = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_EDIT");
$strSectionDelete = CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "SECTION_DELETE");
$arSectionDeleteParams = array("CONFIRM" => GetMessage('CT_BCSL_ELEMENT_DELETE_CONFIRM'));

?>
<div class="row mb-4"  style="min-width: 270px;">
	<div class="col">
		<? if ('Y' == $arParams['SHOW_PARENT_NAME'] && 0 < $arResult['SECTION']['ID'])
		{
			$this->AddEditAction($arResult['SECTION']['ID'], $arResult['SECTION']['EDIT_LINK'], $strSectionEdit);
			$this->AddDeleteAction($arResult['SECTION']['ID'], $arResult['SECTION']['DELETE_LINK'], $strSectionDelete, $arSectionDeleteParams);

			?> <h2 class="mt-3 mb-2" id="<? echo $this->GetEditAreaId($arResult['SECTION']['ID']); ?>" ><?
			echo (
			isset($arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]) && $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"] != ""
				? $arResult['SECTION']["IPROPERTY_VALUES"]["SECTION_PAGE_TITLE"]
				: $arResult['SECTION']['NAME']
			);
			?>

			</h2><?
		}

        $APPLICATION->IncludeComponent(
            "bitrix:breadcrumb",
            "oshisha_breadcrumb",
            array(
                "PATH" => "",
                "SITE_ID" => "s1",
                "START_FROM" => "0"
            )
        );

        $categoryAll = CIBlockSection::getList(['SORT' => 'asc'],
            ["IBLOCK_ID" => "9", "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", 'DEPTH_LEVEL' => 1],
            ['ID', 'NAME', 'CODE', 'DETAIL_PICTURE', 'PICTURE', 'DESCRIPTION']
        );
        ?>
        <div class="d-flex flex-column mb-5" id="box_boxes">
            <?php while ($CategoryParents = $categoryAll->fetch()) {
                $categoryBrandAll = CIBlockSection::getList(
                    [],
                    ["IBLOCK_ID" => "9", "ACTIVE" => "Y", "GLOBAL_ACTIVE" => "Y", 'SECTION_ID' => $CategoryParents['ID'], ''],
                    ['ID', 'NAME', 'CODE', 'DETAIL_PICTURE', 'PICTURE', 'DESCRIPTION']
                );
                ?>
                <div class="d-flex flex-column mb-5" id="box_brands">
                    <h5 class="mt-5 mb-3"><b><?= $CategoryParents['NAME']; ?></b></h5>
                    <div class="box_with_brands_parents" id="click_brand_<?= $CategoryParents['ID']; ?>">
                        <? while ($categoryBrand = $categoryBrandAll->fetch()) {
                            $image = CFile::GetPath($categoryBrand['PICTURE']);
                            if (!empty($image)) {
                                ?>
                                <div class="box_with_brands d-flex justify-content-center align-items-center col col-sm">
                                    <a  href="<?='/brands/'.$categoryBrand['CODE'].'/';?>"
                                       style="background-image: url('<?= $image ?>');">
                                    </a>
                                </div>
                            <?php } else { ?>
                                <div class="box_with_brands col col-sm">
                                    <a class="d-flex justify-content-center align-items-center" href="<?='/brands/'.$categoryBrand['CODE'].'/';?>">
                                        <?= $categoryBrand['NAME']; ?></a>
                                </div>
                            <?php }
                        } ?>
                    </div>
                    <div class="d-flex justify-content-end m-2 box_button">
                        <a href="javascript:void(0)" class="link_menu_catalog link_red_button link_brand" id="click_brand_<?=
                        $CategoryParents['ID']; ?>">Посмотреть все</a>
                    </div>
                </div>
            <? } ?>
        </div>
	</div>
</div>
