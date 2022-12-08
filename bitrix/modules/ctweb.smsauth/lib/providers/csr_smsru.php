<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

//Подключаем файл с обработчиком провайдера
require_once('vendor/smsru/autoload.php');

class csr_smsru{
	
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
		//Получаем ключ
		$apiId = Option::get($module_id, 'CSR_SMSRU_TOKEN');
		
		//Подключаемся
		$client = new \Zelenin\SmsRu\Api(new \Zelenin\SmsRu\Auth\ApiIdAuth($apiId));
		
		$balance = $client->myBalance();

		if($balance->code == 100){
			return $balance->balance;
		}
		else{
			$msg = self::GetAttr($balance, 'availableDescriptions');
			return $msg[$balance->code];
		}
	}
	
	/************************************************
		Получаем коды
	************************************************/
	public function getCodes($id = '0'){
		$msgs = array(
			"-1"=>"Сообщение не найдено.",
			"100"=>"Сообщение находится в нашей очереди",
			"101"=>"Сообщение передается оператору",
			"102"=>"Сообщение отправлено (в пути)",
			"103"=>"Сообщение доставлено",
			"104"=>"Не может быть доставлено: время жизни истекло",
			"105"=>"Не может быть доставлено: удалено оператором",
			"106"=>"Не может быть доставлено: сбой в телефоне",
			"107"=>"Не может быть доставлено: неизвестная причина",
			"108"=>"Не может быть доставлено: отклонено",
			"130"=>"Не может быть доставлено: превышено количество сообщений на этот номер в день",
			"131"=>"Не может быть доставлено: превышено количество одинаковых сообщений на этот номер в минуту",
			"132"=>"Не может быть доставлено: превышено количество одинаковых сообщений на этот номер в день",
			"200"=>"Неправильный api_id",
		    "201"=> "Не хватает средств на лицевом счету.",
		    "202"=> "Неправильно указан получатель.",
		    "203"=> "Нет текста сообщения.",
		    "204"=> "Имя отправителя не согласовано с администрацией.",
		    "205"=> "Сообщение слишком длинное (превышает 8 СМС).",
		    "206"=> "Будет превышен или уже превышен дневной лимит на отправку сообщений.",
		    "207"=> "На этот номер (или один из номеров) нельзя отправлять сообщения, либо указано более 100 номеров в списке получателей.",
		    "208"=> "Параметр time указан неправильно.",
		    "209"=> "Вы добавили этот номер (или один из номеров) в стоп-лист.",
			"210"=>"Используется GET, где необходимо использовать POST",
			"211"=>"Метод не найден",
			"220"=>"Сервис временно недоступен, попробуйте чуть позже.",
			"230"=>"Превышен общий лимит количества сообщений на этот номер в день.",
			"231"=>"Превышен лимит одинаковых сообщений на этот номер в минуту.",
			"232"=>"Превышен лимит одинаковых сообщений на этот номер в день.",
			"300"=>"Неправильный token (возможно истек срок действия, либо ваш IP изменился)",
			"301"=>"Неправильный пароль, либо пользователь не найден",
			"302"=>"Пользователь авторизован, но аккаунт не подтвержден (пользователь не ввел код, присланный в регистрационной смс)"
		);
		
		if(!isset($msgs[$id]))
			return 'Не известный номер ответ: '.$id;
		
		return $msgs[$id];
	}
	
	/************************************************
		Отправляем сообщение
	************************************************/
	public function sendSMS($module_id, $arFields){

		//Получаем ключ
		$apiId = Option::get($module_id, 'CSR_SMSRU_TOKEN');
		$apiId = $apiId;
		
		//Подключаемся
		$client = new \Zelenin\SmsRu\Api(new \Zelenin\SmsRu\Auth\ApiIdAuth($apiId));
		
		//Получаем текст сообщения
		$text = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
			$text = str_replace('#'.$key.'#', $field, $text);
		}

		//Готовим сообщение
		$sms = new \Zelenin\SmsRu\Entity\Sms($arFields["PHONE"], $text);
		
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$sms->translit = 1;
		}
		else{
			$sms->translit = 0;
		}

        if(Option::get($module_id, 'CSR_SMSRU_TEST_SMS') == 1) {
            $sms->test = 1;
        }
        else {
            $sms->test = 0;
        }
		
		//Партнерская программа
		$sms->partner_id=182951;
		
		//Отправитель
		$sedner = Option::get($module_id, 'CSR_SMSRU_SENDERS');
		if(!empty($sedner)){
			$sms->from=$sedner;
		}
		
		//Отправляем SMS
		$res = $client->smsSend($sms);

		$status_code = $res->code;
		$arFields["RESULT_MSG"] = $status_code.': '.self::getCodes($status_code);
		
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
		
		self::toLog($arFields);
	}
	
	/************************************************
		Получаем отправителей
	************************************************/
	public function getSenders($tabControl, $module_id){		
		$token = self::getToken($module_id);
		
		$body=file_get_contents("https://sms.ru/my/senders?api_id=".$token);
		
		$reply = array_filter(explode("\n", $body));
		$code = array_shift($reply);
		
		$arSenders = array();
		foreach($reply as $s){
			$arSenders[$s] = $s;
		}
		if ($code=="100") {
			$tabControl->AddDropDownField("CSR_SMSRU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_SMSRU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_SMSRU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$token = Option::get($module_id, 'CSR_SMSRU_TOKEN');
		$tabControl->AddEditField("CSR_SMSRU_TOKEN", GetMessage('CSR_API_KEY'), false, array("size"=>30, "maxlength"=>255), $token);
		if(!empty($token)){
			$balance = self::getBalance($module_id);
			if(is_float($balance)){
				$tabControl->AddViewField("CSR_SMSRU_BALANCE", GetMessage("CSR_BALANCE"), $balance);
                $tabControl->AddCheckBoxField("CSR_SMSRU_TEST_SMS", GetMessage("CSR_TEST_SMS"), false, 1, Option::get($module_id, "CSR_SMSRU_TEST_SMS", false) == 1);
				$tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
			}
			else{
				$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
			}
		}
		else{
			$tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://ctweb.sms.ru")));	
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
}