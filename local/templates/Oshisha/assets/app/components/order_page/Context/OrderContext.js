import {createContext, useState, useEffect, useRef} from "react";

const OrderContext = createContext();

export const OrderContextProvider = (props) => {
    const [result, setResult] = useState(props.result);
    const [params, setParams] = useState(props.params);
    const [options, setOptions] = useState(props.options);
    const [locations, setLocations] = useState(props.locations);
    const [locationProperty, setLocationProperty] = useState(props.result.ORDER_PROP.properties.find(prop => prop.CODE === 'LOCATION'));
    const [OrderGeneralUserPropsBlockId, setOrderGeneralUserPropsBlockId] =
        useState(props.OrderGeneralUserPropsBlockId);
        
    const afterSendReactRequest = (response) => {
        setResult(response.order);
        setLocations(response.locations);
    }

    const mounted = useRef();
    useEffect(() => {
        if (!mounted.current) {
            mounted.current = true;
        } else {
            BX.saleOrderAjax && BX.saleOrderAjax.initDeferredControl();
        }

        BX.OrderPageComponents.endLoader();
    });

    return <OrderContext.Provider value={{result, setResult, params, setParams, options, setOptions, locations,
        setLocations, locationProperty, setLocationProperty, OrderGeneralUserPropsBlockId,
        setOrderGeneralUserPropsBlockId, afterSendReactRequest}}>
        {props.children}
    </OrderContext.Provider>
}

OrderContextProvider.propTypes = {
    // children: PropTypes.node,
    // filesList: PropTypes.array
}
export default OrderContext