<?php use Bitrix\Sale\Fuser;

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
CUtil::InitJSCore(array('fx'));
$item_id = $listRecommendProd['ITEMS'] = [];
$id_USER = $USER->GetID();
$FUser_id = Fuser::getId($id_USER);
if (!empty($arResult["PROPERTIES"][FILTER_PROD_NEWS ?? 'FILTER_PROD_NEWS']['VALUE'])) {
    $res = CIBlockElement::GetList([],
        ["ID" => $arResult["PROPERTIES"]['FILTER_PROD_NEWS']['VALUE'],
            'IBLOCK_ID' => IBLOCK_CATALOG]
    );
    while ($items = $res->Fetch()) {
        $listRecommendProd['ITEMS'][] = $items;
    }
}

$item_id[] = $arResult['ID'];

$count_likes = DataBase_like::getLikeFavoriteAllProduct($item_id, $FUser_id);
foreach ($count_likes['ALL_LIKE'] as $keyLike => $count) {
    $arResult['COUNT_LIKES'] = $count;
}
foreach ($count_likes['USER'] as $keyFAV => $count) {
    $arResult['COUNT_LIKE'] = $count['Like'][0];
    $arResult['COUNT_FAV'] = $count['Fav'][0];
}
?>
<div class="news-detail <?= $themeClass ?>">

    <div class="mb-3" id="<?= $this->GetEditAreaId($arResult['ID']) ?>">
        <?php if ($arParams["DISPLAY_PICTURE"] != "N"): ?>
            <?php if ($arResult["VIDEO"]) { ?>
                <div class="mb-5 news-detail-youtube embed-responsive embed-responsive-16by9" style="display: block;">
                    <iframe src="<?= $arResult["VIDEO"] ?>" frameborder="0" allowfullscreen=""></iframe>
                </div>
            <?php } else if ($arResult["SOUND_CLOUD"]) { ?>
                <div class="mb-5 news-detail-audio">
                    <iframe width="100%" height="166" scrolling="no" frameborder="no"
                            src="https://w.soundcloud.com/player/?url=<?= urlencode($arResult["SOUND_CLOUD"]) ?>&amp;color=ff5500&amp;auto_play=false&amp;
                            hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false"></iframe>
                </div>
            <?php } else if (is_array($arResult["DETAIL_PICTURE"])) { ?>
                <div class="mb-5 mt-4 news-detail-img">
                    <img
                            class="card-img-top"
                            src="<?= $arResult["DETAIL_PICTURE"]["SRC"] ?>"
                            alt="<?= $arResult["DETAIL_PICTURE"]["ALT"] ?>"
                            title="<?= $arResult["DETAIL_PICTURE"]["TITLE"] ?>"
                    />
                </div>
            <?php } ?>
        <?php endif ?>
        <div class="news_boxes mt-3">
            <div class="news-detail-body mb-5">
                <div class="box_with_properties mb-4">
                    <div class="d-flex flex-column align-self-center">
                        <?php if ($arParams["DISPLAY_NAME"] != "N" && $arResult["NAME"]): ?>
                            <h1 class="news-detail-title"><?= $arResult["NAME"] ?></h1>
                        <?php endif; ?>
                        <div class="d-flex flex-row">
                            <h5 class=" mb-0">
                                <?= explode(' ', $arResult["DATE_CREATE"])[0]; ?>
                            </h5>
                        </div>
                    </div>
                    <div class="box_news">
                        <div class="box_with_net detail flex flex-col align-items-center">
                            <?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
                                'templates',
                                array(
                                    'ID_PROD' => $arResult['ID'],
                                    'F_USER_ID' => $FUser_id,
                                    'LOOK_LIKE' => true,
                                    'LOOK_FAVORITE' => false,
                                    'COUNT_LIKE' => $arResult['COUNT_LIKE'],
                                    'COUNT_FAV' => $arResult['COUNT_FAV'],
                                    'COUNT_LIKES' => $arResult['COUNT_LIKES'],
                                ),
                                $component,
                                array('HIDE_ICONS' => 'Y')
                            ); ?>
                            <span title="Поделиться" class="shared" data-element-id="<?= $arResult['ID'] ?>">
                                <i class="fa fa-paper-plane-o font-20" aria-hidden="true"></i>
                                <div class="shared_block">
                                    <?php $APPLICATION->IncludeComponent(
                                        "arturgolubev:yandex.share",
                                        "",
                                        array(
                                            "DATA_IMAGE" => "",
                                            "DATA_RESCRIPTION" => "",
                                            "DATA_TITLE" => $arResult['NAME'],
                                            "DATA_URL" => $arResult['DETAIL_PAGE_URL'],
                                            "OLD_BROWSERS" => "N",
                                            "SERVISE_LIST" => BXConstants::Shared(),
                                            "TEXT_ALIGN" => "ar_al_left",
                                            "TEXT_BEFORE" => "",
                                            "VISUAL_STYLE" => "icons"
                                        )
                                    ); ?>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="news-detail-content">
                    <?php if ($arResult["NAV_RESULT"]): ?>
                        <?php if ($arParams["DISPLAY_TOP_PAGER"]): ?><?= $arResult["NAV_STRING"] ?><br/><?php endif; ?>
                        <?= $arResult["NAV_TEXT"]; ?>
                        <?php if ($arParams["DISPLAY_BOTTOM_PAGER"]): ?>
                            <br/><?= $arResult["NAV_STRING"] ?><?php endif; ?>
                    <?php elseif ($arResult["DETAIL_TEXT"] <> ''): ?>
                        <?= $arResult["DETAIL_TEXT"]; ?>
                    <?php else: ?>
                        <?php echo $arResult["PREVIEW_TEXT"]; ?>
                    <?php endif ?>
                </div>
                <p class="mt-5 d-flex align-self-end mb-4" style="display:none !important;">
                    <a class="link_tag" style="font-size: 14px"
                       href="/news/"><?= GetMessage("T_NEWS_DETAIL_BACK") ?></a>
                </p>
            </div>

            <div class="comments_box_news mt-5 mb-5">
                <h2 class="mt-4 mb-3 text-left"><b>Комментарии</b></h2>
                <?php $componentCommentsParams = array(
                    'ELEMENT_ID' => $arResult['ID'],
                    'ELEMENT_CODE' => '',
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'SHOW_DEACTIVATED' => $arParams['SHOW_DEACTIVATED'],
                    'URL_TO_COMMENT' => '',
                    'WIDTH' => '',
                    'COMMENTS_COUNT' => '5',
                    'FB_USE' => $arParams['FB_USE'],
                    'FB_APP_ID' => $arParams['FB_APP_ID'],
                    'VK_USE' => $arParams['VK_USE'],
                    'VK_API_ID' => $arParams['VK_API_ID'],
                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    'BLOG_TITLE' => '',
                    'BLOG_URL' => $arParams['BLOG_URL'],
                    'PATH_TO_SMILE' => '',
                    'EMAIL_NOTIFY' => $arParams['BLOG_EMAIL_NOTIFY'],
                    'AJAX_POST' => 'Y',
                    'USE_REVIEW' => 'Y',
                    "BLOG_USE" => "Y",
                    'SHOW_SPAM' => 'Y',
                    'SHOW_RATING' => 'N',
                    'FB_TITLE' => '',
                    'FB_USER_ADMIN_ID' => '',
                    'FB_COLORSCHEME' => 'light',
                    'FB_ORDER_BY' => 'reverse_time',
                    'VK_TITLE' => '',
                    'TEMPLATE_THEME' => $arParams['~TEMPLATE_THEME']
                );
                if (isset($arParams["USER_CONSENT"]))
                    $componentCommentsParams["USER_CONSENT"] = $arParams["USER_CONSENT"];
                if (isset($arParams["USER_CONSENT_ID"]))
                    $componentCommentsParams["USER_CONSENT_ID"] = $arParams["USER_CONSENT_ID"];
                if (isset($arParams["USER_CONSENT_IS_CHECKED"]))
                    $componentCommentsParams["USER_CONSENT_IS_CHECKED"] = $arParams["USER_CONSENT_IS_CHECKED"];
                if (isset($arParams["USER_CONSENT_IS_LOADED"]))
                    $componentCommentsParams["USER_CONSENT_IS_LOADED"] = $arParams["USER_CONSENT_IS_LOADED"];
                $APPLICATION->IncludeComponent(
                    'bitrix:catalog.comments',
                    'oshisha_catalog.commets',
                    $componentCommentsParams,
                    $component,
                    array('HIDE_ICONS' => 'Y')
                ); ?>
            </div>
        </div>

        <?php if (($arParams["USE_RATING"] == "Y") && ($arParams["USE_SHARE"] == "Y")) { ?>
        <div class="d-flex justify-content-between"> <?php } ?>

            <?php if ($arParams["USE_RATING"] == "Y"): ?>
                <div>
                    <?php $APPLICATION->IncludeComponent(
                        "bitrix:iblock.vote",
                        "bootstrap_v4",
                        array(
                            "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                            "IBLOCK_ID" => $arParams["IBLOCK_ID"],
                            "ELEMENT_ID" => $arResult["ID"],
                            "MAX_VOTE" => $arParams["MAX_VOTE"],
                            "VOTE_NAMES" => $arParams["VOTE_NAMES"],
                            "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                            "CACHE_TIME" => $arParams["CACHE_TIME"],
                            "DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"],
                            "SHOW_RATING" => "Y",
                        ),
                        $component
                    ); ?>
                </div>
            <?php endif ?>
            <?php if ($arParams["USE_SHARE"] == "Y"): ?>
                <div>
                    <noindex>
                        <?php
                        $APPLICATION->IncludeComponent(
                            "bitrix:main.share",
                            $arParams["SHARE_TEMPLATE"],
                            array(
                                "HANDLERS" => $arParams["SHARE_HANDLERS"],
                                "PAGE_URL" => $arResult["~DETAIL_PAGE_URL"],
                                "PAGE_TITLE" => $arResult["~NAME"],
                                "SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
                                "SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
                                "HIDE" => $arParams["SHARE_HIDE"],
                            ),
                            $component,
                            array("HIDE_ICONS" => "Y")
                        );
                        ?>
                    </noindex>
                </div>
            <?php endif ?>
        </div>
    </div>
    <div class="mb-5 mt-5">
        <div data-entity="parent-container">
            <div data-entity="header" data-showed="false">
                <h4 class="font-19"><b>Рекомендуемые товары</b></h4>
            </div>
            <div class="by-card">
                <?php if (!empty($listRecommendProd['ITEMS'])) {
                    $arResult['ITEMS'] = $listRecommendProd['ITEMS'];
                    $APPLICATION->IncludeComponent(
                        "bitrix:enterego.slider",
                        ".default",
                        $arResult,
                        false
                    );
                } ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        BX.ready(function () {
            var slider = new JCNewsSlider('<?=CUtil::JSEscape($this->GetEditAreaId($arResult['ID']));?>', {
                imagesContainerClassName: 'news-detail-slider-container',
                leftArrowClassName: 'news-detail-slider-arrow-container-left',
                rightArrowClassName: 'news-detail-slider-arrow-container-right',
                controlContainerClassName: 'news-detail-slider-control'
            });
        });
    </script>
