import React from 'react'
import PropTypes from 'prop-types'

export default function OshishaInfoDelivery({curDelivery, listOshDelivery, address, date}) {

    const oshDelivery = listOshDelivery.find(item => item.checked)

    return (
        <div className='border border-light-red rounded-[10px] dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
            <div className='font-medium dark:font-normal text-xl mb-4 dark:text-white'>
                Укажите адрес и способ доставки
            </div>
            <div className='row mb-3 flex flex-wrap'>
                {curDelivery.PRICE_FORMATED !== undefined
                    ? <div>
                        <div className='col-md-6 col-lg-6 col-12 basis-1/2'>
                            <span className='col-md-6 col-lg-6 col-12 basis-1/2'>Способ доставки:</span>
                            <span className='ml-2 font-lg-13'>{oshDelivery?.name}</span>
                        </div>
                        <div className='col-md-6 col-lg-6 col-12 basis-1/2'>
                            <span className='col-md-6 col-lg-6 col-12 basis-1/2'>Стоимость:</span>
                            <span className='ml-2 font-lg-13'>{curDelivery.PRICE_FORMATED}</span>
                        </div>
                        <div className='col-md-6 col-lg-6 col-12 basis-1/2'>
                            <span className='col-md-6 col-lg-6 col-12 basis-1/2'>Адрес:</span>
                            <span className='ml-2 font-lg-13'>{address}</span>
                        </div>
                        <div className='col-md-6 col-lg-6 col-12 basis-1/2'>
                            <span className='col-md-6 col-lg-6 col-12 basis-1/2'>Предпочтительная дата получения:</span>
                            <span className='ml-2 font-lg-13'>{date}</span>
                        </div>
                    </div>
                    : <div>
                        <p>Выберите один из подходящих Вам вариантов:</p>
                        <p>самовывоз, пункт выдачи заказов или доставка курьером до двери</p>
                    </div>
                }
            </div>
            <div className='inline-block underline text-light-red dark:text-white font-semibold dark:font-medium cursor-pointer text-sm'>
                Выбрать адрес
            </div>
        </div>
    )
}

OshishaInfoDelivery.propTypes = {
    curDelivery: PropTypes.object.isRequired,
    listOshDelivery: PropTypes.array.isRequired,
    address: PropTypes.string.isRequired,
    date: PropTypes.string.isRequired
}