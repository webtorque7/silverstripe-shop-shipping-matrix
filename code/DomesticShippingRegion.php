<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/05/14
 * Time: 1:28 PM
 */

class DomesticShippingRegion extends DataObject{
	private static $db = array(
		'Region' => 'Text',
		'Sort' => 'Int',
		'Amount' => 'Currency'
	);

	private static $belongs_many_many = array(
		'DomesticShippingCarriers' => 'DomesticShippingCarrier'
	);

	private static $summary_fields = array(
		'Region' => 'Region',
		'Amount' => 'Amount'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingCarriers');
		$fields->removeByName('Title');
		$address = new AddressExtension;
		$region = $address->getRegionList();

		$fields->addFieldsToTab('Root.Main', array(
			DropdownField::create('Region', 'Region', $region),
			TextField::create('Amount', 'Amount')
		));
		return $fields;
	}
}