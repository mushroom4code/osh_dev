<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

require_once('vendor/redsmsru/RedsmsApiSimple.php');

class ProviderREDSMSRU extends ProviderBase {
    protected $code = 'REDSMSRU';

    public function Auth() {
        // Test: ok
        if (!$this->api) {
            $login = $this->options[$this->getFieldKey('LOGIN')];
            $passw = $this->options[$this->getFieldKey('TOKEN')];

            $this->api = new \RedsmsApiSimple($login, $passw);
        }

        return $this->api;
    }

    public function getBalance(){
        // Test: ok
        try {
            $api = self::Auth();
            $res = $api->clientInfo();
            if ($res['success']) {
                return floatval($res['info']['balance']);
            } else {
                $this->LAST_ERROR = $res['info']['message'];
                return $res['info']['message'];
            }
        } catch (\Exception $e) {
            $this->LAST_ERROR = $e->getMessage();
            return $e->getMessage();
        }

    }

    public function sendSMS($arFields){
        // Test: ok
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

        try {
            $res = $api->sendSMS($arSend['phone'], $arSend['text'], $arSend['sender']);

            if ($res['success'] && empty($res['errors'])) {
                Module::addLog("{$arFields['PHONE']}: success");
                return true;
            } else {
                $errors = join("\n", $res['errors']);
                Module::addLog("{$arFields['PHONE']}: {$errors}", 'ERROR');
                return false;
            }
        } catch (\Exception $e) {
            Module::addLog("{$arFields['PHONE']}: {$e->getMessage()}", 'ERROR');
            $this->LAST_ERROR = $e->getMessage();
            return false;
        }
    }

    public function getSenders()
    {
        // Test: ok
        $arSenders = array();
        try {
            $api = self::Auth();
            $res = $api->senderNameList();
            if ($res['success']) {
                foreach ($res['items'] as $sender) {
                    $arSenders[$sender['name']] = $sender['name'];
                }
            }
        } catch (\Exception $e) {
            $this->LAST_ERROR = $e->getMessage();
        } finally {
            return $arSenders;
        }
    }

    public function showAuthForm($tabControl){
        // Test: ok
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
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>$this->getLink())));
        }
    }

    public function getLink() {
        return "https://redsms.ru";
    }
}