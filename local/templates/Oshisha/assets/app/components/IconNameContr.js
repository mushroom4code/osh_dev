import React from 'react';

function IconNameContr({width, height, color, form, newColor, button, newColorIcon}) {

    return (
        <svg width={width || '50'} height={height || "51"} viewBox="0 0 50 51" fill="none"
             className={button ? 'mr-1' : 'mr-4'}
             xmlns="http://www.w3.org/2000/svg">
            <g clipPath="url(#clip0_2032_5062)">
                <rect y="0.154785" width="50" height="50.8453" rx="25"
                      className={color ? "dark:fill-tagFilterGray fill-lightGrayBg" : form ? newColor : "dark:fill-white fill-lightGrayBg"}/>
                <path
                    d="M15.7272 14.3916H33.4543C34.2043 14.3916 34.8179 15.0156 34.8179 15.7783C34.8179 16.5409 34.2043 17.165 33.4543 17.165H15.7272C14.9773 17.165 14.3636 16.5409 14.3636 15.7783C14.3636 15.0156 14.9773 14.3916 15.7272 14.3916ZM33.4543 32.4184C34.2043 32.4184 34.8179 31.7944 34.8179 31.0317V28.2584C35.5679 28.2584 36.1816 27.6344 36.1816 26.8717V25.6237C36.1816 25.5266 36.1679 25.4434 36.1543 25.3463L35.0361 19.661C34.9134 19.0231 34.3543 18.5516 33.6998 18.5516H15.4818C14.8273 18.5516 14.2682 19.0231 14.1454 19.661L13.0273 25.3463C13.0136 25.4434 13 25.5266 13 25.6237V26.8717C13 27.6344 13.6136 28.2584 14.3636 28.2584V35.1917C14.3636 35.9544 14.9773 36.5784 15.7272 36.5784H25.2726C26.0226 36.5784 26.6362 35.9544 26.6362 35.1917V28.2584H32.0907V31.0317C32.0907 31.7944 32.7043 32.4184 33.4543 32.4184ZM23.909 33.8051H17.0909V28.2584H23.909V33.8051Z"
                    className={color ? "fill-white" : form ? newColorIcon : 'dark:fill-black fill-white'}/>
                <line x1="25.5038" y1="22.6068" x2="25.5037" y2="26.8882"
                      className={color ? "stroke-white" : form ? 'stroke-darkBox dark:stroke-white' : 'dark:stroke-black stroke-white'}
                      strokeWidth="1.82" strokeLinecap="round"/>
            </g>
        </svg>);
}

export default IconNameContr;