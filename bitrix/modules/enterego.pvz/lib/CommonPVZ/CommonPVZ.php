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
        switch ($typeDelivery) {
            case 'SDEK':
            case 'СДЭК':
                return new SDEKDelivery();
            case 'FivePost':
            case '5Post':
                return new FivePostDelivery();
            case 'Oshisha':
                return new OshishaDelivery();
            case 'PEK':
            case 'ПЭК':
                return new PEKDelivery();
            case 'PickPoint':
                return new PickPointDelivery();
            case 'Почта России':
            case 'RussianPost':
                return new RussianPostDelivery();
            case 'RussianPostEms':
                return new RussianPostDelivery('RussianPostEms');
            case 'RussianPostFirstClass':
                return new RussianPostDelivery('RussianPostFirstClass');
            case 'RussianPostRegular':
                return new RussianPostDelivery('RussianPostRegular');
            case 'Dellin':
            case 'Деловые линии':
                return new DellinDelivery();
            default:
                return null;
        }
    }

    public static function getInstance($deliveryParams): array
    {
        return [];
    }
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
    abstract public function getPVZ(string $city_name, array &$result_array, int &$id_feature, string $code_city);

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    abstract public function getPrice($array);

    abstract public function getPriceDoorDelivery($params);
}