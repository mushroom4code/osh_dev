<?
//Файл провайдера
use Bitrix\Main\Config\Option;

//Файл с обработкой
require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');

class cwsa_redsmsru{
	
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
		return self::sendQuery($module_id, 'balance', false);
	}
	
	/************************************************
		Получаем  подпись
	************************************************/
	public function getSignature( $arParams, $module_id ){
		$api_key = Option::get($module_id, 'CSR_REDSMSRU_TOKEN');
		$login   = Option::get($module_id, 'CSR_REDSMSRU_LOGIN');
		
		$arParams["login"] = $login;
		$arParams["timestamp"] = self::getTimestamp();
		
	    ksort( $arParams );
	    reset( $arParams );
	 
	    return md5( implode( $arParams ) . $api_key );
	}
	
	/************************************************
		Отправляем сообщение
	************************************************/
	public function sendSMS($module_id, $arFields){

		$arSend = array();
		
		//Получаем текст сообщения
        $arSend['text'] = Option::get($module_id, 'CSR_TEXT_MESSAGE');
		
		//И обрабатываем переменные
		foreach($arFields as $key=>$field){
            $arSend['text'] = str_replace('#'.$key.'#', $field, $text);
		}
				
		//Траснлит
		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
            $arSend['text'] = self::transliterate($arSend['text']);
		}
		
		//Готовим сообщение
		$arSend['phone'] = $arFields["PHONE"];
		
		//Отправитель
        $arSend['sender'] = Option::get($module_id, 'CSR_REDSMSRU_SENDERS', NULL);
		
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
		$reply = self::sendQuery($module_id, 'senders', false);	

		$arSenders = array();	
		
		if(isset($reply) && is_array($reply) && sizeof($reply) > 0){
			foreach($reply as $k=>$s){
				if($s=='order'){
					$mod = ' ('.GetMessage('CSR_ON_MODERATE').')';
				}
				else{
					$mod = '';
				}
				$arSenders[$k] = $k.$mod;
			}	
		}

		if (sizeof($arSenders)>0) {
			$tabControl->AddDropDownField("CSR_REDSMSRU_SENDERS", GetMessage("CSR_SENDERS"), false, $arSenders, Option::get($module_id, 'CSR_REDSMSRU_SENDERS'));
		}
		else{
			$tabControl->AddViewField("CSR_REDSMSRU_SENDERS", "", GetMessage("CSR_SENDERS_NOT_FOUND"));
		}
	}
	
	/************************************************
		Получаем форму для админки
	************************************************/
	public function getForm($tabControl, $module_id){
		$login = Option::get($module_id, 'CSR_REDSMSRU_LOGIN');
		$token = Option::get($module_id, 'CSR_REDSMSRU_TOKEN');
		$tabControl->AddEditField("CSR_REDSMSRU_LOGIN", GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
		$tabControl->AddEditField("CSR_REDSMSRU_TOKEN", GetMessage('CSR_API_KEY'), false, array("size"=>30, "maxlength"=>255), $token);
		if(!empty($token) && !empty($login)){
			$balance = self::getBalance($module_id);
			if($balance->money > 0){
				$tabControl->AddViewField("CSR_REDSMSRU_BALANCE", GetMessage("CSR_BALANCE"), $balance->money);	
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
		Получаем коды
	************************************************/
	public function getCodes($id = '0'){
		$msgs = array(
			'-1'=>'Сообщение не найдено.',
			'000' => 'Сервис отключен',
			'1' => 'Не указана подпись',
			'2' => 'Не указан логин',
			'3' => 'Не указан текст',
			'4' => 'Не указан телефон',
			'5' => 'Не указан отправитель',
			'6' => 'Не корректная подпись',
			'7' => 'Не корректный логин',
			'8' => 'Не корректное имя отправителя',
			'9' => 'Не зарегистрированное имя отправителя',
			'10' => 'Не одобренное имя отправителя',
			'11' => 'В тексте содержатся запрещенные слова',
			'12' => 'Ошибка отправки СМС',
			'13' => 'Номер находится в стоп-листе. Отправка на этот номер запрещена',
			'14' => 'В запросе более 50 номеров',
			'15' => 'Не указана база',
			'16' => 'Не корректный номер',
			'17' => 'Не указаны ID СМС',
			'18' => 'Не получен статус',
			'19' => 'Пустой ответ',
			'20' => 'Номер уже существует',
			'21' => 'Отсутствует название',
			'22' => 'Шаблон уже существует',
			'23' => 'Не указан месяц (Формат: YYYY-MM)',
			'24' => 'Не указана временная метка',
			'25' => 'Ошибка доступа к базе',
			'26' => 'База не содержит номеров',
			'27' => 'Нет валидных номеров',
			'28' => 'Не указана начальная дата',
			'29' => 'Не указана конечная дата',
			'30' => 'Не указана дата (Формат: YYYY-MM-DD)',
		);
		
		if(!isset($msgs[$id]))
			return 'Не известный номер ответ: '.$id;
		
		return $msgs[$id];
	}
	
	/************************************************
		Отправляем в лог
	************************************************/
	public function toLog($arFields){
		$handler = new CityWebSmsAuth_Handler();
		$handler->addToLog($arFields);
	}
	
	/************************************************
		Отправляем запрос
	************************************************/
	public function sendQuery($module_id, $method, $arParams = false) {
		//Получаем логин
		$login = Option::get($module_id, 'CSR_REDSMSRU_LOGIN');
		
		//Формируем подпись
		$sign = self::getSignature( $arParams, $module_id);
		
		//Формируем ссылку на отпарвку
		$url = 'https://lk.redsms.ru/get/'.$method.'.php';
		
		//Формируем параметры
		$str = '?login='.$login.'&signature='.$sign;
		if($arParams){
		    foreach($arParams as $k=>$val){
			    $str.='&'.$k.'='.$val;
		    }	
		}
	    $str.='&timestamp='.self::getTimestamp();
	    
	    //Отправляем запрос
	    $content = file_get_contents($url.$str);
	    $content = json_decode($content);

	    //Логируем если есть ошибка
	    if($content->error){
		    return ($content->error.': '.self::getCodes($content->error));
	    }
	    else{
		    return $content;
	    }
	}
	
	/************************************************
		Получаем точку времени
	************************************************/
	public function getTimestamp() {
		return file_get_contents('https://lk.redsms.ru/get/timestamp.php');
	}
	
	/************************************************
		Переводим в транслит
	************************************************/
	public function transliterate($st) {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я',' ', '№'
        ];
        $lat = [
            'a','b','v','g','d','e','io','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Io','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','e','Yu','Ya',' ',''
        ];
		
		return str_replace($cyr, $lat, $st);
	}
}