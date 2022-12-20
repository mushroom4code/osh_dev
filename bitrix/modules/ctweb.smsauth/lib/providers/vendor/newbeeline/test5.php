<?php

	header("Content-Type: text/xml; charset=UTF-8");
	
	require('QTSMS.class.php');
	require('test_config.php');

	$sms = new QTSMS($cfg['login'], $cfg['password'], $cfg['host']);
	
	// получение только новых входящих смс для ящика 134
	// new_only, sib_num, date_from, date_to
	$r_xml = $sms->inbox_sms(1,134);
	
	// получение только новых входящих смс для ящика 134 c 10.01.2010 00:00:00  до 15.01.2010 00:00:00
	// $r_xml = $sms->inbox_sms(0,134,'10.01.2010 00:00:00','15.01.2010 00:00:00');
	
	echo $r_xml; // результат XML

?>