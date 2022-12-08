<?php

namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Tools;

class AgentHandler extends abstractGeneral
{
    public static function addAgent($agent,$interval = 1800)
    {
        $result = null;
        if(
            method_exists('Ipolh\SDEK\AgentHandler',$agent) &&
            !in_array($agent,array('addAgent','getAgentList'))
        ){
            $result = \CAgent::AddAgent('\Ipolh\SDEK\AgentHandler::'.$agent.'();',self::$MODULE_ID,"N",$interval);
        }

        return $result;
    }

    public static function getAgentList()
    {
        $agents = array(
            Tools::getMessage('AGENT_UPDATELIST')  => array("sdekOption::agentUpdateList();", self::$MODULE_ID),
            Tools::getMessage('AGENT_ORDERSTATES') => array("sdekOption::agentOrderStates();",self::$MODULE_ID,"N",1800),
            Tools::getMessage('AGENT_ORDERCHECKS') => array("\\Ipolh\\SDEK\\AgentHandler::getSendedOrdersState();",self::$MODULE_ID,"N",1800)
        );

        return $agents;
    }

    public static function getSendedOrdersState()
    {
        \Ipolh\SDEK\StatusHandler::getSendedOrdersState();
        return '\Ipolh\SDEK\AgentHandler::getSendedOrdersState();';
    }


}