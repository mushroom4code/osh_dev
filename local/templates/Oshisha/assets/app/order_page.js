import OrderUserTypeCheck from "./components/order_page/OrderUserTypeCheck";
import OrderUserProps from "./components/order_page/OrderUserProps";
import OrderUserAgreements from "./components/order_page/OrderUserAgreements";
import OrderComments from "./components/order_page/OrderComments";
import {createRoot} from 'react-dom/client';
import React from "react";

BX.namespace('BX.OrderPageComponents');

BX.OrderPageComponents = {
    result: null,
    params: null,
    locations: null,
    OrderUserTypeCheckRef: null,
    OrderUserTypeCheckBlock: null,
    OrderUserTypeCheckRoot: null,
    OrderUserPropsBlock: null,
    OrderUserPropsRoot: null,
    OrderUserAgreementsBlock: null,
    OrderUserAgreementsRoot: null,
    OrderCommentsBlock: null,
    OrderCommentsRoot: null,

    init: function(currentDataset) {
        this.result = JSON.parse(currentDataset.result);
        this.params = JSON.parse(currentDataset.params);
        this.locations = JSON.parse(currentDataset.locations);

        this.OrderUserTypeCheckBlock = document.getElementById(document.currentScript.dataset.userCheckBlockId);
        if (this.OrderUserTypeCheckBlock) {
            this.OrderUserTypeCheckRef = React.createRef();
            this.OrderUserTypeCheckRoot = createRoot(this.OrderUserTypeCheckBlock);
        }

        this.OrderUserPropsBlock = document.getElementById(document.currentScript.dataset.userPropsBlockId);
        if (this.OrderUserPropsBlock) {
            this.OrderUserPropsRoot = createRoot(this.OrderUserPropsBlock);
        }

        this.OrderUserAgreementsBlock = document.getElementById(document.currentScript.dataset.userAgreementsBlockId);
        if (this.OrderUserAgreementsBlock) {
            this.OrderUserAgreementsRoot = createRoot(this.OrderUserAgreementsBlock);
        }

        this.OrderCommentsBlock = document.getElementById(document.currentScript.dataset.newBlockWithCommentId);
        if(this.OrderCommentsBlock) {
            this.OrderCommentsRoot = createRoot(this.OrderCommentsBlock);
        }

        this.renderComponents(this.result, this.locations);
    },

    renderComponents: function (result, locations, areLocationsPrepared = false) {
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

        if (this.OrderUserPropsBlock) {
            this.OrderUserPropsRoot.render(
                <OrderUserProps
                    result={this.result}
                    locations={this.locations}
                    are_locations_prepared={areLocationsPrepared}
                />
            );
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
    }
}

BX.OrderPageComponents.init(document.currentScript.dataset);