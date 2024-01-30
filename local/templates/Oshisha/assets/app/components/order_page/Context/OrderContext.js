import { createContext, useState, useEffect, useRef } from "react";
import axios from 'axios';
import PropTypes from 'prop-types'

const OrderContext = createContext();

export const OrderContextProvider = (props) => {

    const [result, setResult] = useState(props.result);
    const [params, setParams] = useState(props.params);
    const [options, setOptions] = useState(props.options);
    const [orderSaveAllowed, setOrderSaveAllowed] = useState(true);
    const [locations, setLocations] = useState(props.locations);
    const [contrAgents, setContrAgents] = useState(props.contrAgents);
    const [locationProperty, setLocationProperty] = useState(
        props.result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')
    );
    const [OrderGeneralUserPropsBlockId, setOrderGeneralUserPropsBlockId] =
        useState(props.OrderGeneralUserPropsBlockId);

    const mounted = useRef();

    useEffect(() => {
        if (!mounted.current) {
            mounted.current = true;
        } else {
            BX.saleOrderAjax && BX.saleOrderAjax.initDeferredControl();
        }

        BX.OrderPageComponents.endLoader();
    });

    const sendRequest = (action, actionData, additionalData = {}) => {
        var form;
        if ((document.querySelector('input[name="USER_RULES"]').checked === false
            || document.querySelector('input[name="USER_POLITICS"]').checked === false)
            && action === 'saveOrderAjax') {
            BX.OrderPageComponents.endLoader();
            var properties_error_node = document.querySelector('#bx-soa-properties')
                .querySelector('.alert.alert-danger');
            properties_error_node.removeAttribute('style');
            properties_error_node.textContent = 'Примите условия соглашений в конце страницы';
            BX.OrderPageComponents.animateScrollTo(properties_error_node);
        }

        if (!BX.OrderPageComponents.startLoader()) {
            return;
        }

        // this.firstLoad = false;

        action = action ? action : 'refreshOrderAjax';

        var eventArgs = {
            action: action,
            actionData: actionData,
            cancel: false
        };

        BX.Event.EventEmitter.emit('BX.Sale.OrderAjaxComponent:onBeforeSendRequest', eventArgs);
        if (eventArgs.cancel) {
            BX.OrderPageComponents.endLoader();
            return;
        }

        let requestData = BX.OrderPageComponents.getData(action, actionData)
        requestData.order = { ...requestData.order, ...additionalData }


        if (action === 'saveOrderAjax') {
            form = BX('bx-soa-order-form');
            if (form)
                form.querySelector('input[type=hidden][name=sessid]').value = BX.bitrix_sessid();

            BX.ajax.submit(BX('bx-soa-order-form'), saveOrder);
        } else {
            axios.post(
                props.ajaxUrl,
                requestData,
                { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
            ).then(response => {
                if (response.data.redirect && response.data.redirect.length)
                    document.location.href = response.data.redirect;
                switch (eventArgs.action) {
                    case 'refreshOrderAjax':
                        console.log(response.data)
                        refreshOrder(response.data);
                        break;
                    case 'confirmSmsCode':
                    case 'showAuthForm':
                        // this.firstLoad = true;
                        refreshOrder(response.data);
                        break;
                    case 'enterCoupon':
                        if (response.data && response.data.order) {
                            this.deliveryCachedInfo = [];
                            this.refreshOrder(response.data);
                        } else {
                            this.addCoupon(response.data);
                        }
                        break;
                    case 'removeCoupon':
                        if (response.data && response.data.order) {
                            // this.deliveryCachedInfo = [];
                            this.refreshOrder(response.data);
                        } else {
                            this.removeCoupon(response.data);
                        }

                        break;
                }
                // BX.cleanNode(this.savedFilesBlockNode);
                BX.OrderPageComponents.endLoader();
            }
            );
        }
    }

    const refreshOrder = (result) => {
        if (result.error) {
            var mainErrorsNode = document.getElementById(BX.OrderPageComponents.OrderMainErrorsBlockId);
            //BX.OrderPageComponents.showError(mainErrorsNode, blockErrors);
            //BX.OrderPageComponents.animateScrollTo(mainErrorsNode, 800, 20);
        } else {
            setResult(result.order);
            setLocations(result.locations);
            initOptions();
        }
        return true;
    }

    const initOptions = () => {
        var total, newOptions;

        if (result.TOTAL) {
            total = result.TOTAL;
            newOptions = options;
            newOptions.showOrderWeight = total.ORDER_WEIGHT && parseFloat(total.ORDER_WEIGHT) > 0;
            newOptions.showPriceWithoutDiscount = parseFloat(total.ORDER_PRICE) < parseFloat(total.PRICE_WITHOUT_DISCOUNT_VALUE);
            newOptions.showDiscountPrice = total.DISCOUNT_PRICE && parseFloat(total.DISCOUNT_PRICE) > 0;
            newOptions.showTaxList = total.TAX_LIST && total.TAX_LIST.length;
            newOptions.showPayedFromInnerBudget = total.PAYED_FROM_ACCOUNT_FORMATED && total.PAYED_FROM_ACCOUNT_FORMATED.length;
            setOptions(newOptions);
        }
    }

    const saveOrder = (submitResult) => {
        //enterego - on iphone for cityid add <a href:''> -
        submitResult = submitResult.replace(/<a.+a>/, '');
        //
        var res = BX.parseJSON(submitResult), redirected = false;
        if (res && res.order) {
            submitResult = res.order;

            if (submitResult.REDIRECT_URL) {
                if (params.USE_ENHANCED_ECOMMERCE === 'Y') {
                    setAnalyticsDataLayer('purchase', submitResult.ID);
                }

                (window.b24order = window.b24order || []).push({ id: result.ID, sum: result.TOTAL.ORDER_PRICE });

                redirected = true;
                location.href = submitResult.REDIRECT_URL;
            } else {
                BX.OrderPageComponents.showErrors(submitResult.ERROR, true, true);
            }
        }

        if (!redirected) {
            handleNotRedirected();
        }
        BX.OrderPageComponents.endLoader();
    }

    const handleNotRedirected = () => {
        disallowOrderSave();
        BX.OrderPageComponents.endLoader();
    }

    const isOrderSaveAllowed = () => {
        return orderSaveAllowed === true;
    }

    const allowOrderSave = () => {
        setOrderSaveAllowed(true);
    }
    const disallowOrderSave = () => {
        setOrderSaveAllowed(false);
    }

    const isValidForm = () => {
        if (!options.propertyValidation)
            return true;

        var propsErrors = isValidPropertiesBlock();

        if (propsErrors.length) {
            var propsBlockNode = document.getElementById(BX.OrderPageComponents.OrderPropertiesBlockId);
            BX.OrderPageComponents.showError(propsBlockNode, propsErrors);
            BX.OrderPageComponents.animateScrollTo(propsBlockNode, 800, 20);
        }

        return !(propsErrors.length);
    }

    const isValidPropertiesBlock = (excludeLocation) => {
        if (!options.propertyValidation)
            return [];

        return [];
    }

    return <OrderContext.Provider value={{
        result, setResult, params, setParams, options, setOptions, locations,
        setLocations, locationProperty, contrAgents, setContrAgents, setLocationProperty, OrderGeneralUserPropsBlockId,
        setOrderGeneralUserPropsBlockId, sendRequest, isValidForm, isOrderSaveAllowed, allowOrderSave
    }}>
        {props.children}
    </OrderContext.Provider>
}

OrderContextProvider.propTypes = {
    result: PropTypes.shape({
        DELIVERY: PropTypes.object,
        ORDER_PROP: PropTypes.shape({
            groups: PropTypes.array,
            properties: PropTypes.arrayOf(PropTypes.shape({
                ID: PropTypes.string
            }))
        })

    })
    // children: PropTypes.node,
    // filesList: PropTypes.array
}
export default OrderContext