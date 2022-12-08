<?
//Аналог sms-sending.ru
	
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

//Подключаем файл с обработчиком провайдера
require_once('vendor/smssendingru/smssendingru.php');

class cwsa_smsuslugiru{
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth($module_id){
		$login = Option::get($module_id, 'CSR_SMSSENDINGRU_LOGIN');
		$passw = Option::get($module_id, 'CSR_SMSSENDINGRU_PASSWORD');
		
		return new smssendingruAPI($login, $passw);
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){
		$api = self::Auth($module_id);
		$res = $api->balance();
		
		return $res;
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
		$text = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
			$text = str_replace('#'.$key.'#', $field, $text);
		}
				
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$CSR_HANDLER = new CityWebSmsAuth_Handler();
			$text = $CSR_HANDLER->csr_transliterate($text);
		}
		
		//Готовим сообщение
		$arPhone = array($arFields["PHONE"]);
		$arSend['text'] = $text;
		
		$sender = Option::get($module_id, 'CSR_SMSSENDINGRU_SENDERS');
		if(empty($sender))
			$sender = false;
			
		$arSend['source'] = $sender;
		
		//Отправляем SMS
		$res = $api->send($arSend, $arPhone);
		
		if($res['code'] == 1){
			$arFields["RESULT"] = 'success';
			$arFields["RESULT_MSG"] = $res["descr"];
		}
		else{
		    $arFields["RESULT"] = 'fail';
		    self::toLog($res["code"].': '.$res["descr"]);
		}
		
		self::toLog($arFields);
	}
	
	/************************************************
		Получаем отправителей
	************************************************/
	public function getSenders($tabControl, $module_id){
		$api = self::Auth($module_id);
		$res = $api->getSenders();
		$reply = json_decode($res);
		
		$arSenders = array();
		foreach($reply->source as $s){
			$arSenders[$s] = $s;
		}
		
		if (sizeof($arSenders) > 0) {
			$tabControl->AddDropDownField("CSR_SMSSENDINGRU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_SMSSENDINGRU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_SMSSENDINGRU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_SMSSENDINGRU_LOGIN');
		$password = Option::get($module_id, 'CSR_SMSSENDINGRU_PASSWORD');
		
		$tabControl->AddEditField("CSR_SMSSENDINGRU_LOGIN", GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_SMSSENDINGRU_PASSWORD", GetMessage('CSR_PASS'), false, array("size"=>30, "maxlength"=>255, "id"=>"password-type"), $password);
		
		
		if(!empty($login) && !empty($password)){
			$balance = self::getBalance($module_id);
			
			if($balance !== false){
				$tabControl->AddViewField("CSR_SMSSENDINGRU_BALANCE", GetMessage("CSR_BALANCE"), $balance);	
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));	
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => '')));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"http://sms-sending.ru/")));
		}
	}
	
	/************************************************
		Получаем сохраненный токен
	************************************************/
	public function getToken($module_id){
		return Option::get($module_id, 'CSR_SMSSENDINGRU_TOKEN');
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
}