import React, {useEffect, useState} from "react";

function OrderUserPropString({property, disabled}) {
    var propContainer;
    // console.log(property);
    // TODO Enterego pickup
    console.log(property);
    console.log(property.getSettings());
    console.log(property.parentNode);
    propContainer = BX.create('DIV', {props: {className: 'soa-property-container'}});
    property.appendTo(propContainer);
    propsItemNode.appendChild(propContainer);
    property.parent = propsItemNode;
    this.alterProperty(property, propContainer, disabled);
    this.bindValidation(property.getId(), propContainer);

    return (<div className="soa-property-container">
        {locationsJsx}
    </div>);
}

export default OrderUserPropString;