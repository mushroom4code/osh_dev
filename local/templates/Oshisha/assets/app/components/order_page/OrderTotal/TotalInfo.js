import React from "react";

function TotalInfo({result, params, options, getSelectedDelivery}) {

    const total = result.TOTAL;
    const className = 'bx-soa-cart-total-line mb-4 overflow-hidden flex justify-between text_filter_basket text-md ' +
        'text-textLight dark:text-textDarkLightGray font-normal dark:font-light';

    let priceHtml = total.ORDER_PRICE_FORMATED;
    let discText = params.MESS_ECONOMY;

    if (parseFloat(total.ORDER_PRICE) === 0) {
        priceHtml = <span className={'bx-soa-price-free'}>{params.MESS_PRICE_FREE}</span>
    }

    if (total.DISCOUNT_PERCENT_FORMATED && parseFloat(total.DISCOUNT_PERCENT_FORMATED) > 0)
        discText += total.DISCOUNT_PERCENT_FORMATED;


    const arData = [
        {
            key: 'cart_total_line_prod_quantity',
            name: 'Товары (' + Object.keys(result.GRID.ROWS).length + ')',
            className: className,
            value: priceHtml,
            html: true,
            total: false,
            show: true
        },
        {
            key: 'sum_weight',
            name: BX.message('SOA_SUM_WEIGHT_SUM'),
            className: className,
            value: total.ORDER_WEIGHT_FORMATED,
            html: true,
            total: false,
            show: true
        },
        {
            key: 'sum_delivery',
            name: BX.message('SOA_SUM_DELIVERY'),
            className: className,
            value: getDeliveryValue(total, result, params, getSelectedDelivery),
            html: false,
            total: false,
            show: true
        },
        {
            key: 'discount_price',
            name: discText,
            className: className,
            value: total.DISCOUNT_PRICE_FORMATED,
            html: true,
            total: false,
            show: options.showDiscountPrice
        },
        {
            key: 'total_price_formated',
            name: BX.message('SOA_SUM_IT'),
            className: "bx-soa-cart-total-line-total border-t pt-4 border-borderColor dark:border-gray-slider-arrow " +
                "text_filter_basket text-md text-textLight dark:text-white text_filter_basket my-6" +
                " bx-soa-cart-total-line overflow-hidden flex justify-between text-md",
            value: total.ORDER_TOTAL_PRICE_FORMATED,
            html: true,
            total: true,
            show: true
        },
        {
            key: 'paysystems_price_formated',
            name: BX.message('SOA_PAYSYSTEM_PRICE'),
            className: className,
            value: '~' + total.PAY_SYSTEM_PRICE_FORMATED,
            html: true,
            total: true,
            show: parseFloat(total.PAY_SYSTEM_PRICE) >= 0 && result.DELIVERY.length
        }]


    return (
        arData.length > 0 ?
            arData.map((item) => {
                return (
                    item.show ?
                        <div key={item.key} className={item.className}>
                            <span
                                className={`bx-soa-cart-t  text-textLight dark:text-textDarkLightGray 
                                ${item.total ? ' font-medium dark:font-medium' : ' font-normal dark:font-light'}`}>
                                {item.name}
                            </span>
                            {item.html ?
                                <span dangerouslySetInnerHTML={{__html: item.value}}
                                      className={`bx-soa-cart-d text-textLight dark:text-textDarkLightGray 
                                  ${item.total ? ' font-medium dark:font-medium' : ' font-normal dark:font-light'}`}></span>
                                : <span
                                    className={'bx-soa-cart-d text-textLight dark:text-textDarkLightGray font-normal dark:font-light'}>
                                {item.value}
                                </span>
                            }
                        </div>
                        : false
                )
            })
            : false
    )
}

const getDeliveryValue = (total, result, params, getSelectedDelivery) => {
    const curDelivery = getSelectedDelivery(result);
    let deliveryValue = total.DELIVERY_PRICE_FORMATED;
    let deliveryError = curDelivery && curDelivery.CALCULATE_ERRORS && curDelivery.CALCULATE_ERRORS.length;

    if (deliveryError) {
        deliveryValue = <a className="bx-soa-price-not-calc text-hover-red font-semibold"
                           dangerouslySetInnerHTML={{__html: BX.message('SOA_NOT_CALCULATED')}}></a>;
    } else {
        if (parseFloat(total.DELIVERY_PRICE) === 0) {
            deliveryValue = <span className={'bx-soa-price-free'}
                                  dangerouslySetInnerHTML={{__html: params.MESS_PRICE_FREE}}></span>
        }

        if (curDelivery && typeof curDelivery.DELIVERY_DISCOUNT_PRICE !== 'undefined'
            && parseFloat(curDelivery.PRICE) > parseFloat(curDelivery.DELIVERY_DISCOUNT_PRICE)) {
            deliveryValue += '<span class="line-through text-tagFilterGray dark:text-borderColor text-sm ml-2" ' +
                'dangerouslySetInnerHTML={{__html: curDelivery.PRICE_FORMATED}}></span>';
        }
    }

    return deliveryValue;
}

export default TotalInfo;