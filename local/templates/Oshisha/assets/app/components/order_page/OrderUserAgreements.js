import React from "react";

function OrderUserAgreements({}) {
    return(
        <>
            <p className="d-flex flex-row align-items-center font-14">
                <input type="checkbox" required
                       className="check_input form-check-input mr-2 mt-0 checked_active_button"
                       id="soa-property-USER_RULES" defaultChecked={true} name="USER_RULES"/>
                <label className="bx-soa-custom-label m-0">
                    Я принимаю условия
                    <a className="color-redLight text-decoration-underline" href="/about/users_rules/">
                        Пользовательского соглашения
                    </a>
                </label>
            </p>
            <p className="d-flex flex-row align-items-center font-14">
                <input type="checkbox" required defaultChecked={true}
                       className="check_input form-check-input mr-2 mt-0 checked_active_button"
                       name="USER_POLITICS"/>
                <label className="bx-soa-custom-label m-0">
                    Я принимаю условия
                    <a className="color-redLight text-decoration-underline" href="/about/politics/">
                        Политики конфиденциальности
                    </a>
                </label>
            </p>
        </>
    );
}

export default OrderUserAgreements;