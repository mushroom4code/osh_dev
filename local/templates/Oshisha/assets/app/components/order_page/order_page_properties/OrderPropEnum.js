import React from "react";

function OrderPropEnum({property, disabled}) {

    if (!disabled) {
        return (<div className="soa-property-container flex justify-between w-full">
            {Object.keys(property.OPTIONS).map(key => <div key={'order_prop_enum_'+key}>
                    <label className="font-semibold dark:font-normal">
                        <input className="form-check-input ring-0 focus:ring-0 focus:ring-transparent
                               focus:ring-offset-transparent focus:outline-none mr-2" type="radio"
                               name={'ORDER_PROP_'+property.ID} defaultValue={key}
                               defaultChecked={
                                (property.VALUE.length !== 0)
                                    ? (property.VALUE[0] === key ? true : null)
                                    : (property.DEFAULT_VALUE === key ? true : null)
                               }
                        />
                        {property.OPTIONS[key]}
                    </label>
                </div>
            )}
        </div>);
    }
}

export default OrderPropEnum;