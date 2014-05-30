<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:35
 */

class InternationalShippingZone extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'ShippingCountries' => 'Text'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarriers');
		$fields->addFieldToTab('Root.Main',
			new CheckboxSetField(
				'ShippingCountries',
				'Shipping Countries',
				ShopConfig::$iso_3166_countryCodes));
		return $fields;
	}
} 