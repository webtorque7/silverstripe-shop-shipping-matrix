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

	/**
	 * DomesticShippingCarriers is deprecated, should use DomesticShippingCarrier, region can only
	 * belong to on carrier or there is no way to lookup carrier from the region
	 *
	 * @var array
	 */
//	private static $belongs_many_many = array(
//		'DomesticShippingCarriers' => 'DomesticShippingCarrier'
//	);

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
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingCarriers');

		$region = singleton('Address')->getRegionList();

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title'),
			CheckboxSetField::create('Region', 'Region', $region),
			TextField::create('Amount', 'Amount')
		));
		return $fields;
	}

	public function requireDefaultRecords()
	{
		parent::requireDefaultRecords();

		//move DomesticShippingCarriers into a has_one
		foreach (DomesticShippingRegion::get() as $region) {
			$carrier = DB::query("SELECT DomesticShippingCarrierID from DomesticShippingCarrier_DomesticShippingRegions WHERE DomesticShippingRegionID = {$region->ID}")->value();

			if ($carrier) {
				$region->DomesticShippingCarrierID = $carrier;

				//delete old records
				DB::query("DELETE FROM DomesticShippingCarrier_DomesticShippingRegions WHERE DomesticShippingRegionID = {$region->ID}");
			}
		}
	}
}