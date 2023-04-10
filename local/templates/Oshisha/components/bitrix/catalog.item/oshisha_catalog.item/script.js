(function (window) {
    'use strict';

    if (window.JCCatalogItem)
        return;

    var BasketButton = function (params) {
        BasketButton.superclass.constructor.apply(this, arguments);
        this.buttonNode = BX.create('button', {
            props: {className: 'btn btn-primary btn-buy btn-sm', id: this.id},
            style: typeof params.style === 'object' ? params.style : {},
            text: params.text,
            events: this.contextEvents
        });

        if (BX.browser.IsIE()) {
            this.buttonNode.setAttribute("hideFocus", "hidefocus");
        }
    };
    BX.extend(BasketButton, BX.PopupWindowButton);

    window.JCCatalogItem = function (arParams) {
        this.productType = 0;
        this.showQuantity = true;
        this.showAbsent = true;
        this.secondPict = false;
        this.showOldPrice = false;
        this.showMaxQuantity = 'N';
        this.relativeQuantityFactor = 5;
        this.showPercent = false;
        this.showSkuProps = false;
        this.basketAction = 'ADD';
        this.showClosePopup = false;
        this.useCompare = false;
        this.showSubscription = false;
        this.visual = {
            ID: '',
            PICT_ID: '',
            SECOND_PICT_ID: '',
            PICT_SLIDER_ID: '',
            QUANTITY_ID: '',
            QUANTITY_UP_ID: '',
            QUANTITY_DOWN_ID: '',
            PRICE_ID: '',
            PRICE_OLD_ID: '',
            DSC_PERC: '',
            SECOND_DSC_PERC: '',
            DISPLAY_PROP_DIV: '',
            BASKET_PROP_DIV: '',
            SUBSCRIBE_ID: ''
        };
        this.product = {
            checkQuantity: false,
            maxQuantity: 0,
            stepQuantity: 1,
            isDblQuantity: false,
            canBuy: true,
            name: '',
            pict: {},
            id: 0,
            addUrl: '',
            buyUrl: ''
        };

        this.basketMode = '';
        this.basketData = {
            useProps: false,
            emptyProps: false,
            quantity: 'quantity',
            props: 'prop',
            basketUrl: '',
            sku_props: '',
            sku_props_var: 'basket_props',
            add_url: '',
            buy_url: ''
        };

        this.compareData = {
            compareUrl: '',
            compareDeleteUrl: '',
            comparePath: ''
        };

        this.defaultPict = {
            pict: null,
            secondPict: null
        };

        this.defaultSliderOptions = {
            interval: 3000,
            wrap: true
        };
        this.slider = {
            options: {},
            items: [],
            active: null,
            sliding: null,
            paused: null,
            interval: null,
            progress: null
        };
        this.touch = null;

        this.quantityDelay = null;
        this.quantityTimer = null;

        this.checkQuantity = false;
        this.maxQuantity = 0;
        this.minQuantity = 0;
        this.stepQuantity = 1;
        this.isDblQuantity = false;
        this.canBuy = true;
        this.precision = 6;
        this.precisionFactor = Math.pow(10, this.precision);
        this.bigData = false;
        this.fullDisplayMode = false;
        this.viewMode = '';
        this.templateTheme = '';

        this.currentPriceMode = '';
        this.currentPrices = [];
        this.currentPriceSelected = 0;
        this.currentQuantityRanges = [];
        this.currentQuantityRangeSelected = 0;

        this.offers = [];
        this.offerNum = 0;
        this.treeProps = [];
        this.selectedValues = {};

        this.obProduct = null;
        this.blockNodes = {};
        this.obQuantity = null;
        this.obQuantityUp = null;
        this.obQuantityDown = null;
        this.obQuantityLimit = {};
        this.obPict = null;
        this.obSecondPict = null;
        this.obPictSlider = null;
        this.obPictSliderIndicator = null;
        this.obPrice = null;
        this.obTree = null;
        this.obBuyBtn = null;
        this.obBasketActions = null;
        this.obNotAvail = null;
        this.obSubscribe = null;
        this.obDscPerc = null;
        this.obSecondDscPerc = null;
        this.obSkuProps = null;
        this.obMeasure = null;
        this.obCompare = null;

        this.obPopupWin = null;
        this.basketUrl = '';
        this.basketParams = {};
        this.isTouchDevice = BX.hasClass(document.documentElement, 'bx-touch');
        this.hoverTimer = null;
        this.hoverStateChangeForbidden = false;
        this.mouseX = null;
        this.mouseY = null;

        this.useEnhancedEcommerce = false;
        this.dataLayerName = 'dataLayer';
        this.brandProperty = false;

        this.errorCode = 0;

        if (typeof arParams === 'object') {
            if (arParams.PRODUCT_TYPE) {
                this.productType = parseInt(arParams.PRODUCT_TYPE, 10);
            }

            this.showQuantity = arParams.SHOW_QUANTITY;
            this.showAbsent = arParams.SHOW_ABSENT;
            this.secondPict = arParams.SECOND_PICT;
            this.showOldPrice = arParams.SHOW_OLD_PRICE;
            this.showMaxQuantity = arParams.SHOW_MAX_QUANTITY;
            this.relativeQuantityFactor = parseInt(arParams.RELATIVE_QUANTITY_FACTOR);
            this.showPercent = arParams.SHOW_DISCOUNT_PERCENT;
            this.showSkuProps = arParams.SHOW_SKU_PROPS;
            this.showSubscription = arParams.USE_SUBSCRIBE;

            if (arParams.ADD_TO_BASKET_ACTION) {
                this.basketAction = arParams.ADD_TO_BASKET_ACTION;
            }

            this.showClosePopup = arParams.SHOW_CLOSE_POPUP;
            this.useCompare = arParams.DISPLAY_COMPARE;
            this.fullDisplayMode = arParams.PRODUCT_DISPLAY_MODE === 'Y';
            this.bigData = arParams.BIG_DATA;
            this.viewMode = arParams.VIEW_MODE || '';
            this.templateTheme = arParams.TEMPLATE_THEME || '';
            this.useEnhancedEcommerce = arParams.USE_ENHANCED_ECOMMERCE === 'Y';
            this.dataLayerName = arParams.DATA_LAYER_NAME;
            this.brandProperty = arParams.BRAND_PROPERTY;

            this.visual = arParams.VISUAL;

            switch (this.productType) {
                case 0: // no catalog
                case 1: // product
                case 2: // set
                    if (arParams.PRODUCT && typeof arParams.PRODUCT === 'object') {
                        this.currentPriceMode = arParams.PRODUCT.ITEM_PRICE_MODE;
                        this.currentPrices = arParams.PRODUCT.ITEM_PRICES;
                        this.currentPriceSelected = arParams.PRODUCT.ITEM_PRICE_SELECTED;
                        this.currentQuantityRanges = arParams.PRODUCT.ITEM_QUANTITY_RANGES;
                        this.currentQuantityRangeSelected = arParams.PRODUCT.ITEM_QUANTITY_RANGE_SELECTED;

                        if (this.showQuantity) {
                            this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
                            this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;

                            if (this.product.checkQuantity) {
                                this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.MAX_QUANTITY) : parseInt(arParams.PRODUCT.MAX_QUANTITY, 10));
                            }

                            this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.STEP_QUANTITY) : parseInt(arParams.PRODUCT.STEP_QUANTITY, 10));

                            this.checkQuantity = this.product.checkQuantity;
                            this.isDblQuantity = this.product.isDblQuantity;
                            this.stepQuantity = this.product.stepQuantity;
                            this.maxQuantity = this.product.maxQuantity;
                            this.minQuantity = this.currentPriceMode === 'Q'
                                ? parseFloat(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY)
                                : this.stepQuantity;

                            if (this.isDblQuantity) {
                                this.stepQuantity = Math.round(this.stepQuantity * this.precisionFactor) / this.precisionFactor;
                            }
                        }

                        this.product.canBuy = arParams.PRODUCT.CAN_BUY;

                        if (arParams.PRODUCT.MORE_PHOTO_COUNT) {
                            this.product.morePhotoCount = arParams.PRODUCT.MORE_PHOTO_COUNT;
                            this.product.morePhoto = arParams.PRODUCT.MORE_PHOTO;
                        }

                        if (arParams.PRODUCT.RCM_ID) {
                            this.product.rcmId = arParams.PRODUCT.RCM_ID;
                        }

                        this.canBuy = this.product.canBuy;
                        this.product.name = arParams.PRODUCT.NAME;
                        this.product.pict = arParams.PRODUCT.PICT;
                        this.product.id = arParams.PRODUCT.ID;
                        this.product.DETAIL_PAGE_URL = arParams.PRODUCT.DETAIL_PAGE_URL;

                        if (arParams.PRODUCT.ADD_URL) {
                            this.product.addUrl = arParams.PRODUCT.ADD_URL;
                        }

                        if (arParams.PRODUCT.BUY_URL) {
                            this.product.buyUrl = arParams.PRODUCT.BUY_URL;
                        }

                        if (arParams.BASKET && typeof arParams.BASKET === 'object') {
                            this.basketData.useProps = arParams.BASKET.ADD_PROPS;
                            this.basketData.emptyProps = arParams.BASKET.EMPTY_PROPS;
                        }
                    } else {
                        this.errorCode = -1;
                    }

                    break;
                case 3: // sku
                    if (arParams.PRODUCT && typeof arParams.PRODUCT === 'object') {
                        this.product.name = arParams.PRODUCT.NAME;
                        this.product.id = arParams.PRODUCT.ID;
                        this.product.DETAIL_PAGE_URL = arParams.PRODUCT.DETAIL_PAGE_URL;
                        this.product.morePhotoCount = arParams.PRODUCT.MORE_PHOTO_COUNT;
                        this.product.morePhoto = arParams.PRODUCT.MORE_PHOTO;

                        if (arParams.PRODUCT.RCM_ID) {
                            this.product.rcmId = arParams.PRODUCT.RCM_ID;
                        }
                    }

                    if (arParams.OFFERS && BX.type.isArray(arParams.OFFERS)) {
                        this.offers = arParams.OFFERS;
                        this.offerNum = 0;

                        if (arParams.OFFER_SELECTED) {
                            this.offerNum = parseInt(arParams.OFFER_SELECTED, 10);
                        }

                        if (isNaN(this.offerNum)) {
                            this.offerNum = 0;
                        }

                        if (arParams.TREE_PROPS) {
                            this.treeProps = arParams.TREE_PROPS;
                        }

                        if (arParams.DEFAULT_PICTURE) {
                            this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
                            this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
                        }
                    }

                    break;
                default:
                    this.errorCode = -1;
            }
            if (arParams.BASKET && typeof arParams.BASKET === 'object') {
                if (arParams.BASKET.QUANTITY) {
                    this.basketData.quantity = arParams.BASKET.QUANTITY;
                }

                if (arParams.BASKET.PROPS) {
                    this.basketData.props = arParams.BASKET.PROPS;
                }

                if (arParams.BASKET.BASKET_URL) {
                    this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
                }

                if (3 === this.productType) {
                    if (arParams.BASKET.SKU_PROPS) {
                        this.basketData.sku_props = arParams.BASKET.SKU_PROPS;
                    }
                }

                if (arParams.BASKET.ADD_URL_TEMPLATE) {
                    this.basketData.add_url = arParams.BASKET.ADD_URL_TEMPLATE;
                }

                if (arParams.BASKET.BUY_URL_TEMPLATE) {
                    this.basketData.buy_url = arParams.BASKET.BUY_URL_TEMPLATE;
                }

                if (this.basketData.add_url === '' && this.basketData.buy_url === '') {
                    this.errorCode = -1024;
                }
            }

            if (this.useCompare) {
                if (arParams.COMPARE && typeof arParams.COMPARE === 'object') {
                    if (arParams.COMPARE.COMPARE_PATH) {
                        this.compareData.comparePath = arParams.COMPARE.COMPARE_PATH;
                    }

                    if (arParams.COMPARE.COMPARE_URL_TEMPLATE) {
                        this.compareData.compareUrl = arParams.COMPARE.COMPARE_URL_TEMPLATE;
                    } else {
                        this.useCompare = false;
                    }

                    if (arParams.COMPARE.COMPARE_DELETE_URL_TEMPLATE) {
                        this.compareData.compareDeleteUrl = arParams.COMPARE.COMPARE_DELETE_URL_TEMPLATE;
                    } else {
                        this.useCompare = false;
                    }
                } else {
                    this.useCompare = false;
                }
            }
        }

        if (this.errorCode === 0) {
            BX.ready(BX.delegate(this.init, this));
        }
    };

    window.JCCatalogItem.prototype = {
        init: function () {
            var i = 0,
                treeItems = null;

            this.obProduct = BX(this.visual.ID);
            if (!this.obProduct) {
                this.errorCode = -1;
            }

            this.obPict = BX(this.visual.PICT_ID);
            if (!this.obPict) {
                this.errorCode = -2;
            }

            if (this.secondPict && this.visual.SECOND_PICT_ID) {
                this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
            }

            this.obPictSlider = BX(this.visual.PICT_SLIDER_ID);
            this.obPictSliderIndicator = BX(this.visual.PICT_SLIDER_ID + '_indicator');
            this.obPictSliderProgressBar = BX(this.visual.PICT_SLIDER_ID + '_progress_bar');
            if (!this.obPictSlider) {
                this.errorCode = -4;
            }

            this.obPrice = BX(this.visual.PRICE_ID);
            this.obPriceOld = BX(this.visual.PRICE_OLD_ID);
            this.obPriceTotal = BX(this.visual.PRICE_TOTAL_ID);
            if (!this.obPrice) {
                this.errorCode = -16;
            }

            if (this.showQuantity && this.visual.QUANTITY_ID) {
                this.obQuantity = BX(this.visual.QUANTITY_ID);
                this.blockNodes.quantity = this.obProduct.querySelector('[data-entity="quantity-block"]');

                if (!this.isTouchDevice) {
                    BX.bind(this.obQuantity, 'focus', BX.proxy(this.onFocus, this));
                    BX.bind(this.obQuantity, 'blur', BX.proxy(this.onBlur, this));
                }

                if (this.visual.QUANTITY_UP_ID) {
                    this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
                }

                if (this.visual.QUANTITY_DOWN_ID) {
                    this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
                }
            }

            if (this.visual.QUANTITY_LIMIT && this.showMaxQuantity !== 'N') {
                this.obQuantityLimit.all = BX(this.visual.QUANTITY_LIMIT);
                if (this.obQuantityLimit.all) {
                    this.obQuantityLimit.value = this.obQuantityLimit.all.querySelector('[data-entity="quantity-limit-value"]');
                    if (!this.obQuantityLimit.value) {
                        this.obQuantityLimit.all = null;
                    }
                }
            }

            if (this.productType === 3 && this.fullDisplayMode) {
                if (this.visual.TREE_ID) {
                    this.obTree = BX(this.visual.TREE_ID);
                    if (!this.obTree) {
                        this.errorCode = -256;
                    }
                }

                if (this.visual.QUANTITY_MEASURE) {
                    this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
                }
            }

            this.obBasketActions = BX(this.visual.BASKET_ACTIONS_ID);
            if (this.obBasketActions) {
                if (this.visual.BUY_ID) {
                    this.obBuyBtn = BX(this.visual.BUY_ID);
                }
            }

            this.obNotAvail = BX(this.visual.NOT_AVAILABLE_MESS);

            if (this.showSubscription) {
                this.obSubscribe = BX(this.visual.SUBSCRIBE_ID);
            }

            if (this.showPercent) {
                if (this.visual.DSC_PERC) {
                    this.obDscPerc = BX(this.visual.DSC_PERC);
                }
                if (this.secondPict && this.visual.SECOND_DSC_PERC) {
                    this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
                }
            }

            if (this.showSkuProps) {
                if (this.visual.DISPLAY_PROP_DIV) {
                    this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
                }
            }

            if (this.errorCode === 0) {
                // product slider events
                if (this.isTouchDevice) {
                    BX.bind(this.obPictSlider, 'touchstart', BX.proxy(this.touchStartEvent, this));
                    BX.bind(this.obPictSlider, 'touchend', BX.proxy(this.touchEndEvent, this));
                    BX.bind(this.obPictSlider, 'touchcancel', BX.proxy(this.touchEndEvent, this));
                } else {
                    if (this.viewMode === 'CARD') {
                        // product hover events
                        BX.bind(this.obProduct, 'mouseenter', BX.proxy(this.hoverOn, this));
                        BX.bind(this.obProduct, 'mouseleave', BX.proxy(this.hoverOff, this));
                    }

                    // product slider events
                    BX.bind(this.obProduct, 'mouseenter', BX.proxy(this.cycleSlider, this));
                    BX.bind(this.obProduct, 'mouseleave', BX.proxy(this.stopSlider, this));
                }

                if (this.bigData) {
                    var links = BX.findChildren(this.obProduct, {tag: 'a'}, true);
                    if (links) {
                        for (i in links) {
                            if (links.hasOwnProperty(i)) {
                                if (links[i].getAttribute('href') == this.product.DETAIL_PAGE_URL) {
                                    BX.bind(links[i], 'click', BX.proxy(this.rememberProductRecommendation, this));
                                }
                            }
                        }
                    }
                }

                if (this.showQuantity) {
                    var startEventName = this.isTouchDevice ? 'touchstart' : 'mousedown';
                    var endEventName = this.isTouchDevice ? 'touchend' : 'mouseup';

                    if (this.obQuantityUp) {
                        BX.bind(this.obQuantityUp, startEventName, BX.proxy(this.startQuantityInterval, this));
                        BX.bind(this.obQuantityUp, endEventName, BX.proxy(this.clearQuantityInterval, this));
                        BX.bind(this.obQuantityUp, 'mouseout', BX.proxy(this.clearQuantityInterval, this));
                        BX.bind(this.obQuantityUp, 'click', BX.delegate(this.quantityUp, this));
                    }

                    if (this.obQuantityDown) {
                        BX.bind(this.obQuantityDown, startEventName, BX.proxy(this.startQuantityInterval, this));
                        BX.bind(this.obQuantityDown, endEventName, BX.proxy(this.clearQuantityInterval, this));
                        BX.bind(this.obQuantityDown, 'mouseout', BX.proxy(this.clearQuantityInterval, this));
                        BX.bind(this.obQuantityDown, 'click', BX.delegate(this.quantityDown, this));
                    }

                    if (this.obQuantity) {
                        BX.bind(this.obQuantity, 'change', BX.delegate(this.quantityChange, this));
                    }
                }

                switch (this.productType) {
                    case 0: // no catalog
                    case 1: // product
                    case 2: // set
                        if (parseInt(this.product.morePhotoCount) > 1 && this.obPictSlider) {
                            this.initializeSlider();
                        }

                        this.checkQuantityControls();

                        break;
                    case 3: // sku
                        if (this.offers.length > 0) {
                            treeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);

                            if (treeItems && treeItems.length) {
                                for (i = 0; i < treeItems.length; i++) {
                                    BX.bind(treeItems[i], 'click', BX.delegate(this.selectOfferProp, this));
                                }
                            }

                            this.setCurrent();
                        } else if (parseInt(this.product.morePhotoCount) > 1 && this.obPictSlider) {
                            this.initializeSlider();
                        }

                        break;
                }

                if (this.obBuyBtn) {
                    if (this.basketAction === 'ADD') {
                        BX.bind(this.obBuyBtn, 'click', BX.proxy(this.add2Basket, this));
                    } else {
                        BX.bind(this.obBuyBtn, 'click', BX.proxy(this.buyBasket, this));
                    }
                }

                if (this.useCompare) {
                    this.obCompare = BX(this.visual.COMPARE_LINK_ID);
                    if (this.obCompare) {
                        BX.bind(this.obCompare, 'click', BX.proxy(this.compare, this));
                    }

                    BX.addCustomEvent('onCatalogDeleteCompare', BX.proxy(this.checkDeletedCompare, this));
                }
            }
        },

        setAnalyticsDataLayer: function (action) {
            if (!this.useEnhancedEcommerce || !this.dataLayerName)
                return;

            var item = {},
                info = {},
                variants = [],
                i, k, j, propId, skuId, propValues;

            switch (this.productType) {
                case 0: //no catalog
                case 1: //product
                case 2: //set
                    item = {
                        'id': this.product.id,
                        'name': this.product.name,
                        'price': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].PRICE,
                        'brand': BX.type.isArray(this.brandProperty) ? this.brandProperty.join('/') : this.brandProperty
                    };
                    break;
                case 3: //sku
                    for (i in this.offers[this.offerNum].TREE) {
                        if (this.offers[this.offerNum].TREE.hasOwnProperty(i)) {
                            propId = i.substring(5);
                            skuId = this.offers[this.offerNum].TREE[i];

                            for (k in this.treeProps) {
                                if (this.treeProps.hasOwnProperty(k) && this.treeProps[k].ID == propId) {
                                    for (j in this.treeProps[k].VALUES) {
                                        propValues = this.treeProps[k].VALUES[j];
                                        if (propValues.ID == skuId) {
                                            variants.push(propValues.NAME);
                                            break;
                                        }
                                    }

                                }
                            }
                        }
                    }

                    item = {
                        'id': this.offers[this.offerNum].ID,
                        'name': this.offers[this.offerNum].NAME,
                        'price': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].PRICE,
                        'brand': BX.type.isArray(this.brandProperty) ? this.brandProperty.join('/') : this.brandProperty,
                        'variant': variants.join('/')
                    };
                    break;
            }

            switch (action) {
                case 'addToCart':
                    info = {
                        'event': 'addToCart',
                        'ecommerce': {
                            'currencyCode': this.currentPrices[this.currentPriceSelected] && this.currentPrices[this.currentPriceSelected].CURRENCY || '',
                            'add': {
                                'products': [{
                                    'name': item.name || '',
                                    'id': item.id || '',
                                    'price': item.price || 0,
                                    'brand': item.brand || '',
                                    'category': item.category || '',
                                    'variant': item.variant || '',
                                    'quantity': this.showQuantity && this.obQuantity ? this.obQuantity.value : 1
                                }]
                            }
                        }
                    };
                    break;
            }

            window[this.dataLayerName] = window[this.dataLayerName] || [];
            window[this.dataLayerName].push(info);
        },

        hoverOn: function (event) {
            clearTimeout(this.hoverTimer);
            this.obProduct.style.height = getComputedStyle(this.obProduct).height;
            BX.addClass(this.obProduct, 'hover');

            BX.PreventDefault(event);
        },

        hoverOff: function (event) {
            if (this.hoverStateChangeForbidden)
                return;

            BX.removeClass(this.obProduct, 'hover');
            this.hoverTimer = setTimeout(
                BX.delegate(function () {
                    this.obProduct.style.height = 'auto';
                }, this),
                300
            );

            BX.PreventDefault(event);
        },

        onFocus: function () {
            this.hoverStateChangeForbidden = true;
            BX.bind(document, 'mousemove', BX.proxy(this.captureMousePosition, this));
        },

        onBlur: function () {
            this.hoverStateChangeForbidden = false;
            BX.unbind(document, 'mousemove', BX.proxy(this.captureMousePosition, this));

            var cursorElement = document.elementFromPoint(this.mouseX, this.mouseY);
            if (!cursorElement || !this.obProduct.contains(cursorElement)) {
                this.hoverOff();
            }
        },

        captureMousePosition: function (event) {
            this.mouseX = event.clientX;
            this.mouseY = event.clientY;
        },

        getCookie: function (name) {
            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));

            return matches ? decodeURIComponent(matches[1]) : null;
        },

        rememberProductRecommendation: function () {
            // save to RCM_PRODUCT_LOG
            var cookieName = BX.cookie_prefix + '_RCM_PRODUCT_LOG',
                cookie = this.getCookie(cookieName),
                itemFound = false;

            var cItems = [],
                cItem;

            if (cookie) {
                cItems = cookie.split('.');
            }

            var i = cItems.length;

            while (i--) {
                cItem = cItems[i].split('-');

                if (cItem[0] == this.product.id) {
                    // it's already in recommendations, update the date
                    cItem = cItems[i].split('-');

                    // update rcmId and date
                    cItem[1] = this.product.rcmId;
                    cItem[2] = BX.current_server_time;

                    cItems[i] = cItem.join('-');
                    itemFound = true;
                } else {
                    if ((BX.current_server_time - cItem[2]) > 3600 * 24 * 30) {
                        cItems.splice(i, 1);
                    }
                }
            }

            if (!itemFound) {
                // add recommendation
                cItems.push([this.product.id, this.product.rcmId, BX.current_server_time].join('-'));
            }

            // serialize
            var plNewCookie = cItems.join('.'),
                cookieDate = new Date(new Date().getTime() + 1000 * 3600 * 24 * 365 * 10).toUTCString();

            document.cookie = cookieName + "=" + plNewCookie + "; path=/; expires=" + cookieDate + "; domain=" + BX.cookie_domain;
        },

        startQuantityInterval: function () {
            var target = BX.proxy_context;
            var func = target.id === this.visual.QUANTITY_DOWN_ID
                ? BX.proxy(this.quantityDown, this)
                : BX.proxy(this.quantityUp, this);

            this.quantityDelay = setTimeout(
                BX.delegate(function () {
                    this.quantityTimer = setInterval(func, 150);
                }, this),
                300
            );
        },

        clearQuantityInterval: function () {
            clearTimeout(this.quantityDelay);
            clearInterval(this.quantityTimer);
        },

        quantityUp: function () {
            var curValue = 0,
                boolSet = true;

            if (this.errorCode === 0 && this.showQuantity && this.canBuy) {
                curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
                if (!isNaN(curValue)) {
                    curValue += this.stepQuantity;
                    if (this.checkQuantity) {
                        if (curValue > this.maxQuantity) {
                            boolSet = false;
                        }
                    }

                    if (boolSet) {
                        if (this.isDblQuantity) {
                            curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
                        }

                        this.obQuantity.value = curValue;

                        this.setPrice();
                    }
                }
            }
        },

        quantityDown: function () {
            var curValue = 0,
                boolSet = true;

            if (this.errorCode === 0 && this.showQuantity && this.canBuy) {
                curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
                if (!isNaN(curValue)) {
                    curValue -= this.stepQuantity;

                    this.checkPriceRange(curValue);

                    if (curValue < this.minQuantity) {
                        boolSet = false;
                    }

                    if (boolSet) {
                        if (this.isDblQuantity) {
                            curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;
                        }

                        this.obQuantity.value = curValue;

                        this.setPrice();
                    }
                }
            }
        },

        quantityChange: function () {
            var curValue = 0,
                intCount;

            if (this.errorCode === 0 && this.showQuantity) {
                if (this.canBuy) {
                    curValue = this.isDblQuantity ? parseFloat(this.obQuantity.value) : Math.round(this.obQuantity.value);
                    if (!isNaN(curValue)) {
                        if (this.checkQuantity) {
                            if (curValue > this.maxQuantity) {
                                curValue = this.maxQuantity;
                            }
                        }

                        this.checkPriceRange(curValue);

                        intCount = Math.floor(
                            Math.round(curValue * this.precisionFactor / this.stepQuantity) / this.precisionFactor
                        ) || 1;
                        curValue = (intCount <= 1 ? this.stepQuantity : intCount * this.stepQuantity);
                        curValue = Math.round(curValue * this.precisionFactor) / this.precisionFactor;

                        if (curValue < this.minQuantity) {
                            curValue = this.minQuantity;
                        }

                        this.obQuantity.value = curValue;
                    } else {
                        this.obQuantity.value = this.minQuantity;
                    }
                } else {
                    this.obQuantity.value = this.minQuantity;
                }

                this.setPrice();
            }
        },

        quantitySet: function (index) {
            var resetQuantity, strLimit;

            var newOffer = this.offers[index],
                oldOffer = this.offers[this.offerNum];

            if (this.errorCode === 0) {
                this.canBuy = newOffer.CAN_BUY;

                this.currentPriceMode = newOffer.ITEM_PRICE_MODE;
                this.currentPrices = newOffer.ITEM_PRICES;
                this.currentPriceSelected = newOffer.ITEM_PRICE_SELECTED;
                this.currentQuantityRanges = newOffer.ITEM_QUANTITY_RANGES;
                this.currentQuantityRangeSelected = newOffer.ITEM_QUANTITY_RANGE_SELECTED;

                if (this.canBuy) {
                    if (this.blockNodes.quantity) {
                        BX.style(this.blockNodes.quantity, 'display', '');
                    }

                    if (this.obBasketActions) {
                        BX.style(this.obBasketActions, 'display', '');
                    }

                    if (this.obNotAvail) {
                        BX.style(this.obNotAvail, 'display', 'none');
                    }

                    if (this.obSubscribe) {
                        BX.style(this.obSubscribe, 'display', 'none');
                    }
                } else {
                    if (this.blockNodes.quantity) {
                        BX.style(this.blockNodes.quantity, 'display', 'none');
                    }

                    if (this.obBasketActions) {
                        BX.style(this.obBasketActions, 'display', 'none');
                    }

                    if (this.obNotAvail) {
                        BX.style(this.obNotAvail, 'display', '');
                    }

                    if (this.obSubscribe) {
                        if (newOffer.CATALOG_SUBSCRIBE === 'Y') {
                            BX.style(this.obSubscribe, 'display', '');
                            this.obSubscribe.setAttribute('data-item', newOffer.ID);
                            BX(this.visual.SUBSCRIBE_ID + '_hidden').click();
                        } else {
                            BX.style(this.obSubscribe, 'display', 'none');
                        }
                    }
                }

                this.isDblQuantity = newOffer.QUANTITY_FLOAT;
                this.checkQuantity = newOffer.CHECK_QUANTITY;

                if (this.isDblQuantity) {
                    this.stepQuantity = Math.round(parseFloat(newOffer.STEP_QUANTITY) * this.precisionFactor) / this.precisionFactor;
                    this.maxQuantity = parseFloat(newOffer.MAX_QUANTITY);
                    this.minQuantity = this.currentPriceMode === 'Q' ? parseFloat(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY) : this.stepQuantity;
                } else {
                    this.stepQuantity = parseInt(newOffer.STEP_QUANTITY, 10);
                    this.maxQuantity = parseInt(newOffer.MAX_QUANTITY, 10);
                    this.minQuantity = this.currentPriceMode === 'Q' ? parseInt(this.currentPrices[this.currentPriceSelected].MIN_QUANTITY) : this.stepQuantity;
                }

                if (this.showQuantity) {
                    var isDifferentMinQuantity = oldOffer.ITEM_PRICES.length
                        && oldOffer.ITEM_PRICES[oldOffer.ITEM_PRICE_SELECTED]
                        && oldOffer.ITEM_PRICES[oldOffer.ITEM_PRICE_SELECTED].MIN_QUANTITY != this.minQuantity;

                    if (this.isDblQuantity) {
                        resetQuantity = Math.round(parseFloat(oldOffer.STEP_QUANTITY) * this.precisionFactor) / this.precisionFactor !== this.stepQuantity
                            || isDifferentMinQuantity
                            || oldOffer.MEASURE !== newOffer.MEASURE
                            || (
                                this.checkQuantity
                                && parseFloat(oldOffer.MAX_QUANTITY) > this.maxQuantity
                                && parseFloat(this.obQuantity.value) > this.maxQuantity
                            );
                    } else {
                        resetQuantity = parseInt(oldOffer.STEP_QUANTITY, 10) !== this.stepQuantity
                            || isDifferentMinQuantity
                            || oldOffer.MEASURE !== newOffer.MEASURE
                            || (
                                this.checkQuantity
                                && parseInt(oldOffer.MAX_QUANTITY, 10) > this.maxQuantity
                                && parseInt(this.obQuantity.value, 10) > this.maxQuantity
                            );
                    }

                    this.obQuantity.disabled = !this.canBuy;

                    if (resetQuantity) {
                        this.obQuantity.value = this.minQuantity;
                    }

                    if (this.obMeasure) {
                        if (newOffer.MEASURE) {
                            BX.adjust(this.obMeasure, {html: newOffer.MEASURE});
                        } else {
                            BX.adjust(this.obMeasure, {html: ''});
                        }
                    }
                }

                if (this.obQuantityLimit.all) {
                    if (!this.checkQuantity || this.maxQuantity == 0) {
                        BX.adjust(this.obQuantityLimit.value, {html: ''});
                        BX.adjust(this.obQuantityLimit.all, {style: {display: 'none'}});
                    } else {
                        if (this.showMaxQuantity === 'M') {
                            strLimit = (this.maxQuantity / this.stepQuantity >= this.relativeQuantityFactor)
                                ? BX.message('RELATIVE_QUANTITY_MANY')
                                : BX.message('RELATIVE_QUANTITY_FEW');
                        } else {
                            strLimit = this.maxQuantity;

                            if (newOffer.MEASURE) {
                                strLimit += (' ' + newOffer.MEASURE);
                            }
                        }

                        BX.adjust(this.obQuantityLimit.value, {html: strLimit});
                        BX.adjust(this.obQuantityLimit.all, {style: {display: ''}});
                    }
                }
            }
        },

        initializeSlider: function () {
            var wrap = this.obPictSlider.getAttribute('data-slider-wrap');
            if (wrap) {
                this.slider.options.wrap = wrap === 'true';
            } else {
                this.slider.options.wrap = this.defaultSliderOptions.wrap;
            }

            if (this.isTouchDevice) {
                this.slider.options.interval = false;
            } else {
                this.slider.options.interval = parseInt(this.obPictSlider.getAttribute('data-slider-interval')) || this.defaultSliderOptions.interval;
                // slider interval must be more than 700ms because of css transitions
                if (this.slider.options.interval < 700) {
                    this.slider.options.interval = 700;
                }

                if (this.obPictSliderIndicator) {
                    var controls = this.obPictSliderIndicator.querySelectorAll('[data-go-to]');
                    for (var i in controls) {
                        if (controls.hasOwnProperty(i)) {
                            BX.bind(controls[i], 'click', BX.proxy(this.sliderClickHandler, this));
                        }
                    }
                }

                if (this.obPictSliderProgressBar) {
                    if (this.slider.progress) {
                        this.resetProgress();
                        this.cycleSlider();
                    } else {
                        this.slider.progress = new BX.easing({
                            transition: BX.easing.transitions.linear,
                            step: BX.delegate(function (state) {
                                this.obPictSliderProgressBar.style.width = state.width / 10 + '%';
                            }, this)
                        });
                    }
                }
            }
        },

        checkTouch: function (event) {
            if (!event || !event.changedTouches)
                return false;

            return event.changedTouches[0].identifier === this.touch.identifier;
        },

        touchStartEvent: function (event) {
            if (event.touches.length != 1)
                return;

            this.touch = event.changedTouches[0];
        },

        touchEndEvent: function (event) {
            if (!this.checkTouch(event))
                return;

            var deltaX = this.touch.pageX - event.changedTouches[0].pageX,
                deltaY = this.touch.pageY - event.changedTouches[0].pageY;

            if (Math.abs(deltaX) >= Math.abs(deltaY) + 10) {
                if (deltaX > 0) {
                    this.slideNext();
                }

                if (deltaX < 0) {
                    this.slidePrev();
                }
            }
        },

        sliderClickHandler: function (event) {
            var target = BX.getEventTarget(event),
                slideIndex = target.getAttribute('data-go-to');

            if (slideIndex) {
                this.slideTo(slideIndex)
            }

            BX.PreventDefault(event);
        },

        slideNext: function () {
            if (this.slider.sliding)
                return;

            return this.slide('next');
        },

        slidePrev: function () {
            if (this.slider.sliding)
                return;

            return this.slide('prev');
        },

        slideTo: function (pos) {
            this.slider.active = BX.findChild(this.obPictSlider, {className: 'item active'}, true, false);
            this.slider.progress && (this.slider.interval = true);

            var activeIndex = this.getItemIndex(this.slider.active);

            if (pos > (this.slider.items.length - 1) || pos < 0)
                return;

            if (this.slider.sliding)
                return false;

            if (activeIndex == pos) {
                this.stopSlider();
                this.cycleSlider();
                return;
            }

            return this.slide(pos > activeIndex ? 'next' : 'prev', this.eq(this.slider.items, pos));
        },

        slide: function (type, next) {
            var active = BX.findChild(this.obPictSlider, {className: 'item active'}, true, false),
                isCycling = this.slider.interval,
                direction = type === 'next' ? 'left' : 'right';

            next = next || this.getItemForDirection(type, active);

            if (BX.hasClass(next, 'active')) {
                return (this.slider.sliding = false);
            }

            this.slider.sliding = true;

            isCycling && this.stopSlider();

            if (this.obPictSliderIndicator) {
                BX.removeClass(this.obPictSliderIndicator.querySelector('.active'), 'active');
                var nextIndicator = this.obPictSliderIndicator.querySelectorAll('[data-go-to]')[this.getItemIndex(next)];
                nextIndicator && BX.addClass(nextIndicator, 'active');
            }

            if (BX.hasClass(this.obPictSlider, 'slide') && !BX.browser.IsIE()) {
                var self = this;
                BX.addClass(next, type);
                next.offsetWidth; // force reflow
                BX.addClass(active, direction);
                BX.addClass(next, direction);
                setTimeout(function () {
                    BX.addClass(next, 'active');
                    BX.removeClass(active, 'active');
                    BX.removeClass(active, direction);
                    BX.removeClass(next, type);
                    BX.removeClass(next, direction);
                    self.slider.sliding = false;
                }, 700);
            } else {
                BX.addClass(next, 'active');
                this.slider.sliding = false;
            }

            this.obPictSliderProgressBar && this.resetProgress();
            isCycling && this.cycleSlider();
        },

        stopSlider: function (event) {
            event || (this.slider.paused = true);

            this.slider.interval && clearInterval(this.slider.interval);

            if (this.slider.progress) {
                this.slider.progress.stop();

                var width = parseInt(this.obPictSliderProgressBar.style.width);

                this.slider.progress.options.duration = this.slider.options.interval * width / 200;
                this.slider.progress.options.start = {width: width * 10};
                this.slider.progress.options.finish = {width: 0};
                this.slider.progress.options.complete = null;
                this.slider.progress.animate();
            }
        },

        cycleSlider: function (event) {
            event || (this.slider.paused = false);

            this.slider.interval && clearInterval(this.slider.interval);

            if (this.slider.options.interval && !this.slider.paused) {
                if (this.slider.progress) {
                    this.slider.progress.stop();

                    var width = parseInt(this.obPictSliderProgressBar.style.width);

                    this.slider.progress.options.duration = this.slider.options.interval * (100 - width) / 100;
                    this.slider.progress.options.start = {width: width * 10};
                    this.slider.progress.options.finish = {width: 1000};
                    this.slider.progress.options.complete = BX.delegate(function () {
                        this.slider.interval = true;
                        this.slideNext();
                    }, this);
                    this.slider.progress.animate();
                } else {
                    this.slider.interval = setInterval(BX.proxy(this.slideNext, this), this.slider.options.interval);
                }
            }
        },

        resetProgress: function () {
            this.slider.progress && this.slider.progress.stop();
            this.obPictSliderProgressBar.style.width = 0;
        },

        getItemForDirection: function (direction, active) {
            var activeIndex = this.getItemIndex(active),
                willWrap = direction === 'prev' && activeIndex === 0
                    || direction === 'next' && activeIndex == (this.slider.items.length - 1);

            if (willWrap && !this.slider.options.wrap)
                return active;

            var delta = direction === 'prev' ? -1 : 1,
                itemIndex = (activeIndex + delta) % this.slider.items.length;

            return this.eq(this.slider.items, itemIndex);
        },

        getItemIndex: function (item) {
            this.slider.items = BX.findChildren(item.parentNode, {className: 'item'}, true);

            return this.slider.items.indexOf(item || this.slider.active);
        },

        eq: function (obj, i) {
            var len = obj.length,
                j = +i + (i < 0 ? len : 0);

            return j >= 0 && j < len ? obj[j] : {};
        },

        selectOfferProp: function () {
            var i = 0,
                value = '',
                strTreeValue = '',
                arTreeItem = [],
                rowItems = null,
                target = BX.proxy_context;

            if (target && target.hasAttribute('data-treevalue')) {
                if (BX.hasClass(target, 'selected'))
                    return;

                strTreeValue = target.getAttribute('data-treevalue');
                arTreeItem = strTreeValue.split('_');
                if (this.searchOfferPropIndex(arTreeItem[0], arTreeItem[1])) {
                    rowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
                    if (rowItems && 0 < rowItems.length) {
                        for (i = 0; i < rowItems.length; i++) {
                            value = rowItems[i].getAttribute('data-onevalue');
                            if (value === arTreeItem[1]) {
                                BX.addClass(rowItems[i], 'selected');
                            } else {
                                BX.removeClass(rowItems[i], 'selected');
                            }
                        }
                    }
                }
            }
        },

        searchOfferPropIndex: function (strPropID, strPropValue) {
            var strName = '',
                arShowValues = false,
                i, j,
                arCanBuyValues = [],
                allValues = [],
                index = -1,
                arFilter = {},
                tmpFilter = [];

            for (i = 0; i < this.treeProps.length; i++) {
                if (this.treeProps[i].ID === strPropID) {
                    index = i;
                    break;
                }
            }

            if (-1 < index) {
                for (i = 0; i < index; i++) {
                    strName = 'PROP_' + this.treeProps[i].ID;
                    arFilter[strName] = this.selectedValues[strName];
                }
                strName = 'PROP_' + this.treeProps[index].ID;
                arShowValues = this.getRowValues(arFilter, strName);
                if (!arShowValues) {
                    return false;
                }
                if (!BX.util.in_array(strPropValue, arShowValues)) {
                    return false;
                }
                arFilter[strName] = strPropValue;
                for (i = index + 1; i < this.treeProps.length; i++) {
                    strName = 'PROP_' + this.treeProps[i].ID;
                    arShowValues = this.getRowValues(arFilter, strName);
                    if (!arShowValues) {
                        return false;
                    }
                    allValues = [];
                    if (this.showAbsent) {
                        arCanBuyValues = [];
                        tmpFilter = [];
                        tmpFilter = BX.clone(arFilter, true);
                        for (j = 0; j < arShowValues.length; j++) {
                            tmpFilter[strName] = arShowValues[j];
                            allValues[allValues.length] = arShowValues[j];
                            if (this.getCanBuy(tmpFilter))
                                arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
                        }
                    } else {
                        arCanBuyValues = arShowValues;
                    }
                    if (this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues)) {
                        arFilter[strName] = this.selectedValues[strName];
                    } else {
                        if (this.showAbsent)
                            arFilter[strName] = (arCanBuyValues.length > 0 ? arCanBuyValues[0] : allValues[0]);
                        else
                            arFilter[strName] = arCanBuyValues[0];
                    }
                    this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
                }
                this.selectedValues = arFilter;
                this.changeInfo();
            }
            return true;
        },

        updateRow: function (intNumber, activeID, showID, canBuyID) {
            var i = 0,
                value = '',
                isCurrent = false,
                rowItems = null;

            var lineContainer = this.obTree.querySelectorAll('[data-entity="sku-line-block"]'),
                listContainer;

            if (intNumber > -1 && intNumber < lineContainer.length) {
                listContainer = lineContainer[intNumber].querySelector('ul');
                rowItems = BX.findChildren(listContainer, {tagName: 'li'}, false);
                if (rowItems && 0 < rowItems.length) {
                    for (i = 0; i < rowItems.length; i++) {
                        value = rowItems[i].getAttribute('data-onevalue');
                        isCurrent = value === activeID;

                        if (isCurrent) {
                            BX.addClass(rowItems[i], 'selected');
                        } else {
                            BX.removeClass(rowItems[i], 'selected');
                        }

                        if (BX.util.in_array(value, canBuyID)) {
                            BX.removeClass(rowItems[i], 'notallowed');
                        } else {
                            BX.addClass(rowItems[i], 'notallowed');
                        }

                        rowItems[i].style.display = BX.util.in_array(value, showID) ? '' : 'none';

                        if (isCurrent) {
                            lineContainer[intNumber].style.display = (value == 0 && canBuyID.length == 1) ? 'none' : '';
                        }
                    }
                }
            }
        },

        getRowValues: function (arFilter, index) {
            var i = 0,
                j,
                arValues = [],
                boolSearch = false,
                boolOneSearch = true;

            if (0 === arFilter.length) {
                for (i = 0; i < this.offers.length; i++) {
                    if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
                        arValues[arValues.length] = this.offers[i].TREE[index];
                    }
                }
                boolSearch = true;
            } else {
                for (i = 0; i < this.offers.length; i++) {
                    boolOneSearch = true;
                    for (j in arFilter) {
                        if (arFilter[j] !== this.offers[i].TREE[j]) {
                            boolOneSearch = false;
                            break;
                        }
                    }
                    if (boolOneSearch) {
                        if (!BX.util.in_array(this.offers[i].TREE[index], arValues)) {
                            arValues[arValues.length] = this.offers[i].TREE[index];
                        }
                        boolSearch = true;
                    }
                }
            }
            return (boolSearch ? arValues : false);
        },

        getCanBuy: function (arFilter) {
            var i, j,
                boolSearch = false,
                boolOneSearch = true;

            for (i = 0; i < this.offers.length; i++) {
                boolOneSearch = true;
                for (j in arFilter) {
                    if (arFilter[j] !== this.offers[i].TREE[j]) {
                        boolOneSearch = false;
                        break;
                    }
                }
                if (boolOneSearch) {
                    if (this.offers[i].CAN_BUY) {
                        boolSearch = true;
                        break;
                    }
                }
            }

            return boolSearch;
        },

        setCurrent: function () {
            var i,
                j = 0,
                arCanBuyValues = [],
                strName = '',
                arShowValues = false,
                arFilter = {},
                tmpFilter = [],
                current = this.offers[this.offerNum].TREE;

            for (i = 0; i < this.treeProps.length; i++) {
                strName = 'PROP_' + this.treeProps[i].ID;
                arShowValues = this.getRowValues(arFilter, strName);
                if (!arShowValues) {
                    break;
                }
                if (BX.util.in_array(current[strName], arShowValues)) {
                    arFilter[strName] = current[strName];
                } else {
                    arFilter[strName] = arShowValues[0];
                    this.offerNum = 0;
                }
                if (this.showAbsent) {
                    arCanBuyValues = [];
                    tmpFilter = [];
                    tmpFilter = BX.clone(arFilter, true);
                    for (j = 0; j < arShowValues.length; j++) {
                        tmpFilter[strName] = arShowValues[j];
                        if (this.getCanBuy(tmpFilter)) {
                            arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
                        }
                    }
                } else {
                    arCanBuyValues = arShowValues;
                }
                this.updateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
            }
            this.selectedValues = arFilter;
            this.changeInfo();
        },

        changeInfo: function () {
            var i, j,
                index = -1,
                boolOneSearch = true,
                quantityChanged;

            for (i = 0; i < this.offers.length; i++) {
                boolOneSearch = true;
                for (j in this.selectedValues) {
                    if (this.selectedValues[j] !== this.offers[i].TREE[j]) {
                        boolOneSearch = false;
                        break;
                    }
                }
                if (boolOneSearch) {
                    index = i;
                    break;
                }
            }
            if (index > -1) {
                if (parseInt(this.offers[index].MORE_PHOTO_COUNT) > 1 && this.obPictSlider) {
                    // hide pict and second_pict containers
                    if (this.obPict) {
                        this.obPict.style.display = 'none';
                    }

                    if (this.obSecondPict) {
                        this.obSecondPict.style.display = 'none';
                    }

                    // clear slider container
                    BX.cleanNode(this.obPictSlider);

                    // fill slider container with slides
                    for (i in this.offers[index].MORE_PHOTO) {
                        if (this.offers[index].MORE_PHOTO.hasOwnProperty(i)) {
                            this.obPictSlider.appendChild(
                                BX.create('SPAN', {
                                    props: {className: 'product-item-image-slide item' + (i == 0 ? ' active' : '')},
                                    style: {backgroundImage: 'url(\'' + this.offers[index].MORE_PHOTO[i].SRC + '\')'}
                                })
                            );
                        }
                    }

                    // fill slider indicator if exists
                    if (this.obPictSliderIndicator) {
                        BX.cleanNode(this.obPictSliderIndicator);

                        for (i in this.offers[index].MORE_PHOTO) {
                            if (this.offers[index].MORE_PHOTO.hasOwnProperty(i)) {
                                this.obPictSliderIndicator.appendChild(
                                    BX.create('DIV', {
                                        attrs: {'data-go-to': i},
                                        props: {className: 'product-item-image-slider-control' + (i == 0 ? ' active' : '')}
                                    })
                                );
                                this.obPictSliderIndicator.appendChild(document.createTextNode(' '));
                            }
                        }

                        this.obPictSliderIndicator.style.display = '';
                    }

                    if (this.obPictSliderProgressBar) {
                        this.obPictSliderProgressBar.style.display = '';
                    }

                    // show slider container
                    this.obPictSlider.style.display = '';
                    this.initializeSlider();
                } else {
                    // hide slider container
                    if (this.obPictSlider) {
                        this.obPictSlider.style.display = 'none';
                    }

                    if (this.obPictSliderIndicator) {
                        this.obPictSliderIndicator.style.display = 'none';
                    }

                    if (this.obPictSliderProgressBar) {
                        this.obPictSliderProgressBar.style.display = 'none';
                    }

                    // show pict and pict_second containers
                    if (this.obPict) {
                        if (this.offers[index].PREVIEW_PICTURE) {
                            BX.adjust(this.obPict, {style: {backgroundImage: 'url(\'' + this.offers[index].PREVIEW_PICTURE.SRC + '\')'}});
                        } else {
                            BX.adjust(this.obPict, {style: {backgroundImage: 'url(\'' + this.defaultPict.pict.SRC + '\')'}});
                        }

                        this.obPict.style.display = '';
                    }

                    if (this.secondPict && this.obSecondPict) {
                        if (this.offers[index].PREVIEW_PICTURE_SECOND) {
                            BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url(\'' + this.offers[index].PREVIEW_PICTURE_SECOND.SRC + '\')'}});
                        } else if (this.offers[index].PREVIEW_PICTURE.SRC) {
                            BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url(\'' + this.offers[index].PREVIEW_PICTURE.SRC + '\')'}});
                        } else if (this.defaultPict.secondPict) {
                            BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url(\'' + this.defaultPict.secondPict.SRC + '\')'}});
                        } else {
                            BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url(\'' + this.defaultPict.pict.SRC + '\')'}});
                        }

                        this.obSecondPict.style.display = '';
                    }
                }

                if (this.showSkuProps && this.obSkuProps) {
                    if (this.offers[index].DISPLAY_PROPERTIES.length) {
                        BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
                    } else {
                        BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
                    }
                }

                this.quantitySet(index);
                this.setPrice();
                this.setCompared(this.offers[index].COMPARED);

                this.offerNum = index;
            }
        },

        checkPriceRange: function (quantity) {
            if (typeof quantity === 'undefined' || this.currentPriceMode != 'Q')
                return;

            var range, found = false;

            for (var i in this.currentQuantityRanges) {
                if (this.currentQuantityRanges.hasOwnProperty(i)) {
                    range = this.currentQuantityRanges[i];

                    if (
                        parseInt(quantity) >= parseInt(range.SORT_FROM)
                        && (
                            range.SORT_TO == 'INF'
                            || parseInt(quantity) <= parseInt(range.SORT_TO)
                        )
                    ) {
                        found = true;
                        this.currentQuantityRangeSelected = range.HASH;
                        break;
                    }
                }
            }

            if (!found && (range = this.getMinPriceRange())) {
                this.currentQuantityRangeSelected = range.HASH;
            }

            for (var k in this.currentPrices) {
                if (this.currentPrices.hasOwnProperty(k)) {
                    if (this.currentPrices[k].QUANTITY_HASH == this.currentQuantityRangeSelected) {
                        this.currentPriceSelected = k;
                        break;
                    }
                }
            }
        },

        getMinPriceRange: function () {
            var range;

            for (var i in this.currentQuantityRanges) {
                if (this.currentQuantityRanges.hasOwnProperty(i)) {
                    if (
                        !range
                        || parseInt(this.currentQuantityRanges[i].SORT_FROM) < parseInt(range.SORT_FROM)
                    ) {
                        range = this.currentQuantityRanges[i];
                    }
                }
            }

            return range;
        },

        checkQuantityControls: function () {
            if (!this.obQuantity)
                return;

            var reachedTopLimit = this.checkQuantity && parseFloat(this.obQuantity.value) + this.stepQuantity > this.maxQuantity,
                reachedBottomLimit = parseFloat(this.obQuantity.value) - this.stepQuantity < this.minQuantity;

            if (reachedTopLimit) {
                BX.addClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled');
            } else if (BX.hasClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled')) {
                BX.removeClass(this.obQuantityUp, 'product-item-amount-field-btn-disabled');
            }

            if (reachedBottomLimit) {
                BX.addClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled');
            } else if (BX.hasClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled')) {
                BX.removeClass(this.obQuantityDown, 'product-item-amount-field-btn-disabled');
            }

            if (reachedTopLimit && reachedBottomLimit) {
                this.obQuantity.setAttribute('disabled', 'disabled');
            } else {
                this.obQuantity.removeAttribute('disabled');
            }
        },

        setPrice: function () {
            var obData, price;

            if (this.obQuantity) {
                this.checkPriceRange(this.obQuantity.value);
            }

            this.checkQuantityControls();

            price = this.currentPrices[this.currentPriceSelected];

            if (this.obPrice) {
                if (price) {
                    BX.adjust(this.obPrice, {html: BX.Currency.currencyFormat(price.RATIO_PRICE, price.CURRENCY, true)});
                } else {
                    BX.adjust(this.obPrice, {html: ''});
                }

                if (this.showOldPrice && this.obPriceOld) {
                    if (price && price.RATIO_PRICE !== price.RATIO_BASE_PRICE) {
                        BX.adjust(this.obPriceOld, {
                            style: {display: ''},
                            html: BX.Currency.currencyFormat(price.RATIO_BASE_PRICE, price.CURRENCY, true)
                        });
                    } else {
                        BX.adjust(this.obPriceOld, {
                            style: {display: 'none'},
                            html: ''
                        });
                    }
                }

                if (this.obPriceTotal) {
                    if (price && this.obQuantity && this.obQuantity.value != this.stepQuantity) {
                        BX.adjust(this.obPriceTotal, {
                            html: BX.message('PRICE_TOTAL_PREFIX') + ' <strong>'
                                + BX.Currency.currencyFormat(price.PRICE * this.obQuantity.value, price.CURRENCY, true)
                                + '</strong>',
                            style: {display: ''}
                        });
                    } else {
                        BX.adjust(this.obPriceTotal, {
                            html: '',
                            style: {display: 'none'}
                        });
                    }
                }

                if (this.showPercent) {
                    if (price && parseInt(price.DISCOUNT) > 0) {
                        obData = {style: {display: ''}, html: -price.PERCENT + '%'};
                    } else {
                        obData = {style: {display: 'none'}, html: ''};
                    }

                    if (this.obDscPerc) {
                        BX.adjust(this.obDscPerc, obData);
                    }

                    if (this.obSecondDscPerc) {
                        BX.adjust(this.obSecondDscPerc, obData);
                    }
                }
            }
        },

        compare: function (event) {
            var checkbox = this.obCompare.querySelector('[data-entity="compare-checkbox"]'),
                target = BX.getEventTarget(event),
                checked = true;

            if (checkbox) {
                checked = target === checkbox ? checkbox.checked : !checkbox.checked;
            }

            var url = checked ? this.compareData.compareUrl : this.compareData.compareDeleteUrl,
                compareLink;

            if (url) {
                if (target !== checkbox) {
                    BX.PreventDefault(event);
                    this.setCompared(checked);
                }

                switch (this.productType) {
                    case 0: // no catalog
                    case 1: // product
                    case 2: // set
                        compareLink = url.replace('#ID#', this.product.id.toString());
                        break;
                    case 3: // sku
                        compareLink = url.replace('#ID#', this.offers[this.offerNum].ID);
                        break;
                }

                BX.ajax({
                    method: 'POST',
                    dataType: checked ? 'json' : 'html',
                    url: compareLink + (compareLink.indexOf('?') !== -1 ? '&' : '?') + 'ajax_action=Y',
                    onsuccess: checked
                        ? BX.proxy(this.compareResult, this)
                        : BX.proxy(this.compareDeleteResult, this)
                });
            }
        },

        compareResult: function (result) {
            var popupContent, popupButtons;

            if (this.obPopupWin) {
                this.obPopupWin.close();
            }

            if (!BX.type.isPlainObject(result))
                return;

            this.initPopupWindow();

            if (this.offers.length > 0) {
                this.offers[this.offerNum].COMPARED = result.STATUS === 'OK';
            }

            if (result.STATUS === 'OK') {
                BX.onCustomEvent('OnCompareChange');

                popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>'
                    + BX.message('COMPARE_MESSAGE_OK')
                    + '</p></div>';

                if (this.showClosePopup) {
                    popupButtons = [
                        new BasketButton({
                            text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
                            events: {
                                click: BX.delegate(this.compareRedirect, this)
                            },
                            style: {marginRight: '10px'}
                        }),
                        new BasketButton({
                            text: BX.message('BTN_MESSAGE_CLOSE_POPUP'),
                            events: {
                                click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                            }
                        })
                    ];
                } else {
                    popupButtons = [
                        new BasketButton({
                            text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
                            events: {
                                click: BX.delegate(this.compareRedirect, this)
                            }
                        })
                    ];
                }
            } else {
                popupContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>'
                    + (result.MESSAGE ? result.MESSAGE : BX.message('COMPARE_UNKNOWN_ERROR'))
                    + '</p></div>';
                popupButtons = [
                    new BasketButton({
                        text: BX.message('BTN_MESSAGE_CLOSE'),
                        events: {
                            click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                        }
                    })
                ];
            }

            this.obPopupWin.setTitleBar(BX.message('COMPARE_TITLE'));
            this.obPopupWin.setContent(popupContent);
            this.obPopupWin.setButtons(popupButtons);
            this.obPopupWin.show();
        },

        compareDeleteResult: function () {
            BX.onCustomEvent('OnCompareChange');

            if (this.offers && this.offers.length) {
                this.offers[this.offerNum].COMPARED = false;
            }
        },

        setCompared: function (state) {
            if (!this.obCompare)
                return;

            var checkbox = this.obCompare.querySelector('[data-entity="compare-checkbox"]');
            if (checkbox) {
                checkbox.checked = state;
            }
        },

        setCompareInfo: function (comparedIds) {
            if (!BX.type.isArray(comparedIds))
                return;

            for (var i in this.offers) {
                if (this.offers.hasOwnProperty(i)) {
                    this.offers[i].COMPARED = BX.util.in_array(this.offers[i].ID, comparedIds);
                }
            }
        },

        compareRedirect: function () {
            if (this.compareData.comparePath) {
                location.href = this.compareData.comparePath;
            } else {
                this.obPopupWin.close();
            }
        },

        checkDeletedCompare: function (id) {
            switch (this.productType) {
                case 0: // no catalog
                case 1: // product
                case 2: // set
                    if (this.product.id == id) {
                        this.setCompared(false);
                    }

                    break;
                case 3: // sku
                    var i = this.offers.length;
                    while (i--) {
                        if (this.offers[i].ID == id) {
                            this.offers[i].COMPARED = false;

                            if (this.offerNum == i) {
                                this.setCompared(false);
                            }

                            break;
                        }
                    }
            }
        },

        initBasketUrl: function () {
            this.basketUrl = (this.basketMode === 'ADD' ? this.basketData.add_url : this.basketData.buy_url);
            switch (this.productType) {
                case 1: // product
                case 2: // set
                    this.basketUrl = this.basketUrl.replace('#ID#', this.product.id.toString());
                    break;
                case 3: // sku
                    this.basketUrl = this.basketUrl.replace('#ID#', this.offers[this.offerNum].ID);
                    break;
            }
            this.basketParams = {
                'ajax_basket': 'Y'
            };
            if (this.showQuantity) {
                this.basketParams[this.basketData.quantity] = this.obQuantity.value;
            }
            if (this.basketData.sku_props) {
                this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
            }
        },

        fillBasketProps: function () {
            if (!this.visual.BASKET_PROP_DIV) {
                return;
            }
            var
                i = 0,
                propCollection = null,
                foundValues = false,
                obBasketProps = null;

            if (this.basketData.useProps && !this.basketData.emptyProps) {
                if (this.obPopupWin && this.obPopupWin.contentContainer) {
                    obBasketProps = this.obPopupWin.contentContainer;
                }
            } else {
                obBasketProps = BX(this.visual.BASKET_PROP_DIV);
            }
            if (obBasketProps) {
                propCollection = obBasketProps.getElementsByTagName('select');
                if (propCollection && propCollection.length) {
                    for (i = 0; i < propCollection.length; i++) {
                        if (!propCollection[i].disabled) {
                            switch (propCollection[i].type.toLowerCase()) {
                                case 'select-one':
                                    this.basketParams[propCollection[i].name] = propCollection[i].value;
                                    foundValues = true;
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
                propCollection = obBasketProps.getElementsByTagName('input');
                if (propCollection && propCollection.length) {
                    for (i = 0; i < propCollection.length; i++) {
                        if (!propCollection[i].disabled) {
                            switch (propCollection[i].type.toLowerCase()) {
                                case 'hidden':
                                    this.basketParams[propCollection[i].name] = propCollection[i].value;
                                    foundValues = true;
                                    break;
                                case 'radio':
                                    if (propCollection[i].checked) {
                                        this.basketParams[propCollection[i].name] = propCollection[i].value;
                                        foundValues = true;
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            }
            if (!foundValues) {
                this.basketParams[this.basketData.props] = [];
                this.basketParams[this.basketData.props][0] = 0;
            }
        },

        add2Basket: function () {
            this.basketMode = 'ADD';
            this.basket();
        },

        buyBasket: function () {
            this.basketMode = 'BUY';
            this.basket();
        },

        sendToBasket: function () {
            if (!this.canBuy) {
                return;
            }

            // check recommendation
            if (this.product && this.product.id && this.bigData) {
                this.rememberProductRecommendation();
            }

            this.initBasketUrl();
            this.fillBasketProps();
            BX.ajax({
                method: 'POST',
                dataType: 'json',
                url: this.basketUrl,
                data: this.basketParams,
                onsuccess: BX.proxy(this.basketResult, this)
            });
        },

        basket: function () {
            var contentBasketProps = '';
            if (!this.canBuy) {
                return;
            }
            switch (this.productType) {
                case 1: // product
                case 2: // set
                    if (this.basketData.useProps && !this.basketData.emptyProps) {
                        this.initPopupWindow();
                        this.obPopupWin.setTitleBar(BX.message('TITLE_BASKET_PROPS'));
                        if (BX(this.visual.BASKET_PROP_DIV)) {
                            contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
                        }
                        this.obPopupWin.setContent(contentBasketProps);
                        this.obPopupWin.setButtons([
                            new BasketButton({
                                text: BX.message('BTN_MESSAGE_SEND_PROPS'),
                                events: {
                                    click: BX.delegate(this.sendToBasket, this)
                                }
                            })
                        ]);
                        this.obPopupWin.show();
                    } else {
                        this.sendToBasket();
                    }
                    break;
                case 3: // sku
                    this.sendToBasket();
                    break;
            }
        },

        basketResult: function (arResult) {
            var strContent = '',
                strPict = '',
                successful,
                buttons = [];

            if (this.obPopupWin)
                this.obPopupWin.close();

            if (!BX.type.isPlainObject(arResult))
                return;

            successful = arResult.STATUS === 'OK';

            if (successful) {
                this.setAnalyticsDataLayer('addToCart');
            }

            if (successful && this.basketAction === 'BUY') {
                this.basketRedirect();
            } else {
                this.initPopupWindow();

                if (successful) {
                    BX.onCustomEvent('OnBasketChange');

                    if (BX.findParent(this.obProduct, {className: 'bx_sale_gift_main_products'}, 10)) {
                        BX.onCustomEvent('onAddToBasketMainProduct', [this]);
                    }

                    switch (this.productType) {
                        case 1: // product
                        case 2: // set
                            strPict = this.product.pict.SRC;
                            break;
                        case 3: // sku
                            strPict = (this.offers[this.offerNum].PREVIEW_PICTURE ?
                                    this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
                                    this.defaultPict.pict.SRC
                            );
                            break;
                    }

                    strContent = '<div style="width: 100%; margin: 0; text-align: center;"><img src="'
                        + strPict + '" height="130" style="max-height:130px"><p>' + this.product.name + '</p></div>';

                    if (this.showClosePopup) {
                        buttons = [
                            new BasketButton({
                                text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
                                events: {
                                    click: BX.delegate(this.basketRedirect, this)
                                },
                                style: {marginRight: '10px'}
                            }),
                            new BasketButton({
                                text: BX.message("BTN_MESSAGE_CLOSE_POPUP"),
                                events: {
                                    click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                                }
                            })
                        ];
                    } else {
                        buttons = [
                            new BasketButton({
                                text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
                                events: {
                                    click: BX.delegate(this.basketRedirect, this)
                                }
                            })
                        ];
                    }
                } else {
                    strContent = '<div style="width: 100%; margin: 0; text-align: center;"><p>'
                        + (arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'))
                        + '</p></div>';
                    buttons = [
                        new BasketButton({
                            text: BX.message('BTN_MESSAGE_CLOSE'),
                            events: {
                                click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
                            }
                        })
                    ];
                }
                this.obPopupWin.setTitleBar(successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'));
                this.obPopupWin.setContent(strContent);
                this.obPopupWin.setButtons(buttons);
                this.obPopupWin.show();
            }
        },

        basketRedirect: function () {
            location.href = (this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
        },

        initPopupWindow: function () {
            if (this.obPopupWin)
                return;

            this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_' + this.visual.ID, null, {
                autoHide: true,
                offsetLeft: 0,
                offsetTop: 0,
                overlay: true,
                closeByEsc: true,
                titleBar: true,
                closeIcon: true,
                contentColor: 'white',
                className: this.templateTheme ? 'bx-' + this.templateTheme : ''
            });
        }
    };
})(window);

// FAST WINDOW
$(document).on('click', '.open-fast-window', function () {
    let json_product = $(this).closest('.catalog-item-product').find('input.product-values').val();
    if (json_product !== '') {

        let wrapper = $('.section_wrapper');
        let product = JSON.parse(json_product);
        $(wrapper).find('div.box-popup-product').remove();

        let box_popup = BX.create('DIV', {
            props: {
                className: 'position-fixed width-100 top-32 d-flex justify-content-center z-index-1400 box-popup-product'
            },
            children: [
                BX.create('DIV', {
                    props: {
                        className: 'open-modal-product bg-gray-white p-lg-4 p-md-4 p-3 max-width-1024 width-100 br-10'
                    },
                })
            ]
        });

        let box_product = BX.findChildByClassName(box_popup, 'open-modal-product');
        let match = 0;
        let attr_val = 'data-max-quantity';

        box_product.appendChild(
            BX.create('H5', {
                props: {
                    className: 'mb-2 d-flex flex-row font-19'
                },
                children: [
                    BX.create('SPAN', {
                        props: {
                            className: 'col-11 font-weight-bold mb-2 p-0',
                        },
                        text: product.NAME
                    }),
                    BX.create('SPAN', {
                        props: {
                            className: 'col-1 text-right p-0 close-box cursor-pointer',
                            title: 'Закрыть'
                        },
                        html: '<svg width="25" height="25" viewBox="0 0 9 8" fill="none"' +
                            ' xmlns="http://www.w3.org/2000/svg"><path d="M1 7.5L8 0.5M1 0.5L8 7.5"' +
                            ' stroke="#565656" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                    })
                ]
            })
        );

        if (product.TYPE_PRODUCT === 'OFFERS') {
            let i = 0;
            let active = 0;

            box_product.appendChild(BX.create('DIV', {
                props: {
                    className: 'flex-lg-row flex-md-row flex-column d-flex'
                },
                children: [
                    BX.create('DIV', {
                        props: {
                            className: 'box-with-image-prod col-lg-6 col-md-6 col-12 mb-lg-0 mb-md-0 mb-4 position-relative'
                        },
                        children: [
                            BX.create('div', {
                                props: {
                                    className: 'flex-class box-with-image-one height_100',
                                },
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'position-absolute like-with-fav',
                                },
                                children: [
                                    BX.create('DIV', {
                                        props: {
                                            className: 'box_with_like like-modal',
                                        },
                                        children: [
                                            BX.create('A', {
                                                props: {
                                                    className: 'icon_like method mb-3',
                                                    title: "Нравится",
                                                    href: 'javascript:void(0);'
                                                },
                                                dataset: {
                                                    method: 'like'
                                                },
                                                html: '<i class="fa fa-heart-o" aria-hidden="true"></i>' +
                                                    '<article class="like_span" id="likes">' + product.LIKE.COUNT_LIKES + '</article>'
                                            }),
                                            BX.create('A', {
                                                props: {
                                                    className: 'product-item__favorite-star method',
                                                    title: "Добавить в избранное",
                                                    href: 'javascript:void(0);'
                                                },
                                                dataset: {
                                                    method: 'favorite'
                                                },
                                                html: '<i class="fa fa-star-o" aria-hidden="true"></i>'
                                            })
                                        ]
                                    })
                                ]
                            })
                        ]
                    }),
                    BX.create('DIV', {
                        props: {
                            className: 'col-lg-6 col-md-6 col-12 d-flex flex-column color-darkOsh justify-content-between catalog-item-product'
                        },
                        children: [
                            BX.create('DIV', {
                                props: {
                                    className: 'offers-box mb-2 d-flex flex-row flex-wrap',
                                },
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'prices-box ml-lg-3 ml-md-3 ml-0 mb-lg-4 mb-md-2 mb-2',
                                },
                                children: [
                                    BX.create('P', {
                                        props: {
                                            className: 'base-price bx_price font-weight-bold font-27 mb-3'
                                        },
                                    }),
                                    BX.create('DIV', {
                                        props: {
                                            className: 'price-group prices-all mb-lg-4 mb-md-3 mb-3'
                                        },
                                    }),
                                    BX.create('DIV', {
                                        props: {
                                            className: 'add-to-basket box-basket d-flex flex-row align-items-center bx_catalog_item_controls mb-3'
                                        },
                                    })
                                ]
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'props-box ml-lg-4 ml-md-4 ml-0 d-flex flex-lg-row flex-md-row flex-column ' +
                                        'justify-content-between align-items-end',
                                },
                                children: [
                                    BX.create('DIV', {
                                        props: {
                                            className: 'props-box-child col-lg-8 col-md-8 col-12 pl-0 d-flex flex-column justify-content-between'
                                        },
                                        children: [
                                            BX.create('DIV', {
                                                props: {
                                                    className: 'props-box-child-advanse'
                                                },
                                            }),
                                            BX.create('DIV', {
                                                props: {
                                                    className: 'props-box-child-popup mt-lg-3 mt-md-2 mt-3 mb-lg-0 mb-md-0 mb-3'
                                                },
                                            })
                                        ]
                                    }),
                                    BX.create('A', {
                                        props: {
                                            className: 'color-redLight font-weight-bold col-lg-4 col-md-4 col-12 ' +
                                                'text-decoration-underline font-14 p-0 text-lg-right text-md-right',
                                            href: product.DETAIL_PAGE_URL
                                        },
                                        text: 'Подробнее'
                                    })
                                ]
                            })
                        ]
                    })
                ]
            }));

            $.each(product.OFFERS, function (key, offer) {
                let active_price = 'd-none';
                let active_box = 'false';
                if (i === 0) {
                    active_price = 'd-block';
                    active = key;
                    active_box = 'true'
                    let box_mages = BX.findChildByClassName(box_product, 'box-with-image-one');
                    box_mages.appendChild(BX.create('IMG', {
                        props: {
                            className: 'w-h-350',
                            src: offer.DETAIL_PICTURE,
                            alt: 'modal-product'
                        },
                    }))

                }
                i++;
                let box_offers = BX.findChildByClassName(box_product, 'offers-box');

                $.each(offer.PROPS, function (key_prop, prop) {
                    if (prop.CODE === 'SHTUK_V_UPAKOVKE' && prop.VALUE !== ''
                        || prop.CODE === 'GRAMMOVKA_G' && prop.VALUE !== '') {
                        box_offers.appendChild(BX.create('DIV', {
                            props: {
                                className: 'red_button_cart width-fit-content mb-lg-2 m-md-2 m-1 offer-box ',
                                src: offer.DETAIL_PICTURE,
                                alt: 'modal-product',
                            },
                            html: prop.VALUE,
                            dataset: {
                                active: active_box,
                                product_id: offer.ID,
                                product_quantity: offer.QUANTITY,
                                price_base: offer.PRICE[1].PRICE,
                                basket_quantity: offer.BASKET
                            }
                        }))
                    } else if (prop.CODE === 'TSVET' && prop.VALUE !== '') {
                        box_offers.appendChild(BX.create('IMG', {
                            props: {
                                className: 'mr-2 br-10 box-offer-fast offer-box',
                                style: 'width:90px; height:90px',
                                src: offer.DETAIL_PICTURE,
                                alt: 'modal-product'
                            },
                            dataset: {
                                active: active_box,
                                product_id: offer.ID,
                                product_quantity: offer.QUANTITY,
                                price_base: offer.PRICE[1].PRICE,
                                basket_quantity: offer.BASKET
                            }
                        }))
                    }
                });


                // PRICE
                let price_group = BX.findChildByClassName(box_product, 'price-group');
                let price_base = BX.findChildByClassName(box_product, 'base-price');
                let new_contain_fot_price = price_group.appendChild(BX.create('DIV', {
                    props: {
                        className: 'price-box-with-child box-prices mt-lg-3 mt-md-2 mt-3 mb-lg-0 mb-md-0 mb-3 flex-column ' + active_price
                    },
                    dataset: {
                        offer_id: offer.ID
                    }
                }))

                $.each(offer.PRICE, function (i, price) {
                    let sale = '';
                    if ((product.USE_DISCOUNT === 'Да' || product.USE_CUSTOM_SALE_PRICE === true)
                        && parseInt(product.SALE_PRICE) > 0) {
                        sale = 'text-decoration-color: #f55f5c; text-decoration-line: line-through;'
                    }

                    if (parseInt(price.PRICE_TYPE_ID) === product.BASE_PRICE) {
                        if ((product.USE_DISCOUNT === 'Да' || product.USE_CUSTOM_SALE_PRICE === true)
                            && parseInt(offer.SALE_PRICE) > 0) {

                            let print_price = offer.PRICE[0].PRICE;
                            let sale_price = offer.SALE_PRICE;
                            let print_price_sum = (parseInt(offer.PRICE[0].PRICE) - parseInt(sale_price)) ?? 0;
                            price_base.innerHTML = sale_price + ' руб. <span class="font-14 ml-3">' +
                                ' <b class="decoration-color-red mr-2">' + print_price + '₽</b>' +
                                '<b class="sale-percent"> - ' + print_price_sum + '₽</b></span>';

                        } else {
                            price_base.innerHTML = '<span class="font-14 card-price-text">от </span>' + price.PRICE + '₽';
                        }
                    }

                    new_contain_fot_price.appendChild(BX.create('P', {
                        props: {
                            className: 'mb-2',
                        },
                        children: [
                            BX.create('SPAN', {
                                props: {
                                    className: 'font-16 mr-2 font-weight-bold',
                                },
                                html: '<b>' + price.NAME + '</b>'
                            }),
                            BX.create('SPAN', {
                                props: {
                                    className: 'font-16',
                                },
                                html: ' - '
                            }),
                            BX.create('SPAN', {
                                props: {
                                    className: 'font-16 ml-2 font-weight-bold',
                                    style: sale
                                },
                                html: '<b>' + price.PRINT_PRICE + '</b>'
                            })
                        ]
                    }));
                });
            })

            let basket_box = BX.findChildByClassName(box_product, 'add-to-basket');
            if (parseInt(product.OFFERS[active].QUANTITY) > 0) {

                basket_box.appendChild(BX.create('DIV', {
                    props: {
                        className: 'product-item-amount-field-contain-wrap mr-4',
                    },
                    children: [
                        BX.create('DIV', {
                            props: {
                                className: 'product-item-amount-field-contain d-flex flex-row align-items-center',
                            },
                            children: [
                                BX.create('A', {
                                    props: {
                                        className: 'btn-minus  minus_icon no-select add2basket removeToBasketOpenWindow',
                                        href: 'javascript:void(0)'
                                    },
                                    dataset: {
                                        url: product.DETAIL_PAGE_URL,
                                        product_id: product.OFFERS[active].ID,
                                        id: product.BUY_LINK
                                    },
                                }),
                                BX.create('DIV', {
                                    props: {
                                        className: 'product-item-amount-field-block',
                                    },
                                    children: [
                                        BX.create('INPUT', {
                                            props: {
                                                className: 'product-item-amount card_element inputBasketOpenWindow',
                                                type: 'text',
                                                value: 0,
                                                id: product.QUANTITY_ID,
                                                style: 'background-color:#e1e1e1'
                                            },
                                            dataset: {
                                                url: product.DETAIL_PAGE_URL,
                                                product_id: product.OFFERS[active].ID,
                                            },
                                        })
                                    ]
                                }),
                                BX.create('A', {
                                    props: {
                                        className: 'btn-plus plus_icon no-select add2basket addToBasketOpenWindow',
                                        href: 'javascript:void(0)',
                                        title: 'Доступно ' + product.OFFERS[active].QUANTITY + 'шт.',
                                        id: product.BUY_LINK
                                    },
                                    dataset: {
                                        url: product.DETAIL_PAGE_URL,
                                        product_id: product.OFFERS[active].ID,
                                    },
                                }),
                            ]
                        }),
                        BX.create('DIV', {
                            props: {
                                className: 'alert_quantity',
                            },
                            dataset: {
                                id: product.OFFERS[active].ID,
                            },
                        }),
                    ],
                }));

                basket_box.appendChild(BX.create('SPAN', {
                    props: {
                        className: 'btn red_button_cart btn-plus add2basket buttonToBasketOpenWindow',
                    },
                    dataset: {
                        url: product.DETAIL_PAGE_URL,
                        product_id: product.OFFERS[active].ID,
                        id: product.BUY_LINK
                    },
                    html: '<img class="image-cart" src="/local/templates/Oshisha/images/cart-white.png"/>',
                }))
            }
            match = product.OFFERS[active].QUANTITY;
            attr_val = 'data-max_quantity'
        } else {
            box_product.appendChild(BX.create('DIV', {
                props: {
                    className: 'd-flex flex-lg-row flex-md-row flex-column'
                },
                children: [
                    BX.create('DIV', {
                        props: {
                            className: 'box-with-image-prod col-lg-6 col-md-6 col-12 mb-lg-0 mb-md-0 mb-4 position-relative'
                        },
                        children: [
                            BX.create('div', {
                                props: {
                                    className: 'flex-class box-with-image-one height_100',
                                },
                                children: [
                                    BX.create('IMG', {
                                        props: {
                                            className: 'w-h-350',
                                            src: product.MORE_PHOTO[0].SRC,
                                            alt: 'modal-product'
                                        },
                                    }),
                                ]
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'position-absolute like-with-fav',
                                },
                                children: [
                                    BX.create('DIV', {
                                        props: {
                                            className: 'box_with_like like-modal',
                                        },
                                        children: [
                                            BX.create('A', {
                                                props: {
                                                    className: 'icon_like method mb-3',
                                                    title: "Нравится",
                                                    href: 'javascript:void(0);'
                                                },
                                                dataset: {
                                                    method: 'like'
                                                },
                                                html: '<i class="fa fa-heart-o" aria-hidden="true"></i>' +
                                                    '<article class="like_span" id="likes">' + product.LIKE.COUNT_LIKES + '</article>'
                                            }),
                                            BX.create('A', {
                                                props: {
                                                    className: 'product-item__favorite-star method',
                                                    title: "Добавить в избранное",
                                                    href: 'javascript:void(0);'
                                                },
                                                dataset: {
                                                    method: 'favorite'
                                                },
                                                html: '<i class="fa fa-star-o" aria-hidden="true"></i>'
                                            })
                                        ]
                                    })
                                ]
                            })
                        ]
                    }),
                    BX.create('DIV', {
                        props: {
                            className: 'col-lg-6 col-md-6 col-12 d-flex flex-column color-darkOsh justify-content-between'
                        },
                        children: [
                            BX.create('DIV', {
                                props: {
                                    className: 'prices-box ml-lg-4 ml-md-4 ml-0 mb-lg-4 mb-md-2 mb-2',
                                },
                                children: [
                                    BX.create('P', {
                                        props: {
                                            className: 'base-price font-weight-bold font-27 mb-3'
                                        },
                                    }),
                                    BX.create('DIV', {
                                        props: {
                                            className: 'price-group mb-lg-4 mb-md-3 mb-3'
                                        },
                                    }),
                                    BX.create('DIV', {
                                        props: {
                                            className: 'add-to-basket box-basket d-flex flex-row align-items-center bx_catalog_item_controls mb-3'
                                        },
                                    })
                                ]
                            }),
                            BX.create('DIV', {
                                props: {
                                    className: 'props-box ml-lg-4 ml-md-4 ml-0 d-flex flex-lg-row flex-md-row flex-column ' +
                                        'justify-content-between align-items-end',
                                },
                                children: [
                                    BX.create('DIV', {
                                        props: {
                                            className: 'props-box-child col-lg-8 col-md-8 col-12 pl-0 d-flex flex-column justify-content-between'
                                        },
                                        children: [
                                            BX.create('DIV', {
                                                props: {
                                                    className: 'props-box-child-advanse'
                                                },
                                            }),
                                            BX.create('DIV', {
                                                props: {
                                                    className: 'props-box-child-popup mt-lg-3 mt-md-2 mt-3 mb-lg-0 mb-md-0 mb-3'
                                                },
                                            })
                                        ]
                                    }),
                                    BX.create('A', {
                                        props: {
                                            className: 'color-redLight font-weight-bold col-lg-4 col-md-4 col-12 ' +
                                                'text-decoration-underline font-14 p-0 text-lg-right text-md-right',
                                            href: product.DETAIL_PAGE_URL
                                        },
                                        text: 'Подробнее'
                                    })
                                ]
                            })
                        ]
                    })
                ]
            }));

            // IMAGE && SLIDER
            if (product.MORE_PHOTO.length > 1) {

                let product_box = BX.findChildByClassName(box_product, 'box-with-image-one');
                let image_box = BX.findChildByClassName(box_product, 'box-with-image-prod');

                BX.cleanNode(product_box);
                BX.removeClass(product_box, 'flex-class');

                product_box.appendChild(BX.create('DIV', {
                    props: {
                        className: 'slick-images-box height_100',
                    },
                }));

                image_box.appendChild(BX.create('DIV', {
                    props: {
                        className: 'position-absolute slider-controls max-width-400',
                    },
                    children: [
                        BX.create('DIV', {
                            props: {
                                className: 'slick-images-controls',
                            },
                        })
                    ]
                }));


                let slick = BX.findChildByClassName(product_box, 'slick-images-box');
                let slick_controls = BX.findChildByClassName(image_box, 'slick-images-controls');
                let count = 0;
                $.each(product.MORE_PHOTO, function (k, image) {
                    count++;
                    slick.appendChild(BX.create('IMG', {
                        props: {
                            className: 'w-h-350',
                            src: image.SRC,
                            alt: 'modal-product'
                        },
                    }));

                    slick_controls.appendChild(
                        BX.create('DIV', {
                            props: {
                                className: 'product-slider-controls-image',
                            },
                            children: [
                                BX.create('IMG', {
                                    props: {
                                        src: image.SRC,
                                        alt: 'modal-product'
                                    },
                                })
                            ]
                        })
                    );
                });

                $(slick).slick({
                    arrows: true,
                    prevArrow: '<span class="product-item-detail-slider-left carousel_elem_custom" ' +
                        'data-entity="slider-control-left" style="">' +
                        '<i class="fa fa-angle-left" aria-hidden="true"></i></span>',
                    nextArrow: '<span class="product-item-detail-slider-right carousel_elem_custom" ' +
                        'data-entity="slider-control-right" style=""' +
                        '><i class="fa fa-angle-right" aria-hidden="true"></i></span>',
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    asNavFor: $(slick_controls)
                });

                $(slick_controls).slick({
                    slidesToScroll: 1,
                    slidesToShow: count,
                    asNavFor: $(slick),
                    arrows: false,
                    focusOnSelect: true
                });
            }

            // PRICE
            let price_group = BX.findChildByClassName(box_product, 'price-group');
            let price_base = BX.findChildByClassName(box_product, 'base-price');

            $.each(product.PRICE, function (i, price) {
                let sale = '';

                if ((product.USE_DISCOUNT === 'Да' || product.USE_CUSTOM_SALE_PRICE === true) && parseInt(product.SALE_PRICE) > 0) {
                    sale = 'text-decoration-color: #f55f5c; text-decoration-line: line-through;'
                }

                if (parseInt(price.PRICE_TYPE_ID) === product.BASE_PRICE) {
                    if ((product.USE_DISCOUNT === 'Да' || product.USE_CUSTOM_SALE_PRICE === true) && parseInt(product.SALE_PRICE) > 0) {
                        let print_price = product.PRICE[0].PRINT_PRICE;
                        let sale_price = product.SALE_PRICE;
                        let print_price_sum = (parseInt(product.PRICE[0].PRICE) - parseInt(sale_price)) ?? 0;
                        price_base.innerHTML = sale_price + ' руб. <span class="font-14 ml-3">' +
                            ' <b class="decoration-color-red mr-2">' + print_price + '</b>' +
                            '<b class="sale-percent"> - ' + print_price_sum + ' руб.</b></span>';
                    } else {
                        price_base.innerHTML = '<span class="font-14 card-price-text">от </span>' + price.PRINT_PRICE;
                    }
                }

                price_group.appendChild(BX.create('P', {
                    props: {
                        className: 'mb-2',
                    },
                    children: [
                        BX.create('SPAN', {
                            props: {
                                className: 'font-16 mr-2 font-weight-bold',
                            },
                            html: '<b>' + price.NAME + '</b>'
                        }),
                        BX.create('SPAN', {
                            props: {
                                className: 'font-16',
                            },
                            html: ' - '
                        }),
                        BX.create('SPAN', {
                            props: {
                                className: 'font-16 ml-2 font-weight-bold',
                                style: sale
                            },
                            html: '<b>' + price.PRINT_PRICE + '</b>'
                        })
                    ]
                }));
            });

            let basket_box = BX.findChildByClassName(box_product, 'add-to-basket');
            match = product.PRODUCT.QUANTITY;

            if (parseInt(product.PRODUCT.QUANTITY) > 0) {

                let product_props = product.PRODUCT;

                basket_box.appendChild(BX.create('DIV', {
                    props: {
                        className: 'product-item-amount-field-contain-wrap mr-4',
                    },
                    children: [
                        BX.create('DIV', {
                            props: {
                                className: 'product-item-amount-field-contain d-flex flex-row align-items-center',
                            },
                            children: [
                                BX.create('A', {
                                    props: {
                                        className: 'btn-minus  minus_icon no-select add2basket removeToBasketOpenWindow',
                                        href: 'javascript:void(0)'
                                    },
                                    dataset: {
                                        url: product.DETAIL_PAGE_URL,
                                        product_id: product.ID,
                                        id: product.BUY_LINK
                                    },
                                }),
                                BX.create('DIV', {
                                    props: {
                                        className: 'product-item-amount-field-block',
                                    },
                                    children: [
                                        BX.create('INPUT', {
                                            props: {
                                                className: 'product-item-amount card_element inputBasketOpenWindow',
                                                type: 'text',
                                                value: product.ACTUAL_BASKET,
                                                id: product.QUANTITY_ID,
                                                style: 'background-color:#e1e1e1'
                                            },
                                            dataset: {
                                                url: product.DETAIL_PAGE_URL,
                                                product_id: product.ID,
                                            },
                                        })
                                    ]
                                }),
                                BX.create('A', {
                                    props: {
                                        className: 'btn-plus plus_icon no-select add2basket addToBasketOpenWindow',
                                        href: 'javascript:void(0)',
                                        title: 'Доступно ' + product_props.QUANTITY + 'шт.',
                                        id: product.BUY_LINK
                                    },
                                    dataset: {
                                        url: product.DETAIL_PAGE_URL,
                                        product_id: product.ID,
                                    },
                                }),
                            ]
                        }),
                        BX.create('DIV', {
                            props: {
                                className: 'alert_quantity',
                            },
                            dataset: {
                                id: product.ID,
                            },
                        }),
                    ],
                }));

                basket_box.appendChild(BX.create('SPAN', {
                    props: {
                        className: 'btn red_button_cart btn-plus add2basket buttonToBasketOpenWindow',
                    },
                    dataset: {
                        url: product.DETAIL_PAGE_URL,
                        product_id: product.ID,
                        id: product.BUY_LINK
                    },
                    html: '<img class="image-cart" src="/local/templates/Oshisha/images/cart-white.png"/>',
                }))
            }
        }

        $(BX.findChildByClassName(box_product, 'inputBasketOpenWindow')).attr(attr_val, match);
        $(BX.findChildByClassName(box_product, 'addToBasketOpenWindow')).attr(attr_val, match)
        $(BX.findChildByClassName(box_product, 'removeToBasketOpenWindow')).attr(attr_val, match)
        $(BX.findChildByClassName(box_product, 'buttonToBasketOpenWindow')).attr(attr_val, match)
        //  PROPS
        if (Array.isArray(product.ADVANTAGES_PRODUCT) !== false) {

            let props_box = BX.findChildByClassName(box_product, 'props-box-child-advanse');
            $.each(product.ADVANTAGES_PRODUCT, function (k, prop) {

                let child = BX.findChildrenByClassName(props_box, 'child').length;

                if (child < 3) {
                    props_box.appendChild(BX.create('P', {
                        props: {
                            className: 'mb-2 font-14 font-weight-500 child',
                        },
                        html: prop
                    }));
                }
            });
        }

        if (product.POPUP_PROPS.length > 0) {
            let props_popup = BX.findChildByClassName(box_product, 'props-box-child-popup');
            $.each(product.POPUP_PROPS, function (k, prop) {

                let child = BX.findChildrenByClassName(props_popup, 'child').length;

                if (child < 3) {
                    props_popup.appendChild(BX.create('P', {
                        props: {
                            className: 'mb-2 font-14 font-weight-500 child',
                        },
                        html: prop.NAME + ' : ' + ' ' + prop.VALUE
                    }));
                }
            });
        }

        $(wrapper).append(box_popup);
        // TODO - не нашла как добавлять через атрибуты BX при создании элемента тег дата с дефисом, можно только с подчеркиванием
        $(wrapper).find('.like-modal').attr('data-product-id', product.ID).attr('data-fuser-id', product.LIKE.F_USER_ID);
        if (parseInt(product.LIKE.COUNT_FAV) !== 0) {
            $(wrapper).find('.like-modal a[data-method="favorite"]').attr('data-fav-controls', 'true').attr('style', 'color:red');
        }
        if (parseInt(product.LIKE.COUNT_LIKE) !== 0) {
            $(wrapper).find('.like-modal a[data-method="like"]').attr('data-like-controls', 'true').attr('style', 'color:red');
        }
    }
});

$(document).on('click', '.close-box', function () {
    $(this).closest('.box-popup-product').remove();
});
$(document).on('click', '.close-box-price', function () {
    $(this).closest('.info-prices-box-hover').removeClass('d-flex').addClass('d-none');
});

