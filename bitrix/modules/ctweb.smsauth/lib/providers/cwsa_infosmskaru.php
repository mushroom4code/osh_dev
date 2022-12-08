<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

//Подключаем файл с обработчиком провайдера
require_once('vendor/iqsmsru/iqsmsru.php');

class cwsa_infosmskaru{
	
	public function __construct($module_id){
	}

	/************************************************
		Авторизация
	************************************************/
	public function Auth($module_id){
		$login = Option::get($module_id, 'CSR_INFOSMSKARU_LOGIN');
		$passw = Option::get($module_id, 'CSR_INFOSMSKARU_PASSWORD');
		
		return new iqSMSrest($login, $passw);
	}

	/************************************************
		Получить баланс
	************************************************/	
	public function getBalance($module_id){
		$login = Option::get($module_id, 'CSR_INFOSMSKARU_LOGIN');
		$passw = Option::get($module_id, 'CSR_INFOSMSKARU_PASSWORD');
		
		return file_get_contents('http://api.infosmska.ru/interfaces/getbalance.ashx?login='.$login.'&pwd='.$passw);
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
		$login = Option::get($module_id, 'CSR_INFOSMSKARU_LOGIN');
		$passw = Option::get($module_id, 'CSR_INFOSMSKARU_PASSWORD');
		
		$arSend = array();
		//Получаем текст сообщения
		$arSend['message'] = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
			$arSend['message'] = str_replace('#'.$key.'#', $field, $arSend['message']);
		}
		
		$arSend['phones'] = $arFields["PHONE"];

		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$arSend['translit'] = 1;
		}

		//Отправитель
        $arSend['sender'] = Option::get($module_id, 'CSR_INFOSMSKARU_SENDERS', NULL);

		
		//Собираем строку для отправки
		$query = '?login='.$login.'&pwd='.$passw;
		foreach($arSend as $k=>$val){
			$query.='&'.$k.'='.$val;
		}
		
		//Отправляем SMS
		$res = file_get_contents('http://api.infosmska.ru/interfaces/SendMessages.ashx'.$query);
		
/*
		switch ($status_code) {
		    case 100:
		    case 101:
		    case 102:
		    case 103:
		    	//Сообщение отправлено успешно
		    	$arFields["RESULT"] = 'success';
		        break;
		    default:
		    	//Ошибка при отправке сообщения
		    	$arFields["RESULT"] = 'fail';
		    	self::toLog($status_code.': '.self::getCodes($status_code));
		}
*/
		
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
			$tabControl->AddDropDownField("CSR_INFOSMSKARU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_INFOSMSKARU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_INFOSMSKARU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_INFOSMSKARU_LOGIN');
		$password = Option::get($module_id, 'CSR_INFOSMSKARU_PASSWORD');
		
		$tabControl->AddEditField("CSR_INFOSMSKARU_LOGIN", GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_INFOSMSKARU_PASSWORD", GetMessage('CSR_PASS'), false, array("size"=>30, "maxlength"=>255, "id"=>"password-type"), $password);
		
		
		if(!empty($login) && !empty($password)){
			$balance = self::getBalance($module_id);
			
			if(is_array($balance)){
				$tabControl->AddViewField("CSR_INFOSMSKARU_BALANCE", GetMessage("CSR_BALANCE"), $balance[1].' '.$balance[0]);	
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
		return Option::get($module_id, 'CSR_INFOSMSKARU_TOKEN');
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