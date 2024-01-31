import React, { useContext, useRef } from "react";
import OrderContext from "./Context/OrderContext";

function OrderPayItem({ payment, selectPaySystem }) {
    return (
        <div className={`bg-textDark rounded-lg min-h-[170px] dark:bg-darkBox md:w-2/5 md:mr-4 mr-0 w-full
                ${payment.CHECKED === 'Y' ? 'bx-selected border-2 dark:border border-solid border-light-red ' +
            'dark:border-grey-line-order bg-white' : ''}`}
            onClick={ () => {
                if (payment.CHECKED === 'Y') {
                    return
                }
                selectPaySystem(payment.ID)}
            }>
            <div className="p-8 flex w-full cursor-pointer">
                <input type="checkbox" id={'ID_PAY_SYSTEM_ID_' + payment.ID}
                    name="PAY_SYSTEM_ID"
                    className="bx-soa-pp-company-checkbox hidden" value={payment.ID}
                    defaultChecked={payment.CHECKED === 'Y'} />
                <div className="text-base w-full">
                    <p className={`font-medium dark:font-normal mt-2 mb-2.5 dark:text-white
                     ${payment.CHECKED === 'Y' ? 'text-light-red' : 'text-black'}`}>
                        {payment.NAME}
                    </p>
                    <div className="text-stone-600 text-sm dark:text-gray-300">{payment.DESCRIPTION}</div>
                </div>
            </div>
        </div>
    )
}

function OrderPaySystems() {
    const { result, sendRequest } = useContext(OrderContext);

    const selectPaySystem = (paymentId) => {
        BX.OrderPageComponents.startLoader();

        sendRequest('refreshOrderAjax', { PAY_SYSTEM_ID: paymentId }, { PAY_SYSTEM_ID: paymentId });
    }

    return (
        <div>
            <div className="alert alert-danger hidden"></div>
            <div className="bx-soa-pp">
                <div className='flex flex-wrap flex-row'>
                    {
                        result.PAY_SYSTEM.map(payment => <OrderPayItem key={'paysystem_block_' + payment.ID}
                                                                       payment={payment} selectPaySystem={selectPaySystem} />)
                    }
                </div>
            </div>
        </div>
    );

}

export default OrderPaySystems;