import OrderUserProp from './OrderUserProp';
import React, {useEffect, useState} from "react";

function OrderUserProps({result, locations}) {
    const [propertyCollection, setPropertyCollection] = useState(new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, result.ORDER_PROP)));
    const [resultData, setResultData] = useState(result);
    const [groupBuyerProps, setGroupBuyerProps] = useState(["Личные данные"]);
    const [groupDeliveryProps, setGroupDeliveryProps] = useState(["Данные для доставки"]);

    // function editPropsItems(propsNode) {
    //     if (!this.result.ORDER_PROP || !this.propertyCollection)
    //         return;
    //
    //     let propsItemsContainer = BX.create('DIV', {props: {className: 'grid grid-cols-2 gap-x-2 bx-soa-customer p-0'}}),
    //         group, property, groupIterator = this.propertyCollection.getGroupIterator(), propsIterator;
    //
    //     if (!propsItemsContainer)
    //         propsItemsContainer = this.propsBlockNode.querySelector('.col-sm-12.bx-soa-customer');
    //
    //     const arDelivery = this.params.AR_DELIVERY_PICKUP;
    //     while (group = groupIterator()) {
    //         propsIterator = group.getIterator();
    //         while (property = propsIterator()) {
    //             // TODO Enterego pickup
    //             let disabled = false;
    //             if (propsNode.classList.contains('delivery')) {
    //                 if (this.groupDeliveryProps.find(item => item === group.getName()) !== undefined) {
    //                     // TODO Enterego pickup
    //                     const id_del = this.result.DELIVERY.find(item => item.CHECKED === 'Y').ID;
    //                     if (arDelivery.indexOf(String(id_del)) !== -1) {
    //                         disabled = true;
    //                     }
    //                     this.getPropertyRowNode(property, propsItemsContainer, disabled);
    //                 } else {
    //                     continue;
    //                 }
    //
    //             } else {
    //                 if (this.groupBuyerProps.find(item => item === group.getName()) !== undefined) {
    //                     this.getPropertyRowNode(property, propsItemsContainer, disabled);
    //                 }
    //                 continue;
    //             }
    //         }
    //     }
    //     propsNode.appendChild(propsItemsContainer);
    // }

    const renderProperties = () => {
        let div = [];
        let group, property, groupIterator = propertyCollection.getGroupIterator(), propsIterator;
        let a = [];
        while(group = groupIterator()) {
            propsIterator = group.getIterator();
            while (property = propsIterator()) {
                // TODO Enterego pickup
                let disabled = false;
                // if (propsNode.classList.contains('delivery')) {
                //     if (this.groupDeliveryProps.find(item => item === group.getName()) !== undefined) {
                //         // TODO Enterego pickup
                //         const id_del = this.result.DELIVERY.find(item => item.CHECKED === 'Y').ID;
                //         if (arDelivery.indexOf(String(id_del)) !== -1) {
                //             disabled = true;
                //         }
                //         this.getPropertyRowNode(property, propsItemsContainer, disabled);
                //     } else {
                //         continue;
                //     }
                // } else {
                    if (groupBuyerProps.find(item => item === group.getName()) !== undefined) {
                        a.push(property.getId());
                        div.push(<OrderUserProp key={property.getId()} property={property} locations={locations} disabled={disabled} result={resultData}/>);
                        // getPropertyRowNode(property, disabled);
                    }
                    // continue;
                // }
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

export default OrderUserProps;