<?php

namespace CommonPVZ;

class ProfilesAddresses
{
    public static function save($order) {
        try {
            $sas = 'ses';
            $orderFields = $order->getFields()->getValues();
            $propertyCollection = $order->getPropertyCollection();
            $address = false;
            foreach ($propertyCollection as $propertyItem) {
                $prop = $propertyItem->getProperty();
                if ($prop['IS_ADDRESS'] == 'Y')
                    $address = $propertyItem->getValue();
            }
            if (!empty($address)) {
                $arParams = ['filter'=>[
                    'PROFILE_ID'=>$orderFields['DELIVERY_ID'],
                    'USER_ID' => $orderFields['USER_ID'],
                    'ADDRESS' => $address
                    ]
                ];
                $res = ProfilesAddressesTable::getList($arParams);
                if (!$res->fetch()) {
                    $profilesAddresses = new ProfilesAddressesTable();
                    $result = $profilesAddresses->add([
                        'PROFILE_ID' => $orderFields['DELIVERY_ID'],
                        'USER_ID' => $orderFields['USER_ID'],
                        'ADDRESS' => $address,
                    ]);
                    if ($result->isSuccess())
                        return true;
                    else
                        return false;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function getAll($userId) {
        try {
            $savedProfilesRes = ProfilesAddressesTable::getList(['filter' => [
                'USER_ID' => $userId,
            ]
            ]);
            $savedProfiles = [];
            while ($savedProfile = $savedProfilesRes->fetch()) {
                $savedProfiles[] = $savedProfile;
            }
            return $savedProfiles;
        } catch (\Throwable $e) {
            return false;
        }
    }
}