<?php

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
    $name = trim($USER->GetLogin()); ?>

<div class="left-menu dark:bg-darkBox bg-white md:w-72 w-full md:h-auto h-full md:rounded-xl rounded-0
 p-10 mr-8 min-h-[480px] justify-between">
    <div class="box_with_photo flex flex-col mb-7">
        <svg width="100" height="92" viewBox="0 0 92 84" class="rounded-lg"
             xmlns="http://www.w3.org/2000/svg">
            <g clip-path="url(#clip0_1890_10033)">
                <path d="M0 0H91.07V73.0449C91.07 78.5678 86.5928 83.0449 81.07 83.0449H9.99999C4.47715 83.0449 0 78.5678 0 73.0449V0Z"
                      class="dark:fill-lightGrayBg fill-white"/>
                <rect x="0.5" y="0.5" width="91" height="84" rx="9.5"
                      class="stroke-white  dark:fill-[#393939] fill-white"/>
                <path d="M15.1279 83.564L15.1279 74.9453C15.1279 69.4391 17.5266 64.1584 21.7964 60.2649C26.0661 56.3714 31.8571 54.1841 37.8954 54.1841H53.0737C59.1121 54.1841 64.9031 56.3714 69.1728 60.2649C73.4425 64.1584 75.8412 69.4391 75.8412 74.9453V83.564"
                      class="fill-lightGrayBg  dark:fill-white"/>
                <path d="M15.1279 83.564L15.1279 74.9453C15.1279 69.4391 17.5266 64.1584 21.7964 60.2649C26.0661 56.3714 31.8571 54.1841 37.8954 54.1841H53.0737C59.1121 54.1841 64.9031 56.3714 69.1728 60.2649C73.4425 64.1584 75.8412 69.4391 75.8412 74.9453V83.564"
                      class="stroke-white fill-lightGrayBg dark:fill-white" stroke-linecap="round"/>
                <path d="M45.5367 42.0536C38.3279 42.0536 34.8005 40.4085 34.8005 35.2548C34.8005 30.0533 38.3376 28.4052 45.5367 28.4052C52.7359 28.4052 56.2312 30.0533 56.2312 35.2548C56.2312 40.4085 52.7038 42.0536 45.5367 42.0536ZM45.5367 48.0927C56.5487 48.0927 64.0236 44.3777 64.0236 35.2548C64.0236 26.084 56.5487 22.3721 45.5367 22.3721C34.4766 22.3721 27.0498 26.084 27.0498 35.2548C27.0498 44.3777 34.4766 48.0927 45.5367 48.0927Z"
                      class="fill-light-red dark:fill-white"/>
                <path fill-rule="evenodd" clip-rule="evenodd"
                      d="M61.9829 24.2725C57.9566 21.3628 52.6932 20.3278 42.6041 20.0302C32.4644 19.7012 30.226 13.034 29.8958 9.90526C30.4031 10.3785 31.5099 11.0164 33.2686 11.6701C32.3233 9.76471 32.1121 7.85645 32.1435 6.57031C33.3461 7.99409 38.743 11.1049 49.739 13.088C60.6079 15.0482 62.3877 21.3307 62.0434 24.2169C62.0437 24.2505 62.0438 24.2837 62.0434 24.3165C62.0395 24.3136 62.0356 24.3107 62.0316 24.3079C62.0312 24.3108 62.0308 24.3137 62.0304 24.3166C62.0146 24.3019 61.9988 24.2872 61.9829 24.2725Z"
                      class="fill-light-red dark:fill-white"/>
            </g>
        </svg>
        <a href="/personal/" class="text-textLight dark:text-textDarkLightGray text-md font-semibold mt-3 mb-2"
           id="profile_people"><span><?= htmlspecialcharsbx($name) ?></span>
        </a>
        <?php if ($APPLICATION->GetCurPage() != '/personal/private/'): ?>
            <a href="/personal/private/" class="text-textLight dark:text-white text-xs font-light">Изменить
                профиль</a>
        <?php endif; ?>
    </div>
    <div class="personal_hide flex flex-col">
        <a href="#personal_orders_bar"
           class="dark:text-textDarkLightGray text-md dark:hover:text-white hover:text-light-red font-medium text-textLight mb-3"
           data-toggle="collapse" aria-controls="personal_orders_bar"
           aria-expanded="false" id="not_link">Заказы</a>
        <div id="personal_orders_bar" class="flex flex-col mb-3">
            <a href="/personal/orders/"
               class="dark:text-textDarkLightGray text-sm font-light text-textLight mb-3 dark:hover:text-white hover:text-light-red"
               id="personal_orders">В обработке</a>
            <a href="/personal/orders/?filter_history=Y"
               class="dark:text-textDarkLightGray text-sm font-light dark:hover:text-white hover:text-light-red mb-3 text-textLight"
               id="personal_orders_filter_history_Y">Все заказы</a>
            <a href="/personal/orders/?show_canceled=Y"
               class="dark:text-textDarkLightGray text-sm font-light mb-3 text-textLight dark:hover:text-white hover:text-light-red"
               id="#">Завершено</a>
            <a href="/personal/orders/?show_delivery=Y"
               class="dark:text-textDarkLightGray text-sm font-light mb-3 text-textLight dark:hover:text-white hover:text-light-red"
               id="#">В доставке</a>
        </div>
        <a href="/personal/contragents/"
           class="dark:text-textDarkLightGray dark:hover:text-white hover:text-light-red text-md font-medium mb-4 text-textLight"
           title="Контрагенты"
           id="personal_contragents">Контрагенты</a>
        <a href="/personal/subscribe/"
           class="dark:text-textDarkLightGray dark:hover:text-white hover:text-light-red text-md font-medium mb-4 text-textLight"
           title="Контрагенты"
           id="personal_contragents">Избранное</a>
        <a href="/personal/subscriptions/"
           class="dark:text-textDarkLightGray dark:hover:text-white hover:text-light-red text-md font-medium mb-4 text-textLight"
           title="Подписки на товары">Подписки на товары</a>
        <a href="/?logout=yes&<?= bitrix_sessid_get() ?>"
           class="dark:text-white mb-4 text-sm font-medium flex flex-row text-textLight dark:hover:text-white hover:text-light-red mt-4 items-center"
           title="Выйти"
           id="logoutUser">
            <svg width="24" height="26" viewBox="0 0 24 26" fill="none" class="mr-3 stroke-black dark:stroke-white"
                 xmlns="http://www.w3.org/2000/svg">
                <path d="M14.1914 13.0831H22.4988M22.4988 13.0831L18.9385 16.7776M22.4988 13.0831L18.9385 9.38867"
                      stroke-width="2.17" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M18.6147 5.69444V4.46296C18.6147 3.10271 17.5521 2 16.2412 2H4.37353C3.06267 2 2 3.10271 2 4.46296V21.7037C2 23.064 3.06267 24.1667 4.37353 24.1667H16.2412C17.5521 24.1667 18.6147 23.064 18.6147 21.7037V20.4722"
                      stroke-width="2.17" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Выйти</a>
    </div>
</div>
