import React from 'react'
import PropTypes from 'prop-types'

export default function OshishaInfoDelivery({curDelivery, listOshDelivery, address, date, showHide, setShowHide}) {

    const oshDelivery = listOshDelivery.find(item => item.checked)

    return (
        <div
            className='border border-light-red rounded-lg md:p-7 p-4 dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
            <div className='font-nornal dark:font-light md:text-xl text-lg mb-5 dark:text-textDarkLightGray'>
                Укажите адрес и способ доставки
            </div>
            <div className="flex md:flex-row flex-col justify-between items-end">
                <div className='md:mb-0 mb-3'>
                    {curDelivery.PRICE_FORMATED !== undefined
                        ? <div className="flex flex-col">
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-normal'>Способ доставки:</span>
                                <span
                                    className='ml-2 md:text-md text-sm dark:font-light'>{oshDelivery?.name ?? 'Не выбран'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm  mb-2">
                                <span className='font-semibold dark:font-normal'>Стоимость:</span>
                                <span
                                    className='ml-2 font-normal dark:font-light'>{curDelivery.PRICE_FORMATED ?? 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-normal'>Адрес:</span>
                                <span
                                    className='ml-2 md:text-md text-sm dark:font-light'>{address ?? 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-normal'>Предпочтительная дата получения:</span>
                                <span className='ml-2 md:text-md text-sm dark:font-light'>{date ?? 'Неизвестно'}</span>
                            </p>
                        </div>
                        : <div>
                            <p>Выберите один из подходящих Вам вариантов:</p>
                            <p>самовывоз, пункт выдачи заказов или доставка курьером до двери</p>
                        </div>
                    }
                </div>
                <div onClick={() => (setShowHide(!showHide))}
                     className='inline-block underline text-light-red dark:text-white font-semibold dark:font-medium cursor-pointer text-sm'>
                    Выбрать адрес
                </div>
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