import React from "react";

class OrderTotal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params,
            options: this.props.options,
            orderSaveAllowed: false,
            propsBlockNode: this.props.propsBlockNode
        }
        this.animateScrollTo = this.animateScrollTo.bind(this);
        this.clickOrderSaveAction = this.clickOrderSaveAction.bind(this);
    }

    componentDidMount() {
        BX.saleOrderAjax && BX.saleOrderAjax.initDeferredControl();
        BX.OrderPageComponents.endLoader();
    }

    componentDidUpdate() {
        BX.OrderPageComponents.endLoader();
    }

    getResultJsx() {
        var resultJsx = [];
        if (!this.state.result.TOTAL)
            return resultJsx;

        var total = this.state.result.TOTAL,
            priceHtml, params = {},
            discText, valFormatted, i,
            curDelivery, deliveryError, deliveryValue;


        if (parseFloat(total.ORDER_PRICE) === 0) {
            priceHtml = this.state.params.MESS_PRICE_FREE;
            params.free = true;
        } else {
            priceHtml = total.ORDER_PRICE_FORMATED;
        }

        if (this.state.options.showPriceWithoutDiscount) {
            priceHtml += '<br><span class="bx-price-old">' + total.PRICE_WITHOUT_DISCOUNT + '</span>';
        }
        let product = this.state.result.GRID.ROWS;
        let quantity = Object.keys(product).length;
        let textQuantity = 'Товары (' + quantity + ')';

        resultJsx.push(this.createTotalUnit(textQuantity, priceHtml, params, 'prod_quantity'));
        resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_WEIGHT_SUM'), total.ORDER_WEIGHT_FORMATED,
            [], 'sum_weight'));

        for (i = 0; i < total.TAX_LIST.length; i++) {
            valFormatted = total.TAX_LIST[i].VALUE_MONEY_FORMATED || '';
            resultJsx.push(
                this.createTotalUnit(total.TAX_LIST[i].NAME +
                    (!!total.TAX_LIST[i].VALUE_FORMATED ? ' ' + total.TAX_LIST[i].VALUE_FORMATED : '') + ':',
                    valFormatted,
                    [],
                    'tax_' + i)
            );
        }

        params = {};
        curDelivery = this.getSelectedDelivery();
        deliveryError = curDelivery && curDelivery.CALCULATE_ERRORS && curDelivery.CALCULATE_ERRORS.length;

        if (deliveryError) {
            deliveryValue = BX.message('SOA_NOT_CALCULATED');
            params.error = deliveryError;
        } else {
            if (parseFloat(total.DELIVERY_PRICE) === 0) {
                deliveryValue = this.state.params.MESS_PRICE_FREE;
                params.free = true;
            } else {
                deliveryValue = total.DELIVERY_PRICE_FORMATED;
            }

            if (
                curDelivery && typeof curDelivery.DELIVERY_DISCOUNT_PRICE !== 'undefined'
                && parseFloat(curDelivery.PRICE) > parseFloat(curDelivery.DELIVERY_DISCOUNT_PRICE)
            ) {
                deliveryValue += '<br><span class="bx-price-old">' + curDelivery.PRICE_FORMATED + '</span>';
            }
        }

        resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_DELIVERY'), deliveryValue,
            params, 'sum_delivery'));

        if (this.state.options.showDiscountPrice) {
            discText = this.state.params.MESS_ECONOMY;
            if (total.DISCOUNT_PERCENT_FORMATED && parseFloat(total.DISCOUNT_PERCENT_FORMATED) > 0)
                discText += total.DISCOUNT_PERCENT_FORMATED;

            resultJsx.push(this.createTotalUnit(discText + ':', total.DISCOUNT_PRICE_FORMATED,
                {highlighted: true}, 'discount_price'));
        }

        if (this.state.options.showPayedFromInnerBudget) {
            resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED),
                [], 'total_price_formated');
            resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_PAYED'), total.PAYED_FROM_ACCOUNT_FORMATED),
                [], 'payed_from_account_formated');
            resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_LEFT_TO_PAY'),
                total.ORDER_TOTAL_LEFT_TO_PAY_FORMATED, {total: true}, 'total_left_to_pay_formated'));
        } else {
            resultJsx.push(this.createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED,
                {total: true}, 'total_price_formated'));
        }

        if (parseFloat(total.PAY_SYSTEM_PRICE) >= 0 && this.state.result.DELIVERY.length) {
            resultJsx.push(this.createTotalUnit(BX.message('SOA_PAYSYSTEM_PRICE'),
                '~' + total.PAY_SYSTEM_PRICE_FORMATTED), [], 'paysystems_price_formated');
        }
        if (this.state.result.IS_AUTHORIZED) {
            for (i = 0; i < this.state.result.DELIVERY.length; i++) {
                if (this.state.result.DELIVERY[i].CHECKED === 'Y') {
                    var checkedDelivery = this.state.result.DELIVERY[i];
                    break;
                }
            }
            if (!checkedDelivery?.CALCULATE_ERRORS) {
                resultJsx.push(
                    <div key={'total_action'} className="bx-soa-cart-total-button-container lg:text-[13px]
                         text-[25px]">
                        <a className="btn btn_basket btn-order-save dark:text-textDark
                           shadow-md text-white dark:bg-dark-red bg-light-red lg:py-2 py-6 px-4 rounded-5 block
                           text-center font-semibold"
                           onClick={this.clickOrderSaveAction}>
                            Зарезервировать
                        </a>
                    </div>
                );
            } else {
                resultJsx.push(
                    <span key={'total_action'} className="btn-primary-color font-semibold lg:text-[13px] text-[25px]">
                        {checkedDelivery.CALCULATE_ERRORS}
                    </span>
                );
            }
        } else {
            resultJsx.push(
                <span key={'total_action'} className="btn-primary-color font-semibold lg:text-[13px] text-[25px]">
                    Для оформления заказа необходимо авторизоваться
                </span>
            );
        }
        return resultJsx;
    }

    animateScrollTo(node, duration, shiftToTop) {
        if (!node)
            return;

        var scrollTop = BX.GetWindowScrollPos().scrollTop,
            orderBlockPos = BX.pos(this.state.orderBlockNode),
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

    createTotalUnit(name, value, params, line) {
        var totalValue, totalUnit = [], className = 'bx-soa-cart-total-line lg:text-[13px] ' +
            ' text-[21px] lg:leading-[35px] leading-[78px] overflow-hidden flex justify-between';
        name = name || '';
        value = value || '';
        params = params || {};

        if (params.error) {
            totalValue = (<a className="bx-soa-price-not-calc font-bold" dangerouslySetInnerHTML={{__html: value}} onClick={this.animateScrollTo}></a>);
        } else if (params.free) {
            totalValue = (<span className={'bx-soa-price-free' + (params.total ? 'font-bold' : '')}>{value}</span>);
        } else {
            totalValue = (<span className={params.total ? 'font-bold' : ''} dangerouslySetInnerHTML={{__html: value}}></span>);
        }
        if (params.total) {
            className += ' bx-soa-cart-total-line-total mt-2.5 border-t-[1px] border-grey-line-order lg:pt-[25px] ' +
                'pt-[18px] mb-[13px] font-bold lg:text-[13px] text-[25px]';
        }

        if (params.highlighted) {
            className += ' bx-soa-cart-total-line-highlighted';
        }
        if (name === 'НДС (20%, включен в цену):') {
            name = '';
            totalValue = ''
        }
        if (name === 'Итого:') {
            name = 'Общая стоимость';
        }

        return(
            <div key={'cart_total_line_' + line} className={className}>
                <span className={'bx-soa-cart-t' + (params.total ? ' font-bold' : '')}>{name}</span>
                <span className={'bx-soa-cart-d'
                    + (!!params.total && this.state.options.totalPriceChanged ? ' bx-soa-changeCostSign' : '')}>
                    {totalValue}
                </span>
            </div>
        );
    }

    getSelectedDelivery() {
        var currentDelivery = false,
            i = 0;

        for (i in this.state.result.DELIVERY) {
            if (this.state.result.DELIVERY[i]['CHECKED']) {
                currentDelivery = this.state.result.DELIVERY[i];
                break;
            }
        }

        return currentDelivery;
    }

    click_edit() {
        let select_block = this.state.propsBlockNode.querySelector('.bx-soa-section-title-container');
        let props = BX.findChildren(select_block, {className: 'user_select'}), i, option_company, option_contrs;
        let input_block_company = document.querySelector('input[data-name="company"]');
        let input_block_contragent = document.querySelector('input[data-name="contragent"]');
        let input_period_delivery = document.querySelector('input[data-name="TIME"]');
        let selection;
        if (input_period_delivery) {
            selection = document.querySelector('.select_period').value;
            input_period_delivery.value = selection;
        }
        for (i = 0; i < props.length; i++) {
            option_company = BX.findChildren(props[i], {className: 'company_user_order'});
            option_contrs = BX.findChildren(props[i], {className: 'contragent_user'});
            let elem_company = option_company[0];
            let elem_contrs = option_contrs[0];

            if (elem_contrs.children.length !== 0) {
                let value_option_contragent_id = elem_contrs.options[elem_contrs.selectedIndex].value;
                if (value_option_contragent_id && input_block_contragent !== null) {
                    input_block_contragent.value = value_option_contragent_id;
                }
            }
            if (elem_company.children.length !== 0) {
                let value_option_company_id = elem_company.options[elem_company.selectedIndex].value;
                if (value_option_company_id && input_block_company !== null) {
                    input_block_company.value = value_option_company_id;
                }
            }
        }
    }

    clickOrderSaveAction(event) {
        event.preventDefault();
        if (this.state.result.IS_AUTHORIZED) {
            this.click_edit();
        }
        if (BX.Sale.OrderAjaxComponent.isValidForm()) {
            this.allowOrderSave();
            if (this.state.params.USER_CONSENT === 'Y' && BX.UserConsent) {
                BX.onCustomEvent('bx-soa-order-save', []);
            } else {
                this.doSaveAction();
            }
        }

        return BX.PreventDefault(event);
    }

    doSaveAction() {
        if (this.isOrderSaveAllowed()) {
            // this.reachGoal('order');
            BX.Sale.OrderAjaxComponent.sendRequest('saveOrderAjax');
        }
    }

    isOrderSaveAllowed() {
        return this.state.orderSaveAllowed === true;
    }

    allowOrderSave() {
        this.state.orderSaveAllowed = true;
    }

    disallowOrderSave() {
        this.state.orderSaveAllowed = false;
    }


    render() {
        return (
            <>
                <h5 className="order_text lg:block hidden mb-4 text-[22px] font-semibold dark:font-normal">
                    Оформление заказа
                </h5>
                <div className="flex align-items-center justify-between lg:text-[9px] text-[21px] lg:mb-3 mb-7">
                    <p className=" m-0 mr-1 flex items-center leading-normal font-medium dark:font-normal">
                        При получении заказа, возможно, потребуется предъявить документ, подтверждающий ваш возраст.
                    </p>
                    <span className="confidintial bg-light-red lg:py-[7px] py-[9px] lg:px-2 px-3 whitespace-nowrap
                    text-white font-semibold rounded-[100px] lg:leading-[17px] leading-[41px] self-center h-fit
                    text-center">18+</span>
                </div>
                <div id="bx-soa-total" className="mb-5 bx-soa-sidebar">
                    <div className="bx-soa-cart-total-ghost"></div>
                    <div className="bx-soa-cart-total lg:p-5 p-8 border-[1px] flex flex-col rounded-lg border-solid
                         border-textDark bg-textDark">
                        {this.getResultJsx()}
                    </div>

                </div>
            </>
    );
    }
}

export default OrderTotal;