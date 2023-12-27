import OrderMain from "./components/order_page/OrderMain";
import {createRoot} from 'react-dom/client';
import React from "react";

BX.namespace('BX.OrderPageComponents');

BX.OrderPageComponents = {
    result: null,
    params: null,
    locations: null,
    options: {},
    propertyValidation: null,
    showWarnings: null,
    OrderGeneralUserPropsBlock: null,
    OrderMainBlockId: null,
    OrderMainBlock: null,
    OrderMainRoot: null,

    init: function(currentDataset) {
        this.startLoader();
        this.result = JSON.parse(currentDataset.result);
        this.params = JSON.parse(currentDataset.params);
        this.locations = JSON.parse(currentDataset.locations);
        this.propertyValidation = currentDataset.propertyValidation;
        this.showWarnings = currentDataset.showWarnings;

        this.options.deliveriesPerPage = parseInt(this.params.DELIVERIES_PER_PAGE);
        this.options.paySystemsPerPage = parseInt(this.params.PAY_SYSTEMS_PER_PAGE);
        this.options.pickUpsPerPage = parseInt(this.params.PICKUPS_PER_PAGE);

        this.options.showWarnings = !!this.showWarnings;
        this.options.propertyValidation = !!this.propertyValidation;
        this.options.priceDiffWithLastTime = false;

        this.options.totalPriceChanged = false;

        this.OrderMainBlock = document.getElementById(currentDataset.orderBlockId);
        if (this.OrderMainBlock) {
            this.OrderMainRoot = createRoot(this.OrderMainBlock);
            this.OrderMainRoot.render(
                <OrderMain result={this.result} params={this.params} options={this.options}
                           locations={this.locations}
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
    }
}

BX.OrderPageComponents.init(document.currentScript.dataset);