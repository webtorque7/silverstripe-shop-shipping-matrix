<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30/05/14
 * Time: 8:39 AM
 */

class ShippingQuantityRange extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'MinQuantity' => 'Int',
		'MaxQuantity' => 'Int'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier'
	);

	private static $has_one = array(
		'ShippingRate' => 'ShippingRate'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'MinQuantity' => 'Minimum Quantity',
		'MaxQuantity' => 'Maximum Quantity'
	);

	public function canView($member = null) {
		return true;
	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarriers');
		$fields->removeByName('DomesticShippingCarriers');
		$fields->removeByName('ShippingRateID');
		$fields->addFieldsToTab('Root.Main',array(
				TextField::create('MinQuantity', 'Minimum Quantity'),
				TextField::create('MaxQuantity', 'Maximum Quantity')
			)
		);
		return $fields;
	}
}