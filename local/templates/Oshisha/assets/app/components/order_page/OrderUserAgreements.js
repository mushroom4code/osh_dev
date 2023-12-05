import React from "react";

function OrderUserAgreements({}) {
    return(
        <>
            <p className="flex items-center font-14 h-[45px] dark:text-white">
                <input type="checkbox" required
                       className="check_input form-check-input mr-2 mt-0 checked_active_button rounded-full w-[38px]
                       h-[38px] bg-white border-textDark custom-order-checkbox"
                       id="soa-property-USER_RULES" defaultChecked={true} name="USER_RULES"/>
                <label className="bx-soa-custom-label m-0">
                    Я принимаю условия{' '}
                    <a className="text-light-red underline dark:no-underline dark:text-white dark:font-semibold"
                       href="/about/users_rules/">
                        Пользовательского соглашения
                    </a>
                </label>
            </p>
            <p className="flex items-center font-14 h-[45px]">
                <input type="checkbox" required defaultChecked={true}
                       className="dark check_input form-check-input mr-2 mt-0 checked_active_button rounded-full w-[38px]
                       h-[38px] bg-white border-textDark custom-order-checkbox"
                       name="USER_POLITICS"/>
                <label className="bx-soa-custom-label m-0">
                    Я принимаю условия{' '}
                    <a className="text-light-red underline dark:no-underline dark:text-white dark:font-semibold"
                       href="/about/politics/">
                        Политики конфиденциальности
                    </a>
                </label>
            </p>
        </>
    );
}

export default OrderUserAgreements;