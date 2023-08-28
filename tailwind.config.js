/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./local/templates/Oshisha/**/**/*.{js,php}",
        './index.php',
        "./local/components/bitrix/enterego.slider/templates/.default/template.php"
    ],
    darkMode: 'class',
    theme: {
        extend:{
            borderRadius: {
                '5': '5px',
                '7':'7px'
            },
            colors: {
                dark: '#1C1C1C',
                textLight: '#1A1A1A',
                darkBox: '#313131',
                grayButton: '#464646',
                textDarkLightGray: '#E8E8E8',
                textDark: '#F0F0F0',
                greenButton: '#0FAC2B',
                'gray-product': '#CFCFCF',
                'gray-slider-arrow': '#676767',
                'dark-red': '#B11512',
                'light-red': '#CD1D1D',
                'hover-red': '#FE3431',
                'gray-box-dark': '#3C3C3C'
            },
            minHeight: {
                '550': '550px',
            },
            minWidth: {
                '164': '164px'
            },
            width: {
                'inherit': 'inherit'
            }
        }
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}