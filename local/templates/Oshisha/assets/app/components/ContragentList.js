import {useEffect, useState} from 'react';
import axios from "axios";

function ContragentList() {
    const [listContragent, setListContragent] = useState([])
    const [result, setResult] = useState('')

    console.log(listContragent)

    function getContragents() {
        axios.post('/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
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
        <div>
            <p className="text-lg dark:text-textDarkLightGray text-textLight mb-5 mt-8">Список контрагентов</p>
            <div className="flex flex-row wrap">
            {
                listContragent.map((contragent,key) =>
                    <div key={key} className="p-4 mr-5 rounded-lg dark:bg-darkBox dark:text-textDarkLightGray text-textLight w-96">
                        <p>Наименование организации: {contragent.NAME_ORGANIZATION}</p>
                        <p> ИНН: {contragent.INN}</p>
                        <p> Телефон: {contragent.PHONE_COMPANY}</p>
                    </div>
                )
            }
            </div>
        </div>
        <div className="mt-5">{result}</div>
    </div>);
}

export default ContragentList;