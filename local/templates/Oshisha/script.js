$(document).ready(function () {
    let div = $('div');
    let body = $('body');
    let inputItem = $('input');
    let href = window.location.pathname;
    let screenWidth = window.screen.width;
    let boxAddress = $('#edit_addressBox')
    let checkProductOrdersReady = $('#allProducts');
    let bool = $('span').is('.taste');
    let tasted = $(div).is('#VKUS');
    let brands = $(div).is('.box_with_brands_parents');
    // WORKERS
    let workers = $(div).is('#personal_worker');
    let worker_pages = $(div).is('#worker_pages');
    let worker_pages_home = $(div).is('#worker_pages_lk_home');
    //
    //CONTRAGENT
    let workersArray;
    let arrayCompanyId;
    //
    //BASKET
    let addToBasket = $('a').is('.add2basket');
    let box_basket_elems = $('.basket-items-list').find('.basket-items-list-table');
    let arItemsForDB = [];
    let product_data;
    let bars = $('#basket-card');
    let line = $('#basket-line');
    let bool_basket = $(div).is('#basket-items-list-container');
    //CATALOG

    //
    let checkInput = $(inputItem).is(checkProductOrdersReady);
    let box_for_tasted = $(body).find('.box_for_tasted');
    let icon = $('#icon');
    let main_menu = $('.main_menu');
    // HIDE
    $(main_menu).hide();
    $(boxAddress).hide();
    $('.content_for_box_delivery').hide();
    // SELECT
    let select = $('select');
    let select_sort_basket = ('.select_sort_basket');
    let bool_select_orders = $(select).is('#select_orders');
    let bool_select_order_return = $(select).is('#select_comments');
    let bool_select_contragent_user = $(select).is('#contragent_user');
    let bool_select_company_user_order = $(select).is('#company_user_order');

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
        let data = new Date()
        let newDate = String(data.getFullYear() - 17);
        let inputPicker = $('input#main-profile-day');
        $(inputPicker).datepicker({
            maxDate: new Date(newDate),
            altField: inputPicker,
        });
    }

    function getPriceForProduct(that) {
        if (that.closest('.bx_catalog_item_container') === null) {
            return parseInt($('.product-item-detail-price-current').text().match(/[\d]/gi).join(''));
        }
    }

    // COLOR TASTE
    function tasteInit() {
        let box = $('.variation_taste');

        $(box).find('span').each(
            function () {
                let classes = $(this).attr('class');
                if (classes === 'taste') {
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
    }

    if (bool === true) {
        $(body).find('.variation_taste').each(
            function (index, item) {
                if ($(item).closest('.bx_catalog_item ').find('.taste').length > 2 && $(item).closest('.bx_catalog_item_container') !== null) {
                    //$(item).closest('.toggle_taste ').css('overflow', 'hidden');
                    // $(item).closest('.toggle_taste ').addClass('many_tastes_toggle');
                    $(item).attr('visible', '0');
                }
            }
        );
        $(document).on('click', function (e) {
            if ($(e.target).attr('visible') === '0' && $(e.target).closest('.bx_catalog_item ').find('.taste').length > 2) {
                //$(e.target).closest('.toggle_taste ').removeClass('many_tastes_toggle');
                $(e.target).addClass('many_tastes');
                $(e.target).closest('.toggle_taste ').css({
                    'overflow': 'visible',
                    'zIndex': 500,
                });
                $(e.target).closest('.bx_catalog_item_container').find('.image_cart ').css('filter', 'blur(3px)');
                $(e.target).closest('.bx_catalog_item_container').find('.bx_catalog_item_price ').css('filter', 'blur(3px)');
                $(e.target).closest('.bx_catalog_item_container').find('.box_with_title_like  ').css('filter', 'blur(3px)');
                $(e.target).closest('.bx_catalog_item_container').find('.bx_catalog_item_controls ').css('filter', 'blur(3px)');
                $(e.target).attr('visible', '1');
            } else if ($(e.target).attr('visible') === '1' && $(e.target).closest('.bx_catalog_item ').find('.taste').length > 2) {
                // $(e.target).closest('.toggle_taste ').addClass('many_tastes_toggle');
                $(e.target).removeClass('many_tastes');
                $(e.target).closest('.toggle_taste ').css({
                    'overflow': 'hidden',
                    'zIndex': 'auto'
                });
                $(e.target).attr('visible', '0');
                0
                $(e.target).closest('.bx_catalog_item_container').find('.image_cart ').css('filter', 'blur(0px)');
                $(e.target).closest('.bx_catalog_item_container').find('.bx_catalog_item_price ').css('filter', 'blur(0px)');
                $(e.target).closest('.bx_catalog_item_container').find('.box_with_title_like  ').css('filter', 'blur(0px)');
                $(e.target).closest('.bx_catalog_item_container').find('.bx_catalog_item_controls ').css('filter', 'blur(0px)');
            }

        })
        tasteInit();
    }

    if (tasted === true) {
        $(this).find("input.check_input:checked").each(
            function () {
                let text_taste = $(this).closest('div.form-check').find('label').attr('id');
                let code = $(this).closest('div.form-check').find('label').attr('for');
                /*  $(box_for_tasted).append('<span style="padding: 8px 25px;border:1px solid #f55f5c;color: white;\n' +
                      'margin-right: 10px;border-radius: 10px;font-size: 11px;">' + text_taste + '</span>');*/
                /* $('#osh-filter-horizontal').append('<div id="osh-filter-horizontal-item" class="d-inline-block" data-osh-filter-state="hide">'+
                 '<div id="item-'+code+'" class="osh-filter-item osh-filter-horizontal-border d-inline-block">' + text_taste + '<span onclick="smartFilter.removeHorizontalFilter(\''+code+'\')" class="d-inline-block osh-filter-horizontal-remove"></span></div></div>');
     */
            }
        );
    }

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

        /*if (this.className === "Icon") {
            $('header').removeAttr('style');
            this.className = "Icon open";
            $('body').css('overflow', 'initial');
        } else {
           // $('header').attr('style', 'position: fixed;z-index: 1010;width: 100%;height: 100%;');
            this.className = "Icon";
            $('body').css('overflow', 'hidden');
            $('.box_filter_catalog').hide();
        }*/
    });

    // width JS

    if (screenWidth >= 320 && screenWidth <= 800) {
        let bool_personal = href.indexOf('personal');
        let bool_cart = href.indexOf('cart');
        let bool_order = href.indexOf('/order/');
        if (bool_personal !== -1 && bool_cart === -1 && bool_order === -1) {
            $('.header_top_panel')
                .attr('style', 'background-color:#F0F0F0;filter:drop-shadow(0px 1px 3px rgba(97, 97, 97, 0));');
            $('.left-menu .box_with_photo').attr('style', 'box-shadow: 0px -4px 30px rgba(196, 196, 196, 0.2);');
        }
        if (href === '/personal/') {
            $('.personal_hide').show();
            $('.box_footer').attr('style', 'margin-top: 32rem;');
        } else {
            //  $('.personal_hide').hide();
            // $('#content_box').attr('style', 'margin-top: 8rem;');
        }

    }


    if ($(div).is('.bx_catalog_tile_section')) {
        let count = 4;
        let variableWidth = false;
        if (screenWidth <= 1380) {
            count = 4;

        }
        if (screenWidth <= 1080) {
            count = 3;

        }
        if (screenWidth <= 746) {
            count = 2;
            variableWidth = true;
        }
        $('.bx_catalog_tile_section').slick({
            slidesToShow: count,
            arrows: true,
            infinite: false,
            variableWidth: variableWidth,
            prevArrow: '<span class="new_custom_button_slick_left_cat"  aria-hidden="true"><i class="fa fa-angle-left"'
                + ' aria-hidden="true"></i></span>',
            nextArrow: '<span class="new_custom_button_slick_right_cat" aria-hidden="true"><i class="fa fa-angle-right"'
                + ' aria-hidden="true"></i></span>',
        })
    }

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

    // $('input[data-name="PHONE"]').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});


    // $('input[data-name="EMAIL"]').inputmask("email");
    if ($(div).is('.bx-soa-customer-field')) {
        $('.bx-soa-customer-field input[data-name="FIO"]').attr('placeholder', 'Иванов Иван Иванович');
        $('.bx-soa-customer-field input[data-name="EMAIL"]').attr('placeholder', 'Не указана');
    }

    //  $('input[data-name="INN"]').inputmask({"mask": "9999999999"});

    //BASKET
    $('.product-item-amount').on('input', function () {
        $(this).val($(this).val().replace(/[A-Za-zА-Яа-яЁё]/, ''))
    });

    function changePrice() {
        let value = parseInt($('.card_element').val());
        let maxValue = parseInt($('.btn-plus').attr('data-max-quantity'));
        let minValue = 0;
        if (value < minValue) {
            value = minValue
            $('.card_element').val(minValue)
        } else if (value > maxValue) {
            value = maxValue;
            $('.card_element').val(maxValue);
            $('.alert_quantity').html('К покупке доступно максимум: ' + maxValue + 'шт.').addClass('show_block');

        }
        if (value > 0) {
            $('.ganerate_price_wrap').show();
        }
        $('.ganerate_price').text(getPriceForProduct(this) * value + ' ₽');
    }

    $(document).on('change', '.card_element', function () {
        changePrice();
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

        $(document).on('click', '.add2basket', function () {

            function appendLoader() {
                $('.spanBasketTop').text('').attr('style', 'padding: 4px 8px;').append('' +
                    '<div class="loader"><div class="inner one"></div><div class="inner two">' +
                    '</div> <div class="inner three"></div></div>');
            }

            if (!$('span').is('.spanBasketTop')) {
                $('.basket_top').append('<span class="spanBasketTop"></span>');
            }
            appendLoader();
            let product_id = $(this).attr('data-product_id');
            let product_url = $(this).attr('data-url');
            let box_with_product = $(this).closest('.bx_catalog_item').find('div#result_box');
            let quantityProdDet = $(this).closest('div').find('input.product-item-amount').val();
            let box_with_products_order = $(this).closest('.bx_catalog_items').find('div#result_box');
            let boxInput = $(this).closest('.bx_catalog_item_controls').find('input.card_element');
            let plus = $(this).hasClass('btn-plus');
            let minus = $(this).hasClass('btn-minus');

            let max_QUANTITY = parseInt($(this).attr('data-max-quantity'));

            if (plus === true) {
                if (parseInt($(boxInput).val()) < max_QUANTITY) {
                    let beforeVal = parseInt($(boxInput).val()) + 1;
                    $(boxInput).val(beforeVal);
                    if (beforeVal > 0)
                        $('.ganerate_price_wrap').show();
                    else
                        $('.ganerate_price_wrap').hide();
                    $('.ganerate_price').text(getPriceForProduct(this) * beforeVal + ' ₽');
                    product_data = {
                        'ID': product_id,
                        'QUANTITY': beforeVal,
                        'URL': product_url,
                        'PRICE': getPriceForProduct(this) * beforeVal + ' ₽',
                    };


                } else {
                    $(boxInput).val(max_QUANTITY);
                    if (max_QUANTITY > 0) {
                        $('.ganerate_price_wrap').show();
                        $('.alert_quantity[data-id="' + product_id + '"]').html('К покупке доступно максимум: ' + max_QUANTITY + 'шт.').addClass('show_block');
                    } else
                        $('.ganerate_price_wrap').hide();
                    $('.ganerate_price').text(getPriceForProduct(this) * max_QUANTITY + ' ₽');
                    product_data = {
                        'ID': product_id,
                        'QUANTITY': max_QUANTITY,
                        'URL': product_url,
                        "PRICE": getPriceForProduct(this) * max_QUANTITY + ' ₽',
                    };
                }
                if ($(this).hasClass('red_button_cart')) {
                    $('.red_button_cart[data-product_id="' + product_id + '"]').hide();
                    $('.product-item-amount-field-contain-wrap[data-product_id="' + product_id + '"]').css({'display': 'flex'});
                }
            } else if (minus === true) {
                $('.alert_quantity[data-id="' + product_id + '"]').html('').removeClass('show_block');

                if (parseInt($(boxInput).val()) > 0) {
                    let beforeVal = parseInt($(boxInput).val()) - 1;
                    $(boxInput).val(beforeVal);
                    if (beforeVal > 0)
                        $('.ganerate_price_wrap').show();
                    else
                        $('.ganerate_price_wrap').hide();
                    $('.ganerate_price').text(getPriceForProduct(this) * beforeVal + ' ₽');
                    product_data = {
                        'ID': product_id,
                        'QUANTITY': beforeVal,
                        'URL': product_url,
                        "PRICE": getPriceForProduct(this) * beforeVal + ' ₽',
                    };

                    if (beforeVal == 0) {
                        $('.red_button_cart[data-product_id="' + product_id + '"]').show();
                        $('.product-item-amount-field-contain-wrap[data-product_id="' + product_id + '"]').hide();
                    }
                }
            } else {
                let quantity = parseInt(quantityProdDet);
                if ((quantity > 1) || (quantity !== 0)) {
                    product_data = {'QUANTITY': quantity, 'URL': product_url, 'ID': product_id};
                    $(boxInput).val(quantity);
                    if (quantity > max_QUANTITY) {
                        $('.alert_quantity[data-id="' + product_id + '"]').html('К покупке доступно максимум: ' + max_QUANTITY + 'шт.').addClass('show_block');

                    } else {
                        $('.alert_quantity[data-id="' + product_id + '"]').html('').removeClass('show_block');
                    }
                } else {
                    product_data = {'QUANTITY': 1, 'URL': product_url, 'ID': product_id};
                    $(boxInput).val(1);
                }
            }
            let detailCardBasketAddButton = $('a.add2basket:not(.btn-plus):not(.btn-minus)');
            if ($(detailCardBasketAddButton).is('.basket_prod_detail')) {
                if (product_data.QUANTITY !== '' && parseInt(product_data.QUANTITY) !== 0 && parseInt(product_data.QUANTITY) > 0) {
                    if (!$(detailCardBasketAddButton).hasClass('addProductDetailButton')) {
                        $(detailCardBasketAddButton).hide(200).text('В корзине');
                        $(detailCardBasketAddButton).attr({'onclick': "location.href='/personal/cart/'"});
                        $(detailCardBasketAddButton).removeClass('btn_basket').addClass('addProductDetailButton').show(200);
                    }
                } else {
                    if ($(detailCardBasketAddButton).hasClass('addProductDetailButton')) {
                        $(detailCardBasketAddButton).hide(200).text('В корзину');
                        $(detailCardBasketAddButton).addClass('btn_basket').removeClass('addProductDetailButton').show(200);
                    }
                }
            }
            $(box_with_product).empty();
            $(box_with_products_order).empty();

            addItemArrayANDSend(product_data);
        });

        function deleteBasketItemTop(result) {
            if (result !== '' && result !== 0) {
                $('.spanBasketTop').attr('style', 'padding: 3px 6px;').text(result);
            } else {
                $('.spanBasketTop').remove();
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
            let product_data = [];
            let new_time;
            let time;

            if (ItemArray.length !== 0) {
                $(ItemArray).each(function (key, itemVal) {
                    if (itemVal.TIME === 0) {

                        product_data = {
                            'ID': itemVal.ID,
                            'QUANTITY': itemVal.QUANTITY,
                            'URL': itemVal.URL,
                            'TIME': itemVal.TIME,
                            'PRICE': itemVal.PRICE
                        };

                        $.ajax({
                            type: 'POST',
                            url: '/local/templates/Oshisha/include/add2basket.php',
                            data: 'product_data=' + JSON.stringify(product_data),
                            success: function (result) {
                                deleteBasketItemTop(result);
                            }
                        });
                        $(arItemsForDB).each(function (i, val) {
                            if (val.ID === product_data.ID) {
                                arItemsForDB.splice(i, 1)
                            }
                        });
                    } else {
                        time = itemVal.TIME;
                        if (time !== 0) {
                            new_time = (time - 1)
                        } else {
                            new_time = 0;
                        }
                        product_data = {
                            'ID': itemVal.ID,
                            'QUANTITY': itemVal.QUANTITY,
                            'URL': itemVal.URL,
                            'TIME': new_time,
                            'PRICE': itemVal.PRICE,
                        };
                        $(arItemsForDB).each(function (i, val) {
                            if (val.ID === product_data.ID) {
                                val.QUANTITY = product_data.QUANTITY;
                                val.PRICE = product_data.PRICE;
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
        $(popup_mess).append('<div class="d-flex flex-column align-items-center box_with_message_prodNot" > ' +
            '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
            'К сожалению, товара нет в наличии. Мы можем уведомить вас, когда он снова появиться.</p>' +
            '<a href="javascript:void(0);" id="yes_mess" class="d-flex  link_message_box_product ' +
            'justify-content-center align-items-center">' +
            '<i class="fa fa-bell-o" aria-hidden="true"></i>Уведомить меня</a>' +
            '<span class="close_photo" id="close_photo"></span></div>').show();
        $('#close_photo').on('click', function () {
            $(".box_with_message_prodNot").hide(500).remove()
        });
        $('a#yes_mess').on('click', function () {
            $(popup_mess).hide();
            $(popup_mess).empty();
            $(popup_mess).append('<div class="d-flex flex-column align-items-center box_with_message_prodNot"> ' +
                '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
                'К сожалению, товара нет в наличии. Мы можем уведомить вас, когда он снова появиться.</p>' +
                '<input type="text" class="input_sendMe_mail" id="send_me_mail"  placeholder="Введите свою почту"/>' +
                '<a href="javascript:void(0);" id="sendMailForProd" class="d-flex link_message_box_product ' +
                'justify-content-center align-items-center">Отправить</a><span class="close_photo" id="close_photo">' +
                '</span></div>').show();
            $('#close_photo').on('click', function () {
                $(".box_with_message_prodNot").hide(500).remove()
            });
            $('a#sendMailForProd').on('click', function () {
                let mailClient = $('input#send_me_mail').val();
                if (mailClient !== '') {
                    $(popup_mess).empty();
                    $(popup_mess).append('<div class="d-flex flex-column align-items-center box_with_message_prodNot"> '
                        + '<i class="fa fa-info-circle" aria-hidden="true"></i><p>' +
                        'Спасибо!<br> Мы обязательно сообщим вам, когда товар появится на складе и в магазинах!</p>' +
                        '<span class="close_photo" id="close_photo"></span></div>').show();
                    $('#close_photo').on('click', function () {
                        $(".box_with_message_prodNot").hide(500).remove()
                    });
                } else {
                    $('div#error').empty();
                    $('div.box_with_message_prodNot').append('<div id="error">Заполните почту для отправки!</div>').show(300);
                }
            });

        });

    });

    $('.switch-btn').on('click', function () {
        $(this).toggleClass('switch-on');
    });

    $(document).on('click', '.btn-plus', function () {
        let input = $('input.product-item-amount').val();
        let popup_mess = $(this).closest('div.bx_catalog_item').find('div#popup_mess');
        let classes = $(this).hasClass('product-item-amount-field-btn-disabled');
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


//BRANDS

    if (brands === true) {
        $('#box_boxes').find('.box_with_brands_parents').each(
            function () {
                let heightBlock = $(this).css('height');
                if (heightBlock < '435px') {
                    $(this).closest('div#box_brands').find('a.link_menu_catalog ').attr('style', 'display:none');
                }
            }
        )
    }

    function showHideBlockForButton(box) {
        let height = $(box).css('height');
        let id = $(box).attr('id');
        let idLink = $(this).attr('id');
        if (id === idLink) {
            if (height === '435px') {
                $(this).text('Скрыть');
                $(box).attr('style', 'max-height:2000px;transition: 0.7s;');
            } else {
                $(this).text('Показать все');
                $(box).attr('style', 'max-height:435px;height:435px;transition: 0.7s;');
            }
        }
    }

    $('a.link_brand').on('click', function () {
        let box = $(this).closest('div#box_brands').find('.box_with_brands_parents');
        showHideBlockForButton(box)
    });

// CATALOG
    if ($(div).is('.bx-basket')) {
        $(document).on('click', '.btn_basket_collapse', function () {
            let box = $(this).closest('.box').find('.category');
            let attr = $(box).hasClass('collapse_hide');
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
    $('#edit_address').on('click', function () {
        let display = $(boxAddress).css('display');
        if (display === 'none') {
            $(boxAddress).show(400);
        } else {
            $(boxAddress).hide(400);
        }
    });


// COMPANY
    let boolCompany = $(inputItem).is('#company_user');
    if (boolCompany === true) {
        let company_json = $('#company_user').val();

        if (company_json !== '') {

            let company_user = JSON.parse(company_json);

            if (company_user.ADMIN !== '') {
                $.each(company_user.ADMIN, function (i, value) {
                    let class_item = '';
                    let text = 'Архивировать';
                    let button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
                    if (value.ARCHIVED === '1') {
                        class_item = 'notActive';
                        text = 'Восстановить';
                        button_edit = '';
                    }
                    $('#boxWithCompany').append('<div id="' + value.COMPANY_ID + '" class="' + class_item + ' ' +
                        ' mb-3 box_with_company" data-attr-arch="' + value.ARCHIVED + '" data-method="ent_company"> ' +
                        ' <div class="d-flex flex-row mb-3 justify-content-between align-items-center">' +
                        '<div class="d-flex align-items-center"><span class="mr-3 box_company_icon"></span>' +
                        '<h6 class="mb-0"><b class="nameBox" >' + value.NAME_COMP + '</b></h6></div>' +
                        '<span class="icon_edit_lk"><div class="box_edit" style="display: none!important;">'
                        + button_edit +
                        '<span class="ARCHIVE" >' + text + '</span></div></span></div> ' +
                        '<div class="d-flex flex-column justify-content-between box_contact">' +
                        '<p class="mb-1"><b class="mr-1">Адрес доставки: </b><span class="addressEdit">'
                        + value.ADDRESS + '</span></p>' +
                        '<p class="mb-1"><b class="mr-1">Время работы: </b><span class="times">'
                        + value.TIMES + '</span></p>' +
                        '<p class="mb-1"><b class="mr-1">Номер телефона: </b><span class="phone">'
                        + value.PHONE_COMPANY + '</span></p></div>' +
                        '<div class="box_for_edit"></div></div>');
                });
            }
            if (company_user.USER !== '') {
                let array_company_user = company_user.USER;
                $(array_company_user).each(function (i, value) {
                    let class_item = '';
                    if (value.ARCHIVED === '1') {
                        class_item = 'notActive';
                    }
                    $('#boxWithCompanyUser').append('<div id="' + value.COMPANY_ID + '" class="' + class_item + '' +
                        'mb-3 box_with_company" data-attr-arch="' + value.ARCHIVED + '" data-method="ent_company">' +
                        '<div class="d-flex flex-row mb-3 align-items-center"><span class="mr-3 box_company_icon"></span>' +
                        '<h6 class="mb-0"><b class="nameBox">' + value.NAME_COMP + '</b></h6></div>' +
                        '<div class="d-flex flex-column justify-content-between box_contact">' +
                        '<p class="mb-1"><b class="mr-1" >Адрес доставки: </b>' + value.ADDRESS + '</p>' +
                        '<p class="mb-1"><b class="mr-1">Время работы: </b>' + value.TIMES + '</p>' +
                        '<p class="mb-1"><b class="mr-1">Номер телефона: </b>' + value.PHONE_COMPANY + '</p></div>' +
                        '</div>');
                });
            }
        }


        $(document).on('click', '#CreateCompany', function () {
            let that = $(this);
            let form_loader = $('.company_box');
            let box = $(that).closest('#personal_company');
            let user_id = $('#company_user').attr('data-user-id');
            let CompanyName = $(box).find('input#CompanyName').val();
            let CompanyTime = $(box).find('input#CompanyTime').val();
            let CompanyAddress = $(box).find('input#CompanyAddress').val();
            let CompanyTelephone = $(box).find('input#CompanyTelephone').val();
            $('.mess_danger').remove();
            if (CompanyTelephone !== '' && CompanyAddress !== '' && CompanyTime !== '' && CompanyName !== '') {
                let company_array = {
                    'user_id': user_id,
                    'CompanyName': CompanyName,
                    'CompanyTime': CompanyTime,
                    'CompanyAddress': CompanyAddress,
                    'CompanyTelephone': CompanyTelephone,
                }

                $.ajax({
                    type: 'POST',
                    url: BX.message('SITE_DIR') +
                        'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
                    data: 'company_array=' + JSON.stringify(company_array),
                    beforeSend: function () {
                        $(form_loader).append('<div id="loading_screen"><div class="lds-dual-ring"></div></div>');
                    },
                    success: function (result) {
                        $('div#loading_screen').remove();
                        if ($(div).is('#popupAdd')) {
                            $('#popupAdd').hide(200).empty();
                        }
                        if (result.error || typeof result !== 'object') {
                            let error = result.error
                            if (typeof result === 'string') {
                                error = 'Неверно введены данные!';
                            }
                            $('.form_company_many').append('<span class="mess_danger">' + error + '</span>');
                        } else {
                            let class_item = '';
                            let text = 'Архивировать';
                            let button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
                            if (result.ARCHIVED === '1') {
                                class_item = 'notActive';
                                text = 'Восстановить';
                                button_edit = '';
                            }
                            $('#boxWithCompany').append('<div id="' + result.COMPANY_ID + '" class=" mb-3 box_with_company ' +
                                +class_item + '" ' +
                                'data-attr-arch="' + result.ARCHIVED + '" data-method="ent_company">' +
                                '<div class="d-flex flex-row mb-3 align-items-center justify-content-between"> ' +
                                '<div class="d-flex align-items-center"><span class="mr-3 box_company_icon"></span>' +
                                '<h6 class="mb-0"><b class="nameBox">' + CompanyName + '</b></h6></div>' +
                                '<span class="icon_edit_lk"><div class="box_edit" style="display: none!important;">'
                                + button_edit +
                                '<span class="ARCHIVE" >' + text + '</span></div></span></div>' +
                                '<div class="d-flex flex-column justify-content-between box_contact">' +
                                '<p class="mb-1"><b class="mr-1" >Адрес доставки: </b><span class="addressEdit">'
                                + CompanyAddress + '</span></p>' +
                                '<p class="mb-1"><b class="mr-1">Время работы: </b><span class="times">'
                                + CompanyTime + '</span></p>' +
                                '<p class="mb-1"><b class="mr-1">Номер телефона: </b><span class="phone">'
                                + CompanyTelephone + ' </span></p></div>' +
                                '<div class="box_for_edit"></div></div>');
                        }
                    }
                });
            } else {
                $('.form_company_many').append('<span class="mess_danger">Данные звполнены некорректно</span>')
            }
        });

        if (screenWidth <= 767) {
            if (parseInt($('input#company_user').attr('data-init')) > 2) {
                $('.box_with_company:gt(1)').hide();
            }
        }

        $('a#showCompanyAdmin').on('click', function () {
            let parentBlockID = 'boxWithCompany';
            showHideBlockForButtons('.box_with_company', screenWidth, parentBlockID, $(this).attr('id'));
        });

    }

    function showHideBlockForButtons(box, width, parentBlockID, type_button) {
        let count = '';
        count = width <= 767 ? ':gt(1)' : ':gt(4)';
        let boxes_hide = '#' + parentBlockID + ' ' + box + count;
        if (!$('#' + parentBlockID).hasClass('activeHide')) {
            $('#' + parentBlockID).addClass('activeHide');
            $('#' + type_button).text('Скрыть');
            $(boxes_hide).show(300);
        } else {
            $('#' + type_button).text('Показать все');
            $(boxes_hide).hide(300);
        }
    }


// CONTR_AGENT
    function send_ajax_CreateContragent(arData) {

        let contrAgent_array;
        let resultMessage = '';

        contrAgent_array = {
            'user_id': arData.user_id,
            'INN': arData.INN,
            'NAME_CONT': arData.NAME_CONT,
            'UrAddress': arData.UrAddress,
            'statusPerson': arData.statusPerson,
            'company': arData.company,
            'workers': arData.workers
        }
        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') +
                'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data: 'CreateContragent=' + JSON.stringify(contrAgent_array),
            success: function (result) {
                if (result.error) {
                    $('#step_create_contragent').find('.mess_danger').text(result.error).removeClass('hide_class').show(300);
                } else {
                    result.CONTR_AGENT_ACTIVE = '0';
                    result.ARCHIVED = '0';
                    let class_item = '';
                    let text = 'Архивировать';
                    let button_edit = '<span  class="EDIT_INFO">Редактировать</span>';
                    if (result.ARCHIVED === '1') {
                        class_item = 'notActive';
                        text = 'Восстановить';
                        button_edit = '';
                    }

                    let button = '<span class="icon_edit_lk"><div class="box_edit" style="display: none!important;">'
                        + button_edit +
                        '<span class="ARCHIVE">' + text + '</span></div></span>'
                    if (result.CONTR_AGENT_ACTIVE === '0') {
                        class_item = 'notActive';
                        button = '<span style="font-size: 10px;font-weight: 600">Не подтвержден</span>';
                    }

                    $('#boxWithContrAgents').append('<div id="' + contrAgent_array.CONTR_AGENT_ID + '" ' +
                        'data-attr-arch="' + result.ARCHIVED + '" class="box_with_company contr_agents_box '
                        + class_item + '" data-method="ent_contr_agents" data-active="' + result.CONTR_AGENT_ACTIVE + '">' +
                        '<div class="d-flex flex-row mb-3 justify-content-between align-items-center">' +
                        '<div class="d-flex align-items-center"> <span class="mr-3 icons_worker_contr"></span>' +
                        '<h6 class="mb-0"><b class="nameBox">' + contrAgent_array.NAME_CONT + '</b></h6></div>' + button +
                        '</div>' +
                        '<div class="d-flex flex-column justify-content-between box_contact">' +
                        '<p class="mb-2"><b class="mr-1" >ИНН : </b><span class="INN">' + contrAgent_array.INN + '</span></p>' +
                        '<p class="mb-2"><b class="mr-1">Юридический адрес: </b>' +
                        '<span class="addressEdit">' + contrAgent_array.UrAddress + '</span></p>' +
                        '<div class="box_for_edit"></div></div>').show(200);
                    resultMessage = 'success';
                }
            }
        });

        return resultMessage;
    }

    function send_ajax_edit(arObj, that) {
        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') +
                'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data: 'edit=' + JSON.stringify(arObj),
            success: function (result) {
                if (result === true) {
                    $(that).closest('.box_with_company').find('.addressEdit').html(arObj["arParams"]['UrAddress']);
                    $(that).closest('.box_with_company').find('.times').html(arObj["arParams"]['TIMES']);
                    $(that).closest('.box_with_company').find('.phone').html(arObj["arParams"]['PHONE']);
                    $(that).closest('.box_with_company').find('.nameBox').html(arObj["arParams"]['NAME']);
                    $(that).closest('.box_with_company').find('.box_edit').hide().attr('style', 'display:none!important;');
                    $(that).closest('.box_with_company').find('.box_for_edit').hide(200).empty();

                } else {
                    alert('ошибка заполнения данных!');
                }
            }
        });
    }

    function appendListParams(company_json, boxInfoContrs, boxWorkers, arDataInfoContragent, method, id_contragent) {
        if (company_json !== '') {
            let box_company;
            let box_with_company_checked;
            let box_with_line_company_checked;
            let box_with_workers_info;
            let step;
            let nameOperation;
            let button_step_company;
            let box_with_workers;
            let arData;
            let bool_popup;
            let button_send_ajax;
            let arrayEdit;
            let array_connection_json;
            let array_connection;
            let company_active = [];
            let worker_active = [];
            let companies = JSON.parse(company_json);
            let company = companies.ADMIN;
            let workers_json = $('#workersForContragentAdmin').val();
            let check_edit;

            if (method === 'create') {
                box_company = $('#step_company_contragent_connection');
                box_with_company_checked = $('#boxWithCompanyChecked');
                box_with_line_company_checked = $(box_company).find(box_with_company_checked);
                box_with_workers_info = $('#step_contragent_worker');
                step = $('#stepNumber');
                nameOperation = $('#nameBlock');
                button_send_ajax = $('#CreateContrAgent');
                button_step_company = $('#SaveContragentCompanyConnection');
            } else if (method === 'edit') {
                box_company = $('#step_company_contragent_connection_edit');
                box_with_company_checked = $('#boxWithCompanyCheckedEdit');
                box_with_line_company_checked = $(box_company).find(box_with_company_checked);
                box_with_workers_info = $('#step_contragent_worker_edit');
                step = $('#stepNumberEdit');
                button_send_ajax = $('#SaveParamsEdit');
                nameOperation = $('#nameBlockEdit');
                button_step_company = $('#SaveContragentCompanyConnectionEdit');
                array_connection_json = $('#personal_contr_agent').val();

                if (array_connection_json !== '') {
                    array_connection = JSON.parse(array_connection_json);
                    $.each(array_connection, function (i, value) {
                        if (i === id_contragent) {
                            company_active = value.COMPANY;
                            worker_active = value.USER;
                        }
                    })
                }

            }


            $(boxInfoContrs).hide(400).addClass('hide_class');
            $(box_company).hide().removeClass('hide_class').show(300);
            $(step).text('2');
            $(nameOperation).text('Настройте доступ к компании');

            $(company).each(
                function (i, value) {
                    check_edit = '';
                    let check = Object.keys(company_active).indexOf(value.COMPANY_ID);
                    if (check !== -1) {
                        check_edit = 'checked';
                    }

                    $(box_with_line_company_checked).append(
                        '<div class="line_worker d-flex justify-content-between flex-row align-items-center ' +
                        'padding_top_10">' +
                        '<div class="d-flex flex-row align-items-center">' +
                        '<span class="icons_worker_comp mr-2"></span>' +
                        '<span data-company-id="' + value.COMPANY_ID + '" class="mr-3 name_box">' + value.NAME_COMP +
                        '</span>' +
                        '</div>' +
                        '<input type="checkbox" class="check_input form-check-input" data-company-id="'
                        + value.COMPANY_ID + '" ' + check_edit + '/></div>');
                }
            );

            $(button_step_company).on('click', function () {
                if (workers_json !== '') {
                    let workers = JSON.parse(workers_json);
                    $(box_company).hide(400).addClass('hide_class');
                    $(box_with_workers_info).hide().removeClass('hide_class').show(300);
                    $(step).text('3');
                    $(nameOperation).text('Настройте доступ сотрудникам');
                    box_with_workers = $(box_with_workers_info).find(boxWorkers);
                    $(box_with_workers).empty();

                    $(workers).each(
                        function (i, value) {
                            let name
                            check_edit = '';
                            check_edit = '';
                            let check = Object.keys(worker_active).indexOf(value.USER_ID_WORKER);
                            if (check !== -1) {
                                check_edit = 'checked';
                            }

                            name = value.NAME !== '' && value.NAME !== null ? value.NAME : value.LOGIN;

                            $(box_with_workers).append(
                                '<div class="line_worker d-flex justify-content-between flex-row align-items-center ' +
                                'padding_top_10">' +
                                '<div class="d-flex flex-row align-items-center">' +
                                '<span class="icons_worker_comp mr-2"></span>' +
                                '<span data-worker-id="' + value.USER_ID_WORKER + '" class="mr-3 name_box">'
                                + name + '</span>' +
                                '</div>' +
                                '<input type="checkbox" class="check_input form-check-input" data-worker-id="'
                                + value.USER_ID_WORKER + '" ' + check_edit + '/></div>');
                        }
                    )
                }
                $('.exit_contragent_connection').on('click', function () {
                    $(box_with_workers_info).hide(200).addClass('hide_class');
                    $(box_company).removeClass('hide_class').show(200);
                    $(box_with_workers).empty();
                    $(step).text('2');
                });
            });


            $(button_send_ajax).on('click', function () {
                arrayCompanyId = [];
                workersArray = [];

                function findCheck(box_find, array_push_id, method) {
                    $(box_find).find('div.line_worker').each(
                        function () {
                            let check = $(this).find('.check_input').prop('checked');
                            let id;
                            if (check === true) {
                                id = $(this).find('.check_input').attr('data-' + method + '-id');
                                array_push_id.push(id);
                            }
                        }
                    );
                    return array_push_id;
                }

                findCheck(boxWorkers, workersArray, 'worker');
                findCheck(box_with_company_checked, arrayCompanyId, 'company');

                arData = {
                    'user_id': arDataInfoContragent.user_id,
                    'INN': arDataInfoContragent.INN,
                    'NAME_CONT': arDataInfoContragent.NAME_CONT,
                    'UrAddress': arDataInfoContragent.UrAddress,
                    'statusPerson': arDataInfoContragent.statusPerson,
                    'company': JSON.stringify(arrayCompanyId),
                    'workers': JSON.stringify(workersArray)
                }

                if (method === 'create') {
                    send_ajax_CreateContragent(arData);
                    $(box_with_company_checked).empty();
                    $(box_company).hide();
                    $(box_with_workers_info).hide();
                    $(boxWorkers).empty();
                    $(boxInfoContrs).find('#NameCont').val('');
                    $(boxInfoContrs).find('#INN').val('');
                    $(boxInfoContrs).find('#UrAddress').val('');
                    $(step).text('1');
                    bool_popup = $('div').is('#popupAddContr');
                    if (bool_popup) {
                        $(nameOperation).text('Заполните данные');
                        $('#popupAddContr').hide(200).removeClass('box_popup');
                    } else {
                        $(boxInfoContrs).removeClass('hide_class').show(200);
                        $(nameOperation).text('Добавьте контрагента');
                    }

                } else if (method === 'edit') {
                    let id = $(this).closest('.contr_agents_box').attr('id');
                    arrayEdit = {
                        'ID': id,
                        'USER_ID': arData.user_id,
                        'method': 'ent_contr_agents',
                        'arParams': arData
                    }
                    send_ajax_edit(arrayEdit, this);
                }
            });


            $('.exit_company_connection').on('click', function () {
                $(box_company).hide(200).addClass('hide_class');
                $(boxInfoContrs).removeClass('hide_class').show(200);
                $(step).text('1');
                $(box_with_company_checked).empty();
            });
        }
    }

    function createContrsForm(that) {
        let statusPerson;
        let function_result;
        let checkParam;
        let arDataInfoContragent;
        let company_json;
        let box = $(that).closest('#personal_contr_agents');
        let user_id = $(box).attr('data-user-id');
        let INN = $(box).find('input#INN').val();
        let NAME_CONT = $(box).find('input#NameCont').val();
        let UrAddress = $(box).find('input#UrAddress').val();
        let boxWorkers = $('#boxWithWorkerChecked');
        let boxInfoContrs = $('#step_create_contragent');
        let id_contragent;

        $(box).find('input.input_check').each(function () {
            checkParam = $(this).prop('checked');
            if (checkParam === true) {
                statusPerson = $(this).attr('id');
            }
        });

        arDataInfoContragent = {
            'user_id': user_id,
            'INN': INN,
            'NAME_CONT': NAME_CONT,
            'UrAddress': UrAddress,
            'statusPerson': statusPerson,
        }

        function_result = CreateContragentsValidation('INFO_CONTRAGENT', arDataInfoContragent);

        if (function_result === 'success') {
            id_contragent = $(that).closest('.box_with_company').attr('id');
            company_json = $('#company_user').val();
            appendListParams(company_json, boxInfoContrs, boxWorkers, arDataInfoContragent, 'create', id_contragent);
        } else {
            $(boxInfoContrs).find('.mess_danger').text(function_result).removeClass('hide_class').show(300);
        }
    }

    function CreateContragentsValidation(method, arData) {

        let results = '';
        $('.mess_danger').empty().hide();
        if (method === 'INFO_CONTRAGENT') {
            if (arData.user_id !== undefined) {
                let INN = arData.INN;
                if (arData.NAME_CONT !== undefined && INN !== undefined && arData.UrAddress !== undefined
                    && arData.NAME_CONT !== '' && INN !== '') {
                    if (arData.statusPerson === 'URIC' && INN.length !== 10 || arData.statusPerson === 'IP' && INN.length !== 12
                        || arData.statusPerson === 'FIZ' && INN.length !== 12) {
                        results = 'Поле ИНН заполнено некорректно.';
                    } else {
                        results = 'success';
                    }
                } else {
                    results = 'Заполните все поля!';
                }
            } else {
                results = 'Заполните все поля!';
            }
        }
        return results;
    }

    $('#createContragentPopup').on('click', function () {
        let box_with_inform_controls = $('#popupAddContr');
        $(box_with_inform_controls).addClass('box_popup').show(200);
        $('#step_create_contragent').removeClass('hide_class').show(200);

        $('#close_popups').on('click', function () {
            $(this).closest('.box_popup').hide(200).remove();
        });

    });

    $('#SaveStepContrAgent').click(function () {
        let that = $(this);
        createContrsForm(that);
    });

// RENDER BOX
    let boolCA = $(inputItem).is('#personal_contr_agent');

    if (boolCA === true) {

        let contr_agent_json = $('input#personal_contr_agent').val();

        if (contr_agent_json !== '') {

            let contr_agent = JSON.parse(contr_agent_json);

            if (contr_agent !== '') {
                $.each(contr_agent, function (i, value) {
                    let class_item = '';
                    let text = 'Архивировать';
                    let button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
                    if (value.ARCHIVED === '1') {
                        class_item = 'notActive';
                        text = 'Восстановить';
                        button_edit = '';
                    }
                    let button = '<span class="icon_edit_lk"><div class="box_edit" style="display: none!important;">'
                        + button_edit +
                        '<span class="ARCHIVE">' + text + '</span></div></span>'
                    if (value.CONTR_AGENT_ACTIVE === '0') {
                        class_item = 'notActive';
                        button = '<span style="font-size: 12px;font-weight: 600">Не подтвержден</span>';
                    }

                    $('#boxWithContrAgents').append('<div id="' + value.CONTR_AGENT_ID + '"' +
                        ' data-attr-arch="' + value.ARCHIVED + '" class="' + class_item + ' mb-3 box_with_company ' +
                        'contr_agents_box" data-method="ent_contr_agents" data-active="' + value.CONTR_AGENT_ACTIVE + '">' +
                        '<div class="d-flex flex-row mb-3 justify-content-between align-items-center">' +
                        '<div class="d-flex align-items-center"><span class="mr-3 icons_worker_contr"></span>' +
                        '<h6 class="mb-0"><b class="nameBox">' + value.NAME_CONT + '</b></h6></div>' + button +
                        '</div>' +
                        '<div class="d-flex flex-column justify-content-between box_contact">' +
                        '<p class="mb-1"><b class="mr-1">ИНН : </b><span class="INN">' + value.INN + '</span></p>' +
                        '<p class="mb-1">' +
                        '<b class="mr-1" >Юридический адрес: </b>' +
                        '<span class="addressEdit">' + value.ADDRESS + '</span></p>' +
                        '<div class="box_for_edit"></div></div>');
                });
            }
        }

    }

//EDIT

    $(document).on('click', function (e) {
        if (e.target.className === "icon_edit_lk" || e.target.className === "close_popups") {
            let nameObj;
            let textEdit;
            let user_id;
            let archived;
            let arch;
            let arContr;
            let that_block = $(e.target);
            let block = $(that_block).closest('.box_with_company');
            let boxes = $(block).find('.box_for_edit');
            let option = $(that_block).find('.box_edit');
            let whoIs = $(block).attr('data-method');
            let boolArch = $(block).attr('data-attr-arch');
            let id = $(block).attr('id');
            let method = $(block).attr('data-method');
            let texts = 'Архивировать';

            archived = boolArch === '0' ? 1 : 0;
            $(option).removeAttr('style').show(200);

            if (whoIs === 'ent_company') {
                nameObj = 'выбранную компанию?';
                textEdit = 'Редактирование компании';
                user_id = $('#company_user').attr('data-user-id');
            } else {
                nameObj = 'выбранного контрагента?';
                textEdit = 'Редактирование контрагента';
                user_id = $('#personal_contr_agents').attr('data-user-id');
            }

            $('.ARCHIVE').on('click', function () {

                let block = $(this).closest('.box_with_company');
                let boxes = $(block).find('.box_for_edit');
                arch = $(block).attr('data-attr-arch');
                $(boxes).empty();
                if (arch === '1') {
                    texts = 'Восстановить';
                }

                $(boxes).append('<div class="d-flex flex-column box_editArch">' +
                    '<p class="mb-lg-5 mb-md-5 mb-4 text_font_19"><b>' + texts + ' ' + nameObj + '</b></p>' +
                    '<div class="d-flex flex-row justify-content-between">' +
                    '<span id="ARCHIVED" class="btn_basket btn_s width_50">Да</span>' +
                    '<span class="close_popups btn_black btn_s width_50">Отмена</span></div></div>').show(200);

                $('#ARCHIVED').on('click', function () {
                    let block = $(this).closest('.box_with_company');
                    let boxes = $(block).find('.box_for_edit');
                    $(block).find('.box_edit').attr('style', 'display: none;');
                    $(boxes).hide(200).empty();

                    arContr = {
                        'CONTR_AGENT_ID': id,
                        'ARCHIVED': archived,
                        'USER_ID': user_id,
                        'method': method,
                    }

                    $.ajax({
                        type: 'POST',
                        url: BX.message('SITE_DIR') +
                            'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
                        data: 'archived=' + JSON.stringify(arContr),
                        success: function (result) {
                            $(boxes).empty();
                            if (result === true) {
                                let text = 'Архивировать';
                                let bool_archived = false;

                                if (archived === 1) {
                                    text = 'Восстановить';
                                    bool_archived = true;
                                }

                                if (method === 'ent_company') {
                                    $(block).attr('data-attr-arch', archived)
                                    if (bool_archived) {
                                        $(block).addClass('notActive');
                                    } else {
                                        $(block).removeClass('notActive');
                                    }
                                } else {
                                    $(block).attr('data-attr-arch', archived)
                                    if (bool_archived) {
                                        $(block).addClass('notActive');
                                    } else {
                                        $(block).removeClass('notActive');
                                    }
                                }
                                $(block).find('.box_edit').attr('style', 'display: none;');
                                $(block).find('.box_edit').find('.ARCHIVE').text(text);
                            } else {
                                $(block).find('span.mess_danger').text('Не удалось совершить операцию');
                            }
                        }
                    });
                });
            });

            $('.EDIT_INFO').on('click', function () {
                let context;
                let times;
                let step_title;
                let phone;
                let user_id;
                block = $(this).closest('.box_with_company');
                boxes = $(block).find('.box_for_edit');
                let name_obj = $(block).find('.nameBox').text();
                let address_obj = $(block).find('.addressEdit').text();
                let Inn = $(block).find('.INN').text();
                $(boxes).empty();

                if (method === 'ent_contr_agents') {

                    user_id = $('#personal_contr_agents').attr('data-user-id');
                    step_title = '<h4 class="font_weight_500 d-flex flex-row"><div id="stepNumberEdit">1</div>/3</h4>';

                    context = '<div class="check_form" id="step_create_contragent_edit">' +
                        '<p class="mess_danger hide_class" style="top: 25%"></p>' +
                        '<label for="NAME_POPAP" class="text_lightgray font_weight_500 text_font_13 mb-1">Название</label>' +
                        '<input value="' + name_obj + '" class="form-control  input_lk mb-3" id="NAME_POPUP" disabled/>' +
                        '<label for="INN_POPAP" class="mb-1 font_weight_500 text_font_13">ИНН</label>' +
                        '<input value="' + Inn + '" class="form-control input_lk mb-3" id="INN_POPUP" />' +
                        '<label for="ADDRESS" class="mb-1 font_weight_500 text_font_13">Юридический адрес</label>' +
                        '<input value="' + address_obj + '" class="form-control input_lk  mb-3" id="ADDRESS" />' +
                        '<div class="mb-3"><a class="text_font_13 red_text_link" href="#" id="ARCHIVED">Архивировать</a>' +
                        '</div><div class="d-flex row_section align-items-center justify-content-between">' +
                        '<a href="javascript:void(0)" class="btn_basket col-5 d-flex justify-content-center color_white"' +
                        ' id="SaveParams">Далее</a>' +
                        '<span class="close_popups btn_black col-5 d-flex justify-content-center btn_s">Отменить</span>' +
                        '</div>' +
                        '</div>' +
                        '<div class="check_form hide_class" id="step_company_contragent_connection_edit">' +
                        ' <div class="d-flex flex-column" id="boxWithCompanyCheckedEdit"></div>' +
                        '<div class="form-group mt-4"><div class="d-flex flex-row align-items-center justify-content-start">' +
                        ' <a href="javascript:void(0)" class="btn btn_red btn_popup text_font_13 mr-3 font_weight_500" ' +
                        'id="SaveContragentCompanyConnectionEdit">Далее</a>' +
                        ' <a href="javascript:void(0)" class="btn_gray btn_popup exit_company_connection text_font_13 ' +
                        'font_weight_500">Назад</a></div></div></div>' +
                        ' <div class="check_form hide_class" id="step_contragent_worker_edit">' +
                        '<div id="boxWithWorkerCheckedEdit"></div><div class="form-group mt-4">' +
                        '<div class="d-flex flex-row align-items-center justify-content-start">' +
                        '<a href="javascript:void(0)" class="btn btn_red btn_popup text_font_13 mr-3 font_weight_500" ' +
                        'id="SaveParamsEdit">Сохранить</a>' +
                        '<a href="javascript:void(0)" class="btn_gray btn_popup exit_contragent_connection text_font_13 ' +
                        'font_weight_500">Назад</a></div></div></div>';

                } else if (method === 'ent_company') {

                    step_title = '';
                    user_id = $('#company_user').attr('data-user-id');
                    times = $(block).find('.times').text();
                    phone = $(block).find('.phone').text();

                    context = '<label for="NAME_POPAP" class="text_lightgray font_weight_500 text_font_13 mb-1">Название</label>' +
                        '<input value="' + name_obj + '" class="form-control  input_lk mb-3" id="NAME_POPUP"/>' +
                        '<label for="ADDRESS" class="mb-1 font_weight_500 text_font_13">Адрес доставки</label>' +
                        '<input value="' + address_obj + '" class="form-control input_lk  mb-3" id="ADDRESS" />' +
                        '<label for="TIMES" class="mb-1 font_weight_500 text_font_13">Время работы</label>' +
                        '<input value="' + times + '" class="form-control input_lk  mb-3" id="TIMES" />' +
                        '<label for="PHONE" class="mb-1 font_weight_500 text_font_13">Номер телефона</label>' +
                        '<input value="' + phone + '" class="form-control input_lk  mb-3" id="PHONE" />' +
                        '<a class="text_font_13 red_text_link mb-3" href="#" id="ARCHIVED">Архивировать</a>' +
                        '<div class="d-flex row_section align-items-center justify-content-between">' +
                        '<a href="javascript:void(0)" class="btn_basket col-5 d-flex justify-content-center color_white"' +
                        ' id="EditSave">Сохранить</a>' +
                        '<span class="close_popups btn_black col-5 d-flex justify-content-center btn_s">Отменить</span>' +
                        '</div>';
                }

                $(boxes).append('<div class="d-flex flex-column box_editArch form-group">' +
                    '<div class="width_100 d-flex flex-row justify-content-between mb-4">' +
                    '<label class="label_company" id="nameBlock">' + textEdit + '</label>' + step_title + '</div>'
                    + context + '</div>').show(200);


                $('#EditSave').on('click', function () {
                    let name_obj = $(this).closest('.box_editArch').find('#NAME_POPUP').val();
                    let address_obj = $(this).closest('.box_editArch').find('#ADDRESS').val();
                    let arParams;
                    let PHONE;
                    let TIMES;
                    let INN;
                    let arObj;

                    if (method === 'ent_company') {
                        PHONE = $(this).closest('.box_editArch').find('#PHONE').val();
                        TIMES = $(this).closest('.box_editArch').find('#TIMES').val();
                        arParams = {
                            'NAME': name_obj,
                            'UrAddress': address_obj,
                            'PHONE': PHONE,
                            'TIMES': TIMES
                        }
                    } else {
                        INN = $(this).closest('.box_editArch').find('#INN_POPUP').val();
                        arParams = {
                            'NAME': name_obj,
                            'UrAddress': address_obj,
                            'INN': INN,
                            'user_id': user_id,
                            'ID': id
                        }
                    }

                    arObj = {
                        'ID': id,
                        'USER_ID': user_id,
                        'method': method,
                        'arParams': arParams
                    }

                    send_ajax_edit(arObj, this);
                })

                $('#SaveParams').on('click', function () {
                    let boxWorkers = $('#boxWithWorkerCheckedEdit');
                    let boxInfoContrs = $('#step_create_contragent_edit');
                    let company_json = $('#company_user').val();
                    let name_obj = $(this).closest('.box_editArchf').find('#NAME_POPUP').val();
                    let address_obj = $(this).closest('.box_editArch').find('#ADDRESS').val();
                    let INN = $(this).closest('.box_editArch').find('#INN_POPUP').val();
                    let id_contragent;
                    id_contragent = $(this).closest('.box_with_company').attr('id');
                    let arObj = {
                        'user_id': user_id,
                        'INN': INN,
                        'NAME_CONT': name_obj,
                        'UrAddress': address_obj,
                        'statusPerson': 'admin',
                    }
                    appendListParams(company_json, boxInfoContrs, boxWorkers, arObj, 'edit', id_contragent)
                });

            });
        }
        $('.close_popups').on('click', function () {
            $(this).closest('.box_with_company').find('.box_edit').attr('style', 'display: none;');
            $(this).closest('.box_with_company').find('.box_for_edit').hide(200).empty();
        });
    });

    $('.EDIT_INFO_USER').on('click', function () {

        let nameUser = $(this).closest('tr').find('.name_user').text();
        let emailUser = $(this).closest('tr').find('.email_user').text();
        let phoneUser = $(this).closest('tr').find('.phone_user').text();

        if ($(this).closest('.icon_edit_lk').find('.box_editArch') !== null) {
            $(this).closest('.icon_edit_lk').find('.box_editArch').remove();
        }

        let context = `<label for="NAME_POPAP" class="text_lightgray font_weight_500 text_font_13 mb-1">ФИО</label>
            <span class="FIOError" style="color: red"></span>
            <input value="${nameUser}" class="form-control  input_lk mb-3" id="NAME_POPUP"/>
            <label for="ADDRESS" class="mb-1 font_weight_500 text_font_13">Адрес электронной почты</label>
            <span class="email_error" style="color: red"></span>
            <input value="${emailUser}" class="form-control input_lk  mb-3" id="ADDRESS" />
            <label for="PHONE" class="mb-1 font_weight_500 text_font_13">Номер телефона</label>
            <input value="${phoneUser}" class="form-control input_lk  mb-3" id="PHONE" />
            <div class="d-flex row_section align-items-center justify-content-between">
            <a href="javascript:void(0)" class="btn_basket col-5 d-flex justify-content-center color_white" id="EditSave">Сохранить</a>
            <span class="close_popups btn_black col-5 d-flex justify-content-center btn_s">Отменить</span>
           </div>`;

        let textEdit = 'Редактирование пользователя';

        $(this).closest('.icon_edit_lk').append('<div class="d-flex flex-column box_editArch form-group">' +
            '<div class="width_100 d-flex flex-row justify-content-between mb-4">' +
            '<label class="label_company" id="nameBlock">' + textEdit + '</label></div>'
            + context + '</div>').show(200);

        $(this).closest('.box_edit').hide();

        $('.close_popups').on('click', function () {
            $(this).closest('.box_editArch').remove();
        });

        $('#EditSave').on('click', function () {
            let newNameUser = $(this).closest('.box_editArch').find('#NAME_POPUP').val();
            let newEmailUser = $(this).closest('.box_editArch').find('#ADDRESS').val();
            let newPhoneUser = $(this).closest('.box_editArch').find('#PHONE').val();
            let userId = $(this).closest('tr').attr('data-user-id-worker');


            let newArrObj = {
                'ID': userId,
                'NAME': newNameUser,
                'EMAIL': newEmailUser,
                'PHONE': newPhoneUser
            }

            if (/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/.test(newEmailUser) === false) {
                $('.email_error').text("Введите корректный email");
                return;
            }

            if (newNameUser.length === 0) {
                $('.FIOError').text("Введите имя пользователя");
                return;
            }

            function ajaxForUpdateUser(that, newArrObj) {
                $.ajax({
                    type: 'POST',
                    url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax_for_update_user.php',
                    data: {
                        'newValues': JSON.stringify(newArrObj)
                    },
                    success: function () {
                        $(that).closest('tr').find('.name_user').html(newArrObj['NAME']);
                        $(that).closest('tr').find('.email_user').html(newArrObj['EMAIL']);
                        $(that).closest('tr').find('.phone_user').html(newArrObj['PHONE']);
                        $(that).closest('.box_editArch').remove();
                    }
                })
            }

            ajaxForUpdateUser(this, newArrObj);
        })
    })

    $('.ARCHIVE_USER').on('click', function () {

        if ($(this).closest('.icon_edit_lk').find('.box_editArch') !== null) {
            $(this).closest('.icon_edit_lk').find('.box_editArch').remove();
        }

        $(this).closest('.icon_edit_lk').append('<div class="d-flex flex-column box_editArch">' +
            '<p class="mb-lg-5 mb-md-5 mb-4 text_font_19"><b>Архивировать сотрудника</b></p>' +
            '<div class="d-flex flex-row justify-content-between">' +
            '<span id="ARCHIVED" class="btn_basket btn_s width_50">Да</span>' +
            '<span class="close_popups btn_black btn_s width_50">Отмена</span></div></div>').show(200);

        $(this).closest('.box_edit').hide();

        $('.close_popups').on('click', function () {
            $(this).closest('.box_editArch').remove();
        });

        $('#ARCHIVED').on('click', function () {
            let userId = $(this).closest('tr').attr('data-user-id-worker');
            let archived = $(this).closest('tr').find('.sorting_1').attr('activity');

            let newArrObj = {
                'ID': userId
            }

            if (archived === 'Y') {
                newArrObj['ACTIVE'] = 'N';
                $(this).closest('tr').find('.sorting_1').attr('activity', 'N');
            } else {
                newArrObj['ACTIVE'] = 'Y';
                $(this).closest('tr').find('.sorting_1').removeClass('archived_user');
                $(this).closest('tr').find('.phone_user').removeClass('archived_user');
                $(this).closest('tr').find('.sorting_1').attr('activity', 'Y');
            }

            function ajax_for_archive_user(that, newArrObj) {
                $.ajax({
                    type: 'POST',
                    url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax_for_diactivate_user.php',
                    data: {
                        'newValues': JSON.stringify(newArrObj)
                    },
                    success: function () {
                        if (newArrObj['ACTIVE'] === 'N') {
                            $(that).closest('tr').find('.sorting_1').addClass('archived_user');
                            $(that).closest('tr').find('.phone_user').addClass('archived_user');
                        }
                        $(that).closest('.box_editArch').remove();
                    }
                })
            }

            ajax_for_archive_user(this, newArrObj)
        })
    })

    function addWorker(that) {
        $('.email_error').text("");
        $('.FIOError').text("");
        let user_id = $('#personal_worker').attr('data-user-id');
        let user_name = $(that).closest('.form_company_many').find('#FIOWorker').val();
        let email = $(that).closest('.form_company_many').find('#EmailWorker').val();
        let phone = $(that).closest('.form_company_many').find('#PhoneWorker').val();
        let arData;
        let type;

        if (/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/.test(email) === false) {
            $('.email_error').text("Введите корректный email");
            return;
        }

        if (user_name.length === 0) {
            $('.FIOError').text("Введите имя пользователя");
            return;
        }

        type = "EMAIL";

        arData = {
            'LOGIN': user_name,
            'VALUE': email,
            'TYPE': type,
            'PHONE': phone,
            'user_id': user_id
        }
        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') +
                'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data: 'createWorker=' + JSON.stringify(arData),
            success: function (result) {
                if (isNaN(parseInt(result))) {
                    $('.error').html(result);
                    return;
                }
                $('.bx-background-image').prepend('<div class="modal fade" style="display: block; opacity: 1; background: rgba(0,0,0,.5)">\n' +
                    '  <div class="modal-dialog modal-dialog-centered" role="document">\n' +
                    '    <div class="modal-content">\n' +
                    '      <div class="modal-header">\n' +
                    '        <h5 class="modal-title" id="exampleModalLabel">Успешное выполнение</h5>\n' +
                    '        <button type="button" class="close btn-close-modal" data-dismiss="modal" aria-label="Close">\n' +
                    '          <span aria-hidden="true">&times;</span>\n' +
                    '        </button>\n' +
                    '      </div>\n' +
                    '      <div class="modal-body">\n' +
                    '        Пользователь успешно добавлен\n' +
                    '      </div>\n' +
                    '      <div class="modal-footer">\n' +
                    '        <button type="button" class="btn btn-secondary btn-close-modal" data-dismiss="modal">Закрыть</button>\n' +
                    '      </div>\n' +
                    '    </div>\n' +
                    '  </div>\n' +
                    '</div>').css('overflow-y', 'hidden')

                $('#boxWithContrPeople').hide(200).empty();
                $('table#TableWorkers').find('tbody').append(`<tr data-user-id-worker="${result}">
                                <td class="d-flex flex-row align-items-center">
                                    <span class="avatar mr-3" style="background-color:#a321c3">
                                          <span class="name">${arData['LOGIN'][0]}</span>
                                    </span>
                                    <div>
                                        <a href="/personal/workers/user/${result}"
                                           class="d-flex flex-column">
                                        <span class="name_user">${arData['LOGIN']}</span>
                                        <span class="email_user">${arData['VALUE']}</span>
                                        </a>
                                    </div>
                                </td>
                                <td class="phone_user">${arData['PHONE']}</td>
                                <td></td>
                                <td></td>
                                <td><span class="icon_edit_lk">
                                    <div class="box_edit" style="display: none;">
                                        <span class="EDIT_INFO_USER">Редактировать</span>
                                        <span class="ARCHIVE_USER">Архивировать</span>
                                    </div>
                                </span></td>
                            </tr>`);

            }
        });
    }

// WORKERS
    $('#CreateWorker').on('click', function () {
        addWorker(this)
    });

    $('.check_input').on('click', function () {
        let checked = $(this).attr('checked');
        if (checked === 'checked') {
            $(this).removeAttr('checked');
        } else {
            $(this).attr('checked', 'checked');
        }

    })
    $('#SaveParamsWorker').on('click', function () {

        let worker_container = $('div.name_worker');
        let box_with_company = $('.workers_company');
        let worker_id = $(worker_container).attr('data-worker-id');
        let worker_message = $(worker_container).find('span.message');
        let box_with_contragents = $('.workers_contragents');
        $(worker_message).text('').removeClass('color_green').removeClass('red_text');
        let arDataWorker;
        let id_contragents_true;
        let id_contragents_all;
        let id_company_all;
        let id_company_true;

        function eachElemAll(that) {
            let id;
            let ids = [];

            $(that).find('.line_info').each(function () {
                let method = $(this).find('input').attr('data-method-for-workers');
                id = $(this).find('.name_box').attr('data-' + method + '-id');
                ids.push(id);
            });
            return ids;
        }

        function eachElemTrue(that) {
            let id;
            let ids_true = [];
            $(that).find('.line_info').each(function () {
                let checked = $(this).find('input').prop('checked');
                let method = $(this).find('input').attr('data-method-for-workers');
                if (checked === true) {
                    id = $(this).find('.name_box').attr('data-' + method + '-id');
                    ids_true.push(id);
                }
            });
            return ids_true;
        }

        id_contragents_true = eachElemTrue(box_with_contragents);
        id_contragents_all = eachElemAll(box_with_contragents);
        id_company_true = eachElemTrue(box_with_company);
        id_company_all = eachElemAll(box_with_company);

        arDataWorker = {
            'CONTR_AGENT_ID': JSON.stringify(id_contragents_all),
            'COMPANY_ID': JSON.stringify(id_company_all),
            'CONTR_AGENT_TRUE': JSON.stringify(id_contragents_true),
            'COMPANY_ID_TRUE': JSON.stringify(id_company_true),
            'USER_ID': worker_id
        }

        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') +
                'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            data: 'editWorkerControls=' + JSON.stringify(arDataWorker),
            success: function (result) {
                $(worker_message).text('');
                if (result === 'success') {
                    $(worker_message).text('Изменения сохранены').addClass('color_green').show(200);
                } else {
                    $(worker_message).text('Ошибка присваивания прав доступа сотруднику!').addClass('red_text').show(200);
                }
            }
        });
    });

    function stringToColor(str) {
        let hash = 0;
        let color = '#';
        let i;
        let value;
        let strLength;

        if (!str) {
            return color + '333333';
        }

        strLength = str.length;

        for (i = 0; i < strLength; i++) {
            hash = str.charCodeAt(i) + ((hash << 5) - hash);
        }

        for (i = 0; i < 3; i++) {
            value = (hash >> (i * 8)) & 0xFF;
            color += ('00' + value.toString(16)).substr(-2);
        }

        return color;
    }

    function eachUsersColor(that) {
        let name;
        let letter;
        let backgroundColor;
        name = $(that).find('span.name').text();
        letter = name.substr(0, 1);
        backgroundColor = stringToColor(name);
        $(that).find('span.avatar').attr('style', 'background-color:' + backgroundColor);
        $(that).find('span.name').text(letter);
        let button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
        if (worker_pages || worker_pages_home) {
            $(that).find('.icon_edit_lk').append('<div class="box_edit" style="display: none;">'
                + button_edit + '<span class="ARCHIVE">Архивировать</span></div>');
        }
    }

    if (workers) {
        let table = $('#TableWorkers');
        $(table).DataTable({
            "paging": false,
            "language": {
                "search": " ",
                "searching": "Результатов не найдено"
            },
            "bInfo": false,
            select: true
        });
        $('#TableWorkers_filter label').addClass('order-3').find('input').attr('placeholder',
            'Имя, фамилия или почта').addClass('form-control');

        let company_json = $('#companyArrayForSelected').val();
        let contrAgent_json = $('#contrAgentArrayForSelected').val();

        $('#TableWorkers_filter').attr('class', 'd-flex row-section justify-content-between align-items-center')
            .append('<select class="order-1" id="CompanySelected"><option>Компании</option></select>' +
                '<select class="order-2" id="ContrAgent"><option>Контрагенты</option></select>' +
                '<a class="btn_company btn order-4" href="javascript:void(0)" id="AddWorker">Добавить сотрудника</a>');
        $('#ContrAgent').select2();
        $('#CompanySelected').select2();
        if (company_json !== '' && company_json !== null || contrAgent_json !== '' && contrAgent_json !== null) {
            let company = JSON.parse(company_json);

            let contrAgent = JSON.parse(contrAgent_json);
            $(company).each(function (i, valueComp) {
                $('#CompanySelected').append('<option value="' + valueComp.COMPANY_ID + '">' + valueComp.NAME_COMP + '</option>')
            });
            $(contrAgent).each(function (i, valueCont) {
                $('#ContrAgent').append('<option value="' + valueCont.CONTR_AGENT_ID + '">' + valueCont.NAME_CONT + '</option>')
            });
        }

        $(table).find('tbody tr').each(function () {
                eachUsersColor(this);
            }
        );


        $('#AddWorker').on('click', function () {
            let box = $('#boxWithContrPeople');
            $(box).empty();
            $(box).append('<form class="form_company_many filters mb-5">' +
                '<div class="position-absolute close_modalWindow"></div>' +
                '<div class="form-group mb-2">' +
                '<label class="label_company">Добавить сотрудника</label></div>' +
                '<div class="form-group mb-3 col-12"><span style="color: red" class="FIOError"></span>' +
                '<input required type="text" class="form-control input_lk" id="FIOWorker" autocomplete="off" placeholder="ФИО">' +
                '</div><div class="form-group mb-4 col-12"><span style="color: red" class="email_error"></span>' +
                '<input type="text" class="form-control input_lk" id="EmailWorker" autocomplete="off" ' +
                'placeholder="Адрес электронной почты"></div>' +
                '<div class="form-group mb-4 col-12">' +
                '<input type="text" class="form-control input_lk" id="PhoneWorker" autocomplete="off" ' +
                'placeholder="Телефон"><span style="color: red" class="error"></span></div>' +
                '<div class="form-group">' +
                '<div class="col-4"><a href="javascript:void(0)" class="btn btn_popup text_font_13 btn_red"' +
                ' id="CreateWorker" data-toggle="modal" data-target="#exampleModal">Сохранить</a></div></div></form>').show(200);

            $("#PhoneWorker").mask("+7-(999) 999-9999");

            $('#CreateWorker').on('click', function () {
                addWorker(this);
            });
        });
    }

    if (worker_pages) {
        eachUsersColor(this);
    }

    if (worker_pages_home) {
        $('#worker_pages_lk_home').find('.line_worker').each(function () {
            eachUsersColor(this);
        });
    }

//

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
    let all_cities = $('#cities-list');
    let big_cities = $('#big-cities-list');
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
        let basketItems = BX.namespace('BX.Sale.BasketComponent');
        let classes = $(this).attr('data-sort');
        let sort = false;
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
    // переключение на вид картога карточками
    $(document).on('click', '#card_catalog', function () {
        document.cookie = 'orientation=card';
        $('.catalog-section').removeClass('by-line').addClass('by-card');
        $('.icon_sort_line_active').addClass('icon_sort_line').removeClass('icon_sort_line_active');
        $(this).addClass('icon_sort_bar_active').removeClass('icon_sort_bar');
    });
    // переключение на вид картога списком
    $(document).on('click', '#line_catalog', function () {
        document.cookie = 'orientation=line';
        $('.catalog-section').removeClass('by-card').addClass('by-line');
        $('.icon_sort_bar_active').addClass('icon_sort_bar').removeClass('icon_sort_bar_active');
        $(this).addClass('icon_sort_line_active').removeClass('icon_sort_line');
    });


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
    })


    // $('.catalog_sort_item').on('click', function () {
    //     $(this).parents('.sort_orders_element').hide();
    // })


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
    /*
        $(".datepicker-here").datepicker({
            onSelect: function (selectedDate) {
                if (/\s.\s/.test(selectedDate) === false) {
                    return;
                }
                let arr = selectedDate.split(/\s.\s/);
                let arrOfDate = {
                    'firstDate': arr[0],
                    'secondDate': arr[arr.length - 1],
                    'url': document.location.search,
                }
                $.ajax({
                    url: BX.message('SITE_DIR') +
                        'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax.php',
                    type: 'POST',
                    data: {
                        arrOfDate: JSON.stringify(arrOfDate),
                    },
                    success: function (response) {
                        let data = JSON.parse(response);
                        $('.sale-order-list-inner-container').remove();
                        if (data === null) {
                            $('#content_box').append('Заказов в выбранный промежуток времени нет');
                        } else {
                            data.forEach((item, index) => {
                                function addPictures() {
                                    item['PICTURE'].forEach(item => {
                                        $(`.sort_by_date_orders_${index}`).append(`<img class="image_box_orders" src="${item}"/>`)
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
                    }
                })
            }
        });
    */

    $(document).on('click', '.retail_orders', function () {
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
    })

    /*NEW*/
    $('.sort_mobile').on('click', function () {

        $('.box_filter_catalog').show();
        $('body').css({'overflow': 'hidden'});
    });
    $('.closeButtonFilter').on('click', function () {

        $('.box_filter_catalog').hide();
        $('body').css({'overflow': 'initial'});
    });

    if (window.screen.width < 1024) {
        var top_page = $('.section_wrapper').offset().top;
        $('.box_filter_catalog').css({'top': top_page});
    }


    $('.shared i').on('click', function () {
        let el_id = $(this).parent().data('element-id');
        $(this).next().toggle();
        /*var obj_id = this;
        $.ajax({
            type: 'GET',
            url: '/local/ajax/shared.php?ELEMENT_ID='+el_id,

            success: function (result) {
                console.log(result);
                $(obj_id).find('.shared_block').html(result);
            }
        });*/

        return false;
    });

    $('.smart-filter-tog').on('click', function () {
        var code_vis = $(this).data('code-vis');
        console.log(code_vis);
        $('.catalog-section-list-item-sub[data-code="' + code_vis + '"]').toggleClass('active');
        $(this).toggleClass('smart-filter-angle-up');
    });

});

window.onresize = function (event) {
    if ($('div').is('.basket_category') && window.screen.width <= 746) {
        $('.basket_category').css('width', window.screen.width - 20);
    }
};


$(document).ready(function () {
    $('.search_mobile').on('click', function () {

        $('.box_with_search').toggle();
    });


    var PAGE = $('html, body');
    var pageScroller = $('.page-scroller'),
        pageYOffset = 0,
        inMemory = false,
        inMemoryClass = 'page-scroller--memorized',
        isVisibleClass = 'page-scroller--visible',
        enabledOffset = 60;

    function resetPageScroller() {

        setTimeout(function () {

            if (window.pageYOffset > enabledOffset) {
                pageScroller.addClass(isVisibleClass);
            } else if (!pageScroller.hasClass(inMemoryClass)) {
                pageScroller.removeClass(isVisibleClass);
            }
        }, 150);

        if (!inMemory) {

            pageYOffset = 0;
            pageScroller.removeClass(inMemoryClass);
        }

        inMemory = false;
    }

    if (pageScroller.length > 0) {

        window.addEventListener('scroll', resetPageScroller, window.supportsPassive ? {passive: true} : false);

        pageScroller.on('click', function (event) {

            event.preventDefault();

            window.removeEventListener('scroll', resetPageScroller);

            if (window.pageYOffset > 0 && pageYOffset === 0) {

                inMemory = true;
                pageYOffset = window.pageYOffset;

                pageScroller.addClass(inMemoryClass);

                PAGE.stop().animate({scrollTop: 0}, 500, 'swing', function () {
                    window.addEventListener('scroll', resetPageScroller, window.supportsPassive ? {passive: true} : false);
                });
            } else {

                pageScroller.removeClass(inMemoryClass);

                PAGE.stop().animate({scrollTop: pageYOffset}, 500, 'swing', function () {

                    pageYOffset = 0;
                    window.addEventListener('scroll', resetPageScroller, window.supportsPassive ? {passive: true} : false);
                });
            }
        });
    }
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

$(document).on('submit', '.form-form', function (e) {
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

$(document).on('submit', '.callback_form', function () {
    /*if( !$('#agree6').is(':checked') )
    {
        $('.checkboxes_error').html('Примите условия').show();
        return false;
    }*/
    $('.callback_form .error_field').hide();
    var err = 0;

    if ($('.callback_form input[name="PHONE"]').val() == '') {
        $('.er_CALLBACK_PHONE').html('Поле не заполнено');
        $('.er_CALLBACK_PHONE').show();
        var err = 1;
    }

    if (err != 1) {
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
    $('.callback').on('click', function () {
        $("#callbackModal").arcticmodal(
            {
                closeOnOverlayClick: true,
                afterClose: function (data, el) {
                }
            });
    });
});
// Открытие попап обратного звонка: конец