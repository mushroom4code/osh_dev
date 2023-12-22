import React from "react";

class OrderTotal extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params,
            deliveryBlockNode: this.props.deliveryBlockNode,
            orderBlockId: this.props.orderBlockId
        }
        this.animateScrollTo = this.animateScrollTo.bind(this);
    }

    componentDidMount() {
        BX.OrderPageComponents.endLoader();
    }

    componentDidUpdate() {
        BX.OrderPageComponents.endLoader();
    }

    editTotalBlock() {
        if (!this.totalInfoBlockNode || !this.result.TOTAL)
            return;

        var total = this.result.TOTAL,
            priceHtml, params = {},
            discText, valFormatted, i,
            curDelivery, deliveryError, deliveryValue;


        BX.cleanNode(this.totalInfoBlockNode);

        if (parseFloat(total.ORDER_PRICE) === 0) {
            priceHtml = this.params.MESS_PRICE_FREE;
            params.free = true;
        } else {
            priceHtml = total.ORDER_PRICE_FORMATED;
        }

        if (this.options.showPriceWithoutDiscount) {
            priceHtml += '<br><span class="bx-price-old">' + total.PRICE_WITHOUT_DISCOUNT + '</span>';
        }
        let product = this.result.GRID.ROWS;
        let quantity = Object.keys(product).length;
        let textQuantity = '<span>Товары &nbsp(' + quantity + ')</span>';
        this.totalInfoBlockNode.appendChild(this.createTotalUnit(textQuantity, priceHtml, params));

        // if (this.options.showOrderWeight) {
        this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_WEIGHT_SUM'), total.ORDER_WEIGHT_FORMATED));
        // }

        // if (this.options.showTaxList) {
        for (i = 0; i < total.TAX_LIST.length; i++) {
            valFormatted = total.TAX_LIST[i].VALUE_MONEY_FORMATED || '';
            this.totalInfoBlockNode.appendChild(
                this.createTotalUnit(
                    total.TAX_LIST[i].NAME + (!!total.TAX_LIST[i].VALUE_FORMATED ? ' ' + total.TAX_LIST[i].VALUE_FORMATED : '') + ':',
                    valFormatted
                )
            );
        }
        // }

        params = {};
        curDelivery = this.getSelectedDelivery();
        deliveryError = curDelivery && curDelivery.CALCULATE_ERRORS && curDelivery.CALCULATE_ERRORS.length;

        if (deliveryError) {
            deliveryValue = BX.message('SOA_NOT_CALCULATED');
            params.error = deliveryError;
        } else {
            if (parseFloat(total.DELIVERY_PRICE) === 0) {
                deliveryValue = this.params.MESS_PRICE_FREE;
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

        // if (this.result.DELIVERY.length) {
        this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_DELIVERY'), deliveryValue, params));
        // }

        if (this.options.showDiscountPrice) {
            discText = this.params.MESS_ECONOMY;
            if (total.DISCOUNT_PERCENT_FORMATED && parseFloat(total.DISCOUNT_PERCENT_FORMATED) > 0)
                discText += total.DISCOUNT_PERCENT_FORMATED;

            this.totalInfoBlockNode.appendChild(this.createTotalUnit(discText + ':', total.DISCOUNT_PRICE_FORMATED, {highlighted: true}));
        }


        if (this.options.showPayedFromInnerBudget) {
            this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED));
            this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_PAYED'), total.PAYED_FROM_ACCOUNT_FORMATED));
            this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_LEFT_TO_PAY'), total.ORDER_TOTAL_LEFT_TO_PAY_FORMATED, {total: true}));
        } else {
            this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_IT'), total.ORDER_TOTAL_PRICE_FORMATED, {total: true}));
        }

        if (parseFloat(total.PAY_SYSTEM_PRICE) >= 0 && this.result.DELIVERY.length) {
            this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_PAYSYSTEM_PRICE'), '~' + total.PAY_SYSTEM_PRICE_FORMATTED));
        }
        if (this.result.IS_AUTHORIZED) {
            for (i = 0; i < this.result.DELIVERY.length; i++) {
                if (this.result.DELIVERY[i].CHECKED == 'Y') {
                    var checkedDelivery = this.result.DELIVERY[i];
                    break;
                }
            }
            if (!checkedDelivery?.CALCULATE_ERRORS) {
                this.totalInfoBlockNode.appendChild(
                    BX.create('DIV', {
                        props: {className: 'bx-soa-cart-total-button-container'},
                        children: [
                            BX.create('A', {
                                props: {
                                    href: 'javascript:void(0)',
                                    className: 'btn btn_basket btn-order-save dark:text-textDark shadow-md ' +
                                        ' text-white dark:bg-dark-red bg-light-red py-2 px-4 rounded-5 block text-center'
                                },
                                html: 'Зарезервировать',
                                events: {
                                    click: BX.proxy(this.clickOrderSaveAction, this)
                                }
                            })

                        ]
                    })
                );
                if (!document.querySelector('#second-save-order-js')) {
                    this.newBlockId.append(BX.create('DIV', {
                        props: {
                            id: 'second-save-order-js',
                            style: 'margin-top: 2rem;',
                            className: 'bx-soa-cart-total-button-container hidden'
                        },
                        children: [
                            BX.create('A', {
                                props: {
                                    href: 'javascript:void(0)',
                                    className: 'btn btn_basket btn-order-save block text-center'
                                },
                                html: 'Зарезервировать',
                                events: {
                                    click: BX.proxy(this.clickOrderSaveAction, this)
                                }
                            })

                        ]
                    }));
                }
            } else {
                this.totalInfoBlockNode.appendChild(
                    BX.create('span', {
                        props: {className: 'btn-primary-color'},
                        html: checkedDelivery.CALCULATE_ERRORS
                    })
                )
                if (document.querySelector('#second-save-order-js')) {
                    BX.remove(document.querySelector('#second-save-order-js'))
                }
            }
        } else {
            this.totalInfoBlockNode.appendChild(
                BX.create('span', {
                    props: {className: 'btn-primary-color'},
                    html: 'Для оформления заказа необходимо авторизоваться'
                })
            )
        }

        this.editMobileTotalBlock();
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

    createTotalUnit(name, value, params) {
        var totalValue, totalUnit = [], className = 'bx-soa-cart-total-line leading-[35px] overflow-hidden';
        name = name || '';
        value = value || '';
        params = params || {};

        if (params.error) {
            totalValue = (<a className="bx-soa-price-not-calc" dangerouslySetInnerHTML={{__html: value}} onClick={this.animateScrollTo}></a>);
            totalValue = [BX.create('A', {
                props: {className: 'bx-soa-price-not-calc'},
                html: value,
                events: {
                    click: BX.delegate(function () {
                        this.animateScrollTo(this.state.deliveryBlockNode);
                    }, this)
                }
            })];
        } else if (params.free) {
            totalValue = [BX.create('SPAN', {
                props: {className: 'bx-soa-price-free'},
                html: value
            })];
        } else {
            totalValue = [value];
        }
        if (params.total) {
            className += ' bx-soa-cart-total-line-total mt-2.5 border-t-[1px] border-grey-line-order pt-[25px] mb-[13px]';
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

        return BX.create('DIV', {
            props: {className: className},
            children: [
                BX.create('SPAN', {props: {className: 'bx-soa-cart-t float-left' + (params.total ? ' font-bold' : '')}, html: name}),
                BX.create('SPAN', {
                    props: {
                        className: 'bx-soa-cart-d float-right' + (params.total ? ' font-bold' : '') + (!!params.total && this.options.totalPriceChanged ? ' bx-soa-changeCostSign' : '')
                    },
                    children: totalValue
                })
            ]
        });
    }


    render() {
        if (!this.state.result.TOTAL)
            return;

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
        let textQuantity = '<span>Товары &nbsp(' + quantity + ')</span>';
        this.totalInfoBlockNode.appendChild(this.createTotalUnit(textQuantity, priceHtml, params));

        // if (this.options.showOrderWeight) {
        this.totalInfoBlockNode.appendChild(this.createTotalUnit(BX.message('SOA_SUM_WEIGHT_SUM'), total.ORDER_WEIGHT_FORMATED));
        // }


        const renderProperties = () => {
            let div = [];
            let group, property,
                propsIterator,
                groupIterator = new BX.Sale.PropertyCollection(
                    BX.merge({publicMode: true}, this.state.result.ORDER_PROP)
                ).getGroupIterator();
            let a = [];
            while(group = groupIterator()) {
                propsIterator = group.getIterator();
                while (property = propsIterator()) {
                    // TODO Enterego pickup
                    let disabled = false;
                    if (this.state.group_buyer_props.find(item => item === group.getName()) !== undefined) {
                        a.push(property.getId());
                        div.push(
                            <OrderProp key={property.getId()} property={property} locations={this.state.locations}
                                       disabled={disabled} result={this.state.result}
                                       are_locations_prepared={this.state.are_locations_prepared}/>
                        );
                    }
                }
            }
            return div;
        }

        return(<div className="row">
            <div className="grid grid-cols-2 gap-x-2 bx-soa-customer p-0">
                {renderProperties()}
            </div>
        </div>);
    }
}

export default OrderTotal;