<?php

namespace Ctweb\SMSAuth;

use Bitrix\Main\Event;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;
use CUser;
use Exception;
use InvalidArgumentException;

Loc::loadMessages(__FILE__);

/**
 * Class Manager
 * @package Ctweb\SMSAuth
 */
class Manager
{
    const STEP_PHONE_WAITING = '1';
    const STEP_USER_WAITING = '2';
    const STEP_CODE_WAITING = '3';
    const STEP_SUCCESS = '4';

    const STATE_NONE = 0;
    const STATE_AUTH = 1;
    const STATE_REGISTER = 2;

    const STATE_PHONE_CHANGE = 3;

    const SESSION_FIELD_EXPIRE_TIME = 'EXPIRE';
    const SESSION_FIELD_REUSE_TIME = 'REUSE';
    const SESSION_FIELD_STATE = 'STATE';
    const SESSION_FIELD_STEP = 'STEP';
    const SESSION_FIELD_CODE = 'CODE';
    const SESSION_FIELD_USER_ID = 'USER_ID';

    const GEN_PASSWORD_LENGTH = 11;

    private $step;
    private array $errors;
    protected $client;
    private array $options;

    public function __construct()
    {
        $this->options = Module::getOptions();
        $this->errors = [];
        $this->step = $_SESSION[self::class][self::SESSION_FIELD_STEP] ?? '1';
    }

    public function addError($error){
        $this->errors[] = $error;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getStep()
    {
        return $this->step;
    }

    public function setStep($step = self::STEP_PHONE_WAITING)
    {
        $this->step = $step;
        $_SESSION[self::class][self::SESSION_FIELD_STEP] = $step;
    }

    public function getState()
    {
        return $_SESSION[self::class][self::SESSION_FIELD_STATE];

    }

    protected function setState($state)
    {
        $_SESSION[self::class][self::SESSION_FIELD_STATE] = $state;
    }

    public function getReuseTime()
    {
        return !$this->isTimeExpired() ? $_SESSION[self::class][self::SESSION_FIELD_REUSE_TIME] : false;
    }

    public function isTimeReused(): bool
    {
        return (!isset($_SESSION[self::class][self::SESSION_FIELD_REUSE_TIME]) || time() > $_SESSION[self::class][self::SESSION_FIELD_REUSE_TIME]);
    }

    public function getExpireTime()
    {
        return !$this->isTimeExpired() ? $_SESSION[self::class][self::SESSION_FIELD_EXPIRE_TIME] : false;
    }

    public function isTimeExpired(): bool
    {
        return (!isset($_SESSION[self::class][self::SESSION_FIELD_EXPIRE_TIME]) || time() > $_SESSION[self::class][self::SESSION_FIELD_EXPIRE_TIME]);
    }

    public function validateCode(string $code): bool
    {
        return (!empty($code) && md5($code) === $_SESSION[self::class][self::SESSION_FIELD_CODE]);
    }

    public function StartUserAuth($user_id)
    {
        $phone_field = $this->options['PHONE_FIELD'];

        if ($phone_field) {
            $arSelect = array("ID", 'ACTIVE');
            if (Module::CoreHasOwnPhoneAuth() && $phone_field === 'PHONE_NUMBER') {
                $arSelect[$phone_field] = 'PHONE.' . $phone_field;

                $arUser = UserTable::getList(array(
                    'filter' => array(
                        "ID" => $user_id
                    ),
                    'select' => $arSelect,
                    'runtime' => array(
                        'PHONE' => array(
                            'data_type' => 'Bitrix\Main\UserPhoneAuthTable',
                            'reference' => array(
                                'ref.USER_ID' => 'this.ID',
                            ),
                            'join_type' => 'left'
                        )
                    )
                ))->fetch();
            } else {
                $arSelect[] = $phone_field;

                $arUser = UserTable::getList(array(
                    'filter' => array(
                        "ID" => $user_id
                    ),
                    'select' => $arSelect,
                ))->fetch();
            }

            if ($arUser && $arUser['ID'] == $user_id && strlen($arUser[$phone_field])) {
                $_SESSION[self::class][self::SESSION_FIELD_USER_ID] = $arUser['ID'];
                $res = $this->SendAuthCode($arUser[$phone_field]);
                if ($res) {
                    $this->setState(self::STATE_AUTH);
                    $this->setStep(self::STEP_CODE_WAITING);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $phone
     * @return bool
     */
    private function SendAuthCode($phone): bool
    {
        $phone = $this->NormalizePhone($phone);
        $minlength = $this->options['MIN_PHONE_LENGTH'];

        if (strlen($phone) < $minlength)
            return false;

        $code = $this->GenerateCode();

        $timeReuse = intval($this->options['TIME_REUSE']);
        $timeExpire = intval($this->options['TIME_EXPIRE']);

        $_SESSION[self::class][self::SESSION_FIELD_CODE] = md5($code);
        $_SESSION[self::class][self::SESSION_FIELD_EXPIRE_TIME] = time() + $timeExpire;
        $_SESSION[self::class][self::SESSION_FIELD_REUSE_TIME] = time() + $timeReuse;

        if ($this->options['DEBUG']) {
            Module::addLog(Loc::getMessage('CW_TEST_MESSAGE_SENDED', array('#PHONE#' => $phone, '#CODE#' => $code)), 'MESSAGE');
            return true;
        }
        return (bool)$this->SendSMS($phone, $code);
    }

    /**
     * @emits OnNormalizePhone ( string $original, &string $normalized )
     * @param string $phone
     * @return string
     */
    public function NormalizePhone($phone,$codePhone='')
    {
        $digits =  str_split(preg_replace("/[^\d]/", "", (string) $phone));
        if (count($digits) === 11 && $digits[0] == '8')
            $digits[0] = '7';
        elseif (count($digits) === 10 && !empty($codePhone))
            array_unshift($digits, $codePhone);

        $result = '+' . join('',$digits);

        $event = new Event(Module::MODULE_ID, "OnNormalizePhone", array($phone, &$result));
        $event->send();

        if (!is_string($result))
            throw new InvalidArgumentException("Normalized phone should be a string!");

        return $result;
    }

    public function GenerateCode()
    {
        $length = intval($this->options['CODE_LENGTH']);
        $alphabet = $this->options['ALPHABET'];
        if (strlen($alphabet)) {
            return strtoupper(Random::getStringByCharsets($length, $alphabet));
        } else {
            if ($length) {
                return strtoupper(Random::getString($length));
            } else {
                return '';
            }

        }
    }

    private function SendSMS($phone, $code)
    {
        if ($this->options['ACTIVE']) {
            $phone = $this->NormalizePhone($phone);
            try {
                $obProvider = Module::getProvider($this->options['PROVIDER']);

                $arFields = array(
                    "CODE" => $code,
                    "PHONE" => $phone
                );

                $event = new Event(Module::MODULE_ID, "OnSendSMS", array(&$arFields, &$obProvider));
                $event->send();

                $success = $obProvider->sendSMS($arFields);

                $event = new Event(Module::MODULE_ID, "OnAfterSendSMS", array($success, $arFields, $obProvider));
                $event->send();

                return true;
            } catch (Exception $e) {
                Module::addLog($e->getMessage(), 'ERROR');
                return false;
            }
        }
        return null;
    }

    public function StartUserRegister($arFields): bool
    {
        if (!$this->isTimeReused()) {
            $this->setStep(self::STEP_CODE_WAITING);
            $this->errors[] = 'CODE_ALREADY_SENT';
            return false;
        }

        $phoneField = $this->options['PHONE_FIELD'];
        $arFields[$phoneField] = $arFields['PHONE'];
        $minlength = $this->options['MIN_PHONE_LENGTH'];

        // check phone length
        if (strlen($arFields[$phoneField]) < $minlength) {
            $this->errors[] = 'PHONE_SHORT_LENGTH';
            return false;
        }

        $res = $this->SendAuthCode($arFields[$phoneField]);
        if ($res) {
            $this->setState(self::STATE_REGISTER);
            $this->setStep(self::STEP_CODE_WAITING);
        } else {
            $this->errors[] = 'CODE_NOT_SEND';
            return false;
        }

        return true;
    }

    public function StartUserPhoneChange($arFields): bool
    {
        if (!$this->isTimeReused()) {
            $this->setStep(self::STEP_CODE_WAITING);
            $this->errors[] = 'CODE_ALREADY_SENT';
            return false;
        }

        $phoneField = $this->options['PHONE_FIELD'];
        $arFields[$phoneField] = $arFields['PHONE'];
        $minlength = $this->options['MIN_PHONE_LENGTH'];

        // check phone length
        if (strlen($arFields[$phoneField]) < $minlength) {
            $this->errors[] = 'PHONE_SHORT_LENGTH';
            return false;
        }

        $res = $this->SendAuthCode($arFields[$phoneField]);
        if ($res) {
            $this->setState(self::STATE_PHONE_CHANGE);
            $this->setStep(self::STEP_CODE_WAITING);
        } else {
            $this->errors[] = 'CODE_NOT_SEND';
            return false;
        }

        return true;
    }

    public function GetUsersByPhone($phone, $all = false)
    {
        $arResult = array();
        $minlength = $this->options['MIN_PHONE_LENGTH'];

        if (strlen($phone) < $minlength)
            return false;

        $phone_field = $this->options['PHONE_FIELD'];
        if ($phone_field) {
            $arFilter = array();
            $arSelect = array("ID", "ACTIVE", "LAST_LOGIN", "LOGIN", "LAST_NAME", "NAME");
            if (Module::CoreHasOwnPhoneAuth() && $phone_field === 'PHONE_NUMBER') {
                $arFilter['%PHONE.' . $phone_field] = $phone;
                $arSelect[$phone_field] = 'PHONE.' . $phone_field;

                if (!$all) {
                    $arFilter['ACTIVE'] = 'Y';
                }

                $users = UserTable::getList(array(
                    'filter' => $arFilter,
                    'select' => $arSelect,
                    'runtime' => array(
                        'PHONE' => array(
                            'data_type' => 'Bitrix\Main\UserPhoneAuthTable',
                            'reference' => array(
                                'ref.USER_ID' => 'this.ID',
                            ),
                            'join_type' => 'left'
                        )
                    )
                ));
            } else {
                $arFilter['%' . $phone_field] = $phone;
                $arSelect[] = $phone_field;

                if (!$all) {
                    $arFilter['ACTIVE'] = 'Y';
                }

                $users = UserTable::getList(array(
                    'filter' => $arFilter,
                    'select' => $arSelect,
                ));
            }

            while ($u = $users->Fetch()) {
                $user_phone = $this->NormalizePhone($u[$phone_field]);
                if ($user_phone === $phone) {
                    $arResult[] = array(
                        "ID" => $u["ID"],
                        "ACTIVE" => $u["ACTIVE"],
                        "LAST_LOGIN" => $u["LAST_LOGIN"],
                        "LAST_NAME" => $u["LAST_NAME"],
                        "NAME" => $u["NAME"],
                        "LOGIN" => $u["LOGIN"],
                        "PHONE" => $u[$phone_field]
                    );
                }
            }
        }

        if (empty($arResult))
            return false;
        else
            return $arResult;
    }


    /**
     * @emits OnAfterRegisterConfirm ( int )
     * @param $code
     * @param $phone
     * @return bool
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function RegisterByCode($code, $phone): bool
    {
        global $USER;

        if ($this->getState() === self::STATE_AUTH) {
            return $this->AuthByCode($code);
        }

        if (empty($this->errors)) {
            $timestamp = time();
            $arFields = [];
            $phoneField = $this->options['PHONE_FIELD'];
            $arFields[$phoneField] = $this->NormalizePhone($phone);

            //duplicate registration phone to personal phone (for lk and orders)
            $arFields["PERSONAL_PHONE"] = $arFields[$phoneField] ;
            if (!trim($arFields['EMAIL'])) {
                switch ($this->options['NEW_EMAIL_AS']) {
                    case "PHONE":
                        $arFields['EMAIL'] = "{$arFields[$phoneField]}@noemail.sms";
                        break;
                    default:
                        $arFields['EMAIL'] = "{$timestamp}@noemail.sms";
                }
            }

            if (!trim($arFields['LOGIN'])) {
                switch ($this->options['NEW_LOGIN_AS']) {
                    case "EMAIL":
                        $arFields['LOGIN'] = $arFields['EMAIL'];
                        break;
                    case "PHONE":
                        $arFields['LOGIN'] = $arFields[$phoneField];
                        break;
                    default:
                        $arFields['LOGIN'] = "user_{$timestamp}";
                }
            }

            if ($this->options['NEW_LOGIN_AS'])
                $arFields['PASSWORD'] = Random::getString(self::GEN_PASSWORD_LENGTH);

            $arFields['CONFIRM_PASSWORD'] = $arFields['PASSWORD'];

            $arFields['ACTIVE'] = 'N';

            $groups = Option::get('main', 'new_user_registration_def_group', false);
            if ($groups) {
                $groups = explode(',', $groups);
                $arFields['GROUP_ID'] = $groups;
            }

            $arFields['ACTIVE']='Y';
            $user = new CUser;
            if ($user_id = $user->Add($arFields)) {
                $USER->Authorize($user_id);
                $event = new Event(Module::MODULE_ID, "OnAfterRegisterConfirm", array($_SESSION[self::class][self::SESSION_FIELD_USER_ID]));
                $event->send();

                $this->clearSession();
                $this->setStep(self::STEP_SUCCESS);

                return true;
            } else {
                $this->errors[] = $user->LAST_ERROR;
                return false;
            }
        }else {
            return false;
        }
    }

    public function ChangePhoneByCode($code, $phone) {
        global $USER;

        if ($this->getState() === self::STATE_AUTH) {
            return $this->AuthByCode($code);
        }

        if (empty($this->errors)) {
            $arFields = [];
            $phoneField = $this->options['PHONE_FIELD'];
            $arFields[$phoneField] = $this->NormalizePhone($phone);

            //duplicate registration phone to personal phone (for lk and orders)
            $arFields["PERSONAL_PHONE"] = $arFields[$phoneField] ;

            $user = new CUser;
            if ($user->Update($USER->getContext()->getUserId(), $arFields)) {
                $this->clearSession();
                $this->setStep(self::STEP_SUCCESS);
                return true;
            } else {
                $this->errors[] = $user->LAST_ERROR;
                return false;
            }
        }else {
            return false;
        }
    }

    public function AuthByCode($code, $save_session = null)
    {
        global $USER;

        $code = strtoupper($code);

        if ($this->validateCode($code) && $_SESSION[self::class][self::SESSION_FIELD_USER_ID] > 0) {
            $USER->Authorize($_SESSION[self::class][self::SESSION_FIELD_USER_ID], $save_session === 'Y');
            $this->setStep(self::STEP_SUCCESS);
            $this->clearSession();
            return true;
        }
        return false;

    }

    public function clearSession()
    {
        unset($_SESSION[self::class]);
    }

    public function AbortUserRegister()
    {
        if ($_SESSION[self::class][self::SESSION_FIELD_USER_ID] > 0) {
            $user = CUser::getByID($_SESSION[self::class][self::SESSION_FIELD_USER_ID])->Fetch();
            if ($user && $user['ACTIVE'] === 'N' && !$user['LAST_LOGIN']) {
                CUser::Delete($user['ID']);
                $this->clearSession();
            }
        }
    }
}
