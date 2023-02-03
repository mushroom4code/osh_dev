<?php

namespace enterego;

use CUser;

class EnteregoUser
{

    public static function getFullName($arrayForm = false)
    {
        global $USER;
        $fullName = [
            'last' => $USER->GetLastName() ?? '',
            'first' => $USER->GetFirstName() ?? '',
            'second' => $USER->GetParam('SECOND_NAME') ?? ''
        ];

        return $arrayForm ? $fullName : implode(' ', $fullName);
    }

    public static function getMail()
    {
        global $USER;
        return $USER->GetEmail();
    }

    public static function getPhone()
    {
        global $USER;

        $id =$USER->GetID();
        if ($USER->IsAuthorized()) {
            $rsUser = CUser::GetByID($id);
            $arUser = $rsUser->Fetch();
        }

        return $arUser['PERSONAL_PHONE'] ?? false;
    }

    public static function isUserAuthorized()
    {
        global $USER;
        return $USER->IsAuthorized();
    }

    public static function getUserData()
    {
        $userData = [
            'NAME' => self::getFullName(),
            'PHONE' => self::getPhone(),
            'MAIL' => self::getMail()
        ];

        return $userData ?? false;
    }
}