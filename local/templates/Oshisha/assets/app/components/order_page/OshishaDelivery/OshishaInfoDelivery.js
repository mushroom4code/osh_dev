import React from 'react'
import PropTypes from 'prop-types'
import Car from "./icon/Car";

export default function OshishaInfoDelivery({curDelivery, typeDelivery, address, date, setShowHide}) {

    return (
        <div
            className='border border-hover-red rounded-xl md:p-7 p-4 dark:bg-darkBox dark:text-white dark:border-grey-line-order'>
            <p className='flex flex-row mb-6'>
                <Car/>
                <span className="font-medium md:text-xl text-sm text-textLight dark:text-textDarkLightGray md:ml-3 ml-2">
                    Укажите адрес и способ доставки</span>
            </p>
            <div className="flex flex-col">
                <div className='md:mb-4 mb-3'>
                    {curDelivery.PRICE_FORMATED !== undefined
                        ? <div className="flex md:flex-row flex-wrap flex-col">
                            <p className="text-textLight md:w-1/2 w-full dark:text-textDarkLightGray md:text-sm text-xs mb-3">
                                <span className='font-semibold dark:font-medium'>Способ доставки:</span>
                                <span
                                    className='ml-2 dark:font-light'>{typeDelivery || 'Не выбран'}</span>
                            </p>
                            <p className="text-textLight md:w-1/2 w-full dark:text-textDarkLightGray md:text-sm text-xs mb-3">
                                <span className='font-semibold dark:font-medium'>Стоимость:</span>
                                <span
                                    className='ml-2 font-normal dark:font-light'>{curDelivery.PRICE_FORMATED || 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight md:w-1/2 w-full dark:text-textDarkLightGray md:text-sm text-xs mb-3">
                                <span className='font-semibold dark:font-medium'>Адрес:</span>
                                <span
                                    className='ml-2 dark:font-light'>{address || 'Неизвестно'}</span>
                            </p>
                            <p className="text-textLight md:w-1/2 w-full dark:text-textDarkLightGray md:text-sm text-xs mb-3">
                                <span className='font-semibold dark:font-medium'>Предпочтительная дата получения:</span>
                                <span className='ml-2 dark:font-light'>{date || 'Неизвестно'}</span>
                            </p>
                        </div>
                        : <div className="text-textLight md:w-1/2 w-full dark:text-textDarkLightGray md:text-sm text-xs mb-3">
                            <p>Выберите один из подходящих Вам вариантов:</p>
                            <p>самовывоз, пункт выдачи заказов или доставка курьером до двери</p>
                        </div>
                    }
                </div>
                <div onClick={() => (setShowHide((prev) => !prev))}
                     className='inline-block underline text-hover-red dark:text-white font-medium cursor-pointer text-sm'>
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