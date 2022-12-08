<?php

namespace Ipol\Fivepost\Bitrix\Adapter;


use Ipol\Fivepost\Bitrix\Entity\Options;

class Receiver
{
    protected $coreReceiver;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreReceiver = new \Ipol\Fivepost\Core\Order\Receiver();
        $this->options      = $options;
    }

    public function fromOrder($bId)
    {
        if(!\CModule::includeModule('sale'))
        {
            throw new \Exception('No sale-module');
        }

        $order = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderById($bId);
        if(!$order)
        {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach(array('fullName','email','phone') as $code)
        {
            $arConnector[$this->options->fetchOption($code)] = $code;
            $method = 'set'.ucfirst($code);
            $this->getCoreReceiver()->$method(false);
        }

        // $arProps = $order->loadPropertyCollection()->getArray();
        $arProps = $order->getPropertyCollection ()->getArray();

        foreach($arProps['properties'] as $property)
        {
            if(
                array_key_exists($property['CODE'],$arConnector) &&
                $arConnector[$property['CODE']]                  &&
                $value = array_pop($property['VALUE'])
            )
            {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                $this->getCoreReceiver()->$method($value);
            }
        }
    }

    public function fromArray($array)
    {
        $arPossFields = array('fullName','email','phone');
        foreach($array as $key => $value){
            if(in_array($key,$arPossFields)) {
                $action = 'set' . ucfirst($key);
                $this->getCoreReceiver()->$action($value);
            } else {
                $this->getCoreReceiver()->setField($key,$value);
            }
        }
        return $this;
    }

    /**
     * @return \Ipol\Fivepost\Core\Order\Receiver
     */
    public function getCoreReceiver()
    {
        return $this->coreReceiver;
    }
}