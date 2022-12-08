<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

//Подключаем файл с обработчиком провайдера
require_once('vendor/iqsmsru/iqsmsru.php');

class cwsa_iqsmsru{
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth($module_id){
		$login = Option::get($module_id, 'CSR_IQSMSRU_LOGIN');
		$passw = Option::get($module_id, 'CSR_IQSMSRU_PASSWORD');
		
		return new iqSMSrest($login, $passw);
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){
		$api = self::Auth($module_id);
		$res = $api->getBalance();
		
		if($res == false)
			return false;
		else
			return explode(';', $res);
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

        $arSend['sender'] = Option::get($module_id, 'CSR_IQSMSRU_SENDERS', false);

		//Отправляем SMS
		$res = $api->send($arSend['phone'], $arSend['message'], $arSend['sender']);
		
		if(strpos($res, 'accepted')){
			$arFields["RESULT"] = 'success';
			$arFields["RESULT_MSG"] = $res;
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
		$api = self::Auth($module_id);
		$res = $api->getSenders();
		
		$arSenders = array();
		if($res){
			$reply = explode(PHP_EOL, $res);
			
			foreach($reply as $s){
				$arSenders[$s] = $s;
			}
		}
				
		if (sizeof($arSenders) > 0) {
			$tabControl->AddDropDownField("CSR_IQSMSRU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_IQSMSRU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_IQSMSRU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_IQSMSRU_LOGIN');
		$password = Option::get($module_id, 'CSR_IQSMSRU_PASSWORD');
		
		$tabControl->AddEditField("CSR_IQSMSRU_LOGIN", GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_IQSMSRU_PASSWORD", GetMessage('CSR_PASS'), false, array("size"=>30, "maxlength"=>255, "id"=>"password-type"), $password);
		
		
		if(!empty($login) && !empty($password)){
			$balance = self::getBalance($module_id);
			
			if(is_array($balance)){
				$tabControl->AddViewField("CSR_IQSMSRU_BALANCE", GetMessage("CSR_BALANCE"), $balance[1].' '.$balance[0]);	
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));	
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://iqsms.ru/")));	
		}
	}
	
	/************************************************
		Получаем сохраненный токен
	************************************************/
	public function getToken($module_id){
		return Option::get($module_id, 'CSR_IQSMSRU_TOKEN');
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