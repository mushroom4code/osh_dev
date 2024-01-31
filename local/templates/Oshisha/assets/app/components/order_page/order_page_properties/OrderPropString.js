import React, { useEffect, useState } from "react";

function OrderPropString({ property, disabled }) {

    const [value, setValue] = useState(property?.VALUE[0] ?? '')

    useEffect(()=>{
        setValue(property?.VALUE[0])
    }, [property?.VALUE[0] ?? ''])

    const onChangeValue = (e) => {
        setValue(e.target.value)
    }

    return (<div className="soa-property-container w-full">
        <input type="text" className="w-full text-sm cursor-text p-3 border-grey-line-order ring:grey-line-order dark:border-darkBox rounded-lg
               dark:bg-darkBox"
            size={property.SIZE}
            name={'ORDER_PROP_' + property.ID} placeholder={property.DESCRIPTION}
            data-name={property.CODE} autoComplete={property.IS_EMAIL === 'Y'
                ? 'email'
                : (property.IS_PAYER === 'Y'
                    ? 'name'
                    : (property.IS_PHONE === 'Y'
                        ? 'tel'
                        : null))}
            value={value}
            onChange={onChangeValue}
        />
    </div>);
}

export default OrderPropString;