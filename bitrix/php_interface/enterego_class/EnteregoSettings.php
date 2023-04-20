<?php

namespace Enterego;

use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlQueryException;
use COption;

class EnteregoSettings
{
	public static function getPropSetting($catalog_id = 1, $prop_setting_name = '')
	{
		$connection = Application::getConnection();
		$resQuery = [];

		if (!empty($connection)) {
			try {
				$resQuery = $connection->query("SELECT ID,CODE,$prop_setting_name FROM b_iblock_property WHERE IBLOCK_ID=$catalog_id");
			} catch (SqlQueryException $e) {
			}
		}

		return $resQuery;
	}

	public static function updatePropSetting($catalog_id = 1, $see_popup = '', $setting_name = '', $id_prop = 1)
	{
		$connection = Application::getConnection();

		if (!empty($connection)) {
			try {
				$connection->query(
					"UPDATE  b_iblock_property SET $setting_name='$see_popup' WHERE IBLOCK_ID=$catalog_id AND ID = $id_prop");
			} catch (SqlQueryException $e) {
			}
		}

	}

	/** This method set sale type id for product && basket on date with checked
	 * @return void
	 */
	public static function getSalePriceOnCheckAndPeriod()
	{
		$check = COption::GetOptionString('activation_price_admin', 'USE_CUSTOM_SALE_PRICE');
		$dateOption = json_decode(COption::GetOptionString('activation_price_admin', 'PERIOD'));
		$bool_option_checked = false;

		if (!empty($dateOption->end) && !empty($dateOption->start)) {
			$start = strtotime(date($dateOption->start));
			$now = strtotime(date_format(date_create('now'), 'Y-m-dTH:s'));
			$end = strtotime(date($dateOption->end));
			if ($check === 'true' && ($start <= $now && $end > $now)) {
				$bool_option_checked = true;
			}
		}
		define("USE_CUSTOM_SALE_PRICE", $bool_option_checked);
	}


	public static function getDataPropOffers($paramForCategory = false, $idSection = false): array
	{
		$arData = [];
		if (!$paramForCategory && !$idSection) {
			$arData = [
				'VKUS' => [
					'CODE' => "VKUS",
					'TYPE' => 'colorWithText',
					'PREF' => '',
				],
				'GRAMMOVKA_G' => [
					'CODE' => "GRAMMOVKA_G",
					'TYPE' => 'text',
					'PREF' => 'гр.',
					'CATEGORY' => ['Кальянные смеси','Дисконт']
				],
				'TSVET' => [
					'CODE' => "TSVET",
					'TYPE' => 'color',
					'PREF' => '',
					'CATEGORY' => ['Кальяны', 'Комплектующие','Дисконт']
				],

				'SHTUK_V_UPAKOVKE' => [
					'CODE' => "SHTUK_V_UPAKOVKE",
					'TYPE' => 'text',
					'PREF' => 'шт.',
					'CATEGORY' => ['Уголь','Дисконт']
				],
			];
		}
		return $arData;
	}
}