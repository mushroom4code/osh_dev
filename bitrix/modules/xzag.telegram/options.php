<?php
/** @global CMain $APPLICATION */
/** @global string $RestoreDefaults */

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Context;
use Bitrix\Main\Db\SqlQueryException;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Application;
use Xzag\Telegram\Container;
use Xzag\Telegram\Data\ProxySettings;
use Xzag\Telegram\Event\Factory;

$module_id = 'xzag.telegram';
$moduleAccessLevel = $APPLICATION->GetGroupRight($module_id);
if ($moduleAccessLevel < 'R') {
    return;
}

try {
    if (!Loader::includeModule($module_id)) {
        throw new LoaderException('Module not found');
    }
} catch (LoaderException $e) {
    echo (new CAdminMessage([
        'DETAILS' => $e->getMessage(),
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('XZAG_TELEGRAM_REQUIREMENTS_FAILED'),
        'HTML' => true
    ]))->Show();
    return;
}

$hasErrors = false;
$moduleClass = new xzag_telegram();
if (!$moduleClass->checkRequirements()) {
    $hasErrors = true;
    echo (new CAdminMessage([
        'DETAILS' => $APPLICATION->GetException(),
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('XZAG_TELEGRAM_REQUIREMENTS_FAILED'),
        'HTML' => true
    ]))->Show();
}

$container = Container::instance();
$factory = new Factory();
$eventOptions = $factory->getEventsOptions();

if ($_SERVER['REQUEST_METHOD'] === 'GET'
    && $moduleAccessLevel == 'W'
    && !empty($_GET['RestoreDefaults'])
    && check_bitrix_sessid()
) {
    try {
        Option::delete($module_id);
        $container->refreshOptions();
    } catch (ArgumentNullException $e) {
        echo (new CAdminMessage([
            'DETAILS' => $e->getMessage(),
            'TYPE' => 'ERROR',
            'MESSAGE' => Loc::getMessage('XZAG_TELEGRAM_SETTINGS_UPDATE_FAILED'),
            'HTML' => true
        ]))->Show();
    }
}

$templateErrors = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $moduleAccessLevel == 'W' && check_bitrix_sessid()) {
    if (isset($_POST['Update']) && $_POST['Update'] === 'Y') {
        $token         = isset($_POST['token']) ? trim($_POST['token']) : '';
        $chatId        = isset($_POST['chat_id']) ? trim($_POST['chat_id']) : '';
        $notifications = isset($_POST['notifications']) ? $_POST['notifications'] : [];
        $messages      = isset($_POST['messages']) ? $_POST['messages'] : [];

        $debug         = isset($_POST['debug']) ? $_POST['debug'] : '';

        $proxySettings = ProxySettings::make(isset($_POST['proxy']) ? $_POST['proxy'] : []);

        $templateService = $container->getTemplateService();

        $dbConnection = Application::getConnection();

        try {
            $dbConnection->startTransaction();

            try {
                Option::set($module_id, 'debug', $debug === 'on' ? 'Y' : 'N');

                Option::set($module_id, 'token', $token);
                Option::set($module_id, 'chat_id', $chatId);

                // deactivate options
                foreach ($eventOptions as $eventOption) {
                    $name = $eventOption::getName();
                    Option::set($module_id, "notifications[{$name}]", 'N');
                    Option::delete($module_id, ['name' => "messages[{$name}]"]);
                }

                foreach (array_keys($notifications) as $key) {
                    Option::set($module_id, "notifications[{$key}]", 'Y');
                }

                foreach ($messages as $key => $value) {
                    if ($templateService->validate($value)) {
                        Option::set($module_id, "messages[{$key}]", $value);
                    } else {
                        $templateErrors[$key] = $templateService->getErrors();
                        throw new InvalidArgumentException(
                            Loc::getMessage('XZAG_TELEGRAM_SETTINGS_TEMPLATES_ERROR')
                            . ': '
                            . implode('. ', $templateErrors[$key])
                        );
                    }
                }

                // proxy
                Option::set($module_id, 'proxy[enabled]', $proxySettings->isEnabled() ? 'Y' : 'N');
                Option::set($module_id, 'proxy[host]', $proxySettings->host);
                Option::set($module_id, 'proxy[username]', $proxySettings->username);
                Option::set($module_id, 'proxy[password]', $proxySettings->password);

                $dbConnection->commitTransaction();
            } catch (Throwable $e) {
                $dbConnection->rollbackTransaction();

                echo (new CAdminMessage([
                    'DETAILS' => $e->getMessage(),
                    'TYPE' => 'ERROR',
                    'MESSAGE' => Loc::getMessage('XZAG_TELEGRAM_SETTINGS_UPDATE_FAILED'),
                    'HTML' => true
                ]))->Show();
            }
        } catch (SqlQueryException $e) {
            echo (new CAdminMessage([
                'DETAILS' => $e->getMessage(),
                'TYPE' => 'ERROR',
                'MESSAGE' => Loc::getMessage('XZAG_TELEGRAM_SETTINGS_UPDATE_FAILED'),
                'HTML' => true
            ]))->Show();
        }

        $container->refreshOptions();
    }
}

$aTabs = [
    [
        'DIV' => 'edit0',
        'TAB' => Loc::getMessage('XZAG_TELEGRAM_SETTINGS'),
        'ICON' => 'xzag_telegram_settings',
        'TITLE' => Loc::getMessage('XZAG_TELEGRAM_SETTINGS_TITLE')
    ],
    [
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS'),
        'ICON' => 'xzag_telegram_settings',
        'TITLE' => Loc::getMessage('XZAG_TELEGRAM_NOTIFICATIONS_SETTINGS_TITLE')
    ],
    [
        'DIV' => 'edit2',
        'TAB' => Loc::getMessage('XZAG_TELEGRAM_TEMPLATES_SETTINGS'),
        'ICON' => 'xzag_telegram_settings',
        'TITLE' => Loc::getMessage('XZAG_TELEGRAM_TEMPLATES_SETTINGS_TITLE')
    ],
    [
        'DIV' => 'edit3',
        'TAB' => Loc::getMessage('XZAG_TELEGRAM_DEV_SETTINGS'),
        'ICON' => 'xzag_telegram_settings',
        'TITLE' => Loc::getMessage('XZAG_TELEGRAM_DEV_SETTINGS_TITLE')
    ],
];
$tabControl = new CAdminTabControl('xzagTelegramTabControl', $aTabs, true, true);

$tabControl->Begin();
?>
<form
    id="telegram-settings-form"
    method="POST"
    action="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANGUAGE_ID?>&mid=<?=$module_id?>"
    name="xzag_telegram_settings">
    <?php echo bitrix_sessid_post();

    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td style="width: 40%;">
            <?= Loc::getMessage('XZAG_TELEGRAM_TOKEN'); ?><sup><span class="required">*</span></sup>
        </td>
        <td style="width: 60%;">
            <label>
                <input
                    required="required"
                    type="text"
                    size="40"
                    value="<?= htmlspecialcharsbx($token ?: $container->getOption('token', '')) ?>"
                    name="token">
            </label>
        </td>
    </tr>
    <tr>
        <td style="width: 40%;">
            <?= Loc::getMessage('XZAG_TELEGRAM_CHAT_ID'); ?><sup><span class="required">*</span></sup>
        </td>
        <td style="width: 60%;">
            <label>
                <input
                    required="required"
                    type="text"
                    size="40"
                    value="<?= htmlspecialcharsbx($chatId ?: $container->getOption('chat_id', '')) ?>"
                    name="chat_id">
            </label>
        </td>
    </tr>

    <tr>
        <td colspan="2" style="text-align: center;">
            <?= BeginNote();?>
            <?= GetMessage("XZAG_TELEGRAM_PROXY_NOTE");?>
            <?= EndNote(); ?>
        </td>
    </tr>
    <tr>
        <td style="width: 40%;">
            <label for="proxy-enabled-checkbox">
                <?= Loc::getMessage('XZAG_TELEGRAM_PROXY_ENABLED'); ?>
            </label>
        </td>
        <?php $checked = $container->getOption('proxy[enabled]', 'N'); ?>
        <td style="width: 60%;">
            <input
                type="checkbox"
                <?= $checked == 'Y' ? 'checked="checked"' : ''?>
                name="proxy[enabled]"
                id="proxy-enabled-checkbox">
        </td>
    </tr>

    <tr class="proxy-params">
        <td style="width: 40%;"><label><?= Loc::getMessage('XZAG_TELEGRAM_PROXY_HOST'); ?></label></td>
        <td style="width: 60%;">
            <label>
                <input
                    type="text"
                    size="40"
                    value="<?= htmlspecialcharsbx($container->getOption('proxy[host]', '')) ?>"
                    name="proxy[host]">
            </label>
        </td>
    </tr>

    <tr class="proxy-params">
        <td colspan="2" style="text-align: center;">
            <?= BeginNote();?>
            <?= GetMessage('XZAG_TELEGRAM_PROXY_HOST_HINT');?>
            <?= EndNote(); ?>
        </td>
    </tr>

    <tr class="proxy-params">
        <td style="width: 40%;"><?= Loc::getMessage('XZAG_TELEGRAM_PROXY_USERNAME'); ?></td>
        <td style="width: 60%;">
            <label>
                <input
                    type="text"
                    size="40"
                    value="<?= htmlspecialcharsbx($container->getOption('proxy[username]', '')) ?>"
                    name="proxy[username]">
            </label>
        </td>
    </tr>
    <tr class="proxy-params">
        <td style="width: 40%;"><?= Loc::getMessage('XZAG_TELEGRAM_PROXY_PASSWORD'); ?></td>
        <td style="width: 60%;">
          <label>
              <input
                  type="text"
                  size="40"
                  value="<?= htmlspecialcharsbx($container->getOption('proxy[password]', '')) ?>"
                  name="proxy[password]">
          </label>
        </td>
    </tr>

    <?php $tabControl->BeginNextTab();?>

    <?php
    /**
     * @var
     */
    foreach ($eventOptions as $eventOption) :
        $name = $eventOption::getName();
        ?>
        <tr>
            <td style="width: 20%;"><?= Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_TITLE_' . $name); ?></td>
            <?php $checked = isset($notifications)
                ? (isset($notifications[$name]) ? 'Y' : 'N')
                : $container->getOption("notifications[{$name}]", 'N');
            ?>
            <td style="width: 80%;">
                <label>
                    <input
                            type="checkbox"
                        <?= $checked == 'Y' ? 'checked="checked"' : '' ?>
                            name="notifications[<?= htmlspecialcharsbx($name)?>]">
                </label>
            </td>
        </tr>

        <?php
    endforeach;
    ?>

    <?php $tabControl->BeginNextTab();?>

    <tr>
        <td colspan="2" style="text-align: center;">
            <?= BeginNote();?>
            <?= GetMessage('XZAG_TELEGRAM_TEMPLATES_HINT');?>
            <?= EndNote(); ?>
        </td>
    </tr>

    <?php
    /**
     * @var
     */
    foreach ($eventOptions as $eventOption) :
        $name = $eventOption::getName();
        $customMessage = isset($messages[$name]) ? $messages[$name] : $container->getOption("messages[{$name}]", null);
        ?>
    <tr>
        <td style="width: 20%;"><?= Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_TITLE_' . $name); ?></td>
        <td style="width: 80%;">
            <label>
                <select class="templates-type">
                    <option
                            value="templates[<?= htmlspecialcharsbx($name)?>][default]"
                        <?= is_null($customMessage) ? 'selected="selected"' : '' ?>>
                        <?= Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_DEFAULT'); ?>
                    </option>
                    <option
                            value="templates[<?= htmlspecialcharsbx($name)?>][custom]"
                        <?= !is_null($customMessage) ? 'selected="selected"' : '' ?>>
                        <?= Loc::getMessage('XZAG_TELEGRAM_NOTIFICATION_TEMPLATE_CUSTOM'); ?>
                    </option>
                </select>
            </label>
        </td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td class="templates-group">
          <label for="templates[<?= htmlspecialcharsbx($name)?>][default]"></label>
          <textarea
                rows="5"
                style="width: 100%;"
                class="templates-text"
                id="templates[<?= htmlspecialcharsbx($name)?>][default]"
                readonly="readonly"><?= $default = $eventOption::getDefaultTemplate(); ?>
          </textarea>

          <label for="templates[<?= htmlspecialcharsbx($name)?>][custom]"></label>
          <textarea
              rows="5"
              style="width: 100%; <?= isset($templateErrors[$name]) ? 'border: 5px solid red;' : '' ?>"
              class="templates-text"
              name="messages[<?= htmlspecialcharsbx($name)?>]"
              id="templates[<?= htmlspecialcharsbx($name)?>][custom]"><?= $customMessage ?: $default; ?>
          </textarea>
        </td>
    </tr>
        <?php
    endforeach;
    ?>

    <?php $tabControl->BeginNextTab();?>

    <tr>
        <td style="width: 20%;">
            <label for="debug-checkbox">
                <?= Loc::getMessage('XZAG_TELEGRAM_DEBUG_TITLE'); ?>
            </label>
        </td>
        <?php $checked = $container->getOption('debug', 'N'); ?>
        <td style="width: 60%;">
            <input
                    type="checkbox"
                <?= $checked == 'Y' ? 'checked="checked"' : ''?>
                    name="debug"
                    id="debug-checkbox">
        </td>
    </tr>


    <?php $tabControl->Buttons();?>
    <script type="text/javascript">
        function RestoreDefaults()
        {
            if (confirm('<?= addslashes(GetMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING')) ?>')) {
                window.location = '<?= $APPLICATION->GetCurPage() ?>'
                    + '?RestoreDefaults=Y'
                    + '&lang=<?= LANGUAGE_ID ?>'
                    + '&mid=<?= $module_id ?>'
                    + '&<?= bitrix_sessid_get() ?>';
            }
        }
    </script>
    <input
        type="submit"
        <?= $moduleAccessLevel < 'W' ? ' disabled' : '' ?>
        name="Update"
        value="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_SAVE') ?>"
        class="adm-btn-save"
        title="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_SAVE_TITLE') ?>">
    <input type="hidden" name="Update" value="Y">
    <input
        type="reset"
        name="reset"
        value="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_RESET') ?>"
        title="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_RESET_TITLE') ?>">
    <input
        type="button"
        <?= $moduleAccessLevel < 'W' ? ' disabled' : '' ?>
        title="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT_TITLE') ?>"
        onclick="RestoreDefaults();"
        value="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_RESTORE_DEFAULT') ?>">
    <input
        type="button"
        class="adm-btn-save"
        id="test-notification"
        <?= ($moduleAccessLevel < 'W' || $hasErrors) ? ' disabled' : '' ?>
        title="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION_TITLE') ?>"
        value="<?= Loc::getMessage('XZAG_TELEGRAM_OPTIONS_BTN_TEST_NOTIFICATION') ?>">
</form>
<script type="text/javascript">
    BX.ready(function(){
        function toggleProxyParams(enabled)
        {
            [].forEach.call(document.getElementsByClassName('proxy-params'), function(item) {
                if (enabled) {
                    BX.show(item);
                } else {
                    BX.hide(item);
                }
            });
        }

        function toggleTemplateSelectors()
        {
            [].forEach.call(document.getElementsByClassName('templates-text'), function(item) {
                item.setAttribute('disabled', 'disabled');
                BX.hide(item);
            });

            [].forEach.call(document.getElementsByClassName('templates-type'), function(item) {
                var enabledId = item.value;
                var textarea = document.getElementById(enabledId);
                if (textarea) {
                    textarea.removeAttribute('disabled');
                    BX.show(textarea);
                }
            });
        }

        function testNotification()
        {
            var token = document.querySelector('#telegram-settings-form input[name=token]').value;
            var chat_id = document.querySelector('#telegram-settings-form input[name=chat_id]').value;

            if (!token || !chat_id) {
                alert('<?=CUtil::JSEscape(GetMessage('XZAG_TELEGRAM_FORM_ERROR'))?>');
                return false;
            }

            return BX.ajax({
                'method': 'POST',
                'dataType': 'json',
                'url': '/bitrix/tools/xzag.telegram/test_notification.php'
                    + '?lang=<?= Context::getCurrent()->getLanguage() ?>',
                'data': BX.ajax.prepareData({
                    'token': token,
                    'chat_id': chat_id,
                    'proxy[enabled]': document
                        .querySelector('#telegram-settings-form input[name="proxy[enabled]"]')
                        .checked ? 'on' : 'off',
                    'proxy[host]': document
                        .querySelector('#telegram-settings-form input[name="proxy[host]"]')
                        .value,
                    'proxy[username]': document
                        .querySelector('#telegram-settings-form input[name="proxy[username]"]')
                        .value,
                    'proxy[password]': document
                        .querySelector('#telegram-settings-form input[name="proxy[password]"]')
                        .value
                }),
                'onsuccess': function(result) {
                    if(result['result']) {
                        alert('<?= CUtil::JSEscape(GetMessage('XZAG_TELEGRAM_TEST_SUCCESS')) ?>');
                    } else if (result['error']) {
                        alert(
                            '<?= CUtil::JSEscape(GetMessage('XZAG_TELEGRAM_TEST_ERROR'))?> : '
                            + result['error']['message']
                        );
                    }
                },
                'onfailure': function() {
                    alert('<?= CUtil::JSEscape(GetMessage('XZAG_TELEGRAM_TEST_ERROR')) ?>');
                }
            });
        }

        function bindEvents()
        {
            BX.bind(BX('proxy-enabled-checkbox'), 'click', function() {
                toggleProxyParams(this.checked)
            });

            BX.bind(BX('test-notification'), 'click', function() {
                testNotification()
            });

            [].forEach.call(document.getElementsByClassName('templates-type'), function(item) {
                BX.bind(BX(item), 'change', function() {
                    toggleTemplateSelectors();
                });
            });

        }

        bindEvents();
        toggleProxyParams('<?= $container->getOption('proxy[enabled]', 'N') ?>' === 'Y');
        toggleTemplateSelectors();
    });
</script>
<?php
$tabControl->End();
