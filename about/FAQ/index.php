<?php use Bitrix\Main\Page\Asset;
use enterego\EnteregoUser;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("FAQ");
//Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js");
$userData = EnteregoUser::isUserAuthorized() ? EnteregoUser::getUserData() : false;
if ($USER->IsAuthorized()) {
    ?>
    <div id="faq" class="box_boxes_delivery mt-3 static">
        <h1>FAQ</h1>
        <div class="d-flex flex-column mb-3 mt-4" id="FAQ">

            <?php
            $k = 0;
            $SectionRes = CIBlockSection::GetList(array(),
                array('ACTIVE' => 'Y', 'IBLOCK_CODE' => 'FAQ'),
                false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID')
            );
            while ($arSection = $SectionRes->GetNext()) { ?>
                <div class="mb-5">
                    <h4 class="mb-4"><?= $arSection['NAME'] ?></h4>

                    <div id="<?= $arSection['XML_ID'] ?>_faq">
                        <div class="accordion box_with_map" id="<?= $arSection['CODE'] ?>">
                            <?php
                            $arFilter = array(
                                'IBLOCK_CODE' => 'FAQ',
                                'ACTIVE' => 'Y',
                                'SECTION_ID' => $arSection['ID']
                            );
                            $resU = CIBlockElement::GetList(array(), $arFilter, false, false);
                            while ($rowFaq = $resU->Fetch()) {
                                $k++;
                                ?>
                                <div class="box">
                                    <div class="card_delivery card-header" id="questions_del_<?= $k ?>">
                                        <h3 class="mb-0">
                                            <button class="btn text-left btn_questions d-flex flex-row justify-content-between"
                                                    type="button" data-toggle="collapse"
                                                    data-target="#collapse_questions_del_<?= $k ?>"
                                                    aria-expanded="true"
                                                    aria-controls="collapse_questions_del_<?= $k ?>">
                                                <div class="mr-4 faq_question"><?= $rowFaq['NAME'] ?></div>
                                                <i class="fa fa-angle-down" style="" aria-hidden="true"></i>
                                            </button>
                                        </h3>
                                    </div>
                                    <div id="collapse_questions_del_<?= $k ?>" class="collapse "
                                         aria-labelledby="questions_del_<?= $k ?>"
                                         data-parent="#<?= $arSection['CODE'] ?>">
                                        <div class="card-body d-flex row_section">
                                            <div>
                                                <?= $rowFaq['PREVIEW_TEXT'] ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
        <h4 class="mb-4"><b> Есть вопросы, пожелания или комментарии? Напиши их здесь!</b></h4>
        <div class="mb-5">
            <form class="form_company form-form " id="support" enctype="multipart/form-data">
                <div class="form-form-wrap">
                    <input type="hidden" name="recaptcha_token" id="recaptchaResponse">
                    <?php echo bitrix_sessid_post(); ?>
                    <div class="form-group mb-3">
                        <label class="label_company">Поделись с нами!</label>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text"
                               class="form-control input_lk"
                               id="Name"
                               name="NAME"
                               placeholder="Пожалуйста, представьтесь*"
                            <?php if ($userData): ?>
                                value="<?= $userData['NAME'] ?? '' ?>"
                            <?php endif; ?>
                        >
                        <div class="er_FORM_NAME error_field"></div>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text"
                               data-name="PHONE-FORM"
                               name="PHONE"
                               class="form-control input_lk"
                               id="phoneNumber"
                               placeholder="Мобильный телефон, чтобы связаться с вами*"
                            <?php if ($userData): ?>
                                value="<?= $userData['PHONE'] ?? '' ?>"
                            <?php endif; ?>
                        >
                        <div class="er_FORM_PHONE error_field"></div>
                    </div>
                    <div class="form-group mb-3">
                        <input type="text"
                               data-name="EMAIL"
                               name="EMAIL"
                               class="form-control input_lk"
                               id="userEmail"
                               placeholder="E-mail если хотите получить ответ на почту"
                            <?php if ($userData): ?>
                                value="<?= $userData['MAIL'] ?? '' ?>"
                            <?php endif; ?>
                        >
                    </div>
                    <div class="form-group mb-3">
                                <textarea class="form-control input_lk" name="MESSAGE" id="text"
                                          placeholder="Сообщение*"></textarea>
                        <div class="er_FORM_MESSAGE error_field"></div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="form-control input_lk input_files drop-zone" id="drop-zone">
                            <div class="drop-message">Перетащите файлы сюда</div>
                            <div class="drop-message">или</div>
                            <label class="upload-file-label">
                                <input type="file" name="upload-files" id="upload-files" class="file-upload"
                                       placeholder="Выберите файл или перетащите сюда" multiple="multiple"
                                       accept=".png, .jpg, .jpeg, .gif">
                                <span class="btn">Выберите файлы</span>
                            </label>
                            <div class="drop-message">Приложить можно до 10 изображений в форматах .jpg, .gif, .png
                                объемом не более 5 Мб
                            </div>
                            <ul class="file-list"></ul>
                        </div>
                        <div class="er_FORM_FILES error_field"></div>
                    </div>

                    <div class="form-group mb-5">
                        <label class="form-control input_lk" style="height: auto">
                            <input name="confirm" type="checkbox" checked="checked">
                            <span class="custom__title">Подтверждаю свое согласие с
                                    <a href="/about/politics/"
                                       target="_blank">положением об обработке персональных данных</a></span>
                        </label>
                        <div class="er_FORM_CONFIRM error_field"></div>
                    </div>

                    <div class="form-group mb-2">
                        <div class="col-sm-10">
                            <input class="btn link_menu_catalog get_code_button"
                                   type="submit"
                                   value="Отправить"
                                   onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                        </div>
                        <div class="error_form error_field"></div>
                    </div>
                </div>
                <div class="form_block_ok">
                    Сообщение отправлено.<br>
                    Мы свяжемся с вами в самое ближайшее время!
                </div>
            </form>
        </div>

    </div>
<?php } else { ?>
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <p class="mb-2 mt-5 font-20 font-weight-bolder text-center"> Для ознакомления с информацией необходимо
            <a href="javascript:void(0)" class="link_header_box color-redLight text-decoration-underline">авторизоваться.</a>
        </p>
    </div>
    <?php
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
