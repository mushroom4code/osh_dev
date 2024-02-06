<?php use Bitrix\Main\Page\Asset;
use enterego\EnteregoUser;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
global $USER;
$APPLICATION->SetTitle("FAQ");
//Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js");
$userData = EnteregoUser::getInstance();
if ($USER->IsAuthorized()) {
    ?>
    <div id="faq" class="box_boxes_delivery mt-3 static">
        <h4 class="flex flex-row items-center mb-8 mt-5 text-3xl font-bold dark:font-medium text-textLight
            dark:text-textDarkLightGray">FAQ</h4>
        <div class="flex flex-col mb-3 mt-4" id="FAQ">
            <?php
            $k = 0;
            $SectionRes = CIBlockSection::GetList(array(),
                array('ACTIVE' => 'Y', 'IBLOCK_CODE' => 'FAQ'),
                false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID')
            );
            while ($arSection = $SectionRes->GetNext()) { ?>
                <div class="mb-5">
                    <h4 class="md:text-2xl text-lg mb-5 font-semibold dark:font-normal text-textLight dark:text-textDarkLightGray my-5"><?= $arSection['NAME'] ?></h4>
                    <div id="<?= $arSection['XML_ID'] ?>_faq" data-accordion="collapse">
                        <div id="<?= $arSection['CODE'] ?>" class="accordion box_with_map">
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
                                <h2 id="accordion-collapse-heading-<?= $k ?>" class="open-accordion"
                                    onclick="showHideBox(this)">
                                    <button type="button" class="flex items-center justify-between w-full p-5 font-medium
                            rtl:text-right dark:text-textDarkLightGray border-b border-neutral-200 dark:border-neutral-700
                             focus:ring-4 focus:ring-neutral-200 text-left dark:focus:ring-neutral-800
                              hover:bg-neutral-100 dark:hover:bg-neutral-800 gap-3 md:text-base text-sm text-dark dark:font-normal
                              dark:bg-darkBox bg-white"
                                            data-accordion-target="#accordion-collapse-body-<?= $k ?>"
                                            aria-expanded="true"
                                            aria-controls="accordion-collapse-body-<?= $k ?>">
                                        <?= $rowFaq['NAME'] ?>
                                        <svg data-accordion-icon class="w-3 h-3 rotate-180 shrink-0" aria-hidden="true"
                                             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                  stroke-width="2" d="M9 5 5 1 1 5"/>
                                        </svg>
                                    </button>
                                </h2>
                                <div id="accordion-collapse-body-<?= $k ?>" class="hidden"
                                     aria-labelledby="accordion-collapse-heading-<?= $k ?>">
                                    <div class="p-5 border border-b-0 border-neutral-200 dark:border-neutral-700 dark:bg-neutral-900">
                                        <p class="mb-2 text-dark dark:text-textDarkLightGray font-normal dark:font-light md:text-sm text-xs">
                                            <?= $rowFaq['PREVIEW_TEXT'] ?>
                                        </p>
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
        <?php require_once($_SERVER["DOCUMENT_ROOT"] . '/local/templates/Oshisha/include/forms/feedback.php'); ?>
        <script>
            function showHideBox(item) {
                const idHeader = item.id
                const bodyAsk = document.querySelector('[aria-labelledby="' + idHeader + '"]').classList;
                const boolOpen = bodyAsk.contains('hidden')
                boolOpen ? bodyAsk.remove('hidden') : bodyAsk.add('hidden')
            }
        </script>
    </div>
<?php } else { ?>
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <p class="mb-2 mt-5 font-20 font-weight-bolder text-center"> Для ознакомления с информацией необходимо
            <a href="javascript:void(0)" class="link_header_box color-redLight text-decoration-underline">авторизоваться.</a>
        </p>
    </div>
    <?php
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
