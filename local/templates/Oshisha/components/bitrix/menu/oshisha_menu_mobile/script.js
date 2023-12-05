$(document).ready(function () {

    $('.openMenuMobile').on('click', function () {
        if ($(this).hasClass('closed')) {
            $(this).removeClass('closed').addClass('opened');
            $(this).find('span').removeClass('text-dark dark:font-light').addClass('text-light-red font-normal')
            $(this).find('svg').attr('style', 'transform:rotate(180deg);')
            $(this).find('.fa-angle-right').removeClass('fa-angle-right').addClass('fa-angle-down');
            $(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').show();
        } else {
            $(this).removeClass('opened').addClass('closed');
            $(this).find('span').removeClass('text-light-red font-normal').addClass('text-dark dark:font-light')
            $(this).find('svg').removeAttr('style')
            $(this).find('.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-right');
            $(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').hide();
        }

    });

    $('a.MenuHeader').on('click', function () {
        const boxMenu = $('.header_top#MenuHeader');
        boxMenu.toggleClass('hidden')
        if (boxMenu.hasClass('hidden')) {
            $(this).find('#icon').show()
            $(this).find('svg.close-svg').remove();
        } else {
            $(this).find('#icon').hide()
            $(this).append('<svg width="23" height="23" viewBox="0 0 10 10" fill="none" class="close-svg" ' +
                ' xmlns="http://www.w3.org/2000/svg">' +
                '<path d="M0.833374 9.16669L9.08296 0.917114" stroke="white" stroke-width="1" ' +
                'stroke-linecap="round" stroke-linejoin="round"/>' +
                '<path d="M0.833374 0.833374L9.08296 9.08295" stroke="white" stroke-width="1" ' +
                'stroke-linecap="round" stroke-linejoin="round"/>' +
                '</svg>')
        }
    });

});
