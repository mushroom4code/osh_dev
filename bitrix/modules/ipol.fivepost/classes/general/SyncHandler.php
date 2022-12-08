<?
namespace Ipol\Fivepost;

use \Ipol\Fivepost\PointsHandler;
use \Ipol\Fivepost\LocationsHandler;
use \Ipol\Fivepost\Option;
use \Ipol\Fivepost\Bitrix\Tools;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

/**
 * Class SyncHandler - wrapper for sync routines
 * @package Ipol\Fivepost
 */
class SyncHandler extends AbstractGeneral
{
    /**
     * Sync points, rates, locations data
     *
     * @return \Bitrix\Main\Result
     */
    public static function syncServiceData()
    {
        $syncResult = new Result();

        $lastDate = Option::get('sync_data_lastdate');
        if (empty($lastDate))
            $lastDate = time();

        $currentStep = Option::get('sync_data_step');
        $pageSize = (int)Option::get('sync_data_pagesize');
        $pageNumber = (int)Option::get('sync_data_pagenumber');

        // Sync started
        if ($currentStep === 'SYNC_REFRESH_DATA' && $pageNumber == 0)
            Option::set('sync_data_lastdate', time());

        $total = 0;
        switch ($currentStep) {
            case 'SYNC_REFRESH_DATA':
                $result = PointsHandler::refreshPointsAndRates($pageSize, $pageNumber);
                $data = $result->getData();

                $total = $data['TOTAL_PAGES'];
                if ($data['IS_LAST']) {
                    $nextStep = 'SYNC_TOGGLE_INACTIVE_POINTS';
                    $pageNumber = 0;
                } else {
                    $nextStep = $currentStep;
                    $pageNumber = $data['NEXT_PAGE'];
                }
                break;

            case 'SYNC_TOGGLE_INACTIVE_POINTS':
                $result = PointsHandler::toggleInactivePoints($lastDate);

                $nextStep = 'SYNC_REFRESH_LOCATIONS';
                break;

            case 'SYNC_REFRESH_LOCATIONS':
                // Just skip loading errors if exist, we must have local copy after module installed
                $resultLoad = LocationsHandler::loadLocationsFile();
                $result = LocationsHandler::refreshLocations();

                $nextStep = 'SYNC_FINISH';
                break;

            default:
                $nextStep = 'SYNC_REFRESH_DATA';
                $pageNumber = 0;
                $result = new Result();

                Option::set('sync_data_completed', 'Y');
                break;
        }

        Option::set('sync_data_step', $nextStep);
        Option::set('sync_data_pagesize', $pageSize);
        Option::set('sync_data_pagenumber', $pageNumber);

        $syncResult->setData(['CURRENT_STEP' => $currentStep, 'NEXT_STEP' => $nextStep, 'NEXT_PAGE' => $pageNumber, 'TOTAL_PAGES' => $total]);
        foreach ($result->getErrorMessages() as $err)
            $syncResult->addError(new Error($err));

        return $syncResult;
    }

    /**
     * Make statistic about currently loaded points, locations
     * @return string
     */
    public static function makeStatistic()
    {
        $stat = '<p>'.Tools::getMessage('SYNC_STATISTIC_TITLE').'</p>';
        $stat .= '<ul>';

        $prStat = PointsHandler::makeStatistic()->getData();
        $stat .= '<li>'.Tools::getMessage('SYNC_STATISTIC_POINTS_LOADED').$prStat['POINTS_LOADED'].'</li>';

        //$stat .= '<li>'.Tools::getMessage('SYNC_STATISTIC_RATES_LOADED').''.'</li>';

        $locStat = LocationsHandler::makeStatistic()->getData();
        $stat .= '<li>'.Tools::getMessage('SYNC_STATISTIC_LOCATIONS_LOADED').$locStat['LOCATIONS_LOADED'].'</li>';

        $stat .= '</ul>';

        return $stat;
    }
}
