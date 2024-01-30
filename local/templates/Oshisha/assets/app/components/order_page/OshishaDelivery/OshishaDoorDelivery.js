import React from 'react'
import PropTypes from 'prop-types'
import { listOshDeliveryProp } from './OrderOshishaDelivery';
import OrderProp from '../OrderProp';
import MapMarker from './icon/MapMarker';
import Track from './icon/Track';

function OshishaDoorDelivery({ result, params, sendRequest }) {

    const doorDelivery = result.DELIVERY.find(delivery =>
        delivery.ID === params.OSH_DELIVERY.doorDeliveryId && delivery.CHECKED === 'Y'
    )

    const propTypeDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')
    const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION);

    return (
        <div>
            <div className='w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap'>
                <div className='basis-1/2'>
                    <MapMarker />
                    <span className="font-medium text-[15px]">Доставка + цена </span>
                </div>
                <div className='basis-1/2'>
                    <Track />
                    <span className="font-medium text-[15px]">Срок доставки </span>
                </div>
            </div>
            <div className='w-full px-[15px] pt-3 mx-auto overflow-auto my-2 border border-x-0 border-grey-line-order dark:border-grayLight'>
                {deliveryInfo.map(delivery => {

                    const isChecked = delivery.code === propTypeDelivery?.VALUE[0]

                    return <div key={delivery.name} className={`mt-3 p-3 flex items-center rounded-xl border border-grey-line-order
                     dark:bg-lightGrayBg ${isChecked ? 'dark:border-white' : 'dark:border-0'} `}>
                        <div className='basis-1/2 flex'>
                            <input type='radio' name='delivery' className='form-check-input radio-field form-check-input ring-0 focus:ring-0
                            focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none'
                                checked={isChecked} onChange={() => {
                                    sendRequest('refreshOrderAjax', {}, { [`ORDER_PROP_${propTypeDelivery.ID}`]: delivery.code });
                                }} />
                            <div className='ml-2 text-light-red text-lg font-semibold dark:text-white '>
                                {`${delivery.name} - ${delivery.price}`}
                            </div>
                        </div>
                        <div className='basis-1/2'>
                            от 2 дней
                        </div>
                    </div>
                })}
            </div>
        </div>
    )
}

OshishaDoorDelivery.propTypes = {}

export default OshishaDoorDelivery
