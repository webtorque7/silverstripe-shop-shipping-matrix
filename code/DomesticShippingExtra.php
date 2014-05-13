<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 13:53
 */

class DomesticShippingExtra extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Amount' => 'Currency',
		'Sort' => 'Int'
	);

	private static $belongs_many_many = array(
		'ShippingMatrixModifiers' => 'ShippingMatrixModifier'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'Amount' => 'Amount',
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');
		$fields->removeByName('ShippingMatrixModifiers');


		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title', 'Title'),
			TextField::create('Amount', 'Amount'),
		));

		return $fields;
	}
} 