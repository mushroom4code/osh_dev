import React, {useContext} from "react";
import OrderContext  from "./Context/OrderContext";

function OrderComments() {
    const {result, params} = useContext(OrderContext);
    return(
        <div>
            <div className="form-group bx-soa-customer-field">
                <label htmlFor="orderDescription"
                       className="bx-soa-customer-label block mb-5 md:text-2xl text-xl font-medium dark:font-normal
                     text-textLight dark:text-textDarkLightGray"
                       dangerouslySetInnerHTML={{__html: params.MESS_ORDER_DESC}}>
                </label>
                <textarea name="ORDER_DESCRIPTION" id="orderDescription" cols="4"
                          placeholder="Введите текст"
                          className="form-control bx-soa-customer-textarea bx-ios-fix block p-6 min-h-[8rem]
                          resize-none w-full text-sm text-gray-900 bg-textDark rounded-lg border border-textDark
                          focus:ring-gray-300 dark:bg-darkBox dark:border-darkBox dark:focus:ring-grey-line-order
                          dark:text-white"
                          defaultValue={result.ORDER_DESCRIPTION ? result.ORDER_DESCRIPTION : ''}>
                </textarea>
            </div>
        </div>
    );
}

export default OrderComments;