var skyweb24OrderAjaxBonus={
    init:function(bonus_max, current_bonus, current_bonus_format, currency){
        this.bonuses={};
        this.blocks={};
        this.params={};
        this.bonuses.max=(atob(bonus_max))*1;
        this.bonuses.current=0;
        this.bonuses.currency=currency;
        this.bonuses.added=0;
        this.bonuses.added_format='';
        this.bonuses.avialable=current_bonus;
        this.bonuses.avialable_format=current_bonus_format;
        this.getParamFromSOA();
        this.getBonusAdded();
        this.setEventPaginator();
        this.blocks.payment=document.querySelector('#bx-soa-bonus-2').innerHTML;
        BX.addCustomEvent('onAjaxSuccess', function(data,param) {
            if(data && data.order && param && param.url && param.url=='/bitrix/components/bitrix/sale.order.ajax/ajax.php'){
                skyweb24OrderAjaxBonus.getParamFromSOA();
                skyweb24OrderAjaxBonus.getBonusAdded();
                skyweb24OrderAjaxBonus.setEventPaginator();
            }
        });
    },
    setEventPaginator:function(){
        if(this.soaForm){
            let navigationbuttons=document.querySelectorAll('#bx-soa-order-form .bx-soa-editstep, #bx-soa-order-form .pull-left, #bx-soa-order-form .pull-right');
            if(navigationbuttons.length>0){
                for(let nextbutton of navigationbuttons){
                   nextbutton.addEventListener('click', function(){
                       skyweb24OrderAjaxBonus.setPayBlock();
                   });
                }
            }
        }else{
            setTimeout(function(){skyweb24OrderAjaxBonus.setEventPaginator()}, 100);
        }
    },
    getParamFromSOA:function(){//this function update parameters from OrderAjaxComponent
        if(BX.Sale.OrderAjaxComponent.result && BX.Sale.OrderAjaxComponent.result.ORDER_PROP){
            var SaleOrderAjaxProp = BX.Sale.OrderAjaxComponent.result.ORDER_PROP.properties;
			if(BX.Sale.OrderAjaxComponent.result.sw24_loyalty_max_bonus!==undefined){
				this.bonuses.max=parseFloat(BX.Sale.OrderAjaxComponent.result.sw24_loyalty_max_bonus);
			}
			if(this.bonuses.max==0 && this.bonuses.current>0){
				this.bonuses.current=0;
				
			}
            for (var i in SaleOrderAjaxProp) {
                if (SaleOrderAjaxProp[i].CODE == 'skyweb24_bonus') {
                    this.soaForm=document.querySelector('#bx-soa-order-form');
                    this.params.bonusPayProp=SaleOrderAjaxProp[i].ID;
                    this.params.bonusPayPropInput=this.soaForm.querySelector('#soa-property-'+this.params.bonusPayProp);
					//fix for very custom sale.order.ajax
					if(!this.params.bonusPayPropInput){
						this.params.bonusPayPropInput=BX.create('input', {
							attrs:{type:'hidden', value:this.bonuses.current, id:'soa-property-'+SaleOrderAjaxProp[i].ID, name:"ORDER_PROP_"+SaleOrderAjaxProp[i].ID}
						});
						this.soaForm.appendChild(this.params.bonusPayPropInput);
					}
					//e. o. fix for very custom sale.order.ajax
					
                    this.params.bonusPayPropblock=this.soaForm.querySelector('[data-property-id-row="'+this.params.bonusPayProp+'"]');
					if(this.params.bonusPayPropblock){
						skyweb24OrderAjaxBonus.params.bonusPayPropblock.style.display='none';
					}
                    //if innerpay is run
                    if(BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY && BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY<this.bonuses.max){
                        this.bonuses.max=BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY;
                    }

                    //show blocks
                    this.setPayBlock();
                    break;
                }
            }
        }else{
            setTimeout(function(){skyweb24OrderAjaxBonus.getParamFromSOA();}, 100);
        }
    },
    getBonusAdded:function(){    
        BX.ajax({  
            url: '/bitrix/components/skyweb24/order.ajax.bonus2/ajax.php',
                data: {type:'bonus_added', payed:this.bonuses.current},
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
                    if(data.bonus){
                        skyweb24OrderAjaxBonus.bonuses.added=data.bonus;
                        skyweb24OrderAjaxBonus.bonuses.added_format=data.bonus_format;
                    }else{
						if(skyweb24OrderAjaxBonus.bonuses.added){
							delete skyweb24OrderAjaxBonus.bonuses.added;
						}
						if(skyweb24OrderAjaxBonus.bonuses.added_format){
							delete skyweb24OrderAjaxBonus.bonuses.added_format;
						}
					}
					skyweb24OrderAjaxBonus.setTotalblock();
                },
                onfailure: function(data){
                    //console.log(data)
                }
        });
    },
    setPayBlock:function(){
        setTimeout(function(){
            let descContainer=skyweb24OrderAjaxBonus.soaForm.querySelector('#bx-soa-paysystem .bx-soa-pp-desc-container .bx-soa-pp-company');
            if(descContainer && skyweb24OrderAjaxBonus.bonuses.max>0 && !BX('pay_loyalty_bonus')){
                let cHTML=skyweb24OrderAjaxBonus.blocks.payment.replace('#MAX_BONUS#', skyweb24OrderAjaxBonus.bonuses.max).replace('#CURRENT_BONUS#', skyweb24OrderAjaxBonus.bonuses.current).replace('#ALL_BONUS#', skyweb24OrderAjaxBonus.bonuses.avialable_format).replace('#MAX_BONUS_FORMAT#', BX.Currency.currencyFormat(skyweb24OrderAjaxBonus.bonuses.max, skyweb24OrderAjaxBonus.bonuses.currency, true))
                descContainer.insertBefore(BX.create('div', {
                    attrs:{id:'pay_loyalty_bonus'},
                    html:cHTML
                }), descContainer.firstChild);
                skyweb24OrderAjaxBonus.params.bonusInput=BX('pay_loyalty_bonus').querySelector('input[type=number]');
                skyweb24OrderAjaxBonus.setTotalblock();
                skyweb24OrderAjaxBonus.params.bonusInput.addEventListener('change', function(){
                    skyweb24OrderAjaxBonus.changePayBonus();
                });
                skyweb24OrderAjaxBonus.params.bonusInput.addEventListener('click', function(e){
                    e.stopPropagation();
                    return false;
                });
            }
        }, 200);
    },
    setTotalblock:function(){
		if(this.soaForm){
			let totalBlock=this.soaForm.querySelector('#bx-soa-total .bx-soa-cart-total');
			if(totalBlock){
				let totalLine=totalBlock.querySelector('.bx-soa-cart-total-line-total');
				if(this.bonuses.added>0){
					let addedBlock=totalBlock.querySelector('#loyalty_added');
					if(!addedBlock){
						addedBlock=BX.create('div', {
							attrs:{id:'loyalty_added', className:'bx-soa-cart-total-line'},
							html:' '
						});
						totalBlock.insertBefore(addedBlock,totalLine);
					}
					addedBlock.innerHTML='<span class="bx-soa-cart-t">'+BX.message('hasbonusAdded')+'</span><span class="bx-soa-cart-d">~'+this.bonuses.added_format+'</span>';
				}else{
					if(BX('loyalty_added')){
						BX('loyalty_added').remove();
					}
				}
				if(this.bonuses.current>0){
					let payedBlock=totalBlock.querySelector('#loyalty_payed');
					if(!payedBlock){
						payedBlock=BX.create('div', {
							attrs:{id:'loyalty_payed', className:'bx-soa-cart-total-line'},
							html:' '
						});
						totalBlock.insertBefore(payedBlock,totalLine);
					}
					payedBlock.innerHTML='<span class="bx-soa-cart-t">'+BX.message('bonus_pay_total')+'</span><span class="bx-soa-cart-d">'+BX.Currency.currencyFormat(this.bonuses.current, this.bonuses.currency, true)+'</span>';
				}else if(BX('loyalty_payed')){
					BX('loyalty_payed').remove();
                }
                let basketPrice=BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY?BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY:BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_PRICE;
                basketPrice-=this.bonuses.current;
                totalLine.querySelector('.bx-soa-cart-d').innerHTML=BX.Currency.currencyFormat(basketPrice, this.bonuses.currency, true);
			}
			let duplicateTotalBlock=this.soaForm.querySelector('#bx-soa-total-mobile .bx-soa-cart-total');
			if(duplicateTotalBlock){
				let totalLine=duplicateTotalBlock.querySelector('.bx-soa-cart-total-line-total');
				if(this.bonuses.added>0){
					let addedBlock=duplicateTotalBlock.querySelector('#loyalty_added2');
					if(!addedBlock){
						addedBlock=BX.create('div', {
							attrs:{id:'loyalty_added2', className:'bx-soa-cart-total-line'},
							html:' '
						});
						duplicateTotalBlock.insertBefore(addedBlock,totalLine);
					}
					addedBlock.innerHTML='<span class="bx-soa-cart-t">'+BX.message('hasbonusAdded')+'</span><span class="bx-soa-cart-d">~'+this.bonuses.added_format+'</span>';
				}else{
					if(BX('loyalty_added2')){
						BX('loyalty_added2').remove();
					}
				}
				if(this.bonuses.current>0){
					let payedBlock=duplicateTotalBlock.querySelector('#loyalty_payed2');
					if(!payedBlock){
						payedBlock=BX.create('div', {
							attrs:{id:'loyalty_payed2', className:'bx-soa-cart-total-line'},
							html:' '
						});
						duplicateTotalBlock.insertBefore(payedBlock,totalLine);
					}
					payedBlock.innerHTML='<span class="bx-soa-cart-t">'+BX.message('bonus_pay_total')+'</span><span class="bx-soa-cart-d">'+BX.Currency.currencyFormat(this.bonuses.current, this.bonuses.currency, true)+'</span>';
				}else if(BX('loyalty_payed2')){
					BX('loyalty_payed2').remove();
                }
                let basketPrice=BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY?BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_LEFT_TO_PAY:BX.Sale.OrderAjaxComponent.result.TOTAL.ORDER_TOTAL_PRICE;
                basketPrice-=this.bonuses.current;
                totalLine.querySelector('.bx-soa-cart-d').innerHTML=BX.Currency.currencyFormat(basketPrice, this.bonuses.currency, true);
			}
		}
    },
    changePayBonus:function(){
		//fix for validation float value
		if(this.params.bonusInput.value.indexOf('.')>-1 || this.params.bonusInput.value.indexOf(',')>-1){
			this.params.bonusInput.step='.01';
		}
		
        if(this.params.bonusInput.value>this.bonuses.max){
            this.params.bonusInput.value=this.bonuses.max;
        }
        this.bonuses.current=this.params.bonusInput.value;
        this.params.bonusPayPropInput.value=this.bonuses.current;
        //this.setTotalblock();
        this.reloadTotalblock();
    },
    reloadTotalblock:function(){
        if(this.reloadTimeTotalBlock){
            clearTimeout(this.reloadTimeTotalBlock);
        }
        this.reloadTimeTotalBlock=setTimeout(function(){
            skyweb24OrderAjaxBonus.getBonusAdded();
        }, 500);
    }
}