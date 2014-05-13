<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:04
 */

class ShippingItemUnit extends DataObject{
	private static $db = array(
		'NumberOfItems' => 'Int',
		'Unit' => 'Decimal',
		'Sort' => 'Int'
	);

	public static $summary_fields = array(
		'NumberOfItems' => 'Bottles of Wine per Shipping Unit',
		'Unit' => 'Shipping Unit'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldsToTab('Root.Main', array(
				TextField::create('NumberOfItems', 'Bottles of Wine per Shipping Unit'),
				TextField::create('Unit', 'Shipping Unit (in crates)')
			)
		);

		$fields->removeByName('Sort');
		return $fields;
	}
}