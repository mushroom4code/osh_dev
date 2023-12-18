import React from "react";
import OrderProp from "./OrderProp";

class OrderDelivery extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params,
            options: this.props.options,
            deliveryPagination: {},
            deliveryCachedInfo: [],
            lastSelectedDelivery: null,
            propertyCollection: {},
            groupDeliveryProps: ["Данные для доставки"],
            deliveryBlockNode: this.props.domNode
        }
        this.selectDelivery = this.selectDelivery.bind(this);
    }

    selectDelivery(event) {
        // var target = event.target || event.srcElement,
        //     innerPaySystemSection = this.state.paySystemBlockNode.querySelector('div.bx-soa-pp-inner-ps'),
        //     innerPaySystemCheckbox = this.state.paySystemBlockNode.querySelector('input[type=checkbox][name=PAY_CURRENT_ACCOUNT]'),
        //     fullPayFromInnerPaySystem = this.state.result.TOTAL && parseFloat(this.state.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY) === 0;
        //
        // var actionSection = BX.hasClass(target, 'bx-soa-pp-company') ? target : BX.findParent(target, {className: 'bx-soa-pp-company'}),
        //     actionInput, selectedSection;
        //
        // if (actionSection) {
        //     if (BX.hasClass(actionSection, 'bx-selected'))
        //         return BX.PreventDefault(event);
        //
        //     if (innerPaySystemCheckbox && innerPaySystemCheckbox.checked && fullPayFromInnerPaySystem) {
        //         BX.addClass(actionSection, 'bx-selected');
        //         actionInput = actionSection.querySelector('input[type=checkbox]');
        //         actionInput.checked = true;
        //         BX.removeClass(innerPaySystemSection, 'bx-selected');
        //         innerPaySystemCheckbox.checked = false;
        //     } else {
        //         selectedSection = this.state.paySystemBlockNode.querySelector('.bx-soa-pp-company.bx-selected');
        //         BX.addClass(actionSection, 'bx-selected');
        //         actionInput = actionSection.querySelector('input[type=checkbox]');
        //         actionInput.checked = true;
        //
        //         if (selectedSection) {
        //             BX.removeClass(selectedSection, 'bx-selected');
        //             selectedSection.querySelector('input[type=checkbox]').checked = false;
        //         }
        //     }
        // }
        //
        // BX.Sale.OrderAjaxComponent.sendRequest();




        var activeSection = BX.findParent(event.target, {class: "bx-soa-section"});
        var target = event.target || event.srcElement,
            actionSection = BX.hasClass(target, 'bx-soa-pp-company') ? target : BX.findParent(target, {className: 'bx-soa-pp-company'}),
            selectedSection = this.state.deliveryBlockNode.querySelector('.bx-selected'),
            actionInput, selectedInput, selected;
        selected = this.state.deliveryBlockNode.querySelector('input[type=checkbox]');
        let class_input = BX.hasClass(selected, 'check_checkbox_delivery');

        if (BX.hasClass(actionSection, 'bx-selected') && !class_input)
            return BX.PreventDefault(event);

        if (actionSection) {
            actionInput = actionSection.querySelector('input[type=radio]');
            BX.addClass(actionSection, 'bx-selected');
            actionInput.checked = true;
            $('input[data-name="ADDRESS"]').val('');
        }
        if (selectedSection) {
            selectedInput = selectedSection.querySelector('input[type=radio]');
            BX.removeClass(selectedSection, 'bx-selected');
            selectedInput.checked = false;
        }

        BX.Sale.OrderAjaxComponent.sendRequest();
    }

    static getDeliverySortedArray(objDelivery, params) {
        var deliveries = [],
            problemDeliveries = [],
            sortFunc = function (a, b) {
                var sort = parseInt(a.SORT) - parseInt(b.SORT);
                if (sort === 0) {
                    return a.OWN_NAME.toLowerCase() > b.OWN_NAME.toLowerCase()
                        ? 1
                        : (a.OWN_NAME.toLowerCase() < b.OWN_NAME.toLowerCase() ? -1 : 0);
                } else {
                    return sort;
                }
            },
            k;

        for (k in objDelivery) {
            if (objDelivery.hasOwnProperty(k)) {
                if (params.SHOW_NOT_CALCULATED_DELIVERIES === 'L' && objDelivery[k].CALCULATE_ERRORS) {
                    problemDeliveries.push(objDelivery[k]);
                } else {
                    deliveries.push(objDelivery[k]);
                }
            }
        }

        deliveries.sort(sortFunc);
        problemDeliveries.sort(sortFunc);

        return deliveries.concat(problemDeliveries);
    }

    static getDerivedStateFromProps(props, state) {
        var arReserve, pages, arPages, i;
        if (state.result.DELIVERY) {
            state.result.DELIVERY = OrderDelivery.getDeliverySortedArray(state.result.DELIVERY, state.params);

            if (state.options.deliveriesPerPage > 0 && state.result.DELIVERY.length > state.options.deliveriesPerPage) {
                arReserve = state.result.DELIVERY.slice();
                pages = Math.ceil(arReserve.length / state.options.deliveriesPerPage);
                arPages = [];

                for (i = 0; i < pages; i++) {
                    arPages.push(arReserve.splice(0, state.options.deliveriesPerPage));
                }
                this.deliveryPagination.pages = arPages;

                for (i = 0; i < state.result.DELIVERY.length; i++) {
                    if (state.result.DELIVERY[i].CHECKED === 'Y') {
                        state.deliveryPagination.pageNumber = Math.ceil(++i / state.options.deliveriesPerPage);
                        break;
                    }
                }

                state.deliveryPagination.pageNumber = state.deliveryPagination.pageNumber || 1;
                state.deliveryPagination.currentPage = arPages.slice(state.deliveryPagination.pageNumber - 1, state.deliveryPagination.pageNumber)[0];
                state.deliveryPagination.show = true
            } else {
                state.deliveryPagination.pageNumber = 1;
                state.deliveryPagination.currentPage = state.result.DELIVERY;
                state.deliveryPagination.show = false;
            }
        }

        state.propertyCollection = new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, state.result.ORDER_PROP));

        return state;
    }

    render() {
        var itemsJsx = [];
        // for (k = 0; k < this.state.deliveryPagination.currentPage.length; k++) {
        //     let item_edit = this.state.deliveryPagination.currentPage[k];
        //     deliveryItemNode = this.createDeliveryItem(item_edit);
        //
        //     var checked = item.CHECKED,
        //         deliveryId = parseInt(item.ID),
        //         labelNodes = [
        //             BX.create('INPUT', {
        //                 props: {
        //                     id: 'ID_DELIVERY_ID_' + deliveryId,
        //                     name: 'DELIVERY_ID',
        //                     type: 'radio',
        //                     className: 'bx-soa-pp-company-checkbox form-check-input check_custom mr-2 m-0',
        //                     value: deliveryId,
        //                     checked: checked
        //                 }
        //             })
        //         ],
        //         deliveryCached = this.deliveryCachedInfo[deliveryId], label, title, itemNode, logoNode;
        //
        //     if (this.params.SHOW_DELIVERY_LIST_NAMES == 'Y') {
        //         title = BX.create('DIV', {
        //             props: {className: 'bx-soa-pp-company-smalltitle text-black text-base font-semibold dark:font-normal' +
        //                     ' dark:text-gray-300'},
        //             text: this.params.SHOW_DELIVERY_PARENT_NAMES != 'N' ? item.NAME : item.OWN_NAME
        //         });
        //     }
        //     logoNode = BX.create('DIV', {props: {className: 'bx-soa-pp-company-curs'}});
        //
        //     labelNodes.push(logoNode);
        //     labelNodes.push(title);
        //
        //     label = BX.create('DIV', {
        //         props: {
        //             className: 'bx-soa-pp-company-graf-container box_with_delivery mb-3 border-grey-line-order' +
        //                 ' cursor-pointer p-8 flex items-center w-full border-[1px] rounded-[10px] dark:border-darkBox' +
        //                 ' dark:text-gray-300 dark:bg-darkBox'
        //                 + (item.CALCULATE_ERRORS || deliveryCached && deliveryCached.CALCULATE_ERRORS
        //                     ? ' bx-bd-waring' : '')
        //         },
        //         children: labelNodes,
        //         events: {click: BX.proxy(this.selectDelivery, this)},
        //     });
        //
        //
        //     itemNode = BX.create('DIV', {
        //         props: {className: 'delivery bx-soa-pp-company relative mt-5'},
        //         children: [label],
        //     });
        //
        //     checked && BX.addClass(itemNode, 'bx-selected');
        //
        //     //enterego
        //     if (checked)
        //         //--if (checked && this.result.LAST_ORDER_DATA.PICK_UP)
        //         this.lastSelectedDelivery = deliveryId;
        //     if (BX.hasClass(itemNode, 'bx-selected')) {
        //         this.editPropsItems(itemNode);
        //     }
        //
        //     return itemNode;










        //     let check = '';
        //     if (item_edit.CHECKED === "Y") {
        //         this.editDeliveryInfo(deliveryItemNode, item_edit)
        //         check = 'active_box';
        //     }
        //
        //     if (item_edit.GROUP_ID !== '0') {
        //         let box_with_deliveries = deliveryItemsContainerRow.querySelector('div.parent_type_' + item_edit.GROUP_ID);
        //         let box_type_id = deliveryItemsContainerRow.querySelector('div.box_' + item_edit.GROUP_ID);
        //
        //
        //         if (box_with_deliveries !== null && box_type_id !== null) {
        //             box_type_id.appendChild(deliveryItemNode);
        //         } else {
        //             deliveryItemsContainerRow.appendChild(BX.create('DIV', {
        //                         props: {
        //                             className: 'd-flex flex-column bx-soa-pp-company hidden box_with_del_js parent_type_'
        //                                 + item_edit.GROUP_ID + ' ' + check
        //                         },
        //                         children: [
        //                             BX.create('DIV', {
        //                                 props: {
        //                                     className: 'bx-soa-pp-company-smalltitle color_black text-bold flex justify-content-between' +
        //                                         ' mb-2 box_with_delivery bx-soa-pp-company-graf-container'
        //                                 },
        //                                 html:
        //                                     '<div>' + item_edit.PARENT_NAME + '<i class="fa fa-chevron-down ml-3" aria-hidden="true"></i></div>' +
        //                                     '<div><img height="50" class="img_logo_delivery" src="' + item_edit.LOGOTIP_SRC_2X + '"/></div>'
        //                             }),
        //                             BX.create('DIV', {
        //                                     props: {
        //                                         className: 'p-1 box-none container-with-profile-delivery box_' + item_edit.GROUP_ID
        //                                     },
        //                                 }
        //                             )
        //                         ]
        //                     }
        //                 )
        //             );
        //             deliveryItemsContainerRow.querySelector('.box_' + item_edit.GROUP_ID).appendChild(deliveryItemNode)
        //         }
        //     } else {
        //         deliveryItemsContainerRow.appendChild(deliveryItemNode);
        //     }
        //
        // }
        // deliveryItemsContainer.appendChild(deliveryItemsContainerRow);
        //
        //
        // if (this.deliveryPagination.show)
        //     this.showPagination('delivery', deliveryItemsContainer);
        //
        // deliveryNode.appendChild(deliveryItemsContainer);











        // if (!this.result.ORDER_PROP || !this.propertyCollection)
        //     return;
        //
        // let propsItemsContainer = BX.create('DIV', {props: {className: 'grid grid-cols-2 gap-x-2 bx-soa-customer p-0'}}),
        //     group, property, groupIterator = this.propertyCollection.getGroupIterator(), propsIterator;
        //
        // if (!propsItemsContainer)
        //     propsItemsContainer = this.propsBlockNode.querySelector('.col-sm-12.bx-soa-customer');
        //
        // const arDelivery = this.params.AR_DELIVERY_PICKUP;
        // while (group = groupIterator()) {
        //     propsIterator = group.getIterator();
        //     while (property = propsIterator()) {
        //         // TODO Enterego pickup
        //         let disabled = false;
        //         if (propsNode.classList.contains('delivery')) {
        //             if (this.groupDeliveryProps.find(item => item === group.getName()) !== undefined) {
        //                 // TODO Enterego pickup
        //                 const id_del = this.result.DELIVERY.find(item => item.CHECKED === 'Y').ID;
        //                 if (arDelivery.indexOf(String(id_del)) !== -1) {
        //                     disabled = true;
        //                 }
        //                 this.getPropertyRowNode(property, propsItemsContainer, disabled);
        //             } else {
        //                 continue;
        //             }
        //
        //         } else {
        //             if (this.groupBuyerProps.find(item => item === group.getName()) !== undefined) {
        //                 this.getPropertyRowNode(property, propsItemsContainer, disabled);
        //             }
        //             continue;
        //         }
        //     }
        // }
        // propsNode.appendChild(propsItemsContainer);







        var k;
        for (k = 0; k < this.state.deliveryPagination.currentPage.length; k++) {
            let item = this.state.deliveryPagination.currentPage[k];
            var checked = item.CHECKED,
                deliveryId = parseInt(item.ID),
                deliveryCached = this.state.deliveryCachedInfo[deliveryId], label, title, itemNode, logoNode;

            var itemInnerJsx = [];

            itemInnerJsx.push(
                <input key={'delivery_radio_' + deliveryId} type="radio" name="DELIVERY_ID"
                       id={'ID_DELIVERY_ID_' + deliveryId} value={deliveryId}
                       defaultChecked={checked} className="bx-soa-pp-company-checkbox form-check-input
                       check_custom mr-2 m-0"
                />
            )
            itemInnerJsx.push(<div key={'delivery_logo_node' + deliveryId} className="bx-soa-pp-company-curs"></div>);
            if (this.state.params.SHOW_DELIVERY_LIST_NAMES === 'Y') {
                itemInnerJsx.push(
                    <div key={'delivery_name_block_' + deliveryId} className="bx-soa-pp-company-smalltitle text-black
                    text-base font-semibold dark:font-normal dark:text-gray-300">
                        {this.state.params.SHOW_DELIVERY_PARENT_NAMES != 'N' ? item.NAME : item.OWN_NAME}
                    </div>
                );
            }
            console.log(checked);
            var deliveryItemPropsJsx = [];
            if (checked && this.state.result.ORDER_PROP && this.state.propertyCollection) {
                console.log('it works');

                // deliveryItemPropsJsx.push(
                //     <div className="grid grid-cols-2 gap-x-2 bx-soa-customer p-0"></div>
                // );
                // let propsItemsContainer = BX.create('DIV', {props: {className: 'grid grid-cols-2 gap-x-2 bx-soa-customer p-0'}}),
                let group, property, groupIterator = this.state.propertyCollection.getGroupIterator(), propsIterator;

                const arDelivery = this.state.params.AR_DELIVERY_PICKUP;
                while (group = groupIterator()) {
                    propsIterator = group.getIterator();
                    while (property = propsIterator()) {
                        let disabled = false;
                        if (this.state.groupDeliveryProps.find(item => item === group.getName()) !== undefined) {
                            const id_del = this.state.result.DELIVERY.find(item => item.CHECKED === 'Y').ID;
                            if (arDelivery.indexOf(String(id_del)) !== -1) {
                                disabled = true;
                            }
                            deliveryItemPropsJsx.push(
                                <OrderProp key={property.getId()} property={property} disabled={disabled}
                                           result={this.state.result}/>
                            );
                        }
                    }
                }
            }

            itemsJsx.push(
                <div key={'delivery_item_' + deliveryId} className={'delivery bx-soa-pp-company relative mt-5' +
                    (checked ? ' bx-selected' : '')}>
                    <div className={'bx-soa-pp-company-graf-container box_with_delivery mb-3 border-grey-line-order' +
                        ' cursor-pointer p-8 flex items-center w-full border-[1px] rounded-[10px] dark:border-darkBox' +
                        ' dark:text-gray-300 dark:bg-darkBox'
                        + (item.CALCULATE_ERRORS || deliveryCached && deliveryCached.CALCULATE_ERRORS
                            ? ' bx-bd-waring' : '')} onClick={this.selectDelivery}>
                        {itemInnerJsx}
                    </div>
                    <div className="grid grid-cols-2 gap-x-2 bx-soa-customer p-0">{deliveryItemPropsJsx}</div>
                </div>
            );

            // if (BX.hasClass(itemNode, 'bx-selected')) {
            //     this.editPropsItems(itemNode);
            // }
        }




        console.log(itemsJsx);
        return (
            <div className="bx-soa-section-content">
                <div className="bx-soa-pp">
                    <div className="bx-soa-pp-item-container">
                        <div className="row">
                            {itemsJsx}
                        </div>
                    </div>
                </div>
            </div>
        );
    }
}

export default OrderDelivery;