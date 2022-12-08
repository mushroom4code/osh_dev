<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$alert_colors = array(
    "USER_NOT_FOUND" => "alert-danger",
    "MULTIPLE_USERS_FOUND" => "alert-info",
    "CODE_NOT_CORRECT" => "alert-danger",
    "CODE_EXPIRED" => "alert-danger",
    "NO_PHONE_INPUT" => "alert-danger",
    "SMS_SENDED" => "alert-success",
);
if (!$GLOBALS["USER"]->IsAuthorized()):
    ?>
    <div class="ctweb-smsauth-form">
        <? foreach ($arResult["ERRORS"] as $error): ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="error alert <?=$alert_colors[$error]?>">
                        <?= GetMessage("SMS_AUTH_ERROR_" . $error); ?>
                    </div>
                </div>
            </div>
        <? endforeach; ?>
        <div class="row">
            <div class="col-md-8">
                <form action="<?= POST_FORM_ACTION_URI ?>" method="POST" name="SMS_AUTH_FORM">
                    <? echo bitrix_sessid_post(); ?>
                    <? if (!empty($arResult["USERS_FOUND"])) {
                        $hidden = true;
                    } else
                        $hidden = false; ?>

                    <? if ($hidden) $class = ' hidden'; else $class = ''; ?>

                    <h3 class="bx-title"><?= GetMessage("SMS_AUTH_PLEASE_AUTH") ?></h3>

                    <div class="form-group<?= $class ?>">
                        <label for="smsauth-phone"><?= GetMessage("SMS_AUTH_PHONE") ?></label>
                        <input type="<?= ($hidden) ? "hidden" : "text"; ?>" name="PHONE" placeholder=""
                               value="<?= $_SESSION["SMS_AUTH"]["PHONE"] ?>" class="form-control" id="smsauth-phone"/>
                    </div>

                    <div class="checkbox<?= $class ?>">
                        <label>
                            <input type="<?= ($hidden) ? "hidden" : "checkbox"; ?>" name="SAVE" value="Y"
                                   id="sms-auth-save" <?= ($_POST['SAVE'] === "Y") ? 'checked="checked"' : ""; ?>>
                            <?= GetMessage("SMS_AUTH_SAVE_SESSION") ?>
                        </label>
                    </div>

                    <? if (in_array("MULTIPLE_USERS_FOUND", $arResult["ERRORS"])):?>
                        <h5><strong><?= GetMessage("SMS_AUTH_SELECT_USER") ?></strong></h5>
                        <? $first = $arResult["USERS_FOUND"][0]["ID"]; ?>
                        <? foreach ($arResult["USERS_FOUND"] as $user):?>
                            <div class="radio">
                                <label>
                                    <input id="sms-auth-radio_<?= $user['ID'] ?>" <? if ($user["ID"] == $first) echo "checked=\"checked\""; ?>
                                           type="radio" name="USER_SELECT" value="<?= $user['ID'] ?>"/>
                                    <?= "(" . $user["LOGIN"] . ") " . $user['NAME'] . " " . $user['LAST_NAME']; ?>
                                </label>
                            </div>
                        <? endforeach; ?>
                    <? endif; ?>
                    <? if (!empty($arResult["USERS_FOUND"])):?>
                        <div class="form-group">
                            <label for="sms-auth-code"><?= GetMessage("SMS_AUTH_ENTER_CODE") ?></label>
                            <input type="text" name="AUTH_CODE" id="sms-auth-code" class="form-control">
                        </div>
                        <div id="ctweb-smsauth-time-left"></div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="submit" value="<?= GetMessage("SMS_AUTH_LOG_IN") ?>" name="LOGIN"
                                           class="btn btn-primary"><br>
                                </div>
                                <div class="col-md-6 text-right">
                                    <a href="<?= $APPLICATION->GetCurPageParam(); ?>"
                                       class="btn"><?= GetMessage("SMS_AUTH_CHANGE_PHONE"); ?></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    <? else:?>
                        <input type="submit" value="<?= GetMessage("SMS_AUTH_GET_CODE") ?>" name="GET_CODE"
                               class="btn btn-primary">
                    <? endif; ?>
                </form>
            </div>
        </div>
    </div>
<? else:?>
    <div class="row">
        <div class="col-md-8">
            <div class="error alert alert-success">
                <?= GetMessage("SMS_AUTH_ALREADY_AUTH"); ?>
            </div>
        </div>
    </div>
<? endif; ?>
<script>
    $(document).ready(function () {
        var time_left = <?=(isset($_SESSION["SMS_AUTH"]["EXPIRE"])) ? $_SESSION["SMS_AUTH"]["EXPIRE"] - time() : "null";?>;
        var timer = setInterval(function () {
            if (typeof(time_left) === "number") {
                $("#ctweb-smsauth-time-left").attr('class', 'alert alert-info');
                $("#ctweb-smsauth-time-left").text(time_left + " <?=GetMessage("SMS_AUTH_SECONDS_LEFT");?>");
            }
            else
                $("#ctweb-smsauth-time-left").text("<?=GetMessage("SMS_AUTH_TIME_OUT");?>");
            if (time_left <= 0) {
                clearInterval(timer); //
                $("#ctweb-smsauth-time-left").removeClass("alert-info");
                $("#ctweb-smsauth-time-left").addClass("alert-danger");
                $("#ctweb-smsauth-time-left").text("<?=GetMessage("SMS_AUTH_TIME_OUT");?>");
            }
            time_left -= 1;
        }, 1000);
        <?if (isset($arResult["CODE_SENDED"])):?>
        console.log("<?=$arResult["CODE_SENDED"];?>");
        <?endif;?>
    });
</script>
