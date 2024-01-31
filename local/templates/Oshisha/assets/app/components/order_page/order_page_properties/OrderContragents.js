import React, {useState} from "react";

function OrderContragents({property, contrAgents}) {
    const currentContr = contrAgents.find(contrAgent => contrAgent.ID_CONTRAGENT === property['VALUE'][0]) ?? contrAgents[0];
    const [selectedContragent, setSelectedContragent] = useState(currentContr ?? {});
    const [openList, setOpenList] = useState(false);
    const onClickHandler = (event) => {
        setSelectedContragent(
            contrAgents.find(
                contrAgent => parseInt(contrAgent.ID_CONTRAGENT) === parseInt(event.target.value)
            )
        )
        property.VALUE = [event.target.value];
        setOpenList(!openList)
    };

    return (
        <div id="contragents-block" className="flex md:flex-nowrap flex-wrap w-full pb-6">
            <div className="md:w-3/4 w-full flex flex-col justify-end">
                <label htmlFor="contragent-select" className="bx-soa-custom-label mb-2 relative text-textLight
                    dark:text-textDarkLightGray font-semibold dark:font-light text-sm">{property['NAME']}</label>
                <div className="soa-property-container md:pr-3 pr-0 relative">
                    <p className="w-full text-sm cursor-text p-3 border-grey-line-order border ring:grey-line-order
                     dark:border-darkBox rounded-lg dark:bg-darkBox" onClick={() => (setOpenList(!openList))}>
                        {selectedContragent?.NAME_ORGANIZATION}
                    </p>
                    <ul className={openList ? 'absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 text-base ' +
                        'shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm !border-grey-line-order' +
                        ' dark:!border-darkBox dark:!bg-darkBox' : 'hidden'}
                        id="contragent-select"
                        defaultValue={selectedContragent ? selectedContragent.ID_CONTRAGENT : null}>
                        {
                            contrAgents.map((contrAgent) => {
                                return (
                                    <li key={'option_contragent_' + contrAgent.ID_CONTRAGENT}
                                        onClick={onClickHandler}
                                        className="py-2 pl-3 pr-9 text-textLight dark:text-textDarkLightGray font-normal
                                        dark:font-light hover:bg-textDarkLightGray dark:hover:bg-grayButton"
                                        value={contrAgent.ID_CONTRAGENT}>
                                        {contrAgent.NAME_ORGANIZATION}
                                    </li>
                                )
                            })
                        }
                    </ul>

                </div>
            </div>
            <div className="md:w-1/4 w-full bg-textDark rounded-lg
                dark:border-darkBox dark:bg-darkBox p-3">
                <div className="mb-2 flex flex-col">
                    <span className="lg:text-xs text-10 dark:font-light font-normal mb-1">ИНН:</span>
                    <span className="lg:text-xs text-10 font-semibold dark:font-medium">{selectedContragent?.INN}</span>
                </div>
                <div className="flex flex-col">
                    <span
                        className="lg:text-xs text-10 dark:font-light font-normal mb-1">Наименование организации:</span>
                    <span className="lg:text-xs text-10 font-semibold dark:font-medium">
                            {selectedContragent?.NAME_ORGANIZATION}
                    </span>
                </div>
            </div>
            <input type="hidden" name={'ORDER_PROP_' + property.ID} value={property.VALUE}
                   data-name={property['CODE']}
                   className="w-full rounded-lg !border-grey-line-order !ring-0 dark:!border-darkBox
                     dark:!bg-darkBox p-3 md:mb-0 mb-3"/>
        </div>
    );
}

export default OrderContragents;