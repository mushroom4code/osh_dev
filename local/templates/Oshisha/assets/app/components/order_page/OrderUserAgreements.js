import React from "react";

function OrderUserAgreements() {
    return(
        <>
            <p className="flex items-center font-14 h-[45px] pb-2 dark:text-white">
                <input type="checkbox" required
                       className="check_input form-check-input mr-2 mt-0 checked_active_button rounded-full w-[38px]
                       h-[38px] bg-white border-textDark custom-order-checkbox"
                       id="soa-property-USER_RULES" defaultChecked={true} name="USER_RULES"/>
                <label className="bx-soa-custom-label font-semibold dark:font-normal m-0">
                    Я принимаю условия{' '}
                    <a className="text-light-red no-underline font-bold dark:font-semibold dark:text-white"
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
                <label className="bx-soa-custom-label font-semibold dark:font-normal m-0">
                    Я принимаю условия{' '}
                    <a className="text-light-red no-underline font-bold dark:font-semibold dark:text-white"
                       href="/about/politics/">
                        Политики конфиденциальности
                    </a>
                </label>
            </p>
        </>
    );
}

export default OrderUserAgreements;