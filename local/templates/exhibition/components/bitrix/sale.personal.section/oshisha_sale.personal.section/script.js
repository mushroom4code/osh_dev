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


});
