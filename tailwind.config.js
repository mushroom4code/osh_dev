/** @type {import('tailwindcss').Config} */
module.exports = {
    content: ["./local/templates/Oshisha/**/**/*.{js,php}",'./index.php'],
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
                textDark: '#F0F0F0',
                'dark-red': '#b11512',
                'light-red': '#CD1D1D',
                'hover-red': '#FE3431',
                'gray-box-dark': '#313131'
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