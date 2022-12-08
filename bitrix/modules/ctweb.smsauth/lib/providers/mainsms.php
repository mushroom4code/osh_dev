<?
namespace Ctweb\SMSAuth;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

require_once('vendor/mainsms/mainsmsapi.php');

class ProviderMAINSMS extends ProviderBase {
    protected $code = 'MAINSMS';

    protected $api;

    public function __construct()
    {
        parent::__construct();

        $login = $this->options[$this->getFieldKey('LOGIN')];
        $key = $this->options[$this->getFieldKey('TOKEN')];
        $debug = $this->options['DEBUG'] ? true : false;

        $this->api = new \MainSMSAPI($login, $key, false, $debug);
    }

    public function getBalance(){
        return (float)$this->api->getBalance();
    }

    public function showAuthForm($tabControl){
        $project = $this->options[$this->getFieldKey('LOGIN')];
        $key = $this->options[$this->getFieldKey('TOKEN')];

        $tabControl->AddEditField($this->getFieldKey('LOGIN'), GetMessage('TXT_PROJECT_NAME'), false, array("size"=>30, "maxlength"=>255), $project);
        $tabControl->AddEditField($this->getFieldKey('TOKEN'), GetMessage('TXT_KEY'), false, array("size"=>30, "maxlength"=>255), $key);
        if(!empty($project) && !empty($key)){
            $balance = $this->getBalance();
            if(is_float($balance)){
                $tabControl->AddViewField($this->getFieldKey('BALANCE'), GetMessage("CSR_BALANCE"), $balance);
                $tabControl->AddViewField("CSR_CONNECT_OK", "", GetMessage("CSR_CONNECT_OK"));
            } else {
                $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_CHECK_API", Array("#ERROR_TEXT#" => GetMessage("CSR_BALANCE").': '. $balance)));
            }
        } else{
            $tabControl->AddViewField("CSR_NOTE_SMS_PROVIDER", "", GetMessage("CSR_NEED_REGISTER", array("#SMSLINK#"=>"https://mainsms.ru")));
        }

    }

    public function sendSMS($arFields){

        if (strlen($arFields['PHONE']) <= 0) {
            $this->LAST_ERROR = Loc::GetMessage('TXT_ERROR_NO_PHONE');
            return false;
        }

        $arSend = array();
        $arSend['text'] = $this->PrepareMessage($this->options['TEXT_MESSAGE'], $arFields);
        $arSend['phone'] = $arFields["PHONE"];

        if($this->options['TRANSLIT']){
            $arSend['text'] = $this->transliterate($arSend['text']);
        }

        return $this->api->sendSMS($arSend['phone'], $arSend['text'],  'Oshisha');
    }
}