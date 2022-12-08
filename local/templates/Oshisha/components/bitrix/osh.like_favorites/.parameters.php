<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

global $arComponentParameterLike;
CModule::AddAutoloadClasses("",
    array('DataBase_like' => "/bitrix/modules/osh.like_favorites/lib/DataBase_like.php")
);
/**
 * @var $USER CAllUser|CUser
 *
 */

$arComponentParameterLike = [
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "TEMPLATE_LIKE" => array(
            "PARENT" => "",
            "NAME" => "Шаблон для лайков",
            "TYPE" => "LIST",
            "VALUES" => array(),
        ),
        "MULTIPLE" => "Y",
        "CACHE_TIME" => array(),
        'ID' => '#ELEMENT_ID',
        'F_USER_ID' => '#FUSER_ID',
        'LOOK_LIKE' => 'true',
        'LOOK_FAVORITE' => 'true',
    ),
];
