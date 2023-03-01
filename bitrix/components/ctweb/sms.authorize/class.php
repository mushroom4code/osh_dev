<?

use B01110011ReCaptcha\ReCaptcha;
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Ctweb\SMSAuth\Module;
use Ctweb\SMSAuth\Manager;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule("ctweb.smsauth")) {
    ShowError(Loc::getMessage("SOA_MODULE_NOT_INSTALL"));
    return;
}

class CtwebSMSAuthComponent extends \CBitrixComponent
{
    const ERROR_CODE_EMPTY = 'CODE_EMPTY';
    const ERROR_CODE_NOT_CORRECT = 'CODE_NOT_CORRECT';
    const ERROR_TIME_EXPIRED = 'TIME_EXPIRED';
    const ERROR_USER_NOT_FOUND = 'USER_NOT_FOUND';
    const ERROR_USER_NOT_CHOOSED = 'USER_NOT_CHOOSED';
    const ERROR_CAPTCHA_WRONG = 'CAPTCHA_WRONG';
    const ERROR_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    const METHOD_REUSE_CODE = 'REUSE_CODE';
    const METHOD_CHANGE_PHONE = 'CHANGE_PHONE';

    const RESULT_SUCCESS = 'SUCCESS';
    const RESULT_FAILED = 'FAILED';

    const ERROR_PHONE_EXISTS = 'PHONE_EXISTS';

    /** @var Ctweb\SMSAuth\Manager */
    protected $manager;
    protected $moduleOptions;

    protected function getDefaultComponentParams()
    {
        return array(
            'REDIRECT_TIME' => -1,
            'REDIRECT_URL' => '/',
            'ALLOW_MULTIPLE_USERS' => 'N',
        );
    }

    public function onPrepareComponentParams($arParams)
    {
        global $APPLICATION;

        $this->manager = new Manager;
        $this->moduleOptions = Module::getOptions();
        $arParams = array_merge($this->getDefaultComponentParams(), $arParams);

        $this->arResult = array(
            'USER_VALUES' => array(),
            'ERRORS' => array(),
            'USE_CAPTCHA' => (Option::get("main", "captcha_registration", "N") == "Y"? "Y" : "N"),
            'FORM_ID' =>$this->getEditAreaId('form')
        );
        if ($this->arResult["USE_CAPTCHA"] == "Y")
            $this->arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());

        return $arParams;
    }

    protected function setPhone($phone, $codePhone = '')
    {
        $this->setSessionField('PHONE', $this->manager->NormalizePhone($phone, $codePhone));
    }

    protected function setSessionField($key, $value)
    {
        $_SESSION[self::class][$key] = $value;
    }

    protected function getSessionField($key)
    {
        return $_SESSION[self::class][$key];
    }

    protected function clearSession()
    {
        unset($_SESSION[self::class]);
    }

    private function isPost(): bool
    {
        return $this->request->isPost() && check_bitrix_sessid();
    }

    private function CaptchaCheckToken(): bool
    {
        $secret = COption::GetOptionString("b01110011.recaptcha", "secret_key_N2");
        $reCaptcha = new ReCaptcha($secret);
        $verify = $reCaptcha->verify($_REQUEST['recaptcha_token']);

        if ($verify['success'] == false || floatval($verify['score']) < 0.5) {
            return false;
        }
        return true;
    }

    private function actionStepUserWaiting() {
        global $APPLICATION;
        $arUsers = $this->manager->GetUsersByPhone($this->getSessionField('PHONE'));
        $this->arResult['USER_LIST'] = $arUsers;
        if ($this->isPost()) {
            if ($arUsers) {
                if (strlen($this->request->get('USER_ID'))) {
                    if (($chosedUser = intval($this->request->get('USER_ID')))) {
                        $array = array_filter($arUsers, function ($e) use ($chosedUser) {
                            return $e['ID'] == $chosedUser;
                        });
                        $arUser = reset($array);
                        if ($arUser['ID']) {
                            $res = $this->manager->StartUserAuth($arUser['ID']);
                            if (!$res) {
                                $this->manager->addError(self::ERROR_UNKNOWN_ERROR);
                            }
                        }
                    }
                } else {
                    $this->manager->addError(self::ERROR_USER_NOT_CHOOSED);
                }
            } else {
                $this->manager->addError(self::ERROR_USER_NOT_FOUND);
            }
            LocalRedirect($APPLICATION->GetCurPageParam());
        }
    }

    /**
     * Step validation user code
     */
    private function actionStepCodeWaiting(bool $isAjaxRequest) {
        global $APPLICATION;

        if ($this->isPost() && $this->request['method']===self::METHOD_REUSE_CODE) {
            if ($this->manager->isTimeReused()) {
                $this->actionStepPhoneWaiting($isAjaxRequest, false);
            }
        }
        elseif ($this->manager->isTimeExpired()) {
            if ($this->getSessionField('IS_AJAX_POST')===true) {
                $this->manager->addError(self::ERROR_TIME_EXPIRED);
            }
            $this->clearSession();
            $this->manager->clearSession();
        } else {
            if ($this->isPost()) {
                if ($this->manager->isTimeExpired()) {
                    $this->manager->addError(self::ERROR_TIME_EXPIRED);
                    return;
                }
                $code = $this->request->get('CODE');
                if (!$this->manager->validateCode($code)){
                    $this->manager->addError(self::ERROR_CODE_NOT_CORRECT);
                    return;
                }

                if ($this->manager->getState() === $this->manager::STATE_PHONE_CHANGE) {
                    if ($this->manager->ChangePhoneByCode($this->request->get('CODE'), $this->getSessionField('PHONE'))) {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
                    } else {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_FAILED;
                        $this->manager->addError(self::ERROR_CODE_NOT_CORRECT);
                    }
                }elseif ($this->manager->getState() === $this->manager::STATE_REGISTER) {
                    if ($this->manager->RegisterByCode($this->request->get('CODE'), $this->getSessionField('PHONE'))) {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
                    } else {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_FAILED;
                        $this->manager->addError(self::ERROR_CODE_NOT_CORRECT);
                    }
                } else {
                    if (!$this->manager->AuthByCode($this->request->get('CODE'), $this->getSessionField('SAVE_SESSION'))) {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_FAILED;
                        $this->manager->addError(self::ERROR_CODE_NOT_CORRECT);
                    }
                }

                if (!$isAjaxRequest){
                    LocalRedirect($APPLICATION->GetCurPageParam());
                }
            }
        }
    }

    private function actionStepPhoneWaiting($isAjaxRequest = false, $captcha_check = true)
    {
        global $APPLICATION;
        if ($this->isPost() && $this->request['method'] != self::METHOD_CHANGE_PHONE) {
            // check captcha
            $use_captcha = COption::GetOptionString("b01110011.recaptcha", "registration_enable_s1");
            if ($this->arResult["USE_CAPTCHA"] == "Y" && $captcha_check) {
                if (!$this->CaptchaCheckToken()) {
                    $this->manager->addError(self::ERROR_CAPTCHA_WRONG);
                    return;
                }
            }

            $this->setSessionField('IS_AJAX_POST', $isAjaxRequest);

            if (strlen($this->request->get('PHONE'))) {
                $this->setPhone($this->request->get('PHONE'), $this->request->get('__phone_prefix') ?? '');
            }

            if (strlen($this->request->get('SAVE_SESSION')))
                $this->setSessionField('SAVE_SESSION', $this->request->get('SAVE_SESSION'));

            if (empty($this->manager->getErrors())) {
                if ($this->getSessionField('PHONE')) {
                    $arUsers = $this->manager->GetUsersByPhone($this->getSessionField('PHONE'));
                    if ($arUsers) {
                        if ($this->arParams['PROFILE_AUTH']  == "Y") {
                            $this->manager->addError(self::ERROR_PHONE_EXISTS);
                            return;
                        } else {
                            if ($this->arParams['ALLOW_MULTIPLE_USERS'] === 'Y' && count($arUsers) > 1) {
                                $this->manager->setStep(Manager::STEP_USER_WAITING);
                            } else {
                                $this->manager->setStep(Manager::STEP_CODE_WAITING);
                                $arUser = reset($arUsers);

                                if ($arUser['ID']) {
                                    $res = $this->manager->StartUserAuth($arUser['ID']);
                                    if (!$res) {
                                        $this->manager->addError(self::ERROR_UNKNOWN_ERROR);
                                    }
                                }
                            }
                        }
                    } else {
                        //TODO enable registration
                        if ($this->arParams['PROFILE_AUTH'] == "Y") {
                            if (true) {
                                $res = $this->manager->StartUserPhoneChange(['PHONE' => $this->getSessionField('PHONE')]);
                                if ($res!==true) {
                                    $this->manager->addError(self::ERROR_UNKNOWN_ERROR);
                                }
                            } else {
                                $this->manager->addError(self::ERROR_USER_NOT_FOUND);
                            }
                        } else {
                            if (true) {
                                $res = $this->manager->StartUserRegister(['PHONE' => $this->getSessionField('PHONE')]);
                                if ($res!==true) {
                                    $this->manager->addError(self::ERROR_UNKNOWN_ERROR);
                                }
                            } else {
                                $this->manager->addError(self::ERROR_USER_NOT_FOUND);
                            }
                        }
                    }
                } else {
                    $this->clearSession();
                }
            }
            if ($this->arParams['PROFILE_AUTH'] == "Y") {
                if (!$isAjaxRequest) {
                    LocalRedirect($APPLICATION->GetCurPageParam());
                }
            }
        }
    }

    private function actionStepSuccess() {
        $this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
        $this->clearSession();
        $this->manager->clearSession();
        if ($this->arParams['PROFILE_AUTH'] == "Y") {
            $isAjaxRequest = $this->request["is_ajax_post"] == "Y";
            $this->manager->setStep();
            $this->actionStepPhoneWaiting($isAjaxRequest);
        }
    }

    public function executeComponent()
    {
        global $APPLICATION, $USER;

        $this->setFrameMode(false);
        $this->context = Main\Application::getInstance()->getContext();
        $this->isRequestViaAjax = $this->isPost() && $this->request->get('via_ajax') == 'Y';
        $isAjaxRequest = $this->request["is_ajax_post"] == "Y";

        if ($this->isPost() && $this->request->get('RESET')) {
            $this->manager->clearSession();
            LocalRedirect($APPLICATION->GetCurPageParam());
        }

        if ($this->isPost() && $this->request['method']===self::METHOD_CHANGE_PHONE) {
            $this->manager->clearSession();
            $this->manager->setStep(Manager::STEP_PHONE_WAITING);
        }

        if (!$USER->isAuthorized()) {
            if ($this->isPost()  && $this->request->getPost('METHOD')==='EMAIL_AUTH'){
                if ($this->request->getPost('EMAIL')
                    && $this->request->getPost('PASSWORD')) {

                    $auth = $GLOBALS["USER"]->Login($this->request->getPost('EMAIL'),
                        $this->request->getPost('PASSWORD', $_POST["USER_REMEMBER"]));
                    if ($auth === true) {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
                        $this->arResult['STEP'] = Manager::STEP_SUCCESS;
                    } else {
                        $this->arResult['AUTH_RESULT'] = self::RESULT_FAILED;
                        $this->arResult['STEP'] = Manager::STATE_NONE;
                    }
                    $this->clearSession();
                }
            } else {
                switch ($this->manager->getStep()) {
                    case Manager::STEP_SUCCESS : // all ok, redirect waiting
                        $this->actionStepSuccess();
                        break;

                    case Manager::STEP_USER_WAITING :
                        $this->actionStepUserWaiting();
                        break;

                    case Manager::STEP_CODE_WAITING : // user found, code waiting for auth
                        $this->actionStepCodeWaiting($isAjaxRequest);
                        break;

                    case Manager::STEP_PHONE_WAITING: // no action, phone waiting
                    default: // no action, phone waiting
                        $this->actionStepPhoneWaiting($isAjaxRequest);
                }

                $this->arResult['ERRORS'] = $this->manager->getErrors();
                $this->arResult['USER_VALUES']['SAVE_SESSION'] = $this->getSessionField('SAVE_SESSION');
                $this->arResult['USER_VALUES']['PHONE'] = $this->getSessionField('PHONE');
                $this->arResult['EXPIRE_TIME'] = $this->manager->getExpireTime() - time();
                $this->arResult['REUSE_TIME'] = $this->manager->getReuseTime() - time();
                ($this->arResult['STEP'] = $this->manager->getStep()) || ($this->arResult['STEP'] = Manager::STEP_PHONE_WAITING);
            }
        } elseif ($this->arParams['PROFILE_AUTH'] == "Y") {
            switch ($this->manager->getStep()) {
                case Manager::STEP_SUCCESS : // all ok, redirect waiting
                    $this->actionStepSuccess();
                    break;

                case Manager::STEP_USER_WAITING :
                    $this->actionStepUserWaiting();
                    break;

                case Manager::STEP_CODE_WAITING : // user found, code waiting for auth
                    $this->actionStepCodeWaiting($isAjaxRequest);
                    break;

                case Manager::STEP_PHONE_WAITING: // no action, phone waiting
                default: // no action, phone waiting
                    $this->actionStepPhoneWaiting($isAjaxRequest);
            }

            $this->arResult['ERRORS'] = $this->manager->getErrors();
            $this->arResult['USER_VALUES']['SAVE_SESSION'] = $this->getSessionField('SAVE_SESSION');
            $this->arResult['USER_VALUES']['PHONE'] = $this->getSessionField('PHONE');
            $this->arResult['EXPIRE_TIME'] = $this->manager->getExpireTime() - time();
            $this->arResult['REUSE_TIME'] = $this->manager->getReuseTime() - time();
            ($this->arResult['STEP'] = $this->manager->getStep()) || ($this->arResult['STEP'] = Manager::STEP_PHONE_WAITING);
        } else {
                $this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
                $this->clearSession();
        }

        if ($isAjaxRequest) {
            $APPLICATION->RestartBuffer();
            $APPLICATION->FinalActions(json_encode($this->arResult));
        } else {
            $this->includeComponentTemplate();
        }
    }
}