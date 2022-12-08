<?

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Ctweb\SMSAuth\Module;
use Ctweb\SMSAuth\Manager;
use Bitrix\Main\UserTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

if (!Loader::includeModule("ctweb.smsauth")) {
	ShowError(Loc::getMessage("SOA_MODULE_NOT_INSTALL"));
	return;
}

class CtwebSMSRegisterComponent extends \CBitrixComponent
{
	const ERROR_CODE_EMPTY = 'CODE_EMPTY';
	const ERROR_CODE_NOT_CORRECT = 'CODE_NOT_CORRECT';
	const ERROR_TIME_EXPIRED = 'TIME_EXPIRED';
	const ERROR_UNKNOWN_ERROR = 'UNKNOWN_ERROR';
	const ERROR_CAPTCHA_WRONG = 'CAPTCHA_WRONG';

	const RESULT_SUCCESS = 'SUCCESS';
	const RESULT_FAILED = 'FAILED';

	protected $manager;
	protected $moduleOptions;

	protected function getDefaultComponentParams()
	{
		return array(
			'REQUIRE_FIELDS' => array(),
		);
	}

	public function onPrepareComponentParams($arParams)
	{
		global $APPLICATION;

		$this->manager = new Manager;
		$this->moduleOptions = Module::getOptions();
		$arParams = array_merge($this->getDefaultComponentParams(), $arParams);

		#
		#   Prepare fields
		#
		$phoneUserField = $this->moduleOptions['PHONE_FIELD'];
		$userFields = UserTable::getMap();
		$arRegisterFields = array_merge(Module::getUserRegisterFields());

		foreach (array_flip(array_merge($this->moduleOptions['REGISTER_FIELDS'], array($phoneUserField))) as $code => $field) {
			if (key_exists($code, $userFields))
				$arFields[$code] = $userFields[$code];
			else {
				$arFields[$code] = array('data_type' => 'string');
			}
		}

		foreach ($arRegisterFields as $key => $name) {
			$arFields[$key]['name'] = $name;

			if (in_array($key, $arParams['REQUIRE_FIELDS']))
				$arFields[$key]['required'] = 'Y';
		}
		$arFields[$phoneUserField]['required'] = 'Y';

		$this->arResult = array(
			'USER_VALUES' => array(),
			'ERRORS' => array(),
			'FIELDS' => $arFields,
			'USE_CAPTCHA' => (Option::get("main", "captcha_registration", "N") == "Y" ? "Y" : "N"),
			'FORM_ID' =>$this->getEditAreaId('form')
		);

		if ($this->arResult["USE_CAPTCHA"] == "Y")
			$this->arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());


		return $arParams;
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

	private function isPost() {
		return $this->request->isPost() && check_bitrix_sessid() && $this->request->get('FORM_ID') === $this->arResult['FORM_ID'];
	}

	private function actionStepPhoneWaiting() {
		global $APPLICATION;

		if ($this->isPost()) {
			// check captcha
			if ($this->arResult["USE_CAPTCHA"] == "Y")
				if (!$APPLICATION->CaptchaCheckCode($this->request->get("captcha_word"), $this->request->get("captcha_sid")))
					$this->arResult['ERRORS'][] = self::ERROR_CAPTCHA_WRONG;

			$arFields = array();
			foreach (array_keys($this->arResult['FIELDS']) as $field_code) {
				if (strlen($this->request->get($field_code)))
					$arFields[$field_code] = $this->request->get($field_code);
			}

			$this->setSessionField('USER_VALUES', $arFields);
			if (empty($this->arResult['ERRORS'])) {
				$res = $this->manager->StartUserRegister($arFields);
				if (!empty($res['ERRORS'])) {
					$this->arResult['ERRORS'] = array_merge($this->arResult['ERRORS'], $res['ERRORS']);
				}
			}
			$this->setSessionField('ERRORS', $this->arResult['ERRORS']);
			LocalRedirect($APPLICATION->GetCurPageParam());
		}
	}

	private function actionStepCodeWaiting() {
		global $APPLICATION;

		if ($this->manager->isTimeExpired()) {
			$this->arResult['ERRORS'][] = self::ERROR_TIME_EXPIRED;
			$this->manager->AbortUserRegister();
			$this->clearSession();
		} else {
			if ($this->isPost()) {
				if (strlen($this->request->get('CODE'))) {
					if ($this->manager->RegisterByCode($this->request->get('CODE'))) {
						$this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
					} else {
						$this->arResult['AUTH_RESULT'] = self::RESULT_FAILED;
						$this->arResult['ERRORS'][] = self::ERROR_CODE_NOT_CORRECT;
					}
				} else {
					$this->arResult['ERRORS'][] = self::ERROR_CODE_EMPTY;
				}
				$this->setSessionField('ERRORS', $this->arResult['ERRORS']);
				LocalRedirect($APPLICATION->GetCurPageParam());
			}
		}
	}

	private function actionStepSuccess() {
		$this->manager->clearSession();
		$this->clearSession();
	}

	public function executeComponent()
	{
		global $USER, $APPLICATION;

		$this->setFrameMode(false);
		$this->context = Main\Application::getInstance()->getContext();

		if ($this->isPost() && $this->request->get('RESET')) {
			$this->manager->clearSession();
			LocalRedirect($APPLICATION->GetCurPageParam());
		}

		if ($USER->isAuthorized()) {
			$this->arResult['AUTH_RESULT'] = self::RESULT_SUCCESS;
			$this->clearSession();
		} else {
			switch($this->manager->getStep()) {
				case Manager::STEP_SUCCESS:
					$this->actionStepSuccess();
					break;
				case Manager::STEP_CODE_WAITING:
					$this->actionStepCodeWaiting();
					break;
				case Manager::STEP_PHONE_WAITING:
				default:
					$this->actionStepPhoneWaiting();

			}
		}

		($this->arResult['STEP'] = $this->manager->getStep()) || ($this->arResult['STEP'] = Manager::STEP_PHONE_WAITING);
		$this->arResult['ERRORS'] = $this->getSessionField('ERRORS');
		$this->arResult['USER_VALUES'] = $this->getSessionField('USER_VALUES');
		$this->arResult['EXPIRE_TIME'] = $this->manager->getExpireTime();

		$this->includeComponentTemplate();
	}
}