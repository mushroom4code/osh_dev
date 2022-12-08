<?
namespace Ipol\Fivepost;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

use Ipol\Fivepost\Bitrix\Adapter\Cargo;
use Ipol\Fivepost\Bitrix\Adapter\Order;
use Ipol\Fivepost\Bitrix\Entity\BasicResponse;
use Ipol\Fivepost\Bitrix\Entity\DefaultGabarites;
use Ipol\Fivepost\Bitrix\Entity\Options;
use Ipol\Fivepost\Bitrix\Handler\GoodsPicker;
use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\Core\Entity\Money;

IncludeModuleLangFile(__FILE__);

class OrderHandler extends AbstractGeneral
{
    /**
     * @var \Ipol\Fivepost\Bitrix\Adapter\Order
     */
    public static $order;

    /**
     * @return \Ipol\Fivepost\Bitrix\Adapter\Order
     */
    public static function getOrder()
    {
        return self::$order;
    }

    protected static function initOrder()
    {
        $options = new Options();
        self::$order = new Order($options);
    }

    public static function loadCMSOrder($bitrixId)
    {
        self::initOrder();
        return self::getOrder()->newOrder($bitrixId)->getBaseOrder();
    }

    public static function loadUploadOrder($bitrixId, $mode = 1)
    {
        self::initOrder();
        return self::getOrder()->uploadedOrder($bitrixId)->getBaseOrder();
    }

    public static function loadUploadOrderBy5I($fivePostId)
    {
        self::initOrder();
        return self::getOrder()->uploadedOrderBy5I($fivePostId)->getBaseOrder();
    }

    public static function calculateOrder()
    {

    }

    /**
     * Send order data to API
     * @return BasicResponse
     */
    public static function sendOrder()
    {
        $result = new BasicResponse();

        self::initOrder();
        $obOrder = self::getOrder()->requestOrder()->getBaseOrder();

        $resultAdd = self::saveOrder($obOrder);

        $itemCollection = $obOrder->getItems();
        $itemCollection->reset();
        while ($obItem = $itemCollection->getNext()) {
            $price = new Money($obItem->getPrice()); // TODO: beat shit out of data types
            $obItem->setPrice($price);
        }

        if ($resultAdd[0]->isSuccess()) {
            if ($resultAdd[1] === 'new' && !$obOrder->getField('barcodeGenerateByServer')) {
                $oldVal = Option::get('barkCounter');
                Option::set('barkCounter', $oldVal + 1);
            }

            // Core to API date magic
            $obOrder->setField('createDate', $obOrder->getField('createDateFP'));
            if ($obOrder->getField('plannedReceiveDateFP')) {
                $obOrder->setField('plannedReceiveDate', $obOrder->getField('plannedReceiveDateFP'));
            }
            if ($obOrder->getField('shipmentDateFP')) {
                $obOrder->setField('shipmentDate', $obOrder->getField('shipmentDateFP'));
            }

            $controller = new \Ipol\Fivepost\Bitrix\Controller\Order(self::$MODULE_ID, self::$MODULE_LBL, $obOrder);
            $sendResult = $controller->send();
            if ($sendResult->isSuccess()) {
                /** @var \Ipol\Fivepost\Api\Entity\Response\OrdersMake $response */
                $response = $sendResult->getData()->getResponse();
                $contents = $response->getContentList();
                if ($contents->getQuantity()) {
                    $contents->reset();
                    while ($content = $contents->getNext()) {
                        if ($content->isCreated()) {
                            // Order created successfully
                            $orderUuid = $content->getOrderId();

                            $cargoData = [
                                'cargoId'       => '', // 5Post cargo UUID
                                'senderCargoId' => '',
                                'barcode'       => '', // 5Post unique cargo barcode
                            ];
                            $cargoes = $content->getCargoes();
                            if ($cargoes->getQuantity()) {
                                $cargoes->reset();
                                while ($cargo = $cargoes->getNext()) {
                                    $cargoData['cargoId']       = $cargo->getCargoId();
                                    $cargoData['senderCargoId'] = $cargo->getSenderCargoId();
                                    $cargoData['barcode']       = $cargo->getBarcode();
                                    // Cause only one cargo per order supported ATM
                                    break;
                                }
                            }

                            $result->setSuccess(true)->setData(['FIVEPOST_ORDER_UUID' => $orderUuid]);

                            self::markOrderSended($obOrder->getField('orderId'), $orderUuid, $cargoData);

                            if (Options::fetchOption('addTracking') == 'Y') {
                                \Ipol\Fivepost\Bitrix\Handler\Order::addTracking($obOrder->getField('orderId'), $obOrder->getField('track'));
                            }
                        } else {
                            // Some errors raised
                            $result->setSuccess(false);

                            $errors = $content->getErrors();
                            if ($errors->getQuantity()) {
                                $errors->reset();
                                while ($error = $errors->getNext()) {
                                    $tmp[] = '['.$error->getCode().'] '.$error->getMessage();
                                }
                                $result->setErrorText(implode(', ', $tmp));
                            } else {
                                $result->setErrorText('Error while trying to create order, but no error messages get from API');
                            }
                        }
                        // Cause only one order per request sent ATM
                        break;
                    }
                } else {
                    $result->setSuccess(false)->setErrorText('No data while getting response from API');
                }
            } else {
                $result->setSuccess(false)->setErrorText($sendResult->getErrorText());
            }
        } else {
            $arErrors = $resultAdd[0]->getErrors();
            $arReturnErrors = array();
            /** @var \Bitrix\Main\ORM\Fields\FieldError $arError */
            foreach ($arErrors as $arError) {
                $arReturnErrors[] = $arError->getMessage();
            }
            $result->setSuccess(false)->setErrorText(implode(',', $arReturnErrors));
        }

        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode(array(
                'success'      => $result->isSuccess(),
                'error'        => $result->getErrorText(),
                'fivepostUuid' => $result->isSuccess() ? $result->getData()['FIVEPOST_ORDER_UUID'] : false
            ));
        }

        return $result;
    }

    public static function getOrderBarcode()
    {
        $controller = new \Ipol\Fivepost\Bitrix\Controller\Order(self::$MODULE_ID, self::$MODULE_LBL);

        if (Tools::isModuleAjaxRequest() && $_REQUEST[self::$MODULE_LBL.'action'] === __METHOD__) {
            echo $controller->generateBarcode();
        } else {
            return $controller->generateBarcode();
        }

        return false;
    }

    /**
     * Erase order from orders table
     * @param $bitrixId
     * @return \Bitrix\Main\Result
     */
    public static function eraseOrder($bitrixId)
    {
        $eraseResult = new Result();

        if (!empty($bitrixId)) {
            $order = OrdersTable::getByBitrixId($bitrixId, ['ID', 'BITRIX_ID']);
            if (isset($order['ID']) && $order['ID']) {
                $deleteResult = OrdersTable::delete($order['ID']);
                if ($deleteResult->isSuccess())
                    $eraseResult->setData(['ERASED_BITRIX_ID' => $bitrixId]);
                else
                    $eraseResult->addError(new Error(implode(', ', $deleteResult->getErrorMessages())));
            } else {
                $eraseResult->addError(new Error('Erase failed cause no orders found by given Bitrix ID.'));
            }
        } else {
            $eraseResult->addError(new Error('Erase failed cause no Bitrix ID given.'));
        }

        return $eraseResult;
    }

    /**
     * Ajax wrapper for eraseOrder
     * @param $request
     */
    public static function eraseOrderAjaxBid($request)
    {
        $eraseResult = self::eraseOrder($request['bitrixId']);
        $arReturn = array('success' => $eraseResult->isSuccess());
        if (!$eraseResult->isSuccess()) {
            $arReturn['error'] = implode(', ', $eraseResult->getErrorMessages());
        }
        echo Tools::jsonEncode($arReturn);
    }

    /**
     * Unmake order from API
     * @param $bitrixId
     * @return BasicResponse
     */
    public static function deleteOrder($bitrixId)
    {
        if (is_array($bitrixId)) {
            $bitrixId = $bitrixId['bitrixId'];
        }

        $obReturn = new BasicResponse();
        $obOrder = self::loadUploadOrder($bitrixId);

        $controller = new \Ipol\Fivepost\Bitrix\Controller\Order(self::$MODULE_ID, self::$MODULE_LBL,$obOrder);
        $obResult = $controller->delete();
        if ($obResult->isSuccess()) {
            $obReturn->setSuccess(true);
            StatusHandler::checkStatusByBI($bitrixId);
        } else {
            $obReturn->setSuccess(false)->setErrorText($obResult->getErrorText());
        }

        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode(array(
                'success' => $obReturn->isSuccess(),
                'error'   => $obReturn->getErrorText()
            ));
        } else {
            return $obReturn;
        }
    }

    /**
     * Save order to DB
     * @param \Ipol\Fivepost\Core\Order\Order $obOrder
     * @return array (\Bitrix\Main\ORM\Data\AddResult, typeUpdate)
     */
    public static function saveOrder($obOrder)
    {
        $cargoesData = array(
            'items' => $obOrder->getItems(),
            'goods' => $obOrder->getGoods(),
            'cargoes' => [
                'cargoId'       => '', // 5Post cargo UUID
                'senderCargoId' => $obOrder->getField('senderCargoId'),
                'barcodes'      => $obOrder->getField('barcodes'),
                'barcode'       => '', // 5Post unique cargo barcode
            ],
        );

        $data = array(
            'BITRIX_ID'                 => $obOrder->getField('orderId'),
            'FIVEPOST_ID'               => $obOrder->getField('barcode'),
            'FIVEPOST_GUID'             => '',
            'BARK_GENERATE_BY_SERVER'   => ($obOrder->getField('barcodeGenerateByServer') ? 'Y' : 'N'),
            'STATUS'                    => 'new',
            'FIVEPOST_STATUS'           => '',
            'FIVEPOST_EXECUTION_STATUS' => '',
            'BRAND_NAME'                => $obOrder->getField('brandName'),

            'CLIENT_NAME'               => $obOrder->getReceivers()->getFirst()->getFullName(),
            'CLIENT_EMAIL'              => $obOrder->getReceivers()->getFirst()->getEmail(),
            'CLIENT_PHONE'              => $obOrder->getReceivers()->getFirst()->getPhone(),
            'PLANNED_RECEIVE_DATE'      => ($obOrder->getField('plannedReceiveDateDB') ? $obOrder->getField('plannedReceiveDateDB') : ''),
            'SHIPMENT_DATE'             => ($obOrder->getField('shipmentDateDB') ? $obOrder->getField('shipmentDateDB') : ''),
            'RECEIVER_LOCATION'         => $obOrder->getField('receiverLocation'),

            'SENDER_LOCATION'           => $obOrder->getField('senderLocation'),
            'UNDELIVERABLE_OPTION'      => $obOrder->getField('undeliverableOption'),

            'CARGOES'                   => serialize($cargoesData),

            //'CURRENCY'                  => '',
            'DELIVERY_COST'             => $obOrder->getPayment()->getDelivery()->getAmount(),
            'DELIVERY_COST_CURRENCY'    => $obOrder->getField('deliveryCostCurrency'),
            'PAYMENT_VALUE'             => $obOrder->getPayment()->getGoods()->getAmount(),
            'PAYMENT_TYPE'              => $obOrder->getPayment()->getType(),
            'PAYMENT_CURRENCY'          => $obOrder->getField('currency'),
            'PRICE'                     => $obOrder->getPayment()->getEstimated()->getAmount(),
            'PRICE_CURRENCY'            => $obOrder->getField('priceCurrency'),

            'UPTIME'                    => mktime()
        );

        $dbOrder = OrdersTable::getByBitrixId($obOrder->getField('orderId'));
        if ($dbOrder) {
            $dbResult = OrdersTable::update($dbOrder['ID'], $data);
            $type = 'update';
        } else {
            $dbResult = OrdersTable::add($data);
            $type = 'new';
        }

        return array($dbResult, $type);
    }

    /**
     * Mark existing order as successfully sent
     * @param int $bitrixId
     * @param string $fivepostUuid
     * @param array $cargoData
     */
    public static function markOrderSended($bitrixId, $fivepostUuid, $cargoData)
    {
        $dbOrder = OrdersTable::getByBitrixId($bitrixId);
        if ($dbOrder) {
            $cargoes = unserialize($dbOrder['CARGOES']);
            if (array_key_exists('cargoes', $cargoes)) {
                // This must be the same cargo, if not - some unknown error detected
                if ($cargoes['cargoes']['senderCargoId'] == $cargoData['senderCargoId']) {
                    $cargoes['cargoes']['cargoId'] = $cargoData['cargoId'];
                    $cargoes['cargoes']['barcode'] = $cargoData['barcode'];
                }
            } else {
                // Legacy reason
                $cargoes['cargoes'] = $cargoData;
            }

            OrdersTable::update($dbOrder['ID'], array(
                'OK'            => '1',
                'STATUS'        => 'ok',
                'FIVEPOST_GUID' => $fivepostUuid,
                'CARGOES'       => serialize($cargoes),
            ));
        }
    }

    // Some unused stuff
    // ------------------------------------------------------------------------------------------------------------------------

    public static function countCargoGabs($params)
    {
        $answer = array('success' => false);
        if (!$params['orderId']) {
            $answer['error'] = 'No order Id';
        } elseif(!count($params['items'])) {
            $answer['error'] = 'No items';
        } else {
            $arItems = GoodsPicker::fromArray($params['items'], $params['orderId']);
            $obCargo = new Cargo(new DefaultGabarites());
            $obCargo->set($arItems);

            $answer = array(
                'success'    => true,
                'weight'     => $obCargo->getCargo()->getWeight(),
                'dimensions' => $obCargo->getCargo()->getDimensions(),
                'cargo'      => $params['cargo']
            );
        }

        if ($params[self::$MODULE_LBL.'action'])
            echo Tools::jsonEncode($answer);

        return $answer;
    }

    public static function getOrderTarif($orderId)
    {
    }

    public static function getCityPVZ($city, $default = false)
    {
    }

    public static function getSavedPVZ($code)
    {
    }
}