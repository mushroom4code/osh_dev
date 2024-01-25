import {createContext, useState, useEffect, useRef} from "react";
import axios from 'axios';

const OrderContext = createContext();

export const OrderContextProvider = (props) => {
    const [result, setResult] = useState(props.result);
    const [params, setParams] = useState(props.params);
    const [options, setOptions] = useState(props.options);
    const [orderSaveAllowed, setOrderSaveAllowed] = useState(true);
    const [locations, setLocations] = useState(props.locations);
    const [locationProperty, setLocationProperty] = useState(props.result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION'));
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
                animateScrollTo(properties_error_node);
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

        let requestData = getData(action, actionData)
        requestData.order = {...requestData.order, ...additionalData}
        console.log(requestData);
        console.log(result);

        if (action === 'saveOrderAjax') {
            form = BX('bx-soa-order-form');
            if (form)
                form.querySelector('input[type=hidden][name=sessid]').value = BX.bitrix_sessid();

            BX.ajax.submit(BX('bx-soa-order-form'), saveOrder);
        } else {
            axios.post(
                props.ajaxUrl,
                requestData,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            ).then(response => {
                    if (response.data.redirect && response.data.redirect.length)
                        document.location.href = response.data.redirect;
                    switch (eventArgs.action) {
                        case 'refreshOrderAjax':
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

    const getData = (action, actionData) => {
        var data = {
            order: getAllFormData(),
            sessid: BX.bitrix_sessid(),
            via_ajax: 'Y',
            SITE_ID: BX.OrderPageComponents.siteId,
            signedParamsString: BX.OrderPageComponents.signedParamsString
        };

        data[params.ACTION_VARIABLE] = action;

        if (action === 'enterCoupon' || action === 'removeCoupon')
            data.coupon = actionData;

        return data;
    }

    const getAllFormData = () => {
        var form = BX('bx-soa-order-form'),
            prepared = BX.ajax.prepareForm(form),
            i;

        for (i in prepared.data) {
            if (prepared.data.hasOwnProperty(i) && i == '') {
                delete prepared.data[i];
            }
        }

        return !!prepared && prepared.data ? prepared.data : {};
    }

    const refreshOrder = (result) => {
        if (result.error) {
            this.showError(this.mainErrorsNode, result.error);
            this.animateScrollTo(this.mainErrorsNode, 800, 20);
        } else {
            setResult(result.order);
            setLocations(result.locations);
            // this.initOptions();
        }
        return true;
    }

    const animateScrollTo = (node, duration, shiftToTop) => {
        if (!node)
            return;

        var scrollTop = BX.GetWindowScrollPos().scrollTop,
            orderBlockPos = BX.pos(orderBlockNode),
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

                    (window.b24order = window.b24order || []).push({id: result.ID, sum: result.TOTAL.ORDER_PRICE});

                    redirected = true;
                    location.href = submitResult.REDIRECT_URL;
                } else {
                    this.showErrors(result.ERROR, true, true);
                }
            }

            if (!redirected) {
                this.handleNotRedirected();
            }
            BX.OrderPageComponents.endLoader();
    }

    const handleNotRedirected = () => {
        disallowOrderSave();
        BX.OrderPageComponents.endLoader();
    }
    const disallowOrderSave = () => {
        setOrderSaveAllowed(false);
    }

    return <OrderContext.Provider value={{
        result, setResult, params, setParams, options, setOptions, locations,
        setLocations, locationProperty, setLocationProperty, OrderGeneralUserPropsBlockId,
        setOrderGeneralUserPropsBlockId, sendRequest, animateScrollTo
    }}>
        {props.children}
    </OrderContext.Provider>
}

OrderContextProvider.propTypes = {
    // children: PropTypes.node,
    // filesList: PropTypes.array
}
export default OrderContext