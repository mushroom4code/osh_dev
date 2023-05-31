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
                    $propertiesArr[] = ['ORDER_PROPS_ID' => $propertyValues['ORDER_PROPS_ID'],
                        'CODE'=>$propertyValues['CODE'], 'VALUE'=>$propertyValues['VALUE']];
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

                    $profilesAddressesResult = $profilesAddresses->add($profilesAddressesParams);
                    if ($profilesAddressesResult->isSuccess()) {
                        $profilesProperties = new ProfilesPropertiesTable();
                        foreach ($propertiesArr as $property) {
                            if ($isPvz) {
                                if ($property['CODE'] == 'ZIP' || $property['CODE'] == 'LATITUDE' || $property['CODE'] == 'LONGITUDE'
                                    || $property['CODE'] == 'TYPE_PVZ' || $property['CODE'] == 'DEFAULT_ADDRESS_PVZ'
                                    || $property['CODE'] == 'COMMON_PVZ' || $property['CODE'] == 'TYPE_DELIVERY'
                                    || $property['CODE'] == 'LOCATION') {
                                    $profilesPropertiesParams = [
                                        'SAVED_PROFILE_ID' => $profilesAddressesResult->getPrimary()['ID'],
                                        'PROPERTY_ID' => $property['ORDER_PROPS_ID'],
                                        'CODE' => $property['CODE'],
                                        'VALUE' => $property['VALUE']
                                    ];
                                    $result = $profilesProperties->add($profilesPropertiesParams);
                                }
                            } else {
                                if ($property['CODE'] == 'TYPE_DELIVERY' || $property['CODE'] == 'LOCATION' || $property['CODE'] == 'ZIP'
                                    || $property['CODE'] == 'STREET_KLADR' || $property['CODE'] == 'FIAS') {
                                    $profilesPropertiesParams = [
                                        'SAVED_PROFILE_ID' => $profilesAddressesResult->getPrimary()['ID'],
                                        'PROPERTY_ID' => $property['ORDER_PROPS_ID'],
                                        'CODE' => $property['CODE'],
                                        'VALUE' => $property['VALUE']
                                    ];
                                    $result = $profilesProperties->add($profilesPropertiesParams);
                                }
                            }
                        }

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