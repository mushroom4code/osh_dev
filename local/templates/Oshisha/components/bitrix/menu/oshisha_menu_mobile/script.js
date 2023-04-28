$(document).ready(function () {

	$('.openMenuMobile').on('click', function () {
		if( $(this).hasClass('closed') )
		{
			$(this).removeClass('closed').addClass('opened');
			$(this).find('i.fa-angle-right').removeClass('fa-angle-right').addClass('fa-angle-down');
			$(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').show();
		}
		else
		{
			$(this).removeClass('opened').addClass('closed');
			$(this).find('i.fa-angle-down').removeClass('fa-angle-down').addClass('fa-angle-right');
			$(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').hide();	
		}
		
	});
});
