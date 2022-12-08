let WduPopup = BX.CDialog;

/**
 *	Build URL for AJAX
 */
WduPopup.prototype.WdHttpBuildQuery = function(url, params) {
	var query = Object.keys(params)
	 .map(function(k) {return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]);})
		.join('&');
	return url + (query.length ? (url.indexOf('?') == -1 ? '?' : '&') + query : '');
}

/**
 *	Load content via AJAX
 */
WduPopup.prototype.WdLoadContentAjax = function(option, get, post, callbackSuccess, callbackError, hideLoader) {
	let popup = this;
	get = typeof get == 'object' ? get : {};
	post = typeof get == 'object' ? post : {};
	get.wdu_ajax_option = option;
	return BX.ajax({
		url: popup.WdHttpBuildQuery(location.href, get),
		method: 'POST',
		data: post,
		dataType: 'json',
		timeout: 30,
		async: true,
		processData: true,
		scriptsRunFirst: false,
		emulateOnload: false,
		start: true,
		cache: false,
		onsuccess: function(arJsonResult){
			if(arJsonResult.Title != undefined){
				popup.WdSetTitle(arJsonResult.Title);
			}
			if(typeof callbackSuccess == 'function') {
				callbackSuccess(arJsonResult);
			}
			else if(arJsonResult.Content != undefined){
				popup.WdSetContent(arJsonResult.Content);
			}
			if(hideLoader!==true) {
				BX.closeWait();
			}
		},
		onfailure: function(status, error){
			console.error(error.data);
			popup.WdSetTitle('Error');
			popup.WdSetContent('<div class="wdu_bx_dialog_content_preformat" style="font-family:monospace;">'+error.data+'</div>');
			if(typeof callbackError == 'function') {
				callbackError(error);
			}
			if(hideLoader!==true) {
				BX.closeWait();
			}
		}
	});
}

/**
 *	Set title (considering HTML)
 */
WduPopup.prototype.WdSetTitle = function(title) {
	let nodes = this.PARTS.TITLEBAR.querySelectorAll('.bx-core-adm-dialog-head-inner');
	for(let i=0; i<nodes.length; i++) {
		nodes[i].innerHTML = title;
	}
}

/**
 *	Set content (and set height 100%)
 */
WduPopup.prototype.WdSetContent = function(html) {
	let nodes = this.PARTS.CONTENT_DATA.querySelectorAll('.bx-core-adm-dialog-content-wrap-inner');
	for(let i=0; i<nodes.length; i++) {
		nodes[i].innerHTML = '<div class="wdu_bx_dialog_content">' + html + '</div>';
		nodes[i].style.boxSizing = 'border-box';
		nodes[i].style.height = '100%';
		for(let j=0; j<nodes[i].childNodes.length; j++) {
			if(nodes[i].childNodes[j].nodeType == 1){
				nodes[i].childNodes[j].style.height = '100%';
			}
		}
		let scripts = nodes[i].querySelectorAll('script');
		if(scripts.length){
			for(let j=0; j<scripts.length; j++) {
				let script = document.createElement('script');
				script.text = '(function(){' + scripts[j].text + '})();';
				scripts[j].replaceWith(script);
			}
		}
		let checkboxes = this.PARTS.CONTENT_DATA.querySelectorAll('input[type="checkbox"]');
		for(let j=0; j<checkboxes.length; j++) {
			BX.adminFormTools.modifyCheckbox(checkboxes[j]);
		}
	}
	let inputs = this.PARTS.CONTENT_DATA.querySelectorAll('input[type=text],textarea');
	setTimeout(function(){
		for(let i=0; i<inputs.length; i++) {
			inputs[i].focus();
			inputs[i].setSelectionRange(inputs[i].value.length, inputs[i].value.length);
			break;
		}
	}, 1);
}

/**
 *	Set nav buttons
 */
WduPopup.prototype.WdSetNavButtons = function(buttons) {
	let
		empty = buttons == undefined || typeof(buttons) != 'object' || !buttons.length,
		container = this.PARTS.BUTTONS_CONTAINER;
	container.innerHTML = '';
	if(empty) {
		container.insertAdjacentHTML('beforeEnd', '<input type="button" value="0" style="visibility:hidden;" />');
	}
	else if(typeof(buttons) == 'object' || buttons.length){
		this.SetButtons(buttons);
		container.insertAdjacentHTML('beforeEnd', '<div style="clear:both"/>');
	}
}
