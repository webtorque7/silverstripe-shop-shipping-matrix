<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:21
 */

class InternationalShippingWeightRange extends DataObject{
	private static $db = array(
		'MinWeight' => 'Decimal',
		'MaxWeight' => 'Decimal',
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'CarrierShippingZone' => 'CarrierShippingZone'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier'
	);

	private static $summary_fields = array(
		'MinWeight' => 'MinWeight',
		'MaxWeight' => 'MaxWeight'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');

		$fields->removeByName('CarrierShippingZoneID');

		$fields->addFieldsToTab('Root.Main',array(
				TextField::create('MinWeight', 'Minimum Weight'),
				TextField::create('MaxWeight', 'Maximum Weight')
			)
		);

		return $fields;
	}

} 