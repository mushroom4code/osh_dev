import React, {useContext, useState} from "react";
import OrderContext from "./Context/OrderContext";

function OrderContragents({property}) {
    const {result, contragents, sendRequest} = useContext(OrderContext);
    const [selectedContragent, setSelectedContragent] = useState();
    if (!result['IS_AUTHORIZED'] || !contragents) {
        return(<></>);
    } else {
        var propertySettings = property,
            company_name_property = result.ORDER_PROP.properties.find(prop => prop.CODE === 'COMPANY'),
            inn_property = result.ORDER_PROP.properties.find(prop => prop.CODE === 'INN'), existingContragent;
        if (!selectedContragent) {
            if (propertySettings['VALUE'][0] && (existingContragent = contragents.find(contragent => contragent.ID_CONTRAGENT === propertySettings['VALUE'][0]))) {
                setSelectedContragent(
                    existingContragent
                );
            } else {
                setSelectedContragent(contragents[0]);
            }
        }
        var selectJsx = [];


        contragents.forEach((contragent) => {
            selectJsx.push(
                <option key={'option_contragent_' + contragent['ID_CONTRAGENT']}
                        value={contragent['ID_CONTRAGENT']}>
                    {contragent['NAME_ORGANIZATION']}
                </option>
            );
        });

        const onChangeHandler = (event) => {
            var newSelectedContragent = contragents.find(contragent => contragent.ID_CONTRAGENT === event.target.value);
            document.querySelector('input[name="ORDER_PROP_' + inn_property['ID'] + '"]').value =
                newSelectedContragent['INN'];
            document.querySelector('input[name="ORDER_PROP_' + company_name_property['ID'] + '"]').value =
                newSelectedContragent['NAME_ORGANIZATION'];
            setSelectedContragent(newSelectedContragent);
            sendRequest('refreshOrderAjax', []);
        };

        return(
            <div id="contragents-block" className="col-span-2 flex lg:no md:flex-nowrap flex-wrap lg:gap-[30px]
            md:gap-[30px] gap-3.5 pr-2 pb-[23px]">
                <div className="lg:basis-[70%] md:basis-[70%] basis-[100%] flex flex-col justify-between">
                    <label htmlFor="contragent-select" className="bx-soa-custom-label block pb-3.5 relative text-black
                    dark:text-white font-bold dark:font-normal text-sm">{propertySettings['NAME']}</label>
                    <div className="soa-property-container">
                        <select className="w-full rounded-lg !border-grey-line-order !ring-0
                                dark:!border-darkBox dark:!bg-darkBox lg:h-[45px] md:h-[45px] h-[50px]"
                                name={'ORDER_PROP_'+propertySettings['ID']}
                                aria-placeholder={propertySettings['DESCRIPTION']}
                                data-name={propertySettings['CODE']} id="contragent-select"
                                defaultValue={selectedContragent ? selectedContragent['ID_CONTRAGENT'] : null}
                                onChange={onChangeHandler}>
                            {selectJsx}
                        </select>
                    </div>
                </div>
                <div className="lg:basis-[30%] md:basis-[30%] basis-[100%] bg-textDark rounded-[10px]
                dark:border-darkBox dark:bg-darkBox p-3">
                    <div className="mb-1.5">
                        <span className="block text-[10px] leading-[13px] font-medium dark:font-normal mb-1">
                            ИНН:
                        </span>
                        <span className="block text-[11px] leading-[11px] font-bold dark:font-normal">
                            {inn_property['VALUE'][0]}
                        </span>
                    </div>
                    <div>
                        <span className="block text-[10px] leading-[13px] font-medium dark:font-normal mb-1">
                            Наименование организации:
                        </span>
                        <span className="block text-[11px] leading-[11px] font-bold dark:font-normal">
                            {company_name_property['VALUE'][0]}
                        </span>
                    </div>
                </div>
            </div>
        );
    }
}

export default OrderContragents;