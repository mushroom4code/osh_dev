<?php
function createNewItemsChild($arSection, $smartFil, &$arCategory)
{
    if (empty($arCategory[$arSection['ID']])) {
        $child = !empty($arSection['CHILDS']);
        $levelSmall = $arSection['DEPTH_LEVEL'] !== '1';
        if ($child) { ?>
            <div class="catalog-section-list-item-l <?= $levelSmall ? 'd-none mb-1 boxChilds p-child' : 'mb-3' ?>"
                <?php if ($levelSmall) { ?> data-code="<?= $arSection['IBLOCK_SECTION_ID'] ?>" <?php } ?>>
                <div class="catalog-section-list-item-wrap smart-filter-tog"
                     data-role="prop_angle"
                     data-code-vis="<?= $arSection['ID'] ?>">
                    <a href="javascript:void(0)"
                       class="<?= $levelSmall ? 'child hover-red' : 'font-15' ?>"><?= $arSection['NAME'] ?></a>
                    <?php if ($child) { ?>
                        <span data-role="prop_angle" class="smart-filter-tog smart-filter-angle">
                             <i class="fa fa-angle-right smart-filter-angles" aria-hidden="true"></i>
                        </span>
                    <?php } ?>
                </div>
                <div class="catalog-section-list-item-sub <?php if ($smartFil != ''): ?>active<?php endif; ?>"
                     data-code="<?= $arSection['ID'] ?>" <?= $levelSmall ? ' style="margin-left:5px;"' : '' ?>>
                    <a class="mt-2 color-redLight font-13 mb-3 hover-red"
                       href="<?= $arSection['SECTION_PAGE_URL'] ?>">Все</a>
                </div>
                <?php if ($child) {
                    usort($arSection['CHILDS'], 'sort_by_name');
                    foreach ($arSection['CHILDS'] as $arSectionSub) {
                        if (empty($arCategory[$arSectionSub['ID']]) && empty($arSectionSub['CHILDS'])) { ?>
                            <div class="catalog-section-list-item-sub <?php if ($smartFil != ''): ?>active<?php endif; ?>"
                                 data-code="<?= $arSection['ID'] ?>" <?= $levelSmall ? ' style="margin-left:5px;"' : '' ?>>
                                <a href="<?= $arSectionSub['SECTION_PAGE_URL'] ?>"
                                   class="child font-13 hover-red"><?= $arSectionSub['NAME'] ?></a>
                            </div>
                            <?php
                        } else {
                            if (!empty($arSectionSub['CHILDS'])) {
                                $arCategory[$arSection['ID']] = $arSection['ID'];
                                createNewItemsChild($arSectionSub, $smartFil, $arCategory);
                            }
                        }
                    }
                } ?>
            </div>
            <?php
        } else { ?>
            <div class="catalog-section-list-item-sub <?php if ($smartFil != ''): ?>active<?php endif; ?>"
                 data-code="<?= $arSection['ID'] ?>" style="margin-left:5px;">
                <a href="<?= $arSection['SECTION_PAGE_URL'] ?>" class="child font-13 hover-red">
                    <?= $arSection['NAME'] ?></a>
            </div>
        <?php }
    }
}