<?php
namespace PickPoint\DeliveryService\Admin;

use \PickPoint\DeliveryService\Bitrix\Tools;
use \PickPoint\DeliveryService\Bitrix\Adapter;
use \PickPoint\DeliveryService\CourierHandler;
use \PickPoint\DeliveryService\Option;
use \Bitrix\Main\Localization\Loc;

/**
 * Class CourierForm
 * @package PickPoint\DeliveryService\Admin
 */
class CourierForm
{
	protected static $MODULE_ID  = 'pickpoint.deliveryservice';
	protected static $MODULE_LBL = 'PICKPOINT_DELIVERYSERVICE_';
	
	protected static $arButtons = [];
	
	protected static $ikn = false;	
	
	/**
     * Create courier call form window
     */
	public static function makeFormWindow()
	{
        global $APPLICATION;
        $APPLICATION->AddHeadScript(Tools::getJSPath().'wndController.js');
        \CJSCore::Init(array('jquery'));

		self::$ikn = Option::get('pp_ikn_number');
		
        self::generateFormHtml();
        self::loadFormCSS();
        self::loadFormJS();
    }

	/**
     * Generate HTML for courier call form window
     */
    protected static function generateFormHtml()
    {
        ?>
        <div id="<?=self::$MODULE_LBL?>PLACEFORFORM">
            <table id="<?=self::$MODULE_LBL?>wndOrder">
                <tbody>
				<?php Tools::placeFormRow('courier_ikn', 'sign', self::$ikn."<input type='hidden' id='".self::$MODULE_LBL."courier_ikn' value='".self::$ikn."'>");?>
				<?php Tools::placeFormHeaderRow('COURIER_MAIN');?>
				<?php Tools::placeFormRow('courier_fio', 'text', '');?>
				<?php Tools::placeFormRow('courier_phone', 'text', '');?>
				<tr>
					<td></td>
					<td><small><i><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_SIGN_courier_phone')?></i></small></td>
				</tr>
				<?php $cities = CourierHandler::getCourierCitylist();?>
				<?php Tools::placeFormRow('courier_city', 'select', key($cities), $cities, "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.checkRestrictions()\"");?>
				<?php Tools::placeFormRow('courier_address', 'textbox', '');?>
				<tr>
					<td><?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_LBL_courier_date')?></td>
					<td>
                        <div class="adm-input-wrap adm-input-wrap-calendar">
                            <input class="adm-input adm-input-calendar" id="<?=self::$MODULE_LBL?>courier_date" disabled="" name="<?=self::$MODULE_LBL?>courier_date" size="22" value="<?php echo \ConvertTimeStamp()?>" type="text">
                            <span class="adm-calendar-icon" onclick="BX.calendar({node:this, field:'<?=self::$MODULE_LBL?>courier_date', form: '', bTime: false, bHideTime: true, callback_after: <?=self::$MODULE_LBL?>export.getPage('main').events.checkRestrictions}); <?=self::$MODULE_LBL?>export.getPage('main').changeCalendar();"></span>
                        </div>
					</td>
                </tr>
				<tr>
					<td></td>
					<td><span id="<?=self::$MODULE_LBL?>courier_date_error" class="<?=self::$MODULE_LBL?>warning"></span></td>
				</tr>
				<?php $intervals = Adapter::getCourierTimeIntervals();?>
				<?php Tools::placeFormRow('courier_time', 'select', key($intervals), $intervals/*, "onchange=\"".self::$MODULE_LBL."export.getPage('main').events.checkRestrictions()\""*/);?>
				<?php Tools::placeFormRow('courier_number', 'text', '');?>
				<?php Tools::placeFormRow('courier_weight', 'text', '');?>
				<?php Tools::placeFormRow('courier_comment', 'textbox', '');?>
                </tbody>
			</table>
        </div>
        <?php
    }

	/**
     * Courier call form JS. Pretty Mebiys scripts inside.
     */
    protected static function loadFormJS()
    {
		?>
        <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
        <script type="text/javascript">
            var <?=self::$MODULE_LBL?>export = new pickpoint_deliveryservice_adminInterface({
                'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
                'label'    : '<?=self::$MODULE_ID?>',
                'logging'  : true
            });
			
			<?php // Additional common variables?>
            <?=self::$MODULE_LBL?>export.expander({
				MOCityIds : <?=\CUtil::PhpToJSObject(CourierHandler::getMoscowRegionCityIDs())?>,				
            });
        </script>
		<script type="text/javascript">
			<?=self::$MODULE_LBL?>export.addPage('main', {      
				init: function () {      
					var html = $('#<?=self::$MODULE_LBL?>PLACEFORFORM').html();
					$('#<?=self::$MODULE_LBL?>PLACEFORFORM').html(' ');

					if (!html) {
						this.self.log('unable to load data');
					}
					else {
						<?php self::addButton("<input id='".self::$MODULE_LBL."sender' type='button' onclick='".self::$MODULE_LBL."export.getPage(\"main\").send()' value='".Loc::getMessage('PICKPOINT_DELIVERYSERVICE_BTN_COURIER_SEND')."'>")?>;
						
						this.mainWnd = new pickpoint_deliveryservice_wndController({
							title: '<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_HDR_COURIER_WND')?>',
							content: html,
							resizable: true,
							draggable: true,
							height: '500',
							width: '515',
							buttons: <?=\CUtil::PhpToJSObject(self::$arButtons)?>
						});
					}

					// Reload methods: just to be sure
					this.events(this);			
					this.onSend(this);
					
					this.events.checkRestrictions();     
				},

				mainWnd: false,

				open: function () {
					if (this.mainWnd)
						this.mainWnd.open();           
				},
				
				// Calendar mod
				changeCalendar: function () {
					var block = $('[id ^= "calendar_popup_"]'); // calendar
					var links = block.find(".bx-calendar-cell"); // days elements
					
					$('.bx-calendar-left-arrow').attr({'onclick': '<?=self::$MODULE_LBL?>export.getPage("main").changeCalendar();',});
					$('.bx-calendar-right-arrow').attr({'onclick': '<?=self::$MODULE_LBL?>export.getPage("main").changeCalendar();',});
					$('.bx-calendar-top-month').attr({'onclick': '<?=self::$MODULE_LBL?>export.getPage("main").changeCalendarMonth();',});
					$('.bx-calendar-top-year').attr({'onclick': '<?=self::$MODULE_LBL?>export.getPage("main").changeCalendarYear();',}); 
								
					var date = new Date();
					for (var i = 0; i < links.length; i++)
					{
						var d = date.valueOf();
						var atrDate = links[i].attributes['data-date'].value;
						var linkDate = new Date(parseInt(atrDate));				
									
						if ((linkDate.getDay() === 6 || linkDate.getDay() === 0) || (date - atrDate > 24 * 60 * 60 * 1000)) {
							// Skip saturday, sunday and old good days
							$('[data-date="' + atrDate +'"]').addClass("bx-calendar-date-hidden disabled"); 
						}
					}
				},
				
				changeCalendarMonth: function() {			
					var block = $('[id ^= "calendar_popup_month_"]'); 
					var links = block.find(".bx-calendar-month"); 			
					var year = $('[id ^= "calendar_popup_"]').find('.bx-calendar-top-year').html();
					
					var currentDate = new Date();
					
					for (var i = 0; i < links.length; i++)
					{
						var month = links[i].attributes['data-bx-month'].value;
										
						if (currentDate.getFullYear() >= parseInt(year) && currentDate.getMonth() > month) {
							$('[data-bx-month="' + month +'"]').addClass("disabled"); 
						}
						else {
							if ($('[data-bx-month="' + month +'"]').hasClass("disabled"))
								$('[data-bx-month="' + month +'"]').removeClass("disabled"); 
							$(links[i]).attr({'onclick': 'setTimeout(<?=self::$MODULE_LBL?>export.getPage("main").changeCalendar, 200)',}); 
						}
					}			
				},
				
				changeCalendarYear: function() {
					var block = $('[id ^= "calendar_popup_year_"]'); 
					
					var link = block.find(".bx-calendar-year-input"); // Hide year input 
					$(link).css('display', 'none');
					
					var links = block.find(".bx-calendar-year-number");
					var currentDate = new Date();
					
					for (var i = 0; i < links.length; i++)
					{
						var year = links[i].attributes['data-bx-year'].value;
						if (year < currentDate.getFullYear())
							$('[data-bx-year="' + year +'"]').addClass("disabled"); 
						else
							$(links[i]).attr({'onclick': 'setTimeout(<?=self::$MODULE_LBL?>export.getPage("main").changeCalendar, 200)',}); 
					}						
				},		

				// Form sending
				send: function () {
					$('#<?=self::$MODULE_LBL?>sender').css('display', 'none');
					$('.<?=self::$MODULE_LBL?>errInput').removeClass('<?=self::$MODULE_LBL?>errInput');
					var data = this.getInputs();		
					
					if (data.success) {
						this.self.ajax({
							data: this.self.concatObj(data.inputs, {
								<?=self::$MODULE_LBL?>action: 'courierCallRequest',                        
							}),
							dataType: 'json',
							success: this.onSend
						});
					}
					else {
						var alertStr = "<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_NOTSENDED')?>\n\n<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_FILL')?>";
						var headerDiff = {};
						for (var i in data.errors) {
							var handler = $('#<?=self::$MODULE_LBL?>' + i);
							handler.addClass('<?=self::$MODULE_LBL?>errInput');

							switch(i){                       
								default: handler = handler.parent().parent(); break;
							}

							var label = (handler.children(':first-child').find('label').length) ? handler.children(':first-child').find('label').text().trim() : handler.children(':first-child').text().trim();
							var header = false;
							var iter = 0;

							while (!header && iter < 30) {
								if (handler.prev('.heading').length)
									header = handler.prev('.heading').text().trim();
								else
									handler = handler.prev();
								iter++;
							}
							if (typeof(headerDiff[header]) === 'undefined')
								headerDiff[header] = {};
							headerDiff[header][label] = label;
						}
						for (var i in headerDiff) {
							alertStr += "\n" + i + ": ";
							for (var j in headerDiff[i]) {
								alertStr += j + ", ";
							}
							alertStr = alertStr.substring(0, alertStr.length - 2);
						}
						alert(alertStr);
						$('#<?=self::$MODULE_LBL?>sender').css('display', '');
					}
				},

				getInputs: function (giveAnyway) {
					var depths = this.dependences();

					var data = {
						inputs: {},
						errors: {}
					};

					for (var i in depths) {
						if (typeof(depths[i].need) !== 'undefined') {
							var preVal = $('#<?=self::$MODULE_LBL?>' + i).val();
							if ($('#<?=self::$MODULE_LBL?>' + i).attr('type') === 'checkbox')
								preVal = ($('#<?=self::$MODULE_LBL?>' + i).attr('checked')) ? true : false;
							if (typeof(depths[i].link) !== 'undefined') {
								var checkVal = $('#<?=self::$MODULE_LBL?>' + depths[i].link).val();
								if ($('#<?=self::$MODULE_LBL?>' + depths[i].link).attr('type') === 'checkbox')
									checkVal = ($('#<?=self::$MODULE_LBL?>' + i).attr('checked')) ? true : false;
							}
							switch (depths[i].need) {
								case 'dep' :
									if (preVal)
										data.inputs[i] = preVal;
									else if (!checkVal)
										data.errors[i] = i;
									break;
								case 'sub' :
									if (checkVal) {
										if (preVal)
											data.inputs[i] = preVal;
										else
											data.errors[i] = i;
									}
									break;
								case true :
									if (preVal)
										data.inputs[i] = preVal;
									else
										data.errors[i] = i;
									break;
								case false :
									if (preVal)
										data.inputs[i] = preVal;
									break;
							}
						}
					}           

					if (this.self.isEmpty(data.errors) || (typeof(giveAnyway) !== 'undefined' && giveAnyway))
						return {success: true, inputs: data.inputs};
					else
						return {success: false, errors: data.errors};
				},       

				onSend: (function (self) {
					self.onSend = function (data) {
						if (data.success === 'Y') {
							alert("<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_SENDED')?>" + data.OrderNumber);
							self.mainWnd.close();
							window.location.reload();
						}
						else {
							var str = '<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_NOTSENDED')?>' + "\n";
							if (typeof(data.error) !== 'undefined') {
								for (var i in data.error)
									str += "\n" + data.error[i];
							}

							$('#<?=self::$MODULE_LBL?>sender').css('display', '');

							alert(str);
						}
					};
				}),

				dependences: function () {
					var reqs = {
						courier_ikn     : {need: true},
						courier_fio     : {need: true},
						courier_phone   : {need: true},
						courier_city    : {need: true},
						courier_address : {need: true},
						courier_date    : {need: true},
						courier_time    : {need: true},
						courier_number  : {need: true},
						courier_weight  : {need: true},
						courier_comment : {need: false},                
					};            

					return reqs;
				},
				
				// Events, for everyone
				events: (function (self) {
					self.events = {
						checkRestrictions: function() {
							var dateError = $('#<?=self::$MODULE_LBL?>courier_date_error');
							var time = $('#<?=self::$MODULE_LBL?>courier_time');
							var cityID = $('#<?=self::$MODULE_LBL?>courier_city').val();
							var isMO = self.self.inArray(cityID, self.self.MOCityIds);
							
							var dateSelected = $('#<?=self::$MODULE_LBL?>courier_date').val().split('.');					
							var date = new Date();
							var currentDate = new Date();
												
							date.setDate(dateSelected[0]);
							date.setMonth(dateSelected[1] - 1);
							date.setFullYear(dateSelected[2]);									
							
							if (date.getFullYear() === currentDate.getFullYear() && date.getMonth() === currentDate.getMonth() && date.getDay() === currentDate.getDay()) {						
								if (isMO && currentDate.getHours() > 11) {							
									dateError.html("<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_TIMELIMIT_MO')?>");							
								}
								else if (!isMO && currentDate.getHours() > 15) {														
									dateError.html("<?=Loc::getMessage('PICKPOINT_DELIVERYSERVICE_MESS_COURIER_TIMELIMIT_NOT_MO')?>");							
								}
								
								if (dateError.html().length > 0) {
									dateError.removeClass('<?=self::$MODULE_LBL?>hidden');
									
									time.attr('disabled', 'disabled');
									time.children().each(function (ind, stuff) {								
										$(stuff).attr('disabled', 'disabled');
									});							
								}
								else
								{
									time.children().each(function (ind, stuff) {							
										if (isMO) {
											if ($(stuff).val() !== '9-18') {
												$(stuff).attr('disabled', 'disabled');
											}
											else {
												$(stuff).removeAttr('disabled');
												$(stuff).attr('selected', 'selected');
											}
										}
										else {
											$(stuff).removeAttr('disabled');
										}
									});
								}
							}
							
							if (date.getFullYear() > currentDate.getFullYear() || (date.getFullYear() === currentDate.getFullYear()	&& date.getMonth() > currentDate.getMonth()) || 
								(date.getFullYear() === currentDate.getFullYear() && date.getMonth() === currentDate.getMonth() && date.getDay() > currentDate.getDay())) {
								
								dateError.html("");
								dateError.addClass('<?=self::$MODULE_LBL?>hidden');
								
								time.removeAttr('disabled');
								time.children().each(function (ind, stuff) {							
									if (isMO) {
										if ($(stuff).val() !== '9-18') {
											$(stuff).attr('disabled', 'disabled');
										}
										else {
											$(stuff).removeAttr('disabled');
											$(stuff).attr('selected', 'selected');
										}
									}
									else {
										$(stuff).removeAttr('disabled');
									}							
								});
							}									
						},							
					}
				}),

				// UI 
				ui: {
					toggleBlock: function (code) {
						$('.<?=self::$MODULE_LBL?>block_' + code).toggle();
					},
					makeUnseen: function (wat, mode) {
						if (mode) {
							wat.addClass('<?=self::$MODULE_LBL?>hidden');
						}
						else {
							wat.removeClass('<?=self::$MODULE_LBL?>hidden');
						}
					},          
				},
			});
		</script>
		<script type="text/javascript">
            $(document).ready(<?=self::$MODULE_LBL?>export.init);
        </script>
        <?php
    }

	/**
     * Courier call form CSS
     */
    protected static function loadFormCSS()
	{
        Tools::getOptionsCss();
        ?>
        <style>
            .bx-calendar-month-content .disabled {
				pointer-events: none;
				color: #ccc;
			}		
			
			.bx-calendar-year-content .disabled {
				pointer-events: none;
				color: #ccc;
			}		
			
			.bx-calendar-range .disabled {
			  pointer-events: none;
			}		
			
			#<?=self::$MODULE_LBL?>wndOrder {
				width: 100%;
			}
			
			#<?=self::$MODULE_LBL?>wndOrder td:first-of-type {
				width: 45%;
			}
			
			#<?=self::$MODULE_LBL?>wndOrder select {
				width: 260px;
			}
			
			#<?=self::$MODULE_LBL?>wndOrder input {
				width: 250px;
			}
			
			#<?=self::$MODULE_LBL?>wndOrder textarea {
				width: 250px;
				height: 50px;
			}
        </style>
        <?php
    }	

	/**
     * Add buttons in courier call form
	 * @param string $html button HTML
     */
    protected static function addButton($html)
    {
        if (!isset(self::$arButtons))
			self::$arButtons = array();
        
        if (count(self::$arButtons) && count(self::$arButtons) % 3 === 0) {
            self::$arButtons []= '<br><br>';
        }
		
        self::$arButtons []= $html;
    }	
}