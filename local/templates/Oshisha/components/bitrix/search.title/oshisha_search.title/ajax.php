<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["CATEGORIES"]))
    return;
?>
<div class="bx_searche">
    <?
    $dbStatistic = CSearchStatistic::GetList(
            array("TIMESTAMP_X"=>'DESC'),
            array("STAT_SESS_ID" => $_SESSION['SESS_SESSION_ID']),
            array('TIMESTAMP_X', 'PHRASE')
    );
    $dbStatistic->NavStart(3);
    $testar = [];
    while( $arStatistic = $dbStatistic->Fetch()){
        $testar[] = $arStatistic;
//        print_r($arStatistic);
    }

//    $session_id = 1;
//    $artest = [];
//    if ($rs = CSession::GetByID($_SESSION['SESS_SESSION_ID'])) {
//        $ar = $rs->Fetch();
//        $artest[] = $ar;
//         выведем параметры сессии
//        echo "<pre>"; print_r($ar); echo "</pre>";
//    }

//    print_r($testar);




    ?>
    <?foreach($arResult["CATEGORIES"] as $category_id => $arCategory):?>
        <?foreach($arCategory["ITEMS"] as $i => $arItem):?>
            <?//echo $arCategory["TITLE"]?>
            <?if($category_id === "all"):?>
<!--                <div class="bx_item_block" style="min-height:0">-->
<!--                    <div class="bx_img_element"></div>-->
<!--                    <div class="bx_item_element"><hr></div>-->
<!--                </div>-->
                <div class="bx_item_block all_result" onclick="window.location='<?echo $arItem["URL"]?>';">
                    <div class="bx_item_element">
                        <span class="all_result_title">
                            <a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a>
                        </span>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            <?elseif(isset($arResult["ELEMENTS"][$arItem["ITEM_ID"]])):
                $arElement = $arResult["ELEMENTS"][$arItem["ITEM_ID"]];?>
                <div class="bx_item_block" onclick="window.location='<?echo $arItem["URL"]?>';">
                    <?if (is_array($arElement["PICTURE"])):?>
                        <div class="bx_img_element">
                            <div class="bx_image" style="background-image: url('<?echo $arElement["PICTURE"]["src"]?>')"></div>
                        </div>
                    <?endif;?>
                    <div class="bx_item_element">
                        <a href="<?echo $arItem["URL"]?>"><?echo $arItem["NAME"]?></a>
                        <?
                        foreach($arElement["PRICES"] as $code=>$arPrice)
                        {
                            if ($arPrice["MIN_PRICE"] != "Y")
                                continue;

                            if($arPrice["CAN_ACCESS"])
                            {
                                if($arPrice["DISCOUNT_VALUE"] < $arPrice["VALUE"]):?>
                                    <div class="bx_price">
                                        <?=$arPrice["PRINT_DISCOUNT_VALUE"]?>
                                        <span class="old"><?=$arPrice["PRINT_VALUE"]?></span>
                                    </div>
                                <?else:?>
                                    <div class="bx_price"><?=$arPrice["PRINT_VALUE"]?></div>
                                <?endif;
                            }
                            if ($arPrice["MIN_PRICE"] == "Y")
                                break;
                        }
                        ?>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            <?endif;?>
        <?endforeach;?>
    <?endforeach;?>
    <?if(!empty($testar)):?>
        <div class="bx_item_block popular_searches_title" onclick="">
            <span>Популярные запросы</span>
        </div>
        <?foreach ($testar as $popularSearch):?>
            <div class="bx_item_block popular_searches_result" onclick="popularSearchResultSubmit(this)">
                <div class="bx_item_element"
                     onclick="window.location='<?echo $arResult["FORM_ACTION"].'?q='.$popularSearch["PHRASE"]?>';"
                >
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <span class="popular_search_title">
                        <a href="<?echo $arResult["FORM_ACTION"].'?q='.$popularSearch["PHRASE"]?>">
                            <?echo $popularSearch["PHRASE"]?>
                        </a>
                    </span>
                </div>
                <div style="clear:both;"></div>
            </div>
        <?endforeach;?>
    <?endif;?>
</div>