import React, {useContext, useEffect, useState, useRef} from "react";
import OrderContext from "../Context/OrderContext";

function OrderPropLocation({property, disabled}) {
    const {locations, areLocationsPrepared} = useContext(OrderContext);
    var preparedLocations, cleanLocations, locationsTemplate;
    function prepareLocations(locations) {
        var temporaryLocations, i, k, output, allTemporaryLocations = [], allCleanLocations = [];

        if (BX.util.object_keys(locations).length) {
            for (i in locations) {
                if (!locations.hasOwnProperty(i))
                    continue;

                locationsTemplate = locations[i].template || '';
                temporaryLocations = [];
                output = locations[i].output;
                if (output.clean) {
                    allCleanLocations[i] = BX.processHTML(output.clean, false);
                    delete output.clean;
                }

                for (k in output) {
                    if (output.hasOwnProperty(k)) {
                        temporaryLocations.push({
                            output: BX.processHTML(output[k], false),
                            showAlt: locations[i].showAlt,
                            lastValue: locations[i].lastValue,
                            coordinates: locations[i].coordinates || false
                        });
                    }
                }

                allTemporaryLocations[i] = temporaryLocations;
            }
            cleanLocations = allCleanLocations;
            preparedLocations = allTemporaryLocations
        }
    }

    var propRow,  currentLocation, i, k;
    prepareLocations(locations);

    if (property.getId() in preparedLocations) {
        if (!disabled) {
            let locationsJsx = [];
            propRow = preparedLocations[property.getId()];
            for (i = 0; i < propRow.length; i++) {
                currentLocation = propRow[i] ? propRow[i].output : {};
                if (property.isMultiple())
                    locationsJsx.push(
                        <div key={property.getId()+'_cur_location_'+i} className="bx-soa-loc"
                             style={locationsTemplate === 'search' ? 'margin-bottom: 5px' : 'margin-bottom: 20px'}
                             dangerouslySetInnerHTML={{__html: currentLocation.HTML}}>
                        </div>
                    );
                else {
                    locationsJsx.push(
                        <div key={property.getId()+'_cur_location_'+i} className="bx-soa-loc"
                             dangerouslySetInnerHTML={{__html: currentLocation.HTML}}
                        >
                        </div>
                    );
                }
                for (k in currentLocation.SCRIPT) {
                    if (currentLocation.SCRIPT.hasOwnProperty(k))
                        BX.evalGlobal(currentLocation.SCRIPT[k].JS);
                }
            }

            if (property.isMultiple()) {
                locationsJsx.push(
                    <div key={property.getId()+'_is_multiple'} data-prop-id={property.getId()}
                         className="btn btn-sm btn-primary"
                         onClick={BX.proxy(BX.Sale.OrderAjaxComponent.addLocationProperty, BX.Sale.OrderAjaxComponent)}
                    >
                        {BX.message('ADD_DEFAULT')}
                    </div>
                )
            }

            return (<div className="soa-property-container">
                {locationsJsx}
            </div>);
        }
    }
}

export default OrderPropLocation;