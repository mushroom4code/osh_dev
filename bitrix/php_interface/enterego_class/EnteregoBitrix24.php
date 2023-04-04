<?php

namespace Enterego;

class EnteregoBitrix24
{
    private static function sendQuery($method, $queryData)
    {
        $curl = curl_init();
        $queryUrl = CRM_API_ENDPOINT . $method . '.json';

        curl_setopt_array(
            $curl,
            array(
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POST => true,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $queryUrl,
                CURLOPT_POSTFIELDS => http_build_query($queryData),
            ));

        $result = json_decode(curl_exec($curl), true);
        curl_close($curl);

        return $result;
    }

    public static function sendToBitrix24(&$arFields)
    {
        if ($arFields['RESULT'] && CRM_API_ENDPOINT &&
            in_array($arFields['IBLOCK_ID'], [IBLOCK_FEEDBACK_ID, IBLOCK_CALLBACK_ID, IBLOCK_NEW_SITE_COMMENTS])) {

            // check if the contact exists
            $method = 'crm.contact.list';
            $query = [
                "order" => [],
                "filter" => [
                    'PHONE' => $arFields['PROPERTY_VALUES']['PHONE']
                ],
                "select" => ["*"]
            ];
            $checkedContact = self::sendQuery($method, $query);

            // create new contact if not exits
            if ($checkedContact['total'] < 1) {
                // create new contact;
                $method = 'crm.contact.add';
                $query = [
                    'fields' => [
                        "NAME" => $arFields['NAME'],
                        "OPENED" => "Y",
                        "TYPE_ID" => "CLIENT",
                        "SOURCE_ID" => "WEBFORM",
                        'ASSIGNED_BY_ID' => DEFAULT_ASSIGNED_USER,
                        'EXPORT' => 'Y',
                        "PHONE" => [["VALUE" => $arFields['PROPERTY_VALUES']['PHONE'], "VALUE_TYPE" => "WORK"]],
                        'HAS_PHONE' => $arFields['PROPERTY_VALUES']['PHONE'] ? 'Y' : 'N',
                        "EMAIL" => [["VALUE" => $arFields['PROPERTY_VALUES']['EMAIL'], "VALUE_TYPE" => "WORK"]],
                        "HAS_EMAIL" => $arFields['PROPERTY_VALUES']['EMAIL'] ? 'Y' : 'N',

                    ],
                    'params' => [
                        "REGISTER_SONET_EVENT" => "Y"
                    ]
                ];

                $client = self::sendQuery($method, $query);
                $clientId = $client['result'];
                $clientAssigned = DEFAULT_ASSIGNED_USER;
            } else {
                // get exist clientID
                $clientId = $checkedContact['result'][0]['ID'];
                $clientAssigned = $checkedContact['result'][0]['ASSIGNED_BY_ID'];
            }


            // create deal
            $dealTitle = B24_MESSAGE_TITLES[$arFields['IBLOCK_ID']];

            if ($arFields['PROPERTY_VALUES']["FILES"]) {
                $userFiles = $arFields['PROPERTY_VALUES']["FILES"];
            }
            if ($arFields['PROPERTY_VALUES']["USER_FILES"]) {
                $userFiles = $arFields['PROPERTY_VALUES']["USER_FILES"];
            }

            $files = [];
            foreach ($userFiles as $i => $prop) {
                $files[] = ["fileData" => [$prop['name'], base64_encode(file_get_contents($prop['tmp_name']))]];
            }

            $method = 'crm.deal.add';
            $query = [
                'fields' => [
                    "TITLE" => $dealTitle,
                    "TYPE_ID" => DEAL_TYPE_ID,
                    'IS_NEW' => 'Y',
                    "STAGE_ID" => DEAL_STAGE_STATUS_ID,
                    "CONTACT_ID" => $clientId,
                    "OPENED" => "Y",
                    "ASSIGNED_BY_ID" => $clientAssigned,
                    "OPPORTUNITY" => 0,
                    "CATEGORY_ID" => DEAL_FEEDBACK_CATEGORY,
                    ADDFIELD_ID_USERMESSAGE => $arFields['DETAIL_TEXT'],
                    ADDFIELD_ID_USERFILES => $files
                ],
                'parameters' => [
                    'REGISTER_SONET_EVENT' => 'Y'
                ]
            ];

            self::sendQuery($method, $query);
        }
    }

}