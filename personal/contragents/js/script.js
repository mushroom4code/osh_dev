// CONTR_AGENT
function send_ajax_CreateContragent(arData) {

    let resultMessage = '',
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
                let class_item = '',
                    text = 'Архивировать',
                    button_edit = '<span  class="EDIT_INFO">Редактировать</span>';
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
        let box_company, box_with_company_checked, box_with_line_company_checked, box_with_workers_info, step,
            nameOperation, button_step_company, box_with_workers, arData, bool_popup, button_send_ajax, arrayEdit,
            array_connection_json, array_connection, company_active = [], worker_active = [],
            companies = JSON.parse(company_json), company = companies.ADMIN,
            workers_json = $('#workersForContragentAdmin').val(), check_edit;

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
           let arrayCompanyId = [];
            let workersArray = [];

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

console.log('test')
function createContrsForm(that) {
    console.log('test')
    console.log(that)
    let statusPerson, function_result, checkParam, arDataInfoContragent, company_json,
        box = $(that).closest('#personal_contr_agents'),
        user_id = $(box).attr('data-user-id'),
        INN = $(box).find('input#INN').val(),
        NAME_CONT = $(box).find('input#NameCont').val(),
        UrAddress = $(box).find('input#UrAddress').val(),
        boxWorkers = $('#boxWithWorkerChecked'),
        boxInfoContrs = $('#step_create_contragent'),
        id_contragent;

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
    console.log(arDataInfoContragent)
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

$('#CreateContrAgent').on('click',function () {
    let that = $(this);
    createContrsForm(that);
});

// RENDER BOX
let boolCA = $('input').is('#personal_contr_agent');

if (boolCA === true) {

    let contr_agent_json = $('input#personal_contr_agent').val();

    if (contr_agent_json !== '') {

        let contr_agent = JSON.parse(contr_agent_json);

        if (contr_agent !== '') {
            $.each(contr_agent, function (i, value) {
                let class_item = '',
                    text = 'Архивировать',
                    button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
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
        let nameObj, textEdit, user_id, archived, arch, arContr, that_block = $(e.target),
            block = $(that_block).closest('.box_with_company'),
            boxes = $(block).find('.box_for_edit'),
            option = $(that_block).find('.box_edit'),
            whoIs = $(block).attr('data-method'),
            boolArch = $(block).attr('data-attr-arch'),
            id = $(block).attr('id'),
            method = $(block).attr('data-method'),
            texts = 'Архивировать';

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

            let block = $(this).closest('.box_with_company'),
                boxes = $(block).find('.box_for_edit');
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
                let block = $(this).closest('.box_with_company'),
                    boxes = $(block).find('.box_for_edit');
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
            let context, times, step_title, phone, user_id;
            block = $(this).closest('.box_with_company');
            boxes = $(block).find('.box_for_edit');
            let name_obj = $(block).find('.nameBox').text(),
                address_obj = $(block).find('.addressEdit').text(),
                Inn = $(block).find('.INN').text();
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
                let name_obj = $(this).closest('.box_editArch').find('#NAME_POPUP').val(),
                    address_obj = $(this).closest('.box_editArch').find('#ADDRESS').val(),
                    arParams,
                    PHONE,
                    TIMES,
                    INN,
                    arObj;

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
                let boxWorkers = $('#boxWithWorkerCheckedEdit'),
                    boxInfoContrs = $('#step_create_contragent_edit'),
                    company_json = $('#company_user').val(),
                    name_obj = $(this).closest('.box_editArchf').find('#NAME_POPUP').val(),
                    address_obj = $(this).closest('.box_editArch').find('#ADDRESS').val(),
                    INN = $(this).closest('.box_editArch').find('#INN_POPUP').val(),
                    id_contragent;
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

    let nameUser = $(this).closest('tr').find('.name_user').text(),
        emailUser = $(this).closest('tr').find('.email_user').text(),
        phoneUser = $(this).closest('tr').find('.phone_user').text();

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
        let newNameUser = $(this).closest('.box_editArch').find('#NAME_POPUP').val(),
            newEmailUser = $(this).closest('.box_editArch').find('#ADDRESS').val(),
            newPhoneUser = $(this).closest('.box_editArch').find('#PHONE').val(),
            userId = $(this).closest('tr').attr('data-user-id-worker');


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
        let userId = $(this).closest('tr').attr('data-user-id-worker'),
            archived = $(this).closest('tr').find('.sorting_1').attr('activity'),
            newArrObj = {
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
// function addWorker(that) {
//     $('.email_error').text("");
//     $('.FIOError').text("");
//     let user_id = $('#personal_worker').attr('data-user-id'),
//         user_name = $(that).closest('.form_company_many').find('#FIOWorker').val(),
//         email = $(that).closest('.form_company_many').find('#EmailWorker').val(),
//         phone = $(that).closest('.form_company_many').find('#PhoneWorker').val(),
//         arData,
//         type;
//
//     if (/^\w+@[a-zA-Z_]+?\.[a-zA-Z]{2,3}$/.test(email) === false) {
//         $('.email_error').text("Введите корректный email");
//         return;
//     }
//
//     if (user_name.length === 0) {
//         $('.FIOError').text("Введите имя пользователя");
//         return;
//     }
//
//     type = "EMAIL";
//
//     arData = {
//         'LOGIN': user_name,
//         'VALUE': email,
//         'TYPE': type,
//         'PHONE': phone,
//         'user_id': user_id
//     }
//     $.ajax({
//         type: 'POST',
//         url: BX.message('SITE_DIR') +
//             'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
//         data: 'createWorker=' + JSON.stringify(arData),
//         success: function (result) {
//             if (isNaN(parseInt(result))) {
//                 $('.error').html(result);
//                 return;
//             }
//             $('.bx-background-image').prepend('<div class="modal fade" style="display: block; opacity: 1; background: rgba(0,0,0,.5)">\n' +
//                 '  <div class="modal-dialog modal-dialog-centered" role="document">\n' +
//                 '    <div class="modal-content">\n' +
//                 '      <div class="modal-header">\n' +
//                 '        <h5 class="modal-title" id="exampleModalLabel">Успешное выполнение</h5>\n' +
//                 '        <button type="button" class="close btn-close-modal" data-dismiss="modal" aria-label="Close">\n' +
//                 '          <span aria-hidden="true">&times;</span>\n' +
//                 '        </button>\n' +
//                 '      </div>\n' +
//                 '      <div class="modal-body">\n' +
//                 '        Пользователь успешно добавлен\n' +
//                 '      </div>\n' +
//                 '      <div class="modal-footer">\n' +
//                 '        <button type="button" class="btn btn-secondary btn-close-modal" data-dismiss="modal">Закрыть</button>\n' +
//                 '      </div>\n' +
//                 '    </div>\n' +
//                 '  </div>\n' +
//                 '</div>').css('overflow-y', 'hidden')
//
//             $('#boxWithContrPeople').hide(200).empty();
//             $('table#TableWorkers').find('tbody').append(`<tr data-user-id-worker="${result}">
//                                 <td class="d-flex flex-row align-items-center">
//                                     <span class="avatar mr-3" style="background-color:#a321c3">
//                                           <span class="name">${arData['LOGIN'][0]}</span>
//                                     </span>
//                                     <div>
//                                         <a href="/personal/workers/user/${result}"
//                                            class="d-flex flex-column">
//                                         <span class="name_user">${arData['LOGIN']}</span>
//                                         <span class="email_user">${arData['VALUE']}</span>
//                                         </a>
//                                     </div>
//                                 </td>
//                                 <td class="phone_user">${arData['PHONE']}</td>
//                                 <td></td>
//                                 <td></td>
//                                 <td><span class="icon_edit_lk">
//                                     <div class="box_edit" style="display: none;">
//                                         <span class="EDIT_INFO_USER">Редактировать</span>
//                                         <span class="ARCHIVE_USER">Архивировать</span>
//                                     </div>
//                                 </span></td>
//                             </tr>`);
//
//         }
//     });
// }
//
// // WORKERS
// $('#CreateWorker').on('click', function () {
//     addWorker(this)
// });
//
// $('.check_input').on('click', function () {
//     let checked = $(this).attr('checked');
//     if (checked === 'checked') {
//         $(this).removeAttr('checked');
//     } else {
//         $(this).attr('checked', 'checked');
//     }
//
// })
// $('#SaveParamsWorker').on('click',
//     function () {
//
//         let worker_container = $('div.name_worker'),
//             box_with_company = $('.workers_company'),
//             worker_id = $(worker_container).attr('data-worker-id'),
//             worker_message = $(worker_container).find('span.message'),
//             box_with_contragents = $('.workers_contragents');
//
//         $(worker_message).text('').removeClass('color_green').removeClass('red_text');
//         let arDataWorker, id_contragents_true, id_contragents_all, id_company_all, id_company_true;
//
//         function eachElemAll(that) {
//             let id, ids = [];
//
//             $(that).find('.line_info').each(function () {
//                 let method = $(this).find('input').attr('data-method-for-workers');
//                 id = $(this).find('.name_box').attr('data-' + method + '-id');
//                 ids.push(id);
//             });
//             return ids;
//         }
//
//         function eachElemTrue(that) {
//             let id, ids_true = [];
//             $(that).find('.line_info').each(function () {
//                 let checked = $(this).find('input').prop('checked'),
//                     method = $(this).find('input').attr('data-method-for-workers');
//                 if (checked === true) {
//                     id = $(this).find('.name_box').attr('data-' + method + '-id');
//                     ids_true.push(id);
//                 }
//             });
//             return ids_true;
//         }
//
//         id_contragents_true = eachElemTrue(box_with_contragents);
//         id_contragents_all = eachElemAll(box_with_contragents);
//         id_company_true = eachElemTrue(box_with_company);
//         id_company_all = eachElemAll(box_with_company);
//
//         arDataWorker = {
//             'CONTR_AGENT_ID': JSON.stringify(id_contragents_all),
//             'COMPANY_ID': JSON.stringify(id_company_all),
//             'CONTR_AGENT_TRUE': JSON.stringify(id_contragents_true),
//             'COMPANY_ID_TRUE': JSON.stringify(id_company_true),
//             'USER_ID': worker_id
//         }
//
//         $.ajax({
//             type: 'POST',
//             url: BX.message('SITE_DIR') +
//                 'local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
//             data: 'editWorkerControls=' + JSON.stringify(arDataWorker),
//             success: function (result) {
//                 $(worker_message).text('');
//                 if (result === 'success') {
//                     $(worker_message).text('Изменения сохранены').addClass('color_green').show(200);
//                 } else {
//                     $(worker_message).text('Ошибка присваивания прав доступа сотруднику!').addClass('red_text').show(200);
//                 }
//             }
//         });
//     });
//
// function stringToColor(str) {
//     let hash = 0,
//         color = '#',
//         i, value, strLength;
//
//     if (!str) {
//         return color + '333333';
//     }
//
//     strLength = str.length;
//
//     for (i = 0; i < strLength; i++) {
//         hash = str.charCodeAt(i) + ((hash << 5) - hash);
//     }
//
//     for (i = 0; i < 3; i++) {
//         value = (hash >> (i * 8)) & 0xFF;
//         color += ('00' + value.toString(16)).substr(-2);
//     }
//
//     return color;
// }
//
// function eachUsersColor(that) {
//     let name, letter, backgroundColor;
//     name = $(that).find('span.name').text();
//     letter = name.substr(0, 1);
//     backgroundColor = stringToColor(name);
//     $(that).find('span.avatar').attr('style', 'background-color:' + backgroundColor);
//     $(that).find('span.name').text(letter);
//     let button_edit = '<span class="EDIT_INFO" >Редактировать</span>';
//     if (worker_pages || worker_pages_home) {
//         $(that).find('.icon_edit_lk').append('<div class="box_edit" style="display: none;">'
//             + button_edit + '<span class="ARCHIVE">Архивировать</span></div>');
//     }
// }
//
// if (workers) {
//     let table = $('#TableWorkers');
//     $(table).DataTable({
//         "paging": false,
//         "language": {
//             "search": " ",
//             "searching": "Результатов не найдено"
//         },
//         "bInfo": false,
//         select: true
//     });
//     $('#TableWorkers_filter label').addClass('order-3').find('input').attr('placeholder',
//         'Имя, фамилия или почта').addClass('form-control');
//
//     let company_json = $('#companyArrayForSelected').val(),
//         contrAgent_json = $('#contrAgentArrayForSelected').val();
//
//     $('#TableWorkers_filter').attr('class', 'd-flex row-section justify-content-between align-items-center')
//         .append('<select class="order-1" id="CompanySelected"><option>Компании</option></select>' +
//             '<select class="order-2" id="ContrAgent"><option>Контрагенты</option></select>' +
//             '<a class="btn_company btn order-4" href="javascript:void(0)" id="AddWorker">Добавить сотрудника</a>');
//     $('#ContrAgent').select2();
//     $('#CompanySelected').select2();
//     if (company_json !== '' && company_json !== null || contrAgent_json !== '' && contrAgent_json !== null) {
//         let company = JSON.parse(company_json);
//
//         let contrAgent = JSON.parse(contrAgent_json);
//         $(company).each(function (i, valueComp) {
//             $('#CompanySelected').append('<option value="' + valueComp.COMPANY_ID + '">' + valueComp.NAME_COMP + '</option>')
//         });
//         $(contrAgent).each(function (i, valueCont) {
//             $('#ContrAgent').append('<option value="' + valueCont.CONTR_AGENT_ID + '">' + valueCont.NAME_CONT + '</option>')
//         });
//     }
//
//     $(table).find('tbody tr').each(function () {
//             eachUsersColor(this);
//         }
//     );
//
//
//     $('#AddWorker').on('click', function () {
//         let box = $('#boxWithContrPeople');
//         $(box).empty();
//         $(box).append('<form class="form_company_many filters mb-5">' +
//             '<div class="position-absolute close_modalWindow"></div>' +
//             '<div class="form-group mb-2">' +
//             '<label class="label_company">Добавить сотрудника</label></div>' +
//             '<div class="form-group mb-3 col-12"><span style="color: red" class="FIOError"></span>' +
//             '<input required type="text" class="form-control input_lk" id="FIOWorker" autocomplete="off" placeholder="ФИО">' +
//             '</div><div class="form-group mb-4 col-12"><span style="color: red" class="email_error"></span>' +
//             '<input type="text" class="form-control input_lk" id="EmailWorker" autocomplete="off" ' +
//             'placeholder="Адрес электронной почты"></div>' +
//             '<div class="form-group mb-4 col-12">' +
//             '<input type="text" class="form-control input_lk" id="PhoneWorker" autocomplete="off" ' +
//             'placeholder="Телефон"><span style="color: red" class="error"></span></div>' +
//             '<div class="form-group">' +
//             '<div class="col-4"><a href="javascript:void(0)" class="btn btn_popup text_font_13 btn_red"' +
//             ' id="CreateWorker" data-toggle="modal" data-target="#exampleModal">Сохранить</a></div></div></form>').show(200);
//
//         $("#PhoneWorker").mask("+7-(999) 999-9999");
//
//         $('#CreateWorker').on('click', function () {
//             addWorker(this);
//         });
//     });
// }
//
// if (worker_pages) {
//     eachUsersColor(this);
// }
//
// if (worker_pages_home) {
//     $('#worker_pages_lk_home').find('.line_worker').each(function () {
//         eachUsersColor(this);
//     });
// }
//
// //