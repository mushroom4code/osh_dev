$(document).ready(function () {
    $('.article__btn').on('click', function () {
        var maxpage = $(this).data('maxpage');
        var thisPage = $(this).data('page');
        var numPage = thisPage + 1;
        if (maxpage == numPage) {
            $('.article__btn').hide();
        } else {
            $(this).attr('data-page', numPage);
        }
        var paramsstr = $(this).data('paramsstr');

        var url = '/local/components/bbrain/page/templates/.default/ajax.php?numPage=' + numPage + '&AJAX=Y&paramsstr=' + paramsstr;
        $.ajax({
            type: "POST",
            url: url,
            success: function (msg) {
                //console.log(msg);
                $('.article').append(msg);

            }
        });

    });
    //BRANDS

    $('#box_boxes').find('.box_with_brands_parents').each(
        function () {
            let heightBlock = $(this).css('height');
            if (heightBlock < '435px') {
                $(this).closest('div#box_brands').find('a.link_menu_catalog ').attr('style', 'display:none');
            }
        }
    );

    function showHideBlockForButton(box,href) {
        let height = $(box).css('height'),
            id = $(box).attr('id'),
            idLink = $(href).attr('data-id');
        if (id === idLink) {
            if (height === '435px') {
                $(href).text('Скрыть');
                $(box).attr('style', 'max-height:2000px;transition: 0.7s;');
            } else {
                $(href).text('Показать все');
                $(box).attr('style', 'max-height:435px;height:435px;transition: 0.7s;');
            }
        }
    }

    $('a.link_brand').on('click', function () {
        let box = $(this).closest('div#box_brands').find('.box_with_brands_parents');
        showHideBlockForButton(box,$(this));
    });

});