<?php

namespace CommonPVZ;

class SavedDeliveryProfiles
{
    public static function save($order) {
        try {
            $orderFields = $order->getFields()->getValues();
            $propertyCollection = $order->getPropertyCollection();
            $isPvz = !($orderFields['DELIVERY_ID'] == DoorDeliveryProfile::$doorDeliveryId);
            $address = false;
            $propertiesArr = [];
            foreach ($propertyCollection as $propertyItem) {
                $prop = $propertyItem->getProperty();
                if ($prop['IS_ADDRESS'] == 'Y'){
                    $address = $propertyItem->getValue();
                } else {
                    $propertyValues = $propertyItem->getFieldValues();
                    $propertiesArr[] = ['CODE'=>$propertyValues['CODE'], 'VALUE'=>$propertyValues['VALUE']];
                }
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
                    $profilesAddressesParams = [
                        'PROFILE_ID' => $orderFields['DELIVERY_ID'],
                        'USER_ID' => $orderFields['USER_ID'],
                        'ADDRESS' => $address,
                    ];
//                    if ($isPvz)
//                        $profilesAddressesParams['IS_PVZ'] = 'Y';

                    $profilesAddressesResult = $profilesAddresses->add($profilesAddressesParams);
                    if ($profilesAddressesResult->isSuccess()) {
                        $profilesProperties = new ProfilesPropertiesTable();
                        foreach ($propertiesArr as $property) {
                            $profilesPropertiesParams = [
                                'SAVED_PROFILE_ID' => $profilesAddressesResult->getPrimary()['ID'],
                                'CODE' => $property['CODE'],
                                'VALUE' => $property['VALUE']
                            ];
                           $result = $profilesProperties->add($profilesPropertiesParams);
                        }
//                    if ($isPvz)
//                        $profilesAddressesParams['IS_PVZ'] = 'Y';

                        return true;
                    } else {
                        return false;
                    }
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
                $savedProfilesPropertiesRes = ProfilesPropertiesTable::getList([
                    'filter'=>[
                        'SAVED_PROFILE_ID' => $savedProfile['ID']
                    ]
                ]);
                while ($savedProfileProperty = $savedProfilesPropertiesRes->fetch()) {
                    $savedProfile['PROPERTIES'][] = $savedProfileProperty;
                }
                $savedProfiles[] = $savedProfile;
            }
            return $savedProfiles;
        } catch (\Throwable $e) {
            return false;
        }
    }
}