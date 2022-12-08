<?
use
	\WD\Utilities\DirSize,
	\WD\Utilities\Helper;

/********************************************
For module `security` it need to add rule:
/bitrix/admin/wdu_fastsql_edit*
********************************************/
#define("NOT_CHECK_PERMISSIONS", true);
$ModuleID = 'webdebug.utilities';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$ModuleID.'/prolog.php');
CJSCore::Init('jquery');
if (!CModule::IncludeModule($ModuleID)) {
	die('Module is not found!');
}
IncludeModuleLangFile(__FILE__);

$ModuleRights = $APPLICATION->GetGroupRight($ModuleID);
if($ModuleRights=="D") {
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

if($_GET['process']=='Y') {
	$APPLICATION->RestartBuffer();
	$arState = &$_SESSION['WDU_DIRSIZE_STATE'];
	if($_GET['start']=='Y') {
		DirSize::Truncate();
		DirSize::RemoveLastTime();
		$arState = array(
			'FILES_FOUND_COUNT' => 0,
			'FILES_FOUND_SIZE' => 0,
		);
		$mResult = 'BREAK';
	} else {
		$obDirScan = new DirSize(DirSize::STEP_TIME, $arState);
		if(isset($arState['START_PATH']) && !empty($arState['START_PATH'])) {
			$obDirScan->startPath = $arState['START_PATH'];
		}
		$mResult = $obDirScan->Scan($_SERVER['DOCUMENT_ROOT']);
	}
	if($mResult==='BREAK') {
		$arState['START_PATH'] = $obDirScan->nextPath;
		print GetMessage('WDU_FILES_FOUND_STATUS',array('#FILES_FOUND_COUNT#'=>$arState['FILES_FOUND_COUNT'],'#FILES_FOUND_SIZE#'=>CFile::FormatSize($arState['FILES_FOUND_SIZE'])));
		print '<input type="hidden" data-status="next" />';
	} elseif ($mResult===true){
		DirSize::SetLastTime();
		print '<input type="hidden" data-status="success" />';
		print '<input type="hidden" data-date="'.date(CDatabase::DateFormatToPHP(FORMAT_DATETIME), Helper::getOption(WDU_MODULE,'dirsize_date_last')).'" />';
		unset($arState);
		print GetMessage('WDU_FILES_RESULT_SUCCESS');
	} elseif ($mResult===false){
		print '<input type="hidden" data-status="error" />';
		unset($arState);
		print GetMessage('WDU_FILES_RESULT_ERROR');
	}
	die();
}

if($_GET['get_table']=='Y') {
	$APPLICATION->RestartBuffer();
	$Path = urldecode($_GET['path']);
	if(!strlen($Path)) {
		$Path = '/';
	}
	$arItems = DirSize::GetPathItems($Path);
	$SizeSumm = 0;
	foreach($arItems as $arItem){
		$SizeSumm += $arItem['SIZE'];
	}
	$MaxItems = 100;
	$arItems = array_slice($arItems,0,$MaxItems);
	?>
		<div class="adm-list-table-wrap">
			<div class="adm-list-table-top wdu_dirsize_path">
				<?
				$arPath = explode('/',$Path);
				foreach($arPath as $Key => $Value){
					if(strlen($Value)===0) {
						unset($arPath[$Key]);
					}
				}
				$strPath = '';
				print GetMessage('WDU_PATH_CURRENT');
				print '<a href="#" data-path="/">'.GetMessage('WDU_PATH_ROOT').'</a> ';
				foreach($arPath as $strPathItem){
					$strPath .= '/'.$strPathItem;
					print ' / <a href="#" data-path="'.$strPath.'">'.$strPathItem.'</a> ';
				}
				print '<br/>';
				print GetMessage('WDU_SIZE_CURRENT');
				print CFile::FormatSize($SizeSumm);
				?>
			</div>
			<table class="adm-list-table">
				<thead>
					<tr class="adm-list-table-header">
						<td class="adm-list-table-cell" style="width:16px">
							<div class="adm-list-table-cell-inner"></div>
						</td>
						<td class="adm-list-table-cell">
							<div class="adm-list-table-cell-inner"><?=GetMessage('WDU_HEADER_NAME');?></div>
						</td>
						<td class="adm-list-table-cell" style="width:80px">
							<div class="adm-list-table-cell-inner"><?=GetMessage('WDU_HEADER_TYPE');?></div>
						</td>
						<td class="adm-list-table-cell" style="width:100px">
							<div class="adm-list-table-cell-inner"><?=GetMessage('WDU_HEADER_SIZE');?></div>
						</td>
						<td class="adm-list-table-cell" style="width:100px">
							<div class="adm-list-table-cell-inner"><?=GetMessage('WDU_HEADER_SIZE_PERCENT');?></div>
						</td>
						<td class="adm-list-table-cell" style="width:100px">
							<div class="adm-list-table-cell-inner"><?=GetMessage('WDU_HEADER_COUNT');?></div>
						</td>
					</tr>
				</thead>
				<tbody>
					<?foreach($arItems as $arItem):?>
						<?$SizeRelative = round($arItem['SIZE']*100/$SizeSumm,2);?>
						<tr class="adm-list-table-row wdu_dirsize_item">
							<td class="adm-list-table-cell align-right">
								<?if($arItem['TYPE']=='D'):?>
									<a href="/bitrix/admin/fileman_admin.php?lang=<?=LANGUAGE_ID?>&path=<?=urlencode($arItem['PATH']);?>" target="_blank">
										<img src="/bitrix/images/fileman/folder.gif" alt="" width="17" height="15" />
									</a>
								<?elseif($arItem['TYPE']=='F'):?>
									<a href="/bitrix/admin/fileman_file_view.php?path=<?=urlencode($arItem['PATH']);?>&lang=<?=LANGUAGE_ID?>&" target="_blank">
										<img src="/bitrix/images/fileman/file.gif" alt="" width="15" height="18" />
									</a>
								<?endif?>
							</td>
							<td class="adm-list-table-cell align-left">
								<div class="item_name">
									<?if($arItem['TYPE']=='D'):?>
										<a href="#" data-path="<?=$arItem['PATH'];?>"><?=pathinfo($arItem['PATH'],PATHINFO_BASENAME);?></a>
									<?elseif($arItem['TYPE']=='F'):?>
										<span class="item"><?=pathinfo($arItem['PATH'],PATHINFO_BASENAME);?></span>
									<?endif?>
								</div>
							</td>
							<td class="adm-list-table-cell align-left adm-list-table-cell-last">
								<?if($arItem['TYPE']=='F'):?>
									<?
										$strPath = ToLower($arItem['PATH']);
										if(preg_match('#\.tar\.gz$#',$strPath)) {
											$strExt = 'tar.gz';
										} else {
											$strExt = end(explode('.',$strPath));
										}
										print $strExt;
									?>
								<?endif?>
							</td>
							<td class="adm-list-table-cell align-left adm-list-table-cell-last">
								<?=CFile::FormatSize($arItem['SIZE']);?>
							</td>
							<td class="adm-list-table-cell align-left adm-list-table-cell-last">
								<?if($SizeRelative>0.00):?>
									<div class="gauge">
										<div class="bar" style="width:<?=$SizeRelative;?>%"></div>
										<div class="bar_text"><?=$SizeRelative;?>%</div>
									</div>
								<?endif?>
							</td>
							<td class="adm-list-table-cell align-left">
								<?if($arItem['TYPE']=='D'):?>
									<?=$arItem['COUNT'];?>
								<?endif?>
							</td>
						</tr>
					<?endforeach?>
				</tbody>
			</table>
		</div>
	<?
	die();
}

$APPLICATION->SetTitle(GetMessage('WDU_PAGE_TITLE'));

$arTabs = array(
	array("DIV"=>"wdu_dirsize", "TAB"=>GetMessage('WDU_TAB1_NAME'), "TITLE"=>GetMessage('WDU_TAB1_DESC')),
);

$obTabControl = new CAdminTabControl("WDU_DirSize", $arTabs);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$mLastTime = DirSize::GetLastTime();
?>

<div id="wdu_dirsize">
	<?$obTabControl->Begin();?>
	<?$obTabControl->BeginNextTab();?>
		<div class="rescan">
			<div class="button">
				<input type="button" value="<?=GetMessage('WDU_RESCAN_TITLE');?>" id="wdu_dirsize_rescan_button" />
				<span class="date_last"><?=GetMessage('WDU_DATE_LAST_TITLE');?> <span class="value"><?=(!empty($mLastTime)?date(CDatabase::DateFormatToPHP(FORMAT_DATETIME),$mLastTime):GetMessage('WDU_DATE_LAST_TITLE_NO'));?></span></span>
			</div>
			<div class="results" style="display:none; margin-top:20px;"><?=GetMessage('WDU_FILES_FOUND_STATUS',array('#FILES_FOUND_COUNT#'=>0,'#FILES_FOUND_SIZE#'=>CFile::FormatSize(0)));?></div>
		</div>
		<div id="dirsize_table"></div>
	<?$obTabControl->Buttons(false);?>
	<?$obTabControl->End();?>
</div>

<script>
function DirSizeProcess(Start){
	$('#wdu_dirsize .rescan .results').show();
	$.ajax({
		url: '<?=$APPLICATION->GetCurPageParam('',array(''));?>',
		type: 'GET',
		data: 'process=Y&start='+(Start==true?'Y':'N'),
		success: function(HTML) {
			console.log(HTML);
			$('#wdu_dirsize .rescan .results').html(HTML);
			var ObjectStatus = $('#wdu_dirsize .rescan .results input[type=hidden][data-status]');
			if(ObjectStatus.length==1) {
				if(ObjectStatus.attr('data-status')=='next') {
					setTimeout(function(){
						DirSizeProcess(false);
					},<?=(DirSize::DELAY_TIME*1000);?>);
				} else if (ObjectStatus.attr('data-status')=='success'){
					$('#wdu_dirsize .rescan .results').hide();
					DirSizeToggleControls(true);
					if($('#wdu_dirsize .rescan .results input[type=hidden][data-date]').length==1) {
						$('#wdu_dirsize .rescan .date_last .value').html($('#wdu_dirsize .rescan .results input[type=hidden][data-date]').attr('data-date'));
					}
					DirSizeLoadTable('/');
				} else if (ObjectStatus.attr('data-status')=='error'){
					DirSizeToggleControls(true);
				}
			} else {
				DirSizeToggleControls(true);
			}
		}
	});
}
function DirSizeToggleControls(Enabled){
	if(Enabled) {
		$('#wdu_dirsize_rescan_button').removeAttr('disabled');
		$('#dirsize_table').show();
	} else {
		$('#wdu_dirsize_rescan_button').attr('disabled','disabled');
		$('#dirsize_table').hide();
	}
}
function DirSizeLoadTable(Path){
	$('#dirsize_table').addClass('loading').load('<?=$APPLICATION->GetCurPageParam('get_table=Y',array('get_table'));?>&path='+encodeURIComponent(Path),function(){
		$(this).removeClass('loading');
	});
}
$(document).delegate('.wdu_dirsize_item .item_name a, .wdu_dirsize_path a','click',function(E){
	E.preventDefault();
	var Path = $(this).attr('data-path');
	DirSizeLoadTable(Path);
	window.history.pushState(Path,document.title,'/bitrix/admin/wdu_dirsize.php?lang=<?=LANGUAGE_ID;?>&path='+encodeURIComponent(Path));
});
$(document).delegate('.wdu_dirsize_item','dblclick',function(E){
	var Link = $(this).find('.item_name a[data-path]');
	if(Link.length==1) {
		Link.click();
	}
});
$(document).ready(function(){
	$('#wdu_dirsize_rescan_button').click(function(){
		DirSizeToggleControls(false);
		DirSizeProcess(true);
		$('#wdu_dirsize .rescan .date_last .value').html('<?=GetMessage('WDU_DATE_LAST_TITLE_NO')?>');
	});
	$(window).on('popstate',function(E){
		if(E.originalEvent.state!=undefined && E.originalEvent.state.length>0) {
			DirSizeLoadTable(E.originalEvent.state);
		}
	});
	<?if($mLastTime!==false):?>
		<?$strPathFromUrl = @urldecode($_GET['path']);?>
		DirSizeLoadTable('<?=(!empty($strPathFromUrl)?$strPathFromUrl:'/');?>');
	<?endif?>
});
</script>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>