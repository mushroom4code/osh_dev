<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<? if (count($arResult['BANNERS']) > 0): ?>

    <?
    $this->addExternalJs("/bitrix/components/bitrix/advertising.banner/templates/bootstrap_v4/bxcarousel.js");
    $arParams['WIDTH'] = intval($arResult['SIZE']['WIDTH']);
    $arParams['HEIGHT'] = intval($arResult['SIZE']['HEIGHT']);
    if ($arParams['BS_CYCLING'] == 'Y')
        $arParams['BS_INTERVAL'] = intval($arParams['BS_INTERVAL']);
    else
        $arParams['BS_INTERVAL'] = 'false';
    $arParams['BS_WRAP'] = ($arParams['BS_WRAP'] == 'Y' || $arParams['PREVIEW'] == 'Y') ? 'true' : 'false';
    $arParams['BS_PAUSE'] = $arParams['BS_PAUSE'] == 'Y' ? 'true' : 'false';
    $arParams['BS_KEYBOARD'] = $arParams['BS_KEYBOARD'] == 'Y' ? 'true' : 'false';
    $arParams['BS_HIDE_FOR_TABLETS'] = $arParams['BS_HIDE_FOR_TABLETS'] == 'Y' ? ' d-none d-md-block' : '';
    $arParams['BS_HIDE_FOR_PHONES'] = $arParams['BS_HIDE_FOR_PHONES'] == 'Y' ? ' d-none d-sm-block' : '';
//    $arParams['ORDER']["ACCOUNT_NUMBER"] = $arResult["ORDER"]["ACCOUNT_NUMBER"];
    if ($arParams['BS_EFFECT'] == "fade")
        $arParams['BS_EFFECT'] = "slide carousel-fade";
//print_r($arResult);
    $frame = $this->createFrame()->begin("");
    ?>

    <? if ($arParams['PREVIEW'] == 'Y'): ?>
    <div id='tPreview' style="display:none;margin:auto;">
        <? endif; ?>

        <div id="carousel-<?= $arResult['ID'] ?>"
             class="carousel <?= $arParams['BS_EFFECT'] ?><?= $arParams['BS_HIDE_FOR_TABLETS'] ?>"
             data-interval="<?= $arParams['BS_INTERVAL'] ?>" data-wrap="<?= $arParams['BS_WRAP'] ?>"
             data-pause="<?= $arParams['BS_PAUSE'] ?>" data-keyboard="<?= $arParams['BS_KEYBOARD'] ?>"
             data-ride="carousel">


            <!--region Wrapper for slides -->
            <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                <div class="carousel-inner carousel-inner-main" role="listbox">
                    <div class="swiper-wrapper">
                        <? foreach ($arResult["BANNERS"] as $k => $banner): ?>

                            <div class="swiper-slide carousel-item-elems <? if ($k == 0) echo 'active'; ?>">
                                <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                                    <div class="link_banner" style="display:none;">
                                        <a href="javascript:void(0);">Новинка</a>
                                    </div>
                                <? } ?>
                                <?= $banner ?>
                            </div>
                        <? endforeach; ?>
                    </div>

                </div>
                <div class="carousel-indicators swiper-pagination-block"></div>
                <?
            } else {
                ?>
                <div class="carousel-inner" role="listbox">

                    <? foreach ($arResult["BANNERS"] as $k => $banner): ?>

                        <div class=" carousel-item-elems <? if ($k == 0) echo 'active'; ?>">
                            <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                                <div class="link_banner" style="display:none;">
                                    <a href="javascript:void(0);">Новинка</a>
                                </div>
                            <? } ?>
                            <?= $banner ?>
                        </div>
                    <? endforeach; ?>

                </div>
            <? } ?>
            <!--endregion-->

            <!-- region Controls -->
            <? if ($arParams['BS_ARROW_NAV'] == 'Y' || $arParams['PREVIEW'] == 'Y'): ?>
                <a href="#carousel-<?= $arResult['ID'] ?>"
                   class="carousel-nav-<?= $arParams['TYPE'] ?>-prev carousel_custom" role="button" data-slide="prev">
                    <span class="carousel_elem_custom" aria-hidden="true"><i class="fa fa-angle-left"
                                                                             aria-hidden="true"></i></span>
                </a>
                <a href="#carousel-<?= $arResult['ID'] ?>"
                   class="carousel-nav-<?= $arParams['TYPE'] ?>-next carousel_custom_next" role="button"
                   data-slide="next">
                    <span class="carousel_elem_custom" aria-hidden="true"><i class="fa fa-angle-right"
                                                                             aria-hidden="true"></i></span>
                </a>
            <? endif; ?>
            <!--endregion-->

            <!--region Indicators-->
            <?php if ($arParams['TYPE'] != 'MAIN') { ?>
                <? if ($arParams['BS_BULLET_NAV'] == 'Y' || $arParams['BS_PREVIEW'] == 'Y'): ?>
                    <div class="carousel-indicators">
                        <? $i = 0; ?>
                        <? while ($i < count($arResult['BANNERS'])): ?>
                            <span data-target="#carousel-<?= $arResult['ID'] ?>"
                                  data-slide-to="<?= $i ?>"  <? if ($i == 0) echo 'class="active"';
                            $i++ ?>></span>
                        <? endwhile; ?>
                    </div>
                <? endif; ?>
            <? } ?>

            <!--endregion-->
            <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                <script>

                    var swiper = new Swiper('.carousel-inner-main', {
                        slidesPerView: 1,
                        adaptiveHeight: true,
                        spaceBetween: 0,
                        speed: 1000,

                        autoplay: {
                            enabled: true,
                            delay: 1000,
                        },
                        pagination: {
                            el: '.swiper-pagination-block',
                            clickable: true
                        },
                        loop: true,
                        navigation: {
                            prevEl: '.carousel-nav-MAIN-prev',
                            nextEl: '.carousel-nav-MAIN-next'
                        },

                    });
                </script>
            <? }else{ ?>
                <script>
                    BX("carousel-<?=$arResult['ID']?>").addEventListener("slid.bs.carousel", function (e) {
                        var item = e.detail.curSlide.querySelector('.play-caption');
                        if (!!item) {
                            item.style.display = 'none';
                            item.style.left = '-100%';
                            item.style.opacity = 0;
                        }
                    }, false);
                    BX("carousel-<?=$arResult['ID']?>").addEventListener("slide.bs.carousel", function (e) {
                        var nodeToFixFont = e.target && e.target.querySelector(
                            '.carousel-item.active .bx-advertisingbanner-text-title[data-fixfont="false"]'
                        );
                        if (BX.type.isDomNode(nodeToFixFont)) {
                            nodeToFixFont.setAttribute('data-fixfont', 'true');
                            BX.FixFontSize.init({
                                objList: [{
                                    node: nodeToFixFont,
                                    smallestValue: 10
                                }],
                                onAdaptiveResize: true
                            });
                        }

                        var item = e.detail.curSlide.querySelector('.play-caption');
                        if (!!item) {
                            var duration = item.getAttribute('data-duration') || 500,
                                delay = item.getAttribute('data-delay') || 0;

                            setTimeout(function () {
                                item.style.display = '';
                                var easing = new BX.easing({
                                    duration: duration,
                                    start: {left: -100, opacity: 0},
                                    finish: {left: 0, opacity: 100},
                                    transition: BX.easing.transitions.quart,
                                    step: function (state) {
                                        item.style.opacity = state.opacity / 100;
                                        item.style.left = state.left + '%';
                                    },
                                    complete: function () {
                                    }
                                });
                                easing.animate();
                            }, delay);
                        }
                    }, false);
                    BX.ready(function () {
                        var tag = document.createElement('script');
                        tag.src = "";
                        var firstScriptTag = document.getElementsByTagName('script')[0];
                        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
                    });

                    function mutePlayer(e) {
                        e.target.mute();
                    }

                    function loopPlayer(e) {
                        if (e.data === YT.PlayerState.ENDED)
                            e.target.playVideo();
                    }

                    function onYouTubePlayerAPIReady() {
                        if (typeof yt_player !== 'undefined') {
                            for (var i in yt_player) {
                                window[yt_player[i].id] = new YT.Player(
                                    yt_player[i].id, {
                                        events: {
                                            'onStateChange': loopPlayer
                                        }
                                    }
                                );
                                if (yt_player[i].mute == true)
                                    window[yt_player[i].id].addEventListener('onReady', mutePlayer);
                            }
                            delete yt_player;
                        }
                    }
                </script>
            <? } ?>
        </div>
        <? if ($arParams['PREVIEW'] == 'Y'): ?>
    </div>
    <script>
        (function () {
            if (<?=$arParams['WIDTH']?> == 0) {
                BX('tPreview').style.width = top.cWidth / 2 + 'px';
                BX('tPreview').style.height = top.cWidth / 3.55 + 'px';
            } else if (top.cWidth / 2 > <?=$arParams['WIDTH']?>) {
                BX('tPreview').style.width = '<?=$arParams['WIDTH']?>px';
                BX('tPreview').style.height = '<?=$arParams['HEIGHT']?>px';
            } else {
                BX('tPreview').style.width = top.cWidth / 2 + 'px';
                BX('tPreview').style.height = top.cWidth / 3.55 + 'px';
            }
            document.body.style.backgroundColor = 'transparent';
            BX('tPreview').style.display = '';
        })();
    </script>
<? endif; ?>

    <? $frame->end(); ?>

<? endif; ?>
