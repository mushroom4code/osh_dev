import React, {useEffect, useState} from 'react';
import axios from "axios";
import ContragentForm from "./ContragentForm";
import ContragentItem from "./ContragentItem";
import IconNameContr from "./IconNameContr";

function ContragentList() {
    const [listContragent, setListContragent] = useState([])
    const [result, setResult] = useState('')
    const [initToClick, setInitToClick] = useState(false)
    const href = '/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php'

    function getContragents() {
        axios.post(href, {'ACTION': 'getList'}).then(res => {
            if (res.data && res.data?.error === undefined) {
                setListContragent(res.data)
            } else if (res.data?.error) {
                setResult(res.data.error)
            } else {
                setResult('При создании контрагента возникла ошибка! '
                    + 'Можете обратиться к менеджеру или повторить попытку');
            }
        })
    }

    useEffect(() => {
        getContragents()
    }, []);

    return (<div>
        <ContragentForm listLength={listContragent.length} initToClick={initToClick}/>
        <div className="mt-5">{result}</div>
        { listContragent.length > 0 ?
            <div>
            <p className="text-2xl dark:text-textDarkLightGray text-textLight dark:font-normal flex flex-row
            justify-between items-center font-semibold mb-5 mt-8">
                Контрагенты
                {
                    listContragent.length > 0 ?
                        <div className="p-3 dark:bg-lightGrayBg rounded-lg w-fit bg-textDark flex flex-row items-center
                        dark:text-textDarkLightGray text-textLight"
                             onClick={() => {
                                 setInitToClick(!initToClick)
                             }}>
                            <IconNameContr width="35" height="36" color="dark:fill-grayButton fill-textDark"/>
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