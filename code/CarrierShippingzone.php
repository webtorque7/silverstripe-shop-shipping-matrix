<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:45
 */

class CarrierShippingZone extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'PerItemAmount' => 'Decimal',
		'PerKGAmount' => 'Decimal',
		'Sort' => 'Int'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier',
		'ShippingZone' => 'ShippingZone',
		'InternationalShippingWeightRange' => 'InternationalShippingWeightRange',
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'PerItemAmount' => 'Per Item Amount',
		'PerKGAmount' => 'Per KG Amount',
		'InternationalShippingCarrier.Title' => 'International Shipping Carrier',
		'ShippingZone.Title' => 'Shipping Zone'
	);

	public function calculateCharge($weight){

	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarrierID');

		$iswrSQLQuery = new SQLQuery();
		$iswrSQLQuery->setFrom('InternationalShippingWeightRange');
		$iswrSQLQuery->setSelect('ID');
		$iswrSQLQuery->selectField('CONCAT(CONCAT("MinWeight", \'KG - \'), CONCAT("MaxWeight", \'KG \'))', 'WeightRange');

//		$iscSQLQuery = new SQLQuery();
//		$iscSQLQuery->setFrom('InternationalShippingCarrier');
//		$iscSQLQuery->setSelect('ID');
//		$iscSQLQuery->selectField('CONCAT(CONCAT("Title", \' ( Minimum Weight - \'), CONCAT("MinimumWeight", \' ) \'))', 'InternationalShippingCarrierField');

		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingCarrierID');


		$fields->addFieldToTab('Root.Main', TextField::create('Title', 'Title'));
		$fields->addFieldToTab('Root.Main', TextField::create('PerItemAmount', 'Per Item Amount'));
		$fields->addFieldToTab('Root.Main', TextField::create('PerKGAmount', 'Per KG Amount'));

		$fields->addFieldToTab('Root.Main',
			DropdownField::create('InternationalShippingWeightRangeID', 'International Shipping Weight Range',
				$iswrSQLQuery->execute()->map('ID', 'WeightRange')
			)
		);

//		$fields->addFieldToTab('Root.Main',
//			DropdownField::create('InternationalShippingCarrierID', 'International Shipping Carrier',
//				InternationalShippingCarrier::get()->map('ID','Title')
//			)->setEmptyString('Select Shipping Carrier')
//		);

		$fields->addFieldToTab('Root.Main',
			DropdownField::create('ShippingZoneID', 'Shipping Zone',
				ShippingZone::get()->map('ID','Title')
			)->setEmptyString('Select Shipping Zone')
		);

		return $fields;
	}

} 