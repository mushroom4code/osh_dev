<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$adminPage->Init();
$adminMenu->Init($adminPage->aModules);

if (empty($adminMenu->aGlobalMenu)) /**
 * @var CMain|CAllMain $APPLICATION
 */ {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetAdditionalCSS("/bitrix/themes/" . ADMIN_THEME_ID . "/index.css");

$MESS ['admin_index_title'] = "Строка информатора для пользователей на сайте";

$APPLICATION->SetTitle(GetMessage("admin_index_title"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$resOption = COption::GetOptionString('activation_info_admin', 'CHECKED_INFO');
$dateOption = json_decode(COption::GetOptionString('activation_info_admin', 'PERIOD')); ?>
  <script src="/local/templates/Oshisha/pwa/pwa.js" type="text/javascript"></script>
    <div>
      <button id="push-subscription-button">Push notifications !</button>
      <button id="send-push-button">Send a push notification</button>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");