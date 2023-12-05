import React from "react";

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
class OrderUserTypeCheck extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            result: this.props.result,
            params: this.props.params
        }
        this.propertyCollection = new BX.Sale.PropertyCollection(BX.merge({publicMode: true}, this.props.result.ORDER_PROP));
    }

    render() {
        if (!this.state.result.PERSON_TYPE)
            return;

        this.state.result.PERSON_TYPE = getPersonTypeSortedArray(this.state.result.PERSON_TYPE);
        var personTypesCount = this.state.result.PERSON_TYPE.length,
            currentType, oldPersonTypeId, i,
            options = [], delimiter = false, resultTypeCheckJsx = [];
        if (personTypesCount > 1) {
            resultTypeCheckJsx.push(<React.Fragment key={'person_type_group_initial'}>
                <label className="bx-soa-custom-label pb-[2px] relative text-black dark:text-white font-semibold text-sm" key={'person_type_group_label'}
                       dangerouslySetInnerHTML={{__html: this.state.params.MESS_PERSON_TYPE}}>
                </label>
                <br key={'person_type_br_initial'}/>
            </React.Fragment>);
        }
        if (personTypesCount > 2) {
            for (i in this.state.result.PERSON_TYPE) {
                if (this.state.result.PERSON_TYPE.hasOwnProperty(i)) {
                    currentType = this.state.result.PERSON_TYPE[i];
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
            for (i in this.state.result.PERSON_TYPE) {
                if (this.state.result.PERSON_TYPE.hasOwnProperty(i)) {
                    currentType = this.state.result.PERSON_TYPE[i];


                    if (delimiter)
                        resultTypeCheckJsx.push(<br key={'person_type_br_'+currentType.ID}/>);

                    resultTypeCheckJsx.push(<div className="radio-inline" key={'person_type_div_'+currentType.ID}>
                        <label onChange={BX.proxy(BX.Sale.OrderAjaxComponent.sendRequest, BX.Sale.OrderAjaxComponent)}>
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
            this.regionBlockNotEmpty = true;
        } else {
            for (i in this.state.result.PERSON_TYPE)
                if (this.state.result.PERSON_TYPE.hasOwnProperty(i))
                    resultTypeCheckJsx.push(<input className="form-check-input" type="hidden"
                                                   key={'person_type_input_'+this.state.result.PERSON_TYPE[i].ID}
                                                   name='PERSON_TYPE' value={this.state.result.PERSON_TYPE[i].ID}/>
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
}

export default OrderUserTypeCheck;