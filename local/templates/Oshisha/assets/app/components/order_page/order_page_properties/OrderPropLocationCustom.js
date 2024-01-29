import React, {useEffect, useReducer} from "react";
import axios from "axios";

function reducer(state, action) {

    switch (action.type) {
        case 'on_change_city': {
            return {
                ...state,
                cityName: action.cityName,
                timeoutId: action.timeoutId,
                activeLocation: 0,
                openListLocations: false
            }
        }
        case 'show_list_locations': {
            return {
                ...state,
                listLocations: action.listLocations,
                activeLocation: 0,
                openListLocations: true
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
                cityName: state.listLocations[state.activeLocation].DISPLAY
            }
        }
        case 'update_city_name': {
            return {
                ...state,
                cityName: action.cityName
            }
        }
        default: {
            return state
        }
    }

}

function OrderPropLocationCustom({currentLocation, setCurrentLocation}) {

    const initialState = {
        cityName: currentLocation?.DISPLAY ?? '',
        path: '',
        timeoutId: null,
        openListLocations: false,
        activeLocation: 0,
        listLocations: [],
    }

    const [state, dispatch] = useReducer(reducer, initialState)

    useEffect(() => {
        dispatch({type: 'update_city_name', cityName: currentLocation?.DISPLAY ?? ''})
    }, [currentLocation]);

    const selectLocation = (index) => {
        dispatch({type: 'select_location'})
        setCurrentLocation(state.listLocations[index])
    }
    const onSelectLocation = (e) => {
        // sendRequestLocation(state.listLocations[e.target.dataset.index].CODE, e.target.innerHTML);
    }
    const onChangeLocationString = (e) => {

        const cityName = e.target.value;
        clearTimeout(state.timeoutId);
        const timeoutId = setTimeout(() => {
            axios.post("/bitrix/components/bitrix/sale.location.selector.search/get.php",
                {
                    sessid: BX.bitrix_sessid(),
                    select: {1: "CODE", 2: "TYPE_ID", VALUE: "ID", DISPLAY: "NAME.NAME"},
                    additionals: {1: "PATH"},
                    filter: {"=PHRASE": e.target.value, "=NAME.LANGUAGE_ID": "ru", "=SITE_ID": "N2"},
                    version: 2,
                    PAGE_SIZE: 10,
                    PAGE: 0
                },
                {headers: {'Content-Type': 'application/x-www-form-urlencoded'}}
            ).then(response => {
                const responseData = eval("(" + response.data + ")")

                if (responseData.result) {
                    const listLocations = responseData.data.ITEMS.map(location => {
                        const pathInfo = location.PATH.map(path => responseData.data.ETC.PATH_ITEMS[path])
                        return {...location, PATH: pathInfo}
                    })
                    dispatch({type: 'show_list_locations', listLocations})
                } else {
                    setCurrentLocation(null)
                }
            })
        }, 800)

        dispatch({type: 'on_change_city', cityName, timeoutId})

    }

    const onKeyDownLocation = (e) => {
        if (e.keyCode === 13) {
            selectLocation(state.activeLocation);

        } else if (e.keyCode === 38) {
            if (state.activeLocation === 0) {
                return
            }

            dispatch({type: 'set_active_location', activeLocation: state.activeLocation - 1})
        } else if (e.keyCode === 40) {
            if (state.activeLocation === state.listLocations.length - 1) {
                return
            }

            dispatch({type: 'set_active_location', activeLocation: state.activeLocation + 1})
        }
    }

    return (
        <div>
            <div className='title font-medium mb-[0.8em] uppercase'>
                Выберите город:
            </div>
            <div>
                <input value={currentLocation?.CODE ?? ''} type={"hidden"} name='ORDEP_PROP_'/>
                <input value={state.cityName} onKeyDown={onKeyDownLocation}
                       onChange={onChangeLocationString}
                       autoComplete="nope"
                       className='form-control min-width-700 w-full text-sm cursor-text
                 border-grey-line-order ring:grey-line-order dark:border-grayButton rounded-lg dark:bg-grayButton'/>
                <ul className={` ${state.openListLocations ? '' : 'hidden'}`}>
                    {state.listLocations.map((location, index) => <li
                        className={`${state.activeLocation === index ? 'dark:bg-grayButton' : ''}`}
                        key={index} onClick={onSelectLocation} data-index={index}>
                        {location.DISPLAY}, {location?.PATH.map(path => path.DISPLAY).join(', ')}
                    </li>)}
                </ul>
            </div>
        </div>
    );
}


export default OrderPropLocationCustom;