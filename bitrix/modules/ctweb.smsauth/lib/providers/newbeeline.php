<?

namespace Ctweb\SMSAuth;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/* класс апи взят отсюда - https://a2p-sms.beeline.ru */
require_once('vendor/newbeeline/QTSMS.class.php');

class NewBeeline extends ProviderBase
{
    protected $code = 'NEWBEELINE';

    protected $api;

    public function __construct()
    {
        parent::__construct();

        $this->login = $this->options[$this->getFieldKey('LOGIN')];
        $this->password = $this->options[$this->getFieldKey('TOKEN')];
        if ($this->login && $this->password)
            $this->api = new \QTSMS($this->login, $this->password, 'https://a2p-sms-https.beeline.ru/proto/http/');
    }

    public function showAuthForm($tabControl)
    {
        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('TXT_PROJECT_NAME'), false, array("size" => 30, "maxlength" => 255), $this->login);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('TXT_KEY'), false, array("size" => 30, "maxlength" => 255), $this->password);
        if (!empty($this->login) && !empty($this->password)) {
            $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
        } else {
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#" => "https://beeline.ru")));
        }
    }

    public function sendSMS($arFields)
    {
        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::GetMessage('TXT_ERROR_NO_PHONE');
            return false;
        }

        $arSend = array();
        $arSend['text'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phone'] = $arFields["PHONE"];

        if ($this->options['TRANSLIT']) {
            $arSend['text'] = $this->transliterate($arSend['text']);
        }
        return $this->api->post_message($arSend['text'], $arSend['phone'], 'OSHISHA');
    }
}