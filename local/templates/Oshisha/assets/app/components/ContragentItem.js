import React, {useState} from 'react';
import IconNameContr from "./IconNameContr";

const active = 'Активен';
const notAccept = 'Не подтвержден';
const archive = 'Архивирован';
const waitAccept = 'Ожидает подтверждения';

function StatusContr(status) {
    const statusColor = status === active
        ? ' dark:bg-greenButton bg-greenLight text-white font-normal '
        : status === notAccept || status === archive
            ? 'bg-grayLight text-white font-normal'
            : 'bg-yellowSt text-black font-medium'

    return `${statusColor} py-2 px-4 rounded-bl-xl rounded-tr-xl w-fit text-xs flex self-end`;
}


function ContragentItem({contragent}) {
    const colorClass = StatusContr(contragent?.STATUS_VIEW)
    const [initHideBox, setInitHideBox] = useState(false)
    const [initBoxInfo, setInitBoxInfo] = useState(false)

    const arData = [
        {'name': 'Бик', 'value': contragent?.BIC},
        {'name': 'Банк', 'value': contragent?.BANK},
        {'name': 'Расчетный счет', 'value': contragent?.RASCHET_CHET},
        {'name': 'Адрес', 'value': contragent?.ADDRESS},
    ]

    return (
        <div
            className={"xs:mr-1 lg:mr-5 mb-5 dark:bg-darkBox bg-textDark lg:w-5/12 w-full flex flex-col h-fit " + (!initBoxInfo ? 'rounded-lg' : 'rounded-t-lg')}>
            <p className={colorClass}>{contragent?.STATUS_VIEW}</p>
            <div className={"pr-8 pl-8 dark:text-textDarkLightGray text-textLight " + (!initHideBox ? 'pb-8' : 'pb-3')}>
                <div>
                    <p className="mb-5 dark:font-medium font-semibold text-xl flex flex-row items-center">
                        <IconNameContr/>
                        {contragent?.NAME_ORGANIZATION}
                    </p>
                    {contragent.TYPE === 'uric' || contragent.TYPE === 'ip' ?
                        <p className="mb-4 text-sm dark:font-medium font-semibold"> ИНН <span
                            className="dark:font-extralight font-normal ml-3">{contragent?.INN}</span>
                        </p>
                        :
                        <p className="mb-4 text-sm dark:font-medium font-semibold"> Email <span
                            className="dark:font-extralight font-normal ml-3">{contragent?.EMAIL}</span>
                        </p>
                    }
                    <p className="text-sm dark:font-medium font-semibold"> Телефон
                        <span className="dark:font-extralight font-normal ml-3">{contragent?.PHONE_COMPANY}</span>
                    </p>
                </div>
            </div>
            <div className={!initHideBox ? 'hidden' : 'dark:text-textDarkLightGray text-textLight relative pr-8 pl-8'}>
                {
                    initHideBox ?
                        <p className={"p-2 mt-2 mb-3 w-full cursor-pointer flex items-center justify-center" +
                            (initBoxInfo ? ' rotate-180' : '')} onClick={() => {
                            setInitBoxInfo(!initBoxInfo)
                        }}>
                            <svg width="19" height="11" viewBox="0 0 19 11" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M1.4936 2.02622L1.49363 2.02618C1.51817 2.00104 1.54142 1.9943 1.56224 1.9943C1.58306 1.9943 1.60632 2.00104 1.63085 2.02618L1.63087 2.0262L7.32807 7.86484C7.32807 7.86484 7.32807 7.86484 7.32807 7.86485C8.35038 8.91254 10.0213 8.91306 11.0439 7.86484C11.044 7.8648 11.044 7.86476 11.044 7.86473L16.7459 2.02115L16.746 2.02112C16.7705 1.99601 16.7937 1.98926 16.8146 1.98926C16.8354 1.98926 16.8586 1.99601 16.8831 2.02112L16.8833 2.02125C16.9093 2.04793 16.9269 2.08656 16.9269 2.13541C16.9269 2.18427 16.9093 2.22287 16.8833 2.24953L16.8832 2.24966L10.2212 9.07724C10.2212 9.07726 10.2212 9.07729 10.2212 9.07731C9.64783 9.66473 8.73139 9.66495 8.15777 9.0777C8.15777 9.07769 8.15776 9.07769 8.15775 9.07768L1.49359 2.25464C1.49356 2.25461 1.49354 2.25459 1.49351 2.25456C1.46755 2.22791 1.44995 2.18931 1.44995 2.14044C1.44995 2.09152 1.46758 2.05288 1.4936 2.02622Z"
                                    fill="#8B8B8B" stroke="#8B8B8B" strokeWidth="2.5"/>
                            </svg>
                        </p> : false
                }
                {
                    <div className={!initBoxInfo ?
                        'hidden' :
                        'pt-4 absolute dark:bg-darkBox bg-textDark w-full pb-4 pr-8 pl-8 left-0 rounded-b-lg shadow-lg'}>
                        {
                            arData?.map((item, i) => {
                                if (item.value !== '' && item.value !== null) {
                                    if (!initHideBox) {
                                        setInitHideBox(true)
                                    }
                                    return (
                                        <p key={i}
                                           className="mb-4 text-sm dark:font-medium font-semibold">{item.name}
                                            <span className="dark:font-extralight font-normal ml-3">{item.value}</span>
                                        </p>
                                    )
                                }
                            })
                        }
                    </div>
                }
            </div>
        </div>
    );
}

export default ContragentItem;