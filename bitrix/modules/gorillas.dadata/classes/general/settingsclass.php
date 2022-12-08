<?php

IncludeModuleLangFile(__FILE__);
class CDadataSuggestionsSettings
	{
		public static $module_id = 'gorillas.dadata';
		private static $suggestionsFields = array();

		public static function GetSuggestionsFields() {
			if (sizeof(self::$suggestionsFields) == 0){
				self::$suggestionsFields['EMAIL']['value'] = GetMessage("GORILLAS_SUGGESTIONS_EMAIL");
				self::$suggestionsFields['NAME']['value'] = GetMessage("GORILLAS_SUGGESTIONS_FIO_ODNOY_STROKOY");
				self::$suggestionsFields['NAME']['data.surname'] = GetMessage("GORILLAS_SUGGESTIONS_FAMILIA");
				self::$suggestionsFields['NAME']['data.name'] = GetMessage("GORILLAS_SUGGESTIONS_IMA");
				self::$suggestionsFields['NAME']['data.patronymic'] = GetMessage("GORILLAS_SUGGESTIONS_OTCESTVO");
				self::$suggestionsFields['NAME']['data.gender'] = GetMessage("GORILLAS_SUGGESTIONS_POL");
				self::$suggestionsFields['NAME']['data.qc'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_KACESTVA");
				self::$suggestionsFields['ADDRESS']['value'] = GetMessage("GORILLAS_SUGGESTIONS_ADRES_ODNOY_STROKOY");
				self::$suggestionsFields['ADDRESS']['data.postal_code'] = GetMessage("GORILLAS_SUGGESTIONS_INDEKS");
				self::$suggestionsFields['ADDRESS']['data.country'] = GetMessage("GORILLAS_SUGGESTIONS_STRANA");
				self::$suggestionsFields['ADDRESS']['data.region_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_SUBQEKTA_SOKRAS");
				self::$suggestionsFields['ADDRESS']['data.region_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_SUBQEKTA");
				self::$suggestionsFields['ADDRESS']['data.region'] = GetMessage("GORILLAS_SUGGESTIONS_SUBQEKT");
				self::$suggestionsFields['ADDRESS']['data.area_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_RAYONA_SOKRASEN");
				self::$suggestionsFields['ADDRESS']['data.area_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_RAYONA");
				self::$suggestionsFields['ADDRESS']['data.area'] = GetMessage("GORILLAS_SUGGESTIONS_RAYON");
				self::$suggestionsFields['ADDRESS']['data.city_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_GORODA_SOKRASEN");
				self::$suggestionsFields['ADDRESS']['data.city_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_GORODA");
				self::$suggestionsFields['ADDRESS']['data.city'] = GetMessage("GORILLAS_SUGGESTIONS_GOROD");
				self::$suggestionsFields['ADDRESS']['data.settlement_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_NASELENNOGO_PUNK");
				self::$suggestionsFields['ADDRESS']['data.settlement_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_NASELENNOGO_PUNK1");
				self::$suggestionsFields['ADDRESS']['data.settlement'] = GetMessage("GORILLAS_SUGGESTIONS_NASELENNYY_PUNKT");
				self::$suggestionsFields['ADDRESS']['data.street_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_ULICY_SOKRASENN");
				self::$suggestionsFields['ADDRESS']['data.street_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_ULICY");
				self::$suggestionsFields['ADDRESS']['data.street'] = GetMessage("GORILLAS_SUGGESTIONS_ULICA");
				self::$suggestionsFields['ADDRESS']['data.house_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_DOMA_SOKRASENNY");
				self::$suggestionsFields['ADDRESS']['data.house_type_full'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_DOMA");
				self::$suggestionsFields['ADDRESS']['data.house'] = GetMessage("GORILLAS_SUGGESTIONS_DOM");
				self::$suggestionsFields['ADDRESS']['data.block_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_RASSIRENIA_DOMA");
				self::$suggestionsFields['ADDRESS']['data.block'] = GetMessage("GORILLAS_SUGGESTIONS_RASSIRENIE_DOMA");
				self::$suggestionsFields['ADDRESS']['data.flat_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_KVARTIRY_KVARTI");
				self::$suggestionsFields['ADDRESS']['data.flat'] = GetMessage("GORILLAS_SUGGESTIONS_NOMER_KVARTIRY");
				self::$suggestionsFields['ADDRESS']['data.postal_box'] = GetMessage("GORILLAS_SUGGESTIONS_ABONENTSKIY_ASIK");
				self::$suggestionsFields['ADDRESS']['data.kladr_id'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_KLADR");
				self::$suggestionsFields['ADDRESS']['data.okato'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_OKATO");
				self::$suggestionsFields['ADDRESS']['data.oktmo'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_OKTMO");
				self::$suggestionsFields['ADDRESS']['data.tax_office'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_IFNS_FL");
				self::$suggestionsFields['ADDRESS']['data.tax_office_legal'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_IFNS_UL");
				self::$suggestionsFields['ADDRESS']['data.flat_area'] = GetMessage("GORILLAS_SUGGESTIONS_PLOSADQ_KVARTIRY");
 				self::$suggestionsFields['ADDRESS']['data.timezone'] = GetMessage("GORILLAS_SUGGESTIONS_timezone");
				self::$suggestionsFields['ADDRESS']['data.fias_level'] = GetMessage("GORILLAS_SUGGESTIONS_fias_level");
				self::$suggestionsFields['ADDRESS']['data.fias_code'] = GetMessage("GORILLAS_SUGGESTIONS_fias_code");
				self::$suggestionsFields['ADDRESS']['data.geo'] = GetMessage("GORILLAS_SUGGESTIONS_coords");
				self::$suggestionsFields['ADDRESS']['data.geo_lat'] = GetMessage("GORILLAS_SUGGESTIONS_lat");
				self::$suggestionsFields['ADDRESS']['data.geo_lon'] = GetMessage("GORILLAS_SUGGESTIONS_lon");
				self::$suggestionsFields['ADDRESS']['data.metro'] = GetMessage("GORILLAS_SUGGESTIONS_metro");

				self::$suggestionsFields['ADDRESS']['data.fias_id'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_FIAS");
				self::$suggestionsFields['ADDRESS']['data.qc_complete'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_POLNOTY");
				self::$suggestionsFields['ADDRESS']['data.qc_house'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_PROVERKI_DOMA");
				self::$suggestionsFields['ADDRESS']['data.qc'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_KACESTVA");
				self::$suggestionsFields['ADDRESS']['data.unparsed_parts'] = GetMessage("GORILLAS_SUGGESTIONS_NERASPOZNANNAA_CASTQ");
				self::$suggestionsFields['PARTY']['value'] = GetMessage("GORILLAS_SUGGESTIONS_NAIMENOVANIE_ORGANIZ");
				self::$suggestionsFields['PARTY']['data.address.value'] = GetMessage("GORILLAS_SUGGESTIONS_ADRES_ORGANIZACII_OD");
				self::$suggestionsFields['PARTY']['data.branch_count'] = GetMessage("GORILLAS_SUGGESTIONS_CISLO_FILIALOV");
				self::$suggestionsFields['PARTY']['data.branch_type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_PODRAZDELENIA");
				self::$suggestionsFields['PARTY']['data.inn'] = GetMessage("GORILLAS_SUGGESTIONS_INN");
				self::$suggestionsFields['PARTY']['data.kpp'] = GetMessage("GORILLAS_SUGGESTIONS_KPP");
				self::$suggestionsFields['PARTY']['data.management.name'] = GetMessage("GORILLAS_SUGGESTIONS_FIO_RUKOVODITELA");
				self::$suggestionsFields['PARTY']['data.management.post'] = GetMessage("GORILLAS_SUGGESTIONS_DOLJNOSTQ_RUKOVODITE");
				self::$suggestionsFields['PARTY']['data.name.full'] = GetMessage("GORILLAS_SUGGESTIONS_POLNOE_NAIMENOVANIE");
				self::$suggestionsFields['PARTY']['data.name.latin'] = GetMessage("GORILLAS_SUGGESTIONS_NAIMENOVANIE_NA_LATI");
				self::$suggestionsFields['PARTY']['data.name.short'] = GetMessage("GORILLAS_SUGGESTIONS_KRATKOE_NAIMENOVANIE");
				self::$suggestionsFields['PARTY']['data.ogrn'] = GetMessage("GORILLAS_SUGGESTIONS_OGRN");
				self::$suggestionsFields['PARTY']['data.okpo'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_OKPO");
				self::$suggestionsFields['PARTY']['data.okved'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_OKVED");
				self::$suggestionsFields['PARTY']['data.opf.code'] = GetMessage("GORILLAS_SUGGESTIONS_KOD_OKOPF");
				self::$suggestionsFields['PARTY']['data.opf.full'] = GetMessage("GORILLAS_SUGGESTIONS_POLNOE_NAZVANIE_OPF");
				self::$suggestionsFields['PARTY']['data.opf.short'] = GetMessage("GORILLAS_SUGGESTIONS_KRATKOE_NAZVANIE_OPF");
				self::$suggestionsFields['PARTY']['data.state.registration_date'] = GetMessage("GORILLAS_SUGGESTIONS_DATA_REGISTRACII");
				self::$suggestionsFields['PARTY']['data.state.liquidation_date'] = GetMessage("GORILLAS_SUGGESTIONS_DATA_LIKVIDACII");
				self::$suggestionsFields['PARTY']['data.state.status'] = GetMessage("GORILLAS_SUGGESTIONS_STATUS_ORGANIZACII");
				self::$suggestionsFields['PARTY']['data.type'] = GetMessage("GORILLAS_SUGGESTIONS_TIP_ORGANIZACII");
				self::$suggestionsFields['BANK']['value'] = GetMessage("GORILLAS_SUGGESTIONS_NAIMENOVANIE_BANK");
				self::$suggestionsFields['BANK']['unrestricted_value'] = GetMessage("GORILLAS_SUGGESTIONS_NAIMENOVANIE_BANK_FULL");
				self::$suggestionsFields['BANK']['data.address.value'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_ADDRESS_REG");
				self::$suggestionsFields['BANK']['data.address.unrestricted_value'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_ADDRESS_REG_FULL");
				self::$suggestionsFields['BANK']['data.address.data'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_ADDRESS_REG_GRAN");
				self::$suggestionsFields['BANK']['data.bic'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_BIC");
				self::$suggestionsFields['BANK']['data.correspondent_account'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_KORCHET");
				self::$suggestionsFields['BANK']['data.name.payment'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_NAME_PAYMENT");
				self::$suggestionsFields['BANK']['data.name.full'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_NAME_FULL");
				self::$suggestionsFields['BANK']['data.name.short'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_NAME_SHORT");
				self::$suggestionsFields['BANK']['data.okpo'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_OKPO");
				self::$suggestionsFields['BANK']['data.opf.type'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_TYPE_OPF");
				self::$suggestionsFields['BANK']['data.opf.full'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_OPF_FULL");
				self::$suggestionsFields['BANK']['data.opf.short'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_OPF_SHORT");
				self::$suggestionsFields['BANK']['data.registration_number'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_REG_NUMBER");
				self::$suggestionsFields['BANK']['data.rkc'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_REG_RKC");
				self::$suggestionsFields['BANK']['data.state.actuality_date'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_ACTUAL_DATE");
				self::$suggestionsFields['BANK']['data.state.registration_date'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_REC_DATE");
				self::$suggestionsFields['BANK']['data.state.liquidation_date'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_LIQ_DATE");
				self::$suggestionsFields['BANK']['data.state.status'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_STATUS");
				self::$suggestionsFields['BANK']['data.swift'] = GetMessage("GORILLAS_SUGGESTIONS_BANK_SWIFT");

			}
			return self::$suggestionsFields;
		}

		public static function GetSettingsArray() {
			$arSaleProps = array();
			if (CModule::IncludeModule('sale')) {
				$rsPersonType = CSalePersonType::GetList(Array('SORT' => 'ASC', 'NAME' => 'ASC'), Array('ACTIVE' => 'Y'));
				while ($rsPersonRow = $rsPersonType->GetNext()) {
					$arSaleProps[] = $rsPersonRow;
				}
				foreach ($arSaleProps as $arSalePropKey => $arSalePropVal) {
					$rsOrderProps = CSaleOrderProps::GetList(array('SORT' => 'ASC'), array('PERSON_TYPE_ID' => $arSalePropVal['ID'], 'UTIL' => 'N'));

					while ($rsOrderPropRow = $rsOrderProps->GetNext()) {
						$arSaleProps[$arSalePropKey]['PROPERTIES'][] = $rsOrderPropRow;
					}
				}

			}
			return $arSaleProps;

		}

		public static function GetMappingFromPost($LId) {
			$arPostFields = array();
			foreach (self::GetSettingsArray() as $arSaleProp){
				if (in_array($LId,$arSaleProp['LIDS'])) {
					foreach ($arSaleProp['PROPERTIES'] as $arSaleField){
						$arPostFields[$arSaleField['ID']]=$_POST[self::GetFieldName($LId,$arSaleField['ID'])];
					}
				}
			}
			return $arPostFields;
		}

		public static function GetFieldName($LId,$Id){
			return str_replace('.','_',self::$module_id . '_' . $LId . '_' . $Id);
		}
		public static function GetFieldsFromMapping($LId,$Mapping) {
			$arSaleFields = array();
			foreach (self::GetSettingsArray() as $arSaleProp){
				if (in_array($LId,$arSaleProp['LIDS'])) {
					foreach ($arSaleProp['PROPERTIES'] as $arSaleField){
						$arSaleFields[self::GetFieldName($LId, $arSaleField['ID'])] = $Mapping[$arSaleField['ID']];
					}
				}
			}
			return $arSaleFields;
		}
		public static function TranslateFieldsToOrderFields($LId,$Mapping){
			$orderFields = array();
			foreach ($Mapping as $k => $val) {
				$orderFields[str_replace(str_replace('.','_',self::$module_id.'_'.$LId.'_'),'ORDER_PROP_',$k)] = $val;
			}
			return $orderFields;
		}
		public static function GetFieldNames($LId) {
			$arFieldNames = array();
			foreach (self::GetSettingsArray() as $arSaleProp) {
				if (in_array($LId, $arSaleProp['LIDS'])) {
					foreach ($arSaleProp['PROPERTIES'] as $arSaleField) {
						$arFieldNames[self::GetFieldName($LId, $arSaleField['ID'])] = $arSaleField['NAME'];
					}
				}
			}
			return $arFieldNames;
		}




	}