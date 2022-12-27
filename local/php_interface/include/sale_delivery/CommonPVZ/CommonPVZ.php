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
        $this->connect();
    }

    abstract protected function connect();
}