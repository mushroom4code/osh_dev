import OrderUserProp from './OrderUserProp';
import React from "react";

class OrderUserProps extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            locations: this.props.locations,
            are_locations_prepared: this.props.are_locations_prepared,
            group_buyer_props: ["Личные данные"],
            group_delivery_props: ["Данные для доставки"]
        }
    }

    render() {
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
                    if (this.state.group_buyer_props.find(item => item === group.getName()) !== undefined) {
                        a.push(property.getId());
                        div.push(
                            <OrderUserProp key={property.getId()} property={property} locations={this.state.locations}
                                           disabled={disabled} result={this.state.result}
                                           are_locations_prepared={this.state.are_locations_prepared}/>
                        );
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
}

export default OrderUserProps;