<?php

namespace CommonPVZ;


abstract class CommonPVZ
{
    protected $configs = [];
    protected $client = null;
    protected $delivery_name = null;

    public $errors = null;

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

    abstract public function getPrice($array);
}