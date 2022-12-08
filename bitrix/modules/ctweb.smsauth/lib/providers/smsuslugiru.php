<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

require_once('vendor/smssendingru/smssendingru.php');

class ProviderSMSUSLUGIRU extends ProviderBase {
    protected $code = 'SMSUSLUGIRU';

    public function Auth() {
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];

            $this->api =  new \smssendingruAPI($login, $passw);
        }

        return $this->api;
    }

    public function getBalance(){
        $api = $this->Auth();
        $res = $api->balance();

        if ($res) {
            return $res;
        } else {
            $this->LAST_ERROR = $this->getError();
            return $this->getError();
        }
    }

    public function sendSMS($arFields){
        $api = self::Auth();
        $arSend = array();
        $arSend['text'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arPhone = $arFields["PHONE"];

        if($this->options['TRANSLIT']){
            $arSend['text'] = $this->transliterate($arSend['text']);
        }

        // Sender
        $arSend['source'] = $this->options[$this->getFieldKey('SENDERS')];
        if(empty($arSend['source']))
            $arSend['source'] = false;

        $res = $api->send($arSend, $arPhone);

        if($res['code'] == 1){
            Module::addLog("{$arFields['PHONE']}: success");
            return true;
        } else {
            Module::addLog("{$arFields['PHONE']}: {$res["descr"]}", 'ERROR');
            $this->LAST_ERROR = $res["descr"];
            return false;
        }
    }

    public function getSenders()
    {
        $api = self::Auth();
        $res = $api->getSenders();
        $reply = Json::decode($res);

        $arSenders = array();

        if($reply->code == 1) {
            foreach ($reply->source as $s) {
                $arSenders[$s] = $s;
            }
        } else {
            $this->LAST_ERROR = $reply['descr'];
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
            if($balance !== false){
                $tabControl->AddViewField($this->getFieldKey('BALANCE'), GetMessage("CSR_BALANCE"), $balance);
                $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
            } else {
                $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
            }
        }
        else{
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://smsuslugi.ru")));
        }
    }

    public function getDefaultOptions() {
        return array(
            $this->getFieldKey('BALANCE') => '',
            $this->getFieldKey('LOGIN') => '',
            $this->getFieldKey('TOKEN') => '',
            $this->getFieldKey('SENDERS') => ''
        );
    }
}