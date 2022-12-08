<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/lib/providers/vendor/smscru/smsc_api.php');

class cwsa_smscru{
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth(){
		
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){		
		$arParams = array(
			"login"=>$login,
			"psw"=>$password,
		);
		return self::sendQuery($module_id, 'balance', $arParams);
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
		$arSend = array();
		
		//Получаем текст сообщения
		$text = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
			$text = str_replace('#'.$key.'#', $field, $text);
		}
		
		//Готовим сообщение
		$arSend['mes'] = urlencode($text);
		$arSend['phones'] = $arFields["PHONE"];
		$arSend['charset'] = 'utf-8';
		
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$arSend['translit'] = 1;
		}
		else{
			$arSend['translit'] = 0;
		}
		
		//Отправитель
		$sedner = Option::get($module_id, 'CSR_SMSCRU_SENDERS');
		if(!empty($sedner)){
			$arSend['sender'] = $sedner;
		}
		
		//Отправляем SMS
		$res = self::sendQuery($module_id, 'send', $arSend);
		
		if($res->id){
			$arFields["RESULT"] = 'success';
		}
		else{
		    $arFields["RESULT"] = 'fail';
		    self::toLog($res);
		}
		
		self::toLog($arFields);
	}
	
	/************************************************
		Получаем отправителей
	************************************************/
	public function getSenders($tabControl, $module_id){
		$arResult = self::sendQuery($module_id, 'senders', array('get'=>1));
		$arSenders = array();

		if(is_array($arResult)){
			foreach($arResult as $send){
				$arSenders[$send->sender] = $send->sender;
			}	
		}
		
		if (sizeof($arSenders)>0) {
			$tabControl->AddDropDownField("CSR_SMSCRU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_SMSCRU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_SMSCRU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_SMSCRU_LOGIN');
		$password = Option::get($module_id, 'CSR_SMSCRU_PASSWORD');
		
		$tabControl->AddEditField("CSR_SMSCRU_LOGIN", GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_SMSCRU_PASSWORD", GetMessage('CSR_PASS'), false, array("size"=>30, "maxlength"=>255, "id"=>"password-type"), $password);
		
		
		if(!empty($login) && !empty($password)){
			$balance = self::getBalance($module_id);
			
			if($balance->balance > 0){
				$tabControl->AddViewField("CSR_SMSCRU_BALANCE", GetMessage("CSR_BALANCE"), $balance->balance);	
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));	
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"http://smsc.ru/?ppctweb")));	
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
		$arParams["login"] = Option::get($module_id, 'CSR_SMSCRU_LOGIN');
		$arParams["psw"]   = Option::get($module_id, 'CSR_SMSCRU_PASSWORD');
		
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