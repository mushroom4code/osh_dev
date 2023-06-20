BX.namespace('BX.Sale.PersonalProfileComponent');

(function() {
	BX.Sale.PrivateProfileComponent = {
		init: function ()
		{
			var passwordNode = BX('main-profile-password');
			var confirmNode = BX('main-profile-password-confirm');

			var stateNode = BX('main-profile-state');
			var cityNode = BX('main-profile-city');
			var streetNode = BX('main-profile-street');

			BX.ready(function(){
				BX.bind(confirmNode, 'input', function(){
					if (!BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (!BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				});
				BX.bind(passwordNode, 'input', function(){
					if (BX.type.isNotEmptyString(passwordNode.value))
					{
						BX.removeClass(passwordNode.parentNode, 'has-error');
					}
					else if (BX.type.isNotEmptyString(confirmNode.value))
					{
						BX.addClass(passwordNode.parentNode, 'has-error');
					}
				});

				BX.bind(BX('main-profile-submit'), 'click', function () {
					if (BX('notification').checked !== true) {
						BX.removeClass(
							BX("notification-error"),
							'd-none'
						);
						event.preventDefault();
					}
				});

				$('#main-profile-address').suggestions({
					token: window.daDataParam.token,
					type: "ADDRESS",
					hint: false,
					onSelect: function (suggestion) {
						stateNode.value = suggestion.data.region_with_type ?? '';
						cityNode.value = suggestion.data.city ?? '';
						streetNode.value = suggestion.data.street ?? '';
						if (suggestion.data.house!==undefined) {
							streetNode.value += ', ' + suggestion.data.house_type + ' ' + suggestion.data.house;
						}

						if (suggestion.data.flat!==undefined) {
							streetNode.value += ', ' + suggestion.data.flat_type + ' ' + suggestion.data.flat;
						}

					}.bind(this)
				});

			});
		},
	}

})();