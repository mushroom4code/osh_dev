import React, {useEffect, useState} from "react";

function OrderPropString({property, disabled}) {
    const [propertySettings, setPropertySettings] = useState(property.getSettings());

    return (<div className="soa-property-container">
        <input type="text" className="w-full text-sm cursor-text border-grey-line-order ring:grey-line-order dark:border-darkBox rounded-lg
               dark:bg-darkBox"
               size={propertySettings['SIZE']}
               name={'ORDER_PROP_'+propertySettings['ID']} placeholder={propertySettings['DESCRIPTION']}
               data-name={propertySettings['CODE']} autoComplete={propertySettings['IS_EMAIL'] === 'Y'
                   ? 'email'
                   : (propertySettings['IS_PAYER'] === 'Y'
                       ? 'name'
                       : (propertySettings['IS_PHONE'] === 'Y'
                           ? 'tel'
                           : null))}
               defaultValue={propertySettings['VALUE'] ? propertySettings['VALUE'][0] : ''}/>
    </div>);
}

export default OrderPropString;