import React, { useEffect, useReducer } from "react";
import axios from "axios";
import Spinner from "../../elements/Spinner";

function reducer(state, action) {

    switch (action.type) {
        case 'on_change_city': {
            return {
                ...state,
                cityName: action.cityName,
                fullCityPath: '',
                timeoutId: action.timeoutId,
                activeLocation: 0,
                openListLocations: false
            }
        }
        case 'show_list_locations': {
            clearTimeout(state.timeoutId);
            return {
                ...state,
                listLocations: action.listLocations,
                timeoutId: null,
                activeLocation: 0,
                openListLocations: true,
                responseError: action.responseError
            }
        }
        case 'set_active_location': {
            return {
                ...state,
                activeLocation: action.activeLocation
            }
        }
        case 'select_location': {
            return {
                ...state,
                activeLocation: 0,
                openListLocations: false,
                cityName: state.listLocations[state.activeLocation].DISPLAY,
                fullCityPath: (state.listLocations[state.activeLocation].DISPLAY + ', '
                    + state.listLocations[state.activeLocation].PATH.map(path => path.DISPLAY).join(', ')),
                listLocations: []
            }
        }
        case 'update_city_name': {
            return {
                ...state,
                cityName: action.cityName,
                fullCityPath: action.fullCityPath
            }
        }
        case 'update_open_list_locations': {
            clearTimeout(state.timeoutId);
            return {
                ...state,
                timeoutId: null,
                listLocations: [],
                openListLocations: action.openListLocations
            }
        }
        case 'set_response_error': {
            return {
                ...state,
                timeoutId: null,
                responseError: action.responseError
            }
        }
        default: {
            return state
        }
    }

}

function OrderPropLocationCustom({ currentLocation, setCurrentLocation, propLocation }) {

    const initialState = {
        cityName: currentLocation?.DISPLAY ?? '',
        fullCityPath: currentLocation ? (currentLocation.DISPLAY + ', ' + currentLocation?.PATH.map(path => path.DISPLAY).join(', ')) : '',
        path: '',
        timeoutId: null,
        openListLocations: false,
        activeLocation: 0,
        listLocations: [],
        responseError: { error: false, data: [] },
        locationsAjaxUrl: "/bitrix/components/bitrix/sale.location.selector.search/get.php"
    }

    const [state, dispatch] = useReducer(reducer, initialState)

    useEffect(() => {
        dispatch({
            type: 'update_city_name', cityName: currentLocation?.DISPLAY ?? '',
            fullCityPath: (currentLocation?.DISPLAY + ', ' + currentLocation?.PATH.map(path => path.DISPLAY).join(', '))
        })
    }, [currentLocation]);

    const selectLocation = (index) => {
        dispatch({ type: 'select_location' })
        setCurrentLocation(state.listLocations[index])
    }

    const onSelectLocation = (index) => (event) => {
        selectLocation(index);
    }

    const onChangeLocationString = (e, isUseEffect = false) => {
        var cityName;
        cityName = e.target.value;


        clearTimeout(state.timeoutId);
        const timeoutId = setTimeout(() => {
            axios.post(state.locationsAjaxUrl,
                {
                    sessid: BX.bitrix_sessid(),
                    select: { 1: "CODE", 2: "TYPE_ID", VALUE: "ID", DISPLAY: "NAME.NAME" },
                    additionals: { 1: "PATH" },
                    filter: { "=PHRASE": cityName, "=NAME.LANGUAGE_ID": "ru", "=SITE_ID": "N2" },
                    version: 2,
                    PAGE_SIZE: 10,
                    PAGE: 0
                },
                { headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }
            ).then(response => {
                const responseData = eval("(" + response.data + ")");


                if (responseData.result) {
                    const listLocations = responseData.data.ITEMS.map(location => {
                        const pathInfo = location.PATH.map(path => responseData.data.ETC.PATH_ITEMS[path])
                        return { ...location, PATH: pathInfo }
                    })
                    dispatch({ type: 'show_list_locations', listLocations, responseError: { error: false, data: [] } });
                } else {
                    dispatch({ type: 'set_response_error', responseError: { error: true, data: responseData.errors } });
                }
            })
        }, 800);

        dispatch({ type: 'on_change_city', cityName, timeoutId })
    }

    const onKeyDownLocation = (e) => {
        if (e.keyCode === 27) {
            if (state.listLocations.length > 0 || state.responseError.error) {
                dispatch({ type: 'update_open_list_locations', openListLocations: false });
                setCurrentLocation({ ...currentLocation });
            }
        }

        if ((e.keyCode === 13) && (state.listLocations.length > 0)) {
            selectLocation(state.activeLocation);
        } else if (e.keyCode === 38) {
            if (state.activeLocation === 0) {
                return
            }


            dispatch({ type: 'set_active_location', activeLocation: state.activeLocation - 1 })
        } else if (e.keyCode === 40) {
            if (state.activeLocation === state.listLocations.length - 1) {
                return
            }

            dispatch({ type: 'set_active_location', activeLocation: state.activeLocation + 1 })
        }
    }

    const onLostFocus = (e) => {
        const currentTarget = e.currentTarget;

        requestAnimationFrame(() => {
            if (!currentTarget.contains(document.activeElement)) {
                if (state.listLocations.length > 0 || state.responseError.error) {
                    dispatch({ type: 'update_open_list_locations', openListLocations: false });
                    setCurrentLocation({ ...currentLocation });
                }
            }
        });
    }

    return (
        <div>
            <div className='title font-medium mb-[0.8em] uppercase'>
                Выберите город:
            </div>
            <div className='relative' onBlur={onLostFocus}>
                <input value={currentLocation?.CODE ?? ''} type={"hidden"} name='ORDEP_PROP_' />
                <div className='relative'>
                    <div className='relative min-h-[40px]'>
                        <input value={state.fullCityPath} readOnly={true}
                            autoComplete="nope"
                            className='form-control absolute z-[5] w-full text-gray-400 min-width-600 text-sm
                            cursor-text border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg
                             dark:bg-grayButton'/>
                        <input value={state.cityName} onKeyDown={onKeyDownLocation}
                            onChange={onChangeLocationString}
                            autoComplete="nope"
                            className='form-control absolute z-10 w-full bg-transparent min-width-600 text-sm
                            cursor-text border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg
                            dark:bg-grayButton'/>
                        {
                            state.timeoutId != null
                                ? <Spinner
                                    className={'absolute z-10 end-1.5 bottom-2.5 inline w-5 h-5 text-gray-200 animate-spin dark:text-gray-600 fill-red-600'} />
                                : null
                        }
                    </div>
                </div>
                <ul className={'absolute z-20 bg-white dark:bg-grayButton w-full p-2.5 mt-[1px] border-grey-line-order' +
                    ' border-2 rounded-lg ' + ` ${state.openListLocations ? '' : 'hidden'}`}>
                    {state.listLocations.map((location, index) => <li tabIndex='0'
                        className={`${state.activeLocation === index ? 'bg-grey-line-order dark:bg-darkBox' : ''}`
                            + ' dark:hover:bg-darkBox hover:bg-grey-line-order rounded-lg cursor-pointer pl-1'}
                        key={index} onClick={onSelectLocation(index)} data-index={index}>
                        {location.DISPLAY}, {location?.PATH.map(path => path.DISPLAY).join(', ')}
                    </li>)}
                </ul>
            </div>
        </div>
    );
}


export default OrderPropLocationCustom;