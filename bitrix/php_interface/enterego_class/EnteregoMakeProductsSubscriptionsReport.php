<?php
use Enterego\ProductsSubscriptionsTable;
use Shuchkin\SimpleXLSXGen;

function makeProductsSubscriptionsReport(): string
{

    if (date("d") == 26) {
        $res = ProductsSubscriptionsTable::getList();

        $resultArr = [];
        $resultArr[] = ['Название товара', 'Количество оформившихся подписок'];
        while($product = $res->fetch()) {
            $resultArr[] = [$product['PRODUCT_NAME'], $product['SUBSCRIPTION_CLICKS']];
        }

        $xlsx = SimpleXLSXGen::fromArray($resultArr);
        $xlsx->saveAs(dirname(__FILE__).'/productsSubscriptions.xlsx');
        $sendId = CEvent::Send('PRODUCTS_SUBSCRIPTIONS_REPORT', array('s1', 'N2'), array('MESSAGE' => 'test'),
            'N', '', array(dirname(__FILE__).'/productsSubscriptions.xlsx'));
    }

    return "makeProductsSubscriptionsReport;";
}