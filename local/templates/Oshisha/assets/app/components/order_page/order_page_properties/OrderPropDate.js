import React from "react";

function OrderPropDate({property, disabled}) {

    if (!disabled) {
        return(
            <div className="soa-property-container flex justify-between">
                <input type="text" name={'ORDER_PROP_' + property.ID} size={property.SIZE}
                       className="datepicker_order form-control bx-soa-customer-input bx-ios-fix w-full text-sm
                       cursor-text border-grey-line-order ring:grey-line-order dark:border-darkBox rounded-lg
                       dark:bg-darkBox" data-name={property.CODE}/>
            </div>
        );
    }
}

export default OrderPropDate;