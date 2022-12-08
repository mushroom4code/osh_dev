<?php
set_time_limit(0);
IncludeModuleLangFile(__FILE__);

class SWSitemapList {
	static $tableName = 'sw_sitemap_xml';

	static public function getEntityAll() {
		global $DB;
		$result = $DB->Query("SELECT * FROM `".self::$tableName."` ORDER BY `ID`", false);
		return $result;
	}

	static public function getEntityById($id) {
		if (!(int)$id) 
			return false;

		global $DB;
		$result = $DB->Query("SELECT * FROM `".self::$tableName."` WHERE `ID` = '" . $DB->ForSql((int)$id) . "'  ORDER BY `ID`", false);
		return $result;
	}

	static public function deleteEntityById($id) {
		if (!(int)$id) 
			return false;

		global $DB;
		self::removeAgent(array('ID' => (int)$id));
		$result = $DB->Query("DELETE FROM `".self::$tableName."` WHERE `ID` = '" . $DB->ForSql((int)$id) . "'", false);
		return $result;
	}

	static public function updateEntityById($id, $entity) {
		if (!(int)$id) 
			return false;

		global $DB;

		$entity['AGENT'] = self::checkAgent($id, $entity);

		$result = $DB->Update(self::$tableName, self::prepareEntityForSQL($entity), "WHERE ID='" . $DB->ForSql((int)$id) . "'", $err_mess . __LINE__);
		if (strlen($strError) <= 0)
			$DB->Commit();
		else
			$DB->Rollback();

		return $result;
	}

	static public function activityEntityById($id, $active = 'Y') {
		if (!(int)$id) 
			return false;

		$entity = array(
			'ACTIVE' => ($active!='Y'?'N':'Y')
		);

		return self::updateEntityById($id, $entity);
	}

	static public function setEntityField($id, $entity = array()) {
		return self::updateEntityById($id, $entity);
	}

	static public function setEntityLastRun($id) {
		global $DB;
		return self::updateEntityById($id, array('LAST_RUN' => $DB->GetNowFunction()));
	}

	static public function prepareEntitySettings($entity) {
		if (isset($_REQUEST['name']))
			$entity['NAME'] = SWSitemapEdit::rq('name');
		$entity['ACTIVE'] = (!isset($_REQUEST['ACTIVE']) || $_REQUEST['ACTIVE'] !== 'Y' ? 'N' : 'Y');
		if (isset($_REQUEST['LID']))
			$entity['SITE_ID'] = SWSitemapEdit::rq('LID');
		if (isset($_REQUEST['site_dir']))
			$entity['SETTINGS']['site_dir'] = SWSitemapEdit::rq('site_dir');
		if (isset($_REQUEST['site_doc_root']))
			$entity['SETTINGS']['site_doc_root'] = SWSitemapEdit::rq('site_doc_root');
		if (isset($_REQUEST['https']))
			$entity['SETTINGS']['https'] = SWSitemapEdit::rq('https');
		if (!empty($_REQUEST['domains']))
			$entity['SETTINGS']['domains'] = SWSitemapEdit::rq('domains');
		if (isset($_REQUEST['sitemap_filename']))
			$entity['SETTINGS']['sitemap_filename'] = SWSitemapEdit::rq('sitemap_filename');
		$entity['SETTINGS']['log'] = (!isset($_REQUEST['log']) || $_REQUEST['log'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['convert'] = (!isset($_REQUEST['convert']) || $_REQUEST['convert'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['underscore'] = (!isset($_REQUEST['underscore']) || $_REQUEST['underscore'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['gzip'] = (!isset($_REQUEST['gzip']) || $_REQUEST['gzip'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['check_robots'] = (!isset($_REQUEST['check_robots']) || $_REQUEST['check_robots'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['split_f'] = (!isset($_REQUEST['split_f']) || $_REQUEST['split_f'] !== 'Y' ? 'N' : 'Y');
		if (isset($_REQUEST['split']))
			$entity['SETTINGS']['split'] = SWSitemapEdit::rq('split');
		if (isset($_REQUEST['user_agent']))
			$entity['SETTINGS']['user_agent'] = SWSitemapEdit::rq('user_agent');
		$entity['SETTINGS']['aspro_sm'] = (!isset($_REQUEST['aspro_sm']) || $_REQUEST['aspro_sm'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['aspro_sm_val'] = SWSitemapEdit::rq('aspro_sm_val');
		if (!empty($_REQUEST['sotbit']))
			$entity['SETTINGS']['sotbit'] = $_REQUEST['sotbit'];
		$entity['SETTINGS']['sotbit_sm'] = (!isset($_REQUEST['sotbit_sm']) || $_REQUEST['sotbit_sm'] !== 'Y' ? 'N' : 'Y');
		$entity['SETTINGS']['zverushki_sm'] = (!isset($_REQUEST['zverushki_sm']) || $_REQUEST['zverushki_sm'] !== 'Y' ? 'N' : 'Y');

		$entity['SETTINGS']['dir'] = array();
		$entity['SETTINGS']['file'] = array();
		$entity['SETTINGS']['iblock'] = array();
		if (!empty($_REQUEST['dir'])) {
			$entity['SETTINGS']['dir'] = $_REQUEST['dir'];
			foreach($entity['SETTINGS']['dir'] as $dkey => $dir)
				if ($dir['active'] != 'Y')
					unset($entity['SETTINGS']['dir'][$dkey]);
		}
		if (!empty($_REQUEST['file'])) {
			$entity['SETTINGS']['file'] = $_REQUEST['file'];
			foreach($entity['SETTINGS']['file'] as $fkey => $file)
				if ($file['active'] != 'Y')
					unset($entity['SETTINGS']['file'][$fkey]);
		}

		if (!empty($_REQUEST['iblock'])) {
			$entity['SETTINGS']['iblock'] = $_REQUEST['iblock'];
			foreach($entity['SETTINGS']['iblock'] as $ikey => $iblock) {
				if ($iblock['active'] != 'Y') {
					unset($entity['SETTINGS']['iblock'][$ikey]);
					$mxResult = SWSitemapGenerate::checkSCUIBlock($ikey);
					if ($mxResult && $entity['SETTINGS']['iblock'][$mxResult['PRODUCT_IBLOCK_ID']]['active'] != 'Y')
						unset($entity['SETTINGS']['iblock'][$ikey]);
				}
			}
		}

		$tmpstatic = array();
		if (!empty($_REQUEST['static']))
			foreach($_REQUEST['static'] as $static)
				if ($static['del'] !== 'Y' && !empty($static['name']))
					$tmpstatic[$static['name']] = $static;
		$entity['SETTINGS']['static'] = $tmpstatic;
		unset($tmpstatic);

		return $entity;
	}

	static public function checkEntityBeforeSave($entity) {
		global $error;
		$error = '';
		$strWarning = '';
		if (strlen($entity['NAME']) < 1)
			$strWarning .= GetMessage("SW_BAD_NAME")."<br>";
		if (strlen($entity['SITE_ID']) < 1)
			$strWarning .= GetMessage("SW_BAD_LID")."<br>";
		if (strlen($entity['SETTINGS']['domains'][0]) < 1)
			$strWarning .= GetMessage("SW_BAD_DOMEN")."<br>";
		if (strlen($entity['SETTINGS']['sitemap_filename']) < 1)
			$strWarning .= GetMessage("SW_BAD_FILENAME")."<br>";
		if (
			empty($entity['SETTINGS']['dir']) &&
			empty($entity['SETTINGS']['file']) &&
			empty($entity['SETTINGS']['iblock']) &&
			empty($entity['SETTINGS']['static'])
		)
			$strWarning .= GetMessage("SW_BAD_MAP")."<br>";

		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");
		if ($strWarning != '')
			$error = new \_CIBlockError(2, "SW_BAD_SAVE", $strWarning);

		return $error;
	}

	static public function prepareEntityForSQL($entity = array()) {
		global $DB;
		foreach($entity as $key => &$val) {
			if ($key == 'LAST_RUN')
				$val = $DB->GetNowFunction();
			else if(is_array($val))
				$val = "'".serialize($val)."'";
			else
				$val = "'".$DB->ForSql(htmlspecialcharsEx(trim($val)))."'";
		}
		$entity['TIMESTAMP_X'] = $DB->GetNowFunction();
		return $entity;
	}

	static public function saveEntityByEntity($entity = array()) {
		global $DB, $APPLICATION;
		$id = $entity['ID'];

		$DB->PrepareFields(self::$tableName);

		$entity = self::prepareEntitySettings($entity);
		$error = self::checkEntityBeforeSave($entity);
		if (!$error) {
			unset($entity['ID']);

			$DB->StartTransaction();
			if ($id > 0)
				self::updateEntityById($id, $entity);
			else {
				$entity = self::prepareEntityForSQL($entity);
				$id = $DB->Insert(self::$tableName, $entity, $err_mess . __LINE__ , false);
			}
			$id = intval($id);
			if (strlen($strError) <= 0) {
				$DB->Commit();

				self::setEntityField($id, array('ACTIVE' => $entity['ACTIVE']));

				if(isset($_REQUEST['save']) && $_REQUEST['save'] != '')
					LocalRedirect(BX_ROOT . "/admin/solverweb_sitemap_list.php?lang=" . LANGUAGE_ID);
				else
					LocalRedirect($APPLICATION->GetCurPageParam("id=" . $id . "&tabControl_active_tab=" . $_REQUEST['tabControl_active_tab'], array('id', 'tabControl_active_tab', 'action', 'copyID')));
			} else $DB->Rollback();
		} else return $entity;
	}

	static public function getIblocksByType($lid, $iblock = '') {
		if (!CModule::IncludeModule('iblock'))
			return false;

		$arIBlocks = array();
		$result = array();
		$arIBlocks = self::getIblockBySite($lid, $iblock = '');

		foreach ($arIBlocks as $ib)
			$result[$ib['TYPE']][] = $ib;

		return $result;
	}

	static public function getIblockBySite($lid, $iblock = '') {
		if (!CModule::IncludeModule('iblock'))
			return false;
		$arIBlocks = array();
		$filter = array('SITE_ID' => $lid, 'ACTIVE' => 'Y');
		if ((int)$iblock > 0)
			$filter['IBLOCK_ID'] = (int)$iblock;
		$rsIBlock = CIBlock::GetList(array('LID' => 'DESC'), $filter, false);
		while ($arIblock = $rsIBlock->Fetch()) {
			$arIBlocks[$arIblock['ID']] = array(
				'ID'	=> $arIblock['ID'],
				'TYPE'	=> $arIblock['IBLOCK_TYPE_ID'],
				'CODE'	=> $arIblock['CODE'],
				'NAME'	=> $arIblock['NAME'],
				'SPU'	=> $arIblock['SECTION_PAGE_URL'],
				'DPU'	=> $arIblock['DETAIL_PAGE_URL'],
			);
		}
		return $arIBlocks;
	}

	static public function addAgent($entity) {
		return CAgent::AddAgent(
			"SWSitemapGenerate::GenerateAgent(".$entity['ID'].");",
			SWSitemapGenerate::$MODULE_ID,
			"Y",
			86400,
			'',
			$entity['ACTIVE'],
			ConvertTimeStamp((MakeTimeStamp(date('d m Y')) + 97200), "FULL")
		);
	}

	static public function removeAgent($entity) {
		if (!$entity['ID']) {
			if (!$entity['AGENT'])
				$entity = self::getEntityById($entity['ID'])->fetch();
			else
				return false;
		}

		return CAgent::RemoveAgent("SWSitemapGenerate::GenerateAgent(".$entity['ID'].");", SWSitemapGenerate::$MODULE_ID);
	}

	static public function updateAgent($entity, $arFields) {
		if (!$entity['AGENT']) {
			if (!$entity['ID']) return false;
			else $entity = self::getEntityById($entity['ID'])->fetch();
		}

		if (!$entity['AGENT']) return;

		return CAgent::Update($entity['AGENT'], $arFields);
	}

	static public function getAgent($entity) {
		if (!$entity['AGENT']) {
			if (!$entity['ID']) return false;
			else {
				$entity = self::getEntityById($entity['ID'])->fetch();
				if (!$entity['AGENT']) return false;
			}
		}

		if (
			(
				$agent = CAgent::GetById($entity['AGENT'])->GetNext()
			) && $agent['MODULE_ID'] == SWSitemapGenerate::$MODULE_ID
		)
			return $agent['ID'];
		else
			return false;
	}

	static public function checkAgent($id, $entity) {
		if (!(int)$id) return false;
		if ($agent = self::getAgent(array('ID' => $id))) {
			if(isset($entity['ACTIVE']))
				self::updateAgent(array('ID' => $id), array('ACTIVE' => $entity['ACTIVE']));
		}
		else if ($entity['ACTIVE'] == 'Y')
			$agent = self::addAgent(array('ID' => $id, 'ACTIVE' => $entity['ACTIVE']));

		return $agent;
	}

	static public function id404() {
		\CAdminMessage::ShowMessage(
			array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SW_ERROR_404")
			)
		);
	}

	static public function isSystem($path) {
		if (preg_match("#/\\.#", $path))
			return true;

		if (substr($path, 0, strlen($_SERVER["DOCUMENT_ROOT"])) === $_SERVER["DOCUMENT_ROOT"]) {
			$relativePath = substr($path, strlen($_SERVER["DOCUMENT_ROOT"]));
			$relativePath = ltrim($relativePath, "/");
			if (($pos = strpos($relativePath, "/")) !== false)
				$s = substr($relativePath, 0, $pos);
			else
				$s = $relativePath;
			$s = strtolower(rtrim($s, "."));

			$ar = array(
				"bitrix" => 1,
				"local" => 1,
				COption::GetOptionString("main", "upload_dir", "upload") => 1,
				"urlrewrite.php" => 1,
				"node_modules" => 1,
				"vendor" => 1,
				"phpmyadmin" => 1,
			);
			if (isset($ar[$s]))
				return true;
		}

		return false;
	}

	static public function getDirStructure($bLogical, $site, $path) {	// SEO before v14
		global $USER;

		$arDirContent = array();
		if($USER->CanDoFileOperation('fm_view_listing', array($site, $path))) {
			CModule::IncludeModule('fileman');

			$arDirs = array();
			$arFiles = array();

			\CFileMan::GetDirList(array($site, $path), $arDirs, $arFiles, array(), array("NAME" => "asc"), "DF", $bLogical, true);

			$arDirContent_t = array_merge($arDirs, $arFiles);
			for($i=0,$l = count($arDirContent_t);$i<$l;$i++) {
				$file = $arDirContent_t[$i];
				$arPath = array($site, $file['ABS_PATH']);
				if(
					($file["TYPE"]=="F" && !$USER->CanDoFileOperation('fm_view_file',$arPath))
					|| ($file["TYPE"]=="D" && !$USER->CanDoFileOperation('fm_view_listing',$arPath))
					|| ($file["TYPE"]=="F" && $file["NAME"]==".section.php")
					|| is_link($file['PATH'])
				) {
					continue;
				}

				$p = basename($file['PATH']);

				if(
					SWSitemapList::isSystem($file['PATH'])
					|| $file['TYPE'] == 'F' && in_array($p, array("detail.php","urlrewrite.php"))
					|| $file['TYPE'] == 'D' && SWSitemapList::isSystem($p)
					|| is_link($file['PATH'])
				) {
					continue;
				}

				$arFileData = array(
					'NAME' => $bLogical ? $file['LOGIC_NAME'] : $p,
					'FILE' => $p,
					'TYPE' => $file['TYPE'],
					'DATA' => $file,
				);

				if(strlen($arFileData['NAME']) <= 0)
					$arFileData['NAME'] = GetMessage('SEO_DIR_LOGICAL_NO_NAME');

				$arDirContent[] = $arFileData;
			}
			unset($arDirContent_t);
		}

		return $arDirContent;
	}

	static public function getSupportButton() {
		?>
		<script>
			(function(w,d,u){
				var s=d.createElement('script');s.async=true;s.src=u+'?'+(Date.now()/60000|0);
				var h=d.getElementsByTagName('script')[0];h.parentNode.insertBefore(s,h);
			})(window,document,'https://cdn-ru.bitrix24.ru/b199891/crm/site_button/loader_3_la5kw4.js');
		</script>
		<?
	}
}
class SWSitemapEdit {
	public static function rq($key) {
		if (is_array($_REQUEST[$key])) {
			$return = array();
			foreach($_REQUEST[$key] as $val)
				$return[] = trim(htmlspecialcharsEx($val, ENT_QUOTES));
		} else
			$return = trim(htmlspecialcharsEx($_REQUEST[$key], ENT_QUOTES));
		return $return;
	}

	public static function getDirStructure($bLogical, $site_id, $dir, $depth, $checked, $arChecked = array(), $settings){
		if(!is_array($arChecked))
			$arChecked = array();
		$arDirs = SWSitemapList::getDirStructure($bLogical, $site_id, $dir);
		if(count($arDirs) > 0) {
			foreach ($arDirs as $arDir) {
				if ($arDir['TYPE'] == 'F' && substr($arDir['FILE'], strlen($arDir['FILE'])-4) != '.php') continue;
				$d = rtrim($dir, '/').'/'.$arDir['FILE'];
				$bChecked = $arChecked[$d]['active'] === 'Y' || $checked && $arChecked[$d]['active'] !== 'N';

				$d = htmlspecialcharsEx($d);
				$r = RandString(8);

				$varName = $arDir['TYPE'] == 'D' ? 'dir' : 'file';
	?>
	<div class="sitemap-dir-item">
	<? if($arDir['TYPE'] == 'D'): ?>
		<span onclick="loadDir(<?=$bLogical?'true':'false'?>, this, '<?=CUtil::JSEscape($d)?>', '<?=$r?>', '<?=$depth+1?>', BX('dir_<?=$d?>').checked, false)" class="sitemap-tree-icon"></span>
	<? endif; ?>
		<span class="sitemap-dir-item-text">
			<input type="hidden" name="<?=$varName?>[<?=$d?>][active]" value="N" />
			<input type="checkbox" name="<?=$varName?>[<?=$d?>][active]" id="dir_<?=$d?>"<?=($bChecked) ? ' checked="checked"' : ''?> value="Y" onclick="checkAll('<?=$r?>', this.checked);" />
			<label for="dir_<?=$d?>"><?=htmlspecialcharsEx($arDir['NAME'].($bLogical ? (' ('.$arDir['FILE'].')') : ''))?></label>
			
	<? if($arDir['TYPE'] == 'F'/* && $bChecked */): ?>
			<div class="item_props">
				<? echo self::showFreqSelect($varName.'['.$d.'][freq]', $settings['file'][$d]['freq'], $bChecked); ?>
				<? echo self::showModSelect($varName.'['.$d.'][mod]', $settings['file'][$d]['mod'], $bChecked); ?>
				<? echo self::staticAddCellPrior($varName.'['.$d.'][prior]', (is_numeric($settings[$varName][$d]['prior'])?$settings[$varName][$d]['prior']:'0.5'), $bChecked); ?>
			</div>
	<? endif; ?>
		</span>
		<div id="subdirs_<?=$r?>" class="sitemap-dir-item-children"><?
			if($arDir['TYPE'] == 'D'):
				$arSubDirs = self::getDirStructure($bLogical, $site_id, $d, $depth+1, $checked, $arChecked, $settings);
			endif;
		?></div>
	</div>
	<?
			}
		} else {
			echo $space.GetMessage('SW_SITEMAP_NO_DIRS_FOUND');
		}
	}

	public static function showFreqSelect($name, $selected = 'weekly', $check = false) {
		$arVar = array(
			'none' => GetMessage('SW_FREQ_NONE'),
			'always' => GetMessage('SW_FREQ_ALWAYS'),
			'hourly' => GetMessage('SW_FREQ_HOURLY'),
			'daily' => GetMessage('SW_FREQ_DAILY'),
			'weekly' => GetMessage('SW_FREQ_WEEKLY'),
			'monthly' => GetMessage('SW_FREQ_MONTHLY'),
			'yearly' => GetMessage('SW_FREQ_YEARLY'),
			'never' => GetMessage('SW_FREQ_NEVER'),
		);
		$result = '';
		$result .= '<select './*(!$check?'data-':'').*/'class="input_freq" name="'.$name.'" title="'.GetMessage("SW_FREQUENCY").'">';
		foreach ($arVar as $val => $var)
			$result .= '<option value="'.$val.'"'.($selected==$val?' selected="selected"':'').'>'.$var.'</option>';
		$result .= '</select>';
		
		return $result;
	}

	public static function showModSelect($name, $selected = '1', $check = false, $iblock = false) {
		$arVar = array(
			'0' => GetMessage('SW_NO'),
			'1' => GetMessage('SW_YES'),
			'2' => GetMessage('SW_ALWAYS'),
		);
		if ($iblock) {
			$arVar['3'] = GetMessage('SW_BY_ELEMENT');
			$arVar['4'] = GetMessage('SW_BY_ELEMENT_IF');
		}

		$result = '';
		$result .= '<select './*(!$check?'data-':'').*/'class="input_mod" name="'.$name.'" title="'.GetMessage("SW_MODTIME").'">';
		foreach ($arVar as $val => $var)
			$result .= '<option value="'.$val.'"'.($selected==$val?' selected="selected"':'').'>'.$var.'</option>';
		$result .= '</select>';

		return $result;
	}

	public static function showUASelect($name, $selected = '1', $check = false, $iblock = false) {
		$arVar = SWSitemapGenerate::$user_agent;
		
		$result = '';
		$result .= '<select './*(!$check?'data-':'').*/'name="'.$name.'" title="'.GetMessage("SW_USER_AGENT").'">';
		foreach ($arVar as $val => $var)
			$result .= '<option value="'.$val.'"'.($selected==$val?' selected="selected"':'').'>'.$var.'</option>';
		$result .= '</select>';

		return $result;
	}

	public static function staticAddRow($staticId, $arStatic = array()) {
		return '<tr><td style="text-align:center">'.self::staticAddCellReverse('static['.$staticId.'][reverse]', $arStatic['reverse']).'</td>
		<td class="bx-digit-cell">'.self::staticAddCellName('static['.$staticId.'][name]', $arStatic['name']).'</td>
		<td>'.self::staticAddCellFreq('static['.$staticId.'][freq]', $arStatic['freq']).'</td>
		<td>'.self::staticAddCellMod('static['.$staticId.'][mod]', $arStatic['mod']).'</td>
		<td style="text-align:center">'.self::staticAddCellPrior('static['.$staticId.'][prior]', $arStatic['prior']).'</td>
		<td style="text-align:center">'.self::staticAddCellDel('static['.$staticId.'][del]').'</td></tr>';
	}
	public static function staticAddCellReverse($id, $val = false) {
		return '<input type="checkbox" class="adm-designed-checkbox-label" name="'.$id.'" value="Y"'.($val=='Y'?' checked="checked"':'').'>';
	}
	public static function staticAddCellName($id, $val = '') {
		return '<input type="text" name="'.$id.'" id="static_'.$id.'" value="'.htmlspecialcharsEx($val).'" size="15" maxlength="200" style="width:90%">';
	}
	public static function staticAddCellFreq($id, $val = '') {
		return self::showFreqSelect($id, $val);
	}
	public static function staticAddCellMod($id, $val = '') {
		return self::showModSelect($id, $val);
	}
	public static function staticAddCellPrior($id, $val = false, $check = false) {
		return '<input './*(!$check?'data-':'').*/'class="input_prior" name="'.$id.'" type="number" class="adm-input" step="0.1" min="0" max="1" value="'.(is_numeric($val)?$val:"0.5").'" style="width:50px" title="'.GetMessage("SW_PRIORITY").'">';
	}
	public static function staticAddCellDel($id) {
		return '<input type="checkbox" class="adm-designed-checkbox-label" name="'.$id.'" value="Y">';
	}

	public static function getNumberOfTabs($num) {
		$result = array();
		for($i=1;$i<$num+1;$i++)
			$result[] = array(
				"DIV" => "edit".$i,
				"TAB" => GetMessage('SW_TAB_'.$i),
				"ICON" => "sale",
				"TITLE" => GetMessage('SW_TAB_TITLE_'.$i)
			);
		return $result;
	}

	public static function getSiteList($filter = array()) {
		$sites = CSite::GetList(
			$by = "sort",
			$order = "desc",
			$filter
		);
		$result = array();
		while ($arSite = $sites->Fetch()) {
			$domains = array();
			$domains = explode("\n", $arSite['DOMAINS']);
			if (is_array($domains))
				foreach ($domains as $domain)
					$arDomains[] = trim($domain);
			else
				$arDomains[] = trim($domains);
			$result[$arSite['LID']] = array(
				'LID' => $arSite['LID'],
				'NAME' => $arSite['NAME'],
				'DOMAINS' => $arDomains,
				'DIR' => ($arSite['DIR']?:'/'),
				'DOC_ROOT' => ($arSite['DOC_ROOT']?:'')
			);
		}
		return $result;
	}

	public static function SelectBoxMultiRadio($sFieldName, $Value) {
		if(is_array($Value))
			$arValue = $Value;
		else
			$arValue = array($Value);

		$l = self::getSiteList();
		$s = '<div class="adm-list">';
		foreach($l as $l_arr) {
			$s .=
				'<div class="adm-list-item">'.
				'<div class="adm-list-control"><input type="radio" name="'.$sFieldName.'[]" value="'.htmlspecialcharsex($l_arr["LID"]).'" id="'.htmlspecialcharsex($l_arr["LID"]).'" class="typecheckbox"'.(in_array($l_arr["LID"], $arValue)?' checked':'').'></div>'.
				'<div class="adm-list-label"><label for="'.htmlspecialcharsex($l_arr["LID"]).'">['.htmlspecialcharsex($l_arr["LID"]).']&nbsp;'.htmlspecialcharsex($l_arr["NAME"]).'</label></div>'.
				'</div>';
		}
		$s .= '</div>';

		return $s;
    }
}
class SWSitemapGenerate {
	static $MODULE_ID = 'solverweb.sitemap';
	static $arMap = array();
	static $settings = array();
	static $split = 50000;
	static $user_agent = array(
		'0' => '*',
		'1' => 'Googlebot',
		'2' => 'YandexBot'
	);
	static $fileGetContentsSSL = array(
		"ssl" => array(
			"verify_peer" => false,
			"verify_peer_name" => false
		)
	);

	static public function getModuleData() {
		return CModule::CreateModuleObject(self::$MODULE_ID);
	}

	static public function getFileTimestamp($f) {
		if (file_exists($f))
			return date('c', filemtime($f));
			// return date('c', fileatime($f));
		else
			return false;
	}

	static public function getTime($time) {
		if (!CModule::IncludeModule('iblock') || !$time) return false;
		return CIBlockFormatProperties::DateFormat('c', strtotime($time));
	}

	static public function checkForDouble($xmldata = array()) {
		$result = array();
		foreach($xmldata as $var)
			// if(!isset($result[$var['url']]))
				$result[$var['url']] = $var;
		ksort($result);
		return array_values($result);
	}

    static public function clearIndexFromURL($url) {
        $ten = 10;
        $url = (strpos($url,'/index.php')===(strlen($url)-(int)$ten)?rtrim($url,'index.php'):$url);
        $url = (strpos($url,'/index.htm')===(strlen($url)-(int)$ten)?rtrim($url,'index.htm'):$url);
        $url = (strpos($url,'/index.html')===(strlen($url)-(int)$ten-1)?rtrim($url,'index.html'):$url);
		return $url;
	}

	static public function preparePropType($type, $url, $key = 0) {
		if (!empty(self::$settings['static'][$url]) || !empty(self::$settings['static'][self::clearIndexFromURL($url)])) {
			$result = self::$settings['static'][$url];
			// unset(self::$settings['static'][$url]);
		} else
			$result = self::$settings[$type][((int)$key > 0 ? $key : $url)];

		return $result;
	}

	static public function prepareXMLData($url, $time, $type) {
		if ($type['reverse'] == 'Y') return false;
		return array(
			'url'	=> self::clearIndexFromURL($url),
			'mod'	=> ($type['mod']==2?date('c'):($type['mod']?$time:'')),
			'freq'	=> ($type['freq']?:''),
			'prior'	=> ($type['prior']?:''),
		);
	}

	static public function getIndexPage() {
		$index[] = self::prepareXMLData(
			'/index.php',
			self::getFileTimestamp($_SERVER['DOCUMENT_ROOT'].'/index.php'),
			self::preparePropType('static', '/index.php')
		);

		return $index;
	}

	static public function getFilePages() {
		if (empty(self::$settings['file'])) return false;

		$arFiles = array();

		foreach (self::$settings['file'] as $key => $file) {
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$key))
				$arFiles[] = self::prepareXMLData(
					$key,
					self::getFileTimestamp($_SERVER['DOCUMENT_ROOT'].$key),
					self::preparePropType('file', $key)
				);
		}
		return $arFiles;
	}

	static public function getRandomPages() {
		if (empty(self::$settings['static'])) return false;

		$arFiles = array();

		foreach (self::$settings['static'] as $key => $file)
			if ($file['reverse'] != 'Y')
				$arFiles[] = self::prepareXMLData(
					$key,
					self::getFileTimestamp($_SERVER['DOCUMENT_ROOT'].$key),
					self::preparePropType('static', $key)
				);

		return $arFiles;
	}

	static public function checkSectionParentMod($data, $mod) {
		$find = false;
		foreach($data as $k => $sect)
			if ($sect[0]['parent'] && strtotime($sect[0]['mod']) > strtotime($data[$sect[0]['parent']][0]['mod'])) {
				$data[$sect[0]['parent']][0]['mod'] = $sect[0]['mod'];
				$find = true;
			}
		if ($find)
			$data = self::checkSectionParentMod($data, $mod);

		return $data;
	}

	static public function checkSectionElementMod($data, $mod) {
		foreach($data as $k => $sect)
			foreach($sect as $sk => $elem)
				if (
					($sk === 1 && $mod == 3) ||
					($sk && strtotime($elem['mod']) > strtotime($data[$k][0]['mod']))
				)
					$data[$k][0]['mod'] = $elem['mod'];
		$data = self::checkSectionParentMod($data, $mod);

		return $data;
	}

	static public function setSiteDir($path) {
		return str_replace('#SITE_DIR#',self::$settings['site_dir'],$path);
	}

	static public function checkSCUIBlock($iblock) {
		if (!CModule::IncludeModule('catalog')) return false;
		return CCatalogSKU::GetInfoByOfferIBlock($iblock);
	}

	static public function getElementsByIblock($iblock = '', $site_id) {
		if (!CModule::IncludeModule('iblock')) return false;
		
		$mxResult = self::checkSCUIBlock($iblock);
		$mod = self::$settings['iblock'][$iblock]['mod'];
		$arIBlocks = SWSitemapList::getIblockBySite($site_id, $iblock);
		$SEF = (strpos($arIBlocks[$iblock]['DPU'],'#SECTION_ID#') !== false || strpos($arIBlocks[$iblock]['DPU'],'#SECTION_CODE#') !== false || strpos($arIBlocks[$iblock]['DPU'],'#SECTION_CODE_PATH#') !== false);
		$SPU = self::setSiteDir($arIBlocks[$iblock]['SPU']);
		$DPU = self::setSiteDir($arIBlocks[$iblock]['DPU']);

		$arElements = array();
		$arSectionsFilter = array(
			'IBLOCK_ID'     => $iblock,
			'ACTIVE'        => 'Y',
			'GLOBAL_ACTIVE' => 'Y'
		);
		$arSectionsSelect = array(
			'IBLOCK_ID',
			'ID',
			'SECTION_ID',
			'SECTION_PAGE_URL',
			'TIMESTAMP_X'
		);

		foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeSectionGetList", true) as $arEvent)
			\ExecuteModuleEventEx($arEvent, array(&$arSectionsFilter, &$SPU, $iblock, self::$arMap, &$arSectionsSelect));

		$rsSections = CIBlockSection::GetList(
			array(),
			$arSectionsFilter,
			false,
			$arSectionsSelect
		);
		$rsSections->SetUrlTemplates($DPU, $SPU);
		while ($arSection = $rsSections->GetNext()) {
			foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeSectionParse", true) as $arEvent)
				\ExecuteModuleEventEx($arEvent, array(&$arSection, $iblock, self::$arMap));

			$data = self::prepareXMLData(
				$arSection['SECTION_PAGE_URL'],
				self::getTime($arSection['TIMESTAMP_X']),
				self::preparePropType('iblock', $arSection['SECTION_PAGE_URL'], $iblock)
			);
			$data['parent'] = $arSection['SECTION_ID'];

			foreach (\GetModuleEvents(self::$MODULE_ID, "OnAfterSectionParse", true) as $arEvent)
				\ExecuteModuleEventEx($arEvent, array(&$data, &$arSections, $arSection, $iblock, self::$arMap));

			$arSections[$arSection['ID']][] = $data;
		}

		$arElementsFilter = array(
			'IBLOCK_ID'		=> $iblock,
			'LID'			=> $site_id,
			'ACTIVE'		=> 'Y',
			'ACTIVE_DATE'	=> 'Y',
		);
		if ($SEF)
			$arElementsFilter['SECTION_GLOBAL_ACTIVE'] = 'Y';
		if ($mxResult['PRODUCT_IBLOCK_ID'])
			$arElementsFilter['PROPERTY_'.$mxResult['SKU_PROPERTY_ID'].'.ACTIVE'] = 'Y';
		$arElementsSelect = array(
			'ID',
			'IBLOCK_ID',
			'IBLOCK_SECTION_ID',
			'DETAIL_PAGE_URL',
			'TIMESTAMP_X',
		);

		foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeElementGetList", true) as $arEvent)
			\ExecuteModuleEventEx($arEvent, array(&$arElementsFilter, &$DPU, $iblock, self::$arMap, &$arElementsSelect));

		$rsElement = CIBlockElement::GetList(
			array(),
			$arElementsFilter,
			false,
			false,
			$arElementsSelect
		);
		$rsElement->SetUrlTemplates($DPU, $SPU);
		while ($arElement = $rsElement->GetNext()) {
			$element = array();
			foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeElementParse", true) as $arEvent)
				\ExecuteModuleEventEx($arEvent, array(&$arElement, $iblock, self::$arMap));

			if ($SEF && $arElement['IBLOCK_SECTION_ID'] > 0) {
				if (!is_array($arSections[$arElement['IBLOCK_SECTION_ID']])) {
					$dbElSects = CIBlockElement::GetElementGroups($arElement['ID'], true, array('IBLOCK_ID','ID','GLOBAL_ACTIVE'));
					while($arElSect = $dbElSects->Fetch()) {
						if ($arElSect['GLOBAL_ACTIVE'] != 'Y' || !is_array($arSections[$arElSect['ID']])) continue;
						
						$res = CIBlockElement::GetByID($arElement['ID']);
						$res->SetSectionContext($arElSect);
						if ($ar_res = $res->GetNext()) {
							$arElement['IBLOCK_SECTION_ID'] = $arElSect['ID'];
							$arElement['DETAIL_PAGE_URL']	= $ar_res['DETAIL_PAGE_URL'];
							break;
						}
					}
				}

				$element = self::prepareXMLData(
					$arElement['DETAIL_PAGE_URL'],
					self::getTime($arElement['TIMESTAMP_X']),
					self::preparePropType('iblock', $arElement['DETAIL_PAGE_URL'], $iblock)
				);

				foreach (\GetModuleEvents(self::$MODULE_ID, "OnAfterElementParse", true) as $arEvent)
					\ExecuteModuleEventEx($arEvent, array(&$element, &$arSections, $arElement, $iblock, self::$arMap));

				$arSections[$arElement['IBLOCK_SECTION_ID']][] = $element;
			} else {
				$element = self::prepareXMLData(
					$arElement['DETAIL_PAGE_URL'],
					self::getTime($arElement['TIMESTAMP_X']),
					self::preparePropType('iblock', $arElement['DETAIL_PAGE_URL'], $iblock)
				);

				foreach (\GetModuleEvents(self::$MODULE_ID, "OnAfterElementParse", true) as $arEvent)
					\ExecuteModuleEventEx($arEvent, array(&$element, &$arElements, $arElement, $iblock, self::$arMap));

				$arElements[] = $element;
			}
		}

		if (!empty($arSections) && ($mod == 3 || $mod == 4))
			$arSections = self::checkSectionElementMod($arSections, $mod);

		if (!empty($arSections))
			foreach($arSections as $sect)
				foreach($sect as $elem)
					$arElements[] = $elem;

		return $arElements;
	}

    static public function urlEncode($str) {
		global $APPLICATION;
		$strEncodedURL = '';
        $arUrlComponents = preg_split("#(://|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach($arUrlComponents as $i => $part_of_url) {
            if((intval($i) % 2) == 1)
                $strEncodedURL .= (string)$part_of_url;
            else
                $strEncodedURL .= rawurlencode($APPLICATION->ConvertCharset(rawurldecode($part_of_url), SITE_CHARSET, "UTF-8"));
        }
		
        return $strEncodedURL;
    }

    static public function getMapDir($sitemapName) {
		$site_dir = self::$settings['site_dir']?:'/';
		$doc_root = rtrim(self::$settings['site_doc_root']?:$_SERVER["DOCUMENT_ROOT"],'/');
		$arFile = explode('/',$sitemapName);
		array_pop($arFile);
		$dir = $doc_root.$site_dir.implode('/',$arFile);

        return $dir;
    }

    static public function getMapFilename($sitemapName) {
        return array_pop(explode('/',$sitemapName));
    }

    static public function makeMapFilePath($sitemapName) {
		$dir = self::getMapDir($sitemapName);
		if(!is_dir($dir))
			mkdir($dir, 0755, true);
		$filename = self::getMapFilename($sitemapName);
		if (self::$settings['underscore'] == 'Y')
			$filename = str_replace('_','-',$filename);
		$file = $dir.'/'.$filename.'.xml'.(self::$settings['gzip'] == 'Y'?'.gz':'');

		return $file;
    }

	static public function createXML($arData = array(), $sitemapName = 'sitemap', $index = false) {
		$xml = new DOMDocument( "1.0", "UTF-8" );

		// if ($index)
			// $xml->formatOutput = true;

		$xml->appendChild( $xml->createComment("[".date('d.m.Y H:i:s')."] solverweb sitemap generator v".self::getModuleData()->MODULE_VERSION) );
		$xml_urlset = $xml->createElement( $index?"sitemapindex":"urlset" );

		$xml_urlset->setAttribute( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );
		$xml_urlset->setAttribute( "xmlns", "http://www.sitemaps.org/schemas/sitemap/0.9" );
		$xml_urlset->setAttribute( "xsi:schemaLocation", "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/".( $index?"siteindex.xsd":"sitemap.xsd" ) );

		foreach ($arData as $arItem):
			if ($index && self::$settings['underscore'] == 'Y')
				$arItem["url"] = str_replace('_','-',$arItem["url"]);
			$xml_url = $xml->createElement( $index?"sitemap":"url" );
			// $xml_url->appendChild( $xml->createElement( "loc", $arItem["url"] ) );
			$xml_url->appendChild( $xml->createElement( "loc", str_replace('%2F','/',$arItem["url"]) ) ); // urldecode?
			if($index ||$arItem["mod"]) $xml_url->appendChild($xml->createElement( "lastmod",  $index? date('c') : $arItem["mod"] ) );
			if($arItem["prior"]) $xml_url->appendChild( $xml->createElement( "priority", $arItem["prior"] ) );
			if($arItem["freq"] && $arItem["freq"] != 'none') $xml_url->appendChild( $xml->createElement( "changefreq", $arItem["freq"] ) );
			$xml_urlset->appendChild( $xml_url );
		endforeach;

		$xml->appendChild( $xml_urlset );

		$file = self::makeMapFilePath($sitemapName);
		$str = $xml->saveXML();
		$str = rtrim($str);

		// $xml->save($file);
		file_put_contents($file, $str);
		
        if (self::$settings['gzip'] == 'Y') {
			$xmldata = file_get_contents($file, false, stream_context_create(self::$fileGetContentsSSL));
			$gz = gzopen($file, 'w9');
			gzwrite($gz, $xmldata);
			gzclose($gz);
        }
	}

	static public function Generate($id = 0) {
		if (!CModule::IncludeModule('solverweb.sitemap')) return false;
		$converter = CBXPunycode::GetConverter();
		$rsMap = SWSitemapList::getEntityAll();
		while($arMap = $rsMap->fetch()) {
			if ((int)$id > 0 && $arMap['ID'] != $id) continue;

			self::$arMap = array_diff($arMap,['SETTINGS'=>$arMap['SETTINGS']]);
			self::$settings = unserialize($arMap['SETTINGS']);
			self::$split = (int)self::$settings['split'];
			$arMap['DATA'] = array();
			$xmldata = array();

			if (empty(self::$settings['domains'])) continue;

			if ($tmp = self::getFilePages(self::$settings))
				$arMap['DATA']['files'] = $tmp;

			foreach (self::$settings['iblock'] as $iblock_id => $iblock)
				if ($tmp = self::getElementsByIblock($iblock_id, $arMap['SITE_ID'], self::$settings))
					$arMap['DATA']['iblock_'.$iblock_id] = $tmp;

			if (self::$settings['sotbit_sm'] == 'Y' && \Bitrix\Main\Loader::includeModule('sotbit.seometa')) {
				$rsCondition = \Sotbit\Seometa\ConditionTable::getList([
					'select' => [
						'ID',
						'RULE',
						'SITES',
						'ACTIVE',
						'NO_INDEX'
					],
					'filter' => [
						'ACTIVE' => 'Y',
						'!=NO_INDEX' => 'Y',
					]
				]);
				while($arCondition = $rsCondition->Fetch()) {
					if(in_array($arMap['SITE_ID'], unserialize($arCondition['SITES']))) {
						if (empty(unserialize($arCondition['RULE'])['CHILDREN'])) continue;
						$rsSEF = \Sotbit\Seometa\SeometaUrlTable::getList([
							'select' => [
								'CONDITION_ID',
								'ACTIVE',
								'NEW_URL',
								'DATE_CHANGE'
							],
							'filter' => [
								'ACTIVE' => 'Y',
								'CONDITION_ID' => $arCondition['ID']
							]
						]);
						while($arSEF = $rsSEF->Fetch())
							$arMap['DATA']['sotbit'][] = self::prepareXMLData(
								$arSEF['NEW_URL'],
								self::getTime($arSEF['DATE_CHANGE']->toString()),
								self::preparePropType('sotbit', 0)
							);
					}
				}
			}

			if ($tmp = self::getRandomPages(self::$settings))
				$arMap['DATA']['static'] = $tmp;

			foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeXMLGenerate", true) as $arEvent)
				\ExecuteModuleEventEx($arEvent, array(&$arMap['DATA'], self::$arMap));

			$tmp = array();
			foreach (self::$settings['domains'] as $domen) {
				if (self::$settings['convert'] != 'Y' && strpos($domen,'_') === false && !$converter->IsEncoded($domen))
					$domen = $converter->Encode($domen);
				$base = (self::$settings['https']?"https://":"http://").$domen;

				if (
					self::$settings['check_robots'] == 'Y' &&
					file_exists(__DIR__ . '/lib/classes/UserAgentParser.php') &&
					file_exists(__DIR__ . '/lib/classes/robotstxtparser.php')
				) {
					require_once(__DIR__ . '/lib/classes/UserAgentParser.php');
					require_once(__DIR__ . '/lib/classes/robotstxtparser.php');

					$rParser = new RobotsTxtParser(file_get_contents($base.'/robots.txt', false, stream_context_create(self::$fileGetContentsSSL)));
					$user_agent = SWSitemapGenerate::$user_agent[self::$settings['user_agent']];
					$rParser->setUserAgent($user_agent);
				}

				foreach ($arMap['DATA'] as $type => $data)
					foreach ($data as $file) {
						if (empty($file['url'])) continue;
						if ($rParser && $rParser->isDisallowed($file['url'])) continue;

						$file['url'] = $base.self::urlEncode($file['url']);
						$tmp[$type][] = $file;
					}
			}
			$arMap['DATA_COUNT'] = count(self::checkForDouble(call_user_func_array('array_merge', array_values($tmp))));
			if (self::$settings['split_f'] == 'Y') {
				foreach ($tmp as $type => $data) {
					$count = 0;
					$data = self::checkForDouble($data);
					if (count($data) > self::$split) {
						$data = array_chunk($data, self::$split);
						foreach($data as $subdata) {
							self::createXML($subdata, self::$settings['sitemap_filename'].'_'.$type.'.part'.(++$count));
							$xmldata[] = array('url' => $base.self::urlEncode((self::$settings['site_dir']?:'/').self::$settings['sitemap_filename'].'_'.$type.'.part'.$count.'.xml'));
						}
					} else {
						self::createXML($data, self::$settings['sitemap_filename'].'_'.$type);
						$xmldata[] = array('url' => $base.self::urlEncode((self::$settings['site_dir']?:'/').self::$settings['sitemap_filename'].'_'.$type.'.xml'));
					}
					while (file_exists(self::makeMapFilePath(self::$settings['sitemap_filename'].'_'.$type.'.part'.(++$count))))
						unlink(self::makeMapFilePath(self::$settings['sitemap_filename'].'_'.$type.'.part'.($count)));
				}

				foreach (\GetModuleEvents(self::$MODULE_ID, "OnBeforeSplitIndexGenerate", true) as $arEvent)
					\ExecuteModuleEventEx($arEvent, array(&$xmldata, &self::$settings['sitemap_filename']));

				self::createXML($xmldata, self::$settings['sitemap_filename'], true);
			} else
				self::createXML(self::checkForDouble(call_user_func_array('array_merge', array_values($tmp))), self::$settings['sitemap_filename']);

			if (self::$settings['aspro_sm'] == 'Y' && (int)self::$settings['aspro_sm_val'] && class_exists('\Aspro\Max\Smartseo\Engines\SitemapEngine')) {
				$asproSM = new \Aspro\Max\Smartseo\Engines\SitemapEngine((int)self::$settings['aspro_sm_val']);
				$asproSM->update();
			}

			if (self::$settings['zverushki_sm'] == 'Y' && class_exists('\Zverushki\Seofilter\Agent')) {
				\Zverushki\Seofilter\Agent::generateMap($arMap['SITE_ID']);
			}

			SWSitemapList::setEntityLastRun((int)$arMap['ID']);
		}
	}

	static public function GenerateAgent($id = 0) {
		SWSitemapGenerate::Generate($id);
		return "SWSitemapGenerate::GenerateAgent(".$id.");";
	}
}
?>