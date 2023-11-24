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
                        <input className="form-check-input mr-2" type="radio" name={'ORDER_PROP_'+propertySettings['ID']}
                               defaultValue={key}/>
                        {propertySettings['OPTIONS'][key]}
                    </label>
                </div>
            )}
        </div>);
    }
}

export default OrderUserPropEnum;