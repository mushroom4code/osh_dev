import React from "react";
import {useContext, useEffect, useRef} from "react";
import OrderContext from "./Context/OrderContext";

function getPersonTypeSortedArray(objPersonType) {
    var personTypes = [], k;

    for (k in objPersonType) {
        if (objPersonType.hasOwnProperty(k)) {
            personTypes.push(objPersonType[k]);
        }
    }

    return personTypes.sort(function (a, b) {
        return parseInt(a.SORT) - parseInt(b.SORT)
    });
}

OrderUserTypeCheck.propTypes = {};
function OrderUserTypeCheck() {
    const {result, params, afterSendReactRequest}  = useContext(OrderContext);
    var regionBlockNotEmpty, propertyCollection = new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, result.ORDER_PROP));

    const mounted = useRef();
    useEffect(() => {
        if (!mounted.current) {
            mounted.current = true;
        }
    });

    const onChangeHandler = () => {
        if(mounted.current) {
            BX.OrderPageComponents.startLoader();
            BX.bind(BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', [], afterSendReactRequest), BX.Sale.OrderAjaxComponent);
        }
    }

    if (!result.PERSON_TYPE)
        return;

    result.PERSON_TYPE = getPersonTypeSortedArray(result.PERSON_TYPE);
    var personTypesCount = result.PERSON_TYPE.length,
        currentType, oldPersonTypeId, i,
        options = [], delimiter = false, resultTypeCheckJsx = [];
    if (personTypesCount > 1) {
        resultTypeCheckJsx.push(<React.Fragment key={'person_type_group_initial'}>
            <label className="bx-soa-custom-label pb-3.5 relative text-black dark:text-white font-bold
                    dark:font-normal text-sm" key={'person_type_group_label'}
                   dangerouslySetInnerHTML={{__html: params.MESS_PERSON_TYPE}}>
            </label>
            <br key={'person_type_br_initial'}/>
        </React.Fragment>);
    }
    if (personTypesCount > 2) {
        for (i in result.PERSON_TYPE) {
            if (result.PERSON_TYPE.hasOwnProperty(i)) {
                currentType = result.PERSON_TYPE[i];
                options.push(<option key={'person_type_option_'+currentType.ID} value={currentType.ID}
                                     selected={currentType.CHECKED === 'Y'}>
                    {currentType.NAME}
                </option>);

                if (currentType.CHECKED === 'Y')
                    oldPersonTypeId = currentType.ID;
            }
        }
        resultTypeCheckJsx.push(
            <select className="form-control" name="PERSON_TYPE" key={'person_type_select'}
                    onChange={onChangeHandler}>
                {options}
            </select>
        );

        regionBlockNotEmpty = true;
    } else if (personTypesCount === 2) {
        for (i in result.PERSON_TYPE) {
            if (result.PERSON_TYPE.hasOwnProperty(i)) {
                currentType = result.PERSON_TYPE[i];


                if (delimiter)
                    resultTypeCheckJsx.push(<br key={'person_type_br_'+currentType.ID}/>);

                resultTypeCheckJsx.push(<div className="radio-inline" key={'person_type_div_'+currentType.ID}>
                    <label className="font-semibold dark:font-normal"
                           onChange={onChangeHandler}>
                        <input className="form-check-input ring-0 focus:ring-0 focus:ring-transparent
                                   focus:ring-offset-transparent focus:shadow-none focus:outline-none" type="radio"
                               name='PERSON_TYPE' defaultChecked={currentType.CHECKED === 'Y'}
                               value={currentType.ID}/>
                        {BX.util.htmlspecialchars(currentType.NAME)}
                    </label>
                </div>)

                delimiter = true;

                if (currentType.CHECKED === 'Y')
                    oldPersonTypeId = currentType.ID;
            }
        }
        regionBlockNotEmpty = true;
    } else {
        for (i in result.PERSON_TYPE)
            if (result.PERSON_TYPE.hasOwnProperty(i))
                resultTypeCheckJsx.push(<input className="form-check-input" type="hidden"
                                               key={'person_type_input_'+result.PERSON_TYPE[i].ID}
                                               name='PERSON_TYPE' value={result.PERSON_TYPE[i].ID}/>
                );
    }

    if (oldPersonTypeId) {
        resultTypeCheckJsx.push(<input className="form-check-input" type="hidden"
                                       key={'old_person_type_input_'+oldPersonTypeId}
                                       name="PERSON_TYPE_OLD" value={oldPersonTypeId}/>
        );

    }

    var resultTypeCheckJsxReturn;
    if (personTypesCount > 1) {
        resultTypeCheckJsxReturn = [];
        resultTypeCheckJsxReturn.push(<div className="form-group" key={'person_type_group'}>{resultTypeCheckJsx}</div>)
    } else {
        resultTypeCheckJsxReturn = resultTypeCheckJsx;
    }

    return(<div>{resultTypeCheckJsxReturn}</div>);
}

export default OrderUserTypeCheck;