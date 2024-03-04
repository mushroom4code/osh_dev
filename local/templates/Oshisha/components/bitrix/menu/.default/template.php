<?php use Bitrix\Sale\Exchange\EnteregoUserExchange;

/**
 * @global array $arParams
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $cartId
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
global $USER;
$name = '';

if ($USER->IsAuthorized()) {
    $name = trim($USER->GetFirstName());
}
if (!$name)
    $name = trim($USER->GetLogin());

// Переменная для убора функционала под мобильное приложение
$showUserContent = Enterego\PWA\EnteregoMobileAppEvents::getUserRulesForContent();
?>
<div class="left-menu">
    <div class="box_with_photo">
        <div class="box_photo"></div>
        <div class="flex-column d-flex">
            <a href="/personal/" class="link_lk" id="profile_people">
                <span><?= htmlspecialcharsbx($name) ?></span>
            </a>
            <? if ($APPLICATION->GetCurPage() != '/personal/private/'): ?>
                <a href="/personal/private/" class="link_lk edit_profile" id="personal_private">Изменить профиль</a>
            <? endif; ?>
        </div>
    </div>
    <div class="personal_hide">
        <a href="#personal_orders_bar" class="link_lk" data-toggle="collapse" aria-controls="personal_orders_bar"
           aria-expanded="false" id="not_link">Заказы</a>
        <div id="personal_orders_bar" class="collapse">
            <a href="/personal/orders/" class="link_lk_mini" id="personal_orders">В обработке</a>
            <a href="/personal/orders/?filter_history=Y" class="link_lk_mini" id="personal_orders_filter_history_Y">Все
                заказы</a>
            <a href="/personal/orders/?show_canceled=Y" class="link_lk_mini" id="#">Завершено</a>
            <a href="/personal/orders/?show_delivery=Y" class="link_lk_mini" id="#">В доставке</a>
        </div>
        <a href="/personal/subscriptions/" class="link_lk" title="Подписки на товары">Подписки на товары</a>
        <a style="display:none;" href="/personal/profiles/" class="link_lk" title="Мои компании">Мои компании</a>
        <?php
        $user_id = $USER->GetId();
        //        $getCompanyUser = new EnteregoUserExchange();
        //        $getCompanyUser->USER_ID = $user_id;
        //        $getCompanyUser->GetCompanyForUser();
        //        if (!empty($getCompanyUser->company_user)) {
        //            ?>
        <!--            <a style="display:none;" href="/personal/contragents/" class="link_lk" title="Контрагенты"-->
        <!--               id="personal_contragents">Контрагенты</a>-->
        <!--            <a style="display:none;" href="/personal/workers/" class="link_lk" title="Сотрудники">Сотрудники</a>-->
        <!--        --><?php //} ?>
        <a style="display:none;" href="/personal/support/" class="link_lk" title="Написать обращение"
           id="about_contacts">Поддержка</a>
        <a style="display:none;" href="/personal/subscribe/" class="link_lk" title="Перейти в избранное"
           id="personal_subscribe">Избранное</a>
        <a style="display:none;" href="/personal/account/" class="link_lk" title="Подарочные сертификаты"
           id="personal_account">Подарочные
            сертификаты</a>
        <a href="/?logout=yes&<?= bitrix_sessid_get() ?>" class="link_lk close_session mt-4 red_text" title="Выйти"
           id="logoutUser">Выйти</a>
        <?php
        /**
         * Enterego
         * Если пользователь зашел с мобильной версии приложения app.oshisha.net
         */
        if (!$showUserContent) { ?>
            <a class="delete-profile red_text" href="javascript:void(0)">Удалить профиль</a>
            <script>
                $('.delete-profile').on('click', function () {
                    $('body').append('<div class="position-fixed overlay_top top-0 height-100 width-100 left-0 ' +
                        'd-flex justify-content-center align-items-center">' +
                        '<div class="bg-light p-5 d-flex flex-column br-10">' +
                        '<h5 class="mb-5">Уверены что хотите удалить свой профиль?</h5>' +
                        '<div class="d-flex flex-row justify-content-between">' +
                        '<span class="send-remove link_red_button color-white font-weight-500 br-10 font-14 p-2 width_50 text-center">Удалить</span>' +
                        '<span class="font-weight-500 btn_black color-white br-10 font-14 p-2 width_50 text-center remove-window">Отмена</span></div></div></div>');

                    $('.remove-window').on('click', function () {
                        $(this).closest('.overlay_top').remove();
                    })
                    $('.send-remove').on('click', function () {
                        BX.ajax({
                            method: 'POST',
                            url: '/local/ajax/sendPWA.php',
                            data: {action: 'sendMobileRemoveUser'},
                            onsuccess: function (result) {
                                if (result === 'true') {
                                    console.log('https://' + window.location.hostname + '/');
                                    window.location.href = 'https://' + window.location.host + '/'
                                }
                            }
                        });
                    });
                });
            </script>
        <?php } ?>
        <div style="font-size:20px;"><br> <br> <br> <br> <br></div>
    </div>
</div>