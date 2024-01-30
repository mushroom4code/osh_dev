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

function OrderOshishaDelivery({result, params, sendRequest}) {

    const curDelivery = result.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
    const listOshDelivery = curDelivery === null || curDelivery.CALCULATE_DESCRIPTION === ''
        ? [] : JSON.parse(curDelivery.CALCULATE_DESCRIPTION)

    const propLocation = result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')

    const propAddress = result.ORDER_PROP.properties.find(prop => prop.CODE === addressDeliveryPropCode)
    const propAddressPvz = result.ORDER_PROP.properties.find(prop => prop.CODE === addressPvzDeliveryPropCode)
    const propDateDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === dateDeliveryPropCode)
    const propDeliveryInterval = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryIntervalPropCode)

    const [currentLocation, setCurrentLocation] = useState(null)

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

    return (
        <div className='p-2 bg-white dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
            <div className='flex flex-row'>
                <div className='flex-lg-row flex-md-row flex-wrap flex-1'>
                    <div className='flex items-center'>
                        <input checked={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId} type='radio'
                               name='delivery_type' value='Самовывоз'
                               onChange={() => {
                                   sendRequest('refreshOrderAjax', {}, {DELIVERY_ID: params.OSH_DELIVERY.pvzDeliveryId});
                               }}/>
                        <span className='ml-2 text-sm'>Самовывоз</span>
                    </div>
                    <div className='flex items-center'>
                        <input checked={curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId} type='radio'
                               name='delivery_type' value='Доставка курьером'
                               onChange={() => {
                                   sendRequest('refreshOrderAjax', {}, {DELIVERY_ID: params.OSH_DELIVERY.doorDeliveryId});
                               }}/>
                        <span className='ml-2 text-sm'>Доставка курьером</span>
                    </div>
                </div>
                {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                    ? <div className='flex flex-row'> 
                        {propDateDelivery !== undefined 
                            ? <OrderPropDate property={propDateDelivery} className=' basis-1/2 flex flex-col' minDate={dayjs().add(1, 'day').toDate()}/> 
                            : null
                        }
                        {propDeliveryInterval !== undefined
                             ? <OrderPropSelect property={propDeliveryInterval} className=' basis-1/2 flex flex-col'/> 
                             : null}
                    </div>
                    : <div>

                    </div>
                }
            </div>
            <OrderPropLocationCustom currentLocation={currentLocation} propLocation={propLocation}
                                     setCurrentLocation={setCurrentLocation}/>
            <OshishaDaDataAddress currentLocation={currentLocation}
                                  address={propAddress.VALUE[0]} handleSelectSuggest={handleSelectSuggest}/>
            <a href='javascript(0)' className='text-white text-center flex items-center
                    justify-content-center dark:text-textDark shadow-md dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10 rounded-5 font-bold'>
                Подтвердить
            </a>
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
                />
                : null
            }
            <OshishaInfoDelivery
                listOshDelivery={listOshDelivery}
                curDelivery={curDelivery}
                date={propDateDelivery.VALUE[0]}
                address={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                    ? propAddressPvz.VALUE[0]
                    : propAddress.VALUE[0]}
            />
            {listOshDeliveryProp.map(code => {
                const property = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
                if (property === undefined) {
                    return undefined
                }

                return <OrderProp key={code} property={property} disabled={false}/>
            })}
        </div>
    )
}

OrderOshishaDelivery.propTypes = {
    result: PropTypes.object,
    params: PropTypes.object

}

export default OrderOshishaDelivery
