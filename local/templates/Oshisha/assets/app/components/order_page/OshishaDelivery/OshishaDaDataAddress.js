import React, {useEffect, useReducer, useState} from 'react'
import axios from 'axios'
import PropTypes from 'prop-types'
import {ajaxDeliveryUrl} from '../OrderMain';
import OrderPropLocationCustom from "../order_page_properties/OrderPropLocationCustom";
import Spinner from '../../elements/Spinner';

function reducer(state, action) {

    switch (action.type) {
        case 'start_load': {
            return {
                ...state,
                timeoutId: action.timeoutId,
                address: action.address,
                openListSuggest: false,
                activeSuggest: 0
            }
        }
        case 'update_address': {
            return {
                ...state,
                address: action.address
            }
        }
        case 'end_loader': {
            return {
                ...state,
                timeoutId: null,
                openListSuggest: action.listSuggest.length > 0,
                listSuggest: action.listSuggest
            }
        }
        case 'set_suggest': {
            return {
                ...state,
                address: action.address,
                openListSuggest: false,
                activeSuggest: 0,
            }
        }
        case 'cancel_suggest': {
            clearTimeout(state.timeoutId)
            return {
                ...state,
                timeoutId: null,
                openListSuggest: null,
                activeSuggest: 0,
            }
        }
        case 'increase_active': {
            return {
                ...state,
                activeSuggest: state.activeSuggest + 1,
            }
        }
        case 'decrease_active': {
            return {
                ...state,
                activeSuggest: state.activeSuggest - 1,
            }
        }
        case 'set_active': {
            return {
                ...state,
                activeSuggest: action.activeSuggest
            }
        }
        default: {
            return state
        }
    }

}

function OshishaDaDataAddress({handleSelectSuggest, currentLocation, address}) {

    const initialState = {
        timeoutId: null,
        address: address,
        openListSuggest: false,
        activeSuggest: 0,
        listSuggest: [],
    }

    const [state, dispatch] = useReducer(reducer, initialState)

    useEffect(() => {
        dispatch({type: 'update_address', address})
    }, [address]);
    const selectSuggest = (index) => {
        dispatch({type: 'set_suggest', address: state.listSuggest[index].value})
        handleSelectSuggest(state.listSuggest[index])
    }

    const onKeyDownDaDataAddress = (e) => {
        if (e.keyCode === 27) {
            dispatch({type: 'cancel_suggest'});
        }

        if (e.keyCode === 13) {
            selectSuggest(state.activeSuggest)

        } else if (e.keyCode === 38) {
            if (state.activeSuggest === 0) {
                return
            }

            dispatch({type: 'decrease_active'})
        } else if (e.keyCode === 40) {
            if (state.activeSuggest === state.listSuggest.length - 1) {
                return
            }

            dispatch({type: 'increase_active'})
        }
    }

    const onChangeDaDataString = (e, isUseEffect = false) => {
        var curAddress;
        if (!isUseEffect) {
            curAddress = e.target.value;
        } else {
            if (currentLocation === null) {
                return;
            }
            curAddress = currentLocation.DISPLAY;
        }

        clearTimeout(state.timeoutId);
        const timeoutId = setTimeout(() => {
            const data = {
                sessid: BX.bitrix_sessid(),
                address: curAddress,
                action: 'getDaDataSuggest'
            }

            if (currentLocation.DISPLAY !== undefined) {
                data.locations = [{}];
                if (currentLocation.TYPE_ID === '5') {
                    data.locations[0].city = currentLocation.DISPLAY;
                } else if (currentLocation.TYPE_ID === '6') {
                    data.locations[0].settlement = currentLocation.DISPLAY.replace('деревня', '').replace('поселок')
                        .replace('хутор');
                }
                currentLocation.PATH.every((location) => {
                    if(location.TYPE_ID === '3') {
                        data.locations[0].region = location.DISPLAY.replace('область', '').replace('край', '')
                            .replace('Республика', '');
                        return false;
                    }
                    return true;
                });
            }
            axios.post(
                ajaxDeliveryUrl,
                data,
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            ).then(response => {
                dispatch({type: 'end_loader', listSuggest: response.data})
            })
        }, 800);

        dispatch({type: 'start_load', timeoutId: timeoutId, address: curAddress})
    }

    return (
        <div className='my-2'>
            <div className='title font-medium mb-2 uppercase md:text-sm text-xs'>
                Введите адрес:
            </div>
            <div className='relative'>
                <div className='flex w-full'>
                    <button
                        className="flex-shrink-0 z-10 inline-flex items-center py-2 px-4 text-sm font-medium text-center
                         text-white bg-light-red border border-light-red rounded-s-lg hover:bg-hover-red focus:ring-4
                         focus:outline-none focus:ring-[#f55150] dark:bg-dark-red dark:hover:bg-hoverRedDark
                          dark:focus:ring-hoder-red dark:border-hover-red"
                        type="button">
                        {currentLocation?.DISPLAY}
                    </button>
                    <div className="relative flex-1">
                        <input
                            value={state.address}
                            onKeyDown={onKeyDownDaDataAddress}
                            onChange={onChangeDaDataString}
                            onBlur={() => dispatch({type: 'cancel_suggest'})}
                            autoComplete="nope"
                            className='form-control w-full text-sm cursor-text p-2.5
                        border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-r-lg dark:bg-grayButton'
                        />
                        {
                            state.timeoutId != null
                                ? <Spinner
                                    className={'absolute end-1.5 bottom-2.5 inline w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-red-600'}/>
                                : null
                        }
                    </div>
                </div>

                <ul className={'absolute z-30 mt-1 max-h-56 w-full overflow-auto rounded-md bg-white py-1 focus:outline-none ' +
                    'text-base shadow-lg dark:shadow-shadowDark ring-1 ring-black ring-opacity-5 sm:text-sm ' +
                    '!border-grey-line-order dark:!border-darkBox dark:!bg-darkBox '
                    + ` ${state.openListSuggest ? '' : 'hidden'}`}>
                    {state.listSuggest.map((suggest, index) =>
                        <li className={`${state.activeSuggest === index ? 'bg-grey-line-order dark:bg-grayButton ' : ''}`
                            + 'py-2 pl-3 pr-9 text-textLight dark:text-textDarkLightGray font-normal ' +
                            ' dark:font-light hover:bg-textDarkLightGray dark:hover:bg-grayButton'}
                            key={index} onMouseDown={() => selectSuggest(index)}
                            onMouseOver={() => dispatch({type: 'set_active', activeSuggest: index})}>
                            {suggest.value}
                        </li>)}
                </ul>
            </div>
        </div>
    )
}

OshishaDaDataAddress.propTypes = {
    handleSelectSuggest: PropTypes.func
}

export default OshishaDaDataAddress
