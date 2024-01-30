import OrderPropLocation from './order_page_properties/OrderPropLocation';
import OrderPropString from './order_page_properties/OrderPropString';
import OrderPropEnum from "./order_page_properties/OrderPropEnum";
import OrderPropDate from "./order_page_properties/OrderPropDate";
import React, {useContext} from "react";
import OrderContext from "./Context/OrderContext";

function OrderProp({property, disabled}) {
    const {result} = useContext(OrderContext);
    const propertyType = property.TYPE || '';

    let classNames = "bx-soa-customer-field flex justify-between flex-wrap pr-2 pb-6";

    switch (property.CODE) {
        case 'EMAIL':
            if (result.IS_AUTHORIZED) {
                if (BX('user_select')) {
                    BX.adjust(BX('user_select'), {style: {display: "none"}});
                }
            }
            break;
        case 'FIO':
            classNames += ' col-span-2';
            break;
        default:
            break;
    }

    let textLabel = BX.util.htmlspecialchars(property.NAME),
        labelFor = 'soa-property-' + property.ID;

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

    return (<div className={classNames} data-property-id-row={property.ID}>
        <label className="bx-soa-custom-label mb-3 relative text-textLight dark:text-textDarkLightGray font-semibold
             dark:font-light text-sm" htmlFor={labelFor}>
            {textLabel}
        </label>
        {renderProperty()}
    </div>);
}

OrderProp.defaultProps = {
    disabled: false
}

export default OrderProp;