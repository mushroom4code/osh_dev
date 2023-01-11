<?php
CModule::AddAutoloadClasses(
    "osh.like_favorites",
    array(
        "DataBase_like" => "/lib/DataBase_like.php",
        "\Bitrix\Like\ORM_like_favoritesTable" => "/lib/ORM_like_favoritesTable.php"
    )
);