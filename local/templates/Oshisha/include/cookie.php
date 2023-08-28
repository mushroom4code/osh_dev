<?php
/** @var  CAllUser $USER */
$user_consent = $USER->IsAuthorized() ?
    (new Cuser)->GetById($USER->GetID())->Fetch()[USER_CONSENT_PROPERTY] : false;
if (!$USER->IsAuthorized() || $user_consent != '1'): ?>
    <div id="consent-cookie-popup"
         class="dark:bg-darkBox bg-white shadow-lg dark:shadow-none fixed bottom-10 lg:xl:md:left-10 hidden
         lg:xl:px-8 lg:xl:py-6 px-5 py-4 rounded-2xl <?= $USER->IsAuthorized() ? 'js-auth' : 'js-noauth' ?>">
        <p class="dark:text-textDark xl:lg:text-sm text-xs mb-4 text-center font-medium dark:font-light">
            Мы используем файлы Cookie, чтобы улучшить сайт для вас.
        </p>
        <div class="flex flex-row items-center justify-around">
            <a id="cookie-popup-accept" href="javascript:void(0)"
               class="flex flex-row items-center font-medium dark:font-light text-sm mr-3 hover:underline">
                <div class="rounded-full p-2 dark:border-none border-greenButton dark:bg-grayButton border mr-3">
                    <svg width="18" height="16" viewBox="0 0 18 16" xmlns="http://www.w3.org/2000/svg">
                        <g id="checked-tick_svgrepo.com" clip-path="url(#clip0_1100_2679)">
                            <g id="Group">
                                <g id="Group_2">
                                    <path id="Vector"
                                          d="M16.9823 1.8372C16.8537 1.69539 16.6458 1.69175 16.5171 1.83355L5.37547 13.9342L1.81224 9.78913C1.69678 9.63642 1.48892 9.6146 1.34704 9.74185C1.20847 9.8691 1.18868 10.0982 1.30414 10.2545C1.31404 10.2654 1.32063 10.2763 1.33053 10.2872L5.12471 14.7013C5.1841 14.774 5.26987 14.8141 5.35895 14.8141H5.36554C5.45131 14.8141 5.5338 14.7777 5.5965 14.7086L16.979 2.34623C17.111 2.20807 17.111 1.979 16.9823 1.8372Z"
                                          class="dark:fill-white fill-greenButton stroke-greenButton dark:stroke-white"
                                          stroke-width="2"/>
                                </g>
                            </g>
                        </g>
                        <defs>
                            <clipPath id="clip0_1100_2679">
                                <rect width="17.6197" height="15.1725" class="dark:fill-white fill-greenButton" transform="translate(0.25 0.75)"/>
                            </clipPath>
                        </defs>
                    </svg>
                </div>
                Принять
            </a>
            <a id="cookie-popup-about" class="flex flex-row items-center text-sm font-medium dark:font-light hover:underline"
               href="/about/cookie/">
                <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 33 33" fill="none"
                     class="mr-3">
                    <path d="M16.5 30.25C24.0938 30.25 30.25 24.0938 30.25 16.5C30.25 8.90608 24.0938 2.75 16.5 2.75C8.90608 2.75 2.75 8.90608 2.75 16.5C2.75 19.0044 3.54438 22.0204 4.71429 24.0428L3.30001 28.7571L8.48572 27.8143C10.5082 28.9841 13.9956 30.25 16.5 30.25Z"
                          class="stroke-light-red dark:stroke-dark dark:fill-dark fill-white" stroke-width="1.75"
                          stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12.375 13.5535C12.375 8.74094 19.9375 8.74098 19.9375 13.5535C19.9375 16.991 16.5 16.3033 16.5 20.4283"
                          class="stroke-light-red dark:stroke-white" stroke-width="2.5" stroke-linecap="round"
                          stroke-linejoin="round"/>
                    <path d="M16.5 24.7698L16.5178 24.75" class="stroke-light-red dark:stroke-white"
                          stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Подробнее
            </a>
        </div>
    </div>
<?php endif; ?>