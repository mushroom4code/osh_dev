import React, {useEffect, useState} from 'react';
import IconNameContr from "./IconNameContr";
import axios from "axios";

function RelationshipContragent({
                                    contragent,
                                    setState,
                                    setContrResult,
                                    emptyDataInputs,
                                    setResultNew,
                                    setResults,
                                    setColorResNew
                                }) {
    const [init, setInit] = useState(true)
    const [result, setResult] = useState('')
    const [colorRes, setColorRes] = useState('dark:text-hover-red text-hover-red')
    const handleClick = (e) => {
        e.preventDefault()
        axios.post('/local/templates/Oshisha/components/bitrix/sale.personal.section/oshisha_sale.personal.section/ajax.php',
            {'ACTION': 'createRelationship', 'ID_CONTRAGENT': contragent.ID_CONTRAGENT})
            .then(res => {
                if (res.data?.success) {
                    console.log('test2')
                    setResults(res.data?.success)
                    setState(false);
                    setColorResNew('dark:text-textDarkLightGray text-greenButton')
                    setInit(false);

                } else if (res.data?.error) {
                    console.log('test4')
                    setResult(res.data?.error)
                } else {
                    setResult('Вы не смогли запросить связь - попробуйте еще раз или обратитесь к менеджеру')
                }
                console.log('test3')
            })
    }

    return (
        <>
            {init ?
                <form onSubmit={handleClick}
                      className={'dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 md:rounded-xl' +
                          ' md:mb-10 mb-0 md:w-1/2 lg:w-1/2 xl:w-1/3 2xl:w-1/4 xs:w-full md:h-auto h-screen rounded-0' +
                          ' py-11 px-6 md:py-8'}>
                    <div className="mb-10">
                        <p className="xl:text-xl lg:text-xl text-lg dark:font-medium font-bold dark:text-textDarkLightGray
                        text-lightGrayBg">
                            Контрагент с такими данными уже существует.<br></br>
                            Хотите запросить привязку к нему ?
                        </p>
                    </div>
                    <div className="mb-8">
                        <div className="dark:text-textDarkLightGray text-lightGrayBg">
                            <p className="md:mb-5 mb-8 flex flex-row items-center">
                                <IconNameContr newColor="dark:fill-tagFilterGray fill-textDark" form={true}
                                               newColorIcon='fill-darkBox dark:fill-white'/>
                                <span className='dark:font-medium font-medium text-lg w-fit'>
                                    {contragent?.NAME_ORGANIZATION}</span>
                            </p>
                            {
                                contragent?.INN !== null ?
                                    <p className="mb-4 md:text-sm text-lg dark:font-normal font-semibold"> ИНН:
                                        <span className="dark:font-light font-medium ml-3">{contragent?.INN}</span>
                                    </p> :
                                    false
                            }
                            {
                                contragent?.EMAIL !== null ?
                                    <p className="mb-4 md:text-sm text-lg  dark:font-normal font-semibold"> Email:
                                        <span className="dark:font-light font-medium ml-3">{contragent?.EMAIL}</span>
                                    </p> :
                                    false
                            }
                            <p className="md:text-sm text-lg dark:font-normal font-semibold"> Телефон:
                                <span className="dark:font-light font-medium ml-3">{contragent?.PHONE_COMPANY}</span>
                            </p>
                        </div>
                    </div>
                    <div className="flex flex-row md:mt-4 mt-6">
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
                    {result !== '' ? <div className={"mt-5 " + colorRes}>{result}</div> : false}
                </form>
                : false
            }
        </>
    )
}

export default RelationshipContragent;