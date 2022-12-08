$(document).ready(function () {
	$('.article__btn').on('click', function(){
			var maxpage = $(this).data('maxpage');
			var thisPage = $(this).data('page');
			var numPage = thisPage + 1;
			if( maxpage == numPage )
			{
				$('.article__btn').hide();
			}
			else
			{
				$(this).attr( 'data-page', numPage );
			}
			var paramsstr = $(this).data('paramsstr');
			
			var url = '/local/components/bbrain/page/templates/.default/ajax.php?numPage='+numPage+'&AJAX=Y&paramsstr='+paramsstr;
			$.ajax({
				type:"POST",
				url: url,
				success: function(msg){
					//console.log(msg);
					$('.article').append(msg);
				
				}
				});			
		
	});
	
});