import React, { useState } from 'react'
import axios from 'axios'
import PropTypes from 'prop-types'
import { ajaxDeliveryUrl } from '../OrderMain';
import OrderProp from '../OrderProp';
import OrderPropLocationCustom from "../order_page_properties/OrderPropLocationCustom";

function OshishaDaDataAddress({ handleSelectSuggest }) {
    const [locationName, setLocationName] = useState('');
    const [address, setAddress] = useState('')
    const [listSuggest, setListSuggest] = useState([])
    const [activeSuggest, setActiveSuggest] = useState(0)
    const [openListSuggest, setOpenListSuggest] = useState(false)
    const [timeoutId, setTimeoutId] = useState(0);

    const selectSuggest = () => {

        handleSelectSuggest(listSuggest[activeSuggest])
        setAddress(listSuggest[activeSuggest].value);
        setActiveSuggest(0)
        setOpenListSuggest(false)

    }

    const onSelectSuggest = (e) => {
        setAddress(e.target.innerHTML)
    }

    const onKeyDownDaDataAddress = (e) => {
        
        if (e.keyCode === 13) {
            selectSuggest()

        } else if (e.keyCode === 38) {
            if (activeSuggest === 0) {
                return
            }

            setActiveSuggest(activeSuggest - 1)
        } else if (e.keyCode === 40) {
            if (activeSuggest === listSuggest.length - 1) {
                return
            }

            setActiveSuggest(activeSuggest + 1)
        }
    }

    const onChangeDaDataString = (e) => {

        const curAddress = e.target.value;
        setAddress(curAddress);

        clearTimeout(timeoutId);
        setTimeoutId(setTimeout(() => {
            axios.post(
                ajaxDeliveryUrl,
                {
                    sessid: BX.bitrix_sessid(),
                    address: curAddress,
                    locations: [{city: locationName}],
                    action: 'getDaDataSuggest'
                },
                { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
            ).then(response => {
                setOpenListSuggest(true);
                setListSuggest(response.data);
            })
        }, 800));
    }

    return (
        <div>
            <OrderPropLocationCustom locationName={locationName} setLocationName={setLocationName}/>
            <div className='title font-medium mb-[0.8em] uppercase'>
                Введите адрес:
            </div>
            <div>
                <input value={address} onKeyDown={onKeyDownDaDataAddress} onChange={onChangeDaDataString} className='form-control min-width-700 w-full text-sm cursor-text
                 border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg dark:bg-grayButton'/>
                <ul className={` ${openListSuggest ? '' : 'hidden'}`}>
                    {listSuggest.map((suggest, index) => <li className={`${activeSuggest === index ? 'bg-white' : ''}`}
                                                             key={index} onClick={onSelectSuggest}>
                        {suggest.value}
                    </li>)}
                </ul>
            </div>
        </div>
    )
}

OshishaDaDataAddress.propTypes = {}

export default OshishaDaDataAddress
