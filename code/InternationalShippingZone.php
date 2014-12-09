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
		'ShippingCountries' => 'Text',
		'DefaultZone' => 'Boolean'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier'
	);

	private static $has_one = array(
		'ShippingRate' => 'ShippingRate'
	);

	private static $summary_fields = array(
		'Title' => 'Title'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarriers');
		$fields->removeByName('ShippingRateID');
		$fields->addFieldToTab('Root.Main',
			new CheckboxSetField(
				'ShippingCountries',
				'Shipping Countries',
				ShopConfig::config()->iso_3166_country_codes));
		return $fields;
	}

	public static function get_shipping_zone($deliveryCountry) {
		$shippingZones = InternationalShippingZone::get();
		foreach ($shippingZones as $shippingZone) {
			$countryArray = explode(",", $shippingZone->ShippingCountries);
			if (in_array($deliveryCountry, $countryArray)) {
				return $shippingZone;
			}
		}

		//fall back looking for default zone
		return InternationalShippingZone::get()->filter('DefaultZone', 1)->first();
	}
} 