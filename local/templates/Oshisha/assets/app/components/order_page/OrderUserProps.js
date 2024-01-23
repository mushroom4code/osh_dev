import OrderProp from './OrderProp';
import React, {useContext} from "react";
import OrderContext from "./Context/OrderContext";
import OrderContragents from "./OrderContragents";

function OrderUserProps() {
    const {result} = useContext(OrderContext);
    const group_buyer_props = ["Личные данные"];

    const renderProperties = () => {
        let div = [];
        let group, property,
            propsIterator,
            groupIterator = new BX.Sale.PropertyCollection(
                BX.merge({publicMode: true}, result.ORDER_PROP)
            ).getGroupIterator();
        while(group = groupIterator()) {
            propsIterator = group.getIterator();
            while (property = propsIterator()) {
                // TODO Enterego pickup
                let disabled = false;
                if (group_buyer_props.find(item => item === group.getName()) !== undefined) {
                    if (property.getSettings().CODE === 'CONTRAGENT_ID') {
                        div.push(<OrderContragents property={property}/>);
                    } else {
                        div.push(
                            <OrderProp key={property.getId()} property={property} disabled={disabled}/>
                        );
                    }
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

export default OrderUserProps;