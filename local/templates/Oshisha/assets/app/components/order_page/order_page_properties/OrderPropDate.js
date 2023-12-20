import React, {useEffect, useState} from "react";

function OrderPropDate({property, disabled}) {
    const [propertySettings, setPropertySettings] = useState(property.getSettings());
    if (!disabled) {
        return(
            <div className="soa-property-container flex justify-between">
                <input type="text" name={'ORDER_PROP_' + propertySettings['ID']} size={propertySettings['SIZE']}
                       className="datepicker_order form-control bx-soa-customer-input bx-ios-fix w-full text-sm
                       cursor-text border-grey-line-order ring:grey-line-order dark:border-darkBox rounded-lg
                       dark:bg-darkBox" data-name={propertySettings['CODE']}/>
            </div>
        );
    }
}

export default OrderPropDate;