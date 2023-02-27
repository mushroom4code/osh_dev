<?php

namespace CommonPVZ;


abstract class CommonPVZ
{
    protected $configs = [];
    protected $client = null;
    protected $delivery_name = null;

    public $errors = null;

    /** Get object of delivery type
     * @param string $typeDelivery
     * @return FivePostDelivery|PEKDelivery|PickPointDelivery|SDEKDelivery|null
     */
    public static function getInstanceObject(string $typeDelivery) {
        switch ($typeDelivery) {
            case 'SDEK':
            case 'СДЭК':
                return new SDEKDelivery();
            case 'FivePost':
            case '5Post':
                return new FivePostDelivery();
            case 'PEK':
            case 'ПЭК':
                return new PEKDelivery();
            case 'PickPoint':
                return new PickPointDelivery();
            default:
                return null;
        }
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

    abstract public function getPVZ($city_name, &$result_array, &$id_feature, $code_city);

    /** Return calculate price delivery
     * @param $array
     * @return float|int|bool - false if error calculate
     */
    abstract public function getPrice($array);
}