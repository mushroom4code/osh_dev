<?

namespace Ctweb\SMSAuth;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;


Loc::loadMessages(__FILE__);

\CModule::AddAutoloadClasses(
    "ctweb.smsauth",
    array(
        "\\Ctweb\\SMSAuth\\Manager" => "lib/manager.php",
        "\\Ctweb\\SMSAuth\\ProviderBase" => "lib/provider.base.php",
        "\\Ctweb\\SMSAuth\\CAdminForm" => "lib/cadminform.php",
    )
);

require_once(__DIR__ . '/vendor/autoload.php');

class Module {
    const MODULE_ID = 'ctweb.smsauth';
    const LOG_TYPE_NONE = 0;
    const LOG_TYPE_MESSAGES = 1;
    const LOG_TYPE_ERRORS = 2;
    const LOG_TYPE_ALL = 3;

    const LOG_FILE = __DIR__ . '/logs/module.log';

    static function isActive() {
        return Option::get(self::MODULE_ID, 'ACTIVE', 0);
    }

    static public function getLogOptions() {
        return array(
            self::LOG_TYPE_NONE => Loc::getMessage('LOG_TYPE_NONE'),
            self::LOG_TYPE_MESSAGES => Loc::getMessage('LOG_TYPE_MESSAGES'),
            self::LOG_TYPE_ERRORS => Loc::getMessage('LOG_TYPE_ERRORS'),
            self::LOG_TYPE_ALL => Loc::getMessage('LOG_TYPE_ALL'),
        );
    }

    /**
     * @return bool
     */
    public static function CoreHasOwnPhoneAuth()
    {
        return (bool) CheckVersion(ModuleManager::getVersion('main'), '18.5.0');
    }

    /**
     * @emits OnGetPhoneFieldList ( &array )
     * @return array
     */
    static public function getPhoneFieldList() {
        $result = array();

        if (self::CoreHasOwnPhoneAuth()) {
            $result["PHONE_NUMBER"] = Loc::getMessage("CW_REG_FIELD_PHONE_NUMBER");
        }

        $result["PERSONAL_PHONE"] = Loc::GetMessage("FIELD_PERSONAL_PHONE");
        $result["PERSONAL_FAX"] = Loc::GetMessage("FIELD_PERSONAL_FAX");
        $result["PERSONAL_MOBILE"] = Loc::GetMessage("FIELD_PERSONAL_MOBILE");
        $result["PERSONAL_PAGER"] = Loc::GetMessage("FIELD_PERSONAL_PAGER");

        $result["WORK_PHONE"] = Loc::GetMessage("FIELD_WORK_PHONE");
        $result["WORK_FAX"] = Loc::GetMessage("FIELD_WORK_FAX");
        $result["WORK_PAGER"] = Loc::GetMessage("FIELD_WORK_PAGER");

        $event = new \Bitrix\Main\Event(self::MODULE_ID, "OnGetPhoneFieldList", array(&$result));
        $event->send();

        return $result;
    }

    /**
     * @return array
     */
    static public function getLogs() {
        if (!file_exists(self::LOG_FILE)) {
            if (!is_dir(dirname(self::LOG_FILE))) {
                mkdir(dirname(self::LOG_FILE), 0755, true);
            }
            touch(self::LOG_FILE);
        }

        $result = file(self::LOG_FILE);
        return \array_map(function ($e) {
            list($type, $time, $message) = explode(' | ', $e, 3);
            return [
                'TIMESTAMP' => $time,
                'TYPE' => $type,
                'TEXT' => $message,
            ];
        }, $result);
    }

    static public function addLog($text, $type = 'MESSAGE') {
        $logOption = intval(Option::get(self::MODULE_ID, 'LOG_MESSAGES', self::LOG_TYPE_NONE));
        if ($type === 'ERROR' && in_array($logOption, array(self::LOG_TYPE_ERRORS, self::LOG_TYPE_ALL)) ||
            $type === 'MESSAGE' && in_array($logOption, array(self::LOG_TYPE_MESSAGES, self::LOG_TYPE_ALL))
        ) {
            file_put_contents(self::LOG_FILE, sprintf(
                "%s | %s | %s\n",
                    $type,
                    date('d.m.Y H:i:s'),
                    strtr($text, [
                        "\n" => '\n',
                        "\r" => '\r',
                    ])
                ),FILE_APPEND);
        }
    }

    static public function clearLog() {
        if (!file_exists(self::LOG_FILE))
            return;

        unlink(self::LOG_FILE);
    }


    /**
     * @return array
     */
    static public function getOptions() {
        $arModuleOptions = Option::getForModule(self::MODULE_ID);
        $arResult = array_merge(self::getDefaultOptions(), $arModuleOptions);

        foreach ($arResult as $key => $value) {
            $value = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($value)) {
                $arResult[$key] = $value;
            }
        }

        return $arResult;
    }

    /**
     * @return array
     */
    static public function getDefaultOptions() {
        return array(
            'ACTIVE' => 0,
            'LOG_MESSAGES' => 2,
            'DEBUG' => 0,
            'PHONE_FIELD' => key(self::getPhoneFieldList()),
            'CODE_LENGTH' => 5,
            'ALPHABET' => '',
            'MIN_PHONE_LENGTH' => 5,
            'NEW_LOGIN_AS' => 'timestamp',
            'NEW_EMAIL_AS' => 'timestamp',
            'TIME_REUSE' => 60,
            'TIME_EXPIRE' => 180,
            'PROVIDER' => key(self::getProviderList()),
            'TRANSLIT' => 0,
            'ALLOW_REGISTER_AUTH' => 0,
            'REGISTER_FIELDS' => array(key(self::getPhoneFieldList())),
            'TEXT_MESSAGE' => Loc::getMessage("CWSA_SMS_DEFAULT_MESSAGE_TEXT"),
            'NO_PHONE_ERRORS' => 0,
        );
    }

    /**
     * @return array
     */
    static public function getNewLoginAsList() {
        return array(
            'TIMESTAMP' => Loc::getMessage("SWSA_NEW_LOGIN_AS_TIMESTAMP"),
            'EMAIL' => Loc::getMessage("SWSA_NEW_LOGIN_AS_EMAIL"),
            'PHONE' => Loc::getMessage("SWSA_NEW_LOGIN_AS_PHONE"),
        );
    }

    /**
     * @return array
     */
    static public function getNewEmailAsList() {
        return array(
            'TIMESTAMP' => Loc::getMessage("SWSA_NEW_EMAIL_AS_TIMESTAMP"),
            'PHONE' => Loc::getMessage("SWSA_NEW_EMAIL_AS_PHONE"),
        );
    }

    /**
     * @emits OnGetProviderList ( &array )
     * @return array
     */
    static public function getProviderList() {
        $arResult = array(
            'SMSRU' => array('NAME' => 'sms.ru', 'PATH' => __DIR__ . '/lib/providers/smsru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderSMSRU'),
            'SMSCRU' => array('NAME' => 'smsc.ru', 'PATH' => __DIR__ . '/lib/providers/smscru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderSMSCRU'),
            'BYTEHANDCOM' => array('NAME' => 'bytehand.com', 'PATH' => __DIR__ . '/lib/providers/bytehandcom.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderBYTEHANDCOM'),
            'PROSTORSMSRU' => array('NAME' => 'prostorsms.ru', 'PATH' => __DIR__ . '/lib/providers/prostorsmsru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderPROSTORSMSRU'),
            'SMSAERORU' => array('NAME' => 'smsaero.ru', 'PATH' => __DIR__ . '/lib/providers/smsaeroru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderSMSAERORU'),
            'IQSMSRU' => array('NAME' => 'iqsms.ru', 'PATH' => __DIR__ . '/lib/providers/iqsmsru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderIQSMSRU'),
            'P1SMSRU' => array('NAME' => 'p1sms.ru', 'PATH' => __DIR__ . '/lib/providers/p1smsru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderP1SMSRU'),
            'REDSMSRU' => array('NAME' => 'redsms.ru', 'PATH' => __dir__ . '/lib/providers/redsmsru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderREDSMSRU'),
            'INFOSMSKARU' => array('NAME' => 'infosmska.ru', 'PATH' => __DIR__ . '/lib/providers/infosmskaru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderINFOSMSKARU'),
            'MAINSMS' => array('NAME' => 'mainsms.ru', 'PATH' => __DIR__ . '/lib/providers/mainsms.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderMAINSMS'),
            'SMSUSLUGIRU' => array('NAME' => 'sms-uslugi.ru', 'PATH' => __DIR__. '/lib/providers/smsuslugiru.php', 'CLASS' => '\Ctweb\SMSAuth\ProviderSMSUSLUGIRU'),
        );

        $event = new \Bitrix\Main\Event(self::MODULE_ID, "OnGetProviderList", array(&$arResult));
        $event->send();

        return $arResult;
    }

    static public function getProvider($code = null) {
        if (strlen($code)) {
            $provider = self::getProviderList()[$code];
            if (file_exists($provider['PATH'])) {
                require_once $provider['PATH'];

                if (class_exists($provider['CLASS'])) {
                    try {
                        $obProvider = new $provider['CLASS'];

                        return $obProvider;
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }
                } else {
                    throw new \Exception(Loc::getMessage('ERROR_CLASS_NOT_FOUND', array('#CLASS#' => $provider['CLASS'])));
                }
            } else {
                throw new \Exception(Loc::getMessage('ERROR_PATH_NOT_FOUND', array('#PATH#' => $provider['Path'])));
            }
        }
        return null;
    }

    static public function updateOptions($data) {
        $arOptions = self::getDefaultOptions();
        // Main options
        foreach ($arOptions as $key => $value) {

            if ($key === 'PHONE_FIELD' && $value !== $data[$key])
                Option::set(self::MODULE_ID, "NO_PHONE_ERRORS", 0);


            if ($data[$key])
                $value = $data[$key];

            if (is_array($value))
                $value = json_encode($value);

            Option::set(self::MODULE_ID, $key, $value);
        }

        // Profile options
        $obProvider = self::getProvider($data['PROVIDER']);
        if ($obProvider) {
            $obProvider->updateOptions($data);
        }
    }

    /**
     * @emits OnGetUserRegisterFields ( &array )
     * @return array
     */
    static public function getUserRegisterFields() {
        $arResult = array(
            'LOGIN' => Loc::getMessage('CW_REG_FIELD_LOGIN'),
            'NAME' => Loc::getMessage('CW_REG_FIELD_NAME'),
            'LAST_NAME' => Loc::getMessage('CW_REG_FIELD_LAST_NAME'),
            'SECOND_NAME' => Loc::getMessage('CW_REG_FIELD_SECOND_NAME'),
            'EMAIL' => Loc::getMessage('CW_REG_FIELD_EMAIL'),
            'PHONE_NUMBER' => Loc::getMessage('CW_REG_FIELD_PHONE_NUMBER'),
            'PERSONAL_PHONE' => Loc::getMessage('CW_REG_FIELD_PERSONAL_PHONE'),
            'PERSONAL_FAX' => Loc::getMessage('CW_REG_FIELD_PERSONAL_FAX'),
            'PERSONAL_MOBILE' => Loc::getMessage('CW_REG_FIELD_PERSONAL_MOBILE'),
            'PERSONAL_PAGER' => Loc::getMessage('CW_REG_FIELD_PERSONAL_PAGER'),
            'WORK_PHONE' => Loc::getMessage('CW_REG_FIELD_WORK_PHONE'),
            'WORK_FAX' => Loc::getMessage('CW_REG_FIELD_WORK_FAX'),
            'WORK_PAGER' => Loc::getMessage('CW_REG_FIELD_WORK_PAGER'),
        );

        $event = new \Bitrix\Main\Event(self::MODULE_ID, "OnGetUserRegisterFields", array(&$arResult));
        $event->send();

        return $arResult;
    }
}
?>
