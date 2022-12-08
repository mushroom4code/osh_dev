/**
 *	Simple jQuery-plugin for hot keys!
 *	Copyright 2020-07-22, www.Webdebug.ru, written by Denis Son
 *	-----------------------------------------------------------
 *	Register hotkey:
 *	$.wduHotkey('Ctrl+Shift+Win+C', function(e){
 *		console.log('Pressed hotkey «Ctrl+Shift+Win+C»');
 *	});
 *	$.wduHotkey('Ctrl+Shift+Cmd+C', function(e){
 *		console.log('Pressed hotkey «Ctrl+Shift+Cmd+C»');
 *	});
 *	$.wduHotkey('Ctrl+A', function(e){
 *		console.log('Pressed hotkey «Ctrl+A»');
 *	});
 *	$.wduHotkey('G', function(e){
 *		console.log('Pressed key G');
 *	});
 *	-----------------------------------------------------------
 *	Release hotkey:
 *	$.wduHotkey('Ctrl+Shift+Win+C', false);
 *	$.wduHotkey('Ctrl+Shift+Cmd+C', false);
 *	$.wduHotkey('Ctrl+A', false);
 *	$.wduHotkey('G', false);
 *	-----------------------------------------------------------
 */
$.wduHotkey = function(hotkey, callback) {
	
	// Format hotkey, just for beauty
	function ucFirst(text){
		return text[0].toUpperCase() + text.slice(1).toLowerCase();
	}
	
	// Parse hotkey: 'Ctrl+Alt+A' => {altKey:true, ctrlKey:false, shiftKey:false, metaKey:false, key:A, ...}
	function parseKey(hotkey){
		let
			mod = hotkey.replace(/\s/g, '').split('+'),
			btn = mod.pop(),
			result = {
				key: btn.toUpperCase(),
				keyCode: btn.charCodeAt(0),
				altKey: false,
				ctrlKey: false,
				shiftKey: false,
				metaKey: false,
				hotkey: hotkey
			};
		for(let i=0; i<mod.length; i++){
			switch(ucFirst(mod[i])){
				case 'Alt': result.altKey = true; break;
				case 'Ctrl': result.ctrlKey = true; break;
				case 'Shift': result.shiftKey = true; break;
				case 'Win': result.metaKey = true; break;
				case 'Cmd': result.metaKey = true; break;
			}
		}
		return result;
	}
	
	// Search hotkey and return it index
	function searchHotkey(hotkey){
		let result = -1;
		for(let index in $.wduHotkey.list){
			if(hotkey.keyCode == $.wduHotkey.list[index].key.charCodeAt(0)) {
				if(hotkey.altKey == $.wduHotkey.list[index].altKey){
					if(hotkey.ctrlKey == $.wduHotkey.list[index].ctrlKey){
						if(hotkey.shiftKey == $.wduHotkey.list[index].shiftKey){
							if(hotkey.metaKey == $.wduHotkey.list[index].metaKey){
								result = index;
							}
						}
					}
				}
			}
		}
		return result;
	}
	
	// Register new hotkey
	function registerHotkey(hotkey, callback){
		hotkey.callback = callback;
		$.wduHotkey.list.push(hotkey);
	}
	
	// Release unnecessary hotkey
	function releaseHotkey(hotkey){
		let index = searchHotkey(hotkey);
		if(index > -1){
			delete $.wduHotkey.list[index];
		}
	}
	
	// Initialize
	if(!$.wduHotkey.initialized){
		$.wduHotkey.list = [];
	}
	
	// Create hotkey list
	if(!$.wduHotkey.list){
		$.wduHotkey.list = [];
	}
	
	// Process hotkey
	hotkey = parseKey(hotkey);
	if(typeof callback == 'function'){
		registerHotkey(hotkey, callback);
	}
	else{
		releaseHotkey(hotkey);
	}
	
	// Initialize handler
	if(!$.wduHotkey.initialized){
		$.wduHotkey.initialized = true;
		$(document).keydown(function(e) {
			let index = searchHotkey(e);
			if(index > -1){
				return $.wduHotkey.list[index].callback(e);
			}
		});
	}
	
};
$.wduHotkey('Ctrl+Win+A', function(e){
	console.log('Success A!');
});
$.wduHotkey('Ctrl+Shift+Win+C', function(e){
	console.log('Success C!');
});