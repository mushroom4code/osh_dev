import {useEffect, useState} from 'react';
import axios from "axios";
import ContragentForm from "./ContragentForm";
import ContragentItem from "./ContragentItem";

function ContragentList() {
    const [listContragent, setListContragent] = useState([])
    const [result, setResult] = useState('')
    const href = '/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php'

    function getContragents() {
        axios.post(href,
            {'ACTION': 'getList'}).then(res => {
                console.log(res)
                if (res.data) {
                    setListContragent(res.data)
                } else if (res.data?.error) {
                    setResult(res.data?.error)
                } else {
                    setResult('При создании контрагента возникла ошибка! ' +
                        'Можете обратиться к менеджеру или повторить попытку');
                }
            }
        )
    }

    useEffect(() => {
        getContragents()
    }, []);

    return (<div>
        <ContragentForm/>
        <div>
            <p className="text-2xl dark:text-textDarkLightGray text-textLight dark:font-normal font-semibold mb-5 mt-8">
                Контрагенты
            </p>
            <div className="flex flex-row flex-wrap">
                {
                    listContragent.map((contragent, keys) =>
                        <ContragentItem key={keys} contragent={contragent}/>
                    )
                }
            </div>
        </div>
        <div className="mt-5">{result}</div>
    </div>);
}

export default ContragentList;