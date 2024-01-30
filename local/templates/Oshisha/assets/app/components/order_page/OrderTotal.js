import React, {useContext} from "react";
import OrderContext from "./Context/OrderContext";

function OrderTotal() {
    const {
        result, params, options, OrderGeneralUserPropsBlockId, sendRequest, isValidForm,
        isOrderSaveAllowed, allowOrderSave
    } = useContext(OrderContext);
    var orderSaveAllowed = false;

    const getResultJsx = () => {
        var resultJsx = [];
        if (!result.TOTAL)
            return resultJsx;

        var total = result.TOTAL,
            priceHtml, params = {},
            discText, valFormatted, i,
            curDelivery, deliveryError, deliveryValue;


        if (parseFloat(total.ORDER_PRICE) === 0) {
            priceHtml = params.MESS_PRICE_FREE;
            params.free = true;
        } else {
            priceHtml = total.ORDER_PRICE_FORMATED;
        }

        if (options.showPriceWithoutDiscount) {
            priceHtml += '<br><span class="bx-price-old">' + total.PRICE_WITHOUT_DISCOUNT + '</span>';
        }
        let product = result.GRID.ROWS;
        let quantity = Object.keys(product).length;
        let textQuantity = 'Товары (' + quantity + ')';

        resultJsx.push(createTotalUnit(textQuantity, priceHtml, params, 'prod_quantity'));
        resultJsx.push(createTotalUnit(BX.message('SOA_SUM_WEIGHT_SUM'), total.ORDER_WEIGHT_FORMATED,
            [], 'sum_weight'));

        for (i = 0; i < total.TAX_LIST.length; i++) {
            valFormatted = total.TAX_LIST[i].VALUE_MONEY_FORMATED || '';
            resultJsx.push(
                createTotalUnit(total.TAX_LIST[i].NAME +
                    (!!total.TAX_LIST[i].VALUE_FORMATED ? ' ' + total.TAX_LIST[i].VALUE_FORMATED : '') + ':',
                    valFormatted,
                    [],
                    'tax_' + i)
            );
        }

        params = {};
        curDelivery = getSelectedDelivery();
        deliveryError = curDelivery && curDelivery.CALCULATE_ERRORS && curDelivery.CALCULATE_ERRORS.length;

        if (deliveryError) {
            deliveryValue = BX.message('SOA_NOT_CALCULATED');
            params.error = deliveryError;
        } else {
            if (parseFloat(total.DELIVERY_PRICE) === 0) {
                deliveryValue = params.MESS_PRICE_FREE;
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

        resultJsx.push(createTotalUnit(BX.message('SOA_SUM_DELIVERY'), deliveryValue,
            params, 'sum_delivery'));

        if (options.showDiscountPrice) {
            discText = params.MESS_ECONOMY;
            if (total.DISCOUNT_PERCENT_FORMATED && parseFloat(total.DISCOUNT_PERCENT_FORMATED) > 0)
                discText += total.DISCOUNT_PERCENT_FORMATED;

            resultJsx.push(createTotalUnit(discText + ':', total.DISCOUNT_PRICE_FORMATED,
                {highlighted: true}, 'discount_price'));
        }

        if (options.showPayedFromInnerBudget) {
            resultJsx.push(createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED),
                [], 'total_price_formated');
            resultJsx.push(createTotalUnit(BX.message('SOA_SUM_PAYED'), total.PAYED_FROM_ACCOUNT_FORMATED),
                [], 'payed_from_account_formated');
            resultJsx.push(createTotalUnit(BX.message('SOA_SUM_LEFT_TO_PAY'),
                total.ORDER_TOTAL_LEFT_TO_PAY_FORMATED, {total: true}, 'total_left_to_pay_formated'));
        } else {
            resultJsx.push(createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED,
                {total: true}, 'total_price_formated'));
        }

        if (parseFloat(total.PAY_SYSTEM_PRICE) >= 0 && result.DELIVERY.length) {
            resultJsx.push(createTotalUnit(BX.message('SOA_PAYSYSTEM_PRICE'),
                '~' + total.PAY_SYSTEM_PRICE_FORMATTED), [], 'paysystems_price_formated');
        }
        if (result.IS_AUTHORIZED) {
            for (i = 0; i < result.DELIVERY.length; i++) {
                if (result.DELIVERY[i].CHECKED === 'Y') {
                    var checkedDelivery = result.DELIVERY[i];
                    break;
                }
            }
            if (!checkedDelivery?.CALCULATE_ERRORS) {
                resultJsx.push(
                    <div key={'total_action'} className="bx-soa-cart-total-button-container">
                        <a className="btn btn_basket mt-3 btn-order-save block shadow-md text-white w-full font-normal
                        dark:font-light text-sm dark:bg-dark-red bg-light-red py-3 px-4 rounded-5 text-center"
                           onClick={clickOrderSaveAction}>
                            Зарезервировать
                        </a>
                    </div>
                );
            } else {
                resultJsx.push(
                    <span key={'total_action'} className="btn-primary-color text-hover-red text-sm font-medium my-2">
                        {checkedDelivery.CALCULATE_ERRORS}
                    </span>
                );
            }
        } else {
            resultJsx.push(
                <span key={'total_action'} className="btn-primary-color text-hover-red font-medium text-sm my-2">
                    Для оформления заказа необходимо авторизоваться
                </span>
            );
        }
        return resultJsx;
    }

    const createTotalUnit = (name = '', value = '', params = {}, line) => {
        let totalValue, className = 'bx-soa-cart-total-line mb-4' +
            ' overflow-hidden flex justify-between text_filter_basket text-md text-textLight' +
            ' dark:text-textDarkLightGray font-normal dark:font-light';

        if (params.error) {
            totalValue = <a className="bx-soa-price-not-calc font-semibold"
                            dangerouslySetInnerHTML={{__html: value}}
                            onClick={BX.OrderPageComponents.animateScrollTo}></a>;
        } else if (params.free) {
            totalValue = <span className={'bx-soa-price-free' + (params.total ? 'font-bold' : '')}>{value}</span>;
        } else {
            totalValue = <span className={params.total ? 'font-semibold' : ''}
                               dangerouslySetInnerHTML={{__html: value}}></span>;
        }
        if (params.total) {
            className += ' bx-soa-cart-total-line-total border-t pt-4 border-borderColor dark:border-gray-slider-arrow  ' +
                ' text_filter_basket text-md text-textLight dark:text-white font-semibold dark:font-medium my-6';
        }

        if (name === 'Итого:') {
            name = 'Общая стоимость';
        }

        return (
            name !== 'НДС (20%, включен в цену):' ?
                <div key={'cart_total_line_' + line} className={className}>
                    <span className={'bx-soa-cart-t text-textLight ' +
                        ' dark:text-textDarkLightGray font-normal dark:font-light '
                        + (params.total ? ' font-semibold dark:font-semibold' : '')}>{name}</span>
                    <span className={'bx-soa-cart-d text-textLight' +
                        ' dark:text-textDarkLightGray font-normal dark:font-light '
                        + (!!params.total && options.totalPriceChanged ? ' bx-soa-changeCostSign' : '')}>
                    {totalValue}
                </span>
                </div>
                : <></>
        );
    }

    const getSelectedDelivery = () => {
        let currentDelivery = false,
            i = 0;

        for (i in result.DELIVERY) {
            if (result.DELIVERY[i]['CHECKED']) {
                currentDelivery = result.DELIVERY[i];
                break;
            }
        }

        return currentDelivery;
    }

    const click_edit = () => {
        let select_block =
            document.querySelector('#' + OrderGeneralUserPropsBlockId + ' .bx-soa-section-title-container');
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

    const clickOrderSaveAction = (event) => {
        event.preventDefault();
        if (result.IS_AUTHORIZED) {
            click_edit();
        }
        if (isValidForm()) {
            allowOrderSave();
            if (params.USER_CONSENT === 'Y' && BX.UserConsent) {
                BX.onCustomEvent('bx-soa-order-save', []);
            } else {
                doSaveAction();
            }
        }

        return BX.PreventDefault(event);
    }

    const doSaveAction = () => {
        if (isOrderSaveAllowed()) {
            sendRequest('saveOrderAjax', []);
        }
    }


    return (
        <>
            <h5 className="order_text lg:block hidden mb-4 text-[22px] font-semibold dark:font-normal">
                Оформление заказа
            </h5>
            <div className="flex items-center flex-row justify-between lg:mb-3 mb-7">
                <span className="text-10 m-0 font-medium dark:font-normal leading-6">
                <span className="confidintial bg-light-red p-1.5 mr-2 whitespace-nowrap text-white font-medium text-10
                 rounded-full text-center dark:bg-dark-red">18+</span>
                    При получении заказа, возможно, потребуется предъявить документ, подтверждающий ваш возраст.
                </span>
            </div>
            <div id="bx-soa-total" className="mb-5 bx-soa-sidebar">
                <div className="bx-soa-cart-total-ghost"></div>
                <div className="bx-soa-cart-total p-6 rounded-xl bg-textDark dark:bg-darkBox mb-7">
                    {getResultJsx()}
                </div>
            </div>
        </>
    );

}

export default OrderTotal;