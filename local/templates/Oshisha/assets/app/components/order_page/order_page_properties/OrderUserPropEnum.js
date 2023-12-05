import React, {useEffect, useState} from "react";

function OrderUserPropEnum({property, disabled}) {
    const [propertySettings, setPropertySettings] = useState(property.getSettings());
    if (disabled) {
        // prop = this.propsHiddenBlockNode.querySelector('div[data-property-id-row="' + property.getId() + '"]');
        // if (prop) {
        //     values = [];
        //     inputs = prop.querySelectorAll('input[type=radio]');
        //     if (inputs.length) {
        //         for (i = 0; i < inputs.length; i++) {
        //             if (inputs[i].checked)
        //                 values.push(inputs[i].nextSibling.nodeValue);
        //         }
        //     }
        //     inputs = prop.querySelectorAll('option');
        //     if (inputs.length) {
        //         for (i = 0; i < inputs.length; i++) {
        //             if (inputs[i].selected)
        //                 values.push(inputs[i].innerHTML);
        //         }
        //     }
        //
        //     propsItemNode.innerHTML += this.valuesToString(values);
        // }
        // return (<div>
        //
        // </div>);
    } else {
        return (<div className="soa-property-container flex justify-between">
            {Object.keys(propertySettings['OPTIONS']).map(key => <div key={'order_prop_enum_'+key}>
                    <label>
                        <input className="form-check-input ring-0 focus:ring-0 focus:ring-transparent
                               focus:ring-offset-transparent focus:outline-none mr-2" type="radio"
                               name={'ORDER_PROP_'+propertySettings['ID']} defaultValue={key}
                               defaultChecked={
                                (propertySettings['VALUE'].length !== 0)
                                    ? (propertySettings['VALUE'][0] === key ? true : null)
                                    : (propertySettings['DEFAULT_VALUE'] === key ? true : null)
                               }
                        />
                        {propertySettings['OPTIONS'][key]}
                    </label>
                </div>
            )}
        </div>);
    }
}

export default OrderUserPropEnum;