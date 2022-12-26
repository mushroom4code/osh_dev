<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
    die();

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CUser $USER
 * @var string $templateFolder
 */

use Bitrix\Main\Localization\Loc;

$daDataToken = Bitrix\Main\Config\Option::get('gorillas.dadata', "apikey", SITE_ID);

if( stripos($arResult["arUser"]["EMAIL"],'noemail.sms') !== false)
$arResult["arUser"]["EMAIL"] = '';
$jsDaDataParam = '<script type="text/javascript">BX.ready(function(){window.daDataParam = {token: \''.$daDataToken.'\'};});</script>';
$cAsset = Bitrix\Main\Page\Asset::getInstance()->addString($jsDaDataParam);

$this->addExternalJs('https://cdn.jsdelivr.net/npm/suggestions-jquery@latest/dist/js/jquery.suggestions.min.js');
$this->addExternalCss('/bitrix/modules/osh.shipping/install/css/suggestions.css');

?>

<div class="bx_profile">
    <?

    ShowError($arResult["strProfileError"]);

    if ($arResult['DATA_SAVED'] == 'Y') {
        ShowNote(Loc::getMessage('PROFILE_DATA_SAVED'));
    }
    ?>
    <form method="post" class="mb-5" name="form1" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data"
          role="form">
        <?= $arResult["BX_SESSION_CHECK"] ?>
        <input type="hidden" name="lang" value="<?= LANG ?>"/>
        <input type="hidden" name="ID" value="<?= $arResult["ID"] ?>"/>
        <input type="hidden" name="LOGIN" value="<?= $arResult["arUser"]["LOGIN"] ?>"/>
        <div class="main-profile-block-shown" id="user_div_reg">
            <div class="row">
                <div class="col-12 col-md-7">
                    <h5 class="mb-3 desktop"><b>Изменить профиль</b></h5>
                    <h5 class="mb-4 mobile"><b>Мои данные</b></h5>
                    <?
                    if (!in_array(LANGUAGE_ID, array('ru', 'ua'))) {
                        ?>
                        <div class="row">
                            <div class="col align-items-center">
                                <div class="form-group">
                                    <label class="main-profile-form-label"
                                           for="main-profile-title"><?= Loc::getMessage('main_profile_title') ?></label>
                                    <input class="form-control input_lk" type="text" name="TITLE" maxlength="50"
                                           id="main-profile-title" value="<?= $arResult["arUser"]["TITLE"] ?>"/>
                                </div>
                            </div>
                        </div>
                        <?
                    }
                    ?>
                    <div class="form-group  mb-2">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-name">ФИО</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="form-control input_lk" type="text" name="NAME" maxlength="50"
                                   id="main-profile-name" value="<?= $arResult["arUser"]["NAME"] ?>"/>
                        </div>
                    </div>
                    <div class="form-group  mb-2">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-day">Дата рождения</label>
                        <div class="col-sm-12 col-md-12">
							<?if( strtotime($arResult["arUser"]["UF_DATE_CHANGE_BH"]) > strtotime($arResult["arUser"]["PERSONAL_BIRTHDAY"])+365*24*3600):?>
                            <input class="form-control input_lk" type="text" name="BIRTHDAY" maxlength="50"
                                   id="main-profile-day2" disabled value="<?= $arResult["arUser"]["PERSONAL_BIRTHDAY"] ?>"/>	
								<?$dateChange = '<br><b>Изменить дату рождения можно будет '.date('d.m.Y',strtotime($arResult["arUser"]["UF_DATE_CHANGE_BH"])+365*24*3600).'</b>';?>
							<?else:?>
                            <input class="form-control input_lk" type="text" name="PERSONAL_BIRTHDAY" maxlength="50"
                                   id="main-profile-day2" value="<?= $arResult["arUser"]["PERSONAL_BIRTHDAY"] ?>"/>
							<?endif;?>
                        </div>
						<div class="info-date">
						В день рождения вам будут доступны персональные скидки»<br>
						Смена дня рождения доступна не чаще одногораза в год
						<?=$dateChange?>
						</div>
                    </div>
					<script>//$('input[name="PERSONAL_BIRTHDAY"]').inputmask("99/99/9999",{ "placeholder": "dd/mm/yyyy" });;
					    Inputmask("datetime", {
        inputFormat: "dd.mm.yyyy",
        placeholder: "_",
        leapday: "-02-29",
        alias: "tt.mm.jjjj"
    }).mask("input[name='PERSONAL_BIRTHDAY']");
					</script>
                    <div class="form-group  mb-2">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-email">Почта</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="form-control input_lk" type="text" name="EMAIL" maxlength="50"
                                   id="main-profile-email" value="<?= $arResult["arUser"]["EMAIL"] ?>"/>
                        </div>
                    </div>
                    <div class="form-group  mb-2">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-phone">Номер телефона</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="form-control input_lk" type="text" name="PERSONAL_PHONE" maxlength="50"
                                   id="main-profile-phone" value="<?= $arResult["arUser"]["PERSONAL_PHONE"] ?>"/>
                        </div>
                    </div>
					<script>$('#main-profile-phone').inputmask("+7 (999)-999-9999", {clearMaskOnLostFocus: false});</script>
                    <div class="form-group  mb-5" style="display:none">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-radio">Информировать меня по заказам через</label>
                        <div class="col-sm-12 col-md-12 d-flex align-items-center">
                            <input class="input_lk form-check-iniput" type="radio" <?if($arResult["arUser"]['UF_TYPE_CONNECT'] == 4):?>checked<?endif;?> name="UF_TYPE_CONNECT" maxlength="50"
                                   id="main-profile-radio_sms" value="4"/>
                            <label class="radio_input main-profile-form-label"
                                   for="main-profile-radio_sms">Смс</label>
                            <input class="input_lk form-check-input" type="radio" <?if($arResult["arUser"]['UF_TYPE_CONNECT'] == 5):?>checked<?endif;?> name="UF_TYPE_CONNECT" maxlength="50"
                                   id="main-profile-radio_telegram" value="5"/>
                            <label class="radio_input   main-profile-form-label"
                                   for="main-profile-radio_telegram">Телеграм</label>
                        </div>
                    </div>

                    <!--                    --><? //
                    //                    if ($arResult['CAN_EDIT_PASSWORD']) {
                    //                        ?>
                    <!--                        <div class="form-group  mb-2">-->
                    <!--                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"-->
                    <!--                                   for="main-profile-password">Новый пароль</label>-->
                    <!--                            <div class="col-sm-12 col-md-12">-->
                    <!--                                <input class=" form-control input_lk bx-auth-input main-profile-password"-->
                    <!--                                       type="password"-->
                    <!--                                       name="NEW_PASSWORD" maxlength="50" id="main-profile-password" value=""-->
                    <!--                                       autocomplete="off"/>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        <div class="form-group  mb-2">-->
                    <!--                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label main-profile-password"-->
                    <!--                                   for="main-profile-password-confirm">-->
                    <!--                                Подтвердите пароль-->
                    <!--                            </label>-->
                    <!--                            <div class="col-sm-12 col-md-12">-->
                    <!--                                <input class="form-control input_lk" type="password" name="NEW_PASSWORD_CONFIRM"-->
                    <!--                                       maxlength="50" value="" id="main-profile-password-confirm" autocomplete="off"/>-->
                    <!--                                <small id="emailHelp" class="text_small">-->
                    <!--                                    -->
                    <? // echo $arResult["GROUP_POLICY"]["PASSWORD_REQUIREMENTS"]; ?><!--</small>-->
                    <!--                            </div>-->
                    <!--                        </div>-->
                    <!--                        --><? //
                    //
                    //                    }?>
                    <h5 class="mt-2 mb-4" style="margin-top:35px !important; "><b>Данные доставки</b></h5>

                    <?php
                    $address = [];
                    if (!empty($arResult["arUser"]["PERSONAL_STATE"])) {
                        $address[] = $arResult["arUser"]["PERSONAL_STATE"];
                    }
                    if (!empty($arResult["arUser"]["PERSONAL_CITY"])) {
                        $address[] = $arResult["arUser"]["PERSONAL_CITY"];
                    }
                    if (!empty($arResult["arUser"]["PERSONAL_STREET"])) {
                        $address[] = $arResult["arUser"]["PERSONAL_STREET"];
                    }
					//$address = str_repalce('','', $address);
					$strAddress = implode(', ', $address);
					$strAddress = str_replace('г Москва, Москва','г Москва', $strAddress);
					$strAddress = str_replace('г Санкт-Петербург, Санкт-Петербург','г Санкт-Петербург', $strAddress);
                    ?>
                    <div class="form-group  mb-2">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-country">Сохраненый адрес доставки:</label>
                        <div class="col-sm-12 col-md-12">
                          <?= $strAddress ?>
                        </div>
                    </div>
                    <div class="form-group  mb-3">
                        <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                               for="main-profile-address">изменить адрес</label>
                        <div class="col-sm-12 col-md-12">
                            <input class="form-control input_lk" type="text" name="" maxlength="100"
                                   id="main-profile-address" value=" <?= $strAddress ?>"/>
                        </div>
                        <a style="display:none;" href="javascript:void(0);" class="col-sm-12 col-md-12 link_input_address" id="edit_address">Ввести
                            новый
                            адрес</a>
                    </div>
                    <div class="form-group mb-3" id="edit_addressBox">
                        <div class="form-group  mb-2">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                                   for="main-profile-state">Область/край</label>
                            <div class="col-sm-12 col-md-12">
                                <input class="form-control input_lk" type="text" name="PERSONAL_STATE" maxlength="50"
                                       id="main-profile-state" value="<?= $arResult["arUser"]["PERSONAL_STATE"] ?>"/>
                            </div>
                        </div>
                        <div class="form-group  mb-2">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                                   for="main-profile-city">Город</label>
                            <div class="col-sm-12 col-md-12">
                                <input class="form-control input_lk" type="text" name="PERSONAL_CITY" maxlength="50"
                                       id="main-profile-city" value="<?= $arResult["arUser"]["PERSONAL_CITY"] ?>"/>
                            </div>
                        </div>

                        <div class="form-group  mb-2">
                            <label class="col-sm-12 col-md-12 col-form-label main-profile-form-label"
                                   for="main-profile-street">Улица</label>
                            <div class="col-sm-12 col-md-12">
                                <input class="form-control input_lk" type="text" name="PERSONAL_STREET" maxlength="50"
                                       id="main-profile-street" value="<?= $arResult["arUser"]["PERSONAL_STREET"] ?>"/>
                            </div>
                        </div>
                        <a href="javascript:void(0);" class="col-sm-12 col-md-12 link_input_address mb-3">Сохранить</a>
                    </div>

                    <div class="form-group notification_box mb-2">
                        <input class="check_input form-check-input input_lk_notification" type="checkbox"
                               name="notification"
                               id="notification"/><label class="main-profile-form-label_notification"
                                                         for="notification">Согласие на обработку персональных
                            данных</label>
                    </div>
                    <label class="col-sm-12 col-md-12 link_input_address d-none" id="notification-error">
                        Необходимо согласие на обработку персольнальных данных
                    </label>
                    <div class="col">
                        <input type="submit" class="btn link_red_button main-profile-submit" id="main-profile-submit" name="save"
                               value="<?= (($arResult["ID"] > 0) ? Loc::getMessage("MAIN_SAVE") : Loc::getMessage("MAIN_ADD")) ?>">
                    </div>
                </div>
                <div class="col-12 col-md-5 desktop" style="display:none;">
                    <h5 class="mb-3"><b>Подписки</b></h5>
                    <div class="column block_with_subscriptions">
                        <div class="action">
                            <p class="lk">Скидки и акции</p>
                            <span class="lk">Экономьте на акциях и распродажах</span>
                            <div class="box_sms">
                                <p class="lk_light">Смс</p>
                                <div class="box_toggle">
                                    <div class="switch-btn"></div>
                                </div>
                            </div>
                            <div class="box_sms">
                                <p class="lk_light">Почта</p>
                                <div class="box_toggle">
                                    <div class="switch-btn switch-on"></div>
                                </div>
                            </div>
                            <div class="box_sms">
                                <p class="lk_light">Телеграм</p>
                                <div class="box_toggle">
                                    <div class="switch-btn switch-on"></div>
                                </div>
                            </div>
                        </div>
                        <div class="action" style="display:none;">
                            <p class="lk">Избранные товары</p>
                            <span class="lk">Узнайте первыми о снижении цен</span>
                            <div class="box_sms">
                                <p class="lk_light">Смс</p>
                                <div class="box_toggle">
                                    <div class="switch-btn"></div>
                                </div>
                            </div>
                            <div class="box_sms">
                                <p class="lk_light">Почта</p>
                                <div class="box_toggle">
                                    <div class="switch-btn switch-on"></div>
                                </div>
                            </div>
                            <div class="box_sms">
                                <p class="lk_light">Телеграм</p>
                                <div class="box_toggle">
                                    <div class="switch-btn switch-on"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
		<input type=hidden name="CHANGE_FORM" class="CHANGE_FORM" value="">
    </form>

    <div class="clearfix"></div>
    <script>
        BX.Sale.PrivateProfileComponent.init();
		
		

    </script>
</div>
