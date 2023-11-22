import React, {useEffect, useState} from "react";

function OrderComments({result, params}) {
    // var propsCommentContainer, label, input, div;

    // propsCommentContainer = BX.create('DIV');
    // label = BX.create('LABEL', {
    //     attrs: {for: 'orderDescription'},
    //     props: {className: 'bx-soa-customer-label'},
    //     html: this.params.MESS_ORDER_DESC
    // });
    // input = BX.create('TEXTAREA', {
    //     props: {
    //         id: 'orderDescription',
    //         cols: '4',
    //         placeholder: 'Введите комментарий к заказу...',
    //         className: 'form-control bx-soa-customer-textarea bx-ios-fix',
    //         name: 'ORDER_DESCRIPTION'
    //     },
    //     text: this.result.ORDER_DESCRIPTION ? this.result.ORDER_DESCRIPTION : ''
    // });
    // div = BX.create('DIV', {
    //     props: {className: 'form-group bx-soa-customer-field'},
    //     children: [label, input]
    // });
    //
    // propsCommentContainer.appendChild(div);
    // newBlock.appendChild(propsCommentContainer);
    console.log('commentsblock');
    return(
        <div>
            <div className="form-group bx-soa-customer-field">
                <label htmlFor="orderDescription" className="bx-soa-customer-label block mb-2 text-sm font-medium
                 text-gray-900 dark:text-white"
                       dangerouslySetInnerHTML={{__html: params.MESS_ORDER_DESC}}>
                </label>
                <textarea name="ORDER_DESCRIPTION" id="orderDescription" cols="4"
                          placeholder="Введите текст"
                          className="form-control bx-soa-customer-textarea bx-ios-fix block p-6 min-h-[8rem]
                          resize-none w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300
                          focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600
                          dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                          defaultValue={result.ORDER_DESCRIPTION ? result.ORDER_DESCRIPTION : ''}>
                </textarea>
            </div>
        </div>
    );
}

export default OrderComments;