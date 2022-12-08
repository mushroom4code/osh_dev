<?php

namespace Bitrix\Sale\Exchange;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/general/order_loader.php');
use \Bitrix\Sale;
use CDataXML;
use CSaleExport;
use CSaleOrderProps;
use CSaleOrderUserProps;
use CSaleOrderUserPropsValue;
use CSalePersonType;
use CSaleUser;
use CUser;
use CXMLFileStream;

class EntCSaleOrderLoader extends \CSaleOrderLoader
{

    function nodeHandler(CDataXML $dataXml, CXMLFileStream $fileStream)
    {
        $value = $dataXml->GetArray();
        $xmlStream = $this->getXMLStream($fileStream);
        $importer = $this->importer;

        if($importer instanceof Sale\Exchange\ImportOneCBase)
        {
            $r = new Sale\Result();

            if($importer instanceof Sale\Exchange\ImportOneCSubordinateSale)
            {
                $documentData = array($value[GetMessage("CC_BSC1_DOCUMENT")]);
            }
            elseif($importer instanceof Sale\Exchange\ImportOneCPackage)
            {
                $documentData = $value[GetMessage("CC_BSC1_CONTAINER")]['#'][GetMessage("CC_BSC1_DOCUMENT")];
            }
            elseif($importer instanceof Sale\Exchange\EnteregoImport)
            {
                $documentData = array($value[GetMessage("CC_BSC1_COMPANY")]['#']);
            }
            elseif($importer instanceof Sale\Exchange\EnteregoUser)
            {
                $documentData = array($value[GetMessage("CC_BSC1_USER")]['#']);
            }
            else
            {
                $documentData = array($value[GetMessage("CC_BSC1_AGENT")]["#"]);
            }

            if(!is_array($documentData) || count($documentData)<=0)
                $r->addError(new \Bitrix\Main\Error(GetMessage("CC_BSC1_DOCUMENT_XML_EMPTY")));

            if($r->isSuccess())
            {
                /** @var Sale\Result $r */
                $r = $importer::checkSettings();
                if($r->isSuccess())
                {
                    if($xmlStream <> '')
                        $importer->setRawData($xmlStream);

                    $r = $importer->process($documentData);
                }
            }

            if(!$r->isSuccess())
            {
                foreach($r->getErrorMessages() as $errorMessages)
                {
                    if($errorMessages <> '')
                        $this->strError .= "\n".$errorMessages;
                }
            }

            if($r->hasWarnings())
            {
                if(count($r->getWarningMessages())>0)
                {
                    foreach($r->getWarningMessages() as $warningMessages)
                    {
                        if($warningMessages <> '')
                            $this->strError .= "\n".$warningMessages;
                    }
                }
            }

            \Bitrix\Main\Config\Option::set('sale', 'onec_exchange_type', 'container');
            \Bitrix\Main\Config\Option::set('sale', 'onec_exchange_last_time', time());
        }
        elseif(!empty($value[GetMessage("CC_BSC1_DOCUMENT")]))
        {
            //$this->nodeHandlerDefaultModuleOneC($dataXml);
        }
        elseif(\Bitrix\Main\Config\Option::get("sale", "1C_IMPORT_NEW_ORDERS", "Y") == "Y")
        {
            /**
             * @deprecated
             */
            $value = $value[GetMessage("CC_BSC1_AGENT")]["#"];
            $arAgentInfo = $this->collectAgentInfo($value);

            if(!empty($arAgentInfo["AGENT"]))
            {
                $mode = false;
                $arErrors = array();
                $dbUProp = CSaleOrderUserProps::GetList(array(), array("XML_ID" => $arAgentInfo["AGENT"]["ID"]),
                    false, false, array("ID", "NAME", "USER_ID", "PERSON_TYPE_ID", "XML_ID", "VERSION_1C"));
                if($arUProp = $dbUProp->Fetch())
                {
                    if($arUProp["VERSION_1C"] != $arAgentInfo["AGENT"]["VERSION"])
                    {
                        $mode = "update";
                        $arAgentInfo["PROFILE_ID"] = $arUProp["ID"];
                        $arAgentInfo["PERSON_TYPE_ID"] = $arUProp["PERSON_TYPE_ID"];
                        $arAgentInfo["USER_ID"] = $arUProp["USER_ID"];
                    }
                }
                else
                {
                    $user = Sale\Exchange\Entity\UserProfileImportLoader::getUserByCode($arAgentInfo["AGENT"]["ID"]);
                    if(!empty($user))
                    {
                        $arAgentInfo["USER_ID"] = $user['ID'];
                    }
                    else
                    {
                        $arUser = array(
                            "NAME" => $arAgentInfo["AGENT"]["ITEM_NAME"],
                            "EMAIL" => $arAgentInfo["AGENT"]["CONTACT"]["MAIL_NEW"],
                        );

                        if($arUser["NAME"] == '')
                            $arUser["NAME"] = $arAgentInfo["AGENT"]["CONTACT"]["CONTACT_PERSON"];

                        $emServer = $_SERVER["SERVER_NAME"];
                        if(mb_strpos($_SERVER["SERVER_NAME"], ".") === false)
                            $emServer .= ".bx";
                        if($arUser["EMAIL"] == '')
                            $arUser["EMAIL"] = "buyer".time().GetRandomCode(2)."@".$emServer;

                        $arAgentInfo["USER_ID"] = CSaleUser::DoAutoRegisterUser($arUser["EMAIL"], $arUser["NAME"],
                            $this->arParams["SITE_NEW_ORDERS"], $arErrors, array("XML_ID"=>$arAgentInfo["AGENT"]["ID"],
                                "EXTERNAL_AUTH_ID"=>Sale\Exchange\Entity\UserImportBase::EXTERNAL_AUTH_ID));
                    }

                    if(intval($arAgentInfo["USER_ID"]) > 0)
                    {
                        $mode = "add";

                        $obUser = new CUser;
                        $userFields[] = array();

                        if($arAgentInfo["AGENT"]["CONTACT"]["PHONE"] <> '')
                            $userFields["WORK_PHONE"] = $arAgentInfo["AGENT"]["CONTACT"]["PHONE"];

                        if(count($userFields)>0)
                        {
                            if(!$obUser->Update($arAgentInfo["USER_ID"], $userFields, true))
                                $this->strError .= "\n".$obUser->LAST_ERROR;
                        }
                    }
                    else
                    {
                        $this->strError .= "\n".GetMessage("CC_BSC1_AGENT_USER_PROBLEM", Array("#ID#" => $arAgentInfo["AGENT"]["ID"]));
                        if(!empty($arErrors))
                        {
                            foreach($arErrors as $v)
                            {
                                $this->strError .= "\n".$v["TEXT"];
                            }
                        }
                    }
                }

                if($mode)
                {
                    if(empty($arPersonTypesIDs))
                    {
                        $dbPT = CSalePersonType::GetList(array(), array("ACTIVE" => "Y", "LIDS" => $this->arParams["SITE_NEW_ORDERS"]));
                        while($arPT = $dbPT->Fetch())
                        {
                            $arPersonTypesIDs[] = $arPT["ID"];
                        }
                    }

                    if(empty($arExportInfo))
                    {
                        $dbExport = CSaleExport::GetList(array(), array("PERSON_TYPE_ID" => $arPersonTypesIDs));
                        while($arExport = $dbExport->Fetch())
                        {
                            $arExportInfo[$arExport["PERSON_TYPE_ID"]] = unserialize($arExport["VARS"]);
                        }
                    }

                    if(intval($arAgentInfo["PERSON_TYPE_ID"]) <= 0)
                    {
                        foreach($arExportInfo as $pt => $value)
                        {
                            if(($value["IS_FIZ"] == "Y" && $arAgentInfo["AGENT"]["TYPE"] == "FIZ")
                                || ($value["IS_FIZ"] == "N" && $arAgentInfo["AGENT"]["TYPE"] != "FIZ")
                            )
                                $arAgentInfo["PERSON_TYPE_ID"] = $pt;
                        }
                    }

                    if(intval($arAgentInfo["PERSON_TYPE_ID"]) > 0)
                    {
                        $arAgentInfo["ORDER_PROPS_VALUE"] = array();
                        $arAgentInfo["PROFILE_PROPS_VALUE"] = array();

                        $arAgent = $arExportInfo[$arAgentInfo["PERSON_TYPE_ID"]];

                        foreach($arAgent as $k => $v)
                        {
                            if($v["VALUE"] == '' || $v["TYPE"] != "PROPERTY")
                                unset($arAgent[$k]);
                        }

                        foreach($arAgent as $k => $v)
                        {
                            if(!empty($arAgentInfo["ORDER_PROPS"][$k]))
                                $arAgentInfo["ORDER_PROPS_VALUE"][$v["VALUE"]] = $arAgentInfo["ORDER_PROPS"][$k];
                        }

                        if (intval($arAgentInfo["PROFILE_ID"]) > 0)
                        {
                            CSaleOrderUserProps::Update($arUProp["ID"],
                                array("VERSION_1C" => $arAgentInfo["AGENT"]["VERSION"],
                                    "NAME" => $arAgentInfo["AGENT"]["AGENT_NAME"], "USER_ID" => $arAgentInfo["USER_ID"]));
                            $dbUPV = CSaleOrderUserPropsValue::GetList(array(),
                                array("USER_PROPS_ID" => $arAgentInfo["PROFILE_ID"]));
                            while($arUPV = $dbUPV->Fetch())
                            {
                                $arAgentInfo["PROFILE_PROPS_VALUE"][$arUPV["ORDER_PROPS_ID"]] =
                                    array("ID" => $arUPV["ID"], "VALUE" => $arUPV["VALUE"]);
                            }
                        }

                        if(empty($arOrderProps[$arAgentInfo["PERSON_TYPE_ID"]]))
                        {
                            $dbOrderProperties = CSaleOrderProps::GetList(
                                array("SORT" => "ASC"),
                                array(
                                    "PERSON_TYPE_ID" => $arAgentInfo["PERSON_TYPE_ID"],
                                    "ACTIVE" => "Y",
                                    "UTIL" => "N",
                                    "USER_PROPS" => "Y",
                                ),
                                false,
                                false,
                                array("ID", "TYPE", "NAME", "CODE", "USER_PROPS", "SORT", "MULTIPLE")
                            );
                            while ($arOrderProperties = $dbOrderProperties->Fetch())
                            {
                                $arOrderProps[$arAgentInfo["PERSON_TYPE_ID"]][] = $arOrderProperties;
                            }
                        }

                        foreach($arOrderProps[$arAgentInfo["PERSON_TYPE_ID"]] as $arOrderProperties)
                        {
                            $curVal = $arAgentInfo["ORDER_PROPS_VALUE"][$arOrderProperties["ID"]];

                            if ($curVal <> '')
                            {
                                if (intval($arAgentInfo["PROFILE_ID"]) <= 0)
                                {
                                    $arFields = array(
                                        "NAME" => $arAgentInfo["AGENT"]["AGENT_NAME"],
                                        "USER_ID" => $arAgentInfo["USER_ID"],
                                        "PERSON_TYPE_ID" => $arAgentInfo["PERSON_TYPE_ID"],
                                        "XML_ID" => $arAgentInfo["AGENT"]["ID"],
                                        "VERSION_1C" => $arAgentInfo["AGENT"]["VERSION"],
                                    );
                                    $arAgentInfo["PROFILE_ID"] = CSaleOrderUserProps::Add($arFields);
                                }
                                if(intval($arAgentInfo["PROFILE_ID"]) > 0)
                                {
                                    $arFields = array(
                                        "USER_PROPS_ID" => $arAgentInfo["PROFILE_ID"],
                                        "ORDER_PROPS_ID" => $arOrderProperties["ID"],
                                        "NAME" => $arOrderProperties["NAME"],
                                        "VALUE" => $curVal
                                    );
                                    if(empty($arAgentInfo["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]]))
                                    {
                                        CSaleOrderUserPropsValue::Add($arFields);
                                    }
                                    elseif($arAgentInfo["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]]["VALUE"] != $curVal)
                                    {
                                        CSaleOrderUserPropsValue::Update($arAgentInfo["PROFILE_PROPS_VALUE"][$arOrderProperties["ID"]]["ID"], $arFields);
                                    }
                                }
                            }
                        }
                    }
                    else
                    {
                        $this->strError .= "\n".GetMessage("CC_BSC1_AGENT_PERSON_TYPE_PROBLEM", Array("#ID#" => $arAgentInfo["AGENT"]["ID"]));
                    }
                }
            }
            else
            {
                $this->strError .= "\n".GetMessage("CC_BSC1_AGENT_NO_AGENT_ID");
            }
        };
    }

}