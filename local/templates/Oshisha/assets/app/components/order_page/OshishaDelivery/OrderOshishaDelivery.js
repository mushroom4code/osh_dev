import React, {useEffect, useState} from 'react'
import PropTypes from 'prop-types'
import OshishaDoorDelivery from './OshishaDoorDelivery';
import OshishaPvzDelivery from './OshishaPvzDelivery';
import axios from 'axios';
import {ajaxDeliveryUrl} from '../OrderMain';
import OshishaDaDataAddress from './OshishaDaDataAddress';
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

const addressDeliveryPropCode = 'ADDRESS'
const addressPvzDeliveryPropCode = 'ADDRESS_PVZ'
const dateDeliveryPropCode = 'DATE_DELIVERY'
const deliveryIntervalPropCode = 'DELIVERYTIME_INTERVAL'

export const deliveryProp = {
    city: {
        code: 'CITY'
    },
    commonPvz: {
        code: 'COMMON_PVZ'
    },


}

function OrderOshishaDelivery({result, params, sendRequest}) {

    const curDelivery = result.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
    const [typePvzList, setTypePvzList] = useState('map')
    const [showHideBlockWithDelivery, setShowHideBlockWithDelivery] = useState(false)

    const listOshDelivery = curDelivery === null || curDelivery.CALCULATE_DESCRIPTION === ''
        ? [] : JSON.parse(curDelivery.CALCULATE_DESCRIPTION)

    const propLocation = result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')

    const propAddress = result.ORDER_PROP.properties.find(prop => prop.CODE === addressDeliveryPropCode)
    const propAddressPvz = result.ORDER_PROP.properties.find(prop => prop.CODE === addressPvzDeliveryPropCode)
    const propDateDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === dateDeliveryPropCode)
    const propDeliveryInterval = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryIntervalPropCode)

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

        sendRequest('refreshOrderAjax', {}, additionalData);

        //TODO get value for osh distanse delivery
        const dateDelivery = document.querySelector(`input[name="ORDER_PROP_${propDateDelivery.ID}"]`)?.value ?? ''

        // axios.post(
        //     ajaxDeliveryUrl,
        //     {
        //         sessid: BX.bitrix_sessid(),
        //         latitude: latitude,
        //         longitude: longitude,
        //         'action': 'getSavedOshishaDelivery'
        //     },
        //     {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}).then(response => {
        //         if (response.data) {
        //
        //             sendRequest('refreshOrderAjax', {}, additionalData);
        //         } else {
        //             //TODO send request
        //             window.commonDelivery.oshMkadDistance.init(params.OSH_DELIVERY.deliveryOptions).then(oshMkad => {
        //                 oshMkad.afterSave = null;
        //                 oshMkad.getDistance([latitude, longitude], dateDelivery,
        //                     suggest.value, true);
        //             })
        //         }
        //     }
        // )
    }

    const onSelectTypePvzList = (e) => {
        setTypePvzList(e.target.value)
    }

    return (
        <div>
            <OshishaInfoDelivery
                listOshDelivery={listOshDelivery}
                curDelivery={curDelivery}
                showHide={showHideBlockWithDelivery}
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
                         overflow-hidden w-full bg-white dark:bg-darkBox md:p-7 p-4 md:rounded-xl rounded-0 h-full">
                        <div className='flex flex-row mb-5'>
                            <div className='flex-col flex-wrap flex-1'>
                                <p className="mb-2 text-textLight dark:text-textDarkLightGray font-semibold
                                dark:font-medium uppercase md:text-md text-sm">Способ получения</p>
                                <div className='flex items-center mb-1'>
                                    <input checked={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId} type='radio'
                                           name='delivery_type' value='Самовывоз'
                                           className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                           onChange={() => {
                                               sendRequest('refreshOrderAjax', {}, {DELIVERY_ID: params.OSH_DELIVERY.pvzDeliveryId});
                                           }}/>
                                    <span className='ml-2 text-sm'>Самовывоз</span>
                                </div>
                                <div className='flex items-center'>
                                    <input checked={curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId} type='radio'
                                           name='delivery_type' value='Доставка курьером'
                                           className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                           onChange={() => {
                                               sendRequest('refreshOrderAjax', {}, {DELIVERY_ID: params.OSH_DELIVERY.doorDeliveryId});
                                           }}/>
                                    <span className='ml-2 text-sm'>Доставка курьером</span>
                                </div>
                            </div>
                            {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                                ? <div className='flex flex-row flex-1'>
                                    {propDateDelivery !== undefined
                                        ?
                                        <OrderPropDate property={propDateDelivery} className=' basis-1/2 flex flex-col'
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
                            {curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                                ? <>
                                    <div className='flex-1 flex flex-col'>
                                        <p className="mb-2 text-textLight dark:text-textDarkLightGray font-semibold
                                        dark:font-medium uppercase md:text-md text-sm">Показать</p>
                                        <div className="flex items-center mb-1">
                                            <input className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                                   name='TYPE_PVZ_LIST' value='map' type="radio"
                                                   onChange={onSelectTypePvzList} checked={typePvzList === 'map'}
                                            />
                                            <span className='ml-2 text-sm'>На карте</span>
                                        </div>
                                        <div className="flex items-center">
                                            <input className="ring-0 focus:ring-0 focus:ring-hover-red
                                                   focus:ring-offset-hover-red focus:outline-none form-check-input"
                                                   name='TYPE_PVZ_LIST' value='list'
                                                   type="radio"
                                                   onChange={onSelectTypePvzList} checked={typePvzList === 'list'}
                                            />
                                            <span className='ml-2 text-sm'>Списком</span>
                                        </div>
                                    </div>
                                    <div className='flex-1 flex flex-col'>
                                        <p className="mb-2 text-textLight dark:text-textDarkLightGray font-semibold
                                dark:font-medium uppercase md:text-md text-sm">Фильтрация</p>
                                    </div>
                                </>
                                : null
                            }
                        </div>
                        <div className="mb-3 hidden">
                            <a href='javascript(0)' className='text-white text-center flex items-center w-fit justify-center
                         dark:text-textDark shadow-md dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10
                         rounded-5 font-normal'>Подтвердить
                            </a>
                        </div>
                        <div className="mb-3">
                            <OrderPropLocationCustom currentLocation={currentLocation} propLocation={propLocation}
                                                     setCurrentLocation={setCurrentLocation}/>
                            <OshishaDaDataAddress currentLocation={currentLocation}
                                                  address={propAddress.VALUE[0]}
                                                  handleSelectSuggest={handleSelectSuggest}/>
                        </div>
                        <div className="lg:max-h-96 overflow-auto max-h-60">
                            {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                                ? <OshishaDoorDelivery result={result} params={params} sendRequest={sendRequest}/>
                                : null
                            }
                            {curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                                ? <OshishaPvzDelivery
                                    cityCode={currentLocation?.CODE}
                                    cityName={currentLocation?.DISPLAY}
                                    result={result}
                                    params={params}
                                    sendRequest={sendRequest}
                                    typePvzList={typePvzList}
                                />
                                : null
                            }
                        </div>
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
