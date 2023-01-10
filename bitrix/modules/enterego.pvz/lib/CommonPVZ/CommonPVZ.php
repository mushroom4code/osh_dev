<?php

namespace CommonPVZ;

require_once '__config_deliveries.php';

abstract class CommonPVZ
{
    protected $configs = [];
    protected $client = null;
    protected $delivery_name = null;

    public $errors = null;

    public function __construct()
    {
        global $CONFIG_DELIVERIES;

        if ($this->delivery_name !== null) {
            foreach ($CONFIG_DELIVERIES[$this->delivery_name] as $k => $v) {
                $this->configs[$k] = $v;
            }
        }

        $this->connect();
    }

    abstract protected function connect();
}