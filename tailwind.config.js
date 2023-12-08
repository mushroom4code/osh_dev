/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./local/templates/Oshisha/**/*.{js,php}",
        "./local/templates/Oshisha/**/.default/*.{js,php}",
        "./local/components/bitrix/**/.default/*.{js,php}",
        "./local/components/bitrix/**/*.{js,php}",
        './index.php',
        "./personal/*.php", "./personal/contragents/*.php",
        "./local/components/bitrix/enterego.slider/templates/.default/template.php",
        "./images/icon_header/*.svg",
        "./local/assets/js/flags-mask/phonecode.js",
        "./bitrix/modules/enterego.pvz/lib/CommonPVZ/*.js"
    ],
    darkMode: 'class',
    theme: {
        extend: {
            borderRadius: {
                '5': '5px',
                '7': '7px'
            },
            colors: {
                dark: '#1C1C1C',
                textLight: '#1A1A1A',
                darkBox: '#313131',
                grayButton: '#464646',
                grayLight: '#8B8B8B',
                whiteOpacity:'#FFFFFF99',
                filterGray:'#F3F3F3',
                textDarkLightGray: '#E8E8E8',
                textDark: '#F0F0F0',
                greenButton: '#0FAC2B',
                greenLight: '#0BC82D',
                yellowSt: '#FFCB13',
                borderColor:'#D9D9D9',
                grayIconLights: '#BFBFBF',
                tagFilterGray: '#5F5F5F',
                fancyboxDark: '#000000BA',
                fancybox: '#3C3C3C87',
                iconLune:'#838383',
                menuFooter: '#4F4F4F',
                iconGray: '#979797',
                'gray-product': '#CFCFCF',
                'gray-slider-arrow': '#676767',
                lightGrayBg: '#393939',
                'dark-red': '#B11512',
                hoverRedDark: '#c11715',
                shadowDark: '#00000040',
                'light-red': '#CD1D1D',
                'hover-red': '#FE3431',
                'gray-box-dark': '#3C3C3C',
                'grey-line-order': '#E5E5E5',
                darkOpacityWindow: 'rgba(0, 0, 0, 0.73)',
                lightOpacityWindow: 'rgba(60, 60, 60, 0.53)',
            },
            minHeight: {
                '550': '550px',
            },
            minWidth: {
                '164': '164px',
            },
            height: {
                '98': '26rem',
                '48-vh': '48vh'
            },
            width: {
                'inherit': 'inherit'
            },
            fontSize:{
                '13': '13px',
                '10': '10px',
            },
            backgroundImage: {
                'order_pen': "url('/local/templates/Oshisha/images/order_pen.svg')",
                'order_pen_dark': "url('/local/templates/Oshisha/images/order_pen_dark.png')",
            }
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}