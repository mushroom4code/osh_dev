<?php

namespace Enterego\PWA;

use Bitrix\Bizproc\BaseType\User;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
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
        $userAccept = false;
        $cordovaMobile = getallheaders()['X-Mobile-App'] ?? '';
        global $USER;

        $arFilter = array("ID" => $USER->GetID());
        $arParams["SELECT"] = array("UF_MOBILE_ACCEPT");
        $arRes = CUser::GetList('','', $arFilter, $arParams);
        if ($res = $arRes->Fetch()) {
            if($res["UF_MOBILE_ACCEPT"] == '1' || $res["UF_MOBILE_ACCEPT"] || $res["UF_MOBILE_ACCEPT"] == 'да'){
                $userAccept = true;
            }
        }

        if ($cordovaMobile !== 'Cordova' || empty($cordovaMobile) || $userAccept && $USER->IsAuthorized()) {
            $showContent = true;
        }

        return $showContent;
    }

    /**
     * @param int $user_id
     * @return bool
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function setDeactiveUserForCordova(int $user_id = 0): bool
    {
        $result = false;
        $cordovaMobile = getallheaders()['X-Mobile-App'] ?? '';
        if ($cordovaMobile === 'Cordova') {
            $user = new CUser;
            $fields = array(
                "EMAIL" => "user_id_$user_id@gmail.com",
                "LOGIN" => "user_id_$user_id@gmail.com",
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
        }

        return $result;
    }
}
