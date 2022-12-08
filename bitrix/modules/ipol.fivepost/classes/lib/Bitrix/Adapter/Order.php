<?
namespace Ipol\Fivepost\Bitrix\Adapter;

use Ipol\Fivepost\Bitrix\Entity\Options;
use Ipol\Fivepost\Bitrix\Tools;
use Ipol\Fivepost\OrderHandler;
use Ipol\Fivepost\OrderPropsHandler;
use Ipol\Fivepost\OrdersTable;

class Order
{
    protected $bitrixId;
    protected $orderNumber;

    /**
     * @var Options
     */
    protected $options;

    /**
     * @var \Ipol\Fivepost\Core\Order\Order
     */
    protected $baseOrder;

    /**
     * @var Receiver
     */
    protected $receiver;

    /**
     * @var AddressTo
     */
    protected $addressTo;

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @var OrderGoods
     */
    protected $goods;

    /**
     * @var OrderItems
     */
    protected $items;

    protected $moduleLbl;

    public function __construct(Options $options)
    {
        $this->moduleLbl   = IPOL_FIVEPOST_LBL;
        $this->options     = $options;
        $this->baseOrder   = new \Ipol\Fivepost\Core\Order\Order();
        $this->receiver    = new Receiver($options);
        $this->addressTo   = new AddressTo($options);
        $this->payment     = new Payment($options);
        $this->goods       = new OrderGoods($options);
        $this->items       = new OrderItems($options);
    }

    /**
     * Fill new order with data from Bitrix order and default settings
     * @param $bitrixId
     * @return $this
     */
    public function newOrder($bitrixId)
    {
        $this->bitrixId    = $bitrixId;
        $this->orderNumber = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderNumber($bitrixId);

        $this->getBaseOrder()->setStatus('new');

        $this->getReceiver()->fromOrder($bitrixId);
        $this->getAddressTo()->fromOrder($bitrixId);

        $this->getPayment()->fromOrder($bitrixId);
        $this->getGoods()->fromOrder($bitrixId);
        $this->getItems()->fromOrder($bitrixId);

        $this->compileOrder();

        $this->setDefaultFields();

        $order = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderById($this->bitrixId);
        if ($order) {
            $arProps = $order->getPropertyCollection()->getArray();
            foreach ($arProps['properties'] as $property) {
                if (
                    $property['CODE'] == $this->moduleLbl.OrderPropsHandler::getPVZprop() &&
                    $value = array_pop($property['VALUE'])
                ) {
                    $this->getBaseOrder()->setField('receiverLocation', $value);
                }
            }
        }

        return $this;
    }

    /**
     * Link core entities with base order
     */
    protected function compileOrder()
    {
        $this->getBaseOrder()
            ->setNumber($this->getOrderNumber())
            ->addReciever($this->getReceiver()->getCoreReceiver())
            ->setAddressTo($this->getAddressTo()->getCoreAddress())
            ->setPayment($this->getPayment()->getCorePayment())
            ->setGoods($this->getGoods()->getCoreGoods())
            ->setItems($this->getItems()->getCoreItems());
    }

    /**
     * Fill default fields in base order using module options and some order data
     * ! Only for Order::newOrder()
     */
    protected function setDefaultFields()
    {
        // Barcode generation flag: server-side or module-side
        $barcodeGenerateByServer = $this->options->fetchBarkGenerateByServer() === 'Y';

        $this->getBaseOrder()
            ->setField('brandName', $this->options->fetchBrandName())
            ->setField('undeliverableOption', $this->options->fetchUndeliverableOption())
            ->setField('receiverLocation', false)
            ->setField('senderCreateDate', \Ipol\Fivepost\Bitrix\Handler\Order::getOrderDate($this->bitrixId, true))
            ->setField('barcode', $barcodeGenerateByServer ? null : OrderHandler::getOrderBarcode())
            ->setField('barcodeGenerateByServer', $barcodeGenerateByServer);
    }

    /**
     * Fill order data using $_REQUEST. Usually OrderSender form generates this request
     * @return $this
     */
    public function requestOrder()
    {
        // Deal with cp1251
        if (Tools::isModuleAjaxRequest()) {
            $_REQUEST = Tools::encodeFromUTF8($_REQUEST);
        }

        $this->bitrixId    = $_REQUEST['orderId'];
        $this->orderNumber = $_REQUEST['number'];

        $request = self::fromRequest();

        $this->getBaseOrder()->setNumber($this->orderNumber);

        $this->setArrayFields($request['order']);

        $this->getReceiver()->fromArray($request['receiver']);
        $this->getAddressTo()->fromArray($request['addressTo']);

        $this->getPayment()->fromArray($request['payment']);
        $this->getGoods()->fromArray($request['goods']);
        $this->getItems()->fromArray($request['items']);

        if ($this->getBaseOrder()->getField('barcodeGenerateByServer')) {
            $this->getBaseOrder()
                ->setField('barcode', null)
                ->setField('barcodes', null)                                     // Barcodes for cargoes
                ->setField('track', $this->getBaseOrder()->getNumber())          // Order tracking number
                ->setField('senderCargoId', $this->getBaseOrder()->getNumber()); // Unique partner id for cargo
        } else {
            $this->getBaseOrder()
                ->setField('barcodes', array($this->getBaseOrder()->getField('barcode')))
                ->setField('track', $this->getBaseOrder()->getField('barcode'))
                ->setField('senderCargoId', $this->getBaseOrder()->getField('barcode'));
        }

        $arDateCreate  = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderDate($this->bitrixId, true);
        $this->getBaseOrder()->setField('createDate', $arDateCreate['timestamp']);
        $this->getBaseOrder()->setField('createDateFP', $this::makeFivepostTimeFromTimestamp($arDateCreate['timestamp']));

        if (array_key_exists('plannedReceiveDate', $_REQUEST) && $_REQUEST['plannedReceiveDate']) {
            $this->getBaseOrder()->setField('plannedReceiveDate', $_REQUEST['plannedReceiveDate']);
            $this->getBaseOrder()->setField('plannedReceiveDateFP', $this::makeFivepostTimeFromTimestamp($_REQUEST['plannedReceiveDate']));
            $this->getBaseOrder()->setField('plannedReceiveDateDB', $this::makeDBTime($_REQUEST['plannedReceiveDate']));
        }
        if (array_key_exists('shipmentDate', $_REQUEST) && $_REQUEST['shipmentDate']){
            $this->getBaseOrder()->setField('shipmentDate', $_REQUEST['shipmentDate']);
            $this->getBaseOrder()->setField('shipmentDateFP', $this::makeFivepostTimeFromTimestamp($_REQUEST['shipmentDate']));
            $this->getBaseOrder()->setField('shipmentDateDB', $this::makeDBTime($_REQUEST['shipmentDate']));
        }

        $this->compileOrder();

        return $this;
    }

    /**
     * Make structured order array using $_REQUEST data. Just linker.
     * @return array
     */
    protected static function fromRequest()
    {
        return array(
            'receiver'    => array(
                'fullName' => $_REQUEST['clientName'],
                'phone'    => $_REQUEST['clientPhone'],
                'email'    => $_REQUEST['clientEmail']
            ),
            'addressTo'   => array(
                'city'    => $_REQUEST['Recipient_City'],
            ),
            'payment'     => array(
                'goods'      => $_REQUEST['payment_sum'],
                'estimated'  => $_REQUEST['price'],
                'isBeznal'   => $_REQUEST['payment_isBeznal'],
                'delivery'   => $_REQUEST['deliveryCost'],
                // 'payed'      => $_REQUEST['payment_prepayment'],
                'type'       => $_REQUEST['paymentType'],
                // 'ndsDelivery' => $_REQUEST['payment_ndsDelivery'],
                'ndsDefault' => $_REQUEST['payment_ndsDefault'],
            ),
            'goods' => array(
                'length'    => $_REQUEST['length'],
                'width'     => $_REQUEST['width'],
                'height'    => $_REQUEST['height'],
                'weight'    => $_REQUEST['weight']
            ),
            'order' => array(
                'orderId'                 => $_REQUEST['orderId'],
                'barcode'                 => $_REQUEST['barcode'],
                'barcodeGenerateByServer' => (isset($_REQUEST['barcodeGenerateByServer']) && $_REQUEST['barcodeGenerateByServer']),
                'brandName'               => $_REQUEST['brandName'],

                'receiverLocation'        => $_REQUEST['receiverLocation'],
                'senderCreateDate'        => $_REQUEST['senderCreateDate'],
                'senderLocation'          => $_REQUEST['senderLocation'],
                'undeliverableOption'     => $_REQUEST['undeliverableOption'],

                'currency'                => $_REQUEST['currency'],
                'deliveryCostCurrency'    => $_REQUEST['deliveryCostCurrency'],
                'paymentCurrency'         => $_REQUEST['paymentCurrency'],
                'priceCurrency'           => $_REQUEST['priceCurrency'],
            ),
            'items' => $_REQUEST['items']
        );
    }

    /**
     * Fill base order fields using given array with data
     * @param $array
     */
    protected function setArrayFields($array)
    {
        foreach($array as $key => $val) {
            $this->getBaseOrder()->setField(lcfirst($key), (string)$val);
        }
    }

    /**
     * Fill uploaded order using data from OrdersTable (order must sent before)
     * @param int $bitrixId Bitrix order id
     * @param int $mode always 1, for order entity (2 for shipment entity, but not implemented)
     * @return $this
     */
    public function uploadedOrder($bitrixId)
    {
        $this->bitrixId = $bitrixId;

        $arOrder = OrdersTable::getByBitrixId($bitrixId);

        $this->setDefaultFields();

        $arDateCreate  = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderDate($this->bitrixId, true);

        $dbFields = $this->fromDB($arOrder);
        if ($arOrder) {
            $this->getBaseOrder()->setStatus($arOrder['STATUS']);
            $this->getBaseOrder()->setLink($arOrder['FIVEPOST_GUID']);

            $this->setArrayFields($dbFields['order']);
            $this->getReceiver()->fromArray($dbFields['receiver']);
            $this->getAddressTo()->fromOrder($this->bitrixId);
            $this->getPayment()->fromArray($dbFields['payment']);

            $this->getBaseOrder()
                ->setNumber(($this->getOrderNumber()) ? $this->getOrderNumber() : \Ipol\Fivepost\Bitrix\Handler\Order::getOrderNumber($bitrixId))
                ->addReciever($this->getReceiver()->getCoreReceiver())
                ->setAddressTo($this->getAddressTo()->getCoreAddress())
                ->setPayment($this->getPayment()->getCorePayment())
                ->setGoods($dbFields['goods'])
                ->setItems($dbFields['items'])
                ->setField('createDate', $this::makeFivepostTimeFromTimestamp($arDateCreate['timestamp']))
                ->setField('message', $arOrder['MESSAGE']);

            // Some cargo data, used only as info for orders sender form
            if (!empty($dbFields['cargoes'])) {
                $this->getBaseOrder()
                    ->setField('cargoesCargoId', $dbFields['cargoes']['cargoId'])
                    ->setField('cargoesSenderCargoId', $dbFields['cargoes']['senderCargoId'])
                    ->setField('cargoesBarcode', $dbFields['cargoes']['barcode']);
            }

            if (array_key_exists('PLANNED_RECEIVE_DATE', $arOrder) && $arOrder['PLANNED_RECEIVE_DATE']) {
                $this->getBaseOrder()->setField('plannedReceiveDate', $arOrder['PLANNED_RECEIVE_DATE']->toString());
            }
            if (array_key_exists('SHIPMENT_DATE', $arOrder) && $arOrder['SHIPMENT_DATE']) {
                $this->getBaseOrder()->setField('shipmentDate', $arOrder['SHIPMENT_DATE']->toString());
            }
        }

        return $this;
    }

    /**
     * Rebuild given OrdersTable data row to structured array
     * @param array $dataRow row data
     * @return array
     */
    protected function fromDB($dataRow)
    {
        $cargoesData = unserialize($dataRow['CARGOES']);
        return array(
            'receiver'    => array(
                'fullName' => $dataRow['CLIENT_NAME'],
                'phone'    => $dataRow['CLIENT_PHONE'],
                'email'    => $dataRow['CLIENT_EMAIL']
            ),
            'payment'     => array(
                'goods'       => $dataRow['PAYMENT_VALUE'],
                'estimated'   => $dataRow['PRICE'],
                'isBeznal'    => ($dataRow['PAYMENT_TYPE'] === 'Bill'),
                'delivery'    => $dataRow['DELIVERY_COST'],
                // 'payed'      => $_REQUEST['payment_prepayment'],
                'type'        => $dataRow['PAYMENT_TYPE'],
                // 'ndsDelivery' => $_REQUEST['payment_ndsDelivery'],
                'ndsDefault'  => $dataRow['payment_ndsDefault'],
            ),
            'goods' => $cargoesData['goods'],
            'items' => $cargoesData['items'],
            'cargoes' => (array_key_exists('cargoes', $cargoesData) && is_array($cargoesData['cargoes']) ? $cargoesData['cargoes'] : []),
            'order' => array(
                'orderId'                   => $dataRow['BITRIX_ID'],
                'barcode'                   => ($dataRow['FIVEPOST_ID'] ? $dataRow['FIVEPOST_ID'] : null),
                'barcodeGenerateByServer'   => ($dataRow['BARK_GENERATE_BY_SERVER'] === 'Y'),

                'fivepost_status'           => $dataRow['FIVEPOST_STATUS'],
                'fivepost_execution_status' => $dataRow['FIVEPOST_EXECUTION_STATUS'],

                'brandName'                 => $dataRow['BRAND_NAME'],
                'receiverLocation'          => $dataRow['RECEIVER_LOCATION'],
                'senderLocation'            => $dataRow['SENDER_LOCATION'],
                'undeliverableOption'       => $dataRow['UNDELIVERABLE_OPTION'],

                'currency'                  => $dataRow['CURRENCY'],
                'deliveryCostCurrency'      => $dataRow['DELIVERY_COST_CURRENCY'],
                'paymentCurrency'           => $dataRow['PAYMENT_CURRENCY'],
                'priceCurrency'             => $dataRow['PRICE_CURRENCY'],
            ),
        );
    }

    /**
     * Fill uploaded order using data from OrdersTable
     * @param string $id
     * @return Order
     * @throws \Exception
     */
    public function uploadedOrderBy5I($id)
    {
        $obOrder = OrdersTable::getByFivepostId($id);
        if ($obOrder) {
            return $this->uploadedOrder($obOrder['BITRIX_ID']);
        } else {
            throw new \Exception('No order with 5Post ID '.$id);
        }
    }

    /**
     * Mebiys pretty checkboxes checker
     * @param $code
     * @return bool
     */
    protected function checkBoolOption($code)
    {
        $method = 'get'.ucfirst($code);
        return ($this->options->$method() === 'Y');
    }

    protected static function makeFivepostTimeFromTimestamp($timeStamp)
    {
        $strDateCreate = new \DateTime('now', new \DateTimeZone('UTC'));
        $strDateCreate->setTimestamp($timeStamp);
        return str_replace('+00:00', '.000Z', $strDateCreate->format('c'));
    }

    protected static function makeDBTime($timeStamp)
    {
        $obDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($timeStamp);
        return $obDate;
    }

    /**
     * @return mixed
     */
    public function getBitrixId()
    {
        return $this->bitrixId;
    }

    /**
     * @return OrderItems
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param OrderItems $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return \Ipol\Fivepost\Core\Order\Order
     */
    public function getBaseOrder()
    {
        return $this->baseOrder;
    }

    /**
     * @return Receiver
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param Receiver $receiver
     * @return $this
     */
    public function setReceiver(Receiver $receiver)
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return AddressTo
     */
    public function getAddressTo()
    {
        return $this->addressTo;
    }

    /**
     * @param AddressTo $addressTo
     * @return $this
     */
    public function setAddressTo(AddressTo $addressTo)
    {
        $this->addressTo = $addressTo;
        return $this;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     * @return $this
     */
    public function setPayment(Payment $payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return OrderGoods
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param OrderGoods $obGoods
     * @return $this
     */
    public function setGoods($obGoods)
    {
        $this->goods = $obGoods;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }
}