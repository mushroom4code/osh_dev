<?php
namespace Ipolh\SDEK;

use \Ipolh\SDEK\Bitrix\Controller\pvzController;
use \Ipolh\SDEK\Legacy\transitApplication;

class PointsHandler extends abstractGeneral
{
    /**
     * Pvz request types
     */
    const REQUEST_TYPE_SDEK   = 'sdek';
    const REQUEST_TYPE_BACKUP = 'backup';

    public static function updatePoints($requestType = self::REQUEST_TYPE_SDEK, $forced = false)
    {
        $result = array('SUCCESS' => false, 'ERROR' => false);

        if ($accountId = \Ipolh\SDEK\option::get('logged'))
        {
            $account = \sqlSdekLogs::getById($accountId);
            if ($requestType == self::REQUEST_TYPE_BACKUP) {
                // - This is madness!
                // - This is REQUEST_TYPE_BACKUP CALL!
                $application = new transitApplication($account['ACCOUNT'], $account['SECURE']);
                $application->requestType = self::REQUEST_TYPE_BACKUP;
            } else {
                $application = self::makeApplication($account['ACCOUNT'], $account['SECURE']);

                if ($application instanceof \Ipolh\SDEK\SDEK\SdekApplication) {
                    $application
                        ->setTestMode(false)
                        ->setTimeout(30)
                        ->setCache(null)
                        ->setLogger(null)
                    ;
                }
            }

            $controller = new pvzController(false);
            $controller->setApplication($application);
            $refreshResult = $controller->refreshPoints($forced);

            if ($refreshResult->isSuccess()) {
                $result['SUCCESS'] = true;
            } else {
                $result['ERROR'] = implode(', ', $refreshResult->getErrorMessages());
            }
        } else {
            $result['ERROR'] = 'Successful module authorization required before call updatePoints method.';
        }

        return $result;
    }
}