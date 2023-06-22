<?php

//namespace Enterego;

use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\PersonTypeTable;
use Bitrix\Sale\ShipmentCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
Loc::loadMessages(__FILE__);
class EnteregoSaleRestrictions extends Bitrix\Sale\Delivery\Restrictions\Base
{
    /**
     * @param $personTypeId
     * @param array $params
     * @param int $deliveryId
     * @return bool
     */
    public static function check($deliveryName, array $params, $deliveryId = 0)
    {
        if (array_search($deliveryName, $params['DELIVERY_TYPE']) !== false)
            return true;
        else
            return false;
    }
    /**
     * @param CollectableEntity $entity
     * @return int
     */
    public static function extractParams(CollectableEntity $entity)
    {
        return $entity->getOrder()->getPropertyCollection()->getItemByOrderPropertyCode('TYPE_DELIVERY')->getValue();
    }
    /**
     * @return mixed
     */
    public static function getClassTitle()
    {
        return 'Ограничение платежной системы в зависимости от выбранного типа доставки';
    }
    /**
     * @return mixed
     */
    public static function getClassDescription()
    {
        return 'Ограничение платежной системы в зависимости от выбранного типа доставки';
    }
    /**
     * @param $deliveryId
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public static function getParamsStructure($deliveryId = 0)
    {
        $personTypeList = array();
        $dbRes = \Bitrix\Main\GroupTable::getList();
        while ($personType = $dbRes->fetch())
            $personTypeList[$personType["ID"]] = $personType["NAME"]." (".$personType["ID"].")";
        return array(
            "DELIVERY_TYPE" => array(
                "TYPE" => "ENUM",
                'MULTIPLE' => 'Y',
                "LABEL" => 'Для какой доставки выводить возможность опаты наличными',
                "OPTIONS" => array('oshisha' => 'ошиша', 'sdek' => 'сдэк', 'RussianPost' => 'почта россии',
                    'FivePost' => '5post', 'Dellin' => 'деловые линии')
            )
        );
    }
    /**
     * @param $mode
     * @return int
     */
    public static function getSeverity($mode)
    {
        return \Bitrix\Sale\Delivery\Restrictions\Manager::SEVERITY_STRICT;
    }
}