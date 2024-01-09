<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Контакты");

use Bitrix\Conversion\Internals\MobileDetect;
use enterego\EnteregoUser;

$mobile = new MobileDetect();
$option = json_decode(COption::GetOptionString("BBRAIN", 'SETTINGS_SITE'));
$userData = EnteregoUser::getInstance();
?>
    <link rel="preconnect" href="//api-maps.yandex.ru">
    <link rel="dns-prefetch" href="//api-maps.yandex.ru">
    <div class="mb-8" id="box_contacts">
        <h4 class="flex flex-row items-center mb-8 mt-5 text-3xl font-bold dark:font-medium text-textLight
            dark:text-textDarkLightGray">Контакты</h4>
        <div class="mb-5 flex flex-col">
            <div class="flex md:flex-row flex-col mb-12">
                <div class="flex flex-col md:w-1/3 w-full justify-between h-auto mb-3">
                    <div class="md:mr-5 mr-0 p-8 bg-textDark dark:bg-darkBox h-full rounded-lg">
                        <h5 class="text-xl font-semibold dark:font-medium text-tagFilterGray dark:text-textDarkLightGray mb-5">
                            Режим работы
                        </h5>
                        <div class="flex flex-col justify-between items-between">
                            <span class="mb-4 text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                               Мы работаем ежедневно<br>
                                <span class="font-normal dark:font-light">с 10:00 до 20:00.</span>
                            </span>
                            <span class="text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                                Самовывоз со склада доступен<br>
                                 <span class="font-normal dark:font-light">
                                     пн-пт:  с 11:00 до 19:00 <br class="mb-2">
                                     сб:  с 11:00 до 14:00 <br>
                                     вс: доставка и самовывоз не осуществляется
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:w-1/3 w-full justify-between h-auto mb-3">
                    <div class="md:mr-5 mr-0 p-8 bg-textDark dark:bg-darkBox h-full rounded-lg">
                        <h5 class="text-xl font-semibold dark:font-medium text-tagFilterGray dark:text-textDarkLightGray mb-5">
                            Телефоны
                        </h5>
                        <div class="flex flex-col justify-between items-between">
                           <span class="mb-4 text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                                Общий :
                                 <span class="font-normal dark:font-light text-hover-red underline">
                                    <a href="tel:88006004424"> +7 (800) 600-44-24</a>
                                </span>
                            </span>
                            <span class="mb-4 text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                               Для розничных покупателей : <br>
                                  <span class="font-normal dark:font-light underline text-hover-red">
                                     <a href="https://api.whatsapp.com/send?phone=79031184521"> +7 (903) 118-45-21</a>
                                 </span>
                            </span>
                            <span class="text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                                Для оптовых партнеров : <br>
                                  <span class="font-normal dark:font-light underline text-hover-red">
                                     <a href="https://api.whatsapp.com/send?phone=79031184957"> +7 (903) 118-49-57</a>
                                 </span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col md:w-1/3 w-full justify-between h-auto mb-3">
                    <div class="p-8 md:mr-5 mr-0 bg-textDark dark:bg-darkBox h-full rounded-lg">
                        <h5 class="text-xl font-semibold dark:font-medium text-tagFilterGray dark:text-textDarkLightGray mb-5">
                            Email</h5>
                        <div class="flex flex-col justify-between items-between">
                            <span class="mb-4 text-sm text-tagFilterGray dark:text-textDarkLightGray font-medium">
                                По вопросам сотрудничества и всего остального:<br>
                                 <span class="font-normal dark:font-light underline text-hover-red">
                                    <a href="mailto:info@oshisha.net">info@oshisha.net</a>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="mb-8 text-2xl font-bold dark:font-medium dark:text-textDarkLightGray text-textLight">
                Адрес склада
            </h4>
            <div class="flex md:flex-row flex-col mb-3">
                <div class="flex mb-3 mr-3 md:w-1/2 w-full">
                    <div class="md:mr-5 mr-0 p-8 bg-textDark dark:bg-darkBox h-full rounded-lg w-full flex flex-row">
                        <svg width="51" height="50" viewBox="0 0 61 60" fill="none" xmlns="http://www.w3.org/2000/svg"
                             class="mr-4">
                            <path d="M50.1484 50.906C53.7147 48.9615 55.8572 46.4682 55.8572 43.75C55.8572 40.8687 53.4502 38.2403 49.4914 36.25C44.9137 33.9485 38.2614 32.5 30.8572 32.5C23.453 32.5 16.8006 33.9485 12.223 36.25C8.2643 38.2403 5.85718 40.8687 5.85718 43.75C5.85718 46.6313 8.2643 49.2597 12.223 51.25C16.8006 53.5515 23.453 55 30.8572 55C38.6237 55 45.5629 53.4063 50.1484 50.906Z"
                                  fill="white"/>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M13.3572 21.2866C13.3572 12.2917 21.1922 5 30.8572 5C40.5222 5 48.3572 12.2917 48.3572 21.2866C48.3572 30.211 42.7717 40.6247 34.0574 44.349C32.0259 45.217 29.6884 45.217 27.6569 44.349C18.9426 40.6247 13.3572 30.211 13.3572 21.2866ZM30.8572 27.5C33.6187 27.5 35.8572 25.2615 35.8572 22.5C35.8572 19.7386 33.6187 17.5 30.8572 17.5C28.0957 17.5 25.8572 19.7386 25.8572 22.5C25.8572 25.2615 28.0957 27.5 30.8572 27.5Z"
                                  fill="#FF0803"/>
                        </svg>
                        <b class="text-md text-dark dark:text-textDarkLightGray font-medium">
                            г. Москва, ул. Краснобогатырская, 2с2
                            <span class="text-hover-red">(Пешком)</span>
                            <br/>
                            (вход со стороны Краснобогатырской)
                        </b>
                    </div>
                </div>
                <div class="flex md:w-1/2 w-full mb-3">
                    <div class="md:mr-5 mr-0 p-8 bg-textDark dark:bg-darkBox h-full rounded-lg w-full flex flex-row">
                        <svg width="51" height="50" viewBox="0 0 61 60" fill="none" xmlns="http://www.w3.org/2000/svg"
                             class="mr-4">
                            <path d="M50.1484 50.906C53.7147 48.9615 55.8572 46.4682 55.8572 43.75C55.8572 40.8687 53.4502 38.2403 49.4914 36.25C44.9137 33.9485 38.2614 32.5 30.8572 32.5C23.453 32.5 16.8006 33.9485 12.223 36.25C8.2643 38.2403 5.85718 40.8687 5.85718 43.75C5.85718 46.6313 8.2643 49.2597 12.223 51.25C16.8006 53.5515 23.453 55 30.8572 55C38.6237 55 45.5629 53.4063 50.1484 50.906Z"
                                  fill="white"/>
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                  d="M13.3572 21.2866C13.3572 12.2917 21.1922 5 30.8572 5C40.5222 5 48.3572 12.2917 48.3572 21.2866C48.3572 30.211 42.7717 40.6247 34.0574 44.349C32.0259 45.217 29.6884 45.217 27.6569 44.349C18.9426 40.6247 13.3572 30.211 13.3572 21.2866ZM30.8572 27.5C33.6187 27.5 35.8572 25.2615 35.8572 22.5C35.8572 19.7386 33.6187 17.5 30.8572 17.5C28.0957 17.5 25.8572 19.7386 25.8572 22.5C25.8572 25.2615 28.0957 27.5 30.8572 27.5Z"
                                  fill="#FF0803"/>
                        </svg>
                        <b class="text-md text-dark dark:text-textDarkLightGray font-medium">г. Москва, ул.
                            Краснобогатырская, 2с64
                            <span class="text-hover-red"> (на Авто)</span>
                            <br/>(въезд со стороны проспекта Ветеранов)
                        </b>
                    </div>
                </div>
            </div>
            <?php if (!$mobile->isMobile()) { ?>
                <section class="box_map mb-5">
                    <script type="text/javascript" charset="utf-8"
                            src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A149de29607a13c15d0cddf04f2230c7b147fd3f62f69e7b9461ec5ac48550769&amp;width=100%&amp;height=400&amp;lang=ru_RU&amp;scroll=true"></script>
                </section>
            <?php } else { ?>
                <section class="box_map_mobile mb-5">
                    <script type="text/javascript" charset="utf-8"
                            src="https://api-maps.yandex.ru/services/constructor/1.0/js/?um=constructor%3A149de29607a13c15d0cddf04f2230c7b147fd3f62f69e7b9461ec5ac48550769&amp;width=320&amp;height=320&amp;lang=ru_RU&amp;scroll=false"></script>
                </section>
            <?php } ?>
        </div>
        <div class="flex lg:flex-row flex-col">
            <div class="lg:mb-16 mb-5 lg:w-1/2 w-full">
                <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/include/forms/feedback.php'); ?>
            </div>
            <div class="flex flex-col lg:w-1/2 w-full lg:ml-14 ml-0 mt-5">
                <h4 class="mb-8 text-2xl font-bold dark:font-medium dark:text-textDarkLightGray text-textLight">
                    Социальные сети и мессенджеры</h4>
                <div class="mb-16">
                    <nav class="flex flex-row w-full">
                        <a href="<?= $option->TG; ?>" target="_blank" class="mr-8">
                            <img class="w-12 tg h-12" src="<?= SITE_TEMPLATE_PATH . '/images/tg.svg' ?>"/>
                        </a>
                        <a href="<?= 'https://api.whatsapp.com/send?phone=' . $option->PHONE_WTS ?>" target="_blank"
                           class="mr-8">
                            <img class="w-12 h-12 ws" src="<?= SITE_TEMPLATE_PATH . '/images/ws.svg' ?>"/>
                        </a>

                        <a href="<?= $option->VK_LINK; ?>" target="_blank" class="mr-8">
                            <img class="w-16 vk h-12" src="<?= SITE_TEMPLATE_PATH . '/images/vk.svg' ?>"/>
                        </a>
                        <a href="<?= $option->DZEN; ?>" target="_blank" class="mr-8">
                            <img class="w-32 h-12 dzen" src="<?= SITE_TEMPLATE_PATH . '/images/dzen.svg' ?>"/>
                        </a>
                    </nav>
                </div>
                <h4 class=" mb-8 text-2xl font-bold dark:font-medium dark:text-textDarkLightGray text-textLight">
                    Реквизиты</h4>
                <div>
                    <p>ООО "СМАК-СУЛТАНА",</p>
                    <p>ИНН: 7715441408</p>
                </div>
            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>