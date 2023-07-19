<?php if (!empty($arResult['popularSearches'])): ?>
	<div class="bx_item_block popular_searches_title" onclick="">
		<span>Популярные запросы</span>
	</div>
	<? foreach ($arResult['popularSearches'] as $popularSearch): ?>
		<div class="bx_item_block popular_searches_result" onclick="popularSearchResultSubmit(this)">
			<div class="bx_item_element"
			     onclick="window.location='<? echo $arResult["FORM_ACTION"] . '?q=' . $popularSearch["PHRASE"] ?>';">
				<i class="fa fa-search" aria-hidden="true"></i>
				<span class="popular_search_title">
                        <a href="<? echo $arResult["FORM_ACTION"] . '?q=' . $popularSearch["PHRASE"] ?>">
                            <? echo $popularSearch["PHRASE"] ?>
                        </a>
                    </span>
			</div>
			<div style="clear:both;"></div>
		</div>
	<? endforeach; ?>
<? endif;