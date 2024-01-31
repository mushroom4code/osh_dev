import React from 'react';

function DropDown({openList}) {
    return (
        <svg width="20" height="10" viewBox="0 0 22 12"
             className={"absolute top-5 right-2 fill-hover-red " + (openList ? " rotate-180" : " rotate-0")}
             fill="none" xmlns="http://www.w3.org/2000/svg">
            <path
                d="M1.33637 0.650052C0.717385 1.23931 0.717385 2.19478 1.33637 2.78403L9.09042 10.1589C10.3286 11.3365 12.3349 11.336 13.5724 10.158L21.3235 2.7786C21.9426 2.18935 21.9426 1.23388 21.3235 0.644605C20.7046 0.0553138 19.7009 0.0553138 19.082 0.644605L12.4479 6.96051C11.829 7.54991 10.8253 7.54976 10.2064 6.96051L3.57787 0.650052C2.9589 0.0607606 1.95534 0.0607606 1.33637 0.650052Z"/>
        </svg>
    );
}

export default DropDown;