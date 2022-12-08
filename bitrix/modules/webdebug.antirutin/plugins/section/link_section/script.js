/* See additional vars in sources: \WD\Antirutin\Plugin::printJs() */

$(document).delegate(hid+' input[data-role="filter"]', 'input', function(e){
	var text = $.trim($(this).val().toLowerCase()),
		select = $(this).closest('[data-role="select_section"]').find('select'),
		options = select.find('option').removeAttr('selected');
	if(text.length){
		options.hide().each(function(){
			if(($(this).val() + $(this).text()).toLowerCase().indexOf(text) != -1){
				$(this).show();
			}
		});
	}
	else{
		options.show();
	}
});

// Plugin load handler
$(document).delegate(hid, 'pluginload', function(e){
	$('select[data-role="action"]', div).trigger('change');
	wdaSelect2($('select[data-role="action"]', div));
});

// Check fields on start
wdaOnStartHandler(id, function(id, div, title){
	if(!$('select[data-role="action"]', div).val()){
		return $('input[data-role="error_no_action"]', div).val();
	}
	if(!$('select[data-role="section"]', div).val() && $('select[data-role="action"]', div).val() != 'set'){
		return $('input[data-role="error_no_section"]', div).val();
	}
	return true;
});
