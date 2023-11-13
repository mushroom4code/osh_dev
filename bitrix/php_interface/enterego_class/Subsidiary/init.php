<?php

require_once __DIR__ . '/conf.php';
if ($_SERVER['SCRIPT_NAME'] === '/bitrix/admin/public_file_edit.php') {
    return;
}

if (defined('SUBSIDIARY_ENABLE') && SUBSIDIARY_ENABLE && !defined('SITE_ID'))
{
    session_start();
    $_siteId = $_SESSION['subsidiary'] ?? 'N2';
    if (!empty($_siteId && in_array($_siteId, SUBSIDIARY_SITE_LIST))) {
        define('SITE_ID', $_siteId);
    }
}