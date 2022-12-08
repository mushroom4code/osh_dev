<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once('vendor/smsaeroru/SmsaeroApi.class.php');

class ProviderSMSAERORU extends ProviderBase {
    protected $code = 'SMSAERORU';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];
            $sender  = $this->options[$this->getFieldKey('SENDERS')];

            if(empty($sender))
                $sender = false;

            $this->api = new \SmsaeroApiV2($login, $passw, $sender);
        }

        return $this->api;
    }

    public function getBalance(){
        // test: ok
        $api = $this->Auth();
        $res = $api->balance();

        if (!$res['success']) {
            $this->LAST_ERROR = $res['message'];
            return $res['message'];
        } else {
            return floatval($res['data']['balance']);
        }
    }

    public function sendSMS($arFields) {
        // Test: ok
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::getMessage('CSR_ERROR_NO_PHONE');
            return false;
        }

        $api = $this->Auth();
        $arSend = array();
        $arSend['text'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phone'] = $arFields["PHONE"];

        if($this->options['TRANSLIT']){
            $arSend['text'] = $this->transliterate($arSend['text']);
        }
        if($this->options['TRANSLIT']){
            $arSend['translit'] = 1;
        } else{
            $arSend['translit'] = 0;
        }

        $channel = $this->options[$this->getFieldKey('CHANNEL')];
        if(empty($channel))
	        $channel = 'DIGITAL';

        $res = $api->send($arSend['phone'], $arSend['text'], $channel);
        if ($res['success']) {
            Module::addLog("{$arFields['PHONE']}: success");
            return true;
        } else {
            Module::addLog("{$arFields['PHONE']}: {$res['message']}", 'ERROR');
            $this->LAST_ERROR = $res['message'];
            return false;
        }
    }

    public function getSenders()
    {
        // Test: ok
        $arSenders = array();
        $api = $this->Auth();
        $res = $api->sign_list();
        if (!$res['success']) {
            $this->LAST_ERROR = $res['message'];
        } else {
            foreach ($res['data'] as $sign) {
                if ($sign['status'] == 1) {
                    $arSenders[$sign['name']] = $sign['name'];
                }
            }
        }

        return $arSenders;
    }

    public function showAuthForm($tabControl){
        //Test: ok
        $login = $this->options[$this->getFieldKey('LOGIN')];
        $passw = $this->options[$this->getFieldKey('TOKEN')];

        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('CSR_LOGIN'), false, array("size"=>30, "maxlength"=>255), $login);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('CSR_API_KEY'), false, array("size"=>30, "maxlength"=>255), $passw);
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
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://smsaero.ru")));
        }
    }

	public function showSendersForm($tabControl) {
		parent::showSendersForm($tabControl);

		$tabControl->AddDropdownField($this->getFieldKey('CHANNEL'), GetMessage("CSR_CHANNEL"), false, $this->getChannelList(), $this->options[$this->getFieldKey('CHANNEL')]);
	}

    private function getChannelList() {
    	return array(
    		'INFO' => GetMessage("CSR_CHANNEL_INFO"),
    		'DIGITAL' => GetMessage("CSR_CHANNEL_DIGITAL"),
    		'INTERNATIONAL' => GetMessage("CSR_CHANNEL_INTERNATIONAL"),
    		'DIRECT' => GetMessage("CSR_CHANNEL_DIRECT"),
    		'SERVICE' => GetMessage("CSR_CHANNEL_SERVICE"),
	    );
    }

	public function getDefaultOptions() {
		return array(
			$this->getFieldKey('BALANCE') => '',
			$this->getFieldKey('LOGIN') => '',
			$this->getFieldKey('TOKEN') => '',
			$this->getFieldKey('SENDERS') => '',
			$this->getFieldKey('CHANNEL') => '',
		);
	}
}