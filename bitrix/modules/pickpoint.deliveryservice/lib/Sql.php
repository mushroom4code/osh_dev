<?php
namespace PickPoint;

use Bitrix\Main\Application;

class Sql
{
    const ORDERS_TABLE_NAME = 'b_pp_order_postamat';

    /**
     * @param int $orderId
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function setCanceledInvoiceStatus($orderId)
    {
        $sQuery = "UPDATE `". self::ORDERS_TABLE_NAME ."` SET CANCELED = TRUE WHERE ORDER_ID=". $orderId;
        self::dbQuery($sQuery);
    }

    /**
     * @param int $orderId
     * @return array
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function getInvoiceData($orderId = 0)
    {
        $sQuery = "SELECT * FROM `" . self::ORDERS_TABLE_NAME ."` WHERE ORDER_ID=" . $orderId ;
        return self::dbQuery($sQuery)->fetch();
    }

    /**
     * @return array
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function getAllInvoices()
    {
        $invoices = [];

        $sQuery = "SELECT * FROM `" . self::ORDERS_TABLE_NAME ."` ORDER BY ID DESC";
        $dbQuery = self::dbQuery($sQuery);
        while ($invoiceItem = $dbQuery->fetch()){
            $invoices[] = $invoiceItem;
        }

        return $invoices;
    }

    /**
     * @param int $orderId
     * @param string $dataField
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function updateInvoice($orderId, $dataField)
    {
        $sQuery = "UPDATE `" . self::ORDERS_TABLE_NAME ."` SET ". $dataField ." WHERE ORDER_ID=" . $orderId ;
        self::dbQuery($sQuery);
    }

    /**
     * @param string $queryStr
     * @return \Bitrix\Main\DB\Result
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    private static function dbQuery($queryStr)
    {
        $connection = Application::getConnection();
        return $connection->query($queryStr);
    }

    /**
     * @param int $orderId
     * @param int $invoiceId
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function setOrderInvoice($orderId = 0, $invoiceId = 0)
    {
        if (intval($orderId) > 0 && intval($invoiceId) > 0) {
            $sQuery = "UPDATE `" . self::ORDERS_TABLE_NAME ."` SET PP_INVOICE_ID = ". $invoiceId ." WHERE ORDER_ID=" . $orderId;
            self::dbQuery($sQuery);
        }
    }

    /**
     * @param $orderId
     * @param $archiveStatus
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    protected static function updateInvoiceArchiveStatus($orderId, $archiveStatus)
    {
        $sQuery = "UPDATE `" . self::ORDERS_TABLE_NAME ."` SET ARCHIVE =". $archiveStatus ." WHERE ORDER_ID=" . $orderId ;
        self::dbQuery($sQuery);
    }
}