<?php

namespace Ipol\Fivepost\Bitrix\Adapter;


use Ipol\Fivepost\Bitrix\Entity\Options;

abstract class Address
{
    protected $coreAddress;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreAddress = new \Ipol\Fivepost\Core\Order\Address();
        $this->options      = $options;
    }

    public function fromArray($array)
    {
        $arSetters = array('zip','country','region','city','line','comment');
        foreach($arSetters as $part)
        {
            if(array_key_exists($part,$array))
            {
                $method = 'set'.ucfirst($part);
                $this->getCoreAddress()->$method($array[$part]);
            }
        }

        return $this;
    }

    /**
     * @return \Ipol\Fivepost\Core\Order\Address
     */
    public function getCoreAddress()
    {
        return $this->coreAddress;
    }
}