<?
namespace Ipol\Fivepost\Bitrix\Handler;

use \Ipol\Fivepost\ProfileHandler;

use \Bitrix\Main\Loader;
use \Bitrix\Main\SystemException;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Delivery\Services\Table;

/**
 * Class Deliveries
 * @package namespace Ipol\Fivepost\Bitrix\Handler
 */
class Deliveries
{
    /**
     * Get profile code by delivery handler id
     *
     * @param int $id delivery handler id
     * @return false|string profile code
     */
    public static function defineDelivery($id)
    {
        $handler = Table::getList(array('filter' => array('ID' => $id)))->fetch();
        foreach(ProfileHandler::getProfileClasses() as $profile => $className)
        {
            if (strpos($className,$handler['CLASS_NAME']) !== false)
                return $profile;
        }
        return false;
    }

    /**
     * Get profile code by delivery handler class name
     *
     * @param string $class @see ProfileHandler::getProfileClasses()
     * @return false|string profile code
     */
    public static function defineProfileByClass($class)
    {
        foreach(ProfileHandler::getProfileClasses() as $profile => $className)
        {
            if ($class === $className)
                return $profile;
        }
        return false;
    }

    /**
     * Checks if at least one active delivery profile exists
     *
     * @return bool
     */
    public static function isActive()
    {
        return (!empty(self::getActualProfiles(true)));
    }

    /**
     * Checks is any shipment in order with module delivery handler used
     *
     * @param $orderId
     * @return bool
     */
    public static function is5PostDelivery($orderId)
    {
        $order = Order::getOrderById($orderId);
        /** @var Shipment $shipment */
        foreach ($order->getShipmentCollection() as $shipment) {
            if ($shipment->isSystem())
                continue;

            if (self::defineDelivery($shipment->getDeliveryId()))
                return true;
        }

        return false;
    }

    /**
     * Get actual delivery profiles data for all existing module delivery handlers
     *
     * @param bool $onlyActive get only active profiles
     * @return array of delivery profiles data
     */
    public static function getActualProfiles($onlyActive = true)
    {
        $result = array();

        if (!Loader::includeModule('sale'))
            return $result;

        $filter = array('%CLASS_NAME' => array_values(ProfileHandler::getProfileClasses()));
        if ($onlyActive)
            $filter['ACTIVE'] = 'Y';

        $handlerDB = Table::getList(array(
            'filter' => $filter,
            'select' => array('ID', 'CODE', 'PARENT_ID', 'ACTIVE', 'NAME', 'CLASS_NAME'),
        ));
        while ($tmp = $handlerDB->fetch())
            $result[$tmp['ID']] = $tmp;

        return $result;
    }

    /**
     * Get VAT rate by delivery handler id
     *
     * @param int $id delivery handler id
     * @return false|string VAT rate - false can be if no VAT rate stored in b_sale_delivery_srv for this handler or not existed handler id set
     */
    public static function getVatRateByDeliveryId($id)
    {
        try {
            $handler = Table::getList(array('filter' => array('ID' => (int)$id), 'select' => array('VAT_ID')))->fetch();

            if (is_array($handler) && isset($handler['VAT_ID']) && Loader::includeModule('catalog'))
            {
                $possibleVat = \Bitrix\Catalog\VatTable::getList(['filter' => ['ID' => $handler['VAT_ID']], 'select' => ['ID', 'NAME', 'RATE']])->fetch();
                if (is_array($possibleVat))
                {
                    return $possibleVat['RATE'];
                }
            }
        } catch (SystemException $e) {
            // Catch unknown field definition `VAT_ID` on 17-
        }

        return false;
    }

    /**
     * Get delivery profiles data for module profiles with unconfigured or default rate type
     *
     * @param bool $onlyActive get only active profiles
     * @return array of delivery profiles data
     */
    public static function getProfilesWithUnconfiguredRateType($onlyActive = true)
    {
        $result = array();

        if (!Loader::includeModule('sale'))
            return $result;

        $filter = array('%CLASS_NAME' => array_values(ProfileHandler::getProfileClasses()));
        if ($onlyActive)
            $filter['ACTIVE'] = 'Y';

        $handlerDB = Table::getList(array(
            'filter' => $filter,
            'select' => array('ID', 'CODE', 'PARENT_ID', 'ACTIVE', 'NAME', 'CLASS_NAME', 'CONFIG'),
        ));
        while ($tmp = $handlerDB->fetch())
        {
            $config = (empty($tmp['CONFIG']) ? array() : $tmp['CONFIG']);
            if (is_array($config['MAIN']) && array_key_exists('RATE_TYPE', $config['MAIN']) &&
                !in_array($config['MAIN']['RATE_TYPE'], array($tmp['CLASS_NAME']::RATE_TYPE_MIN_PRICE)))
            {
                continue;
            }

            $tmp['LINK'] = '/bitrix/admin/sale_delivery_service_edit.php?PARENT_ID='.$tmp['PARENT_ID'].'&ID='.$tmp['ID'].'&tabControl_active_tab=edit_MAIN';
            $result[$tmp['ID']] = $tmp;
        }

        return $result;
    }
}