<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
include GetLangFileName(dirname(__FILE__).'/', '/.description.php');
$psTitle = GetMessage('PP_PS_TITLE');
$psDescription = GetMessage('PP_PS_DESCRIPTION');

$data = array(
	'NAME' => GetMessage('PP_PS_TITLE'),
);
