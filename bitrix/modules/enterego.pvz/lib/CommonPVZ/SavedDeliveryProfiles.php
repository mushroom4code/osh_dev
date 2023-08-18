<?php

namespace CommonPVZ;

class SavedDeliveryProfiles
{
    public static function save($order) {
        try {
            $orderFields = $order->getFields()->getValues();
            $propertyCollection = $order->getPropertyCollection();
            $deliveriesList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
            if ($deliveriesList[$orderFields['DELIVERY_ID']]['CLASS_NAME'] == '\CommonPVZ\PVZDeliveryProfile')
                $isPvz = true;
            else
                $isPvz = false;

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
            $type_delivery = $propertyCollection->getItemByOrderPropertyCode('TYPE_DELIVERY')->getValue();
            $common_pvz = $propertyCollection->getItemByOrderPropertyCode('COMMON_PVZ')->getValue();
            $zip = $propertyCollection->getItemByOrderPropertyCode('ZIP')->getValue();

            if ($isPvz){
                if (!$common_pvz)
                    $address .= '; '.$zip;
                else
                    $address .= '; '.$common_pvz;
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
                                    || $property['CODE'] == 'TYPE_PVZ' || $property['CODE'] == 'COMMON_PVZ'
                                    || $property['CODE'] == 'TYPE_DELIVERY'|| $property['CODE'] == 'LOCATION'
                                    || $property['CODE'] == 'ADDRESS_PVZ') {
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