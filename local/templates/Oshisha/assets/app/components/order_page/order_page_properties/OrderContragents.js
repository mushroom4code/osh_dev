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
        <div id="contragents-block" className="flex md:flex-nowrap flex-wrap lg:gap-[30px] col-span-2
            md:gap-[30px] gap-3.5 pr-2 pb-[23px]">
            <div className="lg:basis-[70%] md:basis-[70%] basis-[100%] flex flex-col justify-end">
                <label htmlFor="contragent-select" className="bx-soa-custom-label mb-2 relative text-textLight
                    dark:text-textDarkLightGray font-semibold dark:font-light text-sm">{property['NAME']}</label>
                <div className="soa-property-container">
                    <select className="w-full rounded-lg !border-grey-line-order !ring-0
                                dark:!border-darkBox dark:!bg-darkBox lg:h-[45px] md:h-[45px] h-[50px]"
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
            <div className="lg:basis-[30%] md:basis-[30%] basis-[100%] bg-textDark rounded-[10px]
                dark:border-darkBox dark:bg-darkBox p-3">
                <div className="mb-2 flex flex-col">
                    <span className="text-xs font-normal mb-1">ИНН:</span>
                    <span className="text-xs font-semibold dark:font-normal">{selectedContragent.INN}</span>
                </div>
                <div className="flex flex-col">
                    <span className="text-xs font-normal mb-1">Наименование организации:</span>
                    <span className="text-xs font-semibold dark:font-normal">
                            {selectedContragent.NAME_ORGANIZATION}
                    </span>
                </div>
            </div>
        </div>
    );
}

export default OrderContragents;