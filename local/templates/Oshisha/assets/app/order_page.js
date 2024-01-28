import OrderMain from "./components/order_page/OrderMain";
import {createRoot} from 'react-dom/client';
import React from "react";

BX.namespace('BX.OrderPageComponents');

BX.OrderPageComponents = {
    ajaxUrl: null,
    result: null,
    params: null,
    locations: null,
    signedParamsString: null,
    siteId: null,
    options: {},
    propertyValidation: null,
    showWarnings: null,
    OrderGeneralUserPropsBlock: null,
    OrderMainBlockId: null,
    OrderMainBlock: null,
    OrderMainRoot: null,
    OrderMainErrorsBlockId: null,
    OrderDeliveryBlockId: null,
    OrderPropertiesBlockId: null,
    OrderPaysystemsBlockId: null,

    init: function(currentDataset) {
        this.startLoader();
        this.ajaxUrl = currentDataset.ajaxUrl;
        this.result = JSON.parse(currentDataset.result);
        this.params = JSON.parse(currentDataset.params);
        this.locations = JSON.parse(currentDataset.locations);
        this.propertyValidation = currentDataset.propertyValidation;
        this.showWarnings = currentDataset.showWarnings;
        this.signedParamsString = currentDataset.signedParamsString || '';
        this.siteId = currentDataset.siteId || '';

        this.options.deliveriesPerPage = parseInt(this.params.DELIVERIES_PER_PAGE);
        this.options.paySystemsPerPage = parseInt(this.params.PAY_SYSTEMS_PER_PAGE);
        this.options.pickUpsPerPage = parseInt(this.params.PICKUPS_PER_PAGE);

        this.options.showWarnings = !!this.showWarnings;
        this.options.propertyValidation = !!this.propertyValidation;
        this.options.priceDiffWithLastTime = false;

        this.options.totalPriceChanged = false;

        this.OrderMainErrorsBlockId = currentDataset.mainErrorsBlockId;
        this.OrderDeliveryBlockId = currentDataset.deliveryBlockId;
        this.OrderPropertiesBlockId = currentDataset.userPropsBlockId;
        this.OrderPaysystemsBlockId = currentDataset.paysystemsBlockId;

        this.OrderMainBlock = document.getElementById(currentDataset.orderBlockId);
        if (this.OrderMainBlock) {
            this.OrderMainRoot = createRoot(this.OrderMainBlock);
            this.OrderMainRoot.render(
                <OrderMain result={this.result} params={this.params} options={this.options}
                           locations={this.locations} ajaxUrl={this.ajaxUrl}
                           OrderGeneralUserPropsBlockId={currentDataset.generalUserPropsBlockId}
                />
            );
        }
    },

    startLoader: function () {
        if (this.BXFormPosting === true)
            return false;

        this.BXFormPosting = true;

        if (!this.loadingScreen) {
            this.loadingScreen = new BX.PopupWindow('loading_screen', null, {
                overlay: {backgroundColor: 'black', opacity: 1},
                events: {
                    onAfterPopupShow: BX.delegate(function () {
                        BX.cleanNode(this.loadingScreen.popupContainer);
                        BX.removeClass(this.loadingScreen.popupContainer, 'popup-window');
                        loaderForSite('appendLoader',this.loadingScreen.popupContainer)
                        this.loadingScreen.popupContainer.removeAttribute('style');
                        this.loadingScreen.popupContainer.style.display = 'block';
                    }, this)
                }
            });
            BX.addClass(this.loadingScreen.overlay.element, 'bx-step-opacity');
        }

        this.loadingScreen.overlay.element.style.opacity = '0';
        this.loadingScreen.show();
        this.loadingScreen.overlay.element.style.opacity = '0.6';

        return true;
    },

    endLoader: function () {
        this.BXFormPosting = false;

        if (this.loadingScreen && this.loadingScreen.isShown()) {
            this.loadingScreen.close();
        }
    },

    getData: function (action, actionData)  {
        var data = {
            order: this.getAllFormData(),
            sessid: BX.bitrix_sessid(),
            via_ajax: 'Y',
            SITE_ID: this.siteId,
            signedParamsString: this.signedParamsString
        };

        data[this.params.ACTION_VARIABLE] = action;

        if (action === 'enterCoupon' || action === 'removeCoupon')
            data.coupon = actionData;

        return data;
    },

    getAllFormData: function () {
        var form = BX('bx-soa-order-form'),
            prepared = BX.ajax.prepareForm(form),
            i;

        for (i in prepared.data) {
            if (prepared.data.hasOwnProperty(i) && i == '') {
                delete prepared.data[i];
            }
        }

        return !!prepared && prepared.data ? prepared.data : {};
    },

    animateScrollTo: function (node, duration, shiftToTop) {
        if (!node)
            return;

        var scrollTop = BX.GetWindowScrollPos().scrollTop,
            orderBlockPos = BX.pos(this.OrderMainBlock),
            ghostTop = BX.pos(node).top - (BX.browser.IsMobile() ? 50 : 0);

        if (shiftToTop)
            ghostTop -= parseInt(shiftToTop);

        if (ghostTop + window.innerHeight > orderBlockPos.bottom)
            ghostTop = orderBlockPos.bottom - window.innerHeight + 17;

        new BX.easing({
            duration: duration || 800,
            start: {scroll: scrollTop},
            finish: {scroll: ghostTop},
            transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
            step: BX.delegate(function (state) {
                window.scrollTo(0, state.scroll);
            }, this)
        }).animate();
    },

    showErrors: function (errors, scroll, showAll) {
        var errorNodes = this.OrderMainBlock.querySelectorAll('div.alert.alert-danger'),
            section, k, blockErrors;
        for (k = 0; k < errorNodes.length; k++) {
            section = BX.findParent(errorNodes[k], {className: 'bx-soa-section'});
            BX.removeClass(section, 'bx-step-error');
            errorNodes[k].style.display = 'none';
            BX.cleanNode(errorNodes[k]);
        }

        if (!errors || BX.util.object_keys(errors).length < 1)
            return;

        for (k in errors) {
            if (!errors.hasOwnProperty(k))
                continue;

            blockErrors = errors[k];

            switch (k.toUpperCase()) {
                case 'MAIN':
                    var mainErrorsNode = document.getElementById(this.OrderMainErrorsBlockId);
                    this.showError(mainErrorsNode, blockErrors);
                    this.animateScrollTo(mainErrorsNode, 800, 20);
                    break;
                case 'DELIVERY':
                    var deliveryBlockNode = document.getElementById(this.OrderDeliveryBlockId);
                    this.showError(deliveryBlockNode, blockErrors, true);
                    this.animateScrollTo(deliveryBlockNode, 800, 20);
                    break;
                case 'PAY_SYSTEM':
                    var paySystemBlockNode = document.getElementById(this.OrderPaysystemsBlockId);
                    this.showError(paySystemBlockNode, blockErrors, true);
                    this.animateScrollTo(paySystemBlockNode, 800, 20);
                    break;
                case 'PROPERTY':
                    var propsBlockNode = document.getElementById(this.OrderPropertiesBlockId);
                    this.showError(propsBlockNode, blockErrors, true);
                    this.animateScrollTo(propsBlockNode, 800, 20);
                    break;
            }
        }
    },

    showError: function (node, msg, border) {
        if (BX.type.isArray(msg))
            msg = msg.join('<br>');

        var errorContainer = node.querySelector('.alert.alert-danger'), animate;
        if (errorContainer && msg.length) {
            BX.cleanNode(errorContainer);
            errorContainer.appendChild(BX.create('DIV', {html: msg}));


            errorContainer.style.opacity = 0;
            errorContainer.style.display = '';
            new BX.easing({
                duration: 300,
                start: {opacity: 0},
                finish: {opacity: 100},
                transition: BX.easing.makeEaseOut(BX.easing.transitions.quad),
                step: function (state) {
                    errorContainer.style.opacity = state.opacity / 100;
                },
                complete: function () {
                    errorContainer.removeAttribute('style');
                }
            }).animate();

            if (!!border)
                BX.addClass(node, 'bx-step-error');
        }
    }
}

BX.OrderPageComponents.init(document.currentScript.dataset);