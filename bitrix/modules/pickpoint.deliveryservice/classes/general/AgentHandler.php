<?php
namespace PickPoint\DeliveryService;

/**
 * Class AgentHandler
 * @package PickPoint\DeliveryService
 */
class AgentHandler extends AbstractGeneral
{
	/**
	 * Register module agent
     * @return mixed
     */	
    public static function addAgent($agent, $interval = 1800)
    {
        $result = null;
        if (method_exists('PickPoint\DeliveryService\AgentHandler', $agent) && $agent !== 'addAgent') {
            $result = \CAgent::AddAgent('\PickPoint\DeliveryService\AgentHandler::'.$agent.'();', self::getMID(), "N", $interval);
        }

        return $result;
    }

	/**
	 * Refresh order statuses agent
     * @return string
     */	
    public static function refreshStatuses()
    {
        StatusHandler::refreshOrderStates();		
        return '\PickPoint\DeliveryService\AgentHandler::refreshStatuses();';
    }  

	/**
	 * Delete old files agent
     * @return string
     */	
    public static function deleteOldFiles()
    {
		// Unmake old barcode files
        PrintHandler::unmakeOldFiles('barcode', 3600);

		// Unmake old registry files
        PrintHandler::unmakeOldFiles('registry', 86400*90);
		
        return '\PickPoint\DeliveryService\AgentHandler::deleteOldFiles();';
    } 

	/**
	 * Delete old data agent
     * @return string
     */	
    public static function deleteOldData()
    {
		// Unmake old registers
        RegistryHandler::unmakeOldRegisters();
		
		// Unmake old courier calls
        CourierHandler::unmakeOldCourierCalls();		
		
        return '\PickPoint\DeliveryService\AgentHandler::deleteOldData();';
    }
}