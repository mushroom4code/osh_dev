<?
use Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ctweb.smsauth/include.php');
require_once('vendor/mainsms/mainsmsapi.php');

class cwsa_mainsms{

    private $api;

    public function __construct($module_id){
        $login = Option::get($module_id, 'LOGIN');
        $key = Option::get($module_id, 'TOKEN');
        $debug = Option::get($module_id, 'DEBUG');

        $this->api = new \MainSMSAPI($login, $key, false, $debug);
	}

	public function sendSMS($module_id, $arFields){
		$api = self::Auth($module_id);

		$arSend = array();
        $arSend['message'] = Option::get($module_id, 'CSR_TEXT_MESSAGE');

		foreach($arFields as $key=>$field){
            $arSend['message'] = str_replace('#'.$key.'#', $field, $arSend['message']);
		}

		$arSend['phones'] = $arFields["PHONE"];

		if(Option::get($module_id, 'CSR_TRANSLIT') == 1){
			$CSR_HANDLER = new CityWebSmsAuth_Handler();
            $arSend['message'] = $CSR_HANDLER->csr_transliterate($arSend['message']);
		}

		//�����������
        $arSend['sender'] = Option::get($module_id, 'CSR_P1SMSRU_SENDERS');

		$res = $api->sendSMS($arSend['phones'], $arSend['message'], $arSend['sender']);
		$result = (string)$res->information;

		if($result == 'send'){
			$arFields["RESULT"] = 'success';
			$arFields["RESULT_MSG"] = $result;
		}
		else{
		    $arFields["RESULT"] = 'fail';
		    self::toLog($result);
		}

		self::toLog($arFields);
	}
	

}