import OrderUserTypeCheck from "./components/order_page/OrderUserTypeCheck";
import OrderUserProps from "./components/order_page/OrderUserProps";
import OrderDelivery from "./components/order_page/OrderDelivery";
import OrderPaySystems from "./components/order_page/OrderPaySystems";
import OrderUserAgreements from "./components/order_page/OrderUserAgreements";
import OrderComments from "./components/order_page/OrderComments";
import OrderTotal from "./components/order_page/OrderTotal";
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
    OrderUserTypeCheckRef: null,
    OrderUserTypeCheckBlock: null,
    OrderUserTypeCheckRoot: null,
    OrderUserPropsBlock: null,
    OrderUserPropsRoot: null,
    OrderDeliveryRef: null,
    OrderDeliveryBlock: null,
    OrderDeliveryRoot: null,
    OrderPaySystemsBlock: null,
    OrderPaySystemsRef: null,
    OrderPaySystemsRoot: null,
    OrderUserAgreementsBlock: null,
    OrderUserAgreementsRoot: null,
    OrderCommentsBlock: null,
    OrderCommentsRoot: null,
    OrderTotalBlock: null,
    OrderTotalRef: null,
    OrderTotalRoot: null,
    orderBlockId: null,

    init: function(currentDataset) {
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

        this.OrderUserTypeCheckBlock = document.getElementById(currentDataset.userCheckBlockId);
        if (this.OrderUserTypeCheckBlock) {
            this.OrderUserTypeCheckRef = React.createRef();
            this.OrderUserTypeCheckRoot = createRoot(this.OrderUserTypeCheckBlock);
        }

        this.OrderUserPropsBlock = document.getElementById(currentDataset.userPropsBlockId);
        if (this.OrderUserPropsBlock) {
            this.OrderUserPropsRef = React.createRef();
            this.OrderUserPropsRoot = createRoot(this.OrderUserPropsBlock);
        }

        this.OrderDeliveryBlock = document.getElementById(currentDataset.deliveryBlockId);
        if (this.OrderDeliveryBlock) {
            this.OrderDeliveryRef = React.createRef();
            this.OrderDeliveryRoot = createRoot(this.OrderDeliveryBlock);
        }

        this.OrderPaySystemsBlock = document.getElementById(currentDataset.paysystemsBlockId);
        if (this.OrderPaySystemsBlock) {
            this.OrderPaySystemsRef = React.createRef();
            this.OrderPaySystemsRoot = createRoot(this.OrderPaySystemsBlock);
        }

        this.OrderUserAgreementsBlock = document.getElementById(currentDataset.userAgreementsBlockId);
        if (this.OrderUserAgreementsBlock) {
            this.OrderUserAgreementsRoot = createRoot(this.OrderUserAgreementsBlock);
        }

        this.OrderCommentsBlock = document.getElementById(currentDataset.newBlockWithCommentId);
        if(this.OrderCommentsBlock) {
            this.OrderCommentsRoot = createRoot(this.OrderCommentsBlock);
        }

        this.OrderTotalBlock = document.getElementById(currentDataset.totalBlockId);
        if (this.OrderTotalBlock) {
            this.OrderTotalRef = React.createRef();
            this.OrderTotalRoot = createRoot(this.OrderTotalBlock);
        }

        this.orderBlockId = currentDataset.orderBlockId;

        this.renderComponents(this.result, this.locations);
    },

    renderComponents: function (result, locations, areLocationsPrepared = false) {
        this.startLoader();
        if (this.result !== result) {
            this.result = result;
        }

        if (this.locations !== locations) {
            this.locations = locations;
        }

        if (!this.OrderUserTypeCheckRef.current) {
            this.OrderUserTypeCheckRoot.render(
                <OrderUserTypeCheck
                    ref={this.OrderUserTypeCheckRef}
                    result={this.result}
                    params={this.params}
                />
            );
        } else {
            this.OrderUserTypeCheckRef.current.setState({result: this.result, params: this.params});
        }

        if (!this.OrderUserPropsRef.current) {
            this.OrderUserPropsRoot.render(
                <OrderUserProps
                    ref={this.OrderUserPropsRef}
                    result={this.result}
                    locations={this.locations}
                    are_locations_prepared={areLocationsPrepared}
                />
            );
        } else {
            this.OrderUserPropsRef.current.setState({result: this.result, locations: this.locations,
                are_locations_prepared: areLocationsPrepared});
        }

        if (!this.OrderDeliveryRef.current) {
            this.OrderDeliveryRoot.render(
                <OrderDelivery
                    ref={this.OrderDeliveryRef}
                    domNode={this.OrderDeliveryBlock}
                    result={this.result}
                    params={this.params}
                    options={this.options}
                />
            );
        } else {
            this.OrderDeliveryRef.current.setState({result: this.result});
        }

        if (!this.OrderPaySystemsRef.current) {
            this.OrderPaySystemsRoot.render(
                <OrderPaySystems
                    ref={this.OrderPaySystemsRef}
                    domNode={this.OrderPaySystemsBlock}
                    result={this.result}
                    params={this.params}
                    options={this.options}
                />
            );
        } else {
            this.OrderPaySystemsRef.current.setState({result: this.result});
        }

        if (this.OrderUserAgreementsBlock) {
            this.OrderUserAgreementsRoot.render(
                <OrderUserAgreements/>
            );
        }

        if (this.OrderCommentsBlock) {
            this.OrderCommentsRoot.render(
                <OrderComments
                    result={this.result}
                    params={this.params}
                />
            );
        }

        if (!this.OrderTotalRef.current) {
            this.OrderTotalRoot.render(
                <OrderTotal
                    ref={this.OrderTotalRef}
                    domNode={this.OrderTotalBlock}
                    deliveryBlockNode={this.OrderDeliveryBlock}
                    result={this.result}
                    params={this.params}
                    options={this.options}
                    orderBlockId={this.orderBlockId}
                />
            );
        } else {
            this.OrderTotalRef.current.setState({result: this.result});
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