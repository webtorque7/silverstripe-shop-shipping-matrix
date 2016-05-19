<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/05/14
 * Time: 1:28 PM
 */

class DomesticShippingRegion extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Region' => 'Text',
		'Sort' => 'Int',
		'Amount' => 'Currency'
	);

	private static $has_one = array(
		'DomesticShippingCarrier' => 'DomesticShippingCarrier'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Region' => 'Region',
		'Amount' => 'Amount'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName(array(
			'Sort',
			'DomesticShippingCarriers'
		));

		$region = singleton('Address')->getRegionList();

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title'),
			CheckboxSetField::create('Region', 'Region', $region),
			TextField::create('Amount', 'Amount')
		));

		return $fields;
	}
}