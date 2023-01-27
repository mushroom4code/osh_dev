(function () {

	BX.namespace('BX.Ctweb.SMSAuth');

	if (BX.Ctweb.SMSAuth.Controller instanceof Function)
		return;

	const STATE_INIT = 'INIT';
	const STATE_CODE_WAITING = 'CODE_WAITING';
	const STATE_PHONE_WAITING = 'PHONE_WAITING';
	const STATE_USER_WAITING = 'USER_WAITING';
	const STATE_EXPIRED = 'EXPIRED';

	BX.Ctweb.SMSAuth.Controller = function (params) {
		this.state = STATE_INIT;

		this.obCode = null;
		this.obTimer = null;
		this.obSubmit = null;
		this.obReset = null;
		this.obState = null;

		this.timerId = null;

		this.timeLeft = 0;
		this.lastTime = (+ new Date());

		this.constructor(params);
	};

	BX.Ctweb.SMSAuth.Controller.prototype.constructor = function (params) {
		this.obCode = BX(params['TEMPLATE']['CODE']);
		this.obTimer = BX(params['TEMPLATE']['TIMER']);
		this.obSubmit = BX(params['TEMPLATE']['SUBMIT']);
		this.obReset = BX(params['TEMPLATE']['RESET']);
		this.obState = BX(params['TEMPLATE']['STATE']);

		this.timeLeft = params['DATA']['TIME_LEFT'] ? parseInt(params['DATA']['TIME_LEFT']) : 0;
		this.setState(this.obState.value);
	};

	BX.Ctweb.SMSAuth.Controller.prototype.setState = function (state) {
		let prev = this.state;
		this.state = state;
		this.stateTransition(state, prev);
	};

	BX.Ctweb.SMSAuth.Controller.prototype.stateTransition = function (state, prev) {
		this.obState.value = state;
		switch (state) {
			case STATE_INIT:
				if (this.timeLeft > 0)
					this.setState(STATE_CODE_WAITING);
				else
					this.setState(STATE_PHONE_WAITING);
				break;
			case STATE_PHONE_WAITING:
				break;
			case STATE_USER_WAITING:
				break;
			case STATE_CODE_WAITING:
				BX.show(this.obCode.closest('div'));
				BX.show(this.obSubmit);
				BX.hide(this.obReset);
				this.timerId = setInterval(function () {
					let now = (+ new Date());
					let delta =  (now - this.lastTime) / 1000;
					this.lastTime = now;

					let seconds_left = this.updateTime(delta);
					this.renderTime(seconds_left);
					if (seconds_left <= 0) {
						this.setState(STATE_EXPIRED);
					}
				}.bind(this), 100);
				break;
			case STATE_EXPIRED:
				clearInterval(this.timerId);
				BX.adjust(this.obTimer, {html: BX.message('SMS_AUTH_TIME_EXPIRED')});
				BX.hide(this.obCode.closest('div'));
				BX.hide(this.obSubmit);
				BX.show(this.obReset);
				break;
			default:
				throw new Error("No state found: " + state);
		}
	};

	/**
	 * @return int
	 */
	BX.Ctweb.SMSAuth.Controller.prototype.updateTime = function (dt) {
		this.timeLeft -= dt;

		return Math.floor(this.timeLeft);
	};

	BX.Ctweb.SMSAuth.Controller.prototype.renderTime = function (secondsLeft) {
		var minutes = Math.floor(secondsLeft / 60);
		minutes = minutes < 10 ? '0' + minutes : minutes;
		var seconds = Math.floor(secondsLeft % 60);
		seconds = seconds < 10 ? '0' + seconds : seconds;

		BX.adjust(this.obTimer, {html: [BX.message('SMS_AUTH_TIME_LEFT'), minutes, ':', seconds].join('')});
	};

})();