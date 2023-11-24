import React, {useEffect, useState} from "react";

function OrderUserPropLocation({property, locations, disabled}) {
    const [preparedLocations, setPreparedLocations] = useState([]);
    const [cleanLocations, setCleanLocations] = useState([]);
    const [locationsTemplate, setLocationsTemplate] = useState('');

    function prepareLocations(locations) {
        var temporaryLocations, i, k, output, allTemporaryLocations = [], allCleanLocations = [];

        if (BX.util.object_keys(locations).length) {
            for (i in locations) {
                if (!locations.hasOwnProperty(i))
                    continue;

                setLocationsTemplate(locations[i].template || '');
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
            setCleanLocations(allCleanLocations);
            setPreparedLocations(allTemporaryLocations);
        }
    }

    function addLocationProperty(e) {
        // var target = e.target || e.srcElement,
        //     propId = target.getAttribute('data-prop-id'),
        //     lastProp = BX.previousSibling(target),
        //     insertedLoc, k, input, index = 0,
        //     prefix = 'sls-',
        //     randomStr = BX.util.getRandomString(5);
        //
        // if (BX.hasClass(lastProp, 'bx-soa-loc')) {
        //     if (this.locationsTemplate == 'search') {
        //         input = lastProp.querySelector('input[type=text][class=dropdown-field]');
        //         if (input)
        //             index = parseInt(input.name.substring(input.name.indexOf('[') + 1, input.name.indexOf(']'))) + 1;
        //     } else {
        //         input = lastProp.querySelectorAll('input[type=hidden]');
        //         if (input.length) {
        //             input = input[input.length - 1];
        //             index = parseInt(input.name.substring(input.name.indexOf('[') + 1, input.name.indexOf(']'))) + 1;
        //         }
        //     }
        // }
        //
        // if (this.cleanLocations[propId]) {
        //     insertedLoc = BX.create('DIV', {
        //         props: {className: 'bx-soa-loc'},
        //         style: {marginBottom: this.locationsTemplate == 'search' ? '5px' : '20px'},
        //         html: this.cleanLocations[propId].HTML.split('#key#').join(index).replace(/sls-\d{5}/g, prefix + randomStr)
        //     });
        //     target.parentNode.insertBefore(insertedLoc, target);
        //
        //     BX.saleOrderAjax.addPropertyDesc({
        //         id: propId + '_' + index,
        //         attributes: {
        //             id: propId + '_' + index,
        //             type: 'LOCATION',
        //             valueSource: 'form'
        //         }
        //     });
        //
        //
        //     for (k in this.cleanLocations[propId].SCRIPT)
        //         if (this.cleanLocations[propId].SCRIPT.hasOwnProperty(k))
        //             BX.evalGlobal(this.cleanLocations[propId].SCRIPT[k].JS.split('_key__').join('_' + index).replace(/sls-\d{5}/g, prefix + randomStr));
        //
        //     BX.saleOrderAjax.initDeferredControl();
        // }
    }

    var propRow, propNodes, locationString, currentLocation, i, k, values = [];

    useEffect(() => {
        prepareLocations(locations);
    }, []);

    if (property.getId() in preparedLocations) {
        if (disabled) {
            // propRow = this.propsHiddenBlockNode.querySelector('[data-property-id-row="' + property.getId() + '"]');
            // if (propRow) {
            //     propNodes = propRow.querySelectorAll('div.bx-soa-loc');
            //     for (i = 0; i < propNodes.length; i++) {
            //         locationString = this.getLocationString(propNodes[i]);
            //         values.push(locationString.length ? BX.util.htmlspecialchars(locationString) : BX.message('SOA_NOT_SELECTED'));
            //     }
            // }
            // propsItemNode.innerHTML += values.join('<br>');
        } else {
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
                         onClick={addLocationProperty}
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

export default OrderUserPropLocation;