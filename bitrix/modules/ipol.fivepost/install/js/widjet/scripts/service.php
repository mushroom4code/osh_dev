<?php
header('Access-Control-Allow-Origin: *');
error_reporting(0);
ISDEKservice::setTarifPriority(
	array(233, 137, 139, 16, 18, 11, 1, 3, 61, 60, 59, 58, 57, 83),
    array(234, 136, 138, 15, 17, 10, 12, 5, 62, 63)
);

$action = $_REQUEST['isdek_action'];
if (method_exists('ISDEKservice', $action)) {
	ISDEKservice::$action($_REQUEST);
}

ISDEKservice::getCityByAddress($_GET['q']);

class ISDEKservice
{
	// auth
	protected static $account = 'ace4abc673358c61919b28922039e49f'; //������� �����
	protected static $key     = '48662d7f765f4f08c92d852e7af8d8cb'; //������� ����
	

	protected static $tarifPriority = false;

	// Workout
	public static function setTarifPriority($arCourier, $arPickup)
	{
		self::$tarifPriority = array(
			'courier' => $arCourier,
			'pickup'  => $arPickup
		);
	}

	public static function getPVZ()
	{
		$arPVZ = self::getPVZFile();
		if ($arPVZ) {
			self::toAnswer(array('pvz' => $arPVZ));
		}
		self::printAnswer();
	}

	public static function getLang()
	{
		self::toAnswer(array('LANG' => self::getLangArray()));
		self::printAnswer();
	}

	public static function calc($data)
	{
		if (!$data['shipment']['tarifList']) {
			$data['shipment']['tariffList'] = self::$tarifPriority[$data['shipment']['type']];
		}
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) $data['shipment']['ref'] = $_SERVER['HTTP_REFERER'];

		if (!$data['shipment']['cityToId']) {
			$cityTo = self::sendToCity($data['shipment']['cityTo']);
			if ($cityTo && $cityTo['code'] === 200) {
				$pretendents = json_decode($cityTo['result']);
				if ($pretendents && isset($pretendents->geonames)) {
					$data['shipment']['cityToId'] = $pretendents->geonames[0]->id;
				}
			}
		}

		if ($data['shipment']['cityToId']) {
			$answer = self::calculate($data['shipment']);

			if ($answer) {
				$answer['type'] = $data['shipment']['type'];
				if ($data['shipment']['timestamp']) {
					$answer['timestamp'] = $data['shipment']['timestamp'];
				}
				self::toAnswer($answer);
			}
		} else {
			self::toAnswer(array('error' => 'City to not found'));
		}

		self::printAnswer();
	}

	public static function getCity($data)
	{
		if ($data['city']) {
            self::toAnswer(self::getCityByName($data['city']));
		} elseif($data['address']){
		    self::toAnswer(self::getCityByAddress($data['address']));
        } else{
			self::toAnswer(array('error' => 'No city to search given'));
		}

		self::printAnswer();
	}

	protected static function getCityByName($name,$single=true){
	    $arReturn = array();
        $result = self::sendToCity($name);
        if ($result && $result['code'] == 200) {
            $result = json_decode($result['result']);
            if (!isset($result->geonames)) {
                $arReturn = array('error' => 'No cities found');
            } else {
                if($single) {
                    $arReturn = array(
                        'id'      => $result->geonames[0]->id,
                        'city'    => $result->geonames[0]->cityName,
                        'region'  => $result->geonames[0]->regionName,
                        'country' => $result->geonames[0]->countryName
                    );
                } else {
                    $arReturn['cities'] = array();
                    foreach ($result->geonames as $city){
                        array_push($arReturn['cities'],array(
                            'id'      => $city->id,
                            'city'    => $city->cityName,
                            'region'  => $city->regionName,
                            'country' => $city->countryName
                        ));
                    }
                }
            }
        } else {
            $arReturn = array('error' => 'Wrong answer code from server : ' . $result['code']);
        }

        return $arReturn;
    }

    /*protected*/ static function getCityByAddress($address){
        $arReturn = array();
        $arStages = array('country'=>false,'region'=>false,'subregion'=>false);
        $arAddress = explode(',',$address);

        $ind = 0;
        // finging country in address
        if(in_array($arAddress[0],self::getCountries())){
            $arStages['country'] = trim(strtolower($arAddress[0]));
            $ind++;
        }
        // finding region in address
        foreach (self::getRegion() as $regionStr){
            $search = trim(strtolower($arAddress[$ind]));
            $indSearch = strpos($search,$regionStr);
            if($indSearch !== false){
                if($indSearch){
                    $arStages['region'] = substr($search,0,strpos($search,$regionStr));
                } else {
                    $arStages['region'] = substr($search,strlen($regionStr));
                }
                $arStages['region'] = trim($arStages['region']);
                $ind++;
                break;
            }
        }
        // finding subregions
        foreach (self::getSubRegion() as $subRegionStr){
            $search = trim(strtolower($arAddress[$ind]));
            $indSearch = strpos($search,$subRegionStr);
            if($indSearch !== false){
                if($indSearch){
                    $arStages['subregion'] = substr($search,0,strpos($search,$subRegionStr));
                } else {
                    $arStages['subregion'] = substr($search,strlen($subRegionStr));
                }
                $arStages['subregion'] = trim($arStages['subregion']);
                $ind++;
                break;
            }
        }
        // finding city
        $cityName = trim($arAddress[$ind]);
        $cdekCity = self::getCityByName($cityName,false);

        if($cdekCity['error']){
            foreach(self::getCityDef() as $placeLbl){
                $search = trim(strtolower($arAddress[$ind]));
                $indSearch = strpos($search,$placeLbl);
                if($indSearch !== false){
                    if($indSearch){
                        $search = substr($search,0,strpos($search,$placeLbl));
                    } else {
                        $search = substr($search,strlen($placeLbl));
                    }
                    $search = trim($search);
                    $cityName = $search;
                    $cdekCity = self::getCityByName($search,false);
                    break;
                }
            }
        }

        if($cdekCity['error']){
            $arReturn['error'] = $cdekCity['error'];
        } else {
            if(count($cdekCity['cities']) > 0){
                $pretend = false;
                $arPretend = array();
                // parseCountry
                if($arStages['country']){
                    foreach ($cdekCity['cities'] as $arCity) {
                        $possCountry = strtolower($arCity['country']);
                        if (!$possCountry || strpos($arStages['country'], $possCountry) !== false) {
                            $arPretend [] = $arCity;
                        }
                    }
                } else {
                    $arPretend = $cdekCity['cities'];
                }

                // parseRegion
                if(count($arPretend) > 1){
                    if($arStages['region']) {
                        $_arPretend = array();
                        foreach ($arPretend as $arCity) {
                            $possRegion = strtolower($arCity['region']);
                            if(!$possRegion || strpos($possRegion,$arStages['region']) !== false){
                                $_arPretend []= $arCity;
                            }
                        }
                        $arPretend = $_arPretend;
                    }
                }

                // parseSubRegion
                if(count($arPretend) > 1){
                    if($arStages['subregion']) {
                        $_arPretend = array();
                        foreach ($arPretend as $arCity) {
                            $possSubRegion = strtolower($arCity['city']);
                            if(!$possSubRegion || strpos($possSubRegion,$arStages['subregion']) !== false){
                                $_arPretend []= $arCity;
                            }
                        }
                        $arPretend = $_arPretend;
                    }
                }
                // parseUndefined
                    // not full city name
                if(count($arPretend) > 1) {
                    $_arPretend = array();
                    foreach ($arPretend as $arCity) {
                        if (strpos($arCity['city'], ',') === false) {
                            $_arPretend [] = $arCity;
                        }
                    }
                    $arPretend = $_arPretend;
                }
                if(count($arPretend) > 1){
                    $_arPretend = array();
                    foreach ($arPretend as $arCity) {
                        if(strlen($arCity['city']) === strlen($cityName)){
                            $_arPretend []= $arCity;
                        }
                    }
                    $arPretend = $_arPretend;
                }
                    // federalCities
                if(count($arPretend) > 1) {
                    $_arPretend = array();
                    foreach ($arPretend as $arCity) {
                        if ($arCity['city'] === $arCity['region']) {
                            $_arPretend [] = $arCity;
                        }
                    }
                    $arPretend = $_arPretend;
                }


                // end
                if(count($arPretend) === 1){
                    $pretend = array_pop($arPretend);
                }
            } else {
                $pretend = $cdekCity['cities'][0];
            }
            if($pretend){
                $arReturn['city'] = $pretend;
            } else {
                $arReturn['error'] = 'Undefined city';
            }
        }

        return $arReturn;
    }

    protected static function getCountries()
    {
        return array('������', '��������', '�������', '���������', '��������', '�������', '�����������', '����������');
    }

    protected static function getRegion()
    {
        return array('���������� �������','�������','����������','���������� �����','�����','����');
    }

    protected static function getSubRegion()
    {
        return array('������������� �����','�����','��������� �����');
    }

    protected static function getCityDef()
    {
        return array('������� ���������� ����','���������� �����','��������� �������','������ �������','������� �������','�������� ���������','�������� ���������','�/� �������','�������','�������',
                        '�������','����������','�������','�����','���','�������','����','���');
    }

	// PVZ
	protected static function getPVZFile()
	{

		$arPVZ = self::requestPVZ();

		return $arPVZ;
	}

	protected static function requestPVZ()
	{
		if (!function_exists('simplexml_load_string')) {
			self::toAnswer(array('error' => 'No php simplexml-library installed on server'));
			return false;
		}

		$request = self::sendToSDEK('pvzlist', false, 'type=ALL' .(isset($_REQUEST['lang'])? '&lang='.$_REQUEST['lang'] : '') );
		$arLL = array();
		if ($request && $request['code'] == 200) {
			$xml = simplexml_load_string($request['result']);

			$arList = array('PVZ' => array(), 'CITY' => array(), 'REGIONS' => array(), 'CITYFULL' => array(), 'COUNTRIES' => array());

			foreach ($xml as $key => $val) {

				if ($_REQUEST['country'] && $_REQUEST['country'] != 'all' && ((string)$val['CountryName'] != $_REQUEST['country'])) {
					continue;
				}

				$cityCode = (string)$val['CityCode'];
				$type = 'PVZ';
				$city = (string)$val["City"];
				if (strpos($city, '(') !== false)
					$city = trim(substr($city, 0, strpos($city, '(')));
				if (strpos($city, ',') !== false)
					$city = trim(substr($city, 0, strpos($city, ',')));
				$code = (string)$val["Code"];

				$arList[$type][$cityCode][$code] = array(
					'Name'           => (string)$val['Name'],
					'WorkTime'       => (string)$val['WorkTime'],
					'Address'        => (string)$val['Address'],
					'Phone'          => (string)$val['Phone'],
					'Note'           => (string)$val['Note'],
					'cX'             => (string)$val['coordX'],
					'cY'             => (string)$val['coordY'],
					'Dressing'       => ($val['IsDressingRoom'] == 'true'),
					'Cash'           => ($val['HaveCashless'] == 'true'),
					'Station'        => (string)$val['NearestStation'],
					'Site'           => (string)$val['Site'],
					'Metro'          => (string)$val['MetroStation'],
					'AddressComment' => (string)$val['AddressComment'],
					'CityCode'       => (string)$val['CityCode'],
				);
				if ($val->WeightLimit) {
					$arList[$type][$cityCode][$code]['WeightLim'] = array(
						'MIN' => (float)$val->WeightLimit['WeightMin'],
						'MAX' => (float)$val->WeightLimit['WeightMax']
					);
				}

				$arImgs = array();
				if (!is_array($val->OfficeImage)) {
					$arToCheck = array(array('url' => (string)$val->OfficeImage['url']));
				} else {
					$arToCheck = $val->OfficeImage;
				}

				foreach ($val->OfficeImage as $img) {
					if (strstr($_tmpUrl = (string)$img['url'], 'http') === false) {
						continue;
					}
					$arImgs[] = (string)$img['url'];
				}

				if (count($arImgs = array_filter($arImgs)))
					$arList[$type][$cityCode][$code]['Picture'] = $arImgs;
				if ($val->OfficeHowGo)
					$arList[$type][$cityCode][$code]['Path'] = (string)$val->OfficeHowGo['url'];

				if (!array_key_exists($cityCode, $arList['CITY'])) {
					$arList['CITY'][$cityCode] = $city;
					$arList['CITYREG'][$cityCode] = (int)$val['RegionCode'];
					$arList['REGIONSMAP'][(int)$val['RegionCode']][] = (int)$cityCode;
					$arList['CITYFULL'][$cityCode] = (string)$val['CountryName'] . ' ' . (string)$val['RegionName'] . ' ' . $city;
					$arList['REGIONS'][$cityCode] = implode(', ', array_filter(array((string)$val['RegionName'], (string)$val['CountryName'])));
				}

			}

			krsort($arList['PVZ']);

			return $arList;
		} elseif ($request) {
			self::toAnswer(array('error' => 'Wrong answer code from server : ' . $request['code']));
			return false;
		}
	}

	// Calculation
	protected static function calculate($shipment)
	{
		$headers = self::getHeaders();

		$arData = array(
			'dateExecute'    => $headers['date'],
			'version'        => '1.0',
			'authLogin'      => $headers['account'],
			'secure'         => $headers['secure'],
			'senderCityId'   => $shipment['cityFromId'],
			'receiverCityId' => $shipment['cityToId'],
			'ref'            => $shipment['ref'],
			'widget'         => 1,
			'tariffId'       => ($shipment['tariffId']) ? $shipment['tariffId'] : false
		);

		if ($shipment['tariffList']) {
			foreach ($shipment['tariffList'] as $priority => $tarif) {
				$tarif = intval($tarif);
				$arData['tariffList'] [] = array(
					'priority' => $priority + 1,
					'id'       => $tarif
				);
			}
		}

		if ($shipment['goods']) {
			$arData['goods'] = array();
			foreach ($shipment['goods'] as $arGood) {
				$arData['goods'] [] = array(
					'weight' => $arGood['weight'],
					'length' => $arGood['length'],
					'width'  => $arGood['width'],
					'height' => $arGood['height']
				);
			}
		}

		$result = self::sendToCalculate($arData);

		if ($result && $result['code'] == 200) {
			if (!is_null(json_decode($result['result']))) {
				return json_decode($result['result'], true);
			} else {
				self::toAnswer(array('error' => 'Wrong server answer'));
				return false;
			}
		} else {
			self::toAnswer(array('error' => 'Wrong answer code from server : ' . $result['code']));
			return false;
		}
	}

	// API
	protected static function sendToSDEK($where, $XML = false, $get = false)
	{
		$where .= '.php' . (($get) ? "?" . $get : '');
		$where = 'https://integration.cdek.ru/' . $where;

		if ($XML)
			$XML = array('xml_request' => $XML);

		return self::client($where, $XML);
	}

	protected static function getHeaders()
	{
		$date = date('Y-m-d');
		$arHe = array(
			'date' => $date
		);
		if (self::$account && self::$key) {
			$arHe = array(
				'date'    => $date,
				'account' => self::$account,
				'secure'  => md5($date . "&" . self::$key)
			);
		}
		return $arHe;
	}

	protected static function sendToCalculate($data)
	{
		$result = self::client(
			'http://api.cdek.ru/calculator/calculate_price_by_json_request.php',
			array('json' => json_encode($data))
		);
		return $result;
	}

	protected static function sendToCity($data)
	{
		$result = self::client(
			'http://api.cdek.ru/city/getListByTerm/json.php?q=' . urlencode($data)
		);
		return $result;
	}

	protected static function client($where, $data = false)
	{
		if (!function_exists('curl_init')) {
			self::toAnswer(array('error' => 'No php CURL-library installed on server'));
			return false;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $where);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if ($data) {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_REFERER, 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']);
		}
		$result = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		return array(
			'code'   => $code,
			'result' => $result
		);
	}

	// LANG
	protected static function getLangArray()
	{
		$tanslate = array(
			'rus' => array(
				'YOURCITY'   => '��� �����',
				'COURIER'    => '������',
				'PICKUP'     => '���������',
				'TERM'       => '����',
				'PRICE'      => '���������',
				'DAY'        => '��.',
				'RUB'        => '���.',
				'NODELIV'    => '��� ��������',
				'CITYSEARCH' => '����� ������',
				'ALL'        => '���',
				'PVZ'        => '������ ������',
				'MOSCOW'     => '������',
				'RUSSIA'     => '������',
				'COUNTING'   => '���� ������',

				'NO_AVAIL'          => '��� ��������� �������� ��������',
				'CHOOSE_TYPE_AVAIL' => '�������� ������ ��������',
				'CHOOSE_OTHER_CITY' => '�������� ������ ���������� �����',

				'TYPE_ADDRESS'      => '�������� �����',
				'TYPE_ADDRESS_HERE' => '������� ����� ��������',

				'L_ADDRESS' => '����� ������ ������ �������',
				'L_TIME'    => '����� ������',
				'L_WAY'     => '��� � ��� ��������',
				'L_CHOOSE'  => '�������',

				'H_LIST'    => '������ ������� ������ �������',
				'H_PROFILE' => '������ ��������',
				'H_CASH'    => '������ ������',
				'H_DRESS'   => '� ���������',
				'H_SUPPORT' => '������ ���������',
				'H_QUESTIONS' => '���� � ��� ���� �������, ������<br> ������ �� ����� ������������',

                'ADDRESS_WRONG'   => '���������� ���������� ��������� ��������������. �������� ����� �� ����������� ������ � �������� ������.',
                'ADDRESS_ANOTHER' => '������������ � ������ ��������� �������� ��� ���������� ��������������.'
		),
			'eng' => array(
				'YOURCITY'   => 'Your city',
				'COURIER'    => 'Courier',
				'PICKUP'     => 'Pickup',
				'TERM'       => 'Term',
				'PRICE'      => 'Price',
				'DAY'        => 'days',
				'RUB'        => ' RUB',
				'NODELIV'    => 'Not delivery',
				'CITYSEARCH' => 'Search for a city',
				'ALL'        => 'All',
				'PVZ'        => 'Points of self-delivery',
				'MOSCOW'     => 'Moscow',
				'RUSSIA'     => 'Russia',
				'COUNTING'   => 'Calculation',

				'NO_AVAIL'          => 'No shipping methods available',
				'CHOOSE_TYPE_AVAIL' => 'Choose a shipping method',
				'CHOOSE_OTHER_CITY' => 'Choose another location',

				'L_ADDRESS' => 'Adress of self-delivery',
				'L_TIME'    => 'Working hours',
				'L_WAY'     => 'How to get to us',
				'L_CHOOSE'  => 'Choose',

				'H_LIST'    => 'List of self-delivery',
				'H_PROFILE' => 'Shipping method',
				'H_CASH'    => 'Payment by card',
				'H_DRESS'   => 'Dressing room',
				'H_SUPPORT' => 'Support',
				'H_QUESTIONS' => 'If you have any questions,<br> you can ask them to our specialists',

                'ADDRESS_WRONG' => 'Impossible to define address. Please, recheck the address.',
                'ADDRESS_ANOTHER' => 'Read the new terms and conditions.'
			)

		);
		if (isset($_REQUEST['lang']) && isset($tanslate[$_REQUEST['lang']]) ) return $tanslate[$_REQUEST['lang']];
		else return $tanslate['ru'];
	}

	// answering
	protected static $answer = false;

	protected static function toAnswer($wat)
	{
		$stucked = array('error');
		if (!is_array($wat)) {
			$wat = array('info' => $wat);
		}
		if (!is_array(self::$answer)) {
			self::$answer = array();
		}
		foreach ($wat as $key => $sign) {
			if (in_array($key, $stucked)) {
				if (!array_key_exists($key, self::$answer)) {
					self::$answer[$key] = array();
				}
				self::$answer[$key] [] = $sign;
			} else {
				self::$answer[$key] = $sign;
			}
		}
	}

	protected static function printAnswer()
	{
		echo json_encode(self::$answer);
	}
}

?>