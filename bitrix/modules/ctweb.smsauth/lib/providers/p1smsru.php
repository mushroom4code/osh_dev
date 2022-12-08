<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once('vendor/p1smsru/p1smsruapi.php');

class ProviderP1SMSRU extends ProviderBase {
    protected $code = 'P1SMSRU';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];
            $this->api = new \p1smsAPI($login, $passw);
        }

        return $this->api;
    }

    public function getBalance(){
        // Test: ok
        $api = $this->Auth();
        $res = $api->getBalance();

        if ($res->error) {
            $this->LAST_ERROR = reset($res->error);
            return reset($res->error);
        } else {
            return (float) $res->money;
        }
    }

    public function sendSMS($arFields){
        // Test: not ok
        // Reason: messages go to queue, but not sended automatically
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::GetMessage('CSR_ERROR_NO_PHONE');
            return false;
        }

        $api = $this->Auth();
        $arSend = array();
        $arSend['text'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phone'] = $arFields["PHONE"];

        if($this->options['TRANSLIT']){
            $arSend['text'] = $this->transliterate($arSend['text']);
        }

        // Sender
        $arSend['sender'] = $this->options[$this->getFieldKey('SENDERS')];
        if(empty($arSend['sender']))
            $arSend['sender'] = false;

        $res = $api->sendSMS($arSend['phone'], $arSend['text'], $arSend['sender']);

        $error = (string) $res->information;
        if($error !== 'send'){
            Module::addLog("{$arFields['PHONE']}: {$error}", 'ERROR');
            $this->LAST_ERROR = $error;
            return false;
        } else{
            Module::addLog("{$arFields['PHONE']}: success");
            return true;
        }
    }

    public function getSenders()
    {
        // Test: ok
        $arSenders = array();

        $api = self::Auth();
        $res = $api->getSenders();

        if ($res->error) {
            $this->LAST_ERROR = reset($res->error);
        } else {
            foreach ((array) $res->list_originator->originator as $sender) {
                $arSenders[$sender] = $sender;
            }
        }

        return $arSenders;
    }

    public function showAuthForm($tabControl){
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
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://p1sms.ru")));
        }
    }
}