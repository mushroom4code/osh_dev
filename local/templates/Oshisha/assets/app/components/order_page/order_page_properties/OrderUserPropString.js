import React, {useEffect, useState} from "react";

function OrderUserPropString({property, disabled}) {
    const [propertySettings, setPropertySettings] = useState(property.getSettings());

    return (<div className="soa-property-container">
        <input type="text" className="w-full text-sm border-stone-300 rounded-lg" size={propertySettings['SIZE']}
               name={'ORDER_RPOP_'+propertySettings['ID']} placeholder={propertySettings['DESCRIPTION']}
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

export default OrderUserPropString;