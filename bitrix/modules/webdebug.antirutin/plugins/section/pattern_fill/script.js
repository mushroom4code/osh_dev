/* See additional vars in sources: \WD\Antirutin\Plugin::printJs() */

$(document).delegate(hid+' select[data-role="pattern"]', 'change', function(e, data){
	var
		patternJs = $(this).val(),
		refresh = data && data.customTrigger == true;
	//$('div[data-role="mode_settings"]', div).hide().filter('[data-code="'+mode+'"]').show();
	if(!refresh){
		eval(patternJs);
		$(this).val('').trigger('change', {customTrigger: true});
	}
});

// Plugin load handler
$(document).delegate(hid, 'pluginload', function(e){
	window.wdaInheritedPropertiesTemplates = new JCInheritedPropertiesTemplates(
		'wda_pattern_fill',
		'/bitrix/admin/iblock_templates.ajax.php?ENTITY_TYPE=E&IBLOCK_ID=0&ENTITY_ID=0&bxpublic=y'
	);
});

// Check fields on start
wdaOnStartHandler(id, function(id, div, title){
	if(!$('select[data-role="field"]', div).val()){
		return $('input[data-role="error_no_field"]', div).val();
	}
	else{
		var mode = $('select[data-role="mode"]', div).val();
		if(mode == 'simple' && !$('input[data-role="simple_search"]', div).val().length){
			return $('input[data-role="error_no_simple_search"]', div).val();
		}
		else if(mode == 'reg_exp' && !$('input[data-role="reg_exp_search"]', div).val().length){
			return $('input[data-role="error_no_reg_exp_search"]', div).val();
		}
		else if(mode == 'append' && !$('textarea[data-role="append_text"]', div).val().length){
			return $('input[data-role="error_no_append_text"]', div).val();
		}
		else if(mode == 'prepend' && !$('textarea[data-role="prepend_text"]', div).val().length){
			return $('input[data-role="error_no_prepend_text"]', div).val();
		}
	}
	return true;
});
