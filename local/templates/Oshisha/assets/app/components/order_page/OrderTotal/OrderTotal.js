import React, {useContext} from "react";
import OrderContext from "../Context/OrderContext";
import ButtonOrderSaveAndErrors from "./ButtonOrderSaveAndErrors";
import TotalInfo from "./TotalInfo";

function OrderTotal() {
    const {
        result,
        params,
        options,
        sendRequest,
        isValidForm,
        isOrderSaveAllowed,
        allowOrderSave
    } = useContext(OrderContext);


    return (
        <>
            <h5 className="order_text lg:block hidden mb-4 text-[22px] font-semibold dark:font-normal">
                Оформление заказа
            </h5>
            <div className="flex items-center flex-row justify-between mb-3">
                <span className="md:text-xs text-10 m-0 font-medium dark:font-normal flex flex-row items-center">
                    При получении заказа, возможно, потребуется предъявить документ, подтверждающий ваш возраст.
                    <span className="confidintial bg-light-red p-1.5 ml-2 whitespace-nowrap text-white font-medium text-10
                 rounded-full text-center dark:bg-dark-red">18+</span>
                </span>
            </div>
            <div id="bx-soa-total" className="mb-3 bx-soa-sidebar">
                <div className="bx-soa-cart-total-ghost"></div>
                <div className="bx-soa-cart-total p-6 rounded-xl bg-textDark dark:bg-darkBox mb-7">
                    <TotalInfo result={result} params={params} options={options} getSelectedDelivery={getSelectedDelivery}/>
                    <ButtonOrderSaveAndErrors result={result} params={params} isOrderSaveAllowed={isOrderSaveAllowed}
                                              isValidForm={isValidForm} allowOrderSave={allowOrderSave}
                                              sendRequest={sendRequest} getSelectedDelivery={getSelectedDelivery}/>
                </div>
            </div>
            <div className="flex flex-row items-center justify-center">
                <svg width="30" height="31" viewBox="0 0 34 35" className="mr-3" xmlns="http://www.w3.org/2000/svg">
                    <path className="fill-lightGrayBg dark:fill-white"
                          d="M33.3333 17.025C33.3333 13.6578 32.3559 10.3662 30.5245 7.56642C28.6931 4.76668 26.0902 2.58454 23.0447 1.29596C19.9993 0.00737666 16.6482 -0.329775 13.4152 0.327138C10.1822 0.984051 7.21244 2.60553 4.88156 4.98651C2.55069 7.3675 0.96334 10.4011 0.320253 13.7036C-0.322834 17.0061 0.0072214 20.4293 1.26868 23.5402C2.53014 26.6511 4.66635 29.31 7.40717 31.1808C10.148 33.0515 13.3703 34.05 16.6667 34.05C21.087 34.05 25.3262 32.2563 28.4518 29.0635C31.5774 25.8707 33.3333 21.5403 33.3333 17.025ZM13.5667 23.3072L8.80001 18.1997C8.72947 18.1259 8.67296 18.0393 8.63334 17.9444C8.56257 17.8642 8.50615 17.772 8.46667 17.672C8.3785 17.4682 8.33295 17.2478 8.33295 17.025C8.33295 16.8022 8.3785 16.5818 8.46667 16.3781C8.546 16.1691 8.66494 15.9781 8.81668 15.8162L13.8167 10.7087C14.1305 10.3881 14.5562 10.208 15 10.208C15.4438 10.208 15.8695 10.3881 16.1833 10.7087C16.4972 11.0293 16.6735 11.4641 16.6735 11.9175C16.6735 12.3709 16.4972 12.8057 16.1833 13.1263L14.0167 15.3225H23.3333C23.7754 15.3225 24.1993 15.5019 24.5119 15.8212C24.8244 16.1404 25 16.5735 25 17.025C25 17.4765 24.8244 17.9096 24.5119 18.2289C24.1993 18.5481 23.7754 18.7275 23.3333 18.7275H13.9L15.9833 20.9578C16.2883 21.2851 16.4535 21.7229 16.4426 22.1746C16.4317 22.6264 16.2455 23.0553 15.925 23.3668C15.6045 23.6784 15.176 23.8471 14.7338 23.836C14.2915 23.8248 13.8717 23.6346 13.5667 23.3072Z"></path>
                </svg>
                <a href="/personal/"
                   className="font-medium text-base dark:text-textDarkLightGray text-lightGrayBg">Вернуться в корзину</a>
            </div>
        </>
    );
}

// FUNCTIONS
const getSelectedDelivery = (result) => {
    let currentDelivery = false;
    let i = 0;

    for (i in result.DELIVERY) {
        if (result.DELIVERY[i]['CHECKED'] === 'Y') {
            currentDelivery = result.DELIVERY[i];
            break;
        }
    }

    return currentDelivery;
}


export default OrderTotal;