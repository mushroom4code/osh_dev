<?php


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "доставка, условия, стоимость, самовывоз");
/**
 * @var CMain $APPLICATION
 */
$APPLICATION->SetTitle("Доставка и оплата товаров для кальяна в компании Ошиша");

global $USER;
if ($USER->IsAuthorized()) {

    ?><div id="content_box_delivery" class="box_boxes_delivery static">
	<h2 class="font-weight-bold mb-4">Условия доставки и способы оплаты</h2>
	<p class="delivery_description font-weight-bolder">
		 Доставка кальянов, табачной и никотиносодержащей продукции для физических лиц - не осуществляется,<br>
		 в отношении иной продукции применяются следующие условия о доставке:
	</p>
	<div class="d-flex flex-column mt-3" id="delivery_method">
		<h5 style="margin:20px 0 15px">Способы оплаты заказов</h5>
		<p class="delivery_description">
 <span class="red_text">Наличный расчет / картой</span> – оплачиваете курьеру при получении заказа, актуально только для Москвы и Московской области.<br>
 <br>
		</p>
		<div class="box_msk mb-3 d-flex">
			<div class="width_50">
				<h5 style="margin-bottom:15px">Cамовывоз со склада в Москве</h5>
				<p class="mb-4 delivery_description">
					 Самовывоз со склада доступен с понедельника по субботу (<a href="/about/contacts/" style="color:#DD0602;">схема и часы работы</a>).
				</p>
			</div>
			<div class="width_50">
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_door"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Бесплатный самовывоз пешком или на машине</span>
				</div>
			</div>
		</div>
		<div class="box_msk mb-4 d-flex">
			<div class="width_50">
				<h5 style="margin-bottom:15px">Доставка заказов по Москве и МО</h5>
				<p class="mb-4 delivery_description">
					 Доставка в тот же день осуществляется с понедельника по пятницу, доставка на следующий день с понедельника по субботу. В воскресенье доставка <span class="red_text">не работает.</span>
				</p>
				<div class="flex-column d-flex">
					<div class="d-flex row_section mb-3">
 <span class="d-flex align-items-center mr-3 "> <i class="fa fa-circle header_icon" aria-hidden="true"></i> </span>
						<div class="delivery_description">
							 Сделай <span class="red_text">заказ до 17:00</span>, доставим с 21 до 02 в этот же день (кроме сб и вс)<br>
							 доставка от 299 руб, <span class="red_text">бесплатно для заказов от 4000 руб</span>.
						</div>
					</div>
					<div class="d-flex row_section mb-3">
 <span class="d-flex align-items-center mr-3 "> <i class="fa fa-circle header_icon" aria-hidden="true"></i> </span>
						<div class="delivery_description">
							 Сделай <span class="red_text">заказ до 19:00</span>, доставим на следующий день с 11 до 22 (кроме вс)<br>
							 доставка от 299 руб, <span class="red_text">бесплатно для заказов от 4000 руб</span>.
						</div>
					</div>
					<div class="d-flex row_section mb-3">
 <span class="d-flex align-items-center mr-3 "> <i class="fa fa-circle header_icon" aria-hidden="true"></i> </span>
						<div class="delivery_description">
							 Доставка через постаматы 5post. <span class="red_text">Самая недорогая доставка</span>.<br>
							 Стоимость рассчитается автоматически в корзине.
						</div>
					</div>
				</div>
				<p class=" mb-4 delivery_description">
					 А еще можем доставить через СДЭК и постаматы 5post (Пятерочка). Расчет стоимости доставки и выбор постамат/ПВЗ доступны при оформлении заказа.
				</p>
			</div>
			<div class="width_50">
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_door"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Курьером до двери - точно в срок!</span>
				</div>
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_car"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Бесконтактная доставка - мы заботимся о вас!</span>
				</div>
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_car"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Собственная курьерская служба - оригинальный товар напрямую со склада!</span>
				</div>
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_box"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Доставка по Москве и МО до пункта выдачи или постамата CDEK, 5post.</span>
				</div>
			</div>
		</div>
		<div class="box_reg mb-4 d-flex">
			<div class="width_50">
				<h5 style="margin-bottom:15px">Доставка заказов по России</h5>
				<p class="delivery_description">
					 Доставка по России осуществляется всеми удобными для Вас транспортными компаниями, а также через пункты выдачи или постаматы 5post, Почтоматы, CDEK.
				</p>
				<p class="mb-4 delivery_description">
					 При подтверждении заказа согласуем с Вами удобную ТК или способ доставки<br>
 <span class="red_text ">ПЭК, Деловые линии, Байкал Сервис, Кит, Энергия, CDEK и другие.</span><br>
 <br>
					 Стоимость доставки рассчитывается по тарифам ТК или курьерской службы при оформлении заказа на сайте или менеджером при подтверждении заказа.
				</p>
			</div>
			<div class="width_50">
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_door"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">
					Курьером до двери от службы доставки</span>
				</div>
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_car"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">
					Доставка до пункта выдачи транспортной компании</span>
				</div>
				<div class="d-flex justify-content-between box_picture_info mb-3">
 <span class="box_delivery_box"></span> <span class="ml-2 delivery-trigger d-flex align-items-center">Доставка до постамата или пункта выдачи</span>
				</div>
			</div>
		</div>
	</div>
</div>
 <?php } else { ?>
<div id="content_box_delivery" class="box_boxes_delivery static">
	<p class="mb-2 mt-5 font-20 font-weight-bolder text-center">
		 Для ознакомления с информацией необходимо <a href="javascript:void(0)" class="link_header_box color-redLight text-decoration-underline">авторизоваться.</a>
	</p>
</div>
 <br><?php
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");?>