import React, {useEffect, useState} from 'react'
import PropTypes from 'prop-types'
import OshishaDoorDelivery from './OshishaDoorDelivery';
import OshishaPvzDelivery from './OshishaPvzDelivery';
import axios from 'axios';
import {ajaxDeliveryUrl} from '../OrderMain';
import OrderProp from '../OrderProp';
import OshishaInfoDelivery from './OshishaInfoDelivery';
import OrderPropLocationCustom from "../order_page_properties/OrderPropLocationCustom";
import OrderPropDate from "../order_page_properties/OrderPropDate";
import OrderPropSelect from '../order_page_properties/OrderPropSelect';
import dayjs from 'dayjs';
import Close from "./icon/Close";

export const listOshDeliveryProp = ['ADDRESS'
    , 'ADDRESS_PVZ'
    , 'COMMON_PVZ'
    , 'TYPE_DELIVERY'
    , 'ZIP'
    , 'LOCATION'
    , 'CITY'
    , 'FIAS'
    , 'KLADR'
    , 'STREET_KLADR'
    , 'LATITUDE'
    , 'LONGITUDE'
    , 'TYPE_PVZ']

const dateDeliveryPropCode = 'DATE_DELIVERY'
const deliveryIntervalPropCode = 'DELIVERYTIME_INTERVAL'

export const deliveryProp = {
    city: {
        code: 'CITY'
    },
    commonPvz: {
        code: 'COMMON_PVZ'
    },
    typeDelivery: {
        code: 'TYPE_DELIVERY'
    },
    address: {
        code: 'ADDRESS'
    },
    addressPvz: {
        code: 'ADDRESS_PVZ'
    },
    typePvz: {
        code: 'TYPE_PVZ'
    },
}

function OrderOshishaDelivery({result, params, sendRequest}) {

    const curDelivery = result.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
    const [showHideBlockWithDelivery, setShowHideBlockWithDelivery] = useState(false)
    const propLocation = result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')

    const propAddress = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryProp.address.code)
    const propAddressPvz = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryProp.addressPvz.code)
    const propDateDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === dateDeliveryPropCode)
    const propDeliveryInterval = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryIntervalPropCode)
    const typeDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryProp.typeDelivery.code)

    const [currentLocation, setCurrentLocation] = useState(null)

    useEffect(() => {
        axios.post("/bitrix/components/bitrix/sale.location.selector.search/get.php",
            {
                sessid: BX.bitrix_sessid(),
                select: {1: "CODE", 2: "TYPE_ID", VALUE: "ID", DISPLAY: "NAME.NAME"},
                additionals: {1: "PATH"},
                filter: {"=CODE": propLocation.VALUE[0] ?? '0000073738', "=NAME.LANGUAGE_ID": "ru", "=SITE_ID": "N2"},
                version: 2,
                PAGE_SIZE: 1,
                PAGE: 0
            },
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        ).then(response => {
            const locations = eval("(" + response.data + ")")
            if (locations.result && locations.data.ITEMS.length > 0) {
                const pathInfo = locations.data.ITEMS[0].PATH.map(path => locations.data.ETC.PATH_ITEMS[path])
                setCurrentLocation({...locations.data.ITEMS[0], PATH: pathInfo})
            } else {
                setCurrentLocation(null)
            }
        })
        window.commonDelivery.bxPopup.init(params.OSH_DELIVERY.deliveryOptions);
    }, []);

    useEffect(() => {
        if (currentLocation === null) {
            return
        }

        //todo send reqeust when load current location
        axios.post(
            ajaxDeliveryUrl,
            {
                sessid: BX.bitrix_sessid(),
                address: currentLocation.DISPLAY,
                action: 'getDaDataSuggestLocation'
            },
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
        ).then(response => {
            if (response.data.length > 0) {
                //todo not found  деревня, может найти округ или край (надо использовать гранулярные подсказки)
                const propCity = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryProp.city.code)
                if (propCity === undefined || propCity.VALUE[0] !== response.data[0].data.city) {
                    handleSelectSuggest(response.data[0])
                }
            }
        })

    }, [currentLocation])


    const handleSelectSuggest = (suggest) => {
        function setAdditionalData(additionalData, code, value) {
            const prop = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
            if (prop !== undefined) {
                additionalData[[`ORDER_PROP_${prop.ID}`]] = value;
            }
            return additionalData
        }

        const additionalData = {}
        const latitude = suggest?.data?.geo_lat ? Number('' + suggest.data.geo_lat).toPrecision(6) : ''
        const longitude = suggest?.data?.geo_lon ? Number('' + suggest.data.geo_lon).toPrecision(6) : ''

        setAdditionalData(additionalData, 'ADDRESS', suggest.value)
        setAdditionalData(additionalData, 'ZIP', suggest.data.postal_code)
        setAdditionalData(additionalData, 'CITY', suggest.data.city)
        setAdditionalData(additionalData, 'FIAS', suggest.data.fias_id)
        setAdditionalData(additionalData, 'KLADR', suggest.data.kladr_id)
        setAdditionalData(additionalData, 'STREET_KLADR', suggest?.data?.street_kladr_id ?? '')
        setAdditionalData(additionalData, 'LATITUDE', latitude)
        setAdditionalData(additionalData, 'LONGITUDE', longitude)
        additionalData[`ORDER_PROP_${propLocation.ID}`] = currentLocation.CODE

        //TODO get value for osh distanse delivery
        const dateDelivery = document.querySelector(`input[name="ORDER_PROP_${propDateDelivery.ID}"]`)?.value ?? ''

        axios.post(
            ajaxDeliveryUrl,
            {
                sessid: BX.bitrix_sessid(),
                latitude: latitude,
                longitude: longitude,
                'action': 'getSavedOshishaDelivery'
            },
            {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}).then(response => {
                if (response.data) {
                    sendRequest('refreshOrderAjax', {}, additionalData);
                } else {
                    const sendSaveDelivery = (params) => {
                        if (params === null) {
                            sendRequest('refreshOrderAjax', {}, additionalData)
                        } else {
                            axios.post(
                                ajaxDeliveryUrl,
                                {
                                    sessid: BX.bitrix_sessid(),
                                    params: params,
                                    action: 'saveOshishaDelivery'
                                },
                                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
                            ).then(sendRequest('refreshOrderAjax', {}, additionalData))
                        }
                    }

                    window.commonDelivery.oshMkadDistance.init(params.OSH_DELIVERY.deliveryOptions).then(oshMkad => {
                        oshMkad.afterSave = null;
                        oshMkad.getDistance([latitude, longitude], dateDelivery,
                            suggest.value, sendSaveDelivery);
                    })
                }
            }
        )
    }

    return (
        <div>
            <OshishaInfoDelivery
                typeDelivery={typeDelivery?.VALUE[0]}
                curDelivery={curDelivery}
                setShowHide={setShowHideBlockWithDelivery}
                date={propDateDelivery.VALUE[0]}
                address={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                    ? propAddressPvz.VALUE[0]
                    : propAddress.VALUE[0]}
            />
            <div className={showHideBlockWithDelivery ? "" : "hidden"}>
                <div className="fixed top-0 left-0 w-full h-full bg-lightOpacityWindow dark:bg-darkOpacityWindow z-50
                md:flex block justify-center items-center">
                    <div
                        className="flex flex-col md:max-w-5xl md:top-auto top-0 relative md:h-4/5 max-w-auto
                         overflow-hidden w-full bg-white dark:bg-darkBox py-7 md:px-7 px-4 md:rounded-xl rounded-0 h-full">
                        <div className='flex md:flex-row flex-col mb-3'>
                            <div className='flex-col flex-wrap flex-1 mb-6'>
                                <p className="mb-2 text-textLight dark:text-textDarkLightGray
                                font-medium uppercase md:text-sm text-xs">Способ получения</p>
                                <div className='flex items-center mb-1'>
                                    <input checked={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId} type='radio'
                                           name='DELIVERY_ID' value={params.OSH_DELIVERY.pvzDeliveryId}
                                           className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   dark:focus:ring-white dark:focus:ring-offset-white
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                           onChange={() => {
                                               sendRequest('refreshOrderAjax', {});
                                           }}/>
                                    <span className='ml-2 font-light text-sm'>Самовывоз</span>
                                </div>
                                <div className='flex items-center'>
                                    <input checked={curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId} type='radio'
                                           name='DELIVERY_ID' value={params.OSH_DELIVERY.doorDeliveryId}
                                           className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   dark:focus:ring-white dark:focus:ring-offset-white
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                           onChange={() => {
                                               sendRequest('refreshOrderAjax', {});
                                           }}/>
                                    <span className='ml-2 font-light text-sm'>Доставка курьером</span>
                                </div>
                            </div>
                            {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                                ? <div className='flex flex-row flex-1'>
                                    {propDateDelivery !== undefined
                                        ?
                                        <OrderPropDate property={propDateDelivery} className='flex flex-col'
                                                       minDate={dayjs().add(1, 'day').toDate()}/>
                                        : null
                                    }
                                    {propDeliveryInterval !== undefined
                                        ?
                                        <OrderPropSelect property={propDeliveryInterval}
                                                         className=' basis-1/2 flex flex-col'/>
                                        : null}
                                </div>
                                : null
                            }
                        </div>
                        <div className="mb-3 mt-4">
                            <OrderPropLocationCustom currentLocation={currentLocation} propLocation={propLocation}
                                                     setCurrentLocation={setCurrentLocation}/>
                        </div>

                        {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                            ? <OshishaDoorDelivery result={result} params={params} propAddress={propAddress}
                                                   setShowHideBlockWithDelivery={setShowHideBlockWithDelivery}
                                                   sendRequest={sendRequest} currentLocation={currentLocation}
                                                   handleSelectSuggest={handleSelectSuggest}/>
                            : null
                        }
                        {curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                            ? <OshishaPvzDelivery
                                cityCode={currentLocation?.CODE}
                                cityName={currentLocation?.DISPLAY}
                                result={result}
                                params={params}
                                sendRequest={sendRequest}
                                setShowHideBlockWithDelivery={setShowHideBlockWithDelivery}
                            />
                            : null
                        }
                        {
                            curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId ?
                                <div className="md:mt-5 mt-3 w-full flex justify-center">
                                    <a className='text-white text-center w-fit px-10 dark:text-textDark shadow-md
                                    dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 rounded-5 font-normal'
                                       onClick={() => {
                                           setShowHideBlockWithDelivery(false)
                                       }}>
                                        Подтвердить
                                    </a>
                                </div>
                                : false
                        }
                        <Close showHide={showHideBlockWithDelivery}
                               setShowHide={setShowHideBlockWithDelivery}/>
                    </div>
                    {listOshDeliveryProp.map(code => {
                        const property = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
                        if (property === undefined) {
                            return undefined
                        }

                        return <div className="hidden" key={code}>
                            <OrderProp key={code} property={property} disabled={false}/></div>
                    })}
                </div>
            </div>
        </div>
    )
}

OrderOshishaDelivery.propTypes = {
    result: PropTypes.object,
    params: PropTypes.object

}

export default OrderOshishaDelivery
