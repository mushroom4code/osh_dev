<?php

require_once __DIR__ . '/conf.php';

if (defined('SUBSIDIARY_ENABLE') && SUBSIDIARY_ENABLE && !defined('SITE_ID'))
{
    session_start();
    $siteId = $_SESSION['subsidiary'] ?? 'N2';
    if (!empty($siteId && in_array($siteId, SUBSIDIARY_SITE_LIST))) {
        define('SITE_ID', $siteId);
    }
}