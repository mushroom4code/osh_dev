<?
namespace WD\Utilities\Export;

use
	\WD\Utilities\Helper,
	\WD\Utilities\Options;

Helper::loadMessages();

return [
	'OPTIONS' => [
		'server_headers_add' => [
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				$strRowId = $obOptions->getOptionRowId($strOption);
				?>
				<style>
				#<?=$strRowId;?> > td.adm-detail-content-cell-l {
					padding-top:14px;
					vertical-align:top;
				}
				#<?=$strRowId;?> > td.adm-detail-content-cell-r input[data-role="wdu_header_add"] {
					height:25px!important;
				}
				#<?=$strRowId;?> > td.adm-detail-content-cell-r tr:first-child input[data-role="wdu_header_delete"] {
					display:none;
				}
				</style>
				<script>
				$(document).delegate('#<?=$strRowId;?> input[data-role="wdu_header_add"]', 'click', function(e){
					var body = $(this).closest('table').children('tbody');
					var row = body.find('tr').first().clone();
					row.find('input[type=text]').val('');
					body.append(row);
				});
				$(document).delegate('#<?=$strRowId;?> input[data-role="wdu_header_delete"]', 'click', function(e){
					var row = $(this).closest('tr');
					var body = $(this).closest('tbody');
					if(body.children('tr').length > 1){
						row.remove();
					}
				});
				</script>
				<?
			},
			'CALLBACK_MAIN' => function($obOptions, $arOption, $strOption, $strOptionId){
				$arHeaders = Helper::getOption($obOptions->getModuleId(), $strOption);
				if(strlen($arHeaders)){
					$arHeaders = unserialize($arHeaders);
				}
				if(!is_array($arHeaders)){
					$arHeaders = [];
				}
				if(empty($arHeaders)){
					$arHeaders[] = false;
				}
				?>
				<table class="table_<?=$strOption;?>">
					<tbody>
						<?foreach($arHeaders as $strHeader):?>
							<?if(strlen($strHeader) || count($arHeaders) <= 1):?>
								<tr>
									<td>
										<input type="text" name="<?=$strOption;?>[]" value="<?=htmlspecialcharsbx($strHeader);?>" size="50" 
											placeholder="<?=Options::getMessage('SERVER_HEADERS_ADD_PLACEHOLDER');?>" />
									</td>
									<td>
										<input type="button" value="&times;" 
											title="<?=Options::getMessage('SERVER_HEADERS_BUTTON_DELETE');?>" 
											data-role="wdu_header_delete" />
									</td>
								</tr>
							<?endif?>
						<?endforeach?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<input type="button" value="<?=Options::getMessage('SERVER_HEADERS_BUTTON_ADD');?>"
									data-role="wdu_header_add" />
							</td>
						</tr>
					</tfoot>
				</table>
				<?
			},
			'CALLBACK_BEFORE_SAVE' => function($obOptions, &$strValue, $arOption, $strOption, $strOptionId){
				$strValue = serialize($arOption['ORIGINAL_VALUE']);
			},
		],
		'server_headers_remove' => [
			'CALLBACK_HEAD_DATA' => function($obOptions, $arOption, $strOption, $strOptionId){
				$strRowId = $obOptions->getOptionRowId($strOption);
				?>
				<style>
				#<?=$strRowId;?> > td.adm-detail-content-cell-l {
					padding-top:14px;
					vertical-align:top;
				}
				#<?=$strRowId;?> > td.adm-detail-content-cell-r input[data-role="wdu_header_add"] {
					height:25px!important;
				}
				#<?=$strRowId;?> > td.adm-detail-content-cell-r tr:first-child input[data-role="wdu_header_delete"] {
					display:none;
				}
				</style>
				<script>
				$(document).delegate('#<?=$strRowId;?> input[data-role="wdu_header_add"]', 'click', function(e){
					var body = $(this).closest('table').children('tbody');
					var row = body.find('tr').first().clone();
					row.find('input[type=text]').val('');
					body.append(row);
				});
				$(document).delegate('#<?=$strRowId;?> input[data-role="wdu_header_delete"]', 'click', function(e){
					var row = $(this).closest('tr');
					var body = $(this).closest('tbody');
					if(body.children('tr').length > 1){
						row.remove();
					}
				});
				</script>
				<?
			},
			'CALLBACK_MAIN' => function($obOptions, $arOption, $strOption, $strOptionId){
				$arHeaders = Helper::getOption($obOptions->getModuleId(), $strOption);
				if(strlen($arHeaders)){
					$arHeaders = unserialize($arHeaders);
				}
				if(!is_array($arHeaders)){
					$arHeaders = [];
				}
				if(empty($arHeaders)){
					$arHeaders[] = false;
				}
				?>
				<table class="table_<?=$strOption;?>">
					<tbody>
						<?foreach($arHeaders as $strHeader):?>
							<?if(strlen($strHeader) || count($arHeaders) <= 1):?>
								<tr>
									<td>
										<input type="text" name="<?=$strOption;?>[]" value="<?=htmlspecialcharsbx($strHeader);?>" size="50" 
											placeholder="<?=Options::getMessage('SERVER_HEADERS_REMOVE_PLACEHOLDER');?>" />
									</td>
									<td>
										<input type="button" value="&times;" 
											title="<?=Options::getMessage('SERVER_HEADERS_BUTTON_DELETE');?>" 
											data-role="wdu_header_delete" />
									</td>
								</tr>
							<?endif?>
						<?endforeach?>
					</tbody>
					<tfoot>
						<tr>
							<td colspan="2">
								<input type="button" value="<?=Options::getMessage('SERVER_HEADERS_BUTTON_ADD');?>"
									data-role="wdu_header_add" />
							</td>
						</tr>
					</tfoot>
				</table>
				<?
			},
			'CALLBACK_BEFORE_SAVE' => function($obOptions, &$strValue, $arOption, $strOption, $strOptionId){
				$strValue = serialize($arOption['ORIGINAL_VALUE']);
			},
		],
	],
];
?>