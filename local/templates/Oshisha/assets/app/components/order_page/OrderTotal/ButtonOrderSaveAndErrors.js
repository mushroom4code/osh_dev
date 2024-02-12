import React from "react";

function ButtonOrderSaveAndErrors({
                                      result,
                                      params,
                                      isOrderSaveAllowed,
                                      sendRequest,
                                      isValidForm,
                                      allowOrderSave,
                                      getSelectedDelivery
                                  }) {
    const curDelivery = getSelectedDelivery(result);
    const clickOrderSaveAction = (event) => {
        event.preventDefault();

        if (isValidForm()) {
            allowOrderSave();
            if (params.USER_CONSENT === 'Y' && BX.UserConsent) {
                BX.onCustomEvent('bx-soa-order-save', []);
            } else {
                doSaveAction();
            }
        }

        return BX.PreventDefault(event);
    }

    const doSaveAction = () => {
        if (isOrderSaveAllowed()) {
            sendRequest('saveOrderAjax', []);
        }
    }

    return (
        result.IS_AUTHORIZED ?
            curDelivery?.CALCULATE_ERRORS ?
                <span key={'total_error'} dangerouslySetInnerHTML={{__html: curDelivery.CALCULATE_ERRORS}}
                      className="btn-primary-color text-hover-red text-xs font-medium my-2">
                </span> :
                <div key={'total_action'} className="bx-soa-cart-total-button-container">
                    <a className="btn btn_basket mt-3 btn-order-save block shadow-md text-white w-full font-normal
                        dark:font-light text-sm dark:bg-dark-red bg-light-red py-3 px-4 rounded-5 text-center"
                       onClick={clickOrderSaveAction}>
                        Зарезервировать
                    </a>
                </div>
            :
            <span key={'total_register'} className="btn-primary-color text-hover-red font-medium text-sm my-2">
                    Для оформления заказа необходимо авторизоваться
            </span>
    )
}

export default ButtonOrderSaveAndErrors;