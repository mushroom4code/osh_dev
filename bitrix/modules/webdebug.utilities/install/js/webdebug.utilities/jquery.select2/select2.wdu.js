/**
 *	jQuery select2
 */
function wduSelect2(select, config){
	var select2 = $(select);
	if(!select2.hasClass('select2-hidden-accessible')){
		// Prepare
		select2.wrap('<div>').parent().addClass('wdu-select2-wrapper').css('position', 'relative');
		// Get width
		var div = $('<div/>').css({height: '0', overflow: 'hidden', width: screen.width}).appendTo($('body')),
			selectTmp = select2.clone().removeAttr('id').appendTo(div),
			width = selectTmp.width();
		// Config
		config = $.extend({}, {
			dropdownAutoWidth: true,
			dropdownParent: select2.parent(),
			language: phpVars && phpVars.LANGUAGE_ID? phpVars.LANGUAGE_ID : 'ru',
			matcher: function(params, item){
				function optionIsMatch(option, search){
					return [option.text, option.value, option.title].join(' ').toLowerCase().indexOf(searchText) != -1;
				}
				if(params.term == undefined || !params.term.length){
					return item;
				}
				if(item.id == ''){
					return null;
				}
				var searchText = params.term.toLowerCase();
				if(item.element.tagName.toLowerCase() == 'option'){
					if(optionIsMatch(item.element, searchText)) {
						return item;
					}
				}
				else if(item.element.tagName.toLowerCase() == 'optgroup' && Array.isArray(item.children)){
					var filteredOptions = [];
					$.each(item.children, function (index, option) {
						if(optionIsMatch(option, searchText)) {
							filteredOptions.push(option);
						}
					});
					if(filteredOptions.length) {
						var modifiedItem = $.extend({}, item, true);
						modifiedItem.children = filteredOptions;
						return modifiedItem;
					}
				}
				return null;
			}
		}, config);
		if(config.withIcons){
			config.templateResult = function(item){
				if(item.loading || $(item.element).is('optgroup') || !item.id.length) {
					return item.text;
				}
				var option = $(item.element);
				return $(
					'<div class="wdu-select2-item" title="' + option.data('hint') + '">' +
						'<div class=\"wdu-select2-item-icon">' +
							'<img src="' + option.data('icon') + '" alt="" />' +
						'</div>' +
						'<div class="wdu-select2-item-text">' +
							item.text +
						'</div>' +
					'</div>'
				);
			}
			config.templateSelection = function(item){
				if(!item.id) {
					return item.text;
				}
				var option = $(item.element);
				return $(
					'<div class="wdu-select2-item" title="' + option.data('hint') + '">' +
						'<div class=\"wdu-select2-item-icon">' +
							'<img src="' + option.data('icon') + '" alt="" />' +
						'</div>' +
						'<div class="wdu-select2-item-text">' +
							item.text +
						'</div>' +
					'</div>'
				);
			}
		}
		if(config.extConfig){
			config = $.extend(config, config.extConfig);
		}
		select2.select2(config);
		select2.next('.select2').css({'min-width': width + 30, 'max-width': '600px'});
	}
	return select2;
}