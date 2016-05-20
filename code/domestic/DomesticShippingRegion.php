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
		'Amount' => 'Currency',
		'DefaultRegion' => 'Boolean'
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
			'DomesticShippingCarrierID'
		));

		$region = singleton('Address')->getRegionList();

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title', 'Title'),
			CheckboxField::create('DefaultRegion', 'Default Region'),
			CheckboxSetField::create('Region', 'Region', $region),
			TextField::create('Amount', 'Amount')
		));

		return $fields;
	}
	public static function get_shipping_region($deliveryRegion)
	{
		$shippingMatrix = ShippingMatrixConfig::current();
		$domesticCarriers = $shippingMatrix->DomesticShippingCarriers();
		$carrierIDs = implode(',', $domesticCarriers->column('ID'));

		$shippingRegion = DomesticShippingRegion::get()
				->filter('Region:PartialMatch', $deliveryRegion)
				->where('"DomesticShippingCarrierID" IN (' . $carrierIDs . ')')
				->first();

		if($shippingRegion && $shippingRegion->exists()){
			return $shippingRegion;
		}

		//fall back looking for default region
		return DomesticShippingRegion::get()->filter(array('DefaultRegion', true))->first();
	}
}