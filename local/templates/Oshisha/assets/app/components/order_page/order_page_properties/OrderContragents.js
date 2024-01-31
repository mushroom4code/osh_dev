import React, {useState} from "react";
import DropDown from "../../icon/DropDown";
import IconNameContr from "../../contragents/IconNameContr";

function OrderContragents({property, contrAgents}) {
    const currentContr = contrAgents.find(contrAgent => contrAgent.ID_CONTRAGENT === property['VALUE'][0]) ?? contrAgents[0];
    const [selectedContrAgent, setSelectedContrAgent] = useState(currentContr ?? {});
    const [openList, setOpenList] = useState(false);
    const onSelectContr = (event) => {
        setSelectedContrAgent(
            contrAgents.find(
                contrAgent => parseInt(contrAgent.ID_CONTRAGENT) === parseInt(event.target.value)
            )
        )
        property.VALUE = [event.target.value];
        setOpenList(!openList)
    };

    return (
        <div className="flex md:flex-nowrap flex-wrap w-full mb-7 bx-soa-customer-field order-1 pr-2"
             data-property-id-row={property.ID}>
            <div className="md:w-3/4 w-full flex flex-col justify-end">
                <label className="bx-soa-custom-label mb-3 relative text-textLight dark:text-textDarkLightGray
                font-semibold dark:font-light text-sm">
                    {property.NAME}
                </label>
                <div className="soa-property-container md:mr-3 mr-0 relative">
                    <p className="w-full text-sm cursor-text p-3 border-grey-line-order border ring:grey-line-order
                     dark:border-darkBox rounded-lg dark:bg-darkBox relative flex flex-row items-center md:mb-0 mb-3"
                       onClick={() => (setOpenList(!openList))}>
                        <IconNameContr width="25" height="25" button={true} style="mr-2"/>
                        {selectedContrAgent?.NAME_ORGANIZATION ?? ''}
                        <DropDown openList={openList}/>
                    </p>
                    <ul className={openList ?
                        'absolute z-10 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 focus:outline-none ' +
                        'text-base shadow-lg dark:shadow-shadowDark ring-1 ring-black ring-opacity-5 sm:text-sm ' +
                        '!border-grey-line-order dark:!border-darkBox dark:!bg-darkBox' : 'hidden'}>
                        {
                            contrAgents.map((contrAgent) => {
                                return (
                                    <li key={'contrAgent_' + contrAgent.ID_CONTRAGENT}
                                        onClick={onSelectContr}
                                        className="py-2 pl-3 pr-9 text-textLight dark:text-textDarkLightGray font-normal
                                        dark:font-light hover:bg-textDarkLightGray dark:hover:bg-grayButton flex flex-row items-center"
                                        value={contrAgent.ID_CONTRAGENT}>
                                        {contrAgent?.NAME_ORGANIZATION ?? ''}
                                    </li>
                                )
                            })
                        }
                    </ul>
                </div>
            </div>
            <BlockInfo selectedContrAgent={selectedContrAgent}/>
            <input type="hidden" name={'ORDER_PROP_' + property.ID} value={property.VALUE}
                   data-name={property['CODE']}/>
        </div>
    );
}

function BlockInfo({selectedContrAgent}) {
    return (
        <div className="md:w-1/4 w-full bg-textDark rounded-lg dark:border-darkBox dark:bg-darkBox p-3">
            <div className="mb-2 flex flex-col">
                <span className="lg:text-xs text-10 dark:font-light font-normal mb-1">ИНН:</span>
                <span className="lg:text-xs text-10 font-semibold dark:font-medium">{selectedContrAgent?.INN}</span>
            </div>
            <div className="flex flex-col">
                    <span className="lg:text-xs text-10 dark:font-light font-normal mb-1">
                        Наименование организации:
                    </span>
                <span className="lg:text-xs text-10 font-semibold dark:font-medium">
                    {selectedContrAgent?.NAME_ORGANIZATION}
                </span>
            </div>
        </div>
    )
}


export default OrderContragents;