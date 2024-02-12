import React from 'react'
import PropTypes from 'prop-types'
import Car from "./icon/Car";

export default function OshishaInfoDelivery({curDelivery, typeDelivery, address, date, setShowHide}) {

    return (
        <div
            className='border border-hover-red rounded-xl md:p-7 p-4 dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
            <p className='flex flex-row mb-5'>
                <Car/>
                <span className="font-nornal dark:font-light md:text-xl text-lg dark:text-textDarkLightGray ml-3">Укажите адрес и способ доставки</span>
            </p>
            <div className="flex md:flex-row flex-col justify-between md:items-end">
                <div className='md:mb-0 mb-3'>
                    {curDelivery.PRICE_FORMATED !== undefined
                        ? <div className="flex flex-col">
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-medium'>Способ доставки:</span>
                                <span
                                    className='ml-2 md:text-md text-sm dark:font-light'>{typeDelivery || 'Не выбран'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm  mb-2">
                                <span className='font-semibold dark:font-medium'>Стоимость:</span>
                                <span
                                    className='ml-2 font-normal dark:font-light'>{curDelivery.PRICE_FORMATED || 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-medium'>Адрес:</span>
                                <span
                                    className='ml-2 md:text-md text-sm dark:font-light'>{address || 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight dark:text-textDarkLightGray md:text-md text-sm mb-2">
                                <span className='font-semibold dark:font-medium'>Предпочтительная дата получения:</span>
                                <span className='ml-2 md:text-md text-sm dark:font-light'>{date || 'Неизвестно'}</span>
                            </p>
                        </div>
                        : <div>
                            <p>Выберите один из подходящих Вам вариантов:</p>
                            <p>самовывоз, пункт выдачи заказов или доставка курьером до двери</p>
                        </div>
                    }
                </div>
                <div onClick={() => (setShowHide((prev) => !prev))}
                     className='inline-block underline text-hover-red dark:text-white font-semibold dark:font-medium cursor-pointer text-sm'>
                    Выбрать адрес
                </div>
            </div>
        </div>
    )
}

OshishaInfoDelivery.propTypes = {
    curDelivery: PropTypes.object.isRequired,
    typeDelivery: PropTypes.string.isRequired,
    address: PropTypes.string.isRequired,
    date: PropTypes.string.isRequired,
    setShowHide: PropTypes.func.isRequired
}