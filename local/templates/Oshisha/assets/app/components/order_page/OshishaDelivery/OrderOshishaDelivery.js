import React from 'react'
import PropTypes from 'prop-types'
import OshishaDoorDelivery from './OshishaDoorDelivery';
import OshishaPvzDelivery from './OshishaPvzDelivery';
import axios from 'axios';
import { ajaxDeliveryUrl } from '../OrderMain';
import OshishaDaDataAddress from './OshishaDaDataAddress';
import OrderProp from '../OrderProp';

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
    , 'DATE_DELIVERY'
    , 'DELIVERYTIME_INTERVAL'
    , 'TYPE_PVZ']

const dateDeliveryPropCode = 'DATE_DELIVERY'
const deliveryIntervalPropCode = 'DELIVERYTIME_INTERVAL'

function OrderOshishaDelivery({ result, params, afterSendReactRequest }) {

    console.log(result);
    const curDelivery = result.DELIVERY.find(delivery => delivery.CHECKED === 'Y')
    const propLocation = result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION')

    const propDateDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === dateDeliveryPropCode)
    const propDeliveryInterval = result.ORDER_PROP.properties.find(prop => prop.CODE === deliveryIntervalPropCode)

    const handleSelectSuggest = (suggest) => {

        function setAdditionalData(additionalData, code, value) {
            const propAddress = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
            if (propAddress !== undefined) {
                additionalData[[`ORDER_PROP_${propAddress.ID}`]] = value;
            }
            return additionalData
        }

        const additionalData = {}
        const latitude = suggest?.data?.geo_lat ? Number('' + suggest.data.geo_lat).toPrecision(6) : ''
        const longitude = suggest?.data?.geo_lon ? Number('' + suggest.data.geo_lon).toPrecision(6) : ''

        setAdditionalData(additionalData, 'ADDRESS', suggest.value);
        setAdditionalData(additionalData, 'ZIP', suggest.data.postal_code);
        setAdditionalData(additionalData, 'CITY', suggest.data.city);
        setAdditionalData(additionalData, 'FIAS', suggest.data.fias_id);
        setAdditionalData(additionalData, 'KLADR', suggest.data.kladr_id);
        setAdditionalData(additionalData, 'STREET_KLADR', suggest?.data?.street_kladr_id ?? '');
        setAdditionalData(additionalData, 'LATITUDE', latitude);
        setAdditionalData(additionalData, 'LONGITUDE', longitude);

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
            { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }).then(response => {
                if (response.data) {

                    BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', {},
                        afterSendReactRequest, additionalData);
                } else {
                    //TODO send request
                    window.commonDelivery.oshMkadDistance.init(params.OSH_DELIVERY.deliveryOptions).then(oshMkad => {
                        oshMkad.afterSave = null;
                        oshMkad.getDistance([latitude, longitude], dateDelivery,
                            suggest.value, true);
                    })
                }
            }
            )
    }

    return (
        <div>
            <div className='delivery-choose js__delivery-choose inline-block underline text-light-red
     dark:text-white font-semibold dark:font-medium cursor-pointer text-sm'>
                Выбрать адрес доставки
            </div>
            <div className='bg-white dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
                <div className='flex flex-row'>
                    <div className='flex-lg-row flex-md-row flex-wrap'>
                        <div className='flex items-center'>
                            <input checked={curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId} type='radio' name='delivery_type' value='Самовывоз'
                                onChange={() => {
                                    BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', {},
                                        afterSendReactRequest, { DELIVERY_ID: params.OSH_DELIVERY.pvzDeliveryId });
                                }} />
                            <span className='ml-2 text-sm'>Самовывоз</span>
                        </div>
                        <div className='flex items-center'>
                            <input checked={curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId} type='radio' name='delivery_type' value='Доставка курьером'
                                onChange={() => {
                                    BX.Sale.OrderAjaxComponent.sendRequest('refreshOrderAjax', {},
                                        afterSendReactRequest, { DELIVERY_ID: params.OSH_DELIVERY.doorDeliveryId });
                                }} />
                            <span className='ml-2 text-sm'>Доставка курьером</span>
                        </div>
                    </div>
                    {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                        ? <>{propDateDelivery !== undefined ? <OrderProp property={propDateDelivery} /> : null}
                            {propDeliveryInterval !== undefined ? <OrderProp property={propDeliveryInterval} /> : null}
                        </>
                        : null
                    }
                </div>
                <OshishaDaDataAddress handleSelectSuggest={handleSelectSuggest} />
                <a href='javascript(0)' className='link_red_button text-white text-center flex items-center 
                    justify-content-center dark:text-textDark shadow-md dark:bg-dark-red bg-light-red py-2 lg:px-16 md:px-16 px-10 rounded-5 font-bold'>
                    Подтвердить
                </a>
                {curDelivery.ID === params.OSH_DELIVERY.doorDeliveryId
                    ? <OshishaDoorDelivery result={result} params={params} afterSendReactRequest={afterSendReactRequest} />
                    : null
                }
                {curDelivery.ID === params.OSH_DELIVERY.pvzDeliveryId
                    ? <OshishaPvzDelivery
                        cityCode={propLocation.VALUE[0]}
                        result={result}
                        params={params}
                        afterSendReactRequest={afterSendReactRequest}
                    />
                    : null
                }
                {listOshDeliveryProp.map(code => {
                    const property = result.ORDER_PROP.properties.find(prop => prop.CODE === code)
                    if (property === undefined) {
                        return undefined
                    }

                    return <OrderProp key={code} property={property} disabled={false} />
                })}
            </div>

        </div>
    )
}

OrderOshishaDelivery.propTypes = {}

export default OrderOshishaDelivery
