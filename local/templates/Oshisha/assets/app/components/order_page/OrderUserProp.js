import OrderUserPropLocation from './order_page_properties/OrderUserPropLocation';
import OrderUserPropString from './order_page_properties/OrderUserPropString';
import OrderUserPropEnum from "./order_page_properties/OrderUserPropEnum";
import React, {useEffect, useState} from "react";

function OrderUserProp({property, locations, disabled, result, are_locations_prepared}) {
    var propertyType = property.getType() || '';

    let classNames = "form-group bx-soa-customer-field flex justify-between flex-wrap pr-2 pb-[23px]";

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
                return(<OrderUserPropLocation property={property} locations={locations} disabled={disabled}
                                              are_locations_prepared={are_locations_prepared}/>);
            case 'STRING':
                return(<OrderUserPropString property={property} disabled={disabled}/>);
            case 'ENUM':
                return(<OrderUserPropEnum property={property} disabled={disabled}/>);
        }
    }

    return(<div className={classNames} data-property-id-row={property.getId()}>
        <label className="bx-soa-custom-label pb-3.5 relative text-black dark:text-white font-bold dark:font-normal text-sm" htmlFor={labelFor}>
            {textLabel}
        </label>
        {renderProperty()}
    </div>);
}

export default OrderUserProp;