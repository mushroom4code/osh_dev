<?php
namespace Ipol\Fivepost;

use Ipol\Fivepost\Bitrix\Controller\Printer;
use Ipol\Fivepost\Bitrix\Handler\Order;
use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Fivepost\Handler\BarcodeGeneratorCode128;
use Ipol\Fivepost\Fivepost\Handler\BarcodeGeneratorEAN;

use Bitrix\Main\Result;
use Bitrix\Main\Error;

IncludeModuleLangFile(__FILE__);

class BarcodeHandler extends AbstractGeneral
{
    /**
     * Request sticker file for AJAX call. Server-side generation.
     */
    public static function getStickerRequest()
    {
        if (!(isset($_REQUEST['bitrixId']) && $_REQUEST['bitrixId'])) {
            echo json_encode(array('success' => false, 'files' => false));
        } else {
            $result = self::getStickers($_REQUEST['bitrixId']);
            echo json_encode(array(
                'success' => $result->isSuccess(),
                'files'   => ($result->isSuccess() ? $result->getData()['FILES'] : []),
                /* At least one generated sticker = success, no matter how much not generated. ERRORS can answer about them */
                'errors'  => implode("\n\n", $result->isSuccess() ? $result->getData()['ERRORS'] : $result->getErrorMessages()),
            ));
        }
    }

    /**
     * Request sticker files for AJAX call. Mix of server-side and/or module-side generation allowed.
     */
    public static function getStickersRequest($request)
    {
        $resultByServer = null;
        $files = [];

        if (!isset($request['ids'])) {
            echo json_encode(array('success' => false, 'files' => false));
        } else {
            $ids = is_array($request['ids']) ? $request['ids'] : array($request['ids']);
            $bitrixIds = ['BY_SERVER' => [], 'BY_MODULE' => []];

            $dbOrders = OrdersTable::getList(array(
                'select' => ['BITRIX_ID', 'FIVEPOST_GUID', 'FIVEPOST_ID', 'BARK_GENERATE_BY_SERVER'],
                'filter' => ['ID' => $ids]
            ));
            while($tmp = $dbOrders->fetch()) {
                if ($tmp['BARK_GENERATE_BY_SERVER'] === 'Y' && !empty($tmp['FIVEPOST_GUID'])) {
                    $bitrixIds['BY_SERVER'][] = $tmp['BITRIX_ID'];
                } else if ($tmp['BARK_GENERATE_BY_SERVER'] !== 'Y' && !empty($tmp['FIVEPOST_ID'])) {
                    $bitrixIds['BY_MODULE'][] = $tmp['BITRIX_ID'];
                    $files[] = Tools::getJSPath()."ajax.php?".IPOL_FIVEPOST_LBL."action=printBKsRequest&bitrixId=".$tmp['BITRIX_ID'];
                }
            }

            if (!empty($bitrixIds['BY_SERVER'])) {
                $resultByServer = self::getStickers($bitrixIds['BY_SERVER']);
                if ($resultByServer->isSuccess()) {
                    foreach ($resultByServer->getData()['FILES'] as $url) {
                        $files[] = $url;
                    }
                }
            }

            // module-side gen for at least one order = success
            $success = !empty($bitrixIds['BY_MODULE']) || (empty($bitrixIds['BY_MODULE']) && $resultByServer->isSuccess());

            echo json_encode(array(
                'success' => $success,
                'files'   => ($success ? $files : []),
                /* At least one generated sticker = success, no matter how much not generated. ERRORS can answer about them */
                'errors'  => is_null($resultByServer) ? '' : implode("\n\n", $resultByServer->isSuccess() ? $resultByServer->getData()['ERRORS'] : $resultByServer->getErrorMessages()),
            ));
        }
    }

    /**
     * Get sticker files. Server-side generation.
     * @param string|string[] $bitrixId Bitrix order id or ids array
     * @return \Bitrix\Main\Result
     */
    public static function getStickers($bitrixId)
    {
        if (!is_array($bitrixId))
            $bitrixId = array($bitrixId);

        $guids = [];
        $dbOrders = OrdersTable::getList(array(
            'select' => ['BITRIX_ID', 'FIVEPOST_GUID'],
            'filter' => ['BITRIX_ID' => $bitrixId, '!FIVEPOST_GUID' => false, '=BARK_GENERATE_BY_SERVER' => 'Y']
            ));
        while($tmp = $dbOrders->fetch()) {
            $guids[] = $tmp['FIVEPOST_GUID'];
        }

        if (!empty($guids)) {
            $controller = new Printer();
            $result = $controller->getStickers($guids);
        } else {
            $result = new Result();
            $result->addError(new Error('Given orders has no 5Post UUID or not marked for server-side barcode generation.'));
        }

        return $result;
    }

    /**
     * Request sticker file for AJAX call. Multiple orders allowed. Module-side generation.
     */
    public static function printBKsRequestById()
    {
        $dbOrders = OrdersTable::getList(array('filter' => array('ID' => $_REQUEST['ids'])));
        $arOrders = array();
        while ($arOrder = $dbOrders->Fetch()) {
            if (!empty($arOrder['FIVEPOST_ID'])) {
                $arOrders[] = $arOrder['BITRIX_ID'];
            }
        }

        if (!empty($arOrders)) {
            self::printBKs($arOrders);
        }
    }

    /**
     * Request sticker file for AJAX call. Module-side generation.
     */
    public static function printBKsRequest()
    {
        $orders = $_REQUEST['bitrixId'];
        if (is_string($orders) && strpos($orders, ',')) {
            $orders = explode(',', $_REQUEST['bitrixId']);
        }
        self::printBKs($orders);
    }

    /**
     * Get sticker files for printing. Module-side generation.
     */
    public static function printBKs($orders, $template = '')
    {
        if (!is_array($orders) || !array_key_exists('orders', $orders))
            $arBKs = self::getBK($orders);
        else
            $arBKs = $orders;

        if (!$template)
            $template = $_SERVER['DOCUMENT_ROOT'].Tools::getToolsPath().'bkTemplate.php';

        if (!empty($arBKs['orders']))
            include $template;
    }

    /**
     * Makes data for module-side barcode generation
     */
    public static function getBK($arOrders)
    {
        if (!is_array($arOrders)) {
            $arOrders = array($arOrders);
        }

        $arResult = array(
            'shopName'    => Option::get('barkCompany'),
            'path'        => Tools::getJSPath().'ajax.php',
            'actionName'  => self::$MODULE_LBL.'action',
            'orders'      => array(),
            'logo5post'   => Tools::getImagePath().'5postlogo.png',
            'logoCompany' => false
        );

        if (Option::get('barkLogo')) {
            $arResult['logoCompany'] = \CFile::GetPath(Option::get('barkLogo'));
        }

        foreach ($arOrders as $bitrixId) {
            $arOrder = OrdersTable::getByBitrixId($bitrixId);
            if ($arOrder && !empty($arOrder)) {
                $arOrder['NUMBER'] = Order::getOrderNumber($bitrixId);
                $orderPVZ = PointsTable::getByPointGuid($arOrder['RECEIVER_LOCATION']);
                if ($orderPVZ) {
                    $arOrder['POINT_NAME']    = $orderPVZ['NAME'];
                    $arOrder['POINT_ADDRESS'] = $orderPVZ['FULL_ADDRESS'];
                }
                $arResult['orders'][] = $arOrder;
            }
        }

        foreach (array('orderId', 'pointId', 'pointAddr', 'seller', 'company', 'receiver', 'phone', 'hotline') as $lang) {
            $arResult['lang'][$lang] = Tools::getMessage('BK_'.$lang);
        }

        return $arResult;
    }

    /**
     * Returns barcode picture
     */
    public static function getBarcode($request = false)
    {
        if (empty($request)) {
            $request = $_REQUEST;
        }
        if (array_key_exists('oldformat', $_REQUEST) && $_REQUEST['oldformat']) {
            new BarcodeGeneratorEAN($request['barcode']);
        } else {
            new BarcodeGeneratorCode128($request['barcode']);
        }
    }

    /**
     * Unmake old files
     * @param string $prefix
     * @param int $lifetime in seconds
     */
    public static function unmakeOldFiles($prefix = '', $lifetime = 3600)
    {
        $path  = Printer::getFilePath();
        $files = scandir($path);
        $time  = time();

        foreach ($files as $file) {
            if (in_array($file, array(".", "..")))
                continue;

            if ($prefix && strpos($file, $prefix) === false)
                continue;

            $filePath = $path.$file;
            if (is_dir($filePath))
                continue;

            if ($time - filectime($filePath) > $lifetime)
                unlink($filePath);
        }
    }
}