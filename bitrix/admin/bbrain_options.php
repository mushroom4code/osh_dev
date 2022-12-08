<?
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
use Bitrix\Main\Loader; 
Loader::includeModule("highloadblock"); 
Loader::includeModule("iblock"); 
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");


$POST_RIGHT = $APPLICATION->GetGroupRight("sale");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
  $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));


$APPLICATION->SetTitle('Настройки сайта');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");	

	

		if( $REQUEST_METHOD == "POST"  && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid()  )
		{
			

			

			COption::SetOptionString("BBRAIN", "SETTINGS_SITE", json_encode($_REQUEST['SETTINGS']));
		// если сохранение прошло удачно - перенаправим на новую страницу
		  	// (в целях защиты от повторной отправки формы нажатием кнопки "Обновить" в браузере)
		  	if ($apply != "")
		    // если была нажата кнопка "Применить" - отправляем обратно на форму.
		    	LocalRedirect("/bitrix/admin/bbrain_options.php?ID=".$ID."&mess=ok&lang=".LANG);
		  	else
		    // если была нажата кнопка "Сохранить" - отправляем к списку элементов.
		    	LocalRedirect("/bitrix/admin/bbrain_options.php?lang=".LANG);
		}
		
		

?> 
<?
	if($ID > 0)
		$APPLICATION->SetTitle('Редактирование настроек сайта');

?>
<?// конфигурация административного меню





?>


<?
$aTabs = array(
  array("DIV" => "edit1", "TAB" => 'Базовые настройки', "ICON"=>"main_user_edit", "TITLE"=>'Информация о настройках сайта'),

);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

// если есть сообщения об ошибках или об успешном сохранении - выведем их.
if($_REQUEST["mess"] == "ok" )
  CAdminMessage::ShowMessage(array("MESSAGE"=>'Изменения сохранены', "TYPE"=>"OK"));

if($message)
  echo $message->Show();
elseif($ERROR!="")
  CAdminMessage::ShowMessage($ERROR);
elseif($el->LAST_ERROR!="")
  CAdminMessage::ShowMessage($el->LAST_ERROR);
?>

<?
// далее выводим собственно форму
?>

<form method="POST" ENCTYPE="multipart/form-data" id=post_form name="post_form">
<?// проверка идентификатора сессии ?>
<?echo bitrix_sessid_post();?>
<?
// отобразим заголовки закладок
$tabControl->Begin();
?>
<?
//********************
// первая закладка - форма редактирования параметров рассылки
//********************
$tabControl->BeginNextTab();
//echo $str_CATEGORY_ID;

$SETTINGS = json_decode(COption::GetOptionString("BBRAIN", "SETTINGS_SITE"), 1);
?>	

	 <tr>
		<td width="50">Телефон на сайте</td>
		<td width="80%"><input type=text size=40 name="SETTINGS[PHONE]" value="<?=$SETTINGS['PHONE']?>" ></td>	 

	</tr>
	 <tr>
		<td width="50">Название организации</td>
		<td width="80%"><input type=text size=40 name="SETTINGS[COMPANY]" value="<?=$SETTINGS['COMPANY']?>" ></td>	 

	</tr>
	
	 <tr>
		<td width="50">Уведомление до 18</td>
		<td width="80%">
		<textarea name="SETTINGS[ATTENT_TEXT]" width="100%" cols=100 rows=3><?=$SETTINGS['ATTENT_TEXT']?></textarea>
		</td>	 

	</tr>	
	 <tr>
		<td width="50">Уведомление до 18(блок 2)</td>
		<td width="80%">
		<textarea name="SETTINGS[ATTENT_TEXT2]" width="100%" cols=100 rows=3><?=$SETTINGS['ATTENT_TEXT2']?></textarea>
		</td>	 

	</tr>	
	 <tr>
		<td width="50">Ссылка с кнопки "нет" в уведомлении до 18</td>
		<td width="80%"><input type=text size=40 name="SETTINGS[ATTENT_NOT]" value="<?=$SETTINGS['ATTENT_NOT']?>" ></td>	 

	</tr>	
	<tr><td class="heading" colspan=2>Каталог</td></tr>
	 <tr>
		<td width="50">Максимальное количество к покупке</td>
		<td width="80%"><input type=text size=40 name="SETTINGS[MAX_QUANTITY]" value="<?=$SETTINGS['MAX_QUANTITY']?>" ></td>	 

	</tr>
	 <tr>
		<td width="50">Исключаемые свойства из отображения (через запятую без пробелов)</td>
		<td width="80%">
		<textarea name="SETTINGS[arIskCode]" width="100%" cols=100 rows=3><?=$SETTINGS['arIskCode']?></textarea>
		</td>	 

	</tr>	
	
	<tr><td class="heading" colspan=2>Оформление заказа</td></tr>
	 <tr>
		<td width="50">Текст на плашке "Самовывоз"</td>
		<td width="80%">
		<textarea name="SETTINGS[ORDER_PICKUP_TITLE]" width="100%" cols=100 rows=3><?=$SETTINGS['ORDER_PICKUP_TITLE']?></textarea>
		</td>	 

	</tr>
	 <tr>
		<td width="50">Адрес в раскрытом блоке "Самовывоз"</td>
		<td width="80%">
		<textarea name="SETTINGS[ORDER_PICKUP_ADDRESS]" width="100%" cols=100 rows=3><?=$SETTINGS['ORDER_PICKUP_ADDRESS']?></textarea>
		</td>	 

	</tr>
	 <tr>
		<td width="50">Текст в раскрытом блоке "Самовывоз"</td>
		<td width="80%">
		<textarea name="SETTINGS[ORDER_PICKUP_TEXT]" width="100%" cols=100 rows=3><?=$SETTINGS['ORDER_PICKUP_TEXT']?></textarea>
		</td>	 

	</tr>
	
<?
//********************
// вторая закладка - параметры автоматической генерации рассылки
//********************
$tabControl->BeginNextTab();
?>

<?
// завершение формы - вывод кнопок сохранения изменений
$tabControl->Buttons(
  array(
   // "disabled"=>($POST_RIGHT<"W"),
    "back_url"=>"bbrain_options.php?lang=".LANG,

  )
);
?>
<input type="hidden" name="lang" value="<?=LANG?>">

<?if($ID>0 && !$bCopy):?>
  <input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
// завершаем интерфейс закладок
$tabControl->End();
?>

<?
// дополнительное уведомление об ошибках - вывод иконки около поля, в котором возникла ошибка
$tabControl->ShowWarnings("post_form", $message);
?>	
<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");	
?>