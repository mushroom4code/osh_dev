<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(count($arResult["PERSON_TYPE"]) > 1)
{
	global $PERSON_TYPE;
	?>
	<div class="width_100 mb-4 d-flex align-items-center userCheck" id="userCheck">
	
	<?foreach($arResult["PERSON_TYPE"] as $v):?>
	<div class="mr-4 d-flex align-items-center">
	<input class="form-check-input m-0" type="radio" id="PERSON_TYPE_<?=$v["ID"]?>" name="PERSON_TYPE" <?if( $v["ID"] == 1):?>data_id="PERSON_TYPE_FIZ"<?else:?>data_id="PERSON_TYPE_URIC"<?endif;?> value="<?=$v["ID"]?>"<?if ($v["CHECKED"]=="Y") echo " checked=\"checked\"";?> onClick="submitForm()"> <label class="form-check-label ml-2 font_weight_500" for="PERSON_TYPE_<?=$v["ID"]?>"><?=$v["NAME"]?></label>
	</div>
	<?endforeach;?>
	</div>
	<input type="hidden" name="PERSON_TYPE_OLD" value="1"><input name="PROFILE_ID" type="hidden" value="4">
	

	<?
}
else
{
	if(IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"]) > 0)
	{
		//for IE 8, problems with input hidden after ajax
		?>
		<span style="display:none;">
		<input type="text" name="PERSON_TYPE" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>" />
		<input type="text" name="PERSON_TYPE_OLD" value="<?=IntVal($arResult["USER_VALS"]["PERSON_TYPE_ID"])?>" />
		</span>
		<?
	}
	else
	{
		foreach($arResult["PERSON_TYPE"] as $v)
		{
			?>
			<input type="hidden" id="PERSON_TYPE" name="PERSON_TYPE" value="<?=$v["ID"]?>" />
			<input type="hidden" name="PERSON_TYPE_OLD" value="<?=$v["ID"]?>" />
			<?
		}
	}
}
?>