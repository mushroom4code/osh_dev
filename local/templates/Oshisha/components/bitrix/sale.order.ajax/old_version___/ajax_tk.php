<?     //7.49.47
require_once($_SERVER['DOCUMENT_ROOT']. "/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Sale;
use Bitrix\Main;
$arUserResult['ORDER_PROP']['6'] = intval($_REQUEST['location_id']);
$delivery_id = intval($_REQUEST['delivery_id']);
$Result = AjOnSaleAddHandlerDelivery($arUserResult, $delivery_id);
	echo $Result;
function AjOnSaleAddHandlerDelivery($arUserResult, $delivery_id){

    //ID доставки
    if( $delivery_id == 0 ) return;
    $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
    $weight = $basket->getWeight();

    $price = $basket->getPrice();
    $arOrder = array(
    'WEIGHT' => $weight,
    'PRICE' => $price,
    'LOCATION_TO' => $arUserResult['ORDER_PROP']['6'],
    'LOCATION_FROM' => 2027, //москва
    );

    if($delivery_id == 57)
    {
        if( $arUserResult['ORDER_PROP']['6'] == '' )
        	return ;
    	//ЖДЭ
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/other/JDE_API.php');
        $resultDelivery = CustomJdeDelivery::Calculate($arOrder, array());
        if( $resultDelivery['RESULT'] == 'OK' )
        {
        	$arResult['DELIVERY'][57]['CALCULATE_DESCRIPTION']	= 'Ориентировочная стоимость доставки ТК ЖДЭ до склада в вашем городе: <b>'.round($resultDelivery['VALUE']).'</b> рублей<br><a href="https://i.jde.ru/rq/?rnd=" target=_blank>Калькулятор на сайте транспортной компании</a>';
            str_replace("ВНИМАНИЕ! Указана стоимость доставки до транспортной компании.","", $arResult['DELIVERY'][7]['DESCRIPTION']);
        }
    	return $arResult['DELIVERY'][57]['CALCULATE_DESCRIPTION'];
    }
    if($delivery_id == 55)
    {
    	////ДЕЛОВЫЕ ЛИНИИ
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/other/DellinAPI.php');

        $resultDelivery = CustomDellinAPI::Calculate($arOrder, array());
		if( $resultDelivery['STATUS'] == 'OK' )
		{
			//$resultDelivery = round($resultDelivery*0.9);
			$deliveryCheckDesc .= 'Ориентировочная стоимость доставки ТК Деловые Линии до склада в вашем городе: <b>'.$resultDelivery['BODY'][0].'</b> рублей';
		}
		else
			$deliveryCheckDesc .= 'Не удалось получить расчет доставки в ваш город. Возможность доставки ТК Деловые Линии в ваш город просим уточнить у наших менеджеров!';

    	$arResult['DELIVERY'][55]['CALCULATE_DESCRIPTION']	= $deliveryCheckDesc;
    	return $deliveryCheckDesc;
   	}
    	//ПЭК
    	$deliveryCheckDesc = '';
    	/*if( $DELIVERY_ID == 47 )
    	{  */
    if($delivery_id == 56)
    {
	 		//if( count( $arDelivery ) == 2 and $iauto == 0 )
	        $arParamsItemsPEK[] = array($RAZMER_UPAKOVKI[0], $RAZMER_UPAKOVKI[1], $RAZMER_UPAKOVKI[2], $OBEM, $weight/1000,0,0);
			$arLocationCity = CSaleLocation::GetByID($arUserResult['ORDER_PROP']['6']);
								//$arCity = $rsLocationsList->GetNext();

			$CITY_PEK[1]['NAME'] = 'Москва';
			$CITY_PEK[2]['NAME'] = $arLocationCity['CITY_NAME'];
			$towns = GetPeks("https://pecom.ru/ru/calc/towns.php");
			if($towns)
			{
				$towns = json_decode($towns, 1);
			}

			foreach( $towns as $key=>$region )
			{
				if( is_array($region) )
				{
					foreach( $region as $key_city => $_city )
					{
						if($_city == $CITY_PEK[1]['NAME'])
						{
							$CITY_PEK[1]['ID'] = $key_city;
						}
						if($_city == $CITY_PEK[2]['NAME'])
						{
							$CITY_PEK[2]['ID'] = $key_city;
						}
					}
				}
			}

	        if( $CITY_PEK[2]['ID'] and $CITY_PEK[1]['ID'])
	        {      // print_r($arParamsItemsPEK);
				//Ширина, Длина, Высота, Объем, Вес, Признак габаритности груза, Признак ЖУ
				$arParamsDelivery[places] = $arParamsItemsPEK;  //описание параметров груза
				// $arParamsDelivery[places][1][] = array(1,2,3,4,5,0,0);
				$arParamsDelivery[take][town] = $CITY_PEK[1]['ID'];
				$arParamsDelivery[take][tent] = 0;    //растентровка забор
				$arParamsDelivery[take][gidro] = 0;   //гидролифт
				$arParamsDelivery[take][speed] = 0;

				$arParamsDelivery[take][moscow] = 0;   //Без въезда, МОЖД, ТТК, Садовое. значения соответственно: 0,1,2,3
				$arParamsDelivery[deliver][town] = $CITY_PEK[2]['ID'];
				$arParamsDelivery[deliver][tent] = 0;    //растентровка доставка
				$arParamsDelivery[deliver][gidro] = 0;   //гидролифт
				$arParamsDelivery[deliver][moscow] = 0;
				$GET_DELIVERY_PARAMS = http_build_query($arParamsDelivery);
					file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/_log_delivery.txt", print_r($arParamsDelivery, true), FILE_APPEND);
				$priceDelivery = GetPeks("http://calc.pecom.ru/bitrix/components/pecom/calc/ajax.php?".$GET_DELIVERY_PARAMS);
			
				//file_get_contents("http://www.pecom.ru/bitrix/components/pecom/calc/ajax.php?".$GET_DELIVERY_PARAMS);
				$printArDelivery = json_decode($priceDelivery, 1);
	//file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/_log_delivery.txt", print_r($printArDelivery, true), FILE_APPEND);
	            //$deliveryCheckDesc = 'Стоимость доставки до транспортной компании: <b>'.$arResult['DELIVERY'][47]['PRICE'].'</b> рублей <br>';
				if(intval($printArDelivery[auto][2])>0)$deliveryCheckDesc .= 'Ориентировочная стоимость доставки ТК ПЭК <b>до склада</b> в вашем городе: <b>'.round($printArDelivery[auto][2],2).'</b> рублей';
				elseif(intval($printArDelivery[avia][2])>0)
						$deliveryCheckDesc .= 'Ориентировочная стоимость доставки ТК ПЭК <b>до склада</b> в вашем городе: <b>'.round($printArDelivery[avia][2],2).'</b> рублей';	
	    		//$deliveryCheckDesc .= $printArDelivery['periods'];
	    		$arResult['DELIVERY'][56]['CALCULATE_DESCRIPTION']	= $deliveryCheckDesc;
	    	}

    	return $deliveryCheckDesc;
    }

}
	function GetPeks( $url )
	{
		$ch = curl_init($url);
		$headers = array('Content-type: text/html; charset=utf-8');
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

     	$result = curl_exec($ch);
            //  echo curl_errno($ch);
		if(curl_errno($ch) != 0) {
			$result = "";
		};
		curl_close($ch);

		return $result;
	}


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>