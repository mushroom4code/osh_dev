<?
namespace WD\Utilities\Export;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Options,
	\WD\Utilities\MenuManager;

Helper::loadMessages();

return [
	'OPTIONS' => [
		'use_select2_for_modules' => [],
		'set_admin_favicon' => [
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				?>
				<script>
				$(document).delegate('#<?=$strOptionId;?>', 'change', function(e){
					$('#<?=$obOptions->getOptionPrefix('admin_favicon');?>').closest('tr').toggle($(this).prop('checked'));
				});
				$(document).ready(function(){
					$('#<?=$strOptionId;?>').trigger('change');
				});
				</script>
				<?
			},
		],
		'admin_favicon' => [
			'TYPE' => 'text',
			'ATTR' => 'style="width:80%;"',
			'CALLBACK_MORE' => function($obOptions, $arOption, $strOption, $strOptionId){
				$strFunctionName = sprintf('WDU_%s_SelectFavicon', $strOption);
				$strEventName = sprintf('WDU_%s_OnSelectFavicon', $strOption);
				?>
					<script>
					function <?=$strEventName;?>(filename, path, site){
						if(path.length > 1) {
							path += '/';
						}
						$('#<?=$strOptionId;?>').val(path + filename);
					}
					</script>
					<input type="button" value="..." onclick="<?=$strFunctionName;?>();" />
				<?
				$arDialogParams = [
					'event' => $strFunctionName,
					'arResultDest' => ['FUNCTION_NAME' => $strEventName],
					'arPath' => [],
					'select' => 'F',
					'operation' => 'O',
					'showUploadTab' => true,
					'showAddToMenuTab' => false,
					'fileFilter' => 'ico, gif, png',
					'allowAllFiles' => true,
					'saveConfig' => true,
				];
				\CAdminFileDialog::ShowScript($arDialogParams);
			},
		],
		'hide_partners_menu' => [
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				?>
				<script>
				$(document).delegate('#<?=$strOptionId;?>', 'change', function(e){
					var rowFavicon = $('#<?=$obOptions->getOptionPrefix('hide_partners_menu_exclude');?>').closest('tr');
					rowFavicon.toggle($(this).prop('checked'));
				});
				$(document).ready(function(){
					$('#<?=$strOptionId;?>').trigger('change');
				});
				</script>
				<?
			},
		],
		'hide_partners_menu_exclude' => [
			'TYPE' => 'text',
			'ATTR' => 'style="width:80%;"',
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				$strRowId = $obOptions->getOptionRowId($strOption);
				?>
				<script>
				let wduPopupSelectHiddenMenu;
				wduPopupSelectHiddenMenu = new WduPopup({
					height: 360,
					width: 500
				});
				wduPopupSelectHiddenMenu.Open = function(value){
					let popup = this;
					popup.WdSetContent('<?=Options::getMessage('HIDE_PARTNERS_MENU_EXCLUDE_LOADING');?>');
					popup.WdLoadContentAjax('<?=$strOption;?>', false, {current_value: value});
					popup.WdSetNavButtons([{
						'name': '<?=Options::getMessage('HIDE_PARTNERS_MENU_EXCLUDE_SAVE');?>',
						'id': 'wdu_save',
						'className': 'adm-btn-green',
						'action': function(){
							let text = $.map($('div[data-role="wdu_form_hide_partners_menu_exclude"] input[type="checkbox"]:checked')
								.get(), function(item){return $(item).val()}).join(', ');
							$('#<?=$strOptionId;?>').val(text);
							popup.Close();
						}
					}]);
					popup.Show();
				}
				$(document).delegate('#<?=$strRowId;?> input[data-role="wdu_hidden_menu_select"]', 'click', function(e){
					wduPopupSelectHiddenMenu.Open($('#<?=$strOptionId;?>').val());
				});
				</script>
				<?
			},
			'CALLBACK_MORE' => function($obOptions, $arOption, $strOption, $strOptionId){
				?>
				<input type="button" value="..." onclick="" data-role="wdu_hidden_menu_select" />
				<?
			},
			'CALLBACK_AJAX' => function(&$arJsonResult, $obOptions, $arOption, $strOption){
				global $adminPage, $adminMenu;
				\WD\Utilities\MenuManager::stopHandler();
				$adminPage->Init();
				$adminMenu->Init($adminPage->aModules);
				\WD\Utilities\MenuManager::stopHandler();
				$arMenuAll = [];
				foreach($adminMenu->aGlobalMenu as $arMenuItem){
					$arMenuAll[$arMenuItem['menu_id']] = $arMenuItem['text'];
				}
				unset($arMenuAll['settings']);
				$arMenuHidden = Helper::splitCommaValues($this->getPost('current_value'));
				$arJsonResult['Title'] = Options::getMessage('HIDE_PARTNERS_MENU_EXCLUDE_POPUP_TITLE');
				ob_start();
				?>
				<style>
				div[data-role="wdu_form_hide_partners_menu_exclude"] ul {list-style:none; margin:0; padding:0;}
				div[data-role="wdu_form_hide_partners_menu_exclude"] ul li {margin-bottom:4px;}
				</style>
				<div data-role="wdu_form_hide_partners_menu_exclude">
					<ul>
						<?foreach($arMenuAll as $strMenuCode => $strMenuName):?>
							<li>
								<label>
									<input type="checkbox" name="menu[]" value="<?=$strMenuCode;?>"
										<?if(in_array($strMenuCode, $arMenuHidden)):?> checked="checked"<?endif?> />
									<span><?=$strMenuName;?></span>
								</label>
							</li>
						<?endforeach?>
					</ul>
				</div>
				<script>
					let checkboxes = document.querySelectorAll(
						'div[data-role="wdu_form_hide_partners_menu_exclude"] input[type="checkbox"]');
					for(let i=0; i<checkboxes.length; i++){
						BX.adminFormTools.modifyCheckbox(checkboxes[i]);
					}
				</script>
				<?
				$arJsonResult['Content'] = ob_get_clean();
			},
		],
	],
];
?>