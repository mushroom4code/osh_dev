import React, {useState} from 'react';
import IconNameContr from "./IconNameContr";

function RelationshipContragent({contragent, setState, setContrResult, emptyDataInputs, setResultNew}) {
    const [init,setInit] = useState(true)
    const handleClick = (e) => {
        e.preventDefault()
    }

    return (
        <>
            { init ?
                <form onSubmit={handleClick}
                      className={'dark:bg-darkBox bg-white dark:border-0 border-textDark border-2 rounded-xl p-8 mb-10 w-1/3 xs:w-full'}>
                    <div className="mb-10">
                        <p className="text-xl font-medium dark:text-textDarkLightGray text-textLight">
                            Контрагент с такими данными уже существует.<br></br>
                            Хотите запросить привязку к нему ?
                        </p>
                    </div>
                    <div className="mb-8">
                        <div className="dark:text-textDarkLightGray text-textLight">
                            <p className="mb-5 dark:font-medium font-semibold text-xl flex flex-row items-center">
                                <IconNameContr />
                                {contragent?.NAME_ORGANIZATION}
                            </p>
                            <p className="mb-4 text-md dark:font-medium font-semibold"> ИНН: <span
                                className="dark:font-light font-medium ml-3">{contragent?.INN}</span>
                            </p>
                            <p className="text-md dark:font-medium font-semibold"> Телефон:
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