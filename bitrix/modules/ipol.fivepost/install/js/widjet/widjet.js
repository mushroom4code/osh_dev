function ipolWidjetController(setups) {

	var defSetups = {
		label: 'iWidjet',
		params: {}
	};

	if (typeof(setups) === 'undefined') {
		setups = {};
	}

	for (var i in defSetups) {
		if (typeof (setups[i]) === 'undefined') {
			setups[i] = defSetups[i];
		}
	}

	var label = setups.label;
	var params = setups.params;

	this.options = {
		get: function (wat) {
			return options.get(wat);
		},
		set: function (value, option) {
			options.set(value, option);
		}
	};

	this.binders = {
		add: function (callback, event) {
			bindes.addBind(callback, event);
		},
		trigger: function (event, args) {
			bindes.trigger(event, args);
		}
	};

	this.states = {
		check: function (state) {
			states.check(state);
		}
	};

	this.service = {
		cloneObj: function (obj) {
			return service.cloneObj(obj);
		},
		concatObj: function (main, sub) {
			return service.concatObj(main, sub);
		},
		isEmpty: function (stf) {
			return service.isEmpty(stf);
		},
		inArray: function (wat, arr) {
			return service.inArray(wat, arr);
		},
		loadTag: function (src, mode, callback) {
			service.loadTag(src, mode, callback);
		}
	};

	this.logger = {
		warn: function (wat) {
			return logger.warn(wat);
		},
		error: function (wat) {
			return logger.error(wat);
		},
		log: function (wat) {
			return logger.log(wat);
		}
	};

	var logger = {
		warn: function (wat) {
			if (this.check('warn')) {
				console.warn(label + ": ", wat);
			}
		},

		error: function (wat) {
			if (this.check('error')) {
				console.error(label + ": ", wat);
			}
		},

		log: function (wat) {
			if (this.check('log')) {
				if (typeof (wat) === 'object') {
					console.log(label + ": ");
					for (var i in wat) {
						console.log(i, wat[i]);
					}
				} else {
					console.log(label + ": ", wat);
				}
			}
		},

		check: function (type) {
			var depthCheck = false;

			switch (type) {
				case 'warn'  :
					depthCheck = options.check(true, 'showWarns');
					break;
				case 'error' :
					depthCheck = options.check(true, 'showErrors');
					break;
				case 'log'   :
					depthCheck = options.check(true, 'showLogs');
					break;
			}

			return (
				depthCheck &&
				options.check(false, 'hideMessages')
			)
		}
	};

	var service = {
		cloneObj: function (obj) {
			var ret = false;
			if (typeof(obj) !== 'object')
				return ret;
			if (arguments.length === 1) {
				ret = {};
				for (var i in obj)
					ret[i] = obj[i];
			} else {
				ret = [];
				for (var i in obj)
					ret.push(obj[i]);
			}
			return ret;
		},

		concatObj: function (main, sub) {
			if (typeof(main) === 'object' && typeof(sub) === 'object')
				for (var i in sub)
					main[i] = sub[i];
			return main;
		},

		isEmpty: function (stf) {
			var empty = true;
			if (typeof(stf) === 'object')
				for (var i in stf) {
					empty = false;
					break;
				}
			else
				empty = (stf);
			return empty;
		},

		inArray: function (wat, arr) {
			return arr.filter(function (item) {
				return item == wat
			}).length;
		},

		loadTag: function (src, mode, callback) {
			var loadedTag = false;
			if (typeof(mode) === 'undefined' || mode === 'script') {
				loadedTag = document.createElement('script');
				loadedTag.src = src;
				loadedTag.type = "text/javascript";
				loadedTag.language = "javascript";
			} else {
				loadedTag = document.createElement('link');
				loadedTag.href = src;
				loadedTag.rel = "stylesheet";
				loadedTag.type = "text/css";
			}
			var head = document.getElementsByTagName('head')[0];
			head.appendChild(loadedTag);
			if (typeof(callback) !== 'undefined') {
				loadedTag.onload = callback;
				loadedTag.onreadystatechange = function () {
					if (this.readyState === 'complete' || this.readyState === 'loaded')
						loadedTag.onload();
				};
			}
		}
	};

	var options = {
		self: this,
		options: {
			showWarns: {
				value: true,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			showErrors: {
				value: true,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			showLogs: {
				value: true,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			hideMessages: {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			}
		},

		check: function (value, option, isStrict) {
			var given = this.get(option);
			if (given === null) {
				return null;
			} else {
				if (typeof (isStrict) === 'undefined') {
					return (value === given);
				} else {
					return (value == given);
				}
			}
		},

		get: function (wat) {
			if (typeof(this.options[wat]) !== 'undefined') {
				return this.options[wat].value;
			} else {
				logger.warn('Undefined option "' + wat + '"');
				return null;
			}
		},

		set: function (value, option) {
			if (typeof(this.options[option]) === 'undefined') {
				logger.warn('Undefined option to set : ' + option);
			} else {
				if (
					typeof(this.options[option].check) !== 'function' ||

					this.options[option].check.call(this.self, value)
				) {
					this.options[option].value = value;
				} else {
					var subhint = (typeof(this.options[option].hint) !== 'undefined' && this.options[option].hint) ? ': ' + this.options[option].hint : false;
					logger.warn('Incorrect setting value (' + value + ') for option ' + option + subhint);
				}
			}
		},

		iniSetter: function (values, called) {
			for (var i in options.options) {
				if (
					options.options[i].setting === called &&
					typeof(values[i]) !== 'undefined'
				) {
					options.set(values[i], i);
				}
			}
		}
	};

	var bindes = {
		events: {
			onStart: []
		},

		trigger: function (event, args) {
			if (typeof(this.events[event]) === 'undefined') {
				logger.error('Unknown event ' + event);
			} else {
				if (this.events[event].length > 0) {

					for (var i in this.events[event]) {
						this.events[event][i](args);
					}
				}
			}
		},

		iniSetter: function (params) {
			for (var i in this.events) {
				if (this.events.hasOwnProperty(i)) {
					if (typeof(params[i]) !== 'undefined') {
						if (typeof (params[i]) === 'object') {
							for (var j in params[i]) {
								this.addBind(params[i][j], i);
							}
						} else {
							this.addBind(params[i], i);
						}
					}
				}
			}
		},

		addBind: function (callback, event) {
			if (typeof(callback) === 'function') {
				this.events[event].push(callback);
			} else {
				logger.warn('The callback "' + callback + '" for ' + event + ' is not a function');
			}

		}
	};

	var states = {
		self: this,
		states: {start: {_start: false}},

		check: function (state) {
			var founded = false;
			for (var quenue in this.states) {
				for (var qStates in this.states[quenue]) {
					if (qStates === state) {
						this.states[quenue][qStates] = true;
						founded = quenue;
					}
				}
				if (founded)
					break;
			}

			if (founded) {
				var ready = true;
				for (var i in this.states[founded]) {
					if (!this.states[founded][i]) {
						ready = false;
						break;
					}
				}
				if (ready) {
					if (typeof(loaders[founded]) !== 'undefined') {
						options.iniSetter(params, founded);
						loaders[founded].call(this.self, params);
					}
				}
			} else {
				if (state === 'started')
					logger.error('No callbacks for starting');
				else
					logger.error('Unknown state of loading: ' + state);
			}
		}
	};

	var loaders = {
		'start': function (params) {
			bindes.iniSetter(params);
			bindes.trigger('onStart');
			states.check('started');
		}
	};

	var loadingSetups = {
		'options': 'object',
		'states': 'object',
		'loaders': 'funciton',
		'stages': 'object',
		'events': 'string'
	};

	for (var i in loadingSetups) {
		if (typeof(setups[i]) !== 'undefined') {
			for (var j in setups[i]) {
                if (({}).hasOwnProperty.call(setups[i], j)) {
                    if (typeof(setups[i][j]) !== loadingSetups[i]) {
                        logger.error('Illegal ' + i + ' "' + j + '": ' + setups[i][j]);
                    } else {
                        switch (i) {
                            case 'options' :
                                options.options[j] = service.cloneObj(setups.options[j]);
                                break;
                            case 'states'  :
                                states.states[j] = service.cloneObj(setups.states[j]);
                                break;
                            case 'loaders' :
                                loaders[j] = setups.loaders[j];
                                break;
                            case 'events'  :
                                bindes.events[setups.events[j]] = [];
                                break;
                            case 'stages'  :
                                if (typeof(setups.stages[j].states) !== 'object' || typeof(setups.stages[j].function) !== 'function') {
                                    logger.error('Illegal stage "' + j + '": ' + setups[i][j]);
                                } else {
                                    states.states[j] = service.cloneObj(setups.stages[j].states);
                                    loaders[j] = setups.stages[j].function;
                                }
                                break;
                        }
                    }
                }
			}
		}
	}

	states.check('_start');
}

function IPOL_FIVEPOST_Widjet(params) {

	if (!params.path) {
		var scriptPath = document.getElementById('IPOL_FIVEPOST_Widjet').src;
		scriptPath = scriptPath.substring(0, scriptPath.indexOf('widjet.js')) + 'scripts/';
		params.path = scriptPath;
	}

	if (!params.servicepath) {
		params.servicepath = params.path + 'service.php';
	}

	if (!params.templatepath) {
		params.templatepath = params.path + 'template.php';
	}

	if (!params.imagesPlaceMark) {
		params.imagesPlaceMark = {
			'ISSUE_POINT' : 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNzQgMjYyIj48cGF0aCBkPSJNODcuMDAxIDI1NS40NjhsLTQuODA5LTcuODc1Yy0yLjIyOS0zLjY0OC01NC43NDMtODkuNzE3LTcwLjA0Ni0xMjEuOTYyLTQuMDg0LTguNjA3LTguNDU5LTI0LjYwMy04LjQ1OS0zNC43IDAtNDguNDI2IDM2LjU5NS04Ni4zNiA4My4zMTQtODYuMzYgNDYuNzE4IDAgODMuMzEzIDM3LjkzNCA4My4zMTMgODYuMzYgMCAxMC4wOTgtNC4zNzMgMjYuMDk0LTguNDU2IDM0LjctMTUuMzA0IDMyLjI0NS02Ny44MTcgMTE4LjMxNC03MC4wNDcgMTIxLjk2Mkw4NyAyNTUuNDY4eiIgZmlsbD0iIzYyQkI0NiIvPjxwYXRoIGQ9Ik04NyAyNjJsLTUuMDIyLTguMjIzYy0yLjMyNi0zLjgxMS01Ny4xNjUtOTMuNjg4LTczLjE0NS0xMjcuMzU5QzQuNTY2IDExNy40MzEgMCAxMDAuNzI3IDAgOTAuMTg0IDAgMzkuNjE0IDM4LjIxNSAwIDg3IDBzODcgMzkuNjE0IDg3IDkwLjE4NGMwIDEwLjU0My00LjU2NiAyNy4yNDgtOC44MzEgMzYuMjM0LTE1Ljk4IDMzLjY3MS03MC44MTcgMTIzLjU0OC03My4xNDYgMTI3LjM1OUw4Ny4wMDEgMjYyem0wLTI1MC4xMTNjLTQyLjE3IDAtNzUuMjA2IDM0LjM5MS03NS4yMDYgNzguMjk2IDAgOC41NDQgNC4wNTQgMjMuNDY4IDcuNjggMzEuMTA1QzMyLjQgMTQ4LjUyNCA3Mi43NyAyMTUuNzYxIDg3IDIzOS4yNzJjMTQuMjMxLTIzLjUxMiA1NC42MDQtOTAuNzUxIDY3LjUyOS0xMTcuOTg0IDMuNjIzLTcuNjM2IDcuNjc3LTIyLjU2MSA3LjY3Ny0zMS4xMDUgMC00My45MDQtMzMuMDM1LTc4LjI5Ni03NS4yMDUtNzguMjk2eiIgZmlsbD0iIzU4NTk1QiIvPjxwYXRoIGQ9Ik04NyAxNTEuMDU0Yy0yMy4zNiAwLTQ0LjQ1Ni0xMy4zMDgtNTQuODM2LTMzLjg2Mi0yLjA1Ny00LjA2Ny0uMTE4LTkuMDI2IDQuMTY1LTEwLjUxNSAzLjQ4NC0xLjIxIDcuMzQ2LjMyMyA5LjAxIDMuNjM3IDcuODYgMTUuNjQ3IDIzLjg5OCAyNS43ODUgNDEuNjYyIDI1Ljc4NSAxNy43NjcgMCAzMy44MDMtMTAuMTM4IDQxLjY2Mi0yNS43ODUgMS42NjUtMy4zMTQgNS41MjgtNC44NDcgOS4wMTEtMy42MzcgNC4yODMgMS40ODkgNi4yMjEgNi40NDggNC4xNjUgMTAuNTE1LTEwLjM4MSAyMC41NTQtMzEuNDc1IDMzLjg2Mi01NC44MzggMzMuODYyeiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Ik0xMTUuMTQ2IDg5LjI1OGMwIDE0LjkwOS0xMi4yNzIgMjUuMjg4LTI3LjcyIDI1LjI4OC0xMC40MSAwLTE4Ljk1NS00LjYzOS0yNC4yMTUtMTEuODE2LS43NjgtMS4xMDUtMS4zMTYtMi42NS0xLjMxNi00LjA4NiAwLTMuNzUzIDMuMDY5LTYuODQ1IDYuOTA1LTYuODQ1IDEuOTcgMCAzLjgzMy44ODMgNC45MyAyLjIwOCAzLjUwNyA0LjE5NyA3LjU2IDYuNzM1IDEzLjI1NyA2LjczNSA3LjU2MSAwIDEyLjkyOS00LjQxNiAxMi45MjktMTEuMjYzIDAtNi44NDctNS4wNC0xMC40OS0xNC43OTItMTAuNDloLTkuNDIyYy00LjI3MyAwLTcuNDQ5LTMuMjAzLTcuNDQ5LTcuNTA5VjQ3Ljg1YzAtNC4zMDYgMy4xNzYtNy41MSA3LjQ0OS03LjUxaDI4LjkyN2MzLjk0MyAwIDYuNzkyIDIuODcxIDYuNzkyIDYuNzM2IDAgMy43NTQtMi44NDkgNi43MzYtNi43OTIgNi43MzZIODIuNDk2djEyLjM2N2g3LjU1OWMxMy42OTguMDAxIDI1LjA5MSA4LjgzNiAyNS4wOTEgMjMuMDh6IiBmaWxsPSIjZmZmIi8+PC9zdmc+',
			'TOBACCO' : 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNzQgMjYyIj48cGF0aCBkPSJNODQuNDA1IDI1Mi41NmMtMi4zMjMtMy44MDEtNTcuMDYxLTkzLjUwNS03My4wMDItMTI3LjA5Mi00LjEzLTguNzAyLTguNTU0LTI0Ljg0MS04LjU1NC0zNC45OTYgMC00OC45NiAzNi45NjQtODcuMzEyIDg0LjE1Mi04Ny4zMTIgNDcuMTg2IDAgODQuMTQ5IDM4LjM1MyA4NC4xNDkgODcuMzEyIDAgMTAuMTU0LTQuNDIxIDI2LjI5My04LjU1MSAzNC45OTYtMTYuMDIzIDMzLjc1Ny03MC42ODMgMTIzLjI5Ni03My4wMDIgMTI3LjA5MmwtMi41OTYgNC4yNS0yLjU5Ni00LjI1eiIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Ik04NyAyNjJsLTUuMDIyLTguMjIzYy0yLjMyNi0zLjgxLTU3LjE2NS05My42ODgtNzMuMTQ1LTEyNy4zNTlDNC41NjYgMTE3LjQzMSAwIDEwMC43MjYgMCA5MC4xODMgMCAzOS42MTMgMzguMjE1IDAgODcgMGM0OC43ODQgMCA4NyAzOS42MTQgODcgOTAuMTgzIDAgMTAuNTQ0LTQuNTY2IDI3LjI0OS04LjgzMSAzNi4yMzUtMTUuOTggMzMuNjcxLTcwLjgxOCAxMjMuNTQ5LTczLjE0NiAxMjcuMzU5TDg3LjAwMSAyNjJ6bTAtMjUwLjExNGMtNDIuMTcgMC03NS4yMDcgMzQuMzkyLTc1LjIwNyA3OC4yOTYgMCA4LjU0NCA0LjA1NCAyMy40NyA3LjY4IDMxLjEwNSAxMi45MjUgMjcuMjM4IDUzLjI5NyA5NC40NzQgNjcuNTI4IDExNy45ODUgMTQuMjI5LTIzLjUxMiA1NC42MDItOTAuNzUxIDY3LjUyOC0xMTcuOTg1IDMuNjIzLTcuNjM1IDcuNjc3LTIyLjU2IDcuNjc3LTMxLjEwNCAwLTQzLjkwNS0zMy4wMzYtNzguMjk3LTc1LjIwNS03OC4yOTd6IiBmaWxsPSIjNTg1OTVCIi8+PHBhdGggZD0iTTg3IDE1MS4wNTRjLTIzLjM2IDAtNDQuNDU2LTEzLjMwOC01NC44MzYtMzMuODYxLTIuMDU3LTQuMDY3LS4xMTgtOS4wMjcgNC4xNjUtMTAuNTE1IDMuNDgzLTEuMjExIDcuMzQ2LjMyMiA5LjAxIDMuNjM2IDcuODYgMTUuNjQ4IDIzLjg5OCAyNS43ODcgNDEuNjYyIDI1Ljc4NyAxNy43NjcgMCAzMy44MDMtMTAuMTM5IDQxLjY2MS0yNS43ODcgMS42NjUtMy4zMTQgNS41MjgtNC44NDggOS4wMTItMy42MzYgNC4yODMgMS40ODggNi4yMjEgNi40NDggNC4xNjUgMTAuNTE1LTEwLjM4MSAyMC41NTMtMzEuNDc2IDMzLjg2MS01NC44MzggMzMuODYxeiIgZmlsbD0iIzYyQkI0NiIvPjxwYXRoIGQ9Ik0xMTUuMTQ2IDg5LjI2MmMwIDE0LjkwOC0xMi4yNzIgMjUuMjg3LTI3LjcyIDI1LjI4Ny0xMC40MSAwLTE4Ljk1NS00LjYzOC0yNC4yMTUtMTEuODE2LS43NjgtMS4xMDQtMS4zMTYtMi42NS0xLjMxNi00LjA4NyAwLTMuNzUzIDMuMDY5LTYuODQ1IDYuOTAzLTYuODQ1IDEuOTcxIDAgMy44MzQuODgzIDQuOTMgMi4yMDkgMy41MDggNC4xOTYgNy41NiA2LjczNCAxMy4yNTggNi43MzQgNy41NjEgMCAxMi45MjktNC40MTYgMTIuOTI5LTExLjI2MiAwLTYuODQ3LTUuMDQtMTAuNDkxLTE0Ljc5My0xMC40OTFoLTkuNDIxYy00LjI3MyAwLTcuNDUtMy4yMDMtNy40NS03LjUwOVY0Ny44NWMwLTQuMzA2IDMuMTc3LTcuNTA4IDcuNDUtNy41MDhoMjguOTI3YzMuOTQzIDAgNi43OTIgMi44NyA2Ljc5MiA2LjczNiAwIDMuNzUzLTIuODQ5IDYuNzM1LTYuNzkyIDYuNzM1SDgyLjQ5NVY2Ni4xOGg3LjU2YzEzLjY5OC4wMDEgMjUuMDkxIDguODM2IDI1LjA5MSAyMy4wODJ6IiBmaWxsPSIjNTg1OTVCIi8+PC9zdmc+',
			'POSTAMAT' : 'data:image/svg+xml;base64,PHN2ZyBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAxNzQgMjYyIj48cGF0aCBkPSJNODcgMEMzOC4yMTYgMCAwIDM5LjYxNCAwIDkwLjE4M2MwIDEwLjU0MyA0LjU2NiAyNy4yNDggOC44MzMgMzYuMjM1IDE1Ljk4IDMzLjY3MSA3MC44MTggMTIzLjU0OSA3My4xNDUgMTI3LjM1OUw4NyAyNjJsNS4wMjItOC4yMjNjMi4zMjYtMy44MSA1Ny4xNjUtOTMuNjg4IDczLjE0Ni0xMjcuMzU5IDQuMjY1LTguOTg2IDguODMxLTI1LjY5MSA4LjgzMS0zNi4yMzVDMTc0IDM5LjYxMyAxMzUuNzg1IDAgODcgMHoiIGZpbGw9IiM1ODU5NUIiLz48cGF0aCBkPSJNODcgMTUxLjA1NGMtMjMuMzYgMC00NC40NTYtMTMuMzA4LTU0LjgzNi0zMy44NjEtMi4wNTctNC4wNjctLjExOC05LjAyNyA0LjE2NS0xMC41MTUgMy40ODItMS4yMTEgNy4zNDYuMzIyIDkuMDEgMy42MzZDNTMuMTk4IDEyNS45NjIgNjkuMjM2IDEzNi4xIDg3IDEzNi4xYzE3Ljc2NyAwIDMzLjgwMy0xMC4xMzggNDEuNjYyLTI1Ljc4NiAxLjY2NS0zLjMxNCA1LjUzLTQuODQ4IDkuMDExLTMuNjM2IDQuMjgzIDEuNDg4IDYuMjIxIDYuNDQ4IDQuMTY1IDEwLjUxNS0xMC4zODEgMjAuNTUzLTMxLjQ3NSAzMy44NjEtNTQuODM4IDMzLjg2MXoiIGZpbGw9IiM2MkJCNDYiLz48cGF0aCBkPSJNMTE1LjE0NiA4OS4yNjJjMCAxNC45MDgtMTIuMjcyIDI1LjI4Ny0yNy43MiAyNS4yODctMTAuNDEgMC0xOC45NTUtNC42MzgtMjQuMjE1LTExLjgxNi0uNzY4LTEuMTA0LTEuMzE2LTIuNjUtMS4zMTYtNC4wODcgMC0zLjc1MyAzLjA2OS02Ljg0NSA2LjkwNC02Ljg0NSAxLjk3MyAwIDMuODM0Ljg4MyA0LjkzIDIuMjA5IDMuNTA4IDQuMTk2IDcuNTYgNi43MzQgMTMuMjU4IDYuNzM0IDcuNTYxIDAgMTIuOTI5LTQuNDE2IDEyLjkyOS0xMS4yNjIgMC02Ljg0Ny01LjA0LTEwLjQ5MS0xNC43OTItMTAuNDkxaC05LjQyMmMtNC4yNzMgMC03LjQ1LTMuMjAzLTcuNDUtNy41MDlWNDcuODVjMC00LjMwNiAzLjE3Ny03LjUwOCA3LjQ1LTcuNTA4aDI4LjkyN2MzLjk0MyAwIDYuNzkyIDIuODcgNi43OTIgNi43MzYgMCAzLjc1My0yLjg0OSA2LjczNS02Ljc5MiA2LjczNUg4Mi40OTNWNjYuMThoNy41NjJjMTMuNjk4LjAwMiAyNS4wOTEgOC44MzcgMjUuMDkxIDIzLjA4MnoiIGZpbGw9IiNmZmYiLz48L3N2Zz4='
		};
	}

	var loaders = {
		onJSPCSSLoad: function () {
			widjet.states.check('JSPCSS');
		},
		onStylesLoad: function () {
			widjet.states.check('JSPCSS');
			widjet.states.check('styles');
		},
		onIPJQLoad: function () {
			widjet.states.check('jquery')
		},
		onJSPJSLoad: function () {
			widjet.states.check('JSPJS')
		},
		onPVZLoad: function () {
			widjet.states.check('PVZ')
		},
		onDataLoad: function () {
			widjet.states.check('data')
		},
		onLANGLoad: function () {
			widjet.states.check('lang')
		},
		onDocumentLoad : function(){
            widjet.states.check('document')
		},
		onYmapsLoad: function () {
			widjet.states.check('ymaps');
		},
		onYmapsReady: function () {
			widjet.states.check('mapsReady')
		},
		onYmapsInited: function () {
			widjet.states.check('mapsInited')
		}
	};

	var widjet = new ipolWidjetController({
		label: 'IPOL_FIVEPOST_Widjet',
		options: {
			path: {
				value: params.path,
				check: function (wat) {
					return (typeof(wat) === 'string');
				},
				setting: 'start',
				hint: 'Value must be string (url)'
			},
			servicepath: {
				value: params.servicepath,
				check: function (wat) {
					return (typeof(wat) === 'string');
				},
				setting: 'start',
				hint: 'Value must be string (url)'
			},
			templatepath: {
				value: params.templatepath,
				check: function (wat) {
					return (typeof(wat) === 'string');
				},
				setting: 'start',
				hint: 'Value must be string (url)'
			},
			country: {
				value: 'all',
				check: function (wat) {
					return (typeof(wat) === 'string');
				},
				setting: 'start',
				hint: 'Value must be string (countryname)'
			},
            lang: {
                value: 'rus',
                check: function (wat) {
                    return (typeof(wat) === 'string');
                },
                setting: 'start',
                hint: 'Value must be string (laguage name)'
            },
			link: {
				value: params.link,
				check: function (wat) {
					return (ipjq('#' + wat).length);
				},
				setting: 'afterJquery',
				hint: 'No element whit this id to put the widjet'
			},
			defaultCity: {
				value: params.defaultCity,
				check: function (name) {
					return (this.city.check(name) !== false);
				},
				setting: 'dataLoaded',
				hint: 'Default City wasn\'t founded'
			},
			choose: {
				value: true,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
            hidecash: {
                value: false,
                check: function (wat) {
                    return (typeof(wat) === 'boolean');
                },
                setting: 'start',
                hint: 'Value must be bool (true / false)'
            },
            hidecard: {
                value: false,
                check: function (wat) {
                    return (typeof(wat) === 'boolean');
                },
                setting: 'start',
                hint: 'Value must be bool (true / false)'
            },
			popup: {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			noYmaps : {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			apikey: {
				value: '',
				check: function (wat) {
					return (typeof(wat) === 'string');
				},
				setting: 'start',
				hint: 'Value must be string (apikey)'
			},
			yMapsSearch : {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			yMapsSearchMark : {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			region: {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			noCitySelector: {
				value: false,
				check: function (wat) {
					return (typeof(wat) === 'boolean');
				},
				setting: 'start',
				hint: 'Value must be bool (true / false)'
			},
			goods: {
				value: false,
				check: function (wat) {
					if (typeof(wat) !== 'object') {
						return false;
					}
					if (typeof(wat.width) !== 'undefined') {
						return false;
					}
					for (var i in wat) {
						if (
							typeof(wat[i].length) === 'undefined' || !wat[i].length ||
							typeof(wat[i].width) === 'undefined' || !wat[i].width ||
							typeof(wat[i].height) === 'undefined' || !wat[i].height ||
							typeof(wat[i].weight) === 'undefined' || !wat[i].weight
						)
							return false;
					}
					return true;
				},
				setting: 'start',
				hint: 'Value must be an array of objects of type {length:(float),width:(float),height(float),weight(float)}'
			},
		},
		events: [
			'onChoose',
			'onChooseProfile',
			'onReady',
			'onCalculate'
		],
		stages: {
			/*
			 *   when controller is ready - start loadings
			 */
			'mainInit': {
				states: {
					started: false
				},
				function: function () {
					this.service.loadTag(this.options.get('path') + "ipjq.js", 'script', loaders.onIPJQLoad);
					var yalang = (this.options.get('lang') == 'rus') ? 'ru_RU' : 'en_GB';
					var yaapikey = (this.options.get('apikey')) ? "apikey=" + this.options.get('apikey') + "&" : '';
					if(this.options.get('noYmaps')){
						window.setTimeout(loaders.onYmapsLoad,500); // for success loading ymaps
					} else {
						this.service.loadTag("https://api-maps.yandex.ru/2.1/?" + yaapikey + "lang=" + yalang, 'script', loaders.onYmapsLoad);
					}
					this.service.loadTag(this.options.get('path') + 'style.css', 'link', loaders.onStylesLoad);

				}
			},
			/*
			 *    when jquery is ready - load extensions and ajax-calls
			 */
			'afterJquery': {
				states: {
					jquery: false
				},
				function: function () {
					this.service.loadTag(this.options.get('path') + 'jquery.mCustomScrollbar.concat.min.js', 'script', loaders.onJSPJSLoad);

					ipjq.getJSON(
						widjet.options.get('servicepath'),
						{IPOL_FIVEPOST_action: 'getPVZ', country: this.options.get('country'), lang: this.options.get('lang')},
						DATA.parsePVZFile
					);
					ipjq.getJSON(
						widjet.options.get('servicepath'),
						{IPOL_FIVEPOST_action: 'getLang', lang: this.options.get('lang')},
						LANG.write
					);

				}
			},
			/*
			 *  when ymaps's script is added and loaded
			 */
			'ymapsBinder1': {
				states: {
					ymaps    : false,
                    document : false
				},
				function: function () {
					ymaps.ready(loaders.onYmapsReady());
				}
			},
			/*
			 *    waiting untill ymaps are loaded, ready, steady, go
			 */
			'ymapsBinder2': {
				states: {
					mapsReady: false
				},
				function: function () {
					YmapsLoader();
				}
			},
			/*
			 *   when everything, instead of ymaps is ready
			 */
			'dataLoaded': {
				states: {
					JSPCSS: false,
					JSPJS: false,
					PVZ: false,
					styles: false,
					lang: false
				},
				function: function () {
					loaders.onDataLoad();
				}
			},
			/*
			 *   when everything is ready
			 */
			'ready': {
				states: {
					data: false,
					mapsInited: false
				},
				function: function () {
                    if (widjet.options.get('defaultCity') != "auto"){
                        DATA.city.set(widjet.options.get('defaultCity'));
					}
					template.readyA = true;
					template.html.loadCityList(DATA.city.collection);
					if (!widjet.popupped) {
						widjet.finalAction();
					} else {
						widjet.loadedToAction = true;
					}
					this.binders.trigger('onReady');
				}
			}
		},
		params: params
	});

	widjet.popupped = false;
    widjet.active = false;
	widjet.loadedToAction = false;
	widjet.finalActionCalled = false;
	widjet.loaderHided = false;
	
	widjet.paytypes = {};

	widjet.finalAction = function () {
		if (widjet.finalActionCalled === true) {
			return;
		}
		widjet.finalActionCalled = true;
		template.controller.loadCity();

		this.IPOL_FIVEPOSTWidgetEvents();
	};

	widjet.hideLoader = function () {
		if (!widjet.loaderHided) {
			widjet.loaderHided = true;
			ipjq(IDS.get('PRELOADER')).fadeOut(300);
			ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search, .IPOL_FIVEPOST-widget__sidebar, .IPOL_FIVEPOST-widget__logo').removeClass('IPOL_FIVEPOST-widget__inaccessible');
		}
	};

	function YmapsLoader() {
		if (typeof (widjet.incrementer) === 'undefined') {
			widjet.incrementer = 0;
		}
		if (typeof(ymaps.geocode) !== 'function') {
			if (widjet.incrementer++ > 50) {
				widjet.logger.error('Unable to load ymaps');
			} else {
				window.setTimeout(YmapsLoader, 500);
			}
		} else {
			loaders.onYmapsInited();
		}
	}

	var HTML = {
		blocks: {},
		getBlock: function (block, values) {
			if (typeof HTML.blocks[block] != 'undefined') {
				_tmpBlock = HTML.blocks[block];
				if (typeof values == 'object') {
					for (keyVal in values) {
						_tmpBlock = _tmpBlock.replace(new RegExp('\#' + keyVal + '\#', 'g'), values[keyVal]);
					}
				}
				_tmpBlock = IDS.replaceAll(LANG.replaceAll(_tmpBlock));

				return _tmpBlock;
			}
			return '';

		},
		save: function (data) {
			HTML.blocks = data;
			template.html.place();
		}
	};

	var DATA = {
		regions: {
			collection: {},
			cityes: {},
			map: {}
		},
		city: {
			indexOfSome: function (findItem, ObjItem) {
				for (keyI in ObjItem) {
					if (ObjItem[keyI] == findItem) {
						return keyI;
					}
				}
				return false;
			},
			collection: {},
			collectionFull: {},
			current: false,
			get: function () {
				return this.current;
			},
			set: function (intCityID) {
				if (this.checkCity(intCityID)) {
					if (typeof(this.collection[intCityID]) === 'undefined') {
						if (!(intCityID = this.indexOfSome(intCityID, this.collection))) {
							return false;
						}
					}
					this.current = intCityID;
					return intCityID;
				} else {
					widjet.logger.error('Unknown city: ' + intCityID);
					return false;
				}
			},

			checkCity: function (intCityID) {
				return (typeof(this.collection[intCityID]) !== 'undefined') || this.indexOfSome(intCityID, this.collection) > -1;
				// return true;
			},
			getName: function (intCityID) {
				if (this.checkCity(intCityID)) {
					if (typeof(this.collection[intCityID]) === 'undefined') {
						intCityID = this.indexOfSome(intCityID, this.collection);
					}
					return this.collection[intCityID];
				}
				return false;
			},
			getFullName: function (intCityID) {
				if (this.checkCity(intCityID)) {
					if (typeof(this.collectionFull[intCityID]) === 'undefined') {
						intCityID = this.indexOfSome(intCityID, this.collectionFull);
					}
					return this.collectionFull[intCityID];
				}
				return false;
			},
			getId: function (intCityID) {
				if (this.checkCity(intCityID)) {
					if (typeof(this.collection[intCityID]) === 'undefined') {
						intCityID = this.indexOfSome(intCityID, this.collection);
					}
					return intCityID;
				}
				return false;
			}
		},

		PVZ: {
			collection: {},
            bycoord: {},
			bycoordCur: 0,

			check: function (intCityID) {
				return (
					DATA.city.checkCity(intCityID) &&
					typeof(this.collection[intCityID]) !== 'undefined'
				)
			},

			getCityPVZ: function (intCityID) {

				if (this.check(intCityID)) {
					return this.collection[intCityID];
				} else {
					widjet.logger.error('No PVZ in city ' + intCityID);
				}
			},

			getRegionPVZ: function (intCityID) {

				if (this.check(intCityID)) {
					let by_region = {};
					let region = DATA.regions.cityes[intCityID];
					let city_in_region = [];
					city_in_region.push(...DATA.regions.map[region]);
					if (region === 81) city_in_region.push(...DATA.regions.map[9]);
					if (region === 9) city_in_region.push(...DATA.regions.map[81]);
					if (region === 82) city_in_region.push(...DATA.regions.map[26]);
					if (region === 26) city_in_region.push(...DATA.regions.map[82]);
					city_in_region.forEach((item, i, arr) => {
						var pvzList =  DATA.PVZ.collection[item];
						for (let code in pvzList) {
							by_region[code] = pvzList[code];
						}
					});
					return by_region;
				} else {
					widjet.logger.error('No PVZ in city ' + intCityID);
				}
			},

			getCurrent: function () {
				if (widjet.options.get('region')) return this.getRegionPVZ(DATA.city.current);
				return this.getCityPVZ(DATA.city.current);
			}
		},

		parsePVZFile: function (data) {
			if (typeof(data.pvz) === 'undefined') {
				var sign = 'Unable to load list of PVZ : ';
				if (typeof(data.pvz) === 'undefined') {
					for (var i in data.error) {
						sign += data.error[i] + ", ";
					}
					sign = sign.substr(0, sign.length - 2);
				} else {
					sign += 'unknown error.'
				}
				widjet.logger.error(sign);
			}
			else {
				if (typeof(data.pvz.REGIONS) !== 'undefined') {
					DATA.regions.collection = data.pvz.REGIONS;
					DATA.regions.cityes = data.pvz.CITYREG;
					DATA.regions.map = data.pvz.REGIONSMAP;
				}

				for (var pvzCity in data.pvz.POINTS) {
					DATA.PVZ.collection[pvzCity] = data.pvz.POINTS[pvzCity];
					if (
						typeof(data.pvz.CITY[pvzCity]) !== 'undefined' &&
						typeof(DATA.city.collection[pvzCity]) === 'undefined'
					) {
						DATA.city.collection[pvzCity] = data.pvz.CITY[pvzCity];
						DATA.city.collectionFull[pvzCity] = data.pvz.CITYFULL[pvzCity];
					}
				}

				for (var pointId in data.pvz.POINTS) {
					var ql = data.pvz.POINTS[pointId];
					if(typeof(DATA.PVZ.collection[ql['LOCALITY_FIAS_CODE']]) === 'undefined'){
						DATA.PVZ.collection[ql['LOCALITY_FIAS_CODE']] = [];
					}
					DATA.PVZ.collection[ql['LOCALITY_FIAS_CODE']].push(data.pvz.POINTS[pointId]);
					if (
						typeof(data.pvz.CITY[ql['LOCALITY_FIAS_CODE']]) !== 'undefined' &&
						typeof(DATA.city.collection[ql['LOCALITY_FIAS_CODE']]) === 'undefined'
					) {
						DATA.city.collection[ql['LOCALITY_FIAS_CODE']] = data.pvz.CITY[ql['LOCALITY_FIAS_CODE']];
						DATA.city.collectionFull[ql['LOCALITY_FIAS_CODE']] = data.pvz.CITYFULL[ql['LOCALITY_FIAS_CODE']];
					}
				}

				loaders.onPVZLoad();
			}
		}
	};

	var CALCULATION = {
		bad: false,
		data: {
			price: 0,
			term: 0,
			tarif: false
		},
		history: [],
		defaultGabs: {length: 200, width: 300, height: 400, weight: 1000},

		binder: {},

		calculate: function (pointId) {
			if(true || typeof(this.history[pointId]) === 'undefined'){
				var mark = Date.now();
				this.history[pointId] = false;
				this.data.price  = null;
				this.data.term   = null;
				this.data.tarif  = null;
				this.request(pointId,mark);
				this.binder[parseInt(DATA.city.current)] = {};
			} else {
				this.data.price  = this.history[pointId].price;
				this.data.term   = this.history[pointId].term;
				this.data.tarif  = this.history[pointId].tarif;
				
				widjet.binders.trigger('onCalculate', {
					profiles: widjet.service.cloneObj(CALCULATION.data),
					city: DATA.city.current,
					cityName: DATA.city.getName(DATA.city.current),
					pointId  : pointId
				});
			}
		},

		request: function (pointId,timestamp) {
			var data = {
				pointId : pointId,
				city    : DATA.city.get()
			};

			if (typeof cargo.get()[0] !== 'undefined') {
				var cargos = cargo.get();
				data.goods = [];
				for (var i in cargos) {
					data.goods.push(cargos[i]);
				}
			} else {
				data.goods = [this.defaultGabs];
			}

			if (typeof(timestamp) !== 'undefined') {
				data.timestamp = timestamp;
			}

            if (DATA.city.current){
				var obRequest = {IPOL_FIVEPOST_action: 'calcPVZ', shipment: data, filters: widjet.paytypes};
				if(widjet.calcRequestConcat && typeof(widjet.calcRequestConcat) === 'object'){
					for(var i in widjet.calcRequestConcat){
						obRequest[i] = widjet.calcRequestConcat[i];
					}
				}
				ipjq.getJSON(
					widjet.options.get('servicepath'),
					obRequest,
					CALCULATION.onCalc
				);
			}
		},

		onCalc: function (answer) {
			if (typeof(answer.error) !== 'undefined') {
				CALCULATION.bad = true;
				var sign = "";
				var thisIsNorma = false;
				for (var i in answer.error) {
					if (typeof(answer.error[i]) === 'object') {
						for (var j in answer.error[i]) {
							sign += answer.error[i][j].text + ' (' + answer.error[i][j].code + '), ';
							if (answer.error[i][j].code === 3)
								thisIsNorma = true;
						}
					} else{
						if(answer.error[i] === 'No calculation'){
							thisIsNorma = true;
						}else {
							sign += answer.error[i] + ', ';
						}
					}
				}
				
				if (thisIsNorma) {
					widjet.logger.warn('Troubles while calculating: ' + sign.substring(0, sign.length - 2));
					CALCULATION.data.price = false;
					CALCULATION.data.term  = false;
					CALCULATION.data.tarif = false;
				} else
					widjet.logger.error('Error while calculating: ' + sign.substring(0, sign.length - 2));
			} else {
				CALCULATION.bad = false;
				CALCULATION.data.price = answer.result.price;
				CALCULATION.data.term  = answer.result.term;
				CALCULATION.data.tarif = false;
				CALCULATION.history[answer.result.pointId] = {
					price : CALCULATION.data.price,
					term  : CALCULATION.data.term,
					tarif : CALCULATION.data.tarif
				};
			}
			
			widjet.binders.trigger('onCalculate', {
				profiles : widjet.service.cloneObj(CALCULATION.data),
				city     : DATA.city.current,
				cityName : DATA.city.getName(DATA.city.current),
				pointId  : answer.result.pointId
			});
		}
	};

	var cargo = {
		collection: (typeof widjet.options.get('goods') === 'object') ? widjet.options.get('goods') : [],

		add: function (item) {
			if (
				typeof(item) !== 'object' ||
				typeof(item.length) === 'undefined' ||
				typeof(item.width) === 'undefined' ||
				typeof(item.height) === 'undefined' ||
				typeof(item.weight) === 'undefined'
			) {
				widjet.logger.error('Illegal item ' + item);
			} else {
				this.collection.push({
					length: item.length,
					width: item.width,
					height: item.height,
					weight: item.weight
				});
			}
		},

		reset: function () {
			this.collection = [];
		},

		get: function () {
			return widjet.service.cloneObj(this.collection);
		}
	};

	var LANG = {
		collection: {},
		replaceAll: function (content) {
			for (langKey in this.collection) {
				content = content.replace(new RegExp('\#' + langKey + '\#', 'g'), this.collection[langKey]);
			}
			return content;
		},
		get: function (wat) {
			if (typeof(this.collection[wat]) !== 'undefined') {
				return this.collection[wat];
			} else {
				widjet.logger.warn('No lang string with key ' + wat);
				return '';
			}
		},

		write: function (data) {
			ipjq.getJSON(
				widjet.options.get('templatepath'),
				{},
				HTML.save
			);

			if (typeof(data.LANG) === 'undefined') {
				var sign = 'Unable to load land-file : ';
				if (typeof(data.error) !== 'undefined') {
					for (var i in data.error) {
						sign += data.error[i] + ", ";
					}
					sign = sign.substr(0, sign.length - 2);
				} else {
					sign += 'unknown error.'
				}
				widjet.logger.error(sign);
			}
			else {
				LANG.collection = widjet.service.cloneObj(data.LANG);
				loaders.onLANGLoad();
			}
		}
	};

	var makeid = function () {
		var text = "";
		var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

		for (var i = 0; i < 5; i++)
			text += possible.charAt(Math.floor(Math.random() * possible.length));

		return text;
	};

	var IDS = {
		WID: makeid() + '_',
		options: {
			'MAP': 'IPOL_FIVEPOST_map',
			'PRELOADER': 'preloader',
		},
		replaceAll: function (content) {

			for (optKey in this.options) {
				content = content.replace(new RegExp("\#" + optKey + "\#", 'g'), this.WID + this.options[optKey].replace('#', ''));
			}

			return content.replace(new RegExp("\#WID\#", 'g'), this.WID);
		},
		get: function (wat) {
			if (typeof(this.options[wat]) !== 'undefined') {
				return '#' + this.WID + this.options[wat];
			} else {
				return '#' + this.WID + wat;
			}
		}
	};

	var template = {
		readyA: false,
		html: {
			get: function () {

				return HTML.getBlock('widget', {
					'CITY': widjet.options.get('defaultCity')
				});
			},

			makeADAPT: function () {
				if (widjet.options.get('link')) {
					return;
				}
				var moduleH = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).outerHeight();
				if (moduleH < 476) {

				} else {
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list__box, .mCustomScrollBox').css('max-height', 'auto');
				}
			},
			makeFULLSCREEN: function () {

				this.makeADAPT();
				ipjq(window).resize(this.makeADAPT());
			},
			place: function () {
				var html = this.get();

				if (widjet.options.get('link')) {
					ipjq('#' + widjet.options.get('link')).html(html);
				} else if (widjet.options.get('popup')) {
					widjet.popupped = true;
					html = HTML.getBlock('popup', {WIDGET: html});
					ipjq('body').append(html);
					this.makeFULLSCREEN();
				} else {
					html = ipjq(html);
					html.css('position', 'fixed');
					html.css('top', 0);
					html.css('left', 0);
					html.css('z-index', 1000);
					ipjq('body').append(html);
					this.makeFULLSCREEN();

				}
				if (!widjet.options.get('choose')) {
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).addClass('nochoose');
				}

                ipjq(IDS.get('sidebar')).html(HTML.getBlock('sidebar'));
				
				if(widjet.options.get('hidecash')){
					ipjq(IDS.get('butn_cash')).css('display','none');
				}
				if(widjet.options.get('hidecard')){
					ipjq(IDS.get('butn_card')).css('display','none');
				}

                loaders.onDocumentLoad();

				this.makeADAPT();
			},

			loadCityList: function (data) {

				_list = ipjq(IDS.get('city_list'));
				for (var i in data) {
					_block = HTML.getBlock('city', {
						'CITYID': i,
						'CITYNAME': data[i],
						'CITY_DETAILS': (typeof DATA.regions.collection[i] != 'undefined') ? DATA.regions.collection[i] : '&nbsp;'
					});
					_list.prepend(_block);
				}
				
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]').val(DATA.city.getName(template.controller.getCity()));
				if(widjet.options.get('noCitySelector')){
                    ipjq(ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search')).hide();
				}
			},

			hideMap: function () {
				ipjq(IDS.get('MAP')).css('display', 'none');
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('#IPOL_FIVEPOST_info').css('display', 'none');
			},
		},

		controller: {
			getCity: function () {
				return DATA.city.get();
			},
			loadCity: function (doLoad) {

				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel-details__back').click();
				template.ymaps.init(DATA.city.current);

			},

			selectCity: function (city) {
				if (typeof (city) === 'object') {
					city = city.data.name;
				}
				if (typeof city === 'undefined' || !city) {
					city = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li').not('.no-active').first().data('cityid');
				}

				DATA.city.set(city);
				template.controller.loadCity();
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]').val(DATA.city.getName(city));
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li').removeClass('focus').addClass('no-active').parent('ul').removeClass('open');
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]')[0].blur();
			},
			putCity: function (city) {
				if (typeof (city) === 'object') {
					city = city.data.name;
				}
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]').val(DATA.city.getName(city));
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li').removeClass('focus').addClass('no-active').parent('ul').removeClass('open');


			},
			updatePrices: function (obData) {
				if(template.controller.currentPVZ === obData.pointId){
					if(CALCULATION.data.price === false){
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__pricePlace').html(LANG.get('NO_PAY'));
					} else {
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__pricePlace').html(CALCULATION.data.price+' '+LANG.get('RUB')+' '+((CALCULATION.data.term !== null)?CALCULATION.data.term:''));
					}
				}
			},
			
			currentPVZ : false,

			calculate: function (pointId) {

				CALCULATION.calculate(pointId);
			},

			choosePVZ: function (id) {
				var PVZ = DATA.PVZ.getCurrent();
				widjet.binders.trigger('onChoose', {
					'id': id,
					'PVZ': PVZ[id],
					'price': CALCULATION.data.price,
					'term': CALCULATION.data.term,
					'tarif': CALCULATION.data.tarif,
					'city': DATA.city.current,
                    'cityName': DATA.city.getName(DATA.city.current)
				});
				if (!widjet.options.get('link')) {
					// this.close();
				}
			},

			open: function () {
				if (widjet.options.get('link')) {
					widjet.logger.error('This widjet is in non-floating mode - link is set');
				} else {
					template.ui.open();
				}
			},

			close: function () {
				if (widjet.options.get('link')) {
					widjet.logger.error('This widjet is in non-floating mode - link is set');
				} else {
					template.ui.close();
				}
			}
		},

		ui: {
			currentmark: false,

			markChozenPVZ: function (event) {
				template.ymaps.selectMark(event.data.id);
			},

			choosePVZ: function (event) {
				template.controller.choosePVZ(event.data.id);
			},

			open: function () {
				ipjq(IDS.get('IPOL_FIVEPOST_popup')).show();

				if (widjet.loadedToAction) {
					widjet.finalAction();
				} else {
					widjet.popupped = false;
				}
				widjet.active = true;
				if (ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list__box li').length >= 10) {
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar();
				}

                template.ymaps.map.container.fitToViewport();
			},

			close: function () {
                widjet.active = false;
                ipjq(IDS.get('IPOL_FIVEPOST_popup')).hide();
			}
		},

		ymaps: {
			map: false,
			readyToBlink: false,
			linker: IDS.get('MAP').replace('#', ''),

			init: function (city) {
				this.readyToBlink = false;
                if (city == false) {
                    DATA.city.set('0c5b2444-70a0-4932-980c-b4dc0d3f02b5');
                    city = '0c5b2444-70a0-4932-980c-b4dc0d3f02b5';

                    widjet.options.set(city, 'defaultCity');

                    ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]').val(city);
                }

				var retCoords = this.makeCenterByCoords(DATA.PVZ.getCurrent());

                this.loadMap(DATA.city.current,retCoords);
            },
			
			makeCenterByCoords : function(arPVZ){
				var retCoords = false;
				if(typeof(arPVZ) !== 'undefined' && !widjet.service.isEmpty(arPVZ)){
					retCoords = [0,0];
					var cnt = 0;
					for(var i in arPVZ){
						if(arPVZ[i].ADDRESS_LAT && arPVZ[i].ADDRESS_LNG){
							retCoords [0] += parseFloat(arPVZ[i].ADDRESS_LAT);
							retCoords [1] += parseFloat(arPVZ[i].ADDRESS_LNG);
						}
						cnt++
						if(cnt > 100){
							break;
						}
					}
					retCoords [0] = retCoords[0]/cnt;
					retCoords [1] = retCoords[1]/cnt;
				}
				return retCoords;
			},

            loadMap: function (city,coords) {
                var self = this;
				city = DATA.city.getFullName(city);

				if (typeof DATA.PVZ.getCurrent() === 'object') {
					if(self.map){
						self.map.setZoom(10);
						self.map.setCenter(coords);
					}
					self.placeMarks();
					return;
				}
				
				if(widjet.options.get('apikey') || typeof(coords) === 'undefined'){
					ymaps.geocode(city, {
						results: 1
					}).then(function (res) {
						var firstGeoObject = res.geoObjects.get(0);
						var coords = firstGeoObject.geometry.getCoordinates();
						self._loadMap(coords);
					});
				} else {
					self._loadMap(coords);
				}
			},
			
			_loadMap : function (coords){
				if (!self.map) {
					self.makeMap({center: coords});
					self.map.setZoom(10);
				} else {
					self.map.setCenter(coords);
					self.map.setZoom(10);
				}

				self.placeMarks();
			},

			makeMap : function (addInfo) {
				if(typeof(addInfo) !== 'object'){
                    addInfo = {};
				}

                template.ymaps.map = new ymaps.Map(
					template.ymaps.linker,
                    widjet.service.concatObj({
                        zoom: 10,
                        controls: []
                    },addInfo)
				);

                this.map.controls.add(new ymaps.control.ZoomControl(),{
					float: 'none',
					position: {
						left   : 12,
						bottom : 70
					}
				});

				if(widjet.options.get('yMapsSearch')){
					this.map.controls.add(new ymaps.control.SearchControl(), {
						float: 'none',
						position: {
							left: 12,
							top: 20
						},
						noPlacemark: !widjet.options.get('yMapsSearchMark')
					});
				}

                template.ymaps.map.events.add('boundschange', widjet.hideLoader);
                template.ymaps.map.events.add('actionend',    widjet.hideLoader);
            },

			clearMarks: function () {
                if(typeof(this.map.geoObjects) !== 'undefined') {
					if (typeof(this.map.geoObjects.removeAll) !== 'undefined' && false)
						this.map.geoObjects.removeAll();
					else {
						do {
							var map = this.map;
							map.geoObjects.each(function (e) {
								map.geoObjects.remove(e);
							});
						} while (map.geoObjects.getBounds());
					}
                }
			},

			placeMarks: function () {
				var pvzList = DATA.PVZ.getCurrent();

				if (typeof pvzList !== 'object') {
					ipjq(IDS.get('sidebar')).hide();
				} else {
					ipjq(IDS.get('sidebar')).show();
				}

				ipjq(IDS.get('panel')).find(IDS.get('pointlist')).html('');
				ipjq(IDS.get('panel')).find(IDS.get('pointlist')).html(HTML.getBlock('panel_list'));

				_panelContent = ipjq(IDS.get('pointlist')).find('.IPOL_FIVEPOST-widget__panel-content');

                if (typeof pvzList === 'object' && !widjet.service.isEmpty(pvzList)) {
					template.ymaps.clusterer = new ymaps.Clusterer({
						gridSize: 50,
						preset: 'islands#ClusterIcons',
						clusterIconColor: '#61BC47',
						hasBalloon: false,
						groupByCoordinates: false,
						clusterDisableClickZoom: false,
						maxZoom: 11,
						zoomMargin: [45],
						clusterHideIconOnBalloonOpen: false,
						geoObjectHideIconOnBalloonOpen: false
					});
					geoMarks = [];
					for (var i in pvzList) {
						var blocked = false;
						var type = pvzList[i].TYPE;
						if (typeof widjet.paytypes.CARD_ALLOWED != 'undefined' && pvzList[i].CARD_ALLOWED  === 'N') {
							blocked = true; // continue
						}

						if (typeof widjet.paytypes.CASH_ALLOWED != 'undefined' && pvzList[i].CASH_ALLOWED === 'N') {
							blocked = true; // continue
						}

						pvzList[i].placeMark = new ymaps.Placemark([pvzList[i].ADDRESS_LAT, pvzList[i].ADDRESS_LNG], {}, {
							iconLayout: ymaps.templateLayoutFactory.createClass(
								'<div class="IPOL_FIVEPOST-widget__placeMark'+((blocked) ? ' inactive' : '')+'"><img class="IPOL_FIVEPOST-widget__placeMark_img" src="'+params.imagesPlaceMark[type]+'"/></div>'
							),
							iconImageSize: [30, 43],
							iconImageOffset: [0, 0],
							iconShape: {
								type: 'Rectangle',
								coordinates: [
									[-20, -40], [10, 0]
								]
							}
						});
						pvzList[i].blocked = blocked;

						geoMarks.push(pvzList[i].placeMark);
						pvzList[i].placeMark.link = i;

						pvzList[i].list_block = ipjq(HTML.getBlock('point', {
							P_NAME: pvzList[i].NAME,
							P_ADDR: pvzList[i].FULL_ADDRESS,
							P_TYPE: pvzList[i].ADDITIONAL
						}));

						pvzList[i].placeMark.listItem = pvzList[i].list_block;

						pvzList[i].placeMark.events.add(['balloonopen', 'click'], function (metka) {
							_prevMark = template.ui.currentmark;

							template.ui.currentmark = metka.get('target');
							if (typeof _prevMark == 'object') {
								try {
									_prevMark.events.fire('mouseleave');
								} catch (e) {

								}
							}

							ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__sidebar-burger:not(.active)').trigger('click');
							template.ui.markChozenPVZ({data: {id: metka.get('target').link, link: template.ui}});
							pvzList[i].list_block.trigger('opendd');
						});

						if(!blocked){
							pvzList[i].placeMark.events.add(['mouseenter'], function (metka) {
								var cityPvz = DATA.PVZ.getCurrent();
								var subtype = cityPvz[metka.get('target').link];
								ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__panel-content").mCustomScrollbar("scrollTo", metka.get('target').listItem);
								metka.get('target').listItem.addClass('IPOL_FIVEPOST-widget__panel-list__item_active');
								metka.get('target').options.set(
									{
										iconLayout: ymaps.templateLayoutFactory.createClass(
											'<div class="IPOL_FIVEPOST-widget__placeMark chosen"><img style="" class="IPOL_FIVEPOST-widget__placeMark_img" src="'+params.imagesPlaceMark[subtype.TYPE]+'"/></div>'
										)
									}
								);
							});
							pvzList[i].placeMark.events.add(['mouseleave'], function (metka) {
								var cityPvz = DATA.PVZ.getCurrent();
								var subtype = cityPvz[metka.get('target').link];
								if (template.ui.currentmark != metka.get('target')) {
									metka.get('target').listItem.removeClass('IPOL_FIVEPOST-widget__panel-list__item_active');
									metka.get('target').options.set(
										{
											iconLayout: ymaps.templateLayoutFactory.createClass(
												'<div class="IPOL_FIVEPOST-widget__placeMark"><img class="IPOL_FIVEPOST-widget__placeMark_img" src="'+params.imagesPlaceMark[subtype.TYPE]+'"/></div>'
											),
										}
									);
								}
							});
						}
						
						pvzList[i].list_block.mark = pvzList[i].placeMark;
						pvzList[i].list_block.on('click', {mark: i}, function (event) {
							pvzList[event.data.mark].placeMark.events.fire('click');

						}).on('mouseenter', {
							id: i,
							ifOn: true,
							link: template.ymaps
						}, template.ymaps.blinkPVZ).on('mouseleave', {
							id: i,
							ifOn: false,
							link: template.ymaps
						}, template.ymaps.blinkPVZ);

						_panelContent.append(pvzList[i].list_block);
					}

					template.ymaps.clusterer.add(geoMarks);
					_bounds = template.ymaps.clusterer.getBounds();
					if (!this.map) {
						if (_bounds[0][0] == _bounds[1][0]) {
							this.makeMap({center: _bounds[0]});
							this.map.setZoom(10);
						} else {
                            this.makeMap({bounds: _bounds});
							this.map.setBounds(_bounds, {
								zoomMargin: 45,
								checkZoomRange: true,
								duration: 500
							});
							this.map.setZoom(10);
						}

						template.ymaps.clearMarks();
						this.map.geoObjects.add(template.ymaps.clusterer);
						widjet.hideLoader();
					} else {
						if (_bounds[0][0] == _bounds[1][0]) {
							this.map.setCenter(_bounds[0]);
							this.map.setZoom(10);
							template.ymaps.clearMarks();
							this.map.geoObjects.add(template.ymaps.clusterer);
						} else {
							this.map.setBounds(template.ymaps.clusterer.getBounds(), {
								zoomMargin: 45, checkZoomRange: true, duration: 500
							}).then(
								function () {
									template.ymaps.clearMarks();
									this.map.geoObjects.add(template.ymaps.clusterer);
									if (this.map.getZoom() > 12) {
										this.map.setZoom(12);
									} else {
										this.map.setZoom(10);
									}
								},
								function () {
									template.ymaps.clearMarks();
									this.map.geoObjects.add(template.ymaps.clusterer);
									if (this.map.getZoom() > 12) {
										this.map.setZoom(12);
									} else {
										this.map.setZoom(10);
									}
								},
								this
							);
						}
					}
				} else {
					template.ymaps.clearMarks();
					widjet.hideLoader();
				}

				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__panel-content").mCustomScrollbar();
				if (ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list__box li').length >= 10) {
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar();
				}

				this.readyToBlink = true;
			},
			makeUpCenter: function (cords) {
				var projection = this.map.options.get('projection');
				cords = this.map.converter.globalToPage(
					projection.toGlobalPixels(
						cords,
						this.map.getZoom()
					)
				);
				ww = ipjq(IDS.get('panel')).width();

				if (ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).width() - ww > 100) {
					cords[0] = cords[0] + ww / 2;
				}

				cords = projection.fromGlobalPixels(
					this.map.converter.pageToGlobal(cords), this.map.getZoom()
				);

				return cords;
			},

			selectMark: function (wat) {
				var cityPvz = DATA.PVZ.getCurrent();

				if (DATA.city.current !== cityPvz[wat].LOCALITY_FIAS_CODE) {
					DATA.city.set(cityPvz[wat].LOCALITY_FIAS_CODE);
					city = DATA.city.getName(cityPvz[wat].LOCALITY_FIAS_CODE);
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-box input[type=text]').val(city);
				}
				
				template.controller.currentPVZ = cityPvz[wat].POINT_GUID;
				
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__pricePlace').html(LANG.get('COUNTING'));
				
				var paymentInfo = '';
				if(cityPvz[wat].CASH_ALLOWED !== 'N'){
					paymentInfo += LANG.get('H_CASH')+'<br>';
				}
				if(cityPvz[wat].CARD_ALLOWED !== 'N'){
					paymentInfo += LANG.get('H_CARD');
				}
				if(!paymentInfo){
					paymentInfo = LANG.get('L_NOPAY');
				}

				CALCULATION.calculate(cityPvz[wat].POINT_GUID);

				this.map.setCenter(template.ymaps.makeUpCenter([cityPvz[wat].ADDRESS_LAT, cityPvz[wat].ADDRESS_LNG]));

				_detailPanel = ipjq(IDS.get('panel')).find(IDS.get('detail_panel'));
				_detailPanel.html('');

				_photoHTML = '';
				if (typeof cityPvz[wat].Picture != 'undefined') {
					for (_imgIndex in  cityPvz[wat].Picture) {
						_photoHTML += HTML.getBlock('image_c', {D_PHOTO: cityPvz[wat].Picture[_imgIndex]});
					}
				}
				
				var workTime = '';
				if(typeof(cityPvz[wat].WORK_HOURS) !== 'undefined'){
					cityPvz[wat].WORK_HOURS.forEach(function(obWH,ind){
						workTime += LANG.get('DAY'+ind)+'.: '+obWH.O+' - '+obWH.C+'<br>';
					});
				}

				_block = ipjq(HTML.getBlock('panel_details', paramsD = {
					D_NAME: cityPvz[wat].NAME,
					D_ADDR: cityPvz[wat].FULL_ADDRESS,
					D_TIME: workTime,
					D_DESCR : cityPvz[wat].ADDITIONAL,
					D_PAYMENT: paymentInfo
				}));

				var blockedChoose = false;
				if (typeof widjet.paytypes.CARD_ALLOWED != 'undefined' && cityPvz[wat].CARD_ALLOWED  === 'N') {
					blockedChoose = true;
				}
				if (typeof widjet.paytypes.CASH_ALLOWED != 'undefined' && cityPvz[wat].CASH_ALLOWED === 'N') {
					blockedChoose = true;
				}
				
				if(blockedChoose){
					_block.find(IDS.get('choose_button')).css('display','none');
				} else {
					_block.find(IDS.get('choose_button')).css('display','');
					_block.find(IDS.get('choose_button')).on('click', {id: wat}, function (event) {
						template.controller.choosePVZ(event.data.id);
					});
				}

				_detailPanel.html(_block);
				_detailPanel.find('.IPOL_FIVEPOST-widget__panel-content').mCustomScrollbar();
			},

			blinkPVZ: function (event) {
				if (event.data.link.readyToBlink) {
					var cityPvz = DATA.PVZ.getCurrent();
					if (template.ui.currentmark == cityPvz[event.data.id].placeMark || cityPvz[event.data.id].blocked) {
						return;
					}
					var type = cityPvz[event.data.id].TYPE;
					if (event.data.ifOn) {
						event.data.link.clusterer.remove(cityPvz[event.data.id].placeMark);
						event.data.link.map.geoObjects.add(cityPvz[event.data.id].placeMark);
						cityPvz[event.data.id].placeMark.options.set({iconImageHref: widjet.options.get('path') + "images/"+type+"_chosen.png"});
					} else {
						cityPvz[event.data.id].placeMark.options.set({iconImageHref: widjet.options.get('path') + "images/"+type+".png"});
						event.data.link.map.geoObjects.remove(cityPvz[event.data.id].placeMark);
						event.data.link.clusterer.add(cityPvz[event.data.id].placeMark);
					}
				}
			},
		}
	};

	widjet.binders.add(template.controller.updatePrices, 'onCalculate');
	
	widjet.calcRequestConcat = false;
	widjet.setCalcRequestConcat = function(obRequest){
		if(!obRequest || typeof(obRequest) === 'object'){
			widjet.calcRequestConcat = obRequest;
		}
	}

	widjet.resetPVZMarks = function () {
		if(typeof(template.ymaps.map.geoObjects) !== 'undefined'){
			template.ymaps.clearMarks();
			template.ymaps.placeMarks();
		}
	};

	widjet.IPOL_FIVEPOSTWidgetEvents = function () {
		ipjq('.IPOL_FIVEPOST-widget__popup__close-btn').off('click').on('click', function () {
			template.ui.close();
			// ipjq(this).closest('.IPOL_FIVEPOST-widget__popup-mask').hide();
		});

		ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).on('click', '.IPOL_FIVEPOST-widget__sidebar-button', {widjet: widjet}, function () {
			_this = ipjq(this);
			_this.toggleClass('active');
			var idHint = _this.attr('data-hint');
			var wid = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).outerWidth();

			if (_this.hasClass('IPOL_FIVEPOST-widget__sidebar-button-point')) {
				widjet.paytypes = {};
				if (ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__sidebar-button-point.active').length) {
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__sidebar-button-point.active').each(function () {
						widjet.paytypes[ipjq(this).data('mtype')] = true;
					});
				}
				widjet.resetPVZMarks();
			} else {
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).css({
					right: -wid,
					'opacity': '0'
				});

				if (_this.hasClass('IPOL_FIVEPOST-widget__sidebar-burger')) {
					if (_this.hasClass('close')) {
						_this.removeClass('close');
					}
					_this.toggleClass('open');
					if (!_this.hasClass('open')) {
						_this.addClass('close');
					}
					if (ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel').hasClass('open')) {
						if (_this.hasClass('active')) {
							ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel-contacts').fadeOut(600);
							ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel-list, .IPOL_FIVEPOST-widget__panel-details').fadeIn(600);
						} else {
							ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel-list, .IPOL_FIVEPOST-widget__panel-details').fadeOut(600);
							ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel').removeClass('open');
						}
					} else {
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel').addClass('open');
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel-list, .IPOL_FIVEPOST-widget__panel-details').fadeIn(600);
					}
				}
			}
		}).on('click', '.IPOL_FIVEPOST-widget__choose', function () {
			ipjq(this).addClass('widget__loading');
		});
		ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).on('mousemove', '.IPOL_FIVEPOST-widget__sidebar-button', function () {
			if (!ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__panel').hasClass('open')) {
				var idHint = ipjq(this).attr('data-hint');
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).css({
					right: '67px',
					'opacity': '1'
				});
			}
		}).on('mouseleave', '.IPOL_FIVEPOST-widget__sidebar-button', function () {
			var idHint = ipjq(this).attr('data-hint');
			var wid = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).outerWidth();
			ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).css({
				right: -wid,
				'opacity': '0'
			});
		}).on('hover', '.IPOL_FIVEPOST-widget__panel-headline', function () {
			if (ipjq(this).outerWidth() <= ipjq(this).find('span').outerWidth()) {
				ipjq(this).addClass('hover-long');
			}

		}, function () {
			if (ipjq(this).hasClass('hover-long')) {
				ipjq(this).removeClass('hover-long')
			}
		});

		ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__sidebar-button').each(function (index, el) {
			var idHint = ipjq(el).attr('data-hint');
			var top = (ipjq(el).outerHeight() + -ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).outerHeight()) / 2 + 62 * index;
			var wid = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).outerWidth();
			ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(idHint).css({
				'right': -wid,
				'top': top,
				'opacity': '0'
			});
		});

		ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt'))
			.on('click, opendd', ".IPOL_FIVEPOST-widget__panel-list__item", function () {
				ipjq(this).parents(".IPOL_FIVEPOST-widget__panel-list").css('left', '-330px');
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__panel-details").css('right', '0px');
			}).on('click', '.IPOL_FIVEPOST-widget__panel-details__back', function () {
			ipjq(this).parents('.IPOL_FIVEPOST-widget__panel-details').css('right', '-330px');
			ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__panel-list").css('left', '0px');
		}).on('click', '.IPOL_FIVEPOST-widget__panel-details__block-img', function () {
			var src = ipjq(this).find('img').attr('src');
			var $block = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__photo');
			$block.find('img').attr('src', src);
			$block.addClass('active');
		}).on('click', '.IPOL_FIVEPOST-widget__photo', function (e) {
			if (!ipjq(e.target).is('img')) {
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__photo').removeClass('active');
			}
		}).on('focusin', '.IPOL_FIVEPOST-widget__search-box input[type=text]', function () {
			ipjq(this).val('');
			ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__delivery-type').addClass('IPOL_FIVEPOST-widget__delivery-type_close');
			setTimeout(function () {
				ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul').addClass('open')
					.find('li').removeClass('no-active');
			}, 1000);
		}).on('click', '.IPOL_FIVEPOST-widget__search-list ul li', function () {
			template.controller.selectCity(ipjq(this).data('cityid'));
		}).on('keydown', '.IPOL_FIVEPOST-widget__search-box input[type=text]', function (e) {
			var $liActive = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li:not(.no-active)');
			var $liFocus = $liActive.filter('.focus');
			if (e.keyCode === 40) {
				if ($liFocus.length == 0) {
					$liActive.first().addClass('focus');
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liActive.first(), {
						scrollInertia: 300
					});
				} else {
					$liFocus.removeClass('focus');

					if ($liFocus.nextAll().filter(':not(.no-active)').eq(0).length != 0) {
						$liFocus.nextAll().filter(':not(.no-active)').eq(0).addClass('focus');
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liFocus.next($liActive), {
							scrollInertia: 300
						});
					} else {
						$liActive.first().addClass('focus');
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liActive.first(), {
							scrollInertia: 300
						});
					}
				}
			}
			if (e.keyCode === 38) {
				if ($liFocus.length == 0) {
					$liActive.last().addClass('focus');
					ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liActive.last(), {
						scrollInertia: 300
					});
				} else {
					$liFocus.removeClass('focus');
					if ($liFocus.prevAll().filter(':not(.no-active)').eq(0).length != 0) {
						$liFocus.prevAll().filter(':not(.no-active)').eq(0).addClass('focus');
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liFocus.prev($liActive), {
							scrollInertia: 300
						});
					} else {
						$liActive.last().addClass('focus');
						ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find(".IPOL_FIVEPOST-widget__search-list__box").mCustomScrollbar('scrollTo', $liActive.last(), {
							scrollInertia: 300
						});
					}
				}
			}
		}).on('keyup', '.IPOL_FIVEPOST-widget__search-box input[type=text]', function (e) {
			try {
				var filter = new RegExp('^(' + ipjq(this).val() + ')+.*', 'i');
			} catch (e) {
				var filter = '';
			}

			var $li = ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li');

			if (e.keyCode === 13) {
				var $liActive = $li.not('.no-active');
				var $liFocus = $liActive.filter('.focus');
				if ($liFocus.length == 0) {
					template.controller.selectCity();
				} else {
					template.controller.selectCity($liFocus.find('.IPOL_FIVEPOST-widget__search-list__city-name').text());
					$liFocus.removeClass('focus');
				}
				return
			}

			if (filter != '') {
				$matches = $li.filter(function () {
					return filter.test(ipjq(this).find('.IPOL_FIVEPOST-widget__search-list__city-name').text().replace(/[^\wа-яё\s-]+/gi, ""));
				});

				$li.not($matches).addClass('no-active').removeClass('focus');
				if ($matches.length == 0) {
					$li.parent('ul').removeClass('open');
				} else if (!$li.parent('ul').hasClass('open')) {
					$li.parent('ul').addClass('open');
				}

				$matches.each(function (index, el) {
					if (ipjq(el).hasClass('no-active')) {
						ipjq(el).removeClass('no-active');
					}
				});
			} else {
				$li.removeClass('no-active');
			}
		}).on('click', function (e) {
			if (ipjq(e.target).closest('.IPOL_FIVEPOST-widget__search').length == 0 && ipjq(IDS.get('IPOL_FIVEPOST_widget_cnt')).find('.IPOL_FIVEPOST-widget__search-list ul li').not('.no-active').length != 0) {
				template.controller.putCity(template.controller.getCity());
			}
		});
	};

	widjet.city = {
		get: function () {
			return DATA.city.current
		},
		set: function (name) {
			DATA.city.set(name);
			template.controller.loadCity();
		},
		check: function (name) {
			return DATA.city.getId(name);
		}
	};

	widjet.PVZ = {
		get: function (cityName) {
			return DATA.PVZ.getCityPVZ(cityName);
		},
		check: function (cityName) {
			return DATA.PVZ.check(cityName);
		}
	};

	widjet.cargo = {
		add: function (item) {
			cargo.add(item);
		},
		reset: function () {
			cargo.reset();
		},
		get: function () {
			return cargo.get()
		}
	};

	widjet.calculate = function (pointId) {
		CALCULATION.calculate(pointId);
		return CALCULATION.data;
	};

	if (!widjet.options.get('link')) {
		widjet.open = function () {
			template.controller.open();
		};
		widjet.close = function () {
			template.controller.close();
		};
	}

	return widjet;
}