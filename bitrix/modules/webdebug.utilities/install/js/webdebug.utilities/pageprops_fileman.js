let wduPopupPageProps;
wduPopupPageProps = new WduPopup({
	height: 360,
	width: 800
});
wduPopupPageProps.Open = function(prop, site){
	let popup = this;
	this.prop = prop;
	this.site = site;
	popup.WdSetContent('Loading..');
	popup.WdLoadContentAjax('ajax_load_prop', false, {prop:prop, site:site});
	popup.WdSetNavButtons([{
		'name': BX.message('JS_CORE_WINDOW_SAVE'),
		'id': 'wdu_save',
		'className': 'adm-btn-green',
		'action': function(){
			let post = $('form', popup.DIV).serialize();
			popup.WdLoadContentAjax('ajax_load_prop_save', false, post, function(arJsonSuccess){
				$('div[data-role="wdu_pageprop_prop_type_settings"]').html(arJsonSuccess.Content);
				if(arJsonSuccess.Prop){
					let
						prop = arJsonSuccess.Prop,
						row = $('tr[data-role="wdu_pageprop_prop"][data-prop="'+prop.Code+'"][data-site="'+prop.Site+'"]'),
						span = $('span[data-role="wdu_pageprop_prop_icon"]', row);
					span.css('background-image', prop.Icon ? 'url('+prop.Icon+')' : '');
				}
			});
			popup.Close();
		}
	}]);
	popup.Show();
}

$(document).delegate('select[data-role="wdu_pageprop_site_id"]', 'change', function(e){
	let
		siteId = $(this).val(),
		tables = $('table[data-role="wdu_pageprop_table"]'),
		table = tables.hide().filter('[data-site='+siteId+']').show();
});

$(document).delegate('tr[data-prop] input[type="button"]', 'click', function(e){
	let
		row = $(this).closest('tr'),
		prop = row.attr('data-prop'),
		site = row.attr('data-site');
	wduPopupPageProps.Open(prop, site);
});

$(document).delegate('select[data-role="wdu_pageprop_type"]', 'change', function(e){
	let
		popup = wduPopupPageProps,
		data = {
			prop: popup.prop,
			site: popup.site,
			type: $(this).val()
		}
	wduPopupPageProps.WdLoadContentAjax('ajax_load_prop_type', false, data, function(arJsonSuccess){
		let
			div = $('div[data-role="wdu_pageprop_prop_type_settings"]').html(arJsonSuccess.Content),
			checkboxes = $('input[type="checkbox"]', div);
		checkboxes.each(function(){
			BX.adminFormTools.modifyCheckbox(this);
		});
	});
});
