import React, {useContext, useState} from "react";
import OrderContext from "../Context/OrderContext";

function OrderContragents({property}) {
    const {contrAgents} = useContext(OrderContext);
    const currentContr = contrAgents.find(contrAgent => contrAgent.ID_CONTRAGENT === property['VALUE'][0]) ?? contrAgents[0];
    const [selectedContragent, setSelectedContragent] = useState(currentContr ?? {});
    const onChangeHandler = (event) => {
        setSelectedContragent(contrAgents.find(contrAgent => contrAgent.ID_CONTRAGENT === event.target.value))
    };

    return (
        <div id="contragents-block" className="flex md:flex-nowrap flex-wrap w-full pb-6">
            <div className="md:w-3/4 w-full flex flex-col justify-end">
                <label htmlFor="contragent-select" className="bx-soa-custom-label mb-2 relative text-textLight
                    dark:text-textDarkLightGray font-semibold dark:font-light text-sm">{property['NAME']}</label>
                <div className="soa-property-container md:pr-3 pr-0">
                    <select className="w-full rounded-lg !border-grey-line-order !ring-0 dark:!border-darkBox
                     dark:!bg-darkBox p-3 md:mb-0 mb-3"
                            name={'ORDER_PROP_' + property['ID']}
                            aria-placeholder={property['DESCRIPTION']}
                            data-name={property['CODE']} id="contragent-select"
                            defaultValue={selectedContragent ? selectedContragent.ID_CONTRAGENT : null}
                            onChange={onChangeHandler}>
                        {contrAgents.map((contrAgent) => {
                            return (
                                <option key={'option_contragent_' + contrAgent.ID_CONTRAGENT}
                                        value={contrAgent.ID_CONTRAGENT}>
                                    {contrAgent.NAME_ORGANIZATION}
                                </option>
                            )
                        })}
                    </select>
                </div>
            </div>
            <div className="md:w-1/4 w-full bg-textDark rounded-lg
                dark:border-darkBox dark:bg-darkBox p-3">
                <div className="mb-2 flex flex-col">
                    <span className="lg:text-xs text-10 dark:font-light font-normal mb-1">ИНН:</span>
                    <span className="lg:text-xs text-10 font-semibold dark:font-medium">{selectedContragent.INN}</span>
                </div>
                <div className="flex flex-col">
                    <span className="lg:text-xs text-10 dark:font-light font-normal mb-1">Наименование организации:</span>
                    <span className="lg:text-xs text-10 font-semibold dark:font-medium">
                            {selectedContragent.NAME_ORGANIZATION}
                    </span>
                </div>
            </div>
        </div>
    );
}

export default OrderContragents;