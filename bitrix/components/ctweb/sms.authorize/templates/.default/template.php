<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Ctweb\SMSAuth\Manager;

$mainID = $this->GetEditAreaId('');
$jsParams = array(
    'TEMPLATE' => array(
        'PHONE' => $mainID . 'phone',
        'SAVE_SESSION' => $mainID . 'save_session',
        'CODE' => $mainID . 'code',
        'TIMER' => $mainID . 'timer',
        'SUBMIT' => $mainID . 'submit',
        'RESET' => $mainID . 'reset',
	    'STATE' => $mainID . 'state',
    ),
    'DATA' => array(
        'TIME_LEFT' => $arResult['EXPIRE_TIME'] - time(),
    )
);

if ($arResult['AUTH_RESULT'] === 'SUCCESS') : ?>
    <? if ($arResult['STEP'] === Manager::STEP_SUCCESS) : ?>
        <div class="row">
            <div class="col-md-8">
                <div class="error alert alert-success">
                    <?= GetMessage("SMS_SUCCESS_AUTH"); ?>
                </div>
            </div>
        </div>
    <? else: ?>
        <div class="row">
            <div class="col-md-8">
                <div class="error alert alert-success">
                    <?= GetMessage("SMS_AUTH_ALREADY_AUTH"); ?>
                </div>
            </div>
        </div>
    <? endif; ?>
<? else: ?>
    <div class="ctweb-smsauth-form">
        <h3 class="bx-title"><?= GetMessage("SMS_AUTH_TITLE") ?></h3>
        <? foreach ($arResult["ERRORS"] as $error): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="error alert alert-danger">
                        <?
                        ($msg = GetMessage("SMS_AUTH_ERROR_{$error}")) || ($msg = $error);
                        echo $msg;
                        ?>
                    </div>
                </div>
            </div>
        <? endforeach; ?>
        <div class="row">
            <div class="col-md-8">
                <form action="<?= POST_FORM_ACTION_URI ?>" method="POST">
                    <? echo bitrix_sessid_post(); ?>
                    <input type="hidden" name="FORM_ID" value="<?= $arResult['FORM_ID'] ?>">

                    <? if ($arResult['STEP'] === Manager::STEP_PHONE_WAITING) : ?>
	                    <input id="<?= $jsParams['TEMPLATE']['STATE']  ?>" type="hidden" name="" value="PHONE_WAITING">
	                    <div class="form-group">
                            <label class=" col-form-label main-profile-form-label" for="smsauth-phone"><?= GetMessage("SMS_AUTH_PHONE") ?></label>
                            <input type="text" name="PHONE" placeholder=""
                                   value="<?= $arResult['USER_VALUES']['PHONE'] ?>"  class="form-control input_lk"
                                   id="<?= $jsParams['TEMPLATE']['PHONE'] ?>"/>
                        </div>

                        <div class="checkbox" style="display:none;">
                            <label>
                                <input type="checkbox" name="SAVE_SESSION" value="Y"
                                       id="<?= $jsParams['TEMPLATE']['SAVE_SESSION'] ?>"
                                    <?= ($arResult['USER_VALUES']['SAVE_SESSION'] === "Y") ? 'checked="checked"' : ""; ?> />
                                <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                            </label>
                        </div>

                        <?if ($arResult["USE_CAPTCHA"] == "Y"):?>
                            <input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />

                            <div class="form-group">
                                <?=GetMessage("CAPTCHA_REGF_PROMT")?>
                                <div class="form-group">
                                    <div class="bx-captcha"><img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" /></div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="captcha_word" maxlength="50" value="" autocomplete="off"/>
                                </div>
                            </div>

                        <?endif?>

                        <input id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>" type="submit" value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>" class="btn-sms btn link_red_button main-profile-submit">
                    <? elseif ($arResult['STEP'] === Manager::STEP_USER_WAITING): ?>
	                    <input id="<?= $jsParams['TEMPLATE']['STATE']  ?>" type="hidden" name="" value="USER_WAITING">
	                    <label class=" col-form-label main-profile-form-label" for="smsauth-phone"><?= GetMessage("SMS_AUTH_SELECT_USER") ?></label>

                        <? foreach ($arResult["USER_LIST"] as $i => $user): ?>
                            <div class="radio">
                                <label>
                                    <input id="sms-auth-radio_<?= $user['ID'] ?>" <?= (!!$i) ? 'checked="checked"' : '' ?>
                                           type="radio" name="USER_ID" value="<?= $user['ID'] ?>"/>
                                    <?= "(" . $user["LOGIN"] . ") " . $user['NAME'] . " " . $user['LAST_NAME']; ?>
                                </label>
                            </div>

                        <? endforeach; ?>
                        <input id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>" type="submit" value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>" class="btn-sms btn link_red_button main-profile-submit">
                        <input id="<?= $jsParams['TEMPLATE']['RESET'] ?>" name="RESET" type="submit" value="<?= GetMessage("SMS_AUTH_RESET") ?>" class="btn">
                    <? elseif ($arResult['STEP'] === Manager::STEP_CODE_WAITING): ?>
	                    <input id="<?= $jsParams['TEMPLATE']['STATE']  ?>" type="hidden" name="" value="CODE_WAITING">
	                    <div class="form-group">
                            <label class=" col-form-label main-profile-form-label" for="sms-auth-code"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></label>
                            <input type="text" name="CODE" id="<?= $jsParams['TEMPLATE']['CODE'] ?>"
                                    class="form-control input_lk">
                        </div>

                        <div class="form-group">
                            <div id="<?= $jsParams['TEMPLATE']['TIMER'] ?>"></div>
                        </div>

                        <input id="<?= $jsParams['TEMPLATE']['SUBMIT'] ?>" type="submit" value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>" class="btn-sms btn link_red_button main-profile-submit">
                        <input id="<?= $jsParams['TEMPLATE']['RESET'] ?>" name="RESET" type="submit" value="<?= GetMessage("SMS_AUTH_RESET") ?>" class="btn" style="display: none;">
                    <? endif; ?>
                </form>
            </div>
        </div>
    </div>
<? endif; ?>
<script>
    BX.message(<?= json_encode(array(
            'SMS_AUTH_TIME_LEFT' => GetMessage('SMS_AUTH_TIME_LEFT'),
            'SMS_AUTH_TIME_EXPIRED' => GetMessage('SMS_AUTH_TIME_OUT'),
        ))?>);

    BX(function () {
        new BX.Ctweb.SMSAuth.Controller(<?= json_encode($jsParams) ?>);
    });
</script>
