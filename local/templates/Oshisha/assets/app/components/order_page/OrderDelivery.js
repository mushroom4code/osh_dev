import React, {useContext, useRef} from "react";
import OrderContext from "./Context/OrderContext";
import OrderProp from "./OrderProp";
import OrderOshishaDelivery, {listOshDeliveryProp} from "./OshishaDelivery/OrderOshishaDelivery";


function DeliveryItemsProps(result, param, item, checked) {

    return <div>
        {result.result.ORDER_PROP.properties.map((property) => {

            if (listOshDeliveryProp.find(item => item === property.CODE)) {
                return
            }

            if (property.PROPS_GROUP_ID !== '2' && property.PROPS_GROUP_ID !== '9') {
                return null
            }

            return <OrderProp key={property.ID} property={property} disabled={false}
                              result={result}/>
        })}
    </div>
}

function OrderDelivery() {
    const {result, params, options, sendRequest} = useContext(OrderContext);
    const groupDeliveryProps = ["Данные для доставки"];
    const deliveryBlockRef = useRef(null);

    const selectDelivery = (event) => {
        BX.OrderPageComponents.startLoader();
        var target = event.target || event.srcElement,
            actionSection = BX.hasClass(target, 'bx-soa-pp-company') ? target : BX.findParent(target, {className: 'bx-soa-pp-company'}),
            selectedSection = deliveryBlockRef.current.querySelector('.bx-selected'),
            actionInput, selectedInput, selected;
        selected = deliveryBlockRef.current.querySelector('input[type=checkbox]');
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

        sendRequest('refreshOrderAjax', []);
    }

    const getDeliverySortedArray = (objDelivery, params) => {
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

    const getDerivedStateFromProps = (props, state) => {

    }

    const getDeliveryExtraServices = (delivery) => {
        var extraServices = [], brake = false,
            i, currentService, serviceNode, serviceName, input, serviceNodeInnerHtml;
        for (i in delivery.EXTRA_SERVICES) {
            if (!delivery.EXTRA_SERVICES.hasOwnProperty(i))
                continue;

            currentService = delivery.EXTRA_SERVICES[i];

            if (!currentService.canUserEditValue)
                continue;

            if (currentService.editControl.indexOf('checked') == -1) {
                if (i == 0)
                    brake = true;

                serviceNodeInnerHtml = currentService.editControl
                    + (currentService.description && currentService.description.length
                        ? '<div class="bx-soa-service-small">' + BX.util.htmlspecialchars(currentService.description) + '</div>'
                        : '');
                extraServices.push(
                    <div key={'delivery_' + delivery.ID + '_extra_service_' + i} className="form-group bx-soa-pp-field"
                         dangerouslySetInnerHTML={{__html: serviceNodeInnerHtml}}>
                        <label dangerouslySetInnerHTML={{
                            __html: BX.util.htmlspecialchars(currentService.name)
                                + (currentService.price ? ' (' + currentService.priceFormatted + ')' : '')
                        }}></label>
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
                        <label dangerouslySetInnerHTML={{__html: serviceNodeInnerHtml}}></label>
                    </div>
                );
            }
        }

        return extraServices;
    }

    const getDeliveryItemInfoJsx = (item, deliveryId, checked) => {
        var deliveryItemInfoJsx = [];
        if (checked === 'Y') {
            var extraServices = getDeliveryExtraServices(item);
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


    var arReserve, pages, arPages, i, deliveryPagination = {};
    if (result.DELIVERY) {
        result.DELIVERY = getDeliverySortedArray(result.DELIVERY, params);

        if (options.deliveriesPerPage > 0 && result.DELIVERY.length > options.deliveriesPerPage) {
            arReserve = result.DELIVERY.slice();
            pages = Math.ceil(arReserve.length / options.deliveriesPerPage);
            arPages = [];

            for (i = 0; i < pages; i++) {
                arPages.push(arReserve.splice(0, options.deliveriesPerPage));
            }
            deliveryPagination.pages = arPages;

            for (i = 0; i < result.DELIVERY.length; i++) {
                if (result.DELIVERY[i].CHECKED === 'Y') {
                    deliveryPagination.pageNumber = Math.ceil(++i / options.deliveriesPerPage);
                    break;
                }
            }

            deliveryPagination.pageNumber = deliveryPagination.pageNumber || 1;
            deliveryPagination.currentPage = arPages.slice(deliveryPagination.pageNumber - 1, deliveryPagination.pageNumber)[0];
            deliveryPagination.show = true
        } else {
            deliveryPagination.pageNumber = 1;
            deliveryPagination.currentPage = result.DELIVERY;
            deliveryPagination.show = false;
        }
    }

    var propertyCollection = new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, result.ORDER_PROP));

    var itemsJsx = [], itemsJsxByGroup = {}, deliveriesByGroup = {}, t, deliveryCachedInfo = [];

    for (t = 0; t < deliveryPagination.currentPage.length; t++) {
        if (!deliveriesByGroup[deliveryPagination.currentPage[t].GROUP_ID])
            deliveriesByGroup[deliveryPagination.currentPage[t].GROUP_ID] = [];
        deliveriesByGroup[deliveryPagination.currentPage[t].GROUP_ID]
            .push(deliveryPagination.currentPage[t]);
    }

    for (const [groupId, groupItems] of Object.entries(deliveriesByGroup)) {
        var k;
        for (k = 0; k < groupItems.length; k++) {
            let item = groupItems[k];
            var checked = item.CHECKED,
                deliveryId = parseInt(item.ID),
                deliveryCached = deliveryCachedInfo[deliveryId];

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
            if (params.SHOW_DELIVERY_LIST_NAMES === 'Y') {
                itemInnerJsx.push(
                    <div key={'delivery_name_block_' + deliveryId} className="bx-soa-pp-company-smalltitle text-black
                    text-base font-semibold dark:font-normal dark:text-gray-300">
                        {params.SHOW_DELIVERY_PARENT_NAMES != 'N' ? item.NAME : item.OWN_NAME}
                    </div>
                );
            }

            var deliveryItemInfoJsx = getDeliveryItemInfoJsx(item, deliveryId, checked);

            var itemJsx = <div key={'delivery_item_' + deliveryId}
                               className={'delivery bx-soa-pp-company relative mt-5' +
                                   (checked ? ' bx-selected' : '')}>
                <div
                    className={'bx-soa-pp-company-graf-container box_with_delivery mb-3 border-grey-line-order' +
                        ' cursor-pointer p-8 flex items-center w-full border-[1px] rounded-[10px] dark:border-darkBox' +
                        ' dark:text-gray-300 dark:bg-darkBox'
                        + (item.CALCULATE_ERRORS || deliveryCached && deliveryCached.CALCULATE_ERRORS
                            ? ' bx-bd-waring' : '')} onClick={selectDelivery}>
                    {itemInnerJsx}
                </div>
                <div
                    className="grid grid-cols-2 gap-x-2 bx-soa-customer p-0">{deliveryItemInfoJsx}</div>
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
        <div ref={deliveryBlockRef} className="bx-soa-section-content">
            <div className="bx-soa-pp">
                <OrderOshishaDelivery result={result} params={params} sendRequest={sendRequest}/>
                <div className="bx-soa-pp-item-container">
                    <div className="row">
                        {itemsJsx}
                    </div>
                </div>
                <div className="hidden">
                    <DeliveryItemsProps result={result}/>
                </div>
            </div>
        </div>
    );
}

export default OrderDelivery;