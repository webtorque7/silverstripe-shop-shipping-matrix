<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 21/10/14
 * Time: 3:01 PM
 */

class PBTMetric extends DataObject{
	private static $db = array(
		'Code' => 'Varchar(100)',
		'PackageSize' => 'Varchar(3)',
		'PackageDescription' => 'Varchar(200)',
		'Cubic' => 'Decimal(9,4)'
	);

	private static $has_one = array(
		'ShippingMatrixConfig' => 'SiteConfig'
	);

	private static $casting = array(
		'PackageSize' => 'Int'
	);

	private static $summary_fields = array(
		'Code' => 'Code',
		'PackageDescription' => 'PackageDescription',
		'Cubic' => 'Cubic'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('ShippingMatrixConfigID');
		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Code', 'Code'),
			TextField::create('PackageSize', 'Package Size')->setDescription('Number of bottles in numeric value'),
			TextField::create('Cubic', 'Cubic'),
			TextareaField::create('PackageDescription', 'Package Description')
		));
		return $fields;
	}
}