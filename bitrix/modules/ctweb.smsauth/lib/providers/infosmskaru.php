<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once('vendor/iqsmsru/iqsmsru.php');

class ProviderINFOSMSKARU extends ProviderBase {
    protected $code = 'INFOSMSKARU';

    public function getBalance(){
        // Test: ok
        $login = $this->options[$this->getFieldKey('LOGIN')];
        $passw = $this->options[$this->getFieldKey('TOKEN')];

        $res = file_get_contents('http://api.infosmska.ru/interfaces/getbalance.ashx?login='.$login.'&pwd='.$passw);

        if (is_numeric($res))
            return floatval($res);
        else {
            $this->LAST_ERROR = $res;
            return $res;
        }
    }

	public function sendSMS($arFields) {
        // Test: ok
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::getMessage('CSR_ERROR_NO_PHONE');
            return false;
        }

        $arParams = array();
        $arParams['login'] = $this->options[$this->getFieldKey('LOGIN')];
        $arParams['pwd'] = $this->options[$this->getFieldKey('TOKEN')];
        $arParams['phones'] = $arFields["PHONE"];

        $arParams['message'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);

        if($this->options['TRANSLIT']){
            $arParams['translit'] = 1;
        }

        $sender = $this->options[$this->getFieldKey('SENDERS')];
        if(!empty($sender)){
            $arParams['sender'] = $sender;
        }

        $query = http_build_query($arParams);
		$res = file_get_contents('http://api.infosmska.ru/interfaces/SendMessages.ashx?'.$query);

		if (strpos($res, 'Ok') !== false) {
            Module::addLog("{$arFields['PHONE']}: success");
            return true;
        } else {
            Module::addLog("{$arFields['PHONE']}: {$res}", 'ERROR');
            $this->LAST_ERROR = $res;
            return false;
        }
	}

    public function getSenders()
    {
        // Test: ok
        return $this->options[$this->getFieldKey('SENDERS')];
    }

    public function showSendersForm($tabControl){
        //Test: ok
        $sender = $this->getSenders();

        $tabControl->AddEditField($this->getFieldKey('SENDERS'), GetMessage("CSR_SENDERS"), false, array("size"=>30, "maxlength"=>255), $sender);

    }

    public function showAuthForm($tabControl) {
        // Test: ok
        $login = $this->options[$this->getFieldKey('LOGIN')];
        $passw = $this->options[$this->getFieldKey('TOKEN')];

        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('CSR_PASSWORD'), false, array("size"=>30, "maxlength"=>255), $passw);
        if(!empty($login) && !empty($passw)){
            $balance = $this->getBalance();
            if(is_float($balance)){
                $tabControl->AddViewField($this->getFieldKey('BALANCE'), GetMessage("CSR_BALANCE"), $balance);
                $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
            } else {
                $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
            }
        }
        else{
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://infosmska.ru")));
        }
    }
}