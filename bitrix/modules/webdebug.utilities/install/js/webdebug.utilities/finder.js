function wduFinderDoSearch(start){
	let post = {};
	if(start){
		if(!$('input[data-role="wdu_search_text"]').val().length){
			alert($('input[data-role="wdu_search_text"]').attr('data-empty'));
			return;
		}
		post.start = true;
		post.search_text = $('input[data-role="wdu_search_text"]').val();
		post.search_filter = $('input[data-role="wdu_search_filter"]').val();
		post.search_folder = $('input[data-role="wdu_search_folder"]').val();
		post.search_folder_exclude = $('input[data-role="wdu_search_folder_exclude"]').val();
		post.search_reqexp = $('input[data-role="wdu_search_regexp"]').prop('checked') ? 'Y' : 'N';
		post.search_case = $('input[data-role="wdu_search_case"]').prop('checked') ? 'Y' : 'N';
		post.search_encoding = $('input[data-role="wdu_search_encoding"]').prop('checked') ? 'Y' : 'N';
		$('div[data-role="wdu_search_results"] ul').remove();
		$('span[data-role="wdu_search_results_count"]').text('(0/0)').show();
	}
	wduFinderEnableControls(false);
	window.wduFinderAjaxRequest = wduFinderAjax('ajax_search', null, post, function(arJsonResult){
		if(arJsonResult != null && typeof arJsonResult == 'object'){
			if(arJsonResult.ErrorMessage != undefined){
				alert(arJsonResult.ErrorMessage);
				wduFinderEnableControls(true);
			}
			else if(arJsonResult.Continue){
				wduFinderDoSearch();
			}
			else{
				wduFinderEnableControls(true);
			}
			if(arJsonResult['NextPath'] != undefined){
				$('div[data-role="wdu_search_status_next_file"]').text(arJsonResult['NextPath']);
			}
			if(arJsonResult.Results != undefined){
				wduFinderDisplayResults(arJsonResult.Results, arJsonResult.Site);
			}
			if(arJsonResult.ResultsCount != undefined){
				$('span[data-role="wdu_search_results_count"]')
					.text('('+arJsonResult.Results.length+'/'+arJsonResult.ResultsCount+')');
			}
		}
		else{
			wduFinderEnableControls(true);
		}
	}, function(error){
		wduFinderEnableControls(true);
		wduPopupError.Open(error.type, error.data);
	});
}

function wduFinderDoStop(){
	if(window.wduFinderAjaxRequest){
		window.wduFinderAjaxRequest.abort();
	}
	wduFinderEnableControls(true);
}

function wduFinderEnableControls(enabled){
	let
		btnReset = $('input[data-role="wdu_search_reset"]'),
		btnStart = $('input[data-role="wdu_search_start"]'),
		btnStop = $('input[data-role="wdu_search_stop"]'),
		controls = $('input[data-role="wdu_search_text"]').add('input[data-role="wdu_search_filter"]')
			.add('input[data-role="wdu_search_folder"]').add('input[data-role="wdu_search_folder_exclude"]')
			.add('input[data-role="wdu_search_case"]').add('input[data-role="wdu_search_regexp"]')
			.add('input[data-role="wdu_search_encoding"]'),
		divStatus = $('div[data-role="wdu_search_status"]');
	if(enabled){
		btnReset.removeAttr('disabled');
		btnStart.show();
		btnStop.hide();
		controls.removeAttr('disabled');
		divStatus.hide();
	}
	else{
		btnReset.attr('disabled', 'disabled');
		btnStart.hide();
		btnStop.show();
		controls.attr('disabled', 'disabled');
		divStatus.show();
	}
}

function wduFinderDisplayResults(results, site){
	let
		divResults = $('div[data-role="wdu_search_results"]'),
		tpl = '';
	if(!divResults.children('ul').length){
		divResults.prepend('<ul>');
	}
	if(typeof results == 'object'){
		for(let i in results){
			if(!$('li[data-hash="'+results[i]['Hash']+'"]', divResults).length){
				tpl = String.wduFormat('/bitrix/admin/fileman_file_edit.php?path={0}&full_src=Y&site={1}&lang={2}',
					encodeURIComponent(results[i]['File']), '', phpVars.LANGUAGE_ID);
				tpl = String.wduFormat('<li data-hash="{0}"><a href="{1}" target="blank">{2}</a> ({3})</li>',
					results[i]['Hash'], tpl, results[i]['File'], results[i]['Size']);
				divResults.children('ul').append(tpl);
			}
		}
	}
}

$(document).delegate('#wdu_finder_form', 'submit', function(e){
	e.preventDefault();
	wduFinderDoSearch(true);
});

$(document).delegate('#wdu_finder_form :input', 'change', function(e){
	let
		key = $(this).attr('data-key'),
		type = $(this).attr('type'),
		value = (type == 'checkbox') ? ($(this).prop('checked') ? 'Y' : 'N') : $(this).val();
	if(key){
		wduChangeUrl(key, value);
	}
});

$(document).delegate('input[data-role="wdu_search_reset"]', 'click', function(e){
	e.preventDefault();
	if(confirm($(this).attr('data-confirm'))){
		$('#wdu_finder_form :input').each(function(){
			switch($(this).attr('type')){
				case 'checkbox':
					$(this).prop('checked', $(this).attr('data-default') == 'Y').trigger('change');
					break;
				case 'text':
					$(this).val($(this).attr('data-default'));
					break;
			}
		});
		wduSetUrl(location.pathname);
	}
});

$(document).delegate('input[data-role="wdu_search_stop"]', 'click', function(e){
	e.preventDefault();
	wduFinderDoStop();
});

$(document).delegate('input[data-role="wdu_search_regexp"]', 'change', function(e){
	$('input[data-role="wdu_search_case"]').closest('tr').toggle(!$(this).prop('checked'));
	$('input[data-role="wdu_search_text"]').toggleClass('wdu_search_monospace', $(this).prop('checked'));
});

$(document).ready(function(){
	$('input[data-role="wdu_search_regexp"]').trigger('change');
	if($('input[data-role="wdu_search_start_now"]').length){
		$('#wdu_finder_form').trigger('submit');
	}
});
