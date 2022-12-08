$(document).ready(function () {

    // function getCookie(name) {
    //     let matches = document.cookie.match(new RegExp(
    //         "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    //     ));
    //     return matches ? decodeURIComponent(matches[1]) : false;
    // }
    //
    // function setCookie(name, value) {
    //     if (value === 'true') {
    //         return document.cookie = name + '=false';
    //     } else {
    //         return document.cookie = name + '=true';
    //     }
    // }

// лайки
    $(document).on('click','a.method',function () {
        let that = $(this);
        let newCount = 0;
        let value;
        let box_fav = $(that).closest('.box_with_like').find('a.product-item__favorite-star');
        let fav = $(box_fav).find('i');
        let product_id = $(that).closest('.box_with_like').attr('data-product-id');
        let user_id = $(that).closest('.box_with_like').attr('data-user-id');
        let method = $(that).attr('data-method');
        let color = $(that).find('i');
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
            'value': value,
        }

        $.ajax({
            type: 'POST',
            url: BX.message('SITE_DIR') + 'bitrix/modules/osh.like_favorites/install/components/osh.like_favorites/ajax.php',
            data: 'product_array=' + JSON.stringify(product_array),
            success: function (result) {
                if (result === 'success') {
                    if (method === 'like') {
                        if (product_array.value === 1) {
                            numberLike = (number + 1);
                            newCount = $(like).text(numberLike);
                            window.stop(newCount);
                            $(color).attr('style', 'color:red');
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
                            $(color).attr('style', 'color:black');
                            $(that).attr('data-like-controls', 'false');
                        }
                    } else if (method === 'favorite') {
                        if (product_array.value === 1) {
                            $(fav).attr('style', 'color:red');
                            $(that).attr('data-fav-controls', 'true');
                        } else {
                            $(fav).attr('style', 'color:black');
                            $(that).attr('data-fav-controls', 'false');
                        }
                    }
                }
            }
        });
    });
});
