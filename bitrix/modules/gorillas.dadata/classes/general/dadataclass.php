<?php


class CDadataSuggestions
  {

    private static $module_id = 'gorillas.dadata';
    private static $url = "https://dadata.ru/api/v2";
    private static $url_static_css = "https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/css/suggestions.min.css";
    private static $url_static_js = "https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js";


    public static function request($code)
      {
        return array_key_exists($code, $_REQUEST) ? $_REQUEST[$code] : false;
      }

    public static function request_full($code)
      {
        return array_key_exists($code, $_REQUEST) && strlen($_REQUEST[$code]) ? $_REQUEST[$code] : false;
      }

    public static function application()
      {
        return $GLOBALS['APPLICATION'];
      }

    public static function user()
      {
        return $GLOBALS['USER'];
      }

    public static function fieldSelector($fieldNo)
      {
        return "'[name=ORDER_PROP_" . $fieldNo . "]'";

      }

    private static function isNewLocationModule()
      {
        if (!method_exists(CSaleLocation, "isLocationProEnabled"))
          {
            return false;
          }
        return CSaleLocation::isLocationProEnabled();
      }

    public static function GetPartParameterString($SuggestionType, $ParamName)
      {
        if ($SuggestionType == 'NAME')
          {
            if ($ParamName == 'data.name')
              {
                return "params: { parts:['NAME']},";
              }
            if ($ParamName == 'data.surname')
              {
                return "params: { parts:['SURNAME']},";
              }
            if ($ParamName == 'data.patronymic')
              {
                return "params: { parts:['PATRONYMIC']},";
              }
          }
        elseif ($SuggestionType == 'ADDRESS')
          {
            if (in_array($ParamName, array(
              'data.region',
              'data.country',
              'data.area'
            )))
              {
                return "bounds: 'region-area',";
              }
            if (in_array($ParamName, array(
              'data.city',
              'data.settlement'
            )))
              {
                return "bounds: 'city-settlement',";
              }
            if (in_array($ParamName, array('data.street')))
              {
                return "bounds: 'street',";
              }
            if (in_array($ParamName, array(
              'data.house',
              'data.block'
            )))
              {
                return "bounds: 'house',";
              }

          }

      }

    private function getMappingObject()
      {
        $arFieldValues = unserialize(COption::GetOptionString(self::$module_id, 'mapping', "", SITE_ID));
        $arGroupMapping = unserialize(COption::GetOptionString(self::$module_id, 'typemapping', "", SITE_ID));
        $newArray = array();
        $boundFields = array();
        foreach ($arFieldValues as $fieldNo => $fieldVal)
          if ($fieldVal)
            {
              $suggestionType = strstr($fieldVal, '_', true);
              $suggestionVar = substr($fieldVal, strpos($fieldVal, '_') + 1);
              $newArray[$fieldNo]['type'] = $suggestionType;
              foreach ($arGroupMapping as $groupId => $groupFields)
                if (in_array($fieldNo, $groupFields))
                  {
                    $newArray[$fieldNo]['group'] = $groupId;
                  }


              $newArray[$fieldNo]['var'] = $suggestionVar;
              if ($suggestionType == 'NAME')
                {
                  if ($suggestionVar == 'data.name')
                    {
                      $newArray[$fieldNo]['params'] = "{ parts:['NAME']}";
                    }
                  if ($suggestionVar == 'data.surname')
                    {
                      $newArray[$fieldNo]['params'] = "{ parts:['SURNAME']}";
                    }
                  if ($suggestionVar == 'data.patronymic')
                    {
                      $newArray[$fieldNo]['params'] = "{ parts:['PATRONYMIC']}";
                    }
                }
              elseif ($suggestionType == 'ADDRESS')
                {
                  if (in_array($suggestionVar, array(
                    'data.region',
                    'data.country',
                    'data.area'
                  )))
                    {
                      $newArray[$fieldNo]['bounds'] = "region-area";
                      if (isset($newArray[$fieldNo]['group']))
                        {
                          $boundFields[$newArray[$fieldNo]['group']]['city-settlement'] = $fieldNo;
                        }
                    }
                  if (in_array($suggestionVar, array(
                    'data.city',
                    'data.settlement'
                  )))
                    {
                      $newArray[$fieldNo]['bounds'] = "city-settlement";
                      if (isset($newArray[$fieldNo]['group']))
                        {
                          $boundFields[$newArray[$fieldNo]['group']]['street'] = $fieldNo;
                        }
                    }

                  if (in_array($suggestionVar, array('data.street')))
                    {
                      $newArray[$fieldNo]['bounds'] = "street";
                      if (isset($newArray[$fieldNo]['group']))
                        {
                          $boundFields[$newArray[$fieldNo]['group']]['house'] = $fieldNo;
                        }

                    }
                  if (in_array($suggestionVar, array(
                    'data.house',
                    'data.block'
                  )))
                    {

                      $newArray[$fieldNo]['bounds'] = "house";
                    }
                }

            }
        foreach ($newArray as $fieldNo => $fieldProps)
          {
            $fieldBounds = $fieldProps['bounds'];
            $fieldGroup = $fieldProps['group'];
            if (isset($fieldBounds) && isset($fieldGroup))
              {
                $fieldConstraint = $boundFields[$fieldGroup][$fieldBounds];
                if (isset($fieldConstraint))
                  {
                    $newArray[$fieldNo]['constraint'] = $fieldConstraint;
                  }
              }
          }

        return CUtil::PhpToJSObject($newArray);
      }

  /*
 * ������� ����������� javascript.
*/
    public static function setJS($arErrors = array())
      {
        global $APPLICATION;
        $arFieldValues = unserialize(COption::GetOptionString(self::$module_id, 'mapping', "", SITE_ID));
        $probideLinkBy = COption::GetOptionString(self::$module_id, 'provide_link_by', '', SITE_ID);
        $arFieldsTypes = array();
        $arMainType = array();
        $arLocations = array();

        if ($probideLinkBy == 'id')
          {
            $probideLinkByItem = "VALUE";
          }
        else
          {
            $probideLinkByItem = "CODE";
          }

        foreach ($arFieldValues as $fieldNo => $fieldVal)
          {
            if ($fieldVal != "")
              {
                if ($fieldVal != "ADDRESS_LOCATION")
                  {
                    $type = explode("_", $fieldVal, 2);

                    if ($type[1] == "value")
                      {
                        $arMainType[$fieldNo] = $type[0];
                      }
                    else
                      {
                        $arFieldsTypes[$type[0]][$fieldNo] = $type[1];
                      }
                  }
                else
                  {
                    $arLocations[$fieldNo] = $fieldNo;
                  }
              }
          }
        ?>
          <link href="<?= self::$url_static_css ?>" type="text/css" rel="stylesheet"/>
          <script type="text/javascript" src="<?= self::$url_static_js ?>"></script>

          <script type="text/javascript">
						Date.prototype.ddmmyyyy = function () {
							var yyyy = this.getFullYear().toString();
							var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
							var dd = this.getDate().toString();
							return (mm[1] ? mm : "0" + mm[0]) + "." + (dd[1] ? dd : "0" + dd[0]) + "." + yyyy; // padding
						};

						var dadataSuggestions = {
							getCityEl: function (result, region) {
								//console.log(region);
								if(region=="Москва" || region=="Санкт-Петербург")
									region = "Россия";
								if (result.data['ITEMS'] !== undefined) {
									var item = result.data['ITEMS'][0];
									for (var prop in result.data['ITEMS']) {
										var el = result.data['ITEMS'][prop];
										var paths = el['PATH'];
										for (var i = 0; i < paths.length; i++) {
											if (result.data['ETC']['PATH_ITEMS'] !== undefined) {
												var pathEl = result.data['ETC']['PATH_ITEMS'][paths[i]];
												//console.log(pathEl['DISPLAY'] + '----------' + region);
												if (pathEl['DISPLAY'] == region) {
													return el;
												}
											}
										}

									}
									return item;
								}
								return false;
							},
							fieldSelector: function (id) {
								return '[name=ORDER_PROP_' + id + ']';
							},
							getConf: function (type, callback) {
								return {
									serviceUrl: "<?=self::$url?>",
									token: "<?=COption::GetOptionString(self::$module_id, 'apikey', '', SITE_ID)?>",
									partner: "BITRIX.GORILLAS",
									type: type,
									count: 5,
									onSelect: callback,
								}
							},
							setLocation: function (suggestion) {
								var url = '/bitrix/components/gorillas/locations/search.php';
								var city = (suggestion.data.city ? suggestion.data.city : suggestion.data.settlement);
								var region = suggestion.data.region;
								var country = suggestion.data.country;
								BX.showWait();
								//console.log(suggestion);
								var regionWith = (suggestion.data.region_with_type ? suggestion.data.region_with_type : suggestion.data.region);
								if (suggestion.data.region_type == "г")
									regionWith = suggestion.data.region;
								if(suggestion.data.region_type=="обл")
									 regionWith = suggestion.data.region+" "+suggestion.data.region_type_full;

								var requestDada = {
									"select[1]": "CODE",
									"select[2]": "TYPE_ID",
									"select[VALUE]": "ID",
									"select[DISPLAY]": "NAME.NAME",
									"additionals[1]": "PATH",
									"filter[=PHRASE]": city,
									"filter[=NAME.LANGUAGE_ID]": "ru",
									"version": "2",
									"PAGE_SIZE": "10",
									"PAGE": "0",
									"REGION": region,
									"COUNTRY": country,
								};
//console.log(requestDada);
								BX.ajax.post('/bitrix/components/bitrix/sale.location.selector.search/get.php', requestDada, function (data) {
									var result = BX.parseJSON(data);
									BX.closeWait();
//console.log(result);
									if (result.result = true) {
										var item = dadataSuggestions.getCityEl(result, regionWith);
		//console.log(item);
										if (item['CODE']) {
											if (typeof submitForm === "function") {
                        <? foreach($arLocations as $location): ?>
												$(dadataSuggestions.fieldSelector(<?=$location?>)).val(item['<?=$probideLinkByItem?>']);
                        <? endforeach ?>
												FindDistance();//submitForm()
											} else if (typeof window.submitFormProxy === "function") {
                        <? foreach($arLocations as $location): ?>
												$(dadataSuggestions.fieldSelector(<?=$location?>)).val(item['<?=$probideLinkByItem?>']);
                        <? endforeach ?>
												window.submitFormProxy()
											}
										}
									}
								});
                <? if(false): ?>

								$.getJSON(url, {'city': city, 'region': region, 'country': country}, function (result) {
									BX.closeWait();
									if (result.ID > 0) {
										if (typeof submitForm === "function") {
                      <? foreach($arLocations as $location): ?>
											$(dadataSuggestions.fieldSelector(<?=$location?>)).val(result.<?=$probideLinkByItem?>);
                      <? endforeach ?>
											console.log('WTF?');submitForm()
										} else if (typeof window.submitFormProxy === "function") {
                      <? foreach($arLocations as $location): ?>
											$(dadataSuggestions.fieldSelector(<?=$location?>)).val(result.<?=$probideLinkByItem?>);
                      <? endforeach ?>
											window.submitFormProxy()
										}
									}
								});
                <? endif; ?>
							},
							initSuggestionFields: function () {
                <? foreach($arMainType as $id => $type):?>
								$(dadataSuggestions.fieldSelector(<?=$id?>)).suggestions(dadataSuggestions.getConf('<?=$type?>',
									function (suggestion) {
	 console.log('checks');
                    <? if($type == "PARTY"): ?>
										if (suggestion.data.state.registration_date != null) {
											var t = new Date(suggestion.data.state.registration_date);
											suggestion.data.state.registration_date = t.ddmmyyyy();
										}
                    <? endif ?>

                    <? if($type == "ADDRESS"): ?>
										if (suggestion.data.settlement == null && suggestion.data.city != null) {
											suggestion.data.settlement = suggestion.data.city_type + " " + suggestion.data.city;
										}
										if (suggestion.data.area == null && suggestion.data.city_district != null) {
											suggestion.data.area = suggestion.data.city_district;
										}
                    <? endif ?>


                    <? if(!empty($arFieldsTypes[$type])): ?>
                    <? foreach($arFieldsTypes[$type] as $prop=>$code): ?>
                        if (typeof suggestion.<?=$code?> != "undefined" && suggestion.<?=$code?> !=  null) {
                        	var stringValue='';

                            <? if ($code == "data.metro"): ?>
	                        stringValue = suggestion.<?=$code?>.map(e => e.name).join(",");
                            <? else: ?>
	                        stringValue = suggestion.<?=$code?>;
                            <? endif; ?>

                          $(dadataSuggestions.fieldSelector(<?=$prop?>)).val(stringValue);
                        }
                         <? if($code=='data.geo'): ?>
                        if(suggestion.data.geo_lat && suggestion.data.geo_lon)
                            stringValue = suggestion.data.geo_lat+","+suggestion.data.geo_lon;
    						$(dadataSuggestions.fieldSelector(<?=$prop?>)).val(stringValue);
                        <? endif; ?>

                    <? endforeach ?>
                    <? endif ?>
                     <? if($type == "ADDRESS"): ?>
				
										dadataSuggestions.setLocation(suggestion);
                    <? endif ?>

									}));
                <? endforeach ?>
							}
						};

						BX.ready(function () {
							BX.addCustomEvent("onAjaxSuccess", BX.delegate(function (command, params) {
								dadataSuggestions.initSuggestionFields();
							}, this));
							dadataSuggestions.initSuggestionFields();
						});

          </script>
        <?
      }

  /*
 * ����� ������ �� �������� ��� ����������.
 * stdClass Object ([detail] => Zero balance)
 */
    public static function magicFunction($response)
      {
        if (is_array($response->data))
          {
            if (is_array($response->data[0]) && is_object($response->data[0][0]))
              {
                return (array)$response->data[0][0];
              }
          }
        return array();
      }

    public static function onSaleComponentOrderOneStepProcess(&$arResult, $arUserResult, $arParams)
      {
        if (CDadataSuggestions::request('is_ajax_post') != 'Y')
          {
            if (COption::GetOptionString(self::$module_id, 'enabled', 'N', SITE_ID) == 'Y')
              {
                require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/gorillas.dadata/classes/general/settingsclass.php');
                // ���� ���������� ������� jquery
                if (COption::GetOptionString(self::$module_id, 'jquery', 'Y', SITE_ID) == 'Y')
                  {
                    CJSCore::Init(array('jquery'));
                  }

                CDadataSuggestions::setJS();
              }
          }
      }
  }