import React, {useEffect, useState} from "react";

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
function OrderUserTypeCheck({result, params}) {
    const [propertyCollection, setPropertyCollection] = useState(new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, result.ORDER_PROP)));
    const [resultData, setResultData] = useState(result);
    const [paramsData, setParamsData] = useState(params);

    const renderUserTypeCheck = () => {
        if (!resultData.PERSON_TYPE)
            return;

        resultData.PERSON_TYPE = getPersonTypeSortedArray(resultData.PERSON_TYPE);
        var personTypesCount = resultData.PERSON_TYPE.length,
            currentType, oldPersonTypeId, i,
            options = [], delimiter = false, resultTypeCheckJsx = [];
        if (personTypesCount > 1) {
            resultTypeCheckJsx.push(<React.Fragment key={'person_type_group_initial'}>
                <label className="bx-soa-custom-label" key={'person_type_group_label'}
                       dangerouslySetInnerHTML={{__html: paramsData.MESS_PERSON_TYPE}}>
                </label>
                <br key={'person_type_br_initial'}/>
            </React.Fragment>);
        }
        console.log('second step');
        if (personTypesCount > 2) {
            for (i in resultData.PERSON_TYPE) {
                if (resultData.PERSON_TYPE.hasOwnProperty(i)) {
                    currentType = resultData.PERSON_TYPE[i];
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
                        onChange={BX.proxy(BX.Sale.OrderAjaxComponent.sendRequest, BX.Sale.OrderAjaxComponent)}>
                    {options}
                </select>
            );

            this.regionBlockNotEmpty = true;
        } else if (personTypesCount === 2) {
            for (i in resultData.PERSON_TYPE) {
                if (resultData.PERSON_TYPE.hasOwnProperty(i)) {
                    currentType = resultData.PERSON_TYPE[i];


                    if (delimiter)
                        resultTypeCheckJsx.push(<br key={'person_type_br_'+currentType.ID}/>);

                    resultTypeCheckJsx.push(<div className="radio-inline" key={'person_type_div_'+currentType.ID}>
                        <label onChange={BX.proxy(BX.Sale.OrderAjaxComponent.sendRequest, BX.Sale.OrderAjaxComponent)}>
                            <input className="form-check-input" type="radio" name='PERSON_TYPE'
                                   defaultChecked={currentType.CHECKED === 'Y'} value={currentType.ID}/>
                            {BX.util.htmlspecialchars(currentType.NAME)}
                        </label>
                    </div>)

                    delimiter = true;

                    if (currentType.CHECKED === 'Y')
                        oldPersonTypeId = currentType.ID;
                }
            }

            // this.regionBlockNotEmpty = true;
        } else {
            for (i in resultData.PERSON_TYPE)
                if (resultData.PERSON_TYPE.hasOwnProperty(i))
                    resultTypeCheckJsx.push(<input className="form-check-input" type="hidden"
                                                   key={'person_type_input_'+resultData.PERSON_TYPE[i].ID}
                                                   name='PERSON_TYPE' value={resultData.PERSON_TYPE[i].ID}/>
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
        return(resultTypeCheckJsxReturn);
    }

    return(<div>{renderUserTypeCheck()}</div>);
}

export default OrderUserTypeCheck;