<?php
$arClassesList = array(
    "CmcartUserFieldHtml" => "/classes/general/cmcartuserfieldhtml.php",
);

foreach ($arClassesList as $sClassName => $sClassFile)
    require_once(__DIR__ . $sClassFile);