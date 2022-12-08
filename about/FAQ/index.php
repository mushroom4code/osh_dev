<?php use Bitrix\Main\Page\Asset;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("FAQ");
Asset::getInstance()->addJS("https://www.google.com/recaptcha/api.js");
?>
    <div id="faq" class="box_boxes_delivery mt-3 static">
        <h1>FAQ</h1>
        <div class="d-flex flex-column mb-3" id="FAQ">
            
<?
$k = 0;
$SectionRes = CIBlockSection::GetList(array(), array('IBLOCK_ID'=>24, 'ACTIVE'=>'Y'), false, array("CODE", 'NAME', 'ID', 'IBLOCK_SECTION_ID', 'XML_ID'));
while($arSection = $SectionRes->GetNext()) {
?>
<div class="mb-5">
	  <h4 class="mb-4"><?=$arSection['NAME']?></h4>
	  
	  <div id="<?=$arSection['XML_ID']?>_faq">
			<div class="accordion box_with_map" id="<?=$arSection['CODE']?>">
						<?
							$arFilter = array(
							'IBLOCK_ID' => 24, 
							'ACTIVE' => 'Y',
							'SECTION_ID' => $arSection['ID']
							);
						$resU = CIBlockElement::GetList(Array(), $arFilter, false, false);
						while($rowFaq = $resU->Fetch())
						{	$k++;
						?>
                        <div class="box">
                            <div class="card_delivery card-header" id="questions_del_<?=$k?>">
                                <h3 class="mb-0">
                                    <button class="btn text-left btn_questions d-flex flex-row justify-content-between"
                                            type="button" data-toggle="collapse" data-target="#collapse_questions_del_<?=$k?>"
                                            aria-expanded="true" aria-controls="collapse_questions_del_<?=$k?>">
                                        <div class="mr-4 faq_question"><?=$rowFaq['NAME']?></div>
                                        <i class="fa fa-angle-down" style="" aria-hidden="true"></i>
                                    </button>
                                </h3>
                            </div>
                            <div id="collapse_questions_del_<?=$k?>" class="collapse " aria-labelledby="questions_del_<?=$k?>"
                                 data-parent="#<?=$arSection['CODE']?>">
                                <div class="card-body d-flex row_section">
                                    <div>
                                       <?=$rowFaq['PREVIEW_TEXT']?>
                                    </div>
                                </div>
                            </div>
                        </div>	
						<?
						}
						?>			
		
			</div>
	  </div>
</div>
<?
}

?>			
			

        </div>
		<?/*
        <h4 class="mb-4"><b> Есть вопросы, пожелания или комментарии? Напиши их здесь!</b></h4>
        <div class="mb-5">
            <form class="form_company" id="support">
<!--                <input type="hidden" name="recaptcha_token" value="">-->
<!--                --><?php //echo bitrix_sessid_post(); ?>
<!--                <input type="hidden" name="captcha_sid" value="--><?//= $arResult["CAPTCHA_CODE"] ?><!--"/>-->
<!--                <div class="form-group">-->
<!--                    --><?//= GetMessage("CAPTCHA_REGF_PROMT") ?>
<!--                    <div class="form-group">-->
<!--                        <div class="bx-captcha"><img-->
<!--                                    src="/bitrix/tools/captcha.php?captcha_sid=--><?//= $arResult["CAPTCHA_CODE"] ?><!--"-->
<!--                                    width="180" height="40" alt="CAPTCHA"/></div>-->
<!--                    </div>-->
<!--                    <div class="form-group">-->
<!--                        <input type="text" class="form-control" name="captcha_word" maxlength="50"-->
<!--                               value="" autocomplete="off"/>-->
<!--                    </div>-->
<!--                </div>-->
                <div class="form-group mb-3">
                    <label class="label_company">Поделись с нами!</label>
                </div>
                <div class="form-group mb-3">
                    <input type="text" class="form-control input_lk" id="Name" placeholder="ФИО">
                </div>
                <div class="form-group mb-3">
                    <input type="text" data-name="PHONE" class="form-control input_lk" id="phoneNumber" placeholder="Номер телефона">
                </div>
                <div class="form-group mb-5">
                    <textarea class="form-control input_lk" id="text" placeholder="Ваш комментарий"></textarea>
                </div>
                <div class="form-group mb-2">
                    <div class="col-sm-10">
                        <input class="btn link_menu_catalog get_code_button"
                               type="submit"
                               value="Отправить"
                               onclick="this.form.recaptcha_token.value = window.recaptcha.getToken()">
                    </div>
                </div>
            </form>
        </div>
		
		*/?>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
