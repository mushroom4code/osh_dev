<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

//Подключаем файл с обработчиком провайдера
require_once('vendor/smsaeroru/SmsaeroApi.class.php');

class cwsa_smsaeroru{
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth($module_id){
		$login = Option::get($module_id, 'CSR_SMSAERORU_LOGIN');
		$passw = Option::get($module_id, 'CSR_SMSAERORU_PASSWORD');
		$sign  = Option::get($module_id, 'CSR_SMSAERORU_SENDERS');
		
		if(empty($sign))
			$sign = false;
		
		return new SmsaeroApi($login, $passw, $sign);
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){		
		$smsaero_api = self::Auth($module_id);
		return $smsaero_api->balance();
	}
	
	/************************************************
		Получаем коды
	************************************************/
	public function getCodes($id = '0'){
	}
	
	/************************************************
		Отправляем сообщение
	************************************************/
	public function sendSMS($module_id, $arFields){	
		$smsaero_api = self::Auth($module_id);
		
		$arSend = array();
		
		//Получаем текст сообщения
        $arSend['message'] = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
            $arSend['message'] = str_replace('#'.$key.'#', $field, $arSend['message']);
		}
		
		//Готовим сообщение

		
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$arSend['translit'] = 1;
		}
		else{
			$arSend['translit'] = 0;
		}
		
		//Отправитель
        $arSend['sender'] = Option::get($module_id, 'CSR_SMSAERORU_SENDERS', NULL);
		
		$digital = Option::get($module_id, 'CSR_SMSAERORU_TYPE_CHANNEL_SEND');
		if(empty($digital))
			$digital = 0;
			
		$type = Option::get($module_id, 'CSR_SMSAERORU_TYPE_SEND');
		if(empty($type))
			$type = 2;
		
		//Отправляем SMS
		$res = $smsaero_api->send($arFields["PHONE"], $arSend['message'], $digital, $type);
		
		if($res["response"]["result"] == "accepted"){
			$arFields["RESULT"] = 'success';
		}
		else{
		    $arFields["RESULT"] = 'fail';
		    self::toLog($res["response"]["reason"]);
		}
		
		self::toLog($arFields);
	}
	
	/************************************************
		Получаем отправителей
	************************************************/
	public function getSenders($tabControl, $module_id){
		$smsaero_api = self::Auth($module_id);
		$arResult = $smsaero_api->senders();
		
		$arSenders = array();
		foreach($arResult["response"] as $send){
			$arSenders[$send] = $send;
		}
		
		if (sizeof($arSenders)>0) {
			$tabControl->AddDropDownField("CSR_SMSAERORU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_SMSAERORU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_SMSAERORU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_SMSAERORU_LOGIN');
		$password = Option::get($module_id, 'CSR_SMSAERORU_PASSWORD');
		
		$tabControl->AddEditField("CSR_SMSAERORU_LOGIN", GetMessage('CSR_EMAIL'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_SMSAERORU_PASSWORD", GetMessage('CSR_PASS'), false, array("size"=>30, "maxlength"=>255, "id"=>"password-type"), $password);
		
		
		if(!empty($login) && !empty($password)){
			$balance = self::getBalance($module_id);
			
			if($balance['response']['balance'] > 0){
				
				$arTypeSend = array(
					1=>GetMessage( 'CSR_SMSAERORU_TYPE_SEND_1' ),
					2=>GetMessage( 'CSR_SMSAERORU_TYPE_SEND_2' ),
					3=>GetMessage( 'CSR_SMSAERORU_TYPE_SEND_3' ),
					4=>GetMessage( 'CSR_SMSAERORU_TYPE_SEND_4' ),
					6=>GetMessage( 'CSR_SMSAERORU_TYPE_SEND_6' ),
				);
				$arTypeSendSel = Option::get($module_id, 'CSR_SMSAERORU_TYPE_SEND');
				if(empty($arTypeSendSel)){
					$arTypeSendSel = 2;
				}
				$tabControl->AddDropDownField("CSR_SMSAERORU_TYPE_SEND", GetMessage("CSR_TYPE_SEND"), false, $arTypeSend, $arTypeSendSel, array("style='max-width: 300px;'"));
				
				$arTypeChanel = array(
					0=>GetMessage( 'CSR_SMSAERORU_TYPE_CHANNEL_SEND_1' ),
					1=>GetMessage( 'CSR_SMSAERORU_TYPE_CHANNEL_SEND_2' )
				);
				$tabControl->AddDropDownField("CSR_SMSAERORU_TYPE_CHANNEL_SEND", GetMessage("CSR_TYPE_CHANNEL"), false, $arTypeChanel, Option::get($module_id, 'CSR_SMSAERORU_TYPE_CHANNEL_SEND'));
				
				$tabControl->AddViewField("CSR_SMSAERORU_BALANCE", GetMessage("CSR_BALANCE"), $balance['response']['balance']);	
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));	
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => "")));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://smsaero.ru/")));	
		}
	}
	
	/************************************************
		Получаем сохраненный токен
	************************************************/
	public function getToken($module_id){
		return Option::get($module_id, 'CSR_SMSRU_TOKEN');
	}
	
	/************************************************
		Отправляем в лог
	************************************************/
	public function toLog($arFields){
		$handler = new CityWebSmsAuth_Handler();
		$handler->addToLog($arFields);
	}
	
	/************************************************
		Получаем protected
	************************************************/
	public function GetAttr( $obj , $attrName ) {
	    $a = (array)$obj;
	    if ( isset($a[ $attrName ] ) ) {
	        return $a[ $attrName ];
	    }
	    foreach($a as $k => $v) {
	        if ( preg_match("#".preg_quote("\x00" . $attrName)."$#" , $k) ) {
	            return $v;
	        }
	    }
	    return null;
	}
	
	/************************************************
		Отправляем запрос
	************************************************/
	public function sendQuery($module_id, $method, $arParams) {
		
		//Формируем ссылку на отпарвку
		$url = 'http://smsc.ru/sys/'.$method.'.php';
		
		//Формируем параметры
		$str = '?fmt=3';
	    foreach($arParams as $k=>$val){
		    $str.='&'.$k.'='.$val;
	    }
	    
	    //Отправляем запрос
	    $content = file_get_contents($url.$str);
	    $content = json_decode($content);
	    

	    
	    //Логируем если есть ошибка
	    if($content->error){
		    self::toLog($content->error_code.': '.$content->error);
		    return ($content->error_code.': '.$content->error);
	    }
	    else{
		    return $content;
	    }
	}
}