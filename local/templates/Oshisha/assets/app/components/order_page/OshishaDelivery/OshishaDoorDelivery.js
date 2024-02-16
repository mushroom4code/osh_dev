import React from 'react'
import PropTypes from 'prop-types'
import MapMarker from './icon/MapMarker';
import Track from './icon/Track';
import OshishaDaDataAddress from './OshishaDaDataAddress';

function OshishaDoorDerliveryItem({delivery, sendRequest, propTypeDelivery, params}) {

    const isChecked = delivery.code === propTypeDelivery?.VALUE[0]
    if (delivery.price === undefined || delivery.error !== undefined) {
        return null
    }
    return <div className={`mt-3 md:p-5 p-3 flex items-center md:flex-row flex-col
                     rounded-xl border border-grey-line-order dark:bg-lightGrayBg 
                     ${isChecked ? 'dark:border-white border-light-red' : 'dark:border-0'} `}>
        <div className='md:w-1/2 w-full flex md:items-center md:mb-0 mb-1'>
            <div>
                <input type='radio' name='delivery'
                       className='form-check-input radio-field form-check-input ring-0 focus:ring-0
                            focus:ring-transparent focus:ring-offset-transparent focus:shadow-none focus:outline-none'
                       checked={isChecked} onChange={() => {
                    sendRequest('refreshOrderAjax', {}, {
                        DELIVERY_ID: params.OSH_DELIVERY.doorDeliveryId,
                        [`ORDER_PROP_${propTypeDelivery.ID}`]: delivery.code
                    });

                }}/>
            </div>
            <div className='flex flex-col ml-2 '>
                <div
                    className='text-textLight flex md:flex-row flex-col font-semibold dark:font-medium dark:text-white'>
                    {delivery.name} <span className="md:block hidden">-</span>
                    <span
                        className="text-light-red font-semibold dark:font-medium dark:text-white md:ml-2 md:mt-0 mt-1">
                                        {delivery.price}₽
                                    </span>
                </div>
                {delivery.noMarkup === undefined || delivery.noMarkup === false
                    ? null
                    : <span>{`Следующая доставка: ${delivery.noMarkup}`}</span>}
            </div>
        </div>
        <div className='md:w-1/2 w-full md:text-auto text-end'>
            от 2 дней
        </div>
    </div>
}

function OshishaDoorDelivery({
                                 result,
                                 params,
                                 sendRequest,
                                 currentLocation,
                                 handleSelectSuggest,
                                 propAddress
                             }) {

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
                className='w-full md:px-3.5 px-1 mx-auto max-h-96 overflow-auto my-2 border-t
                border-grey-line-order dark:border-grayLight'>
                {deliveryInfo.map(delivery =>
                    <OshishaDoorDerliveryItem key={delivery.name} delivery={delivery} sendRequest={sendRequest}
                                              params={params} propTypeDelivery={propTypeDelivery} />
                )}
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

OshishaDoorDerliveryItem.propTypes = {
    params: PropTypes.object,
    sendRequest: PropTypes.func,
    delivery: PropTypes.object,
    propTypeDelivery: PropTypes.object
}

export default OshishaDoorDelivery
