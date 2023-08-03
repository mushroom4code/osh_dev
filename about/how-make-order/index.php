<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Как сделать заказ на выставке");
if (SITE_ID === SITE_EXHIBITION) { ?>
    <div id="content_box_delivery" class="box_boxes_delivery static">
        <h5 class="font-weight-bold mb-4 mt-4">Как сделать заказ на данном мероприятиии - пошаговая инструкция</h5>
    </div>
    <?php
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>