const path = require('path');
const templatePath = 'local/templates/Oshisha';


module.exports = {
    mode: 'development',
    entry: {
        app: {import:'./'+templatePath+'/assets/app/app.js', filename: 'app.generated.js'},
        order_page: {import:'./'+templatePath+'/assets/app/order_page.js', filename: 'order_page.generated.js'}
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
    },
    module:{
        rules:[   //загрузчик для jsx
            {
                test: /\.jsx?$/, // определяем тип файлов
                exclude: /(node_modules)/,  // исключаем из обработки папку node_modules
                loader: "babel-loader",   // определяем загрузчик
                options:{
                    presets:[
                        ["@babel/preset-react", {"runtime": "automatic"}]
                    ]    // используемые плагины
                }
            },
            {
                test: /\.css$/i,
                use: ["style-loader", "css-loader"],
            },
        ]
    }
};