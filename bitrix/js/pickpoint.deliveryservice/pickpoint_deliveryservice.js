(function() {
	PickpointDeliveryservice = {		
		
		postamatAddressInputs: [],		
		handlers: {},
		
		init: function(params)
		{
			this.postamatAddressInputs = params.postamatAddressInputs || [];
			this.handlers = params.handlers || {};
			
			if (typeof BX !== 'undefined' && BX.addCustomEvent)
				BX.addCustomEvent('onAjaxSuccess', PickpointDeliveryservice.onLoad);
			
			$(document).ready(function() {PickpointDeliveryservice.checkData();});		
			
			PickpointDeliveryservice.onLoad();
		},	
		
		onLoad: function()
		{
			if (PickpointDeliveryservice.checkOwnDelivery())
			{
				PickpointDeliveryservice.fillAddress();				
			}
		},
		
		fillAddress: function()
		{
			var postamat = $('#pp_id').val();
			var address = $('#pp_address').val();		
			var chosenProp = false;
				
			for (var i in PickpointDeliveryservice.postamatAddressInputs)
			{
				if (typeof(PickpointDeliveryservice.postamatAddressInputs[i]) === 'function') 
					continue;

				chosenProp = $('#ORDER_PROP_' + PickpointDeliveryservice.postamatAddressInputs[i]);
				
				if (!chosenProp.length || chosenProp.get(0).tagName !== 'INPUT')
					chosenProp = $('[name="ORDER_PROP_' + PickpointDeliveryservice.postamatAddressInputs[i] + '"]');
				
				if (chosenProp.length)
				{
					if (postamat && address)
						chosenProp.val(address + ' [' + postamat + ']');
					chosenProp.css('background-color', '#eee').attr('readonly', 'readonly');
					break;
				}
			}		
		},	
		
		checkOwnDelivery: function()
		{		
			var selectedDeliveryId = $('[name=DELIVERY_ID]:checked').val();		
			var $postamat = $('#pp_id');
			var $table = $('#tPP');		
			
			if ($postamat && $table)
			{
				if ($postamat.attr('data-delivery-id') === selectedDeliveryId)			
					return selectedDeliveryId;
			}
			return false;		
		},
		
		widgetHandler: function(result) 
		{
			$('#pp_id').val(result["id"]);
			$('#pp_address').val(result["address"]);
			$('#pp_name').val(result["name"]);
			$('#pp_zone').val(result["zone"]);
			$('#pp_coeff').val(result["coeff"]);
			$('#pp_delivery_min').val(result["delivery_min"]);
			$('#pp_delivery_max').val(result["delivery_max"]);
			
			$('#sPPDelivery').html(result['address'] + "<br/>" + result['name']);		
			$('#tPP').css('display','block');
			
			PickPoint.close();
			
			// onAfterPostamatSelected event
			if (typeof (PickpointDeliveryservice.handlers.onAfterPostamatSelected) != 'undefined' && PickpointDeliveryservice.handlers.onAfterPostamatSelected.length)				
			{
				PickpointDeliveryservice.executeFunctionByName(PickpointDeliveryservice.handlers.onAfterPostamatSelected, window, result);
			}
			
			if (typeof submitForm === 'function') 
			{
				submitForm();
			} 
			else if (typeof BX.Sale.OrderAjaxComponent.sendRequest === 'function') 
			{
				BX.Sale.OrderAjaxComponent.sendRequest();
			}				
		},
		
		checkData: function()
		{
			if (PickpointDeliveryservice.checkOwnDelivery())
			{			
				var errorElementIndex;
				var selfErrors = PickpointDeliveryservice.validateData();			

				if ($('#bx-soa-delivery').length)		
				{
					var errors = BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY || [];
					var selfErrorsMsg = (selfErrors.length) ? selfErrors.join('<br>') : '';			
					
					if (BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY_PICKPOINT !== selfErrorsMsg) 
					{
						errorElementIndex = errors.indexOf(BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY_PICKPOINT);
						if (errorElementIndex !== -1) 
						{
							errors.splice(errorElementIndex, 1);
							BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = errors;
							BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
						}
						BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY_PICKPOINT = selfErrorsMsg;
					}
					
					if (selfErrors.length) 
					{
						if (errors.indexOf(selfErrorsMsg) === -1) 
						{
							errors.push(selfErrorsMsg);
							BX.Sale.OrderAjaxComponent.result.ERROR.DELIVERY = errors;
							BX.Sale.OrderAjaxComponent.showBlockErrors(BX.Sale.OrderAjaxComponent.deliveryBlockNode);
						}
						BX.Sale.OrderAjaxComponent.switchOrderSaveButtons(false);
					} 
					else 
					{
						BX.Sale.OrderAjaxComponent.switchOrderSaveButtons(true);
					}
				}
			}

			window.setTimeout(PickpointDeliveryservice.checkData, 500);
		},
		
		validateData: function() 
		{
			var postamat = $('#pp_id').val();
			var phone = $('#pp_sms_phone').val();
			var phoneInProp = $('#pp_phone_in_prop').val();	
			var msg = [];
			
			if (!postamat)
				msg.push(BX.message('PP_DS_JS_POSTAMAT_NOT_SELECTED'));
			
			if (phoneInProp === 'N')
			{
				if (!phone.match(/\+7[0-9]{10}$/))
					msg.push(BX.message('PP_DS_JS_PHONE_NUMBER_INCORRECT'));
			}	

			return msg;
		},	
		
		executeFunctionByName: function(name, context) 
		{
			// Thanks Jason Bunting
			var args = Array.prototype.slice.call(arguments, 2);
			var namespaces = name.split(".");
			var func = namespaces.pop();
			for (var i = 0; i < namespaces.length; i++) {
				context = context[namespaces[i]];
			}
			
			return context[func].apply(context, args);
		},
	};
})();	