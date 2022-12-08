<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

    require_once('vendor/prostorsmsru/prostorsmsru.php');

class ProviderPROSTORSMSRU extends ProviderBase {
    protected $code = 'PROSTORSMSRU';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];

            $this->api = new \prostorSMSrest($login, $passw);
        }

        return $this->api;
    }

    public function getBalance(){
        // Test: ok
        $api = self::Auth();
        $res = $api->getBalance();

        if (strpos($res, '{') === 0) {
            $res = Json::decode($res);
            $this->LAST_ERROR = $res['description'];
            return $res['description'];
        } else {
            $balance = explode(';', $res);
            return floatval($balance[1]);
        }
    }

    public function sendSMS($arFields){
        // Test: ok
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::GetMessage('CSR_ERROR_NO_PHONE');
            return false;
        }

        $api = self::Auth();
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

        $res = $api->send($arSend['phone'], $arSend['text'], $arSend['sender']);
        if (strpos($res, '{') === 0) {
            $res = Json::decode($res);
            $this->LAST_ERROR = $res['description'];
            return false;
        } else {
            list($status, $description) = explode(';', $res);

            if ($status === 'accepted') {
                Module::addLog("{$arFields['PHONE']}: success");
                return true;
            } else {
                Module::addLog("{$arFields['PHONE']}: {$description}", 'ERROR');
                $this->LAST_ERROR = $description;
                return false;
            }
        }
    }

    public function getSenders()
    {
        // Test: ok
        $arSenders = array();
        $api = self::Auth();
        $res = $api->getSenders();

        if (strpos($res, '{') === 0) {
            $res = Json::decode($res);
            $this->LAST_ERROR = $res['description'];
        } else {
            $senders = explode("\n", $res);
            foreach ($senders as $arS) {
                list($s) = explode(';', $arS);
                $arSenders[$s] = $s;
            }
        }

        return $arSenders;
    }

    public function showAuthForm($tabControl){
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
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://iqsms.ru")));
        }
    }
}