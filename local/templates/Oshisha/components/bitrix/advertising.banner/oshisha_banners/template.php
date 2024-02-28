<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php if (count($arResult['BANNERS']) > 0): ?>

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

    <?php if ($arParams['PREVIEW'] == 'Y'): ?>
    <div id='tPreview' style="display:none;margin:auto;">
        <?php endif; ?>

        <div id="carousel-<?= $arResult['ID'] ?>"
             class="carousel w-full <?= $arParams['BS_EFFECT'] ?><?= $arParams['BS_HIDE_FOR_TABLETS'] ?>"
             data-interval="<?= $arParams['BS_INTERVAL'] ?>" data-wrap="<?= $arParams['BS_WRAP'] ?>"
             data-pause="<?= $arParams['BS_PAUSE'] ?>" data-keyboard="<?= $arParams['BS_KEYBOARD'] ?>"
             data-ride="carousel">

            <!--region Wrapper for slides -->
            <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                <div class="carousel-inner carousel-inner-main" role="listbox">
                    <div class="slick-wrap mb-0">
                        <?php foreach ($arResult["BANNERS"] as $k => $banner): ?>
                            <div class=" carousel-item-elems <?php if ($k == 0) echo 'active'; ?>">
                                <?= $banner ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="carousel-indicators absolute inset-x-0 top-full"></div>
            <?php } else { ?>
                <div class="carousel-inner w-full" role="listbox">

                    <?php foreach ($arResult["BANNERS"] as $k => $banner): ?>
                        <div class="carousel-item-elems w-full <?php if ($k == 0) echo 'active'; ?>
                        <?= $arParams['TYPE'] !== "BANNERS_HOME_1" ? ' rounded-lg' : '' ?>">
                            <?= $banner ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($arParams['BS_ARROW_NAV'] == 'Y' || $arParams['PREVIEW'] == 'Y'): ?>
                    <a href="#carousel-<?= $arResult['ID'] ?>"
                       class="carousel-nav-<?= $arParams['TYPE'] ?>-prev
                   carousel_custom absolute inset-y-0 -left-4 flex items-center z-5" role="button" data-slide="prev">
                    <span class="text-white hover:bg-lightGrayBg bg-light-red dark:bg-dark-red py-3 px-4 text-3xl rounded-full hover:text-dark">
                        <svg width="18" height="25" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 14.3333L1.33333 7.66667L8 1" stroke="white" stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                        </svg>
                    </span>
                    </a>
                    <a href="#carousel-<?= $arResult['ID'] ?>"
                       class="carousel-nav-<?= $arParams['TYPE'] ?>-next carousel_custom_next  absolute inset-y-0 -right-4 z-5
                   flex items-center" role="button" data-slide="next">
                    <span class="text-white hover:bg-lightGrayBg bg-light-red dark:bg-dark-red py-3 px-4 text-3xl rounded-full hover:text-dark">
                         <svg width="18" height="25" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 14.3333L7.66667 7.66667L1 1" stroke="white" stroke-width="2"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"/>
                         </svg>
                    </span>
                    </a>
                <?php endif; ?>
            <?php } ?>
            <!--endregion-->

            <!--region Indicators-->
            <?php if ($arParams['TYPE'] != 'MAIN') { ?>
                <?php if ($arParams['BS_BULLET_NAV'] == 'Y' || $arParams['BS_PREVIEW'] == 'Y'): ?>
                    <div class="carousel-indicators absolute inset-x-0 top-full">
                        <?php $i = 0; ?>
                        <?php while ($i < count($arResult['BANNERS'])): ?>
                            <span data-target="#carousel-<?= $arResult['ID'] ?>"
                                  data-slide-to="<?= $i ?>"  <?php if ($i == 0) echo 'class="active"';
                            $i++ ?>></span>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            <?php } ?>

            <!--endregion-->
            <?php if ($arParams['TYPE'] === 'MAIN') { ?>
                <script>

                    $('.slick-wrap').slick({
                        slidesToShow: 1,
                        arrows: true,
                        infinite: true,
                        autoplay: true,
                        autoplaySpeed: 5000,
                        dots: true,
                        prevArrow: '<span class="absolute inset-y-0 -left-4 flex items-center z-20 cursor-pointer ' +
                            'item-mobile-none"  aria-hidden="true">' +
                            '<span class="text-white hover:bg-lightGrayBg transition hover:transition bg-light-red' +
                            '  dark:bg-dark-red py-2.5 px-3.5 rounded-full hover:text-dark"> ' +
                            '<svg class="md:h-6 h-4 md:w-5 w-3.5" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg"> ' +
                            '<path d="M8 14.3333L1.33333 7.66667L8 1" stroke="white" stroke-width="2" stroke-linecap="round" ' +
                            'stroke-linejoin="round"/> </svg> </span></span>',
                        nextArrow: '<span class="absolute inset-y-0 -right-4 20 flex items-center cursor-pointer ' +
                            'item-mobile-none" aria-hidden="true">' +
                            '    <span class="text-white hover:bg-lightGrayBg transition bg-light-red dark:bg-dark-red' +
                            ' py-2.5 px-3.5 rounded-full hover:text-dark hover:transition"> ' +
                            '<svg class="md:h-6 h-4 md:w-5 w-3.5" viewBox="0 0 9 16" fill="none" xmlns="http://www.w3.org/2000/svg"> ' +
                            '<path d="M1 14.3333L7.66667 7.66667L1 1" stroke="white" stroke-width="2"' +
                            ' stroke-linecap="round" stroke-linejoin="round"/> </svg> </span></span>',
                    })
                </script>
            <?php }else{ ?>
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
            <?php } ?>
        </div>
        <?php if ($arParams['PREVIEW'] == 'Y'): ?>
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
<?php endif; ?>

    <?php $frame->end(); ?>

<?php endif; ?>
