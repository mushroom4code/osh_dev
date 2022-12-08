<?php
namespace Ipol\Fivepost\Bitrix\Adapter;

use Ipol\Fivepost\Bitrix\Controller\LocationLinker;

class AddressTo extends Address
{
    public function fromOrder($bId)
    {
        $order = \Ipol\Fivepost\Bitrix\Handler\Order::getOrderById($bId);

        $locationTo = ($order->getPropertyCollection()->getDeliveryLocation()) ? $order->getPropertyCollection()->getDeliveryLocation()->getValue() : false;
        if ($locationTo) {
            $location = new \Ipol\Fivepost\Bitrix\Adapter\Location($locationTo);
            if ($location && $location->getCoreLocation()) {
                $this->getCoreAddress()
                    ->setCountry($location->getCoreLocation()->getCountry())
                    ->setRegion($location->getCoreLocation()->getRegion())
                    ->setCity($location->getCoreLocation()->getName());
            }
        }

        if (!$order) {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach (array('zip','line','street','house','flat') as $code) {
            $arConnector[$this->options->fetchOption($code)] = $code;
        }

        // $arProps = $order->loadPropertyCollection()->getArray();
        $arProps = $order->getPropertyCollection ()->getArray();

        foreach ($arProps['properties'] as $property) {
            if (array_key_exists($property['CODE'],$arConnector)) {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                if ($value = array_pop($property['VALUE'])) {
                    $this->getCoreAddress()->$method($value);
                }
            }
        }

        $this->getCoreAddress()->setComment($order->GetField('USER_DESCRIPTION'));

        $linker = new LocationLinker();
        $linker->tryLinkFromCmsSide($locationTo);
        if ($linker->getLocationLink() && $linker->getLocationLink()->getApi()->getCode()) {
            $this->getCoreAddress()->setCode($linker->getLocationLink()->getApi()->getCode());
        }

        return $this;
    }
}