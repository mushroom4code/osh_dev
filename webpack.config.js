const path = require('path');
const templatePath = 'local/templates/Oshisha';


module.exports = {
    mode: 'development',
    entry: {
        app: './'+templatePath+'/assets/app/app.js'
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'app.generated.js',
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
            }
        ]
    }
};