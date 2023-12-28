import OrderProp from './OrderProp';
import React, {useContext} from "react";
import OrderContext from "./Context/OrderContext";

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
        let a = [];
        while(group = groupIterator()) {
            propsIterator = group.getIterator();
            while (property = propsIterator()) {
                // TODO Enterego pickup
                let disabled = false;
                if (group_buyer_props.find(item => item === group.getName()) !== undefined) {
                    a.push(property.getId());
                    div.push(
                        <OrderProp key={property.getId()} property={property} disabled={disabled}/>
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

export default OrderUserProps;