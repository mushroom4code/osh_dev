import React from 'react';
import IconNameContr from "./IconNameContr";

const active  = 'Активен';
const notAccept = 'Не подтвержден';
const archive = 'Архивирован';
const waitAccept = 'Ожидает подтверждения';

function StatusContr(status) {
    const statusColor = status === active
        ? ' dark:bg-greenButton bg-greenLight text-white font-normal '
        : status === notAccept || status === archive
            ? 'bg-grayLight text-white font-normal'
            : 'bg-yellowSt text-black font-medium'

    return  `${statusColor} py-2 px-4 rounded-bl-xl rounded-tr-xl w-fit text-xs flex self-end`;
}

function ContragentItem({contragent}) {
    const colorClass = StatusContr(contragent?.STATUS_VIEW)

    return (
        <div
            className="mr-5 mb-5 rounded-lg dark:bg-darkBox bg-textDark lg:w-5/12 w-full flex flex-col">
            <p className={colorClass}>{contragent?.STATUS_VIEW}</p>
            <div className="pb-8 pr-8 pl-8 dark:text-textDarkLightGray text-textLight">
                <p className="mb-5 dark:font-medium font-semibold text-xl flex flex-row items-center">
                    <IconNameContr />
                    {contragent?.NAME_ORGANIZATION}
                </p>
                <p className="mb-4 text-sm dark:font-normal font-semibold"> ИНН: <span
                    className="dark:font-light font-medium ml-3">{contragent?.INN}</span>
                </p>
                <p className="text-sm dark:font-normal font-semibold"> Телефон:
                    <span className="dark:font-light font-medium ml-3">{contragent?.PHONE_COMPANY}</span>
                </p>
            </div>
        </div>
    );
}

export default ContragentItem;