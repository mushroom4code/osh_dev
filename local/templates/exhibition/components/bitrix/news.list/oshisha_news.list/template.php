<?php

use Bitrix\Sale\Fuser;
use DataBase_like;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
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

$themeClass = isset($arParams['TEMPLATE_THEME']) ? ' bx-' . $arParams['TEMPLATE_THEME'] : '';
$item_id = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);

foreach ($arResult["ITEMS"] as $arItem) {
    $item_id[] = $arItem['ID'];
}

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);

?>
<div class="box_with_news news-list<?= $themeClass ?>">

    <? if ($arParams["DISPLAY_TOP_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?>
    <? endif; ?>
    <? foreach ($arResult["ITEMS"] as $arItem): ?>
        <?

        $this->AddEditAction(
            $arItem['ID'],
            $arItem['EDIT_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_EDIT"
            )
        );
        $this->AddDeleteAction(
            $arItem['ID'],
            $arItem['DELETE_LINK'],
            CIBlock::GetArrayByID(
                $arItem["IBLOCK_ID"],
                "ELEMENT_DELETE"),
            array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM'))
        );
        $props = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $arItem['ID'], array(), array('CODE' => 'TAG'));
        $propVal = $props->Fetch(); 
        ?>

        <div class="news-list-item col-6 col-md-3" id="<?= $this->GetEditAreaId($arItem['ID']); ?>">
            <div class="box_with_properties mobile_news">
                <?
				if( $arItem["FIELDS"]['ACTIVE_FROM'] )
				{
					$arItem["FIELDS"]['DATE_CREATE'] = $arItem["FIELDS"]['ACTIVE_FROM'];
				}
                foreach ($arItem["FIELDS"] as $code => $value):?>

                    <? if ($code == "SHOW_COUNTER"):?>
                        <div class="news-list-view news-list-post-params">
                            <span class="news-list-icon news-list-icon-eye"></span>
                            <span class="news-list-param"><?= GetMessage("IBLOCK_FIELD_" . $code) ?>: </span>
                            <span class="news-list-value"><?= intval($value); ?></span>
                        </div>
                    <? elseif (
                        $value
                        && (
                            $code == "SHOW_COUNTER_START"
                            || $code == "DATE_ACTIVE_FROM"
                           // || $code == "ACTIVE_FROM"
                            || $code == "DATE_ACTIVE_TO"
                            || $code == "ACTIVE_TO"
                            || $code == "DATE_CREATE"
                            || $code == "TIMESTAMP_X"
                        )
                    ):?>
                        <?
                        $value = CIBlockFormatProperties::DateFormat($arParams["ACTIVE_DATE_FORMAT"], MakeTimeStamp($value, CSite::GetDateFormat()));
                        ?>
                        <div class="news-list-view news-list-post-params">
                            <span class="news-list-value link_tag"><?= $propVal['VALUE_ENUM'] ?></span>
                        </div>
                        <div class="news-list-view news-list-post-params">
                            <span class="news_val_data"><?= $value; ?></span>
                        </div>
                    <? endif; ?>

                <? endforeach; ?>
            </div>
            <div class="card">
                <? if ($arParams["DISPLAY_PICTURE"] != "N"): ?>

                    <?
                    if ($arItem["VIDEO"]) {
                        ?>
                        <div class="news-list-item-embed-video embed-responsive embed-responsive-16by9">
                            <iframe
                                    class="embed-responsive-item"
                                    src="<? echo $arItem["VIDEO"] ?>"
                                    frameborder="0"
                                    allowfullscreen=""
                            ></iframe>
                        </div>
                    <?
                    }
                    else if ($arItem["SOUND_CLOUD"])
                    {
                    ?>
                        <div class="news-list-item-embed-audio embed-responsive embed-responsive-16by9">
                            <iframe
                                    class="embed-responsive-item"
                                    width="100%"
                                    scrolling="no"
                                    frameborder="no"
                                    src="https://w.soundcloud.com/player/?url=<? echo urlencode($arItem["SOUND_CLOUD"]) ?>&amp;color=ff5500&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"
                            ></iframe>
                        </div>
                    <?
                    }
                    else if ($arItem["SLIDER"] && count($arItem["SLIDER"]) > 1)
                    {
                    ?>
                        <div class="news-list-item-embed-slider">
                            <div class="news-list-slider-container" style="width: <?
                            echo count($arItem["SLIDER"]) * 100 ?>%;left: 0;">
                                <?
                                foreach ($arItem["SLIDER"] as $file):?>
                                    <div class="news-list-slider-slide">
                                        <img src="<?= $file["SRC"] ?>" alt="<?= $file["DESCRIPTION"] ?>">
                                    </div>
                                <? endforeach ?>
                            </div>
                            <div class="news-list-slider-arrow-container-left">
                                <div class="news-list-slider-arrow"><i class="fa fa-angle-left"></i></div>
                            </div>
                            <div class="news-list-slider-arrow-container-right">
                                <div class="news-list-slider-arrow"><i class="fa fa-angle-right"></i></div>
                            </div>
                            <ul class="news-list-slider-control">
                                <?
                                foreach ($arItem["SLIDER"] as $i => $file):?>
                                    <li rel="<?= ($i + 1) ?>" <?
                                    if (!$i)
                                        echo 'class="current"' ?>><span></span></li>
                                <? endforeach ?>
                            </ul>
                        </div>
                        <script type="text/javascript">
                            BX.ready(function () {
                                new JCNewsSlider('<?=CUtil::JSEscape($this->GetEditAreaId($arItem['ID']));?>', {
                                    imagesContainerClassName: 'news-list-slider-container',
                                    leftArrowClassName: 'news-list-slider-arrow-container-left',
                                    rightArrowClassName: 'news-list-slider-arrow-container-right',
                                    controlContainerClassName: 'news-list-slider-control'
                                });
                            });
                        </script>
                    <?
                    }
                    else if ($arItem["SLIDER"])
                    {
                    ?>
                        <div class="news-list-item-embed-img">
                            <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])) {
                                ?>
                                <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                                    <img
                                            class="card-img-top"
                                            src="<?= $arItem["SLIDER"][0]["SRC"] ?>"
                                            width="<?= $arItem["SLIDER"][0]["WIDTH"] ?>"
                                            height="<?= $arItem["SLIDER"][0]["HEIGHT"] ?>"
                                            alt="<?= $arItem["SLIDER"][0]["ALT"] ?>"
                                            title="<?= $arItem["SLIDER"][0]["TITLE"] ?>"
                                    />
                                </a>
                                <?
                            } else {
                                ?>
                                <img
                                        class="card-img-top"
                                        src="<?= $arItem["SLIDER"][0]["SRC"] ?>"
                                        width="<?= $arItem["SLIDER"][0]["WIDTH"] ?>"
                                        height="<?= $arItem["SLIDER"][0]["HEIGHT"] ?>"
                                        alt="<?= $arItem["SLIDER"][0]["ALT"] ?>"
                                        title="<?= $arItem["SLIDER"][0]["TITLE"] ?>"
                                />
                                <?
                            }
                            ?>
                        </div>
                        <?
                    }
                    else if (is_array($arItem["PREVIEW_PICTURE"])) {
                    if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])) {
                        ?>
                        <a href="<?= $arItem["DETAIL_PAGE_URL"] ?>">
                            <img
                                    class="card-img-top"
                                    src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                                    alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                                    title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                            />
                        </a>
                        <?
                    }
                    else {
                        ?>
                    <img
                            src="<?= $arItem["PREVIEW_PICTURE"]["SRC"] ?>"
                            class="card-img-top"
                            alt="<?= $arItem["PREVIEW_PICTURE"]["ALT"] ?>"
                            title="<?= $arItem["PREVIEW_PICTURE"]["TITLE"] ?>"
                    />
                        <?
                    }
                    }
                    ?>

                <? endif; ?>


                <div class="card-body">
                    <div class="box_with_properties">
                        <div>
                            <? if ($arParams["DISPLAY_NAME"] != "N" && $arItem["NAME"]): ?>
                                <h4 class="card-title">
                                    <? if (!$arParams["HIDE_LINK_WHEN_NO_DETAIL"] || ($arItem["DETAIL_TEXT"] && $arResult["USER_HAVE_ACCESS"])): ?>
                                        <a href="<? echo $arItem["DETAIL_PAGE_URL"] ?>"><? echo $arItem["NAME"] ?></a>
                                    <? else: ?>
                                        <? echo $arItem["NAME"] ?>
                                    <? endif; ?>
                                </h4>
                            <? endif; ?>

                            <? if ($arParams["DISPLAY_PREVIEW_TEXT"] != "N" && $arItem["PREVIEW_TEXT"]): ?>
                                <p class="card-text"><? echo $arItem["PREVIEW_TEXT"]; ?></p>
                            <? endif; ?>
                        </div>
                        <div class="box_news">
                            <div class="box_with_net">
                                <?php
                                foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
                                    if ($keyLike == $arItem['ID']) {
                                        $arItem['COUNT_LIKES'] = $count;
                                    }
                                }
                                foreach ($count_likes['USER'] as $keyFAV => $count) {
                                    if ($keyFAV == $arItem['ID']) {
                                        $arItem['COUNT_LIKE'] = $count['Like'][0];
                                        $arItem['COUNT_FAV'] = $count['Fav'][0];
                                    }
                                }

                                $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                    'templates',
                                    array(
                                        'ID_PROD' => $arItem['ID'],
                                        'F_USER_ID' => $FUser_id,
                                        'LOOK_LIKE' => true,
                                        'LOOK_FAVORITE' => false,
                                        'COUNT_LIKE' => $arItem['COUNT_LIKE'],
                                        'COUNT_FAV' => $arItem['COUNT_FAV'],
                                        'COUNT_LIKES' => $arItem['COUNT_LIKES'],
                                    )
                                    ,
                                    $component,
                                    array('HIDE_ICONS' => 'Y')
                                ); ?>
                                <span title="Поделиться" class="shared" data-element-id="<?=$arItem['ID']?>"><i class="fa fa-paper-plane-o"
                                                                  aria-hidden="true"></i> 
																  <div class="shared_block">
<?$APPLICATION->IncludeComponent(
	"arturgolubev:yandex.share",
	"",
	Array(
		"DATA_IMAGE" => "",
		"DATA_RESCRIPTION" => "",
		"DATA_TITLE" =>$arItem['NAME'],
		"DATA_URL" => $arItem['DETAIL_PAGE_URL'],
		"OLD_BROWSERS" => "N",
		"SERVISE_LIST" => BXConstants::Shared(),
		"TEXT_ALIGN" => "ar_al_left",
		"TEXT_BEFORE" => "",
		"VISUAL_STYLE" => "icons"
	)
);?>																  
																  </div></span>
                            </div>
                        </div>
                    </div>


                    <? foreach ($arItem["DISPLAY_PROPERTIES"] as $pid => $arProperty): ?>
                        <?
                        if (is_array($arProperty["DISPLAY_VALUE"]))
                            $value = implode("&nbsp;/&nbsp;", $arProperty["DISPLAY_VALUE"]);
                        else
                            $value = $arProperty["DISPLAY_VALUE"];
                        ?>
                        <? if ($arProperty["CODE"] == "FORUM_MESSAGE_CNT"): ?>
                            <div class="news-list-view news-list-post-params">
                                <span class="news-list-icon news-list-icon-comments"></span>
                                <span class="news-list-param"><?= $arProperty["NAME"] ?>:<?= $value; ?></span>
                                <span class="news-list-value"><?= $value; ?></span>
                            </div>
                        <? elseif ($value != ""): ?>
                            <div class="news-list-view news-list-post-params">
                                <span class="news-list-icon"></span>
                                <span class="news-list-param"><?= $arProperty["NAME"] ?>:</span>
                                <span class="news-list-value"><?= $value; ?></span>
                            </div>
                        <? endif; ?>
                    <? endforeach; ?>
                </div>
            </div>
        </div>
    <? endforeach; ?>
    <? if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
        <?= $arResult["NAV_STRING"] ?>
    <? endif; ?>
</div>

<script>

        $(function () {
            if (window.screen.width <= 480) 
			{
                let count = 2;
				let slidesToScroll = 1;
                
                
				console.log(count);
                $('.box_with_news').slick({
                    arrows: true,
                    prevArrow: '<span class="new_custom_button_slick_left" aria-hidden="true">' +
                        '<i class="fa fa-angle-left" aria-hidden="true"></i></span>',
                    nextArrow: '<span class="new_custom_button_slick_right" aria-hidden="true">' +
                        '<i class="fa fa-angle-right" aria-hidden="true"></i></span>',
                    slidesToShow: count,
                    slidesToScroll: slidesToScroll,
					variableWidth: true,
                });
			}
            

        });
</script>