<?php
namespace PickPoint\DeliveryService;

use \Bitrix\Main\Localization\Loc;
use \PickPoint\DeliveryService\Bitrix\Handler\Statuses;
use \PickPoint\DeliveryService\Bitrix\Adapter;

Loc::loadMessages(__FILE__);

/**
 * Class Option
 * @package PickPoint\DeliveryService
 */
class Option extends AbstractGeneral
{   
	// Cache for some option params
	protected static $abyss = [];

    /**
     * Option getter
     * 
     * @return mixed
     */	
	public static function get($option)
    {
        $self = \COption::GetOptionString(self::getMID(), $option, self::getDefault($option));
        		
		if (self::checkMultiple($option) && ($tmp = unserialize($self)))
            $self = $tmp;
		
        return $self;
    }

	/**
     * Return default option value
     * 
     * @return mixed|false
     */	
	public static function getDefault($option)
    {
        $opt = self::collection();
		
        if (array_key_exists($option, $opt))
            return $opt[$option]['default'];	
		
        return false;
    }
	
	/**
     * Return available variants of the option value (used for select input type)
     * 
     * @return array|false
     */	
	public static function getVariants($option)
    {
        $opt = self::collection();
		
        if (array_key_exists($option, $opt)) {
			if (is_array($opt[$option]['variants']))
				return $opt[$option]['variants'];					
			else if (is_callable($opt[$option]['variants']))
				return $opt[$option]['variants']();	
			else
				// What are you?
				return false;			
		}
		
        return false;
    }
	
	/**
     * Option setter
     */	
    public static function set($option, $val, $doSerialise = false)
    {
        if ($doSerialise)
		   $val = serialize($val);
        		
        \COption::SetOptionString(self::getMID(), $option, $val);
    }

    /**
     * Check if option may have multiple values 
	 *
	 * @return bool
     */
    public static function checkMultiple($option)
    {
        $opt = self::collection();
		
        if (array_key_exists($option, $opt) && array_key_exists('multiple', $opt[$option]))
            return $opt[$option]['multiple'];
		
        return false;
    }
	
	/**
     * Check if option marked as required 
	 *
	 * @return bool
     */
    public static function checkRequired($option)
    {
        $opt = self::collection();
		
        if (array_key_exists($option, $opt) && array_key_exists('required', $opt[$option]))
            return $opt[$option]['required'];
		
        return false;
    }	
	
	/**
     * Validate option value by validator rule
	 *
	 * @return mixed
     */
    public static function validateOption($option, $value)
    {
        $opt = self::collection();
		
		// ! Validator must be called as a function
		if (array_key_exists($option, $opt) && is_callable($opt[$option]['validator']))
			return $opt[$option]['validator']($value);			
				
		// Something wrong with validator or option
		return NULL;
    }
	
	/**
     * Validate required options with validator rules
	 *
	 * @return mixed
     */
    public static function validateRequiredOptions($values)
    {		
		$errors = array();		
				
        $opt = self::collection();
		foreach ($opt as $name => $data) {
			if ($data['required'] == true) {
				if (array_key_exists($name, $values)) {
					$check = self::validateOption($name, $values[$name]);				
										
					if (is_string($check)) {
						$errors[] = $check;						
					}  else if (is_null($check)) {
						// Something wrong with validator or option
						$errors[] = Loc::getMessage('PP_REQUIRED_PARAM_VALIDATOR_FAIL', array('#OPTION#' => $name));						
					}					
				} else {
					if ($data['type'] == 'selectbox') {
						$errors[] = self::validateOption($name, '');
					} else if ($data['type'] == 'select') {
						// Damn selects
						$errors[] = Loc::getMessage('PP_WRONG_'.strtoupper(ltrim($name, 'pp_')));
					} else {
                        $errors[] = Loc::getMessage('PP_REQUIRED_PARAM_MISS', array('#OPTION#' => $name));
                    }
				}				
			}						
		}
		
		if (count($errors))
			return implode('<br />', $errors);
		
		return false;		
    }

	/**
     * Check if all required module options defined
	 *
	 * @return bool
     */
	public static function isRequiredOptionsSet()
	{
		$result = true;
		
		$opt = self::collection();
		foreach ($opt as $name => $data) {
			if ($data['required'] == true) {
				$value = self::get($name);				
				$check = self::validateOption($name, $value);				
								
				if (!is_bool($check) || $check !== true) {
					// Some option not defined or something wrong with validator
					$result = false;
					break;					
				}
			}
		}
		
		return $result;
	}
	
	/**
     * Make module options list
	 *
	 * @return array
     */
	public static function makeOptions($hintMacro = false)
    {
        if (!$hintMacro)
            $hintMacro = "<a href='#' class='".self::getMLBL()."PropHint' onclick='return ".self::getMLBL()."setups.popup(\"pop-#CODE#\", this);'></a>";

        $arOptions = array();
        foreach (self::collection() as $optCode => $optVal) {
            if (!array_key_exists('group', $optVal) || !$optVal['group'])
                continue;

            if (!array_key_exists($optVal['group'], $arOptions))
                $arOptions[$optVal['group']] = array();

            $name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#', $optCode, $hintMacro) : '';

            $arOptions[$optVal['group']][] = array($optCode, Loc::getMessage(self::getMLBL()."OPT_{$optCode}").$name, $optVal['default'], array($optVal['type']));
        }

        return $arOptions;
    }
	
	/**
     * Return collection of module options 
	 *
	 * @return array
     */
    public static function collection()
    {
        return array(
			// Main Setup 
			'pp_ikn_number' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) { 				
												$res = (preg_match('#[0-9]{10}#', $val)) ? true : Loc::getMessage('PP_WRONG_IKN');
												return $res;
												},									
            ),
			'pp_api_login' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_API_LOGIN'));
												return $res;
												},
            ),
			'pp_api_password' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_API_PASSWORD'));
												return $res;
												},
            ),
			'pp_enclosure' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_ENCLOSURE'));
												return $res;
												},
            ),
			'pp_test_mode' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			
			'pp_service_types_all' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => array(
									0 => 'STD',
									1 => 'STDCOD',
									2 => 'PRIO',
									3 => 'PRIOCOD',
								),
                'type'      => 'select',
				'required'  => false,
				'validator' => '',
				'multiple'  => true,
            ),
			'pp_service_types_selected' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => array(
									0 => 'STD',
									1 => 'STDCOD',
									2 => 'PRIO',
									3 => 'PRIOCOD',
								),
                'type'      => 'select',				
				'required'  => true,
				'validator' => function ($val) {
												// variants keys to values
												$possible = array_flip(array_keys(Option::getVariants('pp_service_types_selected')));								
																								
												$res = (!empty($val) && count(array_intersect($possible, $val))) ? true : Loc::getMessage('PP_WRONG_SERVICE_TYPES_SELECTED');
												return $res;												
												},
				'multiple'  => true,
            ),
						
			'getting_type' => array(
                'group'     => 'common',
                'hasHint'   => 'Y',
                'default'   => '',
				'variants'  => self::getGettingTypeVariants(),
                'type'      => 'selectbox',				
				'required'  => true,
				'validator' => function ($val) {
												$possible = array_keys(Option::getVariants('getting_type'));	
																								
												$res = (!empty($val) && count(array_intersect($possible, $val))) ? true : Loc::getMessage('PICKPOINT_DELIVERYSERVICE_VALIDATOR_WRONG_GETTING_TYPE');
												return $res;												
												},
				'multiple'  => true,
            ),
			
			/*			
			// Getting Type migration 1.0.5 -> 1.0.6
			$gettingType = \Bitrix\Main\Config\Option::get('pickpoint.deliveryservice', 'pp_enclosing_types_selected', false);
			$gettingTypeMigrate = array(0 => 101, 1 => 102, 2 => 103, 3 => 104);

			if (!empty($gettingType))
			{
				$curGettingTypes = unserialize($gettingType);
				if (is_array($curGettingTypes))
				{
					$newGettingTypes = [];
					foreach ($curGettingTypes as $type)
					{
						if (array_key_exists($type, $gettingTypeMigrate))
						$newGettingTypes[] = $gettingTypeMigrate[$type];
					}
					if (!empty($newGettingTypes))
					{
						$newGettingTypes = serialize($newGettingTypes);
						\Bitrix\Main\Config\Option::set('pickpoint.deliveryservice', 'getting_type', $newGettingTypes);
					}
				}
			}

			// Unmake old options
			//\COption::RemoveOption('pickpoint.deliveryservice', 'pp_enclosing_types_all');
			//\COption::RemoveOption('pickpoint.deliveryservice', 'pp_enclosing_types_selected');	
			*/		
			
			'pp_term_inc' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),			
			'pp_postamat_picker' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => 'ADDRESS',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			'pp_add_info' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '1',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			'pp_order_phone' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			'pp_city_location' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			'pp_order_city_status' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			// --
			
			// Sender
			'pp_from_city' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'select',				
				'required'  => true,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_FROM_CITY'));
												return $res;
												},				
            ),					
			
			// Order sending			
			// Legacy delivery VAT variants defined in ./constants.php
			'delivery_vat' => array(
                'group'     => 'orderSending',
                'hasHint'   => 'Y',
                'default'   => 'VATNONE',
				'variants'  => array(
									'VATNONE' => Loc::getMessage('PICKPOINT_DELIVERYSERVICE_VARIANT_delivery_vat_VATNONE'),
									'VAT0'    => '0%',
									'VAT10'   => '10%',
									'VAT20'   => '20%',
								),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),				
			'set_assessed_cost' => array(
                'group'     => 'orderSending',
                'hasHint'   => 'Y',
                'default'   => 'N',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),			
			
			// Revert
			'pp_store_region' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			'pp_store_city' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'select',				
				'required'  => true,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_STORE_CITY'));
												return $res;
												},					
            ),	
			'pp_store_address' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => function ($val) {
												$res = (strlen(trim($val)) ? true : Loc::getMessage('PP_WRONG_STORE_ADDRESS'));
												return $res;
												},
            ),
			'pp_store_phone' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => function ($val) {
												$res = (preg_match('#[0-9]{10}#', $val)) ? true : Loc::getMessage('PP_WRONG_STORE_PHONE');
												return $res;
												},
            ),
			'pp_store_fio' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			'pp_store_post' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			'pp_store_organisation' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			'pp_store_comment' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),
			// Dimensions
			'pp_dimension_width' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (intval(trim($val)) > 0) ? true : Loc::getMessage('PP_WRONG_DIMENSION_WIDTH');
												return $res;
												},
            ),
			'pp_dimension_height' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (intval(trim($val)) > 0) ? true : Loc::getMessage('PP_WRONG_DIMENSION_HEIGHT');
												return $res;
												},
            ),
			'pp_dimension_depth' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => true,
				'validator' => function ($val) {
												$res = (intval(trim($val)) > 0) ? true : Loc::getMessage('PP_WRONG_DIMENSION_DEPTH');
												return $res;
												},
            ),
			// Regional coefficient
			'pp_use_coeff' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '0',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),			
			'pp_custom_coeff' => array(
                'group'     => '',
                'hasHint'   => 'N',
                'default'   => '',
                'type'      => 'text',
				'required'  => false,
				'validator' => '',
            ),	

			// Statuses
			'set_delivery_id' => array(
                'group'     => 'statuses',
                'hasHint'   => 'Y',
                'default'   => 'N',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),
			'status_sync_enabled' => array(
                'group'     => 'statuses',
                'hasHint'   => 'Y',
                'default'   => 'N',
                'type'      => 'checkbox',
				'required'  => false,
				'validator' => '',
            ),	
			'status_last_sync' => array(
                'group'     => 'statuses',
                'hasHint'   => 'Y',
                'default'   => '',
                'type'      => 'special',
				'required'  => false,
				'validator' => '',
            ),				
			
			'status_10000' => array(
				'group'     => 'statuses',
				'hasHint'   => 'N',
				'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
				'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
			),
			'status_10101' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),	
			
			'status_10201' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),			
			'status_10202' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_10301' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),			
			'status_10302' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_10401' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),			
			'status_10402' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_10501' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10502' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_10601' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10602' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10603' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10604' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),

			'status_10701' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10801' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_10901' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_10902' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),

			'status_11001' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11002' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11003' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11004' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11101' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11201' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11301' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11302' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11401' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11402' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11501' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11502' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11601' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_11602' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_11801' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_12801' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_13101' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_13701' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_13901' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_14201' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),			
			'status_14701' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_14801' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_15001' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),

			'status_15201' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			'status_15202' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),
			
			'status_15301' => array(
                'group'     => 'statuses',
                'hasHint'   => 'N',
                'default'   => '',
				'variants'  => self::getOrderStatusVariants(),
                'type'      => 'selectbox',
				'required'  => false,
				'validator' => '',				
            ),				
        );
    }	
	
	/**
     * Prepare order status variants for status selectboxes
	 *
	 * @return array
     */
	public static function getOrderStatusVariants()
	{
		$arVals = false;
		
		if (array_key_exists('statuses', self::$abyss)) {
			$arVals = self::$abyss['statuses'];
        } else {
			$arVals = array_merge([0 => ''], Statuses::getOrderStatuses());
			self::$abyss['statuses'] = $arVals;
		}
				
		return $arVals;
	}
	
	/**
     * Prepare getting type variants
	 *
	 * @return array
     */
	public static function getGettingTypeVariants()
	{
		return Adapter::getGettingTypes();		
	}
}