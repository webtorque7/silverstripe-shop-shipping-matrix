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
		'MaxQuantity' => 'Int',
		'Rate' => 'Currency'
	);

	private static $belongs_many_many = array(
		'InternationalShippingCarriers' => 'InternationalShippingCarrier',
		'DomesticShippingCarriers' => 'DomesticShippingCarrier'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'MinQuantity' => 'Minimum Quantity',
		'MaxQuantity' => 'Maximum Quantity',
		'Rate' => 'Rate'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarriers');
		$fields->removeByName('DomesticShippingCarriers');
		$fields->addFieldsToTab('Root.Main',array(
				TextField::create('MinQuantity', 'Minimum Quantity'),
				TextField::create('MaxQuantity', 'Maximum Quantity'),
				TextField::create('Rate', 'Rate')
			)
		);
		return $fields;
	}
}