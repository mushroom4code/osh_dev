<?
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Enterego\ProductsSubscriptionsTable;

define('STOP_STATISTICS', true);
define('PUBLIC_AJAX_MODE', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if(isset($_REQUEST['reloadCaptcha']) && $_REQUEST['reloadCaptcha'] == 'Y')
{
	echo $APPLICATION->captchaGetCode();
	die();
}

//enterego deleted sessionid check

Loc::loadMessages(__FILE__);
global $USER;

if (!$USER->IsAuthorized()) {
    echo Bitrix\Main\Web\Json::encode(array('success' => false, 'message' => 'noauth'));
    require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
    die();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if(!Loader::includeModule('catalog'))
	{
        echo Bitrix\Main\Web\Json::encode(array(
            'error' => true, 'message' => Loc::getMessage('CPSA_MODULE_NOT_INSTALLED', array('#NAME#' => 'catalog'))));
        require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
        die();
	}

	if($_POST['checkSubscribe'] == 'Y')
	{
		if(!empty($_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']))
		{
			if(array_key_exists($_POST['itemId'], $_SESSION['SUBSCRIBE_PRODUCT']['LIST_PRODUCT_ID']))
			{
				echo Bitrix\Main\Web\Json::encode(array('subscribe' => true));
				require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
				die();
			}
		}
		echo Bitrix\Main\Web\Json::encode(array('subscribe' => false));
		require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
		die();
	}

	$subscribeManager = new \Bitrix\Catalog\Product\SubscribeManager;
	$contactTypes = $subscribeManager->contactTypes;
	if(!$contactTypes)
	{
		echo Bitrix\Main\Web\Json::encode(
			array('error' => true, 'message' => Loc::getMessage('CPSA_CONTACT_TYPE_NOT_FOUND')));
		require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
		die();
	}
	$defaultContactTypeId = \Bitrix\Catalog\SubscribeTable::CONTACT_TYPE_EMAIL;
	$contactFormData = array(
		'contactFormSubmit' => true,
		'contactTypeData' => array()
	);
	foreach($contactTypes as $contactTypeId => $contactTypeData)
	{
		$contactFormData['contactTypeData'][$contactTypeId]['contactId'] = $contactTypeData['ID'];
		$contactFormData['contactTypeData'][$contactTypeId]['contactLable'] = $contactTypeData['NAME'];
		$contactFormData['contactTypeData'][$contactTypeId]['contactRule'] = $contactTypeData['RULE'];
	}

	$userId = false;
	if($USER && is_object($USER) && $USER->isAuthorized())
	{
		$userId = $USER->getId();
	}

	if($_POST['subscribe'] == 'Y')
	{
		$landingId = (!empty($_POST['landingId']) ? intval($_POST['landingId']) : null);

		if(count($contactTypes) > 1)
		{
			// Returns a response to the formation of the form of contacts.
			if(isset($_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'])
				&& $_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'] == 'Y')
			{
				$contactFormData['captchaCode'] = $APPLICATION->captchaGetCode();
			}
			echo Bitrix\Main\Web\Json::encode($contactFormData);
			require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
			die();
		}
		else
		{
			$contactTypeId = key($contactTypes);
			$userContact = false;
			if($userId)
				$userContact = ($contactTypeId == $defaultContactTypeId) ? $USER->getEmail() : false;

            //enterego response if user have no email
            if ((preg_match('/(@noemail.sms)/', $userContact) != false) || (!$userContact)) {
                echo Bitrix\Main\Web\Json::encode(
                    array('success' => false, 'message' => 'noemail'));
                require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
                die();
            }
            //end
            //enterego changed input params for addsubscription, changed response
			if($userContact)
			{
				$subscribeData = array(
					'USER_CONTACT' => $userContact,
					'ITEM_ID' => $_POST['item_id'],
					'SITE_ID' => SITE_ID,
					'CONTACT_TYPE' => $contactTypeId,
					'USER_ID' => $userId,
				);
				if ($landingId)
				{
					$subscribeData['LANDING_SITE_ID'] = $landingId;
				}
				$subscribeId = $subscribeManager->addSubscribe($subscribeData);
				if($subscribeId)
				{
                    try {
                        $existingProduct = ProductsSubscriptionsTable::getRow(array('filter' => array('PRODUCT_NAME' => $_POST['product_name'])));
                        if(!empty($existingProduct)) {
                            $dbResult = ProductsSubscriptionsTable::update($existingProduct['ID'], array('fields' =>
                                array('SUBSCRIPTION_CLICKS' => $existingProduct['SUBSCRIPTION_CLICKS'] + 1)));
                        } else {
                            $dbResult = ProductsSubscriptionsTable::add(array('fields' =>
                                array('PRODUCT_NAME' => $_POST['product_name'], 'SUBSCRIPTION_CLICKS' => 1
                            )));
                        }

                        if (!$dbResult->isSuccess()) {
                            $dbError = true;
                        } else {
                            $dbError = false;
                        }
                    } catch (\Throwable $e) {
                        $dbError = $e;
                    }
					echo Bitrix\Main\Web\Json::encode(
						array('success' => true, 'message' => 'subscribed', 'clickDbError' => CUtil::PhpToJSObject($dbError), 'subscribeId' => $subscribeId));
					require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
					die();
				}
				else
				{
					$errorObject = current($subscribeManager->getErrors());
					$errors = array('error' => true);
					if($errorObject)
					{
						$errors['message'] = $errorObject->getMessage();
						if($errorObject->getCode() == $subscribeManager::ERROR_ADD_SUBSCRIBE_ALREADY_EXISTS)
						{
							$errors['setButton'] = true;
						}
					}
					echo Bitrix\Main\Web\Json::encode($errors);
					require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
					die();
				}
                //enterego end
			}
			else
			{
				if(isset($_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'])
					&& $_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'] == 'Y')
				{
					$contactFormData['captchaCode'] = $APPLICATION->captchaGetCode();
				}
				echo Bitrix\Main\Web\Json::encode($contactFormData);
				require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
				die();
			}
		}
    //enterego added unsibscribe logic
	}elseif ($_POST['subscribe'] == 'N') {
        $contactTypeId = key($contactTypes);
        $userContact = false;
        if($userId)
            $userContact = ($contactTypeId == $defaultContactTypeId) ? $USER->getEmail() : false;


        if($userContact)
        {
            $subscribe = \Bitrix\Catalog\SubscribeTable::getList(array(
                'select' => array('CNT'),
                'filter' => array(
                    '=ID' => intval($_POST['subscription_id']),
                    '=ITEM_ID' => $_POST['item_id'],
                    '=USER_ID' => $userId
                ),
                'runtime' => array(new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
            ))->fetch();
            if(intval($subscribe['CNT']))
            {
                $subscribeId = \Bitrix\Catalog\SubscribeTable::delete(intval($_POST['subscription_id']));
            }

            if (!empty($subscribeId)) {
                if($subscribeId->isSuccess())
                {
                    echo Bitrix\Main\Web\Json::encode(
                        array('success' => true, 'message' => 'unsubscribed'));
                    require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
                    die();
                }
                else
                {
                    $errors = array('error' => true);
                    $errors['message'] = "Subscription does not exist";
                    echo Bitrix\Main\Web\Json::encode($errors);
                    require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
                    die();
                }
            }
        }
        else
        {
            if(isset($_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'])
                && $_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'] == 'Y')
            {
                $contactFormData['captchaCode'] = $APPLICATION->captchaGetCode();
            }
            echo Bitrix\Main\Web\Json::encode($contactFormData);
            require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
            die();
        }
    //enterego end
    }elseif($_POST['contactFormSubmit'] == 'Y')
	{
		if(empty($_POST['contact']) || !is_array($_POST['contact']))
		{
			echo Bitrix\Main\Web\Json::encode(
				array('error' => true, 'message' => Loc::getMessage('CPSA_INCCORECT_INPUT_DATA')));
			require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
			die();
		}

		if (isset($_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'])
			&& $_SESSION['SUBSCRIBE_PRODUCT']['useCaptcha'] == 'Y')
		{
			if (!$APPLICATION->captchaCheckCode($_POST['captcha_word'], $_POST['captcha_sid']))
			{
				echo Bitrix\Main\Web\Json::encode(
					array('error' => true, 'message' => Loc::getMessage('CPSA_INCCORECT_INPUT_CAPTHA')));
				require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
				die();
			}
		}

		$manyContact = false;
		if(isset($_POST['manyContact']) && $_POST['manyContact'] == 'Y')
		{
			$manyContact = true;
		}
		$useMethodNotification = false;
		$errors = array();
		foreach($_POST['contact'] as $contactTypeId => $contact)
		{
			if($manyContact && $contact['use'] == 'N')
			{
				continue;
			}
			$subscribeData = array(
				'USER_CONTACT' => $contact['user'],
				'ITEM_ID' => $_POST['itemId'],
				'SITE_ID' => $_POST['siteId'],
				'CONTACT_TYPE' => $contactTypeId,
			);
			if($userId)
			{
				$subscribeData['USER_ID'] = $userId;
			}
			if ($landingId)
			{
				$subscribeData['LANDING_SITE_ID'] = $landingId;
			}
			$subscribeManager->addSubscribe($subscribeData);
			$errorObject = current($subscribeManager->getErrors());
			if($errorObject)
			{
				$errors = array(
					'typeName' => $contactTypes[$contactTypeId]['NAME'],
					'errorMessage' => $errorObject->getMessage()
				);
				if($errorObject->getCode() == $subscribeManager::ERROR_ADD_SUBSCRIBE_ALREADY_EXISTS)
				{
					$errors['setButton'] = true;
				}
			}
			$useMethodNotification = true;
		}

		if($errors || !$useMethodNotification)
		{
			$jsonData = array('error' => true, 'message' => $useMethodNotification
				? $errors['errorMessage'] : Loc::getMessage('CPSA_NOT_CHOOSE_METHOD_NOTIFICATION'),
				'typeName' => $errors['typeName']);
			if(!empty($errors['setButton']))
				$jsonData['setButton'] = true;

			echo Bitrix\Main\Web\Json::encode($jsonData);
			require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
			die();
		}

		echo Bitrix\Main\Web\Json::encode(
			array('success' => true, 'message' => Loc::getMessage('CPSA_SUCCESS_SUBSCRIBE')));
		require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
		die();
	}
}

echo Bitrix\Main\Web\Json::encode(array());
require_once($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_after.php');
die();