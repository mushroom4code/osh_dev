import OrderUserPropLocation from './OrderUserPropLocation'
import OrderUserPropString from './OrderUserPropString'
import React, {useEffect, useState} from "react";
import axios from "axios";

function OrderUserProp({property, locations, disabled, result}) {
    var propertyType = property.getType() || '';
    console.log(result);
    //TODO Enterego pickup
    let classNames = "form-group bx-soa-customer-field p-2";
    console.log('here is start property');
    console.log(property.getId());
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
            classNames += " col-start-2 form-check mt-4";
            break;
        default:
            classNames += " col-span-2";
            break;
    }

    let textLabel = BX.util.htmlspecialchars(property.getName()),
        labelFor = 'soa-property-' + property.getId();
    // propsItemNode.setAttribute('data-property-id-row', property.getId());

    const renderProperty = () => {
        console.log('b');
        console.log(propertyType);
        console.log(property.getId());
        switch (propertyType) {
            case 'LOCATION':
                return(<OrderUserPropLocation property={property} locations={locations} disabled={disabled}/>);
            // case 'DATE':
            //     return dateProperty(property, disabled);
            // case 'FILE':
            //     return fileProperty(property, disabled);
            // case 'STRING':
            //     return(<OrderUserPropString property={property} disabled={disabled}/>);
            // case 'ENUM':
            //     return enumProperty(property, disabled);
                // propsItemNode.querySelectorAll('input[type="radio"]').forEach(function(item){
                //     item.classList = 'form-check-input mr-2'
                // })
            // case 'Y/N':
            //     return ynProperty(property, propsItemNode, disabled);
            // case 'NUMBER':
            //     return numberProperty(property, propsItemNode, disabled);
        }
    }

    // propsItemsContainer.appendChild(propsItemNode);


    return(<div className={classNames} data-property-id-row={property.getId()}>
        <label className="bx-soa-custom-label" htmlFor={labelFor}>
            {textLabel}
        </label>
        {renderProperty()}
    </div>);
}

export default OrderUserProp;