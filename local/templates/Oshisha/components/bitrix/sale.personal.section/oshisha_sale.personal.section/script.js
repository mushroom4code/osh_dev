
$(document).ready(function () {

    if (window.daDataParam !== undefined && window.daDataParam.token !== undefined) {
        $('#CompanyAddress').suggestions({
            token: window.daDataParam.token,
            type: "ADDRESS",
            hint: false,
        });
    }

    $('#addCompanyPopup').on('click', function () {
        let box_mess = $('#popupAdd');
        $(box_mess).empty();
        $(box_mess).append('<div class="box_editArch form_company_many" style="left: 100%;" id="personal_company">' +
            '<div class="width_100 d-flex flex-row justify-content-between mb-4">' +
            '<label class="label_company" id="nameBlock">Заполните данные</label></div>' +
            '<label for="NAME_POPAP" class="text_lightgray font_weight_500 text_font_13 mb-1">Название</label>' +
            '<input placeholder="Введите название компании" class="form-control  input_lk mb-3" id="CompanyName"/>' +
            '<label for="ADDRESS" class="mb-1 font_weight_500 text_font_13">Адрес доставки</label>' +
            '<input placeholder="Адрес компании" class="form-control input_lk  mb-3" id="CompanyAddress" />' +
            '<label for="TIMES" class="mb-1 font_weight_500 text_font_13">Время работы</label>' +
            '<input  class="form-control input_lk  mb-3"  placeholder="Время работы" id="CompanyTime" />' +
            '<label for="PHONE" class="mb-1 font_weight_500 text_font_13">Номер телефона</label>' +
            '<input placeholder="+7-(900)-999-99-99" class="form-control input_lk  mb-3" id="CompanyTelephone" />' +
            '<div class="form-group"><div class="col-md-4 col-lg-4 col-12">' +
            '<a href="javascript:void(0)" class="btn btn_company" id="CreateCompany">Сохранить</a>' +
            '</div></div>').show(200);

        if (window.daDataParam.token !== undefined) {
            $('#CompanyAddress').suggestions({
                token: window.daDataParam.token,
                type: "ADDRESS",
                hint: false,
            });
        }
    })

    $('#date_interval_orders').datepicker({
        clearButton: true,
    });

    let $isPickerButtons = $('#datepickers-container').find('.datepicker--buttons');
    if ($isPickerButtons) {
        $isPickerButtons.append(
            '<span class="datepicker--button" js-action="apply">Применить</span>'
        );
    }
    let $applyButton = $('[js-action="apply"]');
    $applyButton.on('click', function (event) {
        event.stopPropagation();
        startLoader();
        let datepicker = $('#date_interval_orders').data('datepicker');
        datepicker.hide();
        let typeSort = $('div#personal_orders span.sort_orders_by').attr('data-sort-order');
        let sortStatus = $('div#personal_orders div.sort_orders_elements').attr('data-sort-status');
        var options = {
            year: 'numeric',
            month: 'numeric',
            day: 'numeric',
            timezone: 'UTC'
        };
        let data = {typeSort: typeSort, sortStatus: sortStatus};
        if (datepicker.selectedDates.length === 2) {
            data['dateInterval'] = datepicker.selectedDates[0].toLocaleString("ru", options) + ' - ' + datepicker.selectedDates[1].toLocaleString("ru", options);
        }

        $.ajax({
            url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort.php',
            type: 'POST',
            data: data,
            success: function (response) {
                endLoader();
                if (response != 'error') {
                    $('.sale-order-list-inner-container').remove();
                    $('div#personal_orders').append(response);
                    $('a.sale-order-list-repeat-link').on('click', copyOrderPopups);
                }
            }
        });
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

    var loadingScreen;
    var BXFormPosting = false;

    function startLoader()
    {
        if (BXFormPosting === true)
            return false;

        BXFormPosting = true;

        if (!loadingScreen)
        {
            loadingScreen = new BX.PopupWindow('loading_screen', null, {
                overlay: {backgroundColor: 'black', opacity: 1},
                events: {
                    onAfterPopupShow: BX.delegate(function () {
                        BX.cleanNode(loadingScreen.popupContainer);
                        BX.removeClass(loadingScreen.popupContainer, 'popup-window');
                        loadingScreen.popupContainer.appendChild(
                            BX.create('DIV', {props: {className: 'lds-dual-ring'}})
                        );
                        loadingScreen.popupContainer.removeAttribute('style');
                        loadingScreen.popupContainer.style.display = 'block';
                    }, this)
                }
            });
            console.log(loadingScreen);
            BX.addClass(loadingScreen.overlay.element, 'bx-step-opacity');
        }

        loadingScreen.overlay.element.style.opacity = '0';
        loadingScreen.show();
        loadingScreen.overlay.element.style.opacity = '0.6';

        return true;
    }


    function endLoader()
    {
        BXFormPosting = false;

        if (loadingScreen && loadingScreen.isShown())
        {
            loadingScreen.close();
        }
    }
    $('#date_interval_orders').datepicker({
        onSelect: function (date, datepicker) {
            if (/^\d\d.\d\d.\d\d\d\d\s-\s\d\d.\d\d.\d\d\d\d$/.test(date)) {
                let typeSort = $('div#personal_orders span.sort_orders_by').attr('data-sort-order');
                let sortStatus = $('div#personal_orders div.sort_orders_elements').attr('data-sort-status');
                $.ajax({
                    url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort.php',
                    type: 'POST',
                    data: {dateInterval: date, typeSort: typeSort, sortStatus: sortStatus},
                    success: function (response) {
                        console.log(response)
                        if (response != 'error') {
                            $('.sale-order-list-inner-container').remove();
                            $('div#personal_orders').append(response);
                            $('a.sale-order-list-repeat-link').on('click', copyOrderPopups);
                        }
                    }
                })
            }
        }
    });

    $('#date_interval_orders').datepicker().on('blur', function () {
        if (!(/^\d\d.\d\d.\d\d\d\d\s-\s\d\d.\d\d.\d\d\d\d$/.test($(this).val()))) {
            let typeSort = $('div#personal_orders span.sort_orders_by').attr('data-sort-order');
            let sortStatus = $('div#personal_orders div.sort_orders_elements').attr('data-sort-status');
            $.ajax({
                url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/sale.personal.order.list/oshisha_sale.personal.order.list/ajax_for_sort.php',
                type: 'POST',
                data: {sortStatus: sortStatus, typeSort: typeSort},
                success: function (response) {
                    console.log(response)
                    if (response != 'error') {
                        $('.sale-order-list-inner-container').remove();
                        $('div#personal_orders').append(response);
                        $('a.sale-order-list-repeat-link').on('click', copyOrderPopups);
                    }
                }
            })
        }
    });
});