<?php

namespace Enterego\PWA;

use Bitrix\Bizproc\BaseType\User;
use Bitrix\Main\UserPhoneAuthTable;
use CUser;

class EnteregoMobileAppEvents
{
    /**
     * @return bool
     */
    public static function getUserRulesForContent(): bool
    {
        $showContent = false;
        $cordovaMobile = getallheaders()['X-Mobile-App'] ?? '';

        global $USER;
//        TODO - убрать после модерации
//        if (($cordovaMobile === 'Cordova' && $USER->IsAuthorized() && $USER->getLogin() !== 'appleTestUser') ||
//            $USER->IsAuthorized() && $USER->getLogin() !== 'appleTestUser' || empty($cordovaMobile) ) {
//            $showContent = true;
//        }

        if ($cordovaMobile !== 'Cordova' || empty($cordovaMobile)) {
            $showContent = true;
        }

        return $showContent;
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public static function setDeactiveUserForCordova(int $user_id = 0): bool
    {
        $result = false;
        $user = new CUser;
        $fields = array(
            "EMAIL" => "$user_id@gmail.com",
            "LOGIN" => "$user_id@gmail.com",
            "PHONE_NUMBER" => "",
            "PERSONAL_PHONE" => $user->getParam('PHONE_NUMBER'),
            "PERSONAL_NOTES" => $user->getParam('EMAIL'),
            "ACTIVE" => "N",
        );

        $phoneUser = UserPhoneAuthTable::getList(array('filter' => ['USER_ID' => $user_id]))->fetchCollection();

        foreach ($phoneUser as $item) {
            $item->delete();
        }

        $user->Update($user_id, $fields);
        if (empty($user->LAST_ERROR)) {
            $result = true;
        }
        return $result;
    }
}
