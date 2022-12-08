<?

namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

require_once('vendor/smscru/smsc_api.php');

class ProviderSMSCRU extends ProviderBase
{
    protected $code = 'SMSCRU';

    public function getBalance()
    {
        // Test: ok
        $arParams = array(
            "login" => $this->options[$this->getFieldKey('LOGIN')],
            "psw" => $this->options[$this->getFieldKey('TOKEN')],
        );
        return $this->sendQuery('balance', $arParams);
    }

    public function sendQuery($method, $arParams)
    {
        // Test: ok
        $arParams["login"] = $this->options[$this->getFieldKey('LOGIN')];
        $arParams["psw"] = $this->options[$this->getFieldKey('TOKEN')];
        $arParams['fmt'] = 3;
        //Формируем ссылку на отпарвку
        $url = 'http://smsc.ru/sys/' . $method . '.php';

        //Формируем параметры
        $url .= '?' . http_build_query($arParams);

        //Отправляем запрос
        $content = file_get_contents($url);
        $content = Json::decode($content);

        return $content;
    }

    public static function getErrorCodes()
    {
        return array(
            '1' => Loc::getMessage('CSR_ERROR_PARAMETER'),
            '2' => Loc::getMessage('CSR_ERROR_CREDINALS'),
            '3' => Loc::getMessage('CSR_ERROR_BALANCE'),
            '4' => Loc::getMessage('CSR_ERROR_IP_BAN'),
            '5' => Loc::getMessage('CSR_ERROR_DATE_FORMAT'),
            '6' => Loc::getMessage('CSR_ERROR_MESSAGE_BAN'),
            '7' => Loc::getMessage('CSR_ERROR_PHONE'),
            '8' => Loc::getMessage('CSR_ERROR_NOT_SENDED'),
            '9' => Loc::getMessage('CSR_ERROR_TOO_MANY'),
        );
    }

    public function sendSMS($arFields)
    {
        //Test: ok
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = GetMessage('CSR_ERROR_NO_PHONE');
            return false;
        }
        $arSend = array();
        $arSend['mes'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phones'] = $arFields["PHONE"];
        $arSend['charset'] = 'utf-8';

        if ($this->options['TRANSLIT']) {
            $arSend['translit'] = 1;
        } else {
            $arSend['translit'] = 0;
        }

        // Sender
        $arSend['sender'] = $this->options[$this->getFieldKey('SENDERS')];
        if (empty($arSend['sender']))
            $arSend['sender'] = false;

        $res = self::sendQuery('send', $arSend);

        if ($res['error_code']) {
            $arFields["RESULT"] = 'fail';
            Module::addLog("{$arFields['PHONE']}: " . $this->getError($res['error_code']), 'ERROR');
            $this->LAST_ERROR = $this->getError($res['error_code']);

            return false;
        } else {
            $arFields["RESULT"] = 'success';
            Module::addLog("{$arFields['PHONE']}: success");

            return true;
        }

    }

    public function getSenders()
    {
        // Test: ok
        $arResult = self::sendQuery('senders', array('get' => 1));
        $arSenders = array();
        if (is_array($arResult)) {
            foreach ($arResult as $send) {
                $arSenders[$send['sender']] = $send['sender'];
            }
        }

        return $arSenders;
    }

    public function showAuthForm($tabControl)
    {
        // Test: ok
        $login = $this->options[$this->getFieldKey('LOGIN')];
        $passw = $this->options[$this->getFieldKey('TOKEN')];

        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('CSR_LOGIN'), false, array("size" => 30, "maxlength" => 255), $login);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('CSR_PASSWORD'), false, array("size" => 30, "maxlength" => 255), $passw);
        if (!empty($login) && !empty($passw)) {
            $balance = $this->getBalance();
            if (is_array($balance)) {
                $tabControl->AddViewField($this->getFieldKey('BALANCE'), GetMessage("CSR_BALANCE"), $balance['balance']);
                $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
            } else {
                $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => $balance)));
            }
        } else {
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#" => "https://smsc.ru")));
        }
    }

}