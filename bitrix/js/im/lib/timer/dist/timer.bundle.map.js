{"version":3,"file":"timer.bundle.map.js","names":["this","BX","Messenger","exports","Timer","babelHelpers","classCallCheck","list","updateInterval","clearInterval","updateIntervalId","setInterval","worker","bind","createClass","key","value","start","name","id","arguments","length","undefined","time","callback","callbackParams","parseFloat","isNaN","dateStop","Date","getTime","has","toString","stop","skipCallback","stopAll","hasOwnProperty","clean","Lib"],"sources":["timer.bundle.js"],"mappings":"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"}