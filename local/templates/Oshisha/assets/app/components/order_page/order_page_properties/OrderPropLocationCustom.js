import React, {useContext, useEffect, useState, useRef} from "react";
import OrderContext from "../Context/OrderContext";
import axios from "axios";

function OrderPropLocationCustom({locationName, setLocationName}) {
    const {locationProperty, afterSendReactRequest} = useContext(OrderContext);
    const [activeLocation, setActiveLocation] = useState(0)
    const [listLocations, setListLocations] = useState([]);
    const [openListLocations, setOpenListLocations] = useState(false);
    const [timeoutId, setTimeoutId] = useState(0);

    const sendRequestLocation = (code, name) => {
        setLocationName(name);
        setActiveLocation(0);
        setOpenListLocations(false);

        const additionalData = {};
        additionalData[[`ORDER_PROP_${locationProperty.ID}`]] = code;
        BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', {},
            afterSendReactRequest, additionalData);
    }
    const selectLocation = () => {
        sendRequestLocation(listLocations[activeLocation].CODE, listLocations[activeLocation].DISPLAY)

    }
    const onSelectLocation = (e) => {
        sendRequestLocation(listLocations[e.target.dataset.index].CODE, e.target.innerHTML);
    }
    const onChangeLocationString = (e) => {

        const curLocationName = e.target.value;
        setLocationName(curLocationName);

        clearTimeout(timeoutId);
        setTimeoutId(setTimeout(() => {
            axios.post("/bitrix/components/bitrix/sale.location.selector.search/get.php",
                {
                    sessid: BX.bitrix_sessid(),
                    select: {1: "CODE", 2: "TYPE_ID", VALUE: "ID", DISPLAY: "NAME.NAME"},
                    additionals: {1: "PATH"},
                    filter: {"=PHRASE": e.target.value, "=NAME.LANGUAGE_ID": "ru", "=SITE_ID": "N2"},
                    version: 2,
                    PAGE_SIZE: 10,
                    PAGE: 0
                },
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            ).then(response => {
                var responseData = eval("("+response.data+")");
                if (responseData.result === true) {
                    setOpenListLocations(true);
                    setListLocations(responseData.data.ITEMS);
                }
            })
        }, 800));
    }

    const onKeyDownLocation = (e) => {
        if (e.keyCode === 13) {
            selectLocation();

        } else if (e.keyCode === 38) {
            if (activeLocation === 0) {
                return
            }

            setActiveLocation(activeLocation - 1)
        } else if (e.keyCode === 40) {
            if (activeLocation === listLocations.length - 1) {
                return
            }

            setActiveLocation(activeLocation + 1)
        }
    }

    return (
        <div>
            <div className='title font-medium mb-[0.8em] uppercase'>
                Выберите город:
            </div>
            <div>
                <input value={locationName} onKeyDown={onKeyDownLocation} onChange={onChangeLocationString}
                       className='form-control min-width-700 w-full text-sm cursor-text
                 border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg dark:bg-grayButton'/>
                <input type="hidden"/>
                <ul className={` ${openListLocations ? '' : 'hidden'}`}>
                    {listLocations.map((location, index) => <li
                        className={`${activeLocation === index ? 'bg-white' : ''}`}
                        key={index} onClick={onSelectLocation} data-index={index}>
                        {location.DISPLAY}
                    </li>)}
                </ul>
            </div>
        </div>
    );
}


export default OrderPropLocationCustom;