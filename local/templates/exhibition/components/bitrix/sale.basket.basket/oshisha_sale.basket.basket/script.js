$(document).ready(function () {
    // Enterego
    $(document).on('click', '.js--open-price-list', function () {
        let box_with_prices = $(this).closest('.basket-items-list-item-amount').find('.box-with-prices-net');
        if ($(box_with_prices).hasClass('d-none')) {
            $(box_with_prices).removeClass('d-none');
            $(this).attr('style', 'transform: rotate(180deg);');
        } else {
            $(this).removeAttr('style');
            $(box_with_prices).addClass('d-none');
        }
    });
    // Enterego

    $('.BasketClearForm').on('submit', function (e) {

        return confirm("Удалить все товары из корзины?");
    });
});

