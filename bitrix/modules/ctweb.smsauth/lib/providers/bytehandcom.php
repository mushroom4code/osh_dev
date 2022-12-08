<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

require_once('vendor/bytehandcom/Bytehand.php');

class ProviderBYTEHANDCOM extends ProviderBase {
    protected $code = 'BYTEHANDCOM';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $token = $this->options[$this->getFieldKey('TOKEN')];
            $sender = $this->options[$this->getFieldKey('SENDERS')];

            $this->api = new \ByteHandApi(array('id' => $login, 'key' => $token, 'from'=> $sender));
        }

        return $this->api;
    }

    static public function getErrorCodes()
    {
        return array(
            '-1' => Loc::getMessage('PROVIDER_ERROR_-1'),
            '1' => Loc::getMessage('PROVIDER_ERROR_1'),
            '2' => Loc::getMessage('PROVIDER_ERROR_2'),
            '3' => Loc::getMessage('PROVIDER_ERROR_3'),
            '4' => Loc::getMessage('PROVIDER_ERROR_4'),
            '5' => Loc::getMessage('PROVIDER_ERROR_5'),
            '6' => Loc::getMessage('PROVIDER_ERROR_6'),
            '7' => Loc::getMessage('PROVIDER_ERROR_7'),
            '8' => Loc::getMessage('PROVIDER_ERROR_8'),
            '9' => Loc::getMessage('PROVIDER_ERROR_9'),
            '10' => Loc::getMessage('PROVIDER_ERROR_10'),
            '11' => Loc::getMessage('PROVIDER_ERROR_11'),
            '12' => Loc::getMessage('PROVIDER_ERROR_12'),
            '13' => Loc::getMessage('PROVIDER_ERROR_13'),
            '14' => Loc::getMessage('PROVIDER_ERROR_14'),
            '15' => Loc::getMessage('PROVIDER_ERROR_15'),
            '16' => Loc::getMessage('PROVIDER_ERROR_16'),
            '17' => Loc::getMessage('PROVIDER_ERROR_17'),
            '18' => Loc::getMessage('PROVIDER_ERROR_18'),
            '19' => Loc::getMessage('PROVIDER_ERROR_19'),
            '20' => Loc::getMessage('PROVIDER_ERROR_20'),
            '21' => Loc::getMessage('PROVIDER_ERROR_21'),
            '22' => Loc::getMessage('PROVIDER_ERROR_22'),
        );
    }

    public function getBalance(){
        // Test: ok
        $client = $this->Auth();
        $balance = $client->getBalance();
        if (is_array($balance) && $balance['error']) {
            return $this->getError($balance['error']->status);
        } else {
            $balance = floatval($balance);
        }

        return $balance;
	}

	public function sendSMS($arFields){
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::GetMessage('CSR_ERROR_NO_PHONE');
            return false;
        }

        $client = $this->Auth();
        $arSend = array();
        $arSend['message'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phone'] = $arFields["PHONE"];


        if($this->options['TRANSLIT']){
            $arSend['message'] = $this->transliterate($arSend['message']);
        }

		//Отправляем SMS
		$res = $client->send($arSend['phone'], $arSend['message']);

		if(!is_array($res)){
            Module::addLog("{$arFields['PHONE']}: success");
            return true;
        } else {
            Module::addLog("{$arFields['PHONE']}: {$res['error']->description}", 'ERROR');
            $this->LAST_ERROR = $res['error']->description;
            return false;
        }
	}

	public function getSenders() {
        // Test: ok
        $arSenders = array('SMS-INFO' => 'SMS-INFO');

        $client = $this->Auth();
        $res = $client->getSenders();
        $res = Json::decode($res);

        if (isset($res[0])) {
            foreach($res as $send){
                switch ($send['state']) {
                    case 'NEW':
                        $mod = ' ('.GetMessage('CSR_ON_MODERATE').')';
                        break;
                    case 'REJECTED':
                        $mod = ' ('.GetMessage('CSR_ON_REJECTED').')';
                        break;
                    default:
                        $mod = '';
                }

                $arSenders[$send['text']] = $send['text'].$mod;
            }
        } else {
            $this->LAST_ERROR = $this->getError($res['status']);
        }

        return $arSenders;
    }

    public function showAuthForm($tabControl){
        // Test: ok
        $login = $this->options[$this->getFieldKey('LOGIN')];
        $apikey = $this->options[$this->getFieldKey('TOKEN')];

        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('CSR_CLIENT_ID'), false, array("size"=>30, "maxlength"=>255), $login);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('CSR_API_KEY_V1'), false, array("size"=>30, "maxlength"=>255), $apikey);

        if(!empty($login) && !empty($apikey)){
            $balance = $this->getBalance();

            if (is_float($balance)){
                $tabControl->AddViewField($this->getFieldKey('BALANCE'), GetMessage("CSR_BALANCE"), $balance);
                $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
            } else {
                $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
            }
        } else {
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=> $this->getLink())));
        }
    }

    public function getLink() {
        return "https://www.bytehand.com";
    }
}