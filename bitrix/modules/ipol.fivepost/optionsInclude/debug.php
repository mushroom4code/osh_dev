<script>
    <?=$LABEL?>setups.addPage('debug',{
        clearLog : function (code) {
            $('#<?=$LABEL?>sublog_'+code).replaceWith('');
            this.self.ajax({
                data: {<?=$LABEL?>action: 'clearLog',src: code}
            })
        }
    });
</script>

<?
$arLogings = array('createOrder','warehouse','cancelOrderByNumber','getOrderStatus');
$arLogs = array();

foreach ($arLogings as $logCode){
    $hasLog = \Ipol\Fivepost\Admin\Logger::getLogInfo($logCode);
    if($hasLog){
        $arLogs [$logCode] = $hasLog;
    }
}

if(!empty($arLogs)){
    $strLog = '';
    foreach ($arLogs as $logCode => $logLink){
        $strLog .= '<div id="'.$LABEL.'sublog_'.$logCode.'" style="margin-bottom: 10px"><a href="'.\Ipol\Fivepost\Bitrix\Tools::getJSPath().'logs/'.$logCode.'.txt" target="_blank">'.
            \Ipol\Fivepost\Bitrix\Tools::getMessage('LABEL_LOG_'.$logCode).'</a>&nbsp
            <div onclick="'.$LABEL.'setups.getPage(\'debug\').clearLog(\''.$logCode.'\')" style="display: inline-block" class="'.$LABEL.'closer" title="'.\Ipol\Fivepost\Bitrix\Tools::getMessage('LABEL_KILLLOG').'"></div></div>';
    }

    Ipol\Fivepost\Bitrix\Tools::placeWarningLabel($strLog,\Ipol\Fivepost\Bitrix\Tools::getMessage('LABEL_haslog'));
}
?>

<?// logging?>
<?\Ipol\Fivepost\Bitrix\Tools::placeOptionBlock('logging');?>

<?// events?>
<?\Ipol\Fivepost\Bitrix\Tools::placeOptionBlock('events');?>
<?
$arEvents = array(
    'onCompabilityBefore' => \Ipol\Fivepost\Bitrix\Tools::getMessage('LABEL_onCompabilityBefore'),
    'onCalculate'         => \Ipol\Fivepost\Bitrix\Tools::getMessage('LABEL_onCalculate'),
);

foreach($arEvents as $code => $name){
    $arSubscribe = array();
    foreach(GetModuleEvents($module_id,$code,true) as $arEvent){
        $arSubscribe []= $arEvent['TO_NAME'];
    }
    if(!empty($arSubscribe)){
        ?>
        <tr class="subHeading"><td colspan="2" valign="top" align="center"><?=$name?></td></tr>
        <?
        foreach($arSubscribe as $path){?>
            <tr><td colspan='2'><?=$path?></td></tr>
        <?}
    }
}
?>

<?// constants?>
<?\Ipol\Fivepost\Bitrix\Tools::placeOptionBlock('constants');?>
<?
    $arConstants = array(
        \Ipol\Fivepost\Bitrix\Entity\Cache::getDeactCacheConst() => \Ipol\Fivepost\Bitrix\Tools::getMessage('LBL_NOCACHE')
    );

    foreach ($arConstants as $code => $lbl){
        \Ipol\Fivepost\Bitrix\Tools::placeOptionRow($lbl,\Ipol\Fivepost\Bitrix\Tools::getMessage('LBL_CONSET' . (defined($code) ? '' : 'NOT')));
    }
?>
