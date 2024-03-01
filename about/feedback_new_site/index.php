<?php use enterego\EnteregoUser;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Обратная связь по новому сайту");
$userData = EnteregoUser::getInstance();
?>
<h5 class="mb-3" id="form"></h5>
<div class="mb-5">
    <form class="send_feed form_company max-w-3xl px-8 py-10 rounded-xl bg-textDark dark:bg-darkBox mb-8 mt-5" id="support">
        <input type="hidden" name="recaptcha_token" value="">
        <div class="form-form-wrap">
            <?php echo bitrix_sessid_post(); ?>
            <div class="form-group mb-8">
                <label class="text-2xl font-medium dark:font-normal text-textLight dark:text-textDarkLightGray">
                    Обратная связь по новому сайту
                </label>
            </div>
            <div class="form-group mb-4">
                <input type="text"
                       class="dark:bg-grayButton bg-white text-textLight dark:text-textDarkLightGray text-sm border-0
                        shadow-none py-3 px-4 outline-none rounded-md input_lk w-full"
                       id="Name"
                       name="NAME"
                       placeholder="Пожалуйста, представьтесь *"
                       value="<?= $userData->getName()['last'] ?? '' ?>"
                />
                <div class="er_FORM_NAME error_field text-xs text-hover-red mt-1 mb-2"></div>
            </div>
            <div class="form-group mb-4">
                <input type="text"
                       data-name="PHONE-FORM"
                       name="PHONE"
                       class="dark:bg-grayButton bg-white text-sm border-0 shadow-none py-3 px-4 outline-none
                           rounded-md input_lk w-full"
                       id="phoneNumber"
                       placeholder="Мобильный телефон, чтобы связаться с вами *"
                       value="<?= $userData->getPhone() ?>"
                >
                <div class="er_FORM_PHONE error_field text-xs text-hover-red mt-1 mb-2"></div>
            </div>
            <div class="form-group mb-5">
                <input type="text"
                       data-name="EMAIL"
                       name="EMAIL"
                       class="dark:bg-grayButton bg-white text-sm border-0 shadow-none py-3 px-4 outline-none
                           rounded-md input_lk w-full"
                       id="userEmail"
                       placeholder="E-mail, для ответа на почту *"
                       value="<?= $userData->getMail() ?>">
                <div class="er_FORM_EMAIL error_field text-xs text-hover-red mt-1 mb-2"></div>
            </div>
            <div class="form-group mb-3">
                    <textarea class="dark:bg-grayButton bg-white text-sm border-0 shadow-none py-3 px-4 outline-none
                           rounded-md input_lk w-full"
                              name="MESSAGE" id="text"
                              placeholder="Ваше сообщение *"></textarea>
                <div class="er_FORM_MESSAGE error_field text-xs text-hover-red mt-1 mb-2"></div>
            </div>

            <div class="form-group mb-7">
                <div class="form-control input_lk input_files drop-zone rounded-lg border border-dashed border-gray-900/25
                 dark:border-tagFilterGray px-6 py-10"
                     id="drop-zone">
                    <div class="drop-message text-xs text-tagFilterGray dark:text-whiteOpacity w-full text-center">
                        Перетащите файлы сюда
                    </div>
                    <div class="drop-message text-xs w-full text-tagFilterGray dark:text-whiteOpacity text-center
                     mb-6">или
                    </div>
                    <label class="mt-2 flex justify-center flex-col mb-4 relative upload-file-label">
                        <div class="text-center">
                            <svg class="mx-auto h-14 w-14 text-gray-300" viewBox="0 0 24 24" fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                            <div class="mt-4 text-sm leading-6 text-gray-600 flex md:flex-row flex-col items-center justify-center mb-4">
                                <div class="cursor-pointer rounded-md bg-white dark:bg-grayButton
                                            font-normal mr-4 px-3 py-1.5  dark:text-white text-hover-red focus-within:outline-none
                                            focus-within:ring-2 focus-within:ring-hover-red focus-within:ring-offset-2
                                             hover:text-hover-red">
                                    Выберите файлы
                                    <input type="file" name="upload-files" id="upload-files"
                                           class="file-upload sr-only w-full h-full top-0 left-0 z-10"
                                           placeholder="Выберите файл или перетащите сюда" multiple="multiple"
                                           accept=".png, .jpg, .jpeg, .gif">
                                </div>
                                <span class="text-xs leading-5 text-gray-600 dark:text-whiteOpacity">PNG, JPG, GIF up to 10MB</span>
                            </div>
                        </div>
                        <div class="drop-message text-xs text-dark dark:text-whiteOpacity w-full text-center">
                            Приложить можно до 10 изображений в форматах .jpg, .gif, .png объемом не более 5 Мб
                        </div>
                    </label>
                    <ul class="file-list flex flex-row flex-wrap"></ul>
                </div>
            </div>
        </div>
        <div class="er_FORM_FILES error_field text-xs text-hover-red mt-1 mb-2"></div>

        <div class="form-group mb-7">
            <label class="form-control input_lk" style="height: auto">
                <input name="confirm" class="check_input xs:p-5 p-4 dark:bg-grayButton checked:hover:bg-grayButton
                         border-iconGray  dark:text-white cursor-pointer font-normal rounded-full text-light-red
                         checked:focus:bg-grayButton mr-2 input_lk_notification" type="checkbox" checked="checked">
                <span class="main-profile-form-label_notification dark:text-textDarkLightGray dark:font-light
                    font-normal md:text-base text-sm">Подтверждаю свое согласие с
                    <a href="/about/politics/"
                       target="_blank" class="font-medium text-hover-red dark:text-white underline">положением об обработке персональных данных</a></span>
            </label>
            <div class="er_FORM_CONFIRM error_field text-xs text-hover-red mt-1 mb-2"></div>
        </div>

        <div class="form-group mb-2">
            <div class="col-sm-10">
                <input class="btn link_menu_catalog get_code_button text-textDark
                        shadow-md flex flex-row justify-center items-center dark:bg-dark-red bg-light-red
                        py-2 px-4 rounded-5 w-48"
                       type="submit"
                       value="Отправить"
                       onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
            </div>
            <div class="error_form error_field text-xs text-hover-red mt-1 mb-2"></div>
        </div>

        <div class="form_block_ok hidden text-sm text-greenButton font-medium mt-1 mb-2">
            Сообщение отправлено.<br>
            Мы свяжемся с вами в самое ближайшее время!
        </div>
    </form>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php") ?>