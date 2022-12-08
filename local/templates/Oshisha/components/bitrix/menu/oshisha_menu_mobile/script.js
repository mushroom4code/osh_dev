$(document).ready(function () {

	$('.fa_icon').on('click', function () {
		if( $(this).hasClass('fa-angle-right') )
		{
			$(this).removeClass('fa-angle-right');
			$(this).addClass('fa-angle-down');
			$(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').show();
		}
		else
		{
			$(this).removeClass('fa-angle-down');
			$(this).addClass('fa-angle-right');
			$(this).closest('.li_menu_top').find('.bx-nav-list-2-lvl').hide();	
		}
		
	});
});
