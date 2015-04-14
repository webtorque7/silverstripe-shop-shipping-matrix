<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/06/14
 * Time: 11:32 AM
 */

class ShippingRate extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'ShippingCharge' => 'Currency'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier',
		'InternationalShippingZone' => 'InternationalShippingZone',
		'ShippingWeightRange' => 'ShippingWeightRange',
		'ShippingQuantityRange' => 'ShippingQuantityRange'
	);

	private static $summary_fields = array(
		'InternationalShippingCarrier.Title' => 'International Shipping Carrier',
		'InternationalShippingZone.Title' => 'Shipping Zone',
		'ShippingWeightRange.Title' => 'Weight Range',
		'ShippingQuantityRange.Title' => 'Quantity Range',
		'ShippingCharge' => 'Shipping Charge'
	);

	public function canView($member = null) {
		return true;
	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title', 'Title'),
			DropdownField::create(
				'InternationalShippingCarrierID',
				'International Shipping Carrier',
				InternationalShippingCarrier::get()->map('ID','Title')
			),
			DropdownField::create(
				'InternationalShippingZoneID',
				'Shipping Zone',
                                $this->InternationalShippingCarrier()->InternationalShippingZones()->map('ID','Title')
			)
		));
		$fields->addFieldsToTab('Root.Main', array(
			LiteralField::create('UnitHelper', 'Select a weight based range OR a quantity based range. Then specify the amount per unit selected'),
			DropdownField::create(
				'ShippingWeightRangeID',
				'Shipping Weight Range',
				ShippingWeightRange::get()->map('ID', 'Title')

			)->setEmptyString('Not Applicable'),
			DropdownField::create(
				'ShippingQuantityRangeID',
				'Shipping Quantity Range',
				ShippingQuantityRange::get()->map('ID', 'Title')
			)->setEmptyString('Not Applicable'),
			TextField::create('ShippingCharge', 'Shipping Charge')->setDescription('For quantity based shipping, input the total charge for this range. For weight based shipping, input the multiplier per kg.')
		), 'InternationalShippingZoneID');
		$fields->removeByName('Sort');
		$fields->removeByName('Title');
		$fields->removeByName('InternationalShippingCarrier');
		return $fields;
	}

}