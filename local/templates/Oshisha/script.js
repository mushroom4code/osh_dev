// COLOR TASTE
function tasteInit() {
    let box = $(document).find('.variation_taste');
    $.each(box, function (i, item) {
        $(item).find('span').each(
            function () {
                let classes = $(this).attr('class');
                if (classes.indexOf('taste') !== -1) {
                    let color = $(this).data('background');
                    $(this).css('background-color', color);
                    let str = '#';
                    if (parseInt(color.replace(str, ''), 16) > 0xffffff / 1.1
                        && color !== '#F55F5C' && color !== '#FF0E15' && color !== '#FF0F17' || color === '#9FFEB0' || color === '#CBF4FF') {
                        $(this).css('color', 'black');
                    } else {
                        $(this).css('color', 'white');
                    }
                }
            }
        );
    });
}

$(document).ready(function () {
    let div = $('div'),
        body = $('body'),
        inputItem = $('input'),
        href = window.location.pathname,
        screenWidth = window.screen.width,
        checkProductOrdersReady = $('#allProducts'),
        bool = $('span').is('.taste'),
        tasted = $(div).is('#VKUS');

    //BASKET
    let addToBasket = $(document).find('.add2basket').length !== 0 ? true : false,
        box_basket_elems = $('.basket-items-list').find('.basket-items-list-table'),
        arItemsForDB = [],
        product_data,
        bars = $('#basket-card'),
        line = $('#basket-line'),
        bool_basket = $(div).is('#basket-items-list-container');
    //CATALOG
    let checkInput = $(inputItem).is(checkProductOrdersReady),
        main_menu = $('.main_menu');
    // HIDE
    $(main_menu).hide();
    $('.content_for_box_delivery').hide();
    // SELECT
    let select = $('select'),
        select_sort_basket = ('.select_sort_basket'),
        bool_select_orders = $(select).is('#select_orders'),
        bool_select_order_return = $(select).is('#select_comments'),
        bool_select_contragent_user = $(select).is('#contragent_user'),
        bool_select_company_user_order = $(select).is('#company_user_order');

    var storageType = localStorage, consentPropertyName = 'cookie_consent';
    var saveToStorage = () => storageType.setItem(consentPropertyName, true);
    var consentPopup = document.getElementById('consent-cookie-popup');
    var consentAcceptBtn = document.getElementById('cookie-popup-accept');

    if (consentPopup !== null) {
        var shouldShow = true;
        if (consentPopup.classList.contains('js-noauth')) {
            shouldShow = !storageType.getItem(consentPropertyName) ? true : false;
        }

        var acceptFn = event => {
            event.preventDefault();
            saveToStorage(storageType);
            consentPopup.classList.add('hidden');
            setTimeout(() => {
                consentPopup.remove();
            }, 700);
            if (consentPopup.classList.contains('js-auth')) {
                $.ajax({
                    type: 'POST',
                    url: '/local/templates/Oshisha/include/addCookieConsent.php',
                    data: 'action=setConsent',
                    success: function (result) {
                        if (result == 'success') {
                        } else if (result == 'error') {
                            console.log(result);
                        } else if (result == 'noauth') {
                            console.log(result);
                        }
                    }
                });
            }
        }

        consentAcceptBtn.addEventListener('click', acceptFn);

        if (shouldShow) {
            setTimeout(() => {
                consentPopup.classList.remove('hidden');
            }, 2000);
        }
    }

    //MAIN
    function getCookie(name) {
        let matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }


    //ORDERS
    let parent_container_delivery = $('.bx-soa-pp-company-graf-container');

    $('.box_with_delivery_type').find(parent_container_delivery).each(function () {
        let parent = $(this).closest('.bx-soa-pp-company-parent');
        if (parent.length !== 0) {
            $(this).addClass('delivery_hive_parent');
            $(this).attr('style', 'width:auto;padding:5px;');
        }
    });

    if (bool_select_orders) {
        $('#select_orders').select2();
    }
    if (bool_select_order_return) {
        $('#select_comments').select2();
    }
    if (bool_select_contragent_user) {
        $('#contragent_user').select2();
    }
    if (bool_select_company_user_order) {
        $('#company_user_order').select2();
    }
    let boot_datepicker = $(inputItem).is('.datepicker');

    if ($(select).is('.select_sort_basket')) {

        $(select_sort_basket).select2({
            minimumResultsForSearch: -1
        });
    }
    if (boot_datepicker) {
        $("input.datepicker").datepicker();
    }
    if ($(inputItem).is('#main-profile-day')) {
        let data = new Date(),
            newDate = String(data.getFullYear() - 17),
            inputPicker = $('input#main-profile-day');
        $(inputPicker).datepicker({
            maxDate: new Date(newDate),
            altField: inputPicker,
        });
    }

    function setPriceGenerate(elem, value) {
        if ($('.ganerate_price').length > 0 && $(elem).closest('.box_with_photo_product').length > 0) {
            $('.ganerate_price').text(getPriceForProduct(elem) * value + ' ₽');
        }
    }

    function getPriceForProduct(that) {
        if (that.closest('.bx_catalog_item_container') === null) {
            return parseInt($('.product-item-detail-price-current').text().match(/[\d]/gi).join(''));
        }
    }


    if (bool === true) {
        $(document).on('click', '.js__taste_toggle', function (e) {
            $(this).closest('.js__tastes').toggleClass('active');
            let cardWrapper = $(e.target).closest('.bx_catalog_item_container'),
                tasteOverlay = cardWrapper.find('.bx_catalog_item_overlay'),
                priceBox = cardWrapper.find('.info-prices-box-hover');

            if ($(this).closest('.js__tastes').hasClass('active')) {
                tasteOverlay.css({height: '100%'});
                priceBox.css({zIndex: '791'});
            } else {
                tasteOverlay.css({height: '0'});
                priceBox.css({zIndex: '791'});
            }
        })
        tasteInit();
    }

    if (tasted === true) {
        $(this).find("input.check_input:checked").each(
            function () {
                let text_taste = $(this).closest('div.form-check').find('label').attr('id');
                let code = $(this).closest('div.form-check').find('label').attr('for');
            }
        );
    }

    $(document).on('click', '.js__show-all-prices', function () {
        const showButton = $(this),
            listHeader = showButton.closest('.info-prices-box-hover'),
            listWrap = listHeader.find('.js__all-prices'),
            priceList = listWrap.find('.prices-block'),
            yDelta = priceList.outerHeight();

        if (listWrap.height() !== 0) {
            listHeader.stop().animate({bottom: 0}, 600);
            listWrap.stop().animate({height: 0}, 600, function () {
                showButton.css({borderRadius: 0}).find('span').text('Показать цены');
            });

        } else {
            showButton.find('span').text('Скрыть цены').css({borderRadius: '0 0 10px 10px'});
            listHeader.stop().animate({bottom: yDelta}, 800);
            listWrap.stop().animate({height: yDelta}, 800);
        }
        listHeader.toggleClass('active');
        return false;
    })


    $('.link_header_catalog').on('click', function () {
        $('#MenuHeader .Icon').click();
        return false;
    });
    $('.Icon').on('click', function () {
        if ($('.header_top').hasClass('show')) {
            $('header').removeAttr('style');
            $('.Icon').addClass('open');
            $('body').css('overflow', 'initial');
        } else {
            $('body').css('overflow', 'hidden');
            $('.box_filter_catalog').hide();
            $('.Icon').removeClass('open');
        }
    });

    // SORT ACTIVE
    function addDeleteClassSortElems() {
        let bool_bar = $('div.basket-items-list-table').hasClass('categoryColumn');
        if (bool_bar) {
            $('.icon_sort_line_active').addClass('icon_sort_line').removeClass('icon_sort_line_active');
            $(bars).addClass('icon_sort_bar_active').removeClass('icon_sort_bar');
        } else {
            $('.icon_sort_bar_active').addClass('icon_sort_bar').removeClass('icon_sort_bar_active');
            $(line).addClass('icon_sort_line_active').removeClass('icon_sort_line');
        }
    }

    if (bool_basket) {
        addDeleteClassSortElems();
    }

    if ($(div).is('.bx-soa-customer-field')) {
        $('.bx-soa-customer-field input[data-name="FIO"]').attr('placeholder', 'Иванов Иван Иванович');
        $('.bx-soa-customer-field input[data-name="EMAIL"]').attr('placeholder', 'Не указана');
    }
    //BASKET
    $('.product-item-amount').on('input', function () {
        $(this).val($(this).val().replace(/[A-Za-zА-Яа-яЁё]/, ''))
    });

    function changePrice() {
        let value = parseInt($('.card_element').val()),
            maxValue = parseInt($('.btn-plus').attr('data-max-quantity')),
            minValue = 0;
        if (value < minValue) {
            value = minValue
            $('.card_element').val(minValue)
        } else if (value > maxValue) {
            value = maxValue;
            $('.card_element').val(maxValue);
            $('.alert_quantity').html('К покупке доступно максимум: <b> ' + maxValue + ' шт.</b>')
                .toggleClass('hidden');

        }
        if (value > 0) {
            $('.ganerate_price_wrap').show();
        }
        setPriceGenerate(this, value)
    }

    $(document).on('keypress', '.card_element', function (e) {
        if (e.which === 13) {
            clearTimeout(window.addToBasketEventTimeout);
            changePrice.call(this);
            addToBasketEvent.call(this);
        }
    })

    $(document).on('input', '.card_element', function () {
        let cardBasketAddButton = $(this).parent().parent().parent();
        if (cardBasketAddButton.hasClass('bx_catalog_item_controls')) {
            cardBasketAddButton = cardBasketAddButton.find('a.add2basket:not(.btn-plus):not(.btn-minus)');
        }
        if ($(cardBasketAddButton).is('.basket_prod_detail')) {
            if ($(cardBasketAddButton).hasClass('addProductDetailButton')) {
                $(cardBasketAddButton).prop('onclick', null).off('click');
                $(cardBasketAddButton).addClass('btn_basket').removeClass('addProductDetailButton').fadeIn(100);
            }
        }

        clearTimeout(window.addToBasketEventTimeout);
        window.addToBasketEventTimeout = setTimeout(() => {
            addToBasketEvent.call(this);
        }, 3000);
    })

    $(document).on('click', '.js-add2basket-gift', function () {
        const product_id = $(this).attr('data-product_id');
        product_data = {
            'ID': product_id,
            'QUANTITY': 1,
        };

        $.ajax({
            type: 'POST',
            url: '/local/templates/Oshisha/include/add2basket.php',
            data: 'product_data=' + JSON.stringify(product_data),
            success: function () {
                location.href = BX.message('BASKET_URL');
            }
        });
    })

    if (addToBasket === true) {
        setInterval(() => sendArrayItems(arItemsForDB), 500);

        function addToBasketEvent() {
            function appendLoader() {
                $('.spanBasketTop').text('').attr('style', 'padding: 6px;top:0;left:0;').append('' +
                    '<div class="p-3 bg-light-red rounded-full">' +
                    '<div class="loader h-full rounded-full absolute">' +
                    '<div class="inner one h-full rounded-full absolute w-full"></div>' +
                    '<div class="inner two h-full rounded-full absolute w-full">' +
                    '</div> <div class="inner three h-full rounded-full absolute w-full"></div></div></div>');
            }

            if (!$('span').is('.spanBasketTop')) {
                $('.basket_top').append('<span class="spanBasketTop relative"></span>' +
                    '<span class="font-12 font-weight-bold price_basket_top"></span>');
            }
            appendLoader();
            let product_id = $(this).attr('data-product_id'),
                product_url = $(this).attr('data-url'),
                box_with_product = $(this).closest('.bx_catalog_item').find('div#result_box'),
                quantityProdDet = $(this).closest('div').find('input.product-item-amount').val(),
                box_with_products_order = $(this).closest('.bx_catalog_items').find('div#result_box'),
                boxInput = $(this).closest('.bx_catalog_item_controls').find('input.card_element'),
                plus = $(this).hasClass('btn-plus'),
                minus = $(this).hasClass('btn-minus'),
                max_QUANTITY = parseInt($(this).attr('data-max-quantity'));

            if (plus === true) {
                if (parseInt($(boxInput).val()) < max_QUANTITY) {
                    let beforeVal = parseInt($(boxInput).val()) + 1;
                    $(boxInput).val(beforeVal);

                    if (beforeVal > 0)
                        $('.ganerate_price_wrap').show();
                    else
                        $('.ganerate_price_wrap').hide();

                    setPriceGenerate(this, beforeVal);

                    product_data = {
                        'ID': product_id,
                        'QUANTITY': beforeVal,
                        'URL': product_url,
                    };

                } else {
                    $(boxInput).val(max_QUANTITY);
                    if (max_QUANTITY > 0) {
                        $('.ganerate_price_wrap').show();
                        $('.alert_quantity[data-id="' + product_id + '"]')
                            .html('К покупке доступно максимум:  <b>' + max_QUANTITY + '&nbsp;шт.</b>')
                            .toggleClass('hidden')
                            .append('<div class="close-count-alert js__close-count-alert">' +
                                '<span class="absolute -right-2 -top-2 cursor-pointer" ' +
                                'onclick="$(this).closest(\'div.alert_quantity\').toggleClass(\'hidden\')">' +
                            '<svg width="25" height="25" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                            '<path opacity="0.7" d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z" fill="#676767"></path>' +
                            '<path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z" fill="white"></path>' +
                            '</svg>' +
                            '</span></div>');
                    } else
                        $('.ganerate_price_wrap').hide();

                    setPriceGenerate(this, max_QUANTITY);

                    product_data = {
                        'ID': product_id,
                        'QUANTITY': max_QUANTITY,
                        'URL': product_url,
                    };
                }
                if ($(this).hasClass('red_button_cart') && !$(this).hasClass('offer-box')) {
                    $(this).hide();
                    $('.product-item-amount-field-contain-wrap[data-product_id="' + product_id + '"]').css({'display': 'flex'});
                }
            } else if (minus === true) {
                $('.alert_quantity[data-id="' + product_id + '"]').html('').removeClass('show_block').append('<div class="close-count-alert js__close-count-alert"></div>');

                if (parseInt($(boxInput).val()) > 0) {
                    let beforeVal = parseInt($(boxInput).val()) - 1;
                    $(boxInput).val(beforeVal);

                    if (beforeVal > 0)
                        $('.ganerate_price_wrap').show();
                    else
                        $('.ganerate_price_wrap').hide();

                    setPriceGenerate(this, beforeVal)

                    product_data = {
                        'ID': product_id,
                        'QUANTITY': beforeVal,
                        'URL': product_url,
                    };

                    if (beforeVal == 0 && $(this).closest('.info-prices-box-bottom').length === 0) {

                        $('.red_button_cart[data-product_id="' + product_id + '"]').show();
                        $('.product-item-amount-field-contain-wrap[data-product_id="' + product_id + '"]').hide();
                    }
                }
            } else {
                let addBasketButton = $(this).closest('.bx_catalog_item_controls').find('.add2basket'),
                    product_id = addBasketButton.data('product_id'),
                    product_url = addBasketButton.data('url');

                if (quantityProdDet) {
                    let quantity = parseInt(quantityProdDet);
                    if ((quantity > 1) || (quantity !== 0)) {
                        product_data = {'QUANTITY': quantity, 'URL': product_url, 'ID': product_id};
                        $(boxInput).val(quantity);
                        if (quantity > max_QUANTITY) {
                            $('.alert_quantity[data-id="' + product_id + '"]').
                            html('К покупке доступно максимум: <b> ' + max_QUANTITY + '&nbsp;шт.</b>')
                                .toggleClass('hidden').append('<div class="close-count-alert js__close-count-alert">' +
                                '<span class="absolute -right-2 -top-2 cursor-pointer" ' +
                                'onclick="$(this).closest(\'div.alert_quantity\').toggleClass(\'hidden\')">' +
                                '<svg width="25" height="25" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                '<path opacity="0.7" d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z" fill="#676767"></path>' +
                                '<path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z" fill="white"></path>' +
                                '</svg>' +
                                '</span></div>');
                        } else {
                            $('.alert_quantity[data-id="' + product_id + '"]').html('')
                                .toggleClass('hidden').append('<div class="close-count-alert js__close-count-alert">' +
                                '<span class="absolute -right-2 -top-2 cursor-pointer" ' +
                                'onclick="$(this).closest(\'div.alert_quantity\').toggleClass(\'hidden\')">' +
                                '<svg width="25" height="25" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                                '<path opacity="0.7" d="M55 30C55 43.807 43.807 55 30 55C16.1929 55 5 43.807 5 30C5 16.1929 16.1929 5 30 5C43.807 5 55 16.1929 55 30Z" fill="#676767"></path>' +
                                '<path d="M22.4242 22.4242C23.1564 21.6919 24.3436 21.6919 25.0757 22.4242L30 27.3485L34.9242 22.4242C35.6565 21.692 36.8435 21.692 37.5757 22.4242C38.308 23.1564 38.308 24.3436 37.5757 25.076L32.6517 30L37.5757 34.924C38.308 35.6562 38.308 36.8435 37.5757 37.5757C36.8435 38.308 35.6562 38.308 34.924 37.5757L30 32.6517L25.076 37.5757C24.3436 38.308 23.1564 38.308 22.4242 37.5757C21.692 36.8435 21.692 35.6565 22.4242 34.9242L27.3485 30L22.4242 25.0757C21.6919 24.3436 21.6919 23.1564 22.4242 22.4242Z" fill="white"></path>' +
                                '</svg>' +
                                '</span></div>')
                        }
                    } else {
                        product_data = {'QUANTITY': 1, 'URL': product_url, 'ID': product_id};
                        $(boxInput).val(1);
                    }
                } else {
                    product_data = {'QUANTITY': 1, 'URL': product_url, 'ID': product_id};
                    $(boxInput).val(1);
                }
            }

            let detailCardBasketAddButton = $('a.add2basket:not(.btn-plus):not(.btn-minus)[data-product_id="' + product_id + '"]');
            if ($(detailCardBasketAddButton).is('.basket_prod_detail')) {
                if (product_data.QUANTITY !== '' && parseInt(product_data.QUANTITY) !== 0 && parseInt(product_data.QUANTITY) > 0) {
                    if (!$(detailCardBasketAddButton).hasClass('addProductDetailButton')) {
                        $(detailCardBasketAddButton).attr({'onclick': "location.href='/personal/cart/'"});
                        $(detailCardBasketAddButton).removeClass('btn_basket').addClass('addProductDetailButton').fadeIn(100);
                    }
                } else {
                    if ($(detailCardBasketAddButton).hasClass('addProductDetailButton')) {
                        $(detailCardBasketAddButton).prop('onclick', null).off('click');
                        $(detailCardBasketAddButton).addClass('btn_basket').removeClass('addProductDetailButton').fadeIn(100)
                    }
                }
            }

            // //  OFFERS &&  UPDATE quantity product fast modal or product card in catalog
            let basketItem = $(boxInput).val();
            let boxUpdateAfterAppend = $(document).find('.catalog-item-product[data-product_id="' + product_id + '"]');
            let parseUpdate = [], boxUpdate;
            let productDef = $(this).closest('.catalog-item-product').hasClass('not-input-parse');

            if (!boxUpdateAfterAppend.hasClass('catalog-fast-window')) {
                if (!productDef && $(boxUpdateAfterAppend).find('.product-values').val() !== undefined) {
                    parseUpdate = JSON.parse($(boxUpdateAfterAppend).find('.product-values').val());
                }
                boxUpdate = $(boxUpdateAfterAppend).closest('.product-item-container');
            } else {
                if (!productDef) {
                    parseUpdate = JSON.parse(
                        $(document).find('.catalog-item-product[data-product="' + $(boxUpdateAfterAppend).attr('data-product') + '"]')
                            .find('.product-values').val());
                }
                boxUpdate = $(document).find('div[data-product="' + $(boxUpdateAfterAppend).attr('data-product') + '"]');
            }

            if (!productDef) {
                parseUpdate.ACTUAL_BASKET = basketItem || 1;
                $(boxUpdate).find('.product-values').val(JSON.stringify(parseUpdate));
            }

            $(boxUpdate).find('.product-item-amount-field-contain-wrap[data-product_id="' + product_id + '"] .card_element').val(basketItem);
            // //  OFFERS &&  UPDATE quantity product fast modal or product card in catalog

            $(box_with_product).empty();
            $(box_with_products_order).empty();

            const price = $(document).find('.sum-box[data-product-id="' + product_data.ID + '"]').attr('data-price') || 0;
            if (price !== null) {
                $(document).find('.sum-box[data-product-id="' + product_data.ID + '"]')
                    .text((parseInt(product_data.QUANTITY) * parseInt(price)) + '₽');
            }
            addItemArrayANDSend(product_data);
        }

        $(document).on('click', '.add2basket', function () {
            clearTimeout(window.addToBasketEventTimeout);
            addToBasketEvent.call(this);
        });


        function deleteBasketItemTop(result) {
            if (result.QUANTITY !== '' && result.QUANTITY !== 0) {
                $('.spanBasketTop').attr('style', 'padding: 3px 6px;').text(result.QUANTITY);
                $('.price_basket_top').text(result.SUM_PRICE + ' ₽');
            } else {
                $('.spanBasketTop').remove();
                $('.price_basket_top').remove();
            }
        }

        function addItemArrayANDSend(arItem) {
            let currentKey = false;

            $(arItemsForDB).each(function (key, itemVal) {
                if (itemVal.ID === arItem.ID) {
                    itemVal.TIME = 2;
                    itemVal.QUANTITY = arItem.QUANTITY;
                    itemVal.PRICE = arItem.PRICE;
                    currentKey = true;
                }
            });
            if (currentKey === false) {
                arItem.TIME = 2;
                arItemsForDB.push(arItem);
            }

        }

        function sendArrayItems(ItemArray) {
            let product_data = [], new_time, time;

            if (ItemArray.length !== 0) {
                $(ItemArray).each(function (key, itemVal) {
                    if (itemVal.TIME === 0) {

                        product_data = {
                            'ID': itemVal.ID,
                            'QUANTITY': itemVal.QUANTITY,
                            'URL': itemVal.URL,
                            'TIME': itemVal.TIME,
                        };

                        $.ajax({
                            type: 'POST',
                            url: '/local/templates/Oshisha/include/add2basket.php',
                            data: 'product_data=' + JSON.stringify(product_data),
                            success: function (result) {
                                if (result.STATUS === 'success') {
                                    deleteBasketItemTop(result);
                                }
                            }
                        });
                        $(arItemsForDB).each(function (i, val) {
                            if (val.ID === product_data.ID) {
                                arItemsForDB.splice(i, 1)
                            }
                        });
                    } else {
                        time = itemVal.TIME;
                        new_time = time !== 0 ? (time - 1) : 0;
                        product_data = {
                            'ID': itemVal.ID,
                            'QUANTITY': itemVal.QUANTITY,
                            'URL': itemVal.URL,
                            'TIME': new_time,
                        };
                        $(arItemsForDB).each(function (i, val) {
                            if (val.ID === product_data.ID) {
                                val.QUANTITY = product_data.QUANTITY;
                                val.TIME = product_data.TIME;
                            }
                        })
                    }
                });
            }
        }

    }

    $(document).on('click', '.detail_popup', function () {
        let popup_mess = $(this).closest('.bx_catalog_item_controls').find('div#popup_mess');
        $(".box_with_message_prodNot").hide(500).remove();
        if ($(this).hasClass('subscribed')) {
            $(popup_mess).append('<div class=" box_with_message_prodNot relative bg-textDarkLightGray w-full dark:bg-grayButton ' +
                ' rounded-lg px-4 py-5 shadow-lg text-dark text-xs text-center dark:text-textDarkLightGray' +
                ' flex flex-col items-center" > ' +
                '<svg width="20" height="20" viewBox="0 0 24 24" class="mb-3 stroke-light-red dark:stroke-white" ' +
                'fill="none" xmlns="http://www.w3.org/2000/svg">' +
                '<circle cx="12" cy="12" r="11" stroke-width="2"/>' +
                '<line x1="12" y1="11" x2="12" y2="18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<line x1="12" y1="7" x2="12" y2="6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>' +
                '<p class="mb-3 dark:font-light font-normal">' +
                'К сожалению, товара нет в наличии.<br> ' +
                'Вы уже подписаны на товар, вас уведомят когда товар появится в наличии.<br></p>' +
                '<a href="javascript:void(0);" id="yes_mess" class="link_message_box_product font-normal ' +
                'rounded-md text-white w-full text-xs flex flex-row items-center justify-center' +
                ' px-7 py-2 dark:shadow-md shadow-shadowDark bg-dark cursor-pointer">' +
                '<svg width="18" class="mr-2" height="22" viewBox="0 0 32 36" fill="none"  xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M26 11.3334C26 8.68121 24.9464 6.13767 23.0711 4.26231C21.1957 2.38694 18.6522 1.33337 16 1.33337C13.3478 1.33337 10.8043 2.38694 8.92893 4.26231C7.05357 6.13767 6 8.68121 6 11.3334C6 23 1 26.3334 1 26.3334H31C31 26.3334 26 23 26 11.3334Z" ' +
                'stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M18.8834 33C18.5903 33.5051 18.1698 33.9244 17.6637 34.2159C17.1577 34.5073 16.584 34.6608 16 34.6608C15.4161 34.6608 14.8423 34.5073 14.3363 34.2159C13.8303 33.9244 13.4097 33.5051 13.1167 33" ' +
                'stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>Отменить подписку</a>' +
                '<span class="close_photo absolute right-1 top-1 cursor-pointer" id="close_photo">' +
                '<svg width="25" height="25" viewBox="0 0 30 30" ' +
                'fill="none" xmlns="http://www.w3.org/2000/svg" class="stroke-dark dark:stroke-white">' +
                '<path d="M8.75 21.25L21.1244 8.87561"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M8.75 8.75L21.1244 21.1244"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg></span></div>').show();
        } else if ($(this).hasClass('noauth')) {
            $(popup_mess).append('<div class="bg-textDarkLightGray dark:bg-grayButton rounded-lg p-4 ' +
                'shadow-lg text-dark text-xs font-normal dark:text-textDarkLightGray box_with_message_prodNot" > ' +
                '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
                'К сожалению, товара нет в наличии. <br>Мы можем уведомить вас, когда он снова появиться.<br>' +
                'Авторизуйтесь для подписки на товар</p>' +
                '<span class="close_photo" id="close_photo"></span></div>').show();
        } else {
            $(popup_mess).append('<div class="bg-textDarkLightGray w-full relative dark:bg-grayButton rounded-lg px-4 py-5 ' +
                'shadow-lg text-dark text-xs text-center dark:text-textDarkLightGray flex flex-col items-center box_with_message_prodNot" > ' +
                '<svg width="20" height="20" viewBox="0 0 24 24" class="mb-3 stroke-light-red dark:stroke-white" ' +
                'fill="none" xmlns="http://www.w3.org/2000/svg">' +
                '<circle cx="12" cy="12" r="11" stroke-width="2"/>' +
                '<line x1="12" y1="11" x2="12" y2="18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<line x1="12" y1="7" x2="12" y2="6"  stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>' +
                '<p class="mb-3 dark:font-light font-normal">К сожалению, товара нет в наличии. ' +
                '<br>Мы можем уведомить вас, когда он снова появиться.<br>' +
                '</p>' +
                '<a href="javascript:void(0);" id="yes_mess" class="link_message_box_product font-normal ' +
                'rounded-md text-white w-full text-xs flex flex-row items-center justify-center' +
                ' px-7 py-2 dark:shadow-md shadow-shadowDark dark:bg-greenButton bg-light-red cursor-pointer ">' +
                '<svg width="18" class="mr-2" height="22" viewBox="0 0 32 36" fill="none" xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M26 11.3334C26 8.68121 24.9464 6.13767 23.0711 4.26231C21.1957 2.38694 18.6522 1.33337 16 1.33337C13.3478 1.33337 10.8043 2.38694 8.92893 4.26231C7.05357 6.13767 6 8.68121 6 11.3334C6 23 1 26.3334 1 26.3334H31C31 26.3334 26 23 26 11.3334Z" ' +
                'stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M18.8834 33C18.5903 33.5051 18.1698 33.9244 17.6637 34.2159C17.1577 34.5073 16.584 34.6608 16 34.6608C15.4161 34.6608 14.8423 34.5073 14.3363 34.2159C13.8303 33.9244 13.4097 33.5051 13.1167 33" ' +
                'stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>Уведомить меня</a>' +
                '<span class="close_photo absolute right-1 top-1 cursor-pointer" id="close_photo"><svg width="25" height="25" viewBox="0 0 30 30" ' +
                'fill="none" xmlns="http://www.w3.org/2000/svg" class="stroke-dark dark:stroke-white">' +
                '<path d="M8.75 21.25L21.1244 8.87561"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M8.75 8.75L21.1244 21.1244"  stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg></span></div>').show();
        }
        $('#close_photo').on('click', function () {
            $(".box_with_message_prodNot").hide(500).remove()
        });
        $('a#yes_mess').on('click', function () {
            var popup_mess = $(this).closest('div#popup_mess');
            var product_id = $(this).closest('div#popup_mess').attr('data-product_id');
            var product_name = $(this).closest('div.item-product-info').find('a.bx_catalog_item_title').text().trim();
            if ($(this).closest('div#popup_mess').hasClass('subscribed')) {
                var subscribe = "N";
                var subscription_id = popup_mess.attr('data-subscription_id');
            } else {
                var subscribe = "Y";
                var subscription_id = "N";
            }
            $.ajax({
                type: 'POST',
                url: '/local/templates/Oshisha/components/bitrix/catalog.product.subscribe/oshisha_catalog.product.subscribe/ajax.php',
                data: {
                    subscribe: subscribe,
                    item_id: product_id,
                    product_name: product_name,
                    subscription_id: subscription_id
                },
                success: function (result_jsn) {
                    var result = JSON.parse(result_jsn);
                    if (result.success === true) {
                        var item_controls = popup_mess.parent();
                        if (result.clickDbError != 'false') {
                            console.log('error while updating productsSubscriptionsTable');
                            console.log(result.clickDbError);
                        }
                        if (result.message === "subscribed") {
                            popup_mess.addClass('subscribed');
                            popup_mess.attr('data-subscription_id', result.subscribeId);
                            item_controls.find('.detail_popup').addClass('subscribed');
                            item_controls.find('.fa-bell-o').addClass('filled');
                            popup_mess.empty();
                        } else if (result.message === "unsubscribed") {
                            popup_mess.removeClass('subscribed');
                            popup_mess.removeAttr('data-subscription_id');
                            item_controls.find('.detail_popup').removeClass('subscribed');
                            item_controls.find('.fa-bell-o').removeClass('filled');
                            popup_mess.empty();
                        }
                    } else if (result.success == false) {
                        if (result.message === "noauth") {
                            popup_mess.empty();
                            popup_mess.append('<div class="d-flex flex-column align-items-center box_with_message_prodNot" > ' +
                                '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
                                'Для того чтобы получать уведомления вам нужно авторизоваться</p>' +
                                '<span class="close_photo" id="close_photo"></span></div>');
                            popup_mess.find('#close_photo').on('click', function () {
                                $(".box_with_message_prodNot").hide(500).remove()
                            });
                        } else if (result.message === "noemail") {
                            popup_mess.empty();
                            popup_mess.append('<div class="d-flex flex-column align-items-center box_with_message_prodNot" > ' +
                                '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
                                'Для того чтобы получать уведомления вам нужно указать почту в настройках профиля</p>' +
                                '<span class="close_photo" id="close_photo"></span></div>');
                            popup_mess.find('#close_photo').on('click', function () {
                                $(".box_with_message_prodNot").hide(500).remove()
                            });
                        }
                    }
                }
            });
            $('#close_photo').on('click', function () {
                $(".box_with_message_prodNot").hide(500).remove()
            });
        });
    });

    $('.switch-btn').on('click', function () {
        $(this).toggleClass('switch-on');
    });

    $(document).on('click', '.btn-plus', function () {
        let input = $('input.product-item-amount').val(),
            popup_mess = $(this).closest('div.bx_catalog_item').find('div#popup_mess'),
            classes = $(this).hasClass('product-item-amount-field-btn-disabled');
        $(popup_mess).hide();
        $(popup_mess).empty();
        if (classes === true && input !== '0') {
            $(popup_mess).append('<div class="d-flex flex-row align-items-center box_with_message_prodNot"' +
                ' style="top: 82%;padding: 18px;" ><i class="fa fa-info-circle" style="margin: 0 10px 0 0" ' +
                'aria-hidden="true"></i><p style="margin: 0">К сожалению, товар на складе закончился.</p>' +
                '</div>').show(300);
            setTimeout(function () {
                $(".box_with_message_prodNot").hide(300).remove()
            }, 2000);
        }
    });

// CATALOG
    if ($(div).is('.bx-basket')) {
        $(document).on('click', '.btn_basket_collapse', function () {
            let box = $(this).closest('.box').find('.category'),
                attr = $(box).hasClass('collapse_hide');
            if (attr === true) {
                $(box).hide().removeClass('collapse_hide').show(300);
                $(this).find('i').attr('style', 'transform:rotate(180deg)');
            } else {
                $(box).hide(300).addClass('collapse_hide');
                $(this).find('i').attr('style', 'transform:rotate(0deg)');
            }

        })
    }
    $(document).on('click', '.btn_questions', function () {
        let attr = $(this).attr('aria-expanded');
        if (attr === 'true') {
            let boolShow;
            if ($(document).find('i[style="transform:rotate(180deg)"]').length > 1) {
                $(document).find('i[style="transform:rotate(180deg)"]').each(function () {
                    boolShow = $(this).closest('.box').find('.collapse').hasClass('show');
                    if (!boolShow) {
                        $(this).removeAttr('style');
                    }
                });
            } else {
                boolShow = $(document).find('i[style="transform:rotate(180deg)"]').closest('.box').find('.collapse').hasClass('show');
                if (!boolShow) {
                    $(document).find('i[style="transform:rotate(180deg)"]').removeAttr('style');
                }
            }
            $(this).find('i').attr('style', 'transform:rotate(180deg)');
        } else {
            $(this).find('i').attr('style', 'transform:rotate(0deg)');
        }
    });
    if (checkInput === true) {
        let check = $(checkProductOrdersReady).prop('checked');
        if (check === true) {
            $('div.box_product').find('div.box_check_product').each(
                function () {
                    $(this).find('input.input_check_product').attr('checked', 'checked');
                    $(this).find('input.input_check_product').prop('checked', 'checked');
                }
            )
        }

        $('.check_input_edit').on('click', function () {
            let idProp = $(this).attr('id');
            if (idProp === 'allProducts') {
                $('div.box_product').find('div.box_check_product').each(
                    function () {
                        $(this).find('input.input_check_product').attr('checked', 'checked');
                        $(this).find('input.input_check_product').prop('checked', 'checked');
                    }
                )
            } else if (idProp === 'small') {
                $('div.box_product').find('div.box_check_product').each(
                    function () {
                        $(this).find('input.input_check_product').prop('checked', '');
                        $(this).find('input.input_check_product').removeAttr('checked');
                    }
                )
            }
        });
    }

    $('.box_with_menu_header').find('.ul_menu').find('li.li_menu_header').each(
        function () {
            let link_href = $(this).find('a');
            if (href === $(link_href).attr('href')) {
                $(link_href).find('span').attr('style', 'color:#F55F5C');
            }
        }
    );

//   LK

    function hideShow(that) {
        let boolShow = $(that).attr('data-hide');
        if (boolShow === 'true') {
            $(that).hide(200);
            $(that).attr('data-hide', 'false');
            $(that).addClass('hides_box');
        } else {
            $(that).show(200);
            $(that).attr('data-hide', 'true');
            $(that).removeClass('hides_box');
        }

    }

    if (screenWidth >= 300 && screenWidth <= 1024) {
        $('.foot_container').find('.box_footer_js').each(
            function () {
                hideShow(this);
            }
        )
    }

    $('.visuallyHidden').on('click', function () {
        let element = $(this).attr('id');
        $('.foot_container').find('.box_footer_js').each(
            function () {
                let id = $(this).attr('data-id');
                if (id === element) {
                    hideShow(this);
                }
            }
        )
    })

// LOCATIONS LIST START
// Список городов для выбора местоположения
    let all_cities = $('#cities-list'),
        big_cities = $('#big-cities-list');
    $("#city-search").keyup(function () {
        all_cities.show();
        big_cities.hide();
        let length = $(this).val();
        if (length.length === 0) {
            all_cities.hide();
            big_cities.show();
        }
        if (all_cities.is(':empty')) {
            $('#choose-city-btn').attr('disabled', 'disabled');
            big_cities.show();
        }
    });
    $('.city-item').each(function () {
        $(this).click(function () {
            let city_selected = $(this).text();
            $('#city-search').val(city_selected);
            $('#choose-city-btn').removeAttr('disabled');
            all_cities.hide();
        });
    });

// list.js init

    new List('locations', {
        valueNames: ['city-item']
    })

    $('.sort').on('click', function () {
        let basketItems = BX.namespace('BX.Sale.BasketComponent'),
            classes = $(this).attr('data-sort'),
            sort = false;
        if (basketItems !== undefined) {
            if (classes === 'grid') {
                basketItems.listTemplate = 'grid';
                sort = true;
                $(box_basket_elems).attr('style', 'display:flex;flex-wrap:wrap;justify-content: flex-start;')
            } else {
                basketItems.listTemplate = 'line';
                $(box_basket_elems).attr('style', '');
            }
        }
        $(box_basket_elems).each(
            function () {
                if (sort) {
                    $(this).addClass('categoryColumn');
                } else {
                    $(this).removeClass('categoryColumn');
                }
                $(this).find('.basket-items-list-item-container').each(function () {
                    basketItems.redrawBasketItemNode(this.getAttribute('data-id'));
                    tasteInit();
                })
            }
        );
        addDeleteClassSortElems();
    });

// CATALOG

    let active_sort_catalog = $(div).is('.box_with_prod');
    if (active_sort_catalog) {
        if (getCookie('orientation') === 'line') {
            $('.icon_sort_line').addClass('icon_sort_line_active').removeClass('icon_sort_line');
        } else {
            $('.icon_sort_bar').addClass('icon_sort_bar_active').removeClass('icon_sort_bar');
        }
    }

    $('.sort_order').on('click', function () {
        $('.sort_orders_element').toggle();
        return false;
    });

    $('.sort_orders').on('click', function () {
        if (!$(this).hasClass('active')) {
            $(this).addClass('active');
            $(this).css('border-radius', '10px 10px 0 0');
            $(this).find('i').css('transform', 'rotate(180deg)');
            $(this).find('.sort_orders_elements').show();
        } else {
            $(this).removeClass('active');
            $(this).find('.sort_orders_elements').hide();
            $(this).css('border-radius', '10px');
            $(this).find('i').css('transform', 'rotate(0)');
        }
    });

    $('.order_sort_item').on('click', function () {
        $(this).closest('.sort_orders').find('.sort_orders_by').text($(this).text());
        let typeSort = $(this).attr('data-sort-order');
        let sortStatus = $(this).closest('.sort_orders_elements').attr('data-sort-status');
        $.ajax({
            url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort.php',
            type: 'POST',
            data: {sortStatus: sortStatus, typeSort: typeSort},
            success: function (response) {
                if (response != 'error') {
                    $('.sale-order-list-inner-container').remove();
                    $('div#personal_orders').append(response);
                    $('a.sale-order-list-repeat-link').on('click', copyOrderPopups);
                }
            }
        })
    })

    $(document).on('click', function (e) {
        let elem = e.target;
        if (elem.classList.contains('close_modalWindow')) {
            elem.closest('.form_company_many').style.display = 'none';
        }
    })

    $(document).on('click', function (e) {
        let elem = e.target;
        if (elem.classList.contains('btn-close-modal')) {
            elem.closest('.modal').remove();
            $('.bx-background-image').css('overflow-y', 'auto');
        }
    })

    $(document).on('click', '.retail_orders', function () {
        console.log('entry', '');
        $(this).closest('div').find('.wholesale_orders').css({
            'background': '#F0F0F0',
            'borderRadius': '10px'
        });
        $(this).css({
            'background': '#F55F5C',
            'borderRadius': '10px 10px 0px 0px'
        })
        let data = {
            type: 'retail',
            url: document.location.search
        }
        $.ajax({
            url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort_by_retail.php',
            type: 'POST',
            data: {sort: JSON.stringify(data)},
            success: function (response) {
                let data = JSON.parse(response);
                $('.sale-order-list-inner-container').remove();
                data.forEach((item, index) => {
                    function addPictures() {
                        item['PICTURE'].forEach(item => {
                            if (item === null) {
                                $(`.sort_by_date_orders_${index}`).append(`<img class="image_box_orders" src="/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/images/no_photo.png"/>`)
                            } else {
                                $(`.sort_by_date_orders_${index}`).append(`<img class="image_box_orders" src="${item}"/>`)
                            }
                        })
                    }

                    function statusOrder() {
                        if (item['STATUS_ID'] === 'F') {
                            return `<span class="status_completed">Выполнен</span>`;
                        }
                        return `<span class="status_pending_payment">Принят, ожидается оплата</span>`
                    }

                    $('#content_box').append(`<div class="row mx-0 mb-5 sale-order-list-inner-container">
                <div class="row mx-0 sale-order-list-title-container">
                    <h3 class="mb-1 mt-1">
                        <div>
                            <span>Заказ № ${item['ACCOUNT_NUMBER']} от ${item['DATE_INSERT_FORMAT'].split(' ')[0]}</span>
                        </div>
                        <div>
                            ${statusOrder()}
                        </div>
                    </h3>
                </div>
                <div class="box_wth_delivery_number">
                    <div class="mt-2">
                        <span>Номер отслеживания:</span> <a href="#">24006875</a>
                    </div>
                </div>

                <div class="row mx-0 mb-4 mt-4 d-flex flex_class justify-content-evenly sort_by_date_orders_${index}">
                </div>
                    <div class="col pt-3">
                    <div class="sale-order-list-inner-row">
                        <div class="sale-order-list-inner-row">
                            <div class=" sale-order-list-about-container">
                                <a class="sale-order-list-about-link"
                                   href="/personal/orders/${item['ACCOUNT_NUMBER']}">Подробности
                                    заказа</a>
                            </div>

                            <div class=" sale-order-list-repeat-container">
                                <a class=" sale-order-list-repeat-link"
                                   href="/personal/cart/">Повторить заказ</a>
                            </div>
                            <div class=" sale-order-list-cancel-container">
                                <a class="sale-order-list-cancel-link"
                                       href="/personal/cancel/${item['ACCOUNT_NUMBER']}?CANCEL=Y">Отменить заказ</a>
                            </div>
                        </div>
                        <div class="sale-order-list-inner">
                            <div class="sale-order-list-inner-row-body">
                                <div class="sale-order-list-payment">
                                    <div class="mb-1 sale-order-list-payment-price">
                                        <span class="sale-order-list-payment-element">Сумма заказа:</span>
                                        <span class="sale-order-list-payment-number">${item['PRICE'].split('.')[0] + ' ₽'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        </div>`);
                    addPictures();
                })
            }
        })
    })

    $(document).on('click', '.wholesale_orders', function () {
        $(this).closest('div').find('.retail_orders').css({
            'background': '#F0F0F0',
            'borderRadius': '10px'
        });
        $(this).css({
            'background': '#F55F5C',
            'borderRadius': '10px 10px 0px 0px'
        })
        let data = {
            type: 'wholesale',
            url: document.location.search
        }
        $.ajax({
            url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort_by_retail.php',
            type: 'POST',
            data: {sort: JSON.stringify(data)},
            success: function (response) {
                let data = JSON.parse(response);
                $('.sale-order-list-inner-container').remove();
                data.forEach((item, index) => {
                    function addPictures() {
                        item['PICTURE'].forEach(item => {
                            if (item === null) {
                                $(`.sort_by_date_orders_${index}`).append(`<img class="image_box_orders" src="/bitrix/components/bitrix/catalog.element/templates/bootstrap_v4/images/no_photo.png"/>`)
                            } else {
                                $(`.sort_by_date_orders_${index}`).append(`<img class="image_box_orders" src="${item}"/>`)
                            }
                        })
                    }

                    function statusOrder() {
                        if (item['STATUS_ID'] === 'F') {
                            return `<span class="status_completed">Выполнен</span>`;
                        }
                        return `<span class="status_pending_payment">Принят, ожидается оплата</span>`
                    }

                    $('#content_box').append(`<div class="row mx-0 mb-5 sale-order-list-inner-container">
                <div class="row mx-0 sale-order-list-title-container">
                    <h3 class="mb-1 mt-1">
                        <div>
                            <span>Заказ № ${item['ACCOUNT_NUMBER']} от ${item['DATE_INSERT_FORMAT'].split(' ')[0]}</span>
                        </div>
                        <div>
                            ${statusOrder()}
                        </div>
                    </h3>
                </div>
                <div class="box_wth_delivery_number">
                    <div class="mt-2">
                        <span>Номер отслеживания:</span> <a href="#">24006875</a>
                    </div>
                </div>

                <div class="row mx-0 mb-4 mt-4 d-flex flex_class justify-content-evenly sort_by_date_orders_${index}">
                </div>
                    <div class="col pt-3">
                    <div class="sale-order-list-inner-row">
                        <div class="sale-order-list-inner-row">
                            <div class=" sale-order-list-about-container">
                                <a class="sale-order-list-about-link"
                                   href="/personal/orders/${item['ACCOUNT_NUMBER']}">Подробности
                                    заказа</a>
                            </div>

                            <div class=" sale-order-list-repeat-container">
                                <a class=" sale-order-list-repeat-link"
                                   href="/personal/cart/">Повторить заказ</a>
                            </div>
                            <div class=" sale-order-list-cancel-container">
                                <a class="sale-order-list-cancel-link"
                                       href="/personal/cancel/${item['ACCOUNT_NUMBER']}?CANCEL=Y">Отменить заказ</a>
                            </div>
                        </div>
                        <div class="sale-order-list-inner">
                            <div class="sale-order-list-inner-row-body">
                                <div class="sale-order-list-payment">
                                    <div class="mb-1 sale-order-list-payment-price">
                                        <span class="sale-order-list-payment-element">Сумма заказа:</span>
                                        <span class="sale-order-list-payment-number">${item['PRICE'].split('.')[0] + ' ₽'}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        </div>`);
                    addPictures();
                })
            }
        })
    });

    function copyOrderPopups(event) {
        event.preventDefault();
        let popup_mess = $(this).parent().find('#popup_mess_order_copy');
        let content;
        if ($(this).hasClass('js--basket-empty') || $('div#personal_orders').hasClass('js--basket-empty')) {
            content = '<p style="font-size: 1rem; font-weight: 500">' +
                'Вы точно хотите повторить заказ?</p>' +
                ($(this).hasClass('js--not-active') ? '<p style="font-size: 0.75rem; font-weight: 500; color: grey; margin-top: unset;">' +
                    '*Некоторые товары больше не находятся в ассортименте и не будут добавлены в корзину</p>' : '') +
                '<div class="confirmation_container">' +
                '<a href="' + $(this).attr('href') + '" id="yes_mess" class="d-flex  link_message_box_product ' +
                'justify-content-center align-items-center">' +
                'Да</a>' +
                '<a href="#" id="no_mess" class="d-flex basket-empty link_message_box_product ' +
                'justify-content-center align-items-center">' +
                'Нет</a></div>';
        } else {
            content = '<p style="font-size: 1rem; font-weight: 500">' +
                'Очистить корзину перед добавлением товаров?</p>' +
                ($(this).hasClass('js--not-active') ? '<p style="font-size: 0.75rem; font-weight: 500; color: grey; margin-top: unset;">' +
                    '*Некоторые товары больше не находятся в ассортименте и не будут добавлены в корзину</p>' : '') +
                '<div class="confirmation_container">' +
                '<a href="' + $(this).attr('href') + '&EMPTY_BASKET=Y" id="yes_mess" class="d-flex  link_message_box_product ' +
                'justify-content-center align-items-center">' +
                'Да</a>' +
                '<a href="' + $(this).attr('href') + '" id="no_mess" class="d-flex  link_message_box_product ' +
                'justify-content-center align-items-center">' +
                'Нет</a></div>';
        }
        var Confirmer = new BX.PopupWindow("popup_mess_order_copy", null, {
            content: content,
            max_width: "300px",
            closeIcon: {width: "31px", height: "30px", top: '3%', left: '88%', color: 'black'},
            zIndex: 300,
            offsetLeft: 0,
            offsetTop: 0,
            className: 'flex-column align-items-center box_with_message_copy_order',
            draggable: {restrict: false},
            overlay: {backgroundColor: 'black', opacity: '80'},  /* затемнение фона */
        });

        Confirmer.show();

        $('#no_mess.basket-empty').click({Confirmer: Confirmer}, popupWindowClose);

        function popupWindowClose(event) {
            event.preventDefault();
            Confirmer.close();
        }
    }

    $('a.sale-order-list-repeat-link').on('click', copyOrderPopups);

    /*NEW*/
    $('.sort_mobile').on('click', function () {

        $('.box_filter_catalog').show();
        $('body').css({'overflow': 'hidden'}).addClass('hide-jivo');
    });
    $('.closeButtonFilter').on('click', function () {

        $('.box_filter_catalog').hide();
        $('body').css({'overflow': 'initial'});
    });

    if (window.screen.width < 1024 && window.screen.width > 768) {
        var top_page = $('.section_wrapper').offset().top;
        $('.box_filter_catalog').css({'top': top_page});
    }

    $(document).on('click', '.js__filter-close', function () {
        if (!$(this).hasClass('disabled_class')) {
            $(this).parents('.box_filter_catalog').find('.filter-view-bar').css({'display': 'none'})

            $(this).parents('.box_filter_catalog').slideUp(function () {
                $('.filter-view').addClass('disabled_class');
                $('.filter-view-bar').show();
            });
            $('body').removeClass('hide-jivo');
            $(document).find('body').css({overflow: 'initial'})
        }
    });

    $('.shared i').on('click', function () {
        $(this).next().toggle();
        return false;
    });

    $('.smart-filter-tog').on('click', function () {
        var code_vis = $(this).data('code-vis');
        $('.catalog-section-list-item-sub[data-code="' + code_vis + '"]').toggleClass('active').toggleClass('hidden');
        $(this).toggleClass('smart-filter-angle-up');
    });


    $('.js__collapse-list').on('click', function () {
        if (!$(this).parents('.socials').length) {
            let elem = $(this),
                lists = $('.js__collapse-list.active');

            lists.next('.col-menu').stop().slideToggle();

            if (!elem.hasClass('active')) {
                lists.toggleClass('active');
                elem.addClass('active').next('.col-menu').stop().slideDown();
            } else {
                lists.toggleClass('active');
            }
        }
    });
});

window.onresize = function (event) {
    if ($('div').is('.basket_category') && window.screen.width <= 746) {
        $('.basket_category').css('width', window.screen.width - 20);
    }

    if (window.screen.width >= 768) {
        $('footer.footer .col-menu').css({display: 'block'});
    }
};


$(document).ready(function () {
    $('.search_mobile').on('click', function () {

        $('.box_with_search').toggle();
    });

    $('input[data-name="PHONE-FORM"]').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});
});


// т.к. FormData не может в multiple, создадим ей массив с файлами сами
let uploadFiles = {};

$(document).find('#drop-zone').on({
    'dragover dragenter': function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('overmouse');
    },
    'dragleave dragend': function (e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('overmouse');
    },
    'drop': function (e) {
        e.preventDefault();
        e.stopPropagation();
        let fls = e.originalEvent.dataTransfer.files,
            index = $('.upload-file-item:last-of-type').data('index') ?? 0;
        drawFileRow(fls, index);
        $('.drop-zone').removeClass('overmouse');
    }
});

$(document).on('change', 'input[type=file]', function (e) {
    let fls = this.files,
        index = $('.upload-file-item:last-of-type').data('index') ?? 0;
    drawFileRow(fls, index);
});

function drawFileRow(fls, index) {
    for (let i = 0, f; f = fls[i]; i++) {
        let j = index + i + 1;

        if (Object.keys(uploadFiles).length < 10) {
            $('.file-list').append(
                '<li class="upload-file-item" data-index="' + j + '">' +
                '<span class="image-box">' + f.name + '</span>' +
                '<span class="file-remove">x</span>' +
                '</li>'
            );

            uploadFiles[j] = f;
        }
    }
}

// Удаление файла из списка подгруженных и из formData
$(document).on('click', '.file-list .file-remove', function (e) {
    e.preventDefault();
    e.stopPropagation();

    let fileToRemoveId = $(this).parents('li').data('index');

    $(this).parents('.file-list')
        .find('[data-index="' + fileToRemoveId + '"]')
        .remove();
    delete uploadFiles[fileToRemoveId];
});

// маска ввода для формы обратной связи
$(document).ready(function () {
    $('.form-form [data-name="EMAIL"]').inputmask('email');
});

$(document).on('submit', '.form-form', function (e) {
    e.preventDefault();

    let postData = new FormData(this),
        errors = {
            emptyField: 'Поле не заполнено',
            wrongFieldData: 'Поле заполнено не до конца',
            wrongFilesSize: 'Некоторые из файлов больше 5 Мб',
            wrongFilesType: 'Некоторые из файлов недопустимого типа',
            wrongFilesCombo: 'Некоторые файлы не отвечают требованиям',
            emptyConfirm: 'Не приняты условия обработки персональных данных',
        },
        fieldName = $(this).find('input[name="NAME"]'),
        fieldPhone = $(this).find('input[name="PHONE"]'),
        fieldMail = $(this).find('input[name="EMAIL"]'),
        fieldMessage = $(this).find('textarea[name="MESSAGE"]'),
        fileTrueTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
        fieldConfirm = $(this).find('input[name="confirm"]'),
        err = 0;

    postData.delete('upload-files');

    $('.error_form').hide();
    $('.form-form .error_field').hide();

    if (fieldName.val().length <= 0) {
        $('.form-form .er_FORM_NAME').html(errors.emptyField).show();
        err++;
    }

    if (fieldPhone.val().length <= 0) {
        $('.form-form .er_FORM_PHONE').html(errors.emptyField).show();
        err++;
    }

    if (fieldMail.val().length <= 0) {
        $('.form-form .er_FORM_EMAIL').html(errors.emptyField).show();
        err++;
    }

    if (!fieldMail.inputmask("isComplete")) {
        $('.form-form .er_FORM_EMAIL').html(errors.wrongFieldData).show();
        err++;
    }

    if (fieldMessage.val().length <= 0) {
        $('.form-form .er_FORM_MESSAGE').html(errors.emptyField).show();
        err++;
    }

    // если files не пустой
    if (uploadFiles.length !== 0) {
        let errSize = 0,
            errType = 0;

        // заполняем объект данных файлами в подходящем для отправки формате
        $.each(uploadFiles, function (key, file) {
            errSize += file.size > 5000000 ? 1 : 0;
            errType += $.inArray(file.type, fileTrueTypes) < 0 ? 1 : 0;
            postData.append(key, file);
        });

        if (errSize > 0 && errType > 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesCombo).show();
        }
        if (errSize > 0 && errType == 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesSize).show();
        }
        if (errSize == 0 && errType > 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesType).show();
        }
    }

    if (!fieldConfirm.prop('checked')) {
        $('.er_FORM_CONFIRM').html(errors.emptyConfirm).show();
        err++;
    }

    if (err < 1) {
        $.ajax({
            url: '/local/ajax/form.php',
            method: 'POST',
            data: postData,
            cache: false,
            dataType: 'json',
            // отключаем обработку передаваемых данных, пусть передаются как есть
            processData: false,
            // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
            contentType: false,
        }).done(function (dataRes) {
            if (dataRes == 1) {
                $('.form-form-wrap').hide();
                $('.form_block_ok').show();
            } else {
                $('.error_form').html(dataRes).show();
            }
            uploadFiles = {};
        });
    }
    return false;
});


$(document).on('submit', '.send_feed', function (e) {
    e.preventDefault();

    let postData = new FormData(this),
        errors = {
            emptyField: 'Поле не заполнено',
            wrongFilesSize: 'Некоторые из файлов больше 5 Мб',
            wrongFilesType: 'Некоторые из файлов недопустимого типа',
            wrongFilesCombo: 'Некоторые файлы не отвечают требованиям',
            emptyConfirm: 'Не приняты условия обработки персональных данных',
        },
        fieldName = $(this).find('input[name="NAME"]'),
        fieldPhone = $(this).find('input[name="PHONE"]'),
        fieldMessage = $(this).find('textarea[name="MESSAGE"]'),
        fileTrueTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'],
        fieldConfirm = $(this).find('input[name="confirm"]'),
        err = 0;

    postData.delete('upload-files');

    $('.error_form').hide();
    $('.form-form .error_field').hide();

    if (fieldName.val().length <= 0) {
        $('.er_FORM_NAME').html(errors.emptyField).show();
        err++;
    }

    if (fieldPhone.val().length <= 0) {
        $('.er_FORM_PHONE').html(errors.emptyField).show();
        err++;
    }

    if (fieldMessage.val().length <= 0) {
        $('.er_FORM_MESSAGE').html(errors.emptyField).show();
        err++;
    }

    // если files не пустой
    if (uploadFiles.length !== 0) {
        let errSize = 0,
            errType = 0;

        // заполняем объект данных файлами в подходящем для отправки формате
        $.each(uploadFiles, function (key, file) {
            errSize += file.size > 5000000 ? 1 : 0;
            errType += $.inArray(file.type, fileTrueTypes) < 0 ? 1 : 0;
            postData.append(key, file);
        });

        if (errSize > 0 && errType > 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesCombo).show();
        }
        if (errSize > 0 && errType == 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesSize).show();
        }
        if (errSize == 0 && errType > 0) {
            $('.er_FORM_FILES').html(errors.wrongFilesType).show();
        }
    }

    if (!fieldConfirm.prop('checked')) {
        $('.er_FORM_CONFIRM').html(errors.emptyConfirm).show();
        err++;
    }

    if (err < 1) {
        $.ajax({
            url: '/local/ajax/form_feed.php',
            method: 'POST',
            data: postData,
            cache: false,
            dataType: 'json',
            // отключаем обработку передаваемых данных, пусть передаются как есть
            processData: false,
            // отключаем установку заголовка типа запроса. Так jQuery скажет серверу что это строковой запрос
            contentType: false,
        }).done(function (dataRes) {
            if (dataRes == 1) {
                $('.form-form-wrap').hide();
                $('.form_block_ok').show();
            } else {
                $('.error_form').html(dataRes).show();
            }
            uploadFiles = {};
        });
    }
    return false;
});

$(document).on('submit', '.callback_form', function (e) {
    e.preventDefault();
    let errors = {
            emptyField: 'Поле не заполнено',
            emptyConfirm: 'Не приняты условия обработки персональных данных',
        },
        fieldPhone = $(this).find('input[name="PHONE"]'),
        fieldConfirm = $(this).find('input[name="confirm"]'),
        err = 0;
    $('.js__error_field').html('').hide();

    if (fieldPhone.val().length <= 0) {
        fieldPhone.parents('.form-group').find('.js__error_field').html(errors.emptyField).show();
        console.log('phone error');
        err++;
    }

    if (!fieldConfirm.prop('checked')) {
        fieldConfirm.parents('.form-group').find('.js__error_field').html(errors.emptyConfirm).show();
        console.log('check error');
        err++;
    }

    if (!err) {
        $.ajax({
            url: '/ajax/callback.php',
            method: 'POST',
            data: $(this).serialize(),
        }).done(function (dataRes) {
            if (dataRes == 1) {
                //location.reload();
                $('.callback_form').hide();
                $('.result-callback').show();
            } else {
                $('.error_form').html(dataRes);
            }
        });
    }
    return false;
});

// Открытие попап обратного звонка: начало
$(document).ready(function () {
    $('.callback_PHONE').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});
    $('.js__callback').on('click', function () {
        $("#callbackModal").arcticmodal(
            {
                closeOnOverlayClick: true,
                afterClose: function (data, el) {
                }
            });
    });
});
// Открытие попап обратного звонка: конец
document.addEventListener('click', (e) => {
    const sortList = document.querySelector('.js__sort_orders_element');
    if (sortList !== null) {
        if (!e.composedPath().includes(sortList)) {
            sortList.style.display = 'none';
        }
    }
});
document.addEventListener('keyup', (e) => {
    const sortList = document.querySelector('.js__sort_orders_element');
    if (sortList !== null) {
        if (e.code === 'Escape') {
            sortList.style.display = 'none';
        }
    }
});

// top menu scroll

if ($(window).width() > 1024) {
    $(window).scroll(function () {
        var appended = false;
        var scrollTop = document.documentElement.scrollTop || document.body.scrollTop;
        if (scrollTop > 0) {
            if (!appended) {
                $(document).find('header').addClass('header-scroll').show(500);
                appended = true;
            }
        } else {
            $(document).find('header').removeClass('header-scroll');
        }
    });
}

$(window).on('resize', function () {
    const catalog = $('.catalog-section.by-line'),
        cardViewBtn = $('#card_catalog'),
        lineViewBtn = $('#line_catalog');

    if (catalog.length > 0) {
        if ($(window).width() < 500) {
            catalog.removeClass('by-line').addClass('by-card');

            if (lineViewBtn.hasClass('icon_sort_line_active')) {
                lineViewBtn.addClass('icon_sort_line').removeClass('icon_sort_line_active');
                cardViewBtn.addClass('icon_sort_bar_active').removeClass('icon_sort_line');
            }
        }
    }
});


jQuery(function () {
    let input = jQuery('.js__show-pass');
    input.wrap('<div class="show-pass-wrap"></div>');
    input.after('<span class="show-pass-btn js__show-pass-btn"></span>');

    jQuery('.js__show-pass-btn').on('click', function (e) {
        e.preventDefault();

        const btn = jQuery(this),
            input = jQuery(this).parents('.show-pass-wrap').find('input');

        if (input.attr('type') === 'password') {
            btn.addClass('active');
            input.attr('type', 'text');
        } else {
            btn.removeClass('active');
            input.attr('type', 'password');
        }
    })
});


$(document).ready(function () {
    $(document).on('click', '.close_header_box', function () {
        $('.overlay').hide();
        if (!$('.overlay-box').hasClass('hidden')) {
            $('.overlay-box').toggle('hidden')
        }
    });

    $(document).on('click', '.js__taste ', function () {
        let tasteCheckId = $(this).attr('data-filter-get'),
            taste = $(this).closest('.js__tastes');
        // Сбрасываем повторную фильтрацию по уже выбранному вкусу

        if (BX(tasteCheckId).checked) {
            $(taste).append('<span class="taste-errors">Вкус уже выбран</span>');
            setTimeout(BX.delegate(
                    function () {
                        $(taste).find('.taste-errors').fadeOut(
                            'slow',
                            function () {
                                this.remove()
                            })
                    }),
                2000
            );
            return;
        }

        $('#' + tasteCheckId).prop('checked', true);
        window.smartFilter.addHorizontalFilter(BX(tasteCheckId));
        window.smartFilter.timer = setTimeout(BX.delegate(function () {
            this.reload(BX(tasteCheckId));
        }, window.smartFilter), 500);
    })
});

$(document).on('click', '.js__close-count-alert', function () {
    $(this).parents('.alert_quantity').html('').removeClass('show_block');
})

//OFFERS

/**
 * Enterego - sort array data grouped products on priority props
 * @param arrProductGrouped
 * @param propCodePriority
 * @returns {*[]}
 */
function sortOnPriorityArDataProducts(arrProductGrouped = [], propCodePriority = '') {
    const selectedPropData = {};
    const productsSuccess = [];

    /** Перебор выбранных свой-в с получением группы значений для общего поиска */
    const selectedProp = $(document).find('.offer-link.selected');
    $.each(selectedProp, function (i_prop, selectProp) {
        const elems = $(selectProp).find('.offer-box');
        let groupKey = $(elems).attr('data-prop_code')
        selectedPropData[groupKey] = JSON.parse($(elems).attr('data-prop_group'));
    });

    $.each(arrProductGrouped, function (prod_id, item) {
        $.each(item.PROPERTIES, function (k, props) {
            if (props?.JS_PROP !== undefined && k !== 'USE_DISCOUNT' &&
                Object.keys(props?.JS_PROP).length === Object.keys(selectedPropData[k]).length) {
                $.each(props.JS_PROP, function (key, jsProp) {
                    let propList = selectedPropData[k][key];
                    let priority = -1;
                    if (propList !== undefined && jsProp.VALUE_ENUM === propList.VALUE_ENUM) {
                        if (propCodePriority === k) {
                            priority = 1;
                        }
                        if (productsSuccess.length <= 0) {
                            let itemPush = {
                                id: parseInt(prod_id),
                                code: propList.CODE,
                                pr: 1 + priority,
                            }
                            productsSuccess.push(itemPush)
                        } else {
                            $.each(productsSuccess, function (iProd, product) {
                                if (parseInt(product.id) === parseInt(prod_id)) {
                                    product.pr = product.pr + 1 + priority;
                                } else {
                                    let productSearch = productsSuccess.filter(item => parseInt(item.id) === parseInt(prod_id));
                                    if (productSearch.length === 0) {
                                        let itemPush = {
                                            id: parseInt(prod_id),
                                            code: propList.CODE,
                                            pr: 1 + priority,
                                        }
                                        productsSuccess.push(itemPush)
                                    } else {
                                        product.pr = product.pr + 1 + priority;
                                    }
                                }
                            });
                        }
                    }
                });
            }
        });
    });
    if (productsSuccess.length > 0) {
        productsSuccess.sort((a, b) => a.pr < b.pr ? 1 : -1)
    }
    return productsSuccess;
}

// NEW DESIGN opt
ToggleThemeLocalStorages();
// togger themes

// install theme
function ToggleThemeLocalStorages() {
    if (localStorage.theme === 'dark' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
}

function toggleTheme(item) {
    item.classList.toggle('bg-indigo-600');
    item.classList.toggle('bg-gray-200');
    item.querySelector('.js--togglerIcon').classList.toggle('translate-x-0')
    item.querySelector('.js--togglerIcon').classList.toggle('translate-x-3.5')
    if (localStorage.theme === 'dark') {
        localStorage.theme = 'light'
    } else {
        localStorage.theme = 'dark'
    }
    ToggleThemeLocalStorages();
}

// LOADER
function loaderForSite(initParam, itemParent = false) {
    const body = itemParent !== false ? itemParent : document.querySelector('body');
    if (initParam === 'appendLoader') {
        if (body.querySelector('div.remove-class')?.length === 0 || body.querySelector('div.remove-class') === null) {
            body.appendChild(BX.create('DIV', {
                props: {
                    className: 'fixed w-screen h-screen z-50 top-0 left-0 remove-class flex justify-center ' +
                        'items-center',
                    style: 'background: rgba(60, 60, 60, 0.81); z-index:1000'
                },
                html: '<div class="loader absolute rounded-full" style="width: 107px;height: 107px;">' +
                    '<div class="inner one h-full w-full absolute" style="border-bottom: 4px solid #ffffff"></div>' +
                    '<div class="inner two h-full w-full absolute" style="border-bottom: 4px solid #ffffff"></div>' +
                    '<div class="inner three h-full w-full absolute" style="border-bottom: 4px solid #ffffff"></div>' +
                    '</div>'
            }));
        }
    } else {
        body.querySelector('.remove-class').remove();
    }
}