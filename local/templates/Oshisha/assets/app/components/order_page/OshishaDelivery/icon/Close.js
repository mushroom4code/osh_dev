import React from 'react'

function Close({showHide, setShowHide}) {

    const onClickHandler = () => {
        setShowHide(!showHide)
    }

    return (
        <svg width="25" height="25" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"
             className="stroke-iconLune dark:stroke-textDarkLightGray absolute top-4 right-4 md:h-6 md:w-6 w-5 h-5"
             onClick={onClickHandler}>
            <g clipPath="url(#clip0_1167_20909)">
                <path d="M0.833374 9.16669L9.08296 0.917114" strokeWidth="1" strokeLinecap="round"
                      strokeLinejoin="round"/>
                <path d="M0.833374 0.833374L9.08296 9.08295" strokeWidth="1" strokeLinecap="round"
                      strokeLinejoin="round"/>
            </g>
        </svg>
    )
}

export default Close;