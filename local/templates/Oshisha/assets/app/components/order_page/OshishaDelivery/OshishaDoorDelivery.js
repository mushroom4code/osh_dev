import React from 'react'
import PropTypes from 'prop-types'
import MapMarker from './icon/MapMarker';
import Track from './icon/Track';
import OshishaDaDataAddress from './OshishaDaDataAddress';

function OshishaDoorDelivery({result, params, sendRequest, currentLocation, handleSelectSuggest, propAddress}) {

    const doorDelivery = result.DELIVERY.find(delivery =>
        delivery.ID === params.OSH_DELIVERY.doorDeliveryId && delivery.CHECKED === 'Y'
    )

    const propTypeDelivery = result.ORDER_PROP.properties.find(prop => prop.CODE === 'TYPE_DELIVERY')
    const deliveryInfo = JSON.parse(doorDelivery.CALCULATE_DESCRIPTION);

    return (
        <div>
            <OshishaDaDataAddress currentLocation={currentLocation}
                                  address={propAddress.VALUE[0]} handleSelectSuggest={handleSelectSuggest}/>
            <div className='w-full px-[15px] mx-auto lg:flex md:flex hidden flex-row flex-wrap mt-8'>
                <div className='basis-1/2'>
                    <MapMarker/>
                    <span className="font-medium dark:font-light text-textLight dark:text-textDarkLightGray text-sm">
                        Доставка + цена
                    </span>
                </div>
                <div className='basis-1/2'>
                    <Track/>
                    <span className="font-medium dark:font-light text-textLight dark:text-textDarkLightGray text-sm">
                        Срок доставки
                    </span>
                </div>
            </div>
            <div
                className='w-full px-[15px] pt-3 mx-auto lg:max-h-96 overflow-auto max-h-60 my-2 border-t border-grey-line-order dark:border-grayLight'>
                {deliveryInfo.map(delivery => {

                    const isChecked = delivery.code === propTypeDelivery?.VALUE[0]
                    if (delivery.price === undefined || delivery.error !== undefined) {
                        return null
                    }

                    return <div key={delivery.name} className={`mt-3 p-5 flex items-center rounded-xl border border-grey-line-order
                     dark:bg-lightGrayBg ${isChecked ? 'dark:border-white border-hover-red' : 'dark:border-0'} `}>
                        <div className='basis-1/2 flex'>
                            <input type='radio' name='delivery' className='form-check-input radio-field form-check-input ring-0 focus:ring-0
                            focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none'
                                   checked={isChecked} onChange={() => {
                                sendRequest('refreshOrderAjax', {}, {
                                    DELIVERY_ID: params.OSH_DELIVERY.doorDeliveryId,
                                    [`ORDER_PROP_${propTypeDelivery.ID}`]: delivery.code
                                });
                            }}/>
                            <div className='ml-2 text-textLight text- font-medium dark:font-normal dark:text-white '>
                                {delivery.name} -
                                <span className="text-hover-red dark:text-white">{delivery.price}</span>
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

OshishaDoorDelivery.propTypes = {
    result: PropTypes.object,
    params: PropTypes.object,
    sendRequest: PropTypes.func,
    currentLocation: PropTypes.object,
    handleSelectSuggest: PropTypes.func,
    propAddress: PropTypes.object
}

export default OshishaDoorDelivery
