<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

require_once('vendor/iqsmsru/iqsmsru.php');

class ProviderIQSMSRU extends ProviderBase {
    protected $code = 'IQSMSRU';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];

            $this->api = new \iqSMSrest($login, $passw);
        }

        return $this->api;
    }

    public function getBalance(){
        // Test: ok
        $api = self::Auth();
        $res = $api->getBalance();

        if (mb_strpos($res, '{') === 0) {
            $res = Json::decode($res);

            if($res['status'] == 'error') {
                $this->LAST_ERROR = $res['description'];
                return false;
            } else {
                foreach ($res['balance'] as $balance) {
                    if ($balance['type'] === 'RUB') {
                        return $balance['balance'];
                    }
                }
                return 0;
            }

        } else {
            list($currency, $balance) = explode(';', $res);
            $balance = floatval($balance);

            return $balance;
        }


    }

    public function sendSMS($arFields){
        // Test: ok
        if (strlen($arFields['PHONE']) <= 0) {
            Module::addLog(Loc::getMessage('CSR_ERROR_NO_PHONE'), 'ERROR');
            $this->LAST_ERROR = Loc::getMessage('CSR_ERROR_NO_PHONE');
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
        $sender = $this->options[$this->getFieldKey('SENDERS')];
        if(empty($sender))
            $sender = false;

        $res = $api->send($arSend['phone'], $arSend['text'], $sender);
        if (mb_strpos($res, '{') === 0) {
            $res = Json::decode($res);
            if ($res['status'] === 'error') {
                Module::addLog("{$arFields['PHONE']}: {$res['description']}", 'ERROR');
                $this->LAST_ERROR = $res['description'];
                return false;
            } else {
                Module::addLog("{$arFields['PHONE']}: {$this->getError($res['code'])}", 'ERROR');
                $this->LAST_ERROR = $this->getError($res['code']);
                return false;
            }
        } else {
            list($error, $status) = explode('=', $res);
            if ($status == 'accepted') {
                Module::addLog("{$arFields['PHONE']}: success");
                return true;
            } else {
                Module::addLog("{$arFields['PHONE']}: {$error}", 'ERROR');
                $this->LAST_ERROR = $error;
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
        if (mb_strpos($res, '{') === 0) {
            $res = Json::decode($res);

            if($res['status'] == 'error') {
                $this->LAST_ERROR = $res['description'];
            } else {
                if($res['senders']){
                    foreach($res['senders'] as $s){
                        $arSenders[$s] = $s;
                    }
                }
            }
        } else {
            $res = explode("\n", $res);
            $arSenders = array_merge($arSenders, array_combine($res, $res));
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