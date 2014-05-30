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
		'Sort' => 'Int',
		'Amount' => 'Currency'
	);

	private static $belongs_many_many = array(
		'DomesticShippingCarriers' => 'DomesticShippingCarrier'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Amount' => 'Amount'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingCarriers');
		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title', 'Region'),
			TextField::create('Amount', 'Amount'),
		));
		return $fields;
	}
}