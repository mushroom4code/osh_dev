<?php

namespace CommonPVZ;


abstract class CommonPVZ
{
    protected $configs = [];
    protected $client = null;
    public string $delivery_name = '';
    public string $delivery_code = 'common_delivery';

    public string $title = '';

    public $errors = null;

    /** Get object of delivery type
     * @param string $typeDelivery
     * @return FivePostDelivery|OshishaDelivery|PEKDelivery|PickPointDelivery|SDEKDelivery|RussianPostDelivery|null
     */
    public static function getInstanceObject(string $typeDelivery) {
//        TODO - заменить статичные строковые кейсы на вызов параметра статичного кода в классе как
//        TODO - OshishaDelivery::$code
        switch ($typeDelivery) {
            case SDEKDelivery::$code:
                return new SDEKDelivery();
            case FivePostDelivery::$code:
                return new FivePostDelivery();
            case OshishaDelivery::$code:
                return new OshishaDelivery();
            case PEKDelivery::$code:
                return new PEKDelivery();
            case PickPointDelivery::$code:
                return new PickPointDelivery();
            case RussianPostDelivery::$code:
                return new RussianPostDelivery();
            case RussianPostDelivery::$code_ems:
                return new RussianPostDelivery(RussianPostDelivery::$code_ems);
            case RussianPostDelivery::$code_first_class:
                return new RussianPostDelivery(RussianPostDelivery::$code_first_class);
            case RussianPostDelivery::$code_regular:
                return new RussianPostDelivery(RussianPostDelivery::$code_regular);
            case DellinDelivery::$code:
                return new DellinDelivery();
            default:
                return null;
        }
    }

    public static function getInstance($deliveryParams): array
    {
        return [];
    }

    abstract public static function getInstanceForPvz($deliveryParams) : array;

    abstract public static function getInstanceForDoor($deliveryParams) : array;

    public function __construct()
    {
        $CONFIG_DELIVERIES = DeliveryHelper::getConfigs();

        if ($this->delivery_name !== null) {
            foreach ($CONFIG_DELIVERIES[$this->delivery_name] as $k => $v) {
                $this->configs[$k] = $v;
            }
        }

        $this->connect();
    }

    abstract protected function connect();

    /**
     * @param $city_name string
     * @param $result_array array
     * @param $id_feature int
     * @param $code_city string
     * @return mixed
     */
    abstract public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city, array $packages, $dimensionsHash, $sumDimensions);

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    abstract public function getPrice($array);

    abstract public function getPriceDoorDelivery($params);
}