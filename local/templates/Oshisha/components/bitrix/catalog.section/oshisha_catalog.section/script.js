(function () {
    'use strict';

    if (!!window.JCCatalogSectionComponent)
        return;

    window.JCCatalogSectionComponent = function (params) {
        this.formPosting = false;
        this.siteId = params.siteId || '';
        this.ajaxId = params.ajaxId || '';
        this.template = params.template || '';
        this.componentPath = params.componentPath || '';
        this.parameters = params.parameters || {};
        this.sortElem = params.sortCatalogId || '';

        //enterego filter for special group category
        this.staticFilter = params.staticFilter || '';

        if (params.navParams) {
            this.navParams = {
                NavNum: params.navParams.NavNum || 1,
                NavPageNomer: parseInt(params.navParams.NavPageNomer) || 1,
                NavPageCount: parseInt(params.navParams.NavPageCount) || 1
            };
        }

        this.bigData = params.bigData || {enabled: false};
        this.container = document.querySelector('[data-entity="' + params.container + '"]');
        this.showMoreButton = null;
        this.showMoreButtonMessage = null;

        if (this.bigData.enabled && BX.util.object_keys(this.bigData.rows).length > 0) {
            BX.cookie_prefix = this.bigData.js.cookiePrefix || '';
            BX.cookie_domain = this.bigData.js.cookieDomain || '';
            BX.current_server_time = this.bigData.js.serverTime;

            BX.ready(BX.delegate(this.bigDataLoad, this));
        }

        if (params.initiallyShowHeader) {
            BX.ready(BX.delegate(this.showHeader, this));
        }

        if (params.deferredLoad) {
            BX.ready(BX.delegate(this.deferredLoad, this));
        }

        // enterego catalog sort
        let urlParams = (new URL(document.location)).searchParams;

        document.querySelectorAll(this.sortElem).forEach(
            function (currentValue, currentIndex, listObj) {
                let sortPanel = currentValue.closest('.sort_panel');

                // определение подписи сортировки при перезагрузке с параметрами
                if (currentValue.getAttribute('data-sort') === urlParams.get("sort_by") &&
                    currentValue.getAttribute('data-order') === urlParams.get("sort_order")) {
                    sortPanel.querySelector('.sort_caption').textContent = currentValue.textContent;
                }

                BX.bind(currentValue, 'click', BX.proxy(function () {
                    const data = {
                        'sort_by': currentValue.getAttribute('data-sort'),
                        'sort_order': currentValue.getAttribute('data-order'),
                        'ajax': 'y',
                        'ajax_filter': 'y',
                        action: 'showMore',
                        PAGEN_0: 1
                    };
                    this.sendRequestRefreshCatalog(data);

                    history.pushState(
                        {
                            sort_by: data.sort_by,
                            sort_order: data.sort_order
                        },
                        '',
                        `${window.location.pathname}?sort_by=${data.sort_by}&sort_order=${data.sort_order}`,
                    );

                    sortPanel.querySelector('.sort_orders_element').style.display = 'none';
                    sortPanel.querySelector('.sort_caption').textContent = currentValue.textContent;
                }, this));
            },
            this
        );

        if (params.lazyLoad) {
            this.showMoreButton = document.querySelector('[data-use="show-more-' + this.navParams.NavNum + '"]');
            this.showMoreButtonMessage = this.showMoreButton.innerHTML;
            BX.bind(this.showMoreButton, 'click', BX.proxy(this.showMore, this));
        }

        if (params.loadOnScroll) {
            BX.bind(window, 'scroll', BX.proxy(this.loadOnScroll, this));

    }
        window.JCCatalogSectionComponentThis = this;
    };

    window.JCCatalogSectionComponent.prototype =
        {
            checkButton: function () {
                if (this.showMoreButton) {
                    if (this.navParams.NavPageNomer == this.navParams.NavPageCount) {
                        BX.remove(this.showMoreButton);
                    } else {
                        this.lazyLoadContainer.appendChild(this.showMoreButton);
                    }
                }
            },

            enableButton: function () {
                if (this.showMoreButton) {
                    BX.removeClass(this.showMoreButton, 'disabled');
                    this.showMoreButton.innerHTML = this.showMoreButtonMessage;
                }
            },

            disableButton: function () {
                if (this.showMoreButton) {
                    BX.addClass(this.showMoreButton, 'disabled');
                    this.showMoreButton.innerHTML = BX.message('BTN_MESSAGE_LAZY_LOAD_WAITER');
                }
            },

            loadOnScroll: function () {
                var scrollTop = BX.GetWindowScrollPos().scrollTop,
                    containerBottom = BX.pos(this.container).bottom;

                if (scrollTop + window.innerHeight > containerBottom) {
                    this.showMore();
                }
            },

            showMore: function () {
                if (this.navParams.NavPageNomer < this.navParams.NavPageCount) {
                    var data = {};
                    data['action'] = 'showMore';
                    data['PAGEN_' + this.navParams.NavNum] = this.navParams.NavPageNomer + 1;

                    if (!this.formPosting) {
                        this.formPosting = true;
                        this.disableButton();
                        this.sendRequest(data);
                    }
                }
            },

            bigDataLoad: function () {
                var url = 'https://analytics.bitrix.info/crecoms/v1_0/recoms.php',
                    data = BX.ajax.prepareData(this.bigData.params);

                if (data) {
                    url += (url.indexOf('?') !== -1 ? '&' : '?') + data;
                }

                var onReady = BX.delegate(function (result) {
                    this.sendRequest({
                        action: 'deferredLoad',
                        bigData: 'Y',
                        items: result && result.items || [],
                        rid: result && result.id,
                        count: this.bigData.count,
                        rowsRange: this.bigData.rowsRange,
                        shownIds: this.bigData.shownIds
                    });
                }, this);

                BX.ajax({
                    method: 'GET',
                    dataType: 'json',
                    url: url,
                    timeout: 3,
                    onsuccess: onReady,
                    onfailure: onReady
                });
            },

            deferredLoad: function () {
                this.sendRequest({action: 'deferredLoad'});
            },

            sendRequest: function (data) {
                var defaultData = {
                    siteId: this.siteId,
                    template: this.template,
                    parameters: this.parameters
                };

                if (this.ajaxId) {
                    defaultData.AJAX_ID = this.ajaxId;
                }

                BX.ajax({
                    url: this.componentPath + '/ajax.php' + (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
                    method: 'POST',
                    dataType: 'json',
                    timeout: 60,
                    data: BX.merge(defaultData, data),
                    onsuccess: BX.delegate(function (result) {
                        if (!result || !result.JS)
                            return;

                        BX.ajax.processScripts(
                            BX.processHTML(result.JS).SCRIPT,
                            false,
                            BX.delegate(function () {
                                this.showAction(result, data);
                            }, this)
                        );
                    }, this)
                });
            },
            sendRequestRefreshCatalog: function (data, url) {
                var defaultData = {
                    siteId: this.siteId,
                    template: this.template,
                    parameters: this.parameters,
                    //enterego filter for special group category
                    staticFilter: this.staticFilter
                };

                if (this.ajaxId) {
                    defaultData.AJAX_ID = this.ajaxId;
                }

                BX.ajax({
                    url: '/local/templates/Oshisha/components/bitrix/catalog.section/oshisha_catalog.section/ajax.php' +
                        (document.location.href.indexOf('clear_cache=Y') !== -1 ? '?clear_cache=Y' : ''),
                    method: 'POST',
                    dataType: 'json',
                    timeout: 60,
                    data: BX.merge(defaultData, data),
                    onsuccess: BX.delegate(function (result) {
                        if (!result || !result.JS)
                            return;

                        BX.cleanNode(window.JCCatalogSectionComponentThis.container);
                        if (result.parameters) {
                            this.parameters = result.parameters;
                        }

                        if (result.navParams) {
                            let navParams = JSON.parse(result.navParams);
                            this.navParams = {
                                NavNum: 1,
                                NavPageNomer: 0,
                                NavPageCount: parseInt(navParams.NavPageCount) || 1
                            };
                        }HeightTaste

                        BX.ajax.processScripts(
                            BX.processHTML(result.JS).SCRIPT,
                            false,
                            BX.delegate(function () {
                                this.showAction(result, data);
                            }, this)
                        );
                    }, this)
                });
            },

            showAction: function (result, data) {
                if (!data)
                    return;

                switch (data.action) {
                    case 'showMore':
                        this.processShowMoreAction(result);
                        break;
                    case 'deferredLoad':
                        this.processDeferredLoadAction(result, data.bigData === 'Y');
                        break;
                }
            },

            processShowMoreAction: function (result) {
                this.formPosting = false;
                this.enableButton();

                if (result) {
                    this.navParams.NavPageNomer++;
                    this.processItems(result.items);
                    this.processPagination(result.pagination);
                    this.processEpilogue(result.epilogue);
                    this.checkButton();
                    this.tasteInit();
                }
            },

            tasteInit: function () {
                let box = $(document).find('.variation_taste');

                $(box).find('span').each(
                    function () {
                        let classes = $(this).attr('class');
                        if (classes === 'taste') {
                            let color = $(this).data('background');
                            $(this).css('background-color', color);
                            let str = '#';
                            if (parseInt(color.replace(str, ''), 16) > 0xffffff / 1.1
                                && color !== '#FF0E15' && color !== '#FF0F17' || color === '#9FFEB0' || color === '#CBF4FF') {
                                $(this).css('color', 'black');
                            } else {
                                $(this).css('color', 'white');
                            }
                        }
                    }
                );
                ё
                $('body').find('.variation_taste').each(
                    function (index, item) {
                        if ($(item).find('.taste').length > 2) {
                            $(item).closest('.toggle_taste ').css('overflow', 'hidden');
                            $(item).closest('.toggle_taste ').addClass('many_tastes_toggle');
                            $(item).attr('visible', '0');
                        }
                    }
                );
            },

            processDeferredLoadAction: function (result, bigData) {
                if (!result)
                    return;

                var position = bigData ? this.bigData.rows : {};

                this.processItems(result.items, BX.util.array_keys(position));
            },

            processItems: function (itemsHtml, position) {
                if (!itemsHtml)
                    return;

                var processed = BX.processHTML(itemsHtml, false),
                    temporaryNode = BX.create('DIV');

                var items, k, origRows;

                temporaryNode.innerHTML = processed.HTML;
                items = temporaryNode.querySelectorAll('[data-entity="items-row"]');

                if (items.length) {
                    this.showHeader(true);

                    for (k in items) {
                        if (items.hasOwnProperty(k)) {
                            origRows = position ? this.container.querySelectorAll('[data-entity="items-row"]') : false;
                            items[k].style.opacity = 0;

                            if (origRows && BX.type.isDomNode(origRows[position[k]])) {
                                origRows[position[k]].parentNode.insertBefore(items[k], origRows[position[k]]);
                            } else {
                                this.container.appendChild(items[k]);
                            }
                        }
                    }

                    new BX.easing({
                        duration: 2000,
                        start: {opacity: 0},
                        finish: {opacity: 100},
                        transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
                        step: function (state) {
                            for (var k in items) {
                                if (items.hasOwnProperty(k)) {
                                    items[k].style.opacity = state.opacity / 100;
                                }
                            }
                        },
                        complete: function () {
                            for (var k in items) {
                                if (items.hasOwnProperty(k)) {
                                    items[k].removeAttribute('style');
                                }
                            }
                        }
                    }).animate();
                }

                BX.ajax.processScripts(processed.SCRIPT);
            },

            processPagination: function (paginationHtml) {
                if (!paginationHtml) {
                    $('.bx-pagination').remove();
                    return;
                }

                var pagination = document.querySelectorAll('[data-pagination-num="' + this.navParams.NavNum + '"]');
                for (var k in pagination) {
                    if (pagination.hasOwnProperty(k)) {
                        pagination[k].innerHTML = paginationHtml;
                    }
                }
            },

            processEpilogue: function (epilogueHtml) {
                if (!epilogueHtml)
                    return;

                var processed = BX.processHTML(epilogueHtml, false);
                BX.ajax.processScripts(processed.SCRIPT);
            },

            showHeader: function (animate) {
                var parentNode = BX.findParent(this.container, {attr: {'data-entity': 'parent-container'}}),
                    header;

                if (parentNode && BX.type.isDomNode(parentNode)) {
                    header = parentNode.querySelector('[data-entity="header"]');

                    if (header && header.getAttribute('data-showed') != 'true') {
                        header.style.display = '';

                        if (animate) {
                            new BX.easing({
                                duration: 2000,
                                start: {opacity: 0},
                                finish: {opacity: 100},
                                transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
                                step: function (state) {
                                    header.style.opacity = state.opacity / 100;
                                },
                                complete: function () {
                                    header.removeAttribute('style');
                                    header.setAttribute('data-showed', 'true');
                                }
                            }).animate();
                        } else {
                            header.style.opacity = 100;
                        }
                    }
                }
            }
        };
})();

function HeightTaste() {
    const breakpointCheck = window.matchMedia('(min-width:481px)');
    if (breakpointCheck.matches === true) {
        if ($('.catalog-section').hasClass('by-card')) {
            var max_height = 0;
            $(".toggle_taste").each(function (index, el) {
                //console.log($(this).height());
                if ($(this).height() > max_height)
                    max_height = $(this).height();
            });
            $(".toggle_taste").css({'height': max_height});


        } else {
            $(".toggle_taste").removeAttr('style');
        }
    } else {
        if ($('.catalog-section').hasClass('by-card')) {
            var max_height = 0;
            var k = 0;
            $(".by-card .toggle_taste").each(function (index, el) {
                if (index % 2 === 0) {
                    var left = $(".by-card .toggle_taste").eq(index).height();
                    var l = index + 1;
                    var right = $(".by-card .toggle_taste").eq(l).height();
                    if (right > left)
                        var resHeight = right;
                    else
                        var resHeight = left;
                    if (resHeight > 0) {
                        var left = $(".by-card .toggle_taste").eq(index).css({'height': resHeight});
                        var right = $(".by-card .toggle_taste").eq(l).css({'height': resHeight});
                    }

                }
            });


        }
    }


}

$(document).ready(function () {
    HeightTaste();

});