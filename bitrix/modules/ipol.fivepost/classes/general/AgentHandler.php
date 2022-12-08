<?
namespace Ipol\Fivepost;

/**
 * Class AgentHandler
 * @package Ipol\Fivepost
 */
class AgentHandler extends AbstractGeneral
{
    public static function addAgent($agent, $interval = 1800)
    {
        $result = null;
        if (method_exists('Ipol\Fivepost\AgentHandler', $agent) && $agent !== 'addAgent') {
            $result = \CAgent::AddAgent('\Ipol\Fivepost\AgentHandler::'.$agent.'();', self::$MODULE_ID, "N", $interval);
        }
        return $result;
    }

    /**
     * Refresh order statuses
     * @return string
     */
    public static function refreshStatuses()
    {
        StatusHandler::refreshOrderStates();
        return '\Ipol\Fivepost\AgentHandler::refreshStatuses();';
    }

    /**
     * Sync points, rates, locations data
     * @return string
     */
    public static function syncServiceData()
    {
        SyncHandler::syncServiceData();
        return '\Ipol\Fivepost\AgentHandler::syncServiceData();';
    }

    /**
     * Unmake old uploaded barcode files
     * One run per week recommended
     * @return string
     */
    public static function unmakeOldFiles()
    {
        // Unmake stickers
        BarcodeHandler::unmakeOldFiles('sticker', 604800);
        return '\Ipol\Fivepost\AgentHandler::unmakeOldFiles();';
    }
}