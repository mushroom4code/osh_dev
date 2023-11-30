import React from "react";

function OrderComments({result, params}) {
    return(
        <div>
            <div className="form-group bx-soa-customer-field">
                <label htmlFor="orderDescription" className="bx-soa-customer-label block mb-4 text-[22px] my-4 pt-[3px] font-semibold
                 text-gray-900 dark:text-white"
                       dangerouslySetInnerHTML={{__html: params.MESS_ORDER_DESC}}>
                </label>
                <textarea name="ORDER_DESCRIPTION" id="orderDescription" cols="4"
                          placeholder="Введите текст"
                          className="form-control bx-soa-customer-textarea bx-ios-fix block p-6 min-h-[8rem]
                          resize-none w-full text-sm text-gray-900 bg-textDark rounded-lg border border-textDark
                          focus:ring-gray-300 focus:border-gray-300"
                          defaultValue={result.ORDER_DESCRIPTION ? result.ORDER_DESCRIPTION : ''}>
                </textarea>
            </div>
        </div>
    );
}

export default OrderComments;