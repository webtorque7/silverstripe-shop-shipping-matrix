<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:35
 */

class ShippingZone extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'SelectedCountries' => 'Text',
		'Sort' => 'Int'
	);

	private static $belongs_to = array(
		'CarrierShippingZone' => 'CarrierShippingZone'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');
		$fields->removeByName('CarrierShippingZoneID');
		$fields->removeByName('Country');

		$fields->addFieldToTab('Root.Main',
			$mycountries = new CheckboxSetField('SelectedCountries','Shipping Countries', ShopConfig::$iso_3166_countryCodes)
		);

		return $fields;
	}


	/*
	 * Calculate charge based on other related entities
	 * Todo:
	 */
	public function calculateCharge($weight){

		$charge = $weight;

		return $charge;
	}

} 