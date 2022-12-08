<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var $APPLICATION CMain* */ ?>
<div class="search-page">
    <form action="" method="get">
        <input type="hidden" name="how" value="<? echo $arResult["REQUEST"]["HOW"] == "d" ? "d" : "r" ?>"/>
                <?  if ($arParams["SHOW_WHEN"]): ?>
        <script>
            var switch_search_params = function () {
                var sp = document.getElementById('search_params');
                var flag;

                if (sp.style.display == 'none') {
                    flag = false;
                    sp.style.display = 'block'
                } else {
                    flag = true;
                    sp.style.display = 'none';
                }

                var from = document.getElementsByName('from');
                for (var i = 0; i < from.length; i++)
                    if (from[i].type.toLowerCase() == 'text')
                        from[i].disabled = flag

                var to = document.getElementsByName('to');
                for (var i = 0; i < to.length; i++)
                    if (to[i].type.toLowerCase() == 'text')
                        to[i].disabled = flag

                return false;
            }
        </script>
        <br/><a class="search-page-params" href="#"
                onclick="return switch_search_params()"><? echo GetMessage('CT_BSP_ADDITIONAL_PARAMS') ?></a>
        <div id="search_params" class="search-page-params"
             style="display:<? echo $arResult["REQUEST"]["FROM"] || $arResult["REQUEST"]["TO"] ? 'block' : 'none' ?>">
            <? $APPLICATION->IncludeComponent(
                'bitrix:main.calendar',
                '',
                array(
                    'SHOW_INPUT' => 'Y',
                    'INPUT_NAME' => 'from',
                    'INPUT_VALUE' => $arResult["REQUEST"]["~FROM"],
                    'INPUT_NAME_FINISH' => 'to',
                    'INPUT_VALUE_FINISH' => $arResult["REQUEST"]["~TO"],
                    'INPUT_ADDITIONAL_ATTR' => 'size="10"',
                ),
                null,
                array('HIDE_ICONS' => 'Y')
            ); ?>
        </div>
                <?  endif ?>
    </form>
</div>
