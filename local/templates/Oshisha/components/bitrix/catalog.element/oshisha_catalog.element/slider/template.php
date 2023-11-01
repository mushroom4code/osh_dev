<div class="col-md-6 col-sm-6 col-lg-6 product_left col-12">
	<div class="product-item-detail-slider-container <?php if (!empty($taste['VALUE'])) { ?> p-lg-md-25
                    <?php } ?>" id="<?= $itemIds['BIG_SLIDER_ID'] ?>">
		<div class="variation_taste" style="max-width: 10%; height: 90%">
			<?php foreach ($taste['VALUE'] as $key => $nameTaste) {
				foreach ($taste['VALUE_XML_ID'] as $keys => $value) {
					if ($key === $keys) {
						$color = explode('#', $value); ?>
						<span class="taste" data-background="<?= '#' . $color[1] ?>"
						      id="<?= $color[0] ?>">
                                    <?= $nameTaste ?>
                                    </span>
					<?php }
				}
			} ?>
		</div>
		<div class="product-item-detail-slider-block
                    <?= ($arParams['IMAGE_RESOLUTION'] === '1by1' ? 'product-item-detail-slider-block-square' : '') ?>"
		     data-entity="images-slider-block">
			<div>
                            <span class="product-item-detail-slider-left carousel_elem_custom"
                                  data-entity="slider-control-left"
                                  style="display: none;"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
				<span class="product-item-detail-slider-right carousel_elem_custom"
				      data-entity="slider-control-right"
				      style="display: none;"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
				<div class="product-item-detail-slider-images-container" data-entity="images-container">
					<?php if (!empty($actualItem['PICTURE'][0]['SRC'])) {
						foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
							<div class="product-item-detail-slider-image<?= ($key == 0 ? ' active' : '') ?>"
							     data-entity="image" data-id="<?= $photo['ID'] ?>">
								<img src="<?= $photo['SRC'] ?>" alt="<?= $alt ?>"
								     title="<?= $title ?>"<?= ($key == 0 ? ' itemprop="image"' : '') ?>>
							</div>
							<?php
						}
					} else { ?>
						<div class="product-item-detail-slider-image active" data-entity="image"
						     data-id="1">
							<img src="/local/templates/Oshisha/images/no-photo.gif" itemprop="image">
						</div>
					<?php }
					if ($arParams['SLIDER_PROGRESS'] === 'Y') { ?>
						<div class="product-item-detail-slider-progress-bar"
						     data-entity="slider-progress-bar"
						     style="width: 0;"></div>
					<?php } ?>
				</div>
			</div>
			<div class="box_with_net" <?php if (empty($taste['VALUE'])){ ?>style="padding: 20px;"<?php } ?>>
				<?php $APPLICATION->IncludeComponent('bitrix:osh.like_favorites',
					'templates',
					array(
						'ID_PROD' => $arResult['ID'],
						'F_USER_ID' => $FUser_id,
						'LOOK_LIKE' => true,
						'LOOK_FAVORITE' => true,
						'COUNT_LIKE' => $arResult['COUNT_LIKE'],
						'COUNT_FAV' => $arResult['COUNT_FAV'],
						'COUNT_LIKES' => $arResult['COUNT_LIKES'],
					)
					,
					$component,
					array('HIDE_ICONS' => 'Y')
				); ?>
				<a href="#" class="delligate shared mt-3 mb-3" title="Поделиться"
				   data-element-id="<?= $arResult['ID'] ?>">
					<i class="fa fa-paper-plane-o" aria-hidden="true"></i>
					<div class="shared_block">
						<? $APPLICATION->IncludeComponent(
							"arturgolubev:yandex.share",
							"",
							array(
								"DATA_IMAGE" => "",
								"DATA_RESCRIPTION" => "",
								"DATA_TITLE" => $arResult['NAME'],
								"DATA_URL" => 'https://' . SITE_SERVER_NAME . $arResult['DETAIL_PAGE_URL'],
								"OLD_BROWSERS" => "N",
								"SERVISE_LIST" => BXConstants::Shared(),
								"TEXT_ALIGN" => "ar_al_left",
								"TEXT_BEFORE" => "",
								"VISUAL_STYLE" => "icons"
							)
						); ?>
					</div>
				</a>
			</div>
		</div>
		<div class="product-item-detail-slider-controls-block margin_block_element"
		     id="<?= $itemIds['SLIDER_CONT_ID'] ?>">
			<?php if (!empty($actualItem['PICTURE']) && count($actualItem['PICTURE']) > 1) {
				foreach ($actualItem['PICTURE'] as $key => $photo) { ?>
					<div
						class="product-item-detail-slider-controls-image<?= ($key == 0 ? ' active' : '') ?>"
						data-entity="slider-control" data-value="<?= $photo['ID'] ?>">
						<img src="<?= $photo['SRC'] ?>">
					</div>
				<?php }
			} ?>
		</div>
	</div>
</div>