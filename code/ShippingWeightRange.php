<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:21
 */

class ShippingWeightRange extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'MinWeight' => 'Decimal',
		'MaxWeight' => 'Decimal'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier'
	);

	private static $has_one = array(
		'ShippingRate' => 'ShippingRate'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'MinWeight' => 'Minimum Weight(kg)',
		'MaxWeight' => 'Maximum Weight(kg)'
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
				TextField::create('MinWeight', 'Minimum Weight(kg)'),
				TextField::create('MaxWeight', 'Maximum Weight(kg)')
			)
		);
		return $fields;
	}
} 