import React, {useState} from 'react';
import IconNameContr from "./IconNameContr";
import axios from "axios";

function RelationshipContragent({contragent, setState, setContrResult, emptyDataInputs, setResultNew}) {
    const [init, setInit] = useState(true)
    const [result, setResult] = useState('')
    const [colorRes, setColorRes] = useState('dark:text-hover-red text-hover-red')
    const handleClick = (e) => {
        e.preventDefault()
        axios.post('/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            {'ACTION': 'createRelationship', 'ID_CONTRAGENT': contragent.ID_CONTRAGENT})
            .then(res => {
                console.log(res)
                if (res.data?.success) {
                    setResult(res.data?.success)
                    setColorRes('dark:text-textDarkLightGray text-greenButton')
                    setInit(false);
                } else if (res.data?.error) {
                    setResult(res.data?.error)
                } else {
                    setResult('Вы не смогли запросить связь - попробуйте еще раз или обратитесь к менеджеру')
                }
            })
    }

    return (
        <>
            <div className={"mt-5 " + colorRes}>{result}</div>
            {init ?
                <form onSubmit={handleClick}
                      className={'dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 rounded-xl p-8 mb-10 ' +
                          'md:w-1/2 lg:w-1/2 xl:w-1/3 2xl:w-1/4 xs:w-full'}>
                    <div className="mb-10">
                        <p className="xl:text-xl lg:text-xl md:text-lg text-md font-medium dark:text-textDarkLightGray
                        text-textLight">
                            Контрагент с такими данными уже существует.<br></br>
                            Хотите запросить привязку к нему ?
                        </p>
                    </div>
                    <div className="mb-8">
                        <div className="dark:text-textDarkLightGray text-textLight">
                            <p className="mb-5 dark:font-medium font-semibold text-lg flex flex-row items-center">
                                <IconNameContr/>
                                {contragent?.NAME_ORGANIZATION}
                            </p>
                            {
                                contragent?.INN !== null ?
                                    <p className="mb-4 text-sm dark:font-normal font-semibold"> ИНН: <span
                                        className="dark:font-light font-medium ml-3">{contragent?.INN}</span>
                                    </p> :
                                    false
                            }
                            {
                                contragent?.EMAIL !== null ?
                                    <p className="mb-4 text-sm dark:font-normal font-semibold"> Email: <span
                                        className="dark:font-light font-medium ml-3">{contragent?.EMAIL}</span>
                                    </p> :
                                    false
                            }
                            <p className="text-sm dark:font-normal font-semibold"> Телефон:
                                <span className="dark:font-light font-medium ml-3">{contragent?.PHONE_COMPANY}</span>
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-row mt-4">
                        <div className="d-flex flex-row align-items-center justify-content-start w-1/2  mr-3">
                            <button className="dark:bg-greenButton rounded-md bg-greenButton text-white w-full px-7 py-3
                             dark:shadow-md shadow-shadowDark dark:hover:bg-greenLight cursor-pointer" type="submit">
                                Запросить
                            </button>
                        </div>
                        <div className="d-flex flex-row align-items-center justify-content-start w-1/2">
                            <button className="dark:bg-grayButton rounded-md bg-grayButton text-white w-full px-7 py-3
                     dark:shadow-md shadow-shadowDark dark:hover:bg-black cursor-pointer" onClick={
                                (e) => {
                                    e.preventDefault()
                                    setState(false);
                                    setInit(false);
                                    setContrResult([])
                                    emptyDataInputs()
                                    setResultNew('')
                                }
                            }>
                                Отменить
                            </button>
                        </div>
                    </div>
                </form>
                : false
            }
        </>
    )
}

export default RelationshipContragent;