

class Skyweb24BonusEvent {
	constructor() {
		this.propId = '';
		this.nextElement = '';
		this.hiddenBonusBlock = document.getElementById('bx-soa-bonus-hidden');
		this.initialize = false;
	}
	getBonusValue() {
		if (this.propId != '') {
			if (this.hiddenBonusBlock.querySelector('input')) {
				if (this.hiddenBonusBlock.querySelector('input')) {
					if (isNaN(parseFloat(this.hiddenBonusBlock.querySelector('input').value))) {
						this.hiddenBonusBlock.querySelector('input').value = 0;
					}
					this.hiddenBonusBlock.querySelector('input').value = parseFloat(this.hiddenBonusBlock.querySelector('input').value);
				}
				if (this.hiddenBonusBlock.querySelector('input').value >= 0 && this.hiddenBonusBlock.querySelector('input').value <= parseFloat(atob(skyweb24_bonus_max))) {
					return this.hiddenBonusBlock.querySelector('input').value;
				} else if (this.hiddenBonusBlock.querySelector('input').value > parseFloat(atob(skyweb24_bonus_max))) {
					this.hiddenBonusBlock.querySelector('input').value = parseFloat(atob(skyweb24_bonus_max));
					return this.hiddenBonusBlock.querySelector('input').value;
				} else {
					return 0;
				}
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
	updateBonusAdded(){
		if(!Skyweb24BonusEventHandler.bonusAdded){
			Skyweb24BonusEventHandler.bonusAdded=0;
			BX.ajax({  
				url: '/bitrix/components/skyweb24/order.ajax.bonus/ajax.php',
					data: {type:'bonus_added'},
					method: 'POST',
					dataType: 'json',
					timeout: 30,
					async: true,
					processData: true,
					scriptsRunFirst: true,
					emulateOnload: true,
					start: true,
					cache: false,
					onsuccess: function(data){
						Skyweb24BonusEventHandler.bonusAdded=data;
						let isInsertAddedBlock=BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector('.bx-soa-cart-total-line.skyweb-bonuses-added');
						if(isInsertAddedBlock){
							BX.remove(isInsertAddedBlock);
						}
						if(Skyweb24BonusEventHandler.bonusAdded.bonus>0){
							let addedBlock=
								BX.create('DIV', {
									props: {className: 'bx-soa-cart-total-line bx-soa-cart-total-line-highlighted skyweb-bonuses-added'},
									children:[
										BX.create('SPAN', {
											props: {className: 'bx-soa-cart-t'},
											html: BX.message('hasbonusAdded')
										}),
										BX.create('SPAN', {
											props: {className: 'bx-soa-cart-d'},
											html: Skyweb24BonusEventHandler.bonusAdded.bonus_format,
										})
									]
								});
								BX.Sale.OrderAjaxComponent.totalInfoBlockNode.insertBefore(addedBlock, BX.Sale.OrderAjaxComponent.totalInfoBlockNode.querySelector('.bx-soa-cart-total-line-total'));
						}
					},
					onfailure: function(data){
						console.log(data)
					}
			});
		}
	}
	setBonusHandler() {
		Skyweb24BonusEventHandler.updateBonusAdded();
		var currencyData = BX.Sale.OrderAjaxComponent.result.GRID.ROWS;
		var currency = 'RUB';
		for (var i in currencyData) {
			currency = currencyData[i].data.CURRENCY;
			break;
		}
		var SaleOrderAjaxProp = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties;
		var issetBonus = false;
		Skyweb24BonusEventHandler.propId = '';
		var tmpBonusPosition = document.getElementById('bx-soa-bonus-hidden').parentElement;

		for (var i in SaleOrderAjaxProp) {
			if (SaleOrderAjaxProp[i].CODE == 'skyweb24_bonus' && parseInt(skyweb24_current_bonus)>0 && atob(skyweb24_bonus_max)>0) {
				Skyweb24BonusEventHandler.propId = SaleOrderAjaxProp[i].ID;
				issetBonus = true;
			}else if(SaleOrderAjaxProp[i].CODE == 'skyweb24_bonus'){
				Skyweb24BonusEventHandler.propId2=SaleOrderAjaxProp[i].ID;
			}
		}
		Skyweb24BonusEventHandler.removeBonusInBlock();
		if (issetBonus) {
			if (document.getElementById('bx-soa-bonus') == null) {
				var tmpSaoContainer = tmpBonusPosition.parentElement;
				var Block = BX.create('DIV', {
					props: { className: 'bx-soa-section bx-active', id: 'bx-soa-bonus' }
				});
				var title = BX.create('DIV', {
					props: { className: 'bx-soa-section-title-container' },
					children: [
						BX.create('H2', {
							props: { className: 'bx-soa-section-title col-sm-9' },
							children: [
								BX.create('SPAN', { props: { className: 'bx-soa-section-title-count' } }),
								BX.create('TEXT', { text: skyweb24_bonus_messages.title })
							]
						}),
						BX.create('DIV', {
							props: { className: 'col-xs-12 col-sm-3 text-right' },
							children: [
								BX.create('A', {
									props: {
										className: 'bx-soa-editstep',
										href: 'javascript:void(0);',
									},
									text: skyweb24_bonus_messages.edit
								}
								)
							]
						})
					]
				});
				var content = BX.Sale.OrderAjaxComponent.getNewContainer();
				Block.appendChild(title)
				Block.appendChild(content)
				BX.bind(title, 'click', BX.proxy(function (e) {
					BX.Sale.OrderAjaxComponent.showByClick(e);
				}, BX.Sale.OrderAjaxComponent));
				BX.bind(title.querySelector('.bx-soa-editstep'), 'click', BX.proxy(function (e) {
					BX.Sale.OrderAjaxComponent.showByClick(e);
				}, BX.Sale.OrderAjaxComponent));
				tmpSaoContainer.insertBefore(Block, tmpBonusPosition);
			} else {
				var bonusBlock = document.querySelector('#bx-soa-bonus');
				var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
				tmpContent.innerHTML = '';
				tmpContent.appendChild(
					BX.create('DIV', {
						props:{className:'row'},
						children: [
							BX.create('DIV', {
								props: { className: 'col-xs-12' },
								html: '<strong>' + skyweb24_bonus_messages.bonus + '</strong>' + BX.Currency.currencyFormat(Skyweb24BonusEventHandler.getBonusValue(), currency, true) + '<br><strong>' + skyweb24_bonus_messages.all_bonus + '</strong>' + skyweb24_current_bonus
							})
						]
					})
				)
			}
			Skyweb24BonusEventHandler.setBonusInBlock();
		} else {
			Skyweb24BonusEventHandler.removeBonusInBlock();
			Skyweb24BonusEventHandler.initialize = false;
			Skyweb24BonusEventHandler.propId = '';
			if (document.getElementById('bx-soa-bonus') == null) {
				
				if(Skyweb24BonusEventHandler.propId2){
					let propInput=document.querySelector('[name=ORDER_PROP_'+Skyweb24BonusEventHandler.propId2+']');
					if(propInput){
						let parentInput=propInput.parentNode.parentNode;
						parentInput.parentNode.removeChild(parentInput);
					}
				}
				
				var tmpSaoContainer = tmpBonusPosition.parentElement;
				var Block = BX.create('DIV', {
					props: { className: 'bx-soa-section bx-active', id: 'bx-soa-bonus' }
				});
				var title = BX.create('DIV', {
					props: { className: 'bx-soa-section-title-container' },
					children: [
						BX.create('H2', {
							props: { className: 'bx-soa-section-title col-sm-9' },
							children: [
								BX.create('SPAN', { props: { className: 'bx-soa-section-title-count' } }),
								BX.create('TEXT', { text: skyweb24_bonus_messages.title })
							]
						}),
						BX.create('DIV', {
							props: { className: 'col-xs-12 col-sm-3 text-right' },
							children: [
								BX.create('A', {
									props: {
										className: 'bx-soa-editstep',
										href: 'javascript:void(0);',
									},
									text: skyweb24_bonus_messages.edit
								}
								)
							]
						})
					]
				});
				var content = BX.Sale.OrderAjaxComponent.getNewContainer();
				Block.appendChild(title)
				Block.appendChild(content)
				BX.bind(title, 'click', BX.proxy(function (e) {
					BX.Sale.OrderAjaxComponent.showByClick(e);
				}, BX.Sale.OrderAjaxComponent));
				tmpSaoContainer.insertBefore(Block, tmpBonusPosition);
			} else {
				var bonusBlock = document.querySelector('#bx-soa-bonus');
				var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
				tmpContent.innerHTML = '';
				tmpContent.appendChild(
					BX.create('DIV', {
						props: { className: 'row' },
						children: [
							BX.create('DIV', {
								props: { className: 'col-xs-12' },
								children: [
									BX.create('strong', {
										text: skyweb24_bonus_messages.no_bonus
									})
								]
							})
						]
					})
				)
			}

			Skyweb24BonusEventHandler.removeBonusInBlock();
			document.getElementById('bx-soa-bonus').remove();
		}
		var buttons = document.querySelectorAll('.bx-soa-section .pull-left.btn,.bx-soa-section .pull-right.btn, .bx-soa-section .bx-soa-editstep, .bx-soa-section-title-container');
		for (var i = 0; i < buttons.length; i++) {
			var cButton = buttons[i];
			BX.bind(cButton, 'click', BX.proxy(Skyweb24BonusEventHandler.Skyweb24BonusHandler, Skyweb24BonusEventHandler));
		}
	}
	setBonusInBlock() {
		var currencyData = BX.Sale.OrderAjaxComponent.result.GRID.ROWS;
		var currency = 'RUB';
		for (var i in currencyData) {
			currency = currencyData[i].data.CURRENCY;
			break;
		}
		var orderTotalPrice = BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_PRICE;
		var bonusNonFormat = Skyweb24BonusEventHandler.getBonusValue();
		var newOrderTotalPrice = BX.Currency.currencyFormat((orderTotalPrice - bonusNonFormat), currency, true);
		var bonusTMP = BX.Currency.currencyFormat(bonusNonFormat, currency, true);
		var totalBlock = document.getElementById('bx-soa-total');
		if (totalBlock.querySelector('.bx-soa-cart-total-line.bonus') == null) {
			var button = totalBlock.querySelector('.bx-soa-cart-total-line.bx-soa-cart-total-line-total');
			var bonusBlock
			var bonusLine = BX.create('DIV', {
				props: { className: 'bx-soa-cart-total-line bx-soa-cart-total-line-highlighted bonus' },
				children: [
					BX.create('SPAN', {
						props: { className: 'bx-soa-cart-t' },
						text: skyweb24_bonus_messages.bonus_pay_total
					}),
					BX.create('SPAN', {
						props: { className: 'bx-soa-cart-d' },
						text: bonusTMP
					})
				]
			});
			totalBlock.querySelector('.bx-soa-cart-total').insertBefore(bonusLine, button);
			totalBlock.querySelector('.bx-soa-cart-total-line.bx-soa-cart-total-line-total .bx-soa-cart-d').innerHTML = newOrderTotalPrice;
		} else {
			totalBlock.querySelector('.bx-soa-cart-total-line.bx-soa-cart-total-line-total .bx-soa-cart-d').innerHTML = newOrderTotalPrice;
			totalBlock.querySelector('.bx-soa-cart-total-line.bonus .bx-soa-cart-d').innerHTML = bonusTMP;
		}

	}
	removeBonusInBlock() {
		var totalBlock = document.getElementById('bx-soa-total');
		if (totalBlock.querySelector('.bx-soa-cart-total-line.bonus') !== null) {
			totalBlock.querySelector('.bx-soa-cart-total-line.bonus').outerHTML = '';
		}
	}
	bonusFade() {
		var currencyData = BX.Sale.OrderAjaxComponent.result.GRID.ROWS;
		var currency = 'RUB';
		for (var i in currencyData) {
			currency = currencyData[i].data.CURRENCY;
			break;
		}
		var bonusBlock = document.querySelector('#bx-soa-bonus');
		BX.bind(bonusBlock.querySelector('.bx-soa-section-title-container'), 'click', BX.proxy(BX.Sale.OrderAjaxComponent.showByClick, BX.Sale.OrderAjaxComponent));

		bonusBlock.querySelector('.bx-soa-editstep').style = '';
		var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
		var tmpFooter = BX.findParent(tmpContent.querySelector('.bx-soa-more-btn'), { className: "row bx-soa-more" })
		if (Skyweb24BonusEventHandler.propId != '') {
			Skyweb24BonusEventHandler.removeBonusFromProp();
			if (BX.firstChild(tmpContent))
				Skyweb24BonusEventHandler.hiddenBonusBlock.appendChild(BX.firstChild(tmpContent));
		}
		tmpContent.appendChild(
			BX.create('DIV', {
				props: { className: 'row' },
				children: [
					BX.create('DIV', {
						props: { className: 'col-xs-12' },
						html: '<strong>' + skyweb24_bonus_messages.bonus + '</strong>' + BX.Currency.currencyFormat(Skyweb24BonusEventHandler.getBonusValue(), currency, true) + '<br><strong>' + skyweb24_bonus_messages.all_bonus + '</strong>' + skyweb24_current_bonus
					})
				]
			})
		)
		BX.remove(tmpFooter);
		Skyweb24BonusEventHandler.setBonusInBlock();
	}

	removeBonusFromProp() {
		Skyweb24BonusEventHandler.removeBonusInBlock();
		if (Skyweb24BonusEventHandler.propId != '') {
			var propsBlock = document.querySelector('#bx-soa-properties');
			for (var i = 0; i < propsBlock.classList.length; i++) {
				if (propsBlock.classList[i] == 'bx-selected') {
					var tmpPropBlock = propsBlock.querySelector('[data-property-id-row="' + Skyweb24BonusEventHandler.propId + '"]');
					if (tmpPropBlock) {
						BX.remove(tmpPropBlock);
					}
					break;
				}
			}
		}
	}
	bonusActive() {
		var currencyData = BX.Sale.OrderAjaxComponent.result.GRID.ROWS;
		var currency = 'RUB';
		for (var i in currencyData) {
			currency = currencyData[i].data.CURRENCY;
			break;
		}
		Skyweb24BonusEventHandler.setBonusInBlock();
		var bonusBlock = document.querySelector('#bx-soa-bonus');
		var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
		tmpContent.innerHTML = '';
		var sections = BX.Sale.OrderAjaxComponent.orderBlockNode.querySelectorAll('.bx-soa-section.bx-active');
		var thisBlock = 0;

		if (!Skyweb24BonusEventHandler.initialize) {
			var propsNode = BX.create('DIV', { props: { className: 'row' } });
			var propsItemsContainer = BX.create('DIV', { props: { className: 'col-sm-12 bx-soa-customer' } });
			var group, propsIterator, property;
			var groupIterator = BX.Sale.OrderAjaxComponent.propertyCollection.getGroupIterator();
			while (group = groupIterator()) {
				propsIterator = group.getIterator();
				while (property = propsIterator()) {
					if (property.getId() == Skyweb24BonusEventHandler.propId) {
						BX.Sale.OrderAjaxComponent.getPropertyRowNode(property, propsItemsContainer, false);
					}
				}
			}
			propsNode.appendChild(propsItemsContainer)
			tmpContent.appendChild(propsNode);
			Skyweb24BonusEventHandler.initialize = true;
			propsItemsContainer.querySelector('.form-group').classList.add('row');
			propsItemsContainer.querySelector('.form-group>label').style = "display:none;";
			propsItemsContainer.querySelector('.form-group>div').classList.add('col-sm-4');
			propsItemsContainer.querySelector('.form-group>div input').style = "width:80%; display:inline;";
			propsItemsContainer.querySelector('.form-group>div').appendChild(BX.create('span', { text: BX.Currency.getCurrencyFormat(currency).FORMAT_STRING.replace('#', '') }));

			propsItemsContainer.querySelector('.form-group').prepend(
				BX.create('label', {
					props: { className: 'col-sm-12' },
					html: skyweb24_bonus_messages.all_bonus + skyweb24_current_bonus + '<br>' + skyweb24_bonus_messages.maximum_bonus + BX.Currency.currencyFormat(atob(skyweb24_bonus_max), currency, true)
				}));
		} else {
			if (tmpContent && BX.firstChild(Skyweb24BonusEventHandler.hiddenBonusBlock)) tmpContent.appendChild(BX.firstChild(Skyweb24BonusEventHandler.hiddenBonusBlock));
		}
		for (var i = 0; i < sections.length; i++) {
			if (sections[i].id == 'bx-soa-bonus') { thisBlock = i + 1; }
		}
		var buttons = [];
		if (thisBlock != 1) {
			buttons.push(
				BX.create('A', {
					props: { href: 'javascript:void(0)', className: 'pull-left btn btn-default btn-md' },
					html: BX.Sale.OrderAjaxComponent.params.MESS_BACK,
					events: {
						click: BX.proxy(function (e) { BX.Sale.OrderAjaxComponent.clickPrevAction(e); Skyweb24BonusEventHandler.bonusFade(); }, BX.Sale.OrderAjaxComponent)
					}
				})
			);
		}
		if (thisBlock != sections.length) {
			buttons.push(
				BX.create('A', {
					props: { href: 'javascript:void(0)', className: 'pull-right btn btn-default btn-md' },
					html: BX.Sale.OrderAjaxComponent.params.MESS_FURTHER,
					events: { click: BX.proxy(function (e) { BX.Sale.OrderAjaxComponent.clickNextAction(e); Skyweb24BonusEventHandler.bonusFade(); }, BX.Sale.OrderAjaxComponent) }
				})
			);
		}

		tmpContent.style.display = '';
		tmpContent.appendChild(
			BX.create('DIV', {
				props: { className: 'row bx-soa-more' },
				children: [
					BX.create('DIV', {
						props: { className: 'bx-soa-more-btn col-xs-12 col' },
						children: buttons
					})
				]
			})
		);
		bonusBlock.appendChild(tmpContent); Skyweb24BonusEventHandler.propId
		var input = bonusBlock.querySelector('#soa-property-' + Skyweb24BonusEventHandler.propId + '[name="ORDER_PROP_' + Skyweb24BonusEventHandler.propId + '"]');
	}
	falseBonusActive() {
		Skyweb24BonusEventHandler.removeBonusInBlock();
		var bonusBlock = document.querySelector('#bx-soa-bonus');
		var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
		tmpContent.innerHTML = '';
		var sections = BX.Sale.OrderAjaxComponent.orderBlockNode.querySelectorAll('.bx-soa-section.bx-active');
		var thisBlock = 0;
		for (var i = 0; i < sections.length; i++) {
			if (sections[i].id == 'bx-soa-bonus') { thisBlock = i + 1; }
		}
		var buttons = [];
		if (thisBlock != 1) {
			buttons.push(
				BX.create('A', {
					props: { href: 'javascript:void(0)', className: 'pull-left btn btn-default btn-md' },
					html: BX.Sale.OrderAjaxComponent.params.MESS_BACK,
					events: {
						click: BX.proxy(function (e) { BX.Sale.OrderAjaxComponent.clickPrevAction(e); Skyweb24BonusEventHandler.falseBonusFade(); }, BX.Sale.OrderAjaxComponent)
					}
				})
			);
		}
		if (thisBlock != sections.length) {
			buttons.push(
				BX.create('A', {
					props: { href: 'javascript:void(0)', className: 'pull-right btn btn-default btn-md' },
					html: BX.Sale.OrderAjaxComponent.params.MESS_FURTHER,
					events: { click: BX.proxy(function (e) { BX.Sale.OrderAjaxComponent.clickNextAction(e); Skyweb24BonusEventHandler.falseBonusFade(); }, BX.Sale.OrderAjaxComponent) }
				})
			);
		}

		tmpContent.appendChild(
			BX.create('DIV', {
				props: { className: 'row' },
				children: [
					BX.create('DIV', {
						props: { className: 'col-xs-12' },
						children: [
							BX.create('P', {
								text: skyweb24_bonus_messages.no_bonus
							})
						]
					})
				]
			})
		)
		tmpContent.appendChild(
			BX.create('DIV', {
				props: { className: 'row bx-soa-more' },
				children: [
					BX.create('DIV', {
						props: { className: 'bx-soa-more-btn col-xs-12' },
						children: buttons
					})
				]
			})
		);
	}
	falseBonusFade() {
		var bonusBlock = document.querySelector('#bx-soa-bonus');
		var tmpContent = bonusBlock.querySelector('.bx-soa-section-content.container-fluid');
		tmpContent.innerHTML = '<strong>' + skyweb24_bonus_messages.no_bonus + '</strong>';
		Skyweb24BonusEventHandler.removeBonusInBlock();
	}
	Skyweb24BonusHandler() {
		setTimeout(function () {
			var bonusBlock = document.querySelector('#bx-soa-bonus'),
				isBonusBlock = false;
			if (bonusBlock) {
				for (var i = 0; i < bonusBlock.classList.length; i++) {
					if (bonusBlock.classList[i] == 'bx-selected') {
						isBonusBlock = true;
						break;
					}
				}
				if (Skyweb24BonusEventHandler.propId != '') {
					if (isBonusBlock == false) {
						if (Skyweb24BonusEventHandler.initialize) {
							Skyweb24BonusEventHandler.bonusFade();
						} else {
							Skyweb24BonusEventHandler.removeBonusFromProp();
						}
					} else {
						Skyweb24BonusEventHandler.bonusActive();
					}
				} else {
					if (isBonusBlock == false) {
						Skyweb24BonusEventHandler.falseBonusFade();
					} else {
						Skyweb24BonusEventHandler.falseBonusActive();
					}
				}
			}
		}, 10);
	}
}


var Skyweb24BonusEventHandler;
BX.ready(function () {
	Skyweb24BonusEventHandler = new Skyweb24BonusEvent();
	Skyweb24BonusEventHandler.setBonusHandler();
	BX.addCustomEvent('onAjaxSuccess', Skyweb24BonusEventHandler.setBonusHandler);
});
