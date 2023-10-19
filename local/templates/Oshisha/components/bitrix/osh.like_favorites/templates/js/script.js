$(document).ready(function () {
// лайки

    $(document).on('click', '.initial_auth_popup', function () {
        event.preventDefault();
        $('.ctweb-smsauth-menu-block').show();
        console.log(document.querySelector('.overlay-box'))
        $(document).find('.overlay-box').toggle('hidden');
    });

    $(document).on('click', 'a.method', function () {
        let that = $(this);
        let newCount = 0;
        let value;
        let box_fav = $(that).closest('.box_with_like').find('a.product-item__favorite-star');
        let fav = $(box_fav).find('svg path');
        let product_id = $(that).closest('.box_with_like').attr('data-product-id');
        let user_id = $(that).closest('.box_with_like').attr('data-user-id');
        let fuser_id = $(that).closest('.box_with_like').attr('data-fuser-id');

        let method = $(that).attr('data-method');
        let color = $(that).find('svg path');
        let like = $(that).closest('.box_with_like').find('#likes');
        let text_like = $(like).text();
        let numberLike;
        let product_array;
        let number = Number(text_like);
        let dataAttrLikeBool = $(that).attr('data-like-controls');
        let dataAttrFav = $(that).attr('data-fav-controls');

        if (method === 'like') {
            if (number > 0 && dataAttrLikeBool !== undefined && dataAttrLikeBool === 'true') {
                value = 0;
            } else {
                value = 1;
            }
        } else if (method === 'favorite') {
            if (dataAttrFav !== undefined && dataAttrFav === 'true') {
                value = 0;
            } else {
                value = 1;
            }
        }
        product_array = {
            'product_id': product_id,
            'method': method,
            'user_id': user_id,
            'fuser_id': fuser_id,
            'value': value,
        }

        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') + 'local/templates/Oshisha/components/bitrix/osh.like_favorites/ajax.php',
            data: 'product_array=' + JSON.stringify(product_array),
            success: function (result) {
                if (result === 'success') {
                    if (method === 'like') {
                        if (product_array.value === 1) {
                            numberLike = (number + 1);
                            newCount = $(like).text(numberLike);
                            window.stop(newCount);
                            $(color).addClass('fill-light-red stroke-light-red dark:fill-white dark:stroke-white').removeClass('stroke-black');
                            $(that).attr('data-like-controls', 'true');
                        } else {
                            if (number > 0) {
                                numberLike = (number - 1);
                                newCount = $(like).text(numberLike);
                                window.stop(newCount);
                            } else {
                                newCount = $(like).text(0);
                                window.stop(newCount);
                            }
                            $(color).addClass('stroke-black').removeClass('fill-light-red stroke-light-red dark:fill-white');
                            $(that).attr('data-like-controls', 'false');
                        }
                    } else if (method === 'favorite') {
                        if (product_array.value === 1) {
                            $(fav).addClass('fill-light-red stroke-light-red dark:fill-white dark:stroke-white').removeClass('stroke-black');
                            $(that).attr('data-fav-controls', 'true');
                        } else {
                            $(fav).addClass('stroke-black').removeClass('fill-light-red stroke-light-red dark:fill-white');
                            $(that).attr('data-fav-controls', 'false');
                        }
                    }
                }
            }
        });
    });
});
