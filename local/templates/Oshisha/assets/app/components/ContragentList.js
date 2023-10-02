import React, {useEffect, useState} from 'react';
import axios from "axios";
import ContragentForm from "./ContragentForm";
import ContragentItem from "./ContragentItem";
import IconNameContr from "./IconNameContr";

function ContragentList() {
    const [listContragent, setListContragent] = useState([])
    const [result, setResult] = useState('')
    const [initToClick, setInitToClick] = useState(false)
    const [loads, setLoads] = useState(false)
    const [showForm, setShowForm] = useState(true)
    const [count, setCount] = useState(0);
    const [color, setColor] = useState('dark:text-hover-red text-hover-red')
    const href = '/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php'

    function getContragents() {
        axios.post(href, {'ACTION': 'getList'}).then(res => {
            if (res.data && res.data?.error === undefined) {
                setListContragent(res.data)
                setLoads(true)
            } else if (res.data?.error) {
                setResult(res.data.error)
                setLoads(true)
            } else {
                setLoads(true)
                setResult('При создании контрагента возникла ошибка! '
                    + 'Можете обратиться к менеджеру или повторить попытку');
            }
        })
    }

    useEffect(() => {
        getContragents()
    }, []);

    useEffect(() => {
        const boxPhone = $('.phoneCodeContragent');
        const boxEmail = $('.emailContragent');
        if (initToClick && boxPhone.length > 0) {
            boxPhone.phonecode({
                preferCo: 'ru',
                default_prefix: '7'
            });
            boxPhone.inputmask("+7 (999)-999-9999", {
                minLength: 10,
                clearIncomplete: true,
                definitionSymbol: "*",
                removeMaskOnSubmit: true,
                autoUnmask: true,
                clearMaskOnLostFocus: false,
                clearMaskOnLostHover: false,
            })
        } else if (initToClick && boxEmail.length > 0) {
            boxEmail.inputmask("email");
        }

    }, [initToClick]);

    return (<div className="px-5">
        <ContragentForm loads={loads} initToClick={initToClick} setState={setInitToClick}
                        listContragent={listContragent.length} setResult={setResult} setColor={setColor}
                        showForm={showForm} setShowForm={setShowForm}
        />
        <div className={"mt-5" + color}>{result}</div>
        {listContragent.length > 0 ?
            <div>
                <p className="text-2xl dark:text-textDarkLightGray text-textLight dark:font-normal flex flex-row
            justify-between items-center font-semibold mb-5">
                    Контрагенты
                    {
                        listContragent.length > 0 ?
                            <div className="p-2 dark:bg-lightGrayBg rounded-lg w-fit bg-textDark flex flex-row items-center
                        dark:text-textDarkLightGray text-textLight"
                                 onClick={() => {
                                     setInitToClick(!initToClick)
                                     setShowForm(true)
                                 }}>
                                <IconNameContr width="35" height="36" button={true} color={true}/>
                                <span className="ml-1 text-sm">Добавить</span>
                            </div>
                            : false
                    }
                </p>
                <div className="flex flex-row flex-wrap">
                    {
                        listContragent.map((contragent, keys) =>
                            <ContragentItem key={keys} contragent={contragent}/>
                        )
                    }
                </div>
            </div> : false
        }
    </div>);
}

export default ContragentList;