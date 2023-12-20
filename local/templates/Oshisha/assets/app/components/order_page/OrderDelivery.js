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

    getDeliveryExtraServices(delivery) {
        var extraServices = [], brake = false,
            i, currentService, serviceNode, serviceName, input, serviceNodeInnerHtml;
        for (i in delivery.EXTRA_SERVICES) {
            if (!delivery.EXTRA_SERVICES.hasOwnProperty(i))
                continue;

            currentService = delivery.EXTRA_SERVICES[i];

            if (!currentService.canUserEditValue)
                continue;

            if (currentService.editControl.indexOf('this.checked') == -1) {
                if (i == 0)
                    brake = true;

                serviceNodeInnerHtml = currentService.editControl
                    + (currentService.description && currentService.description.length
                        ? '<div class="bx-soa-service-small">' + BX.util.htmlspecialchars(currentService.description) + '</div>'
                        : '');
                extraServices.push(
                  <div key={'delivery_' + delivery.ID + '_extra_service_' + i} className="form-group bx-soa-pp-field" dangerouslySetInnerHTML={{__html: serviceNodeInnerHtml}}>
                      <label dangerouslySetInnerHTML={{__html: BX.util.htmlspecialchars(currentService.name)
                              + (currentService.price ? ' (' + currentService.priceFormatted + ')' : '')}}></label>
                  </div>
                );
            } else {
                serviceNodeInnerHtml = currentService.editControl + BX.util.htmlspecialchars(currentService.name)
                    + (currentService.price ? ' (' + currentService.priceFormatted + ')' : '')
                    + (currentService.description && currentService.description.length
                        ? '<div class="bx-soa-service-small">' + BX.util.htmlspecialchars(currentService.description) + '</div>'
                        : '');
                extraServices.push(
                    <div key={'delivery_' + delivery.ID + '_extra_service_' + i} className="checkbox">
                        <label dangerouslySetInnerHTML={{ __html: serviceNodeInnerHtml}}></label>
                    </div>
                );
            }
        }

        return extraServices;
    }

    getDeliveryItemPropsJsx(item, checked) {
        var deliveryItemPropsJsx = [];
        if (checked && this.state.result.ORDER_PROP && this.state.propertyCollection) {
            let group, property, groupIterator = this.state.propertyCollection.getGroupIterator(),
                propsIterator;

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
        return deliveryItemPropsJsx;
    }

    getDeliveryItemInfoJsx(item, deliveryId, checked) {
        var deliveryItemInfoJsx = [];
        if (checked === 'Y') {
            var extraServices = this.getDeliveryExtraServices(item);
            deliveryItemInfoJsx.push(
                <div key={'delivery_info_item_' + deliveryId} className="bx-soa-pp-desc-container">
                    <div className="bx-soa-pp-company d-flex flex-column">
                        <div className="bx-soa-pp-company-block">
                            <div className="bx-soa-pp-company-desc">{item.DESCRIPTION}</div>
                            {item.CALCULATE_DESCRIPTION
                                ? <div className="bx-soa-pp-company-desc">
                                    item.CALCULATE_DESCRIPTION</div>
                                : null}
                        </div>
                        <div style={{clear: "both"}}></div>
                        {extraServices.length ?
                            <div className="bx-soa-pp-company-block">extraServices</div> : null}
                    </div>
                </div>
            );
        }
        return deliveryItemInfoJsx;
    }

    render() {
        var itemsJsx = [], itemsJsxByGroup = {}, deliveriesByGroup = {}, t;

        for (t = 0; t < this.state.deliveryPagination.currentPage.length; t++) {
            if (!deliveriesByGroup[this.state.deliveryPagination.currentPage[t].GROUP_ID])
                deliveriesByGroup[this.state.deliveryPagination.currentPage[t].GROUP_ID] = [];
            deliveriesByGroup[this.state.deliveryPagination.currentPage[t].GROUP_ID]
                .push(this.state.deliveryPagination.currentPage[t]);
        }

        for (const [groupId, groupItems] of Object.entries(deliveriesByGroup)) {
            var k;
            for (k = 0; k < groupItems.length; k++) {
                let item = groupItems[k];
                var checked = item.CHECKED,
                    deliveryId = parseInt(item.ID),
                    deliveryCached = this.state.deliveryCachedInfo[deliveryId];

                var itemInnerJsx = [];

                itemInnerJsx.push(
                    <input key={'delivery_radio_' + deliveryId} type="radio" name="DELIVERY_ID"
                           id={'ID_DELIVERY_ID_' + deliveryId} value={deliveryId}
                           defaultChecked={checked} className="bx-soa-pp-company-checkbox form-check-input
                       check_custom mr-2 m-0"
                    />
                )
                itemInnerJsx.push(<div key={'delivery_logo_node' + deliveryId}
                                       className="bx-soa-pp-company-curs"></div>);
                if (this.state.params.SHOW_DELIVERY_LIST_NAMES === 'Y') {
                    itemInnerJsx.push(
                        <div key={'delivery_name_block_' + deliveryId} className="bx-soa-pp-company-smalltitle text-black
                    text-base font-semibold dark:font-normal dark:text-gray-300">
                            {this.state.params.SHOW_DELIVERY_PARENT_NAMES != 'N' ? item.NAME : item.OWN_NAME}
                        </div>
                    );
                }

                var deliveryItemPropsJsx = this.getDeliveryItemPropsJsx(item, checked);

                var deliveryItemInfoJsx = this.getDeliveryItemInfoJsx(item, deliveryId, checked);

                var itemJsx = <div key={'delivery_item_' + deliveryId}
                                   className={'delivery bx-soa-pp-company relative mt-5' +
                                       (checked ? ' bx-selected' : '')}>
                    <div
                        className={'bx-soa-pp-company-graf-container box_with_delivery mb-3 border-grey-line-order' +
                            ' cursor-pointer p-8 flex items-center w-full border-[1px] rounded-[10px] dark:border-darkBox' +
                            ' dark:text-gray-300 dark:bg-darkBox'
                            + (item.CALCULATE_ERRORS || deliveryCached && deliveryCached.CALCULATE_ERRORS
                                ? ' bx-bd-waring' : '')} onClick={this.selectDelivery}>
                        {itemInnerJsx}
                    </div>
                    <div
                        className="grid grid-cols-2 gap-x-2 bx-soa-customer p-0">{deliveryItemPropsJsx}{deliveryItemInfoJsx}</div>
                </div>;

                if (groupId === '0') {
                    itemsJsx.push(
                        itemJsx
                    );
                } else {
                    if (!itemsJsxByGroup[groupId])
                        itemsJsxByGroup[groupId] = [];
                    itemsJsxByGroup[groupId].push(itemJsx);
                }
            }
        }

        for (const [groupId, groupItems] of Object.entries(itemsJsxByGroup)) {
            var item = deliveriesByGroup[groupId][0], i, check = false;
            for (i = 0; i < deliveriesByGroup[groupId].length; i++) {
                if (deliveriesByGroup[groupId][i].CHECKED === 'Y') {
                    check = true;
                    break;
                }
            }
            var groupJsx =
                <div key={'delivery_items_group' + groupId}
                     className={'d-flex flex-column bx-soa-pp-company box_with_del_js hidden parent_type_'
                         + groupId + ' ' + (check ? 'active_box' : '')}>
                    <div className={'bx-soa-pp-company-smalltitle color_black text-bold flex justify-content-between' +
                        ' mb-2 box_with_delivery bx-soa-pp-company-graf-container'}>
                        <div>{item.PARENT_NAME}<i className="fa fa-chevron-down ml-3" aria-hidden="true"></i></div>
                        <div>
                            <img height="50" className="img_logo_delivery" src={item.LOGOTIP_SRC_2X}/>
                        </div>
                    </div>
                    <div className={'p-1 box-none container-with-profile-delivery box_' + groupId}>{groupItems}</div>
                </div>;

            if (check) {
                itemsJsx.unshift(groupJsx);
            } else {
                itemsJsx.push(groupJsx);
            }
        }

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