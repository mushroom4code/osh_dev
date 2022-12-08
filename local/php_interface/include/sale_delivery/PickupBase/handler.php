<?php

namespace Sale\Handlers\Delivery;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Sale\Location;
use Bitrix\Main\Error;
use CIBlockElement;
use CIBlockSection;

\Bitrix\Main\Loader::includeModule("sale");
Loc::loadMessages(__FILE__);

/*
 * @package Bitrix\Sale\Delivery\Services
 */
class PickupBaseHandler extends \Bitrix\Sale\Delivery\Services\Base
{
	protected static $isCalculatePriceImmediately = true;
	protected  static $whetherAdminExtraServicesShow = true;

	/**
	 * @param array $initParams
	 * @throws \Bitrix\Main\ArgumentTypeException
	 */
	public function __construct(array $initParams)
	{
		parent::__construct($initParams);

		//Default value
		if(!isset($this->config["MAIN"]["0"]))
			$this->config["MAIN"]["0"] = "0";
	}

	/**
	 * @return string Class title.
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_PDLVR_HANDL_CUSTOM_TITLE3");
	}

	/**
	 * @return string Class, service description.
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_PDLVR_HANDL_CUSTOM_DESCRIPTION");
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function getLocationGroups()
	{
/*		$result = array();
		$res = GroupTable::getList(array(
			'select' => array('ID', 'CODE', 'LNAME' => 'NAME.NAME'),
			'filter' => array('NAME.LANGUAGE_ID' => LANGUAGE_ID)
		));

		while($group = $res->fetch())
			$result[$group['ID']] = $group['LNAME'];
*/
		return $result;
	}

	/**
	 * @param \Bitrix\Sale\Shipment|null $shipment
	 * @return CalculationResult
	 * @throws \Bitrix\Main\ArgumentException
	 */
	protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
	{
		global $arSettings;
		
		$result = new \Bitrix\Sale\Delivery\CalculationResult();

        $order = $shipment->getCollection()->getOrder();

        $propertyCollection = $order->getPropertyCollection();


		//местоположение
        /*$somePropValue = $propertyCollection->getItemByOrderPropertyId(6);
        if ($somePropValue) {
            $Location = $somePropValue->getValue();
        }
        $somePropValue = $propertyCollection->getItemByOrderPropertyId(18);
        if ($somePropValue) {
            
        }*/

		$LOCATION = $propertyCollection->getDeliveryLocation()->getValue();
		
        $basket = $order->getBasket();
        $price = $basket->getPrice();
		$WEIGHT =  $basket->getWeight();

		
		/*
		if( $LOCATION > 0 )
		{
				$item = \Bitrix\Sale\Location\LocationTable::getByCode($LOCATION, array(
					'filter' => array('=NAME.LANGUAGE_ID' => LANGUAGE_ID),
					'select' => array('*', 'NAME_RU' => 'NAME.NAME')
				))->fetch();
	
			
			$LOCATION_ID = $item['ID'];
		

		
			
		}*/
		//$deliveryPrice = 
		//$ResultPrice = $this->config["MAIN"]["0"];

        /*if (isset($priceDelivery['price'], $priceDelivery['price_base']) && (float)$priceDelivery['price_base'] > 1) {
           $ResultPrice = $priceDelivery['price'];
        } else {
           return array(
                'RESULT' => 'ERROR',
                'TEXT' => '',
            );
			$ResultPrice = 0;
        }	*/	
		$ResultPrice = 0;
		
			//$description = '';
			$result->setPeriodDescription(0); //Срок доставки: 1-4 Дня
			$result->setDescription($description);
			$result->setDeliveryPrice(
				roundEx(
					$ResultPrice,
					SALE_VALUE_PRECISION
				)
			);
			



			return $result;		
		
	
		

	}

	/**
	 * @return array
	 * @throws \Exception
	 */
	protected function getConfigStructure()
	{
		$currency = $this->currency;

		if(Loader::includeModule('currency'))
		{
			$currencyList = CurrencyManager::getCurrencyList();
			if (isset($currencyList[$this->currency]))
				$currency = $currencyList[$this->currency];
			unset($currencyList);
		}

		$result = array(
			"MAIN" => array(
				"TITLE" => Loc::getMessage("SALE_DLVR_HANDL_CUSTOM_TAB_MAIN"),
				"DESCRIPTION" => Loc::getMessage("SALE_DLVR_HANDL_CUSTOM_TAB_MAIN_DESCR"),
				"ITEMS" => array(

					"CURRENCY" => array(
						"TYPE" => "DELIVERY_READ_ONLY",
						"NAME" => Loc::getMessage("SALE_DLVR_HANDL_CUSTOM_CURRENCY"),
						"VALUE" => $this->currency,
						"VALUE_VIEW" => $currency
					),

					0 => array(
						"TYPE" => "NUMBER",
						"MIN" => 0,
						"NAME" => Loc::getMessage("SALE_DLVR_HANDL_CUSTOM_DEFAULT")
					)
				)
			)
		);

		foreach(self::getLocationGroups() as $groupId => $groupName)
		{
			$result["MAIN"]["ITEMS"][$groupId] = array(
				"TYPE" => "NUMBER",
				"MIN" => 0,
				"NAME" => $groupName
			);
		}

		return $result;
	}

	public function isCalculatePriceImmediately()
	{
		return self::$isCalculatePriceImmediately;
	}

	public static function whetherAdminExtraServicesShow()
	{
		return self::$whetherAdminExtraServicesShow;
	}
}