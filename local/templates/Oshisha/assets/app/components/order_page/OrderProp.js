import OrderContragents from "./OrderContragents";
import OrderPropLocation from './order_page_properties/OrderPropLocation';
import OrderPropString from './order_page_properties/OrderPropString';
import OrderPropEnum from "./order_page_properties/OrderPropEnum";
import OrderPropDate from "./order_page_properties/OrderPropDate";
import React, {useContext} from "react";
import OrderContext from "./Context/OrderContext";

function OrderProp({property, disabled}) {
    const {result} = useContext(OrderContext);
    var propertyType = property.getType() || '';

    var classNames = "form-group bx-soa-customer-field flex justify-between flex-wrap pr-2 pb-[23px]";

    switch (property.getSettings().CODE) {
        case 'EMAIL':
            if (result.IS_AUTHORIZED) {
                if (BX('user_select')) {
                    BX.adjust(BX('user_select'), {style: {display: "none"}});
                }
            }
            classNames += " col-start-1";
            break;
        case 'PHONE':
            classNames += " col-start-2";
            break;
        case 'LOCATION':
            classNames += " col-start-1";
            break;
        case 'MESSAGE_TYPE':
            classNames += " col-start-2 form-check";
            break;
        default:
            classNames += " col-span-2";
            break;
    }

    let textLabel = BX.util.htmlspecialchars(property.getName()),
        labelFor = 'soa-property-' + property.getId();

    const renderProperty = () => {
        switch (propertyType) {
            case 'LOCATION':
                return (<OrderPropLocation property={property} disabled={disabled}/>);
            case 'STRING':
                return (<OrderPropString property={property} disabled={disabled}/>);
            case 'ENUM':
                return (<OrderPropEnum property={property} disabled={disabled}/>);
            case 'DATE':
                return (<OrderPropDate property={property} disabled={disabled}/>);
        }
    }

    return(<div className={classNames} data-property-id-row={property.getId()}>
        <label className="bx-soa-custom-label pb-3.5 relative text-black dark:text-white font-bold dark:font-normal text-sm" htmlFor={labelFor}>
            {textLabel}
        </label>
        {renderProperty()}
    </div>);
}

export default OrderProp;