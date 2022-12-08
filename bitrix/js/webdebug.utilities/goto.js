if(BX && BX.CDialog) {
	var wduPopupGoTo = new BX.CDialog({
		title: BX.message('WDU_POPUP_GOTO_TITLE'),
		content: '',
		icon: 'head-block',
		resizable: false,
		draggable: true,
		height: '250',
		width: '680'
	});
	wduPopupGoTo.SetButtons(
		[{
			'title': BX.message('WDU_POPUP_GOTO_BUTTON_OK'),
			'id': 'wdu_goto_ok',
			'className': 'adm-btn-green',
			'name': '',
			'action': function(){
				BX.submit(BX('wdu-goto-form'));
				wduPopupGoTo.Close();
			}
		}, {
			'title': BX.message('WDU_POPUP_GOTO_BUTTON_CANCEL'),
			'id': 'wdu_goto_cancel',
			'name': '',
			'action': function(){
				wduPopupGoTo.Close();
			}
		}]
	);
	function wduPopupGotoCallback(result) {
		wduPopupGoTo.SetContent(result);
		BX.closeWait();
	}
	function wduPopupGotoOpen() {
		BX.showWait();
		wduPopupGoTo.SetContent(BX.message('WDU_POPUP_GOTO_LOADING'));
		jsAjaxUtil.LoadData('/bitrix/admin/wdu_goto.php?lang=' + phpVars.LANGUAGE_ID + '&' + Math.random(), wduPopupGotoCallback);
		wduPopupGoTo.Show();
	}
}