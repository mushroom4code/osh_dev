<?php
namespace PickPoint\DeliveryService\Bitrix;

use \Bitrix\Main\Localization\Loc;
use \PickPoint\DeliveryService\Option;

/**
 * Class Tools
 * @package PickPoint\DeliveryService\Bitrix 
 */
class Tools
{
	protected static $MODULE_ID  = 'pickpoint.deliveryservice';
	protected static $MODULE_LBL = 'PICKPOINT_DELIVERYSERVICE_';
		
	/**
	 * Convert data from LANG_CHARSET to UTF8
	 * 
	 * @param mixed $handle
	 * @return mixed
	 */	
	public static function encodeToUTF($handle)
	{
        if (LANG_CHARSET !== 'UTF-8') {
            if (is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeToUTF($key);
                    $handle[$key] = self::encodeToUTF($val);
                }
            } else if (is_object($handle)) {
                $arCorresponds = array(); // why = because
                foreach ($handle as $key => $val) {
                    $arCorresponds[$key] = array(self::encodeToUTF($key), self::encodeToUTF($val));
                }

                foreach ($arCorresponds as $key => $new) {
                    unset($handle->$key);
                    $handle->$new[0] = $new[1];
                }
            } else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, LANG_CHARSET, 'UTF-8');
            }
        }
        return $handle;
    }
	
	/**
	 * Convert data from UTF8 to LANG_CHARSET
	 * 
	 * @param mixed $handle
	 * @return mixed
	 */
    public static function encodeFromUTF($handle)
	{
        if (LANG_CHARSET !== 'UTF-8') {
            if (is_array($handle))  {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeFromUTF($key);
                    $handle[$key] = self::encodeFromUTF($val);
                }
            } else if (is_object($handle)) {
                $arCorresponds = array();
                foreach ($handle as $key => $val) {
                    $arCorresponds[$key] = array(self::encodeFromUTF($key), self::encodeFromUTF($val));
                }

                foreach($arCorresponds as $key => $new) {
                    unset($handle->$key);
                    $handle->$new[0] = $new[1];
                }
            } else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, 'UTF-8', LANG_CHARSET);
            }
        }
        return $handle;
    }
	
	/**
	 * Get CSS for module options 
	 */
	public static function getOptionsCss()
	{		
		echo '<style>         
			.'.self::$MODULE_LBL.'header {
				 font-size: 16px;
				 cursor: pointer;
				 display:block;
				 color:#2E569C;
			}
			 
			.'.self::$MODULE_LBL.'inst {
				display:none;
				margin-left:10px;
				margin-top: 10px;
				margin-bottom: 10px;
				color: #555;
			}
			
			.'.self::$MODULE_LBL.'smallHeader
			{
				cursor: pointer;
				display:block;
				color:#2E569C;
			}
			
			.'.self::$MODULE_LBL.'subFaq
			{
				margin-bottom:10px;
				margin-left:10px;
			}			

			.'.self::$MODULE_LBL.'warning{
                color:red !important;
            }
			
			.'.self::$MODULE_LBL.'border {
				border: 1px dotted black;
			}	

			.'.self::$MODULE_LBL.'borderBottom {
				border-bottom: 1px dotted black;
			}

			.'.self::$MODULE_LBL.'hidden {
                display: none !important;
            }						

			.'.self::$MODULE_LBL.'errInput {
                background-color: #ffb3b3 !important;
            }
            
			.'.self::$MODULE_LBL.'PropHint, .'.self::$MODULE_LBL.'PropHint:hover {
                background: url("/bitrix/images/'.self::$MODULE_ID.'/hint.gif") no-repeat transparent !important;
                text-decoration: none !important;
                display: inline-block;
                width: 12px;
				height: 12px;
                position: relative;                
            }
			
            .'.self::$MODULE_LBL.'b-popup {
                background-color: #FEFEFE;
                border: 1px solid #9A9B9B;
                box-shadow: 0 0 10px #B9B9B9;
                display: none;
                font-size: 12px;
                padding: 19px 13px 15px;
                position: absolute;
                top: 38px;
                width: 300px;
                z-index: 50;
            }
            
			.'.self::$MODULE_LBL.'b-popup .'.self::$MODULE_LBL.'pop-text {
                margin-bottom: 10px;
                color:#000;
				font-weight: normal;
				text-align: left;
            }
			
            .'.self::$MODULE_LBL.'pop-text i {
				color:#AC12B1;
			}
			
			.'.self::$MODULE_LBL.'pop-text ul {
				padding-left: 14px;
			}
			
            .'.self::$MODULE_LBL.'b-popup .'.self::$MODULE_LBL.'close {
                background: url("/bitrix/images/'.self::$MODULE_ID.'/popup_close.gif") no-repeat transparent;
                cursor: pointer;
                height: 10px;
                width: 10px;
				position: absolute;
                right: 4px;
                top: 4px;                
            }           
        </style>';
	}
	
	/**
	 * Return path to module JS files
	 *
	 * @return string
	 */
	public static function getJSPath()
    {
        return '/bitrix/js/'.self::$MODULE_ID.'/';
    }
	
	/**
	 * Return path to module option files
	 *
	 * @return string
	 */
	public static function defaultOptionPath()
    {
        return "/bitrix/modules/".self::$MODULE_ID."/optionsInclude/";
    }
	
	/**
	 * Make select for module options
	 */
	public static function makeSelect($id, $vals, $def = false, $atrs = '')
	{
        $select = "<select ".(($id) ? "name='".((strpos($atrs, 'multiple') === false) ? $id : $id.'[]')."' id='{$id}' " : '')." {$atrs}>";
        if (is_array($vals)) {
            foreach($vals as $val => $sign)
                $select .= "<option value='{$val}' ".(((is_array($def) && in_array($val, $def)) || $def == $val) ? 'selected' : '').">{$sign}</option>";
        }
        $select .= "</select>";

        return $select;
    }
	
	/**
	 * Make radio button for module options
	 */
	public static function makeRadio($id, $vals, $def=false, $atrs = '')
	{
        $radio = "";
        if (is_array($vals)) {
            foreach ($vals as $val => $sign) {
                $checked = ($val == $def) ? 'checked' : '';
                $radio .= "<input type='radio' {$atrs} {$checked} name='{$id}' id='".$id.'_'.$val."' value='{$val}'>&nbsp;<label for='".$id.'_'.$val."'>{$sign}</label><br>";
            }
        }

        return $radio;
    }
	
	/**
	 * Add FAQ block in module options
	 */
	public static function placeFAQ($code)
	{
		echo '<a class="'.self::$MODULE_LBL.'header" onclick="$(this).next().toggle(); return false;">'.Loc::getMessage(self::$MODULE_LBL.'FAQ_'.$code.'_TITLE').'</a>';
		echo '<div class="'.self::$MODULE_LBL.'inst">'.Loc::getMessage(self::$MODULE_LBL.'FAQ_'.$code.'_DESCR').'</div>';
	}	
	
	/**
	 * Add hint block in module options
	 */
	public static function placeHint($code)
	{
	?>
        <div id="pop-<?=$code?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text">
				<?=Loc::getMessage(self::$MODULE_LBL."HELPER_".$code)?>
			</div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?php
	}
	
	/**
     * Make module options block with heading, FAQ and options
	 *
	 * @param $code 
	 * @param $isHidden 
     */
    public static function placeOptionBlock($code, $isHidden = false)
    {
        global $arAllOptions;
		
        ?>
        <tr class="heading">
			<td colspan="2" valign="top" align="center" <?//=($isHidden) ? "class='".self::$MODULE_LBL."headerLink' onclick='".self::$MODULE_LBL."setups.getPage(\"main\").showHidden($(this))'" : ''?>>
				<?=Loc::getMessage(self::$MODULE_LBL."HDR_".$code)?>
			</td>
		</tr>
        <?php
		if (Loc::getMessage(self::$MODULE_LBL.'FAQ_'.$code.'_TITLE')) {
		?>
            <tr><td colspan="2"><?php self::placeFAQ($code)?></td></tr>
        <?php
		}
        
        if (array_key_exists($code, $arAllOptions)) 
		{
            ShowParamsHTMLByArray($arAllOptions[$code], $isHidden);

            $collection = Option::collection();
            foreach ($arAllOptions[$code] as $arOption) {
                if (array_key_exists($arOption[0], $collection) && $collection[$arOption[0]]['hasHint'] == 'Y') {
                    self::placeHint($arOption[0]);
                }
            }
        }
    }
	
	/**
	 * Make module option row
	 *
     * @param $name
     * @param $val 
     * @param $required - mark option as required
     */
    public static function placeOptionRow($name, $val, $required = false)
	{
        if ($name) {
		?>
            <tr>
				<td width='50%' class='adm-detail-content-cell-l'><?=($required)?'<span class="required">*</span>':''?><?=$name?></td>
                <td width='50%' class='adm-detail-content-cell-r'><?=$val?></td>
            </tr>
        <?php } else { ?>
            <tr><td colspan='2' style='text-align: center'><?=$val?></td></tr>
        <?php
		}
    }		
	
	/**
	 * Make module form header row
	 *
     * @param $code
     * @param $link 
     * @param $headerClass 
     */
	public static function placeFormHeaderRow($code, $link = false, $headerClass = '')
    {
		?>
		<tr class="heading <?=(($headerClass) ? self::$MODULE_LBL.$headerClass : '')?>">
            <td colspan="2">
                <?=($link)?'<a href="javascript:void(0)" onclick="'.$link.'">':''?><?=Loc::getMessage(self::$MODULE_LBL.'HDR_'.$code)?><?=($link)?'</a>':''?>
                <?php if (Loc::getMessage(self::$MODULE_LBL.'HELPER_'.$code)){?> <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this, "#<?=self::$MODULE_LBL?>wndOrder");'></a><?php self::placeHint($code);}?>
            </td>
        </tr>
		<?php
	}

	/**
	 * Make module form row
	 *
     * @param $code
     * @param $type 
     * @param $def 
     * @param $vals 
     * @param $attrs 
     * @param $trClass 
     */
    public static function placeFormRow($code, $type, $def = false, $vals = false, $attrs = false, $trClass = false)
	{
        if ($type !== 'select' && $type !== 'radio') {
            $attrs = "id='".self::$MODULE_LBL.$code."' name='".self::$MODULE_LBL.$code."' ".$attrs;
        }
		
        $class = '';
        if ($trClass) {
            $class = 'class="';
            if (is_array($trClass)) {
                foreach ($trClass as $className) {
                    $class .= self::$MODULE_LBL.$className.' ';
                }
            } else {
                $class .= self::$MODULE_LBL.$trClass;
            }
            $class .= '"';
        }
        ?>
        <tr <?=$class?>>
            <td>
                <label for="<?=self::$MODULE_LBL?><?=$code?>"><?=Loc::getMessage(self::$MODULE_LBL.'LBL_'.$code)?></label>
                <?php if ($hint = Loc::getMessage(self::$MODULE_LBL.'HELPER_'.$code)) {?>
                    <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this, "#<?=self::$MODULE_LBL?>wndOrder");'></a>
                <?php self::placeHint($code); }?>
            </td>
			<td>
		<?php
        switch ($type) {
            case 'text'     : ?><input type="text" <?=$attrs?> value="<?=htmlspecialchars($def)?>"/><?php break;
            case 'radio'    : echo self::makeRadio(self::$MODULE_LBL.$code, $vals, $def, $attrs); break;
            case 'select'   : echo self::makeSelect(self::$MODULE_LBL.$code, $vals, $def, $attrs); break;
            case 'sign'     : echo $def; break;
            case 'checkbox' : ?><input type="checkbox" <?=$attrs?> value="Y" <?=($def)?'checked':''?>/><?php break;
            case 'textbox'  : ?><textarea <?=$attrs?>><?=$def?></textarea><?php break;
            case 'hidden'   : ?><input type="hidden" <?=$attrs?> value="<?=$def?>"/><span id="<?=self::$MODULE_LBL?>hidLabel_<?=$code?>"><?=$def?></span><?php break;
        }
        ?>
			</td>
		</tr><?php
    }
}