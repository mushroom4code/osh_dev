<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/lib/providers/vendor/bytehandcom/Bytehand.php');

class cwsa_bytehandcom {
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth($module_id){
		$login = Option::get($module_id, 'CSR_BYTEHANDCOM_LOGIN');
		$apikey = Option::get($module_id, 'CSR_BYTEHANDCOM_APIKEY');
		$from =  Option::get($module_id, 'CSR_BYTEHANDCOM_SENDERS');
		if(empty($from))
			$from = 'SMS-INFO';
		
		return new ByteHandApi(array('id' => $login, 'key' => $apikey, 'from'=> $from));
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){	
		$api = self::Auth($module_id);
		return $api->getBalance();
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
		$api = self::Auth($module_id);
		$arSend = array();
		
		//Получаем текст сообщения
        $arSend['message'] = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
            $arSend['message'] = str_replace('#'.$key.'#', $field, $arSend['message']);
		}
				
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$CSR_HANDLER = new CityWebSmsAuth_Handler();
            $arSend['message'] = $CSR_HANDLER->csr_transliterate($arSend['message']);
		}
		
		//Готовим сообщение
		$arSend['phone'] = $arFields["PHONE"];
		
		//Отправляем SMS
		$res = $api->send($arSend['phone'], $arSend['message']);

		if(!is_array($res)){
			$arFields["RESULT"] = 'success';
			$arFields["RESULT_MSG"] = $res;
		}
		else{
		    $arFields["RESULT"] = 'fail';
		    self::toLog($res['error']->status.': '.$res['error']->description);
		}
		
		self::toLog($arFields);
	}
	
	/************************************************
		Получаем отправителей
	************************************************/
	public function getSenders($tabControl, $module_id){
		$api = self::Auth($module_id);
		$res = $api->getSenders();
		$res = json_decode($res);
		
		$arSenders = array('SMS-INFO'=>'SMS-INFO');
		foreach($res as $send){
			
			switch ($send->state) {
			    case 'NEW':
			        $mod = ' ('.GetMessage('CSR_ON_MODERATE').')';
			        break;
			    case 'REJECTED':
			        $mod = ' ('.GetMessage('CSR_ON_REJECTED').')';
			        break;
			    default:
				    $mod = '';
			}

			$arSenders[$send->text] = $send->text.$mod;
		}
		
		if (sizeof($arSenders)>0) {
			$tabControl->AddDropDownField("CSR_BYTEHANDCOM_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_BYTEHANDCOM_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_BYTEHANDCOM_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_BYTEHANDCOM_LOGIN');
		$apikey = Option::get($module_id, 'CSR_BYTEHANDCOM_APIKEY');
		
		$tabControl->AddEditField("CSR_BYTEHANDCOM_LOGIN", GetMessage('CSR_ID_CLIENT'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_BYTEHANDCOM_APIKEY", GetMessage('CSR_API_KEY'), false, array("size"=>30, "maxlength"=>255), $apikey);
		
		
		if(!empty($login) && !empty($apikey)){
			$balance = self::getBalance($module_id);

			if(!is_array($balance)){
				$tabControl->AddViewField("CSR_BYTEHANDCOM_BALANCE", GetMessage("CSR_BALANCE"), $balance);	
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));	
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance["error"]->status.': '.$balance["error"]->description)));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://www.bytehand.com/?r=50a8f6057239b83c")));	
		}
	}
	
	/************************************************
		Получаем сохраненный токен
	************************************************/
	public function getToken($module_id){
		return Option::get($module_id, 'CSR_BYTEHANDCOM_TOKEN');
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
		$arParams["login"] = Option::get($module_id, 'CSR_BYTEHANDCOM_LOGIN');
		$arParams["psw"]   = Option::get($module_id, 'CSR_BYTEHANDCOM_PASSWORD');
		
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