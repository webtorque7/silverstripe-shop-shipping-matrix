<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30/05/14
 * Time: 9:22 AM
 */

class DomesticShippingCarrier extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'TrackingURL' => 'Varchar',
		'TrackerType' => 'Varchar'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $many_many = array(
		'DomesticShippingRegions' => 'DomesticShippingRegion',
		'DomesticShippingExtras' => 'DomesticShippingExtra'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingRegions');
		$fields->removeByName('DomesticShippingExtras');

		$trackerClasses = ClassInfo::subclassesFor('TrackingLinkGenerator');
		$trackers = array_combine($trackerClasses, $trackerClasses);

		$shippingRegionGrid = GridField::create(
			'DomesticShippingRegions',
			'International Shipping Regions',
			$this->DomesticShippingRegions(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$shippingExtraGrid = GridField::create(
			'DomesticShippingExtras',
			'International Shipping Extras',
			$this->DomesticShippingExtras(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('TrackingURL', 'Tracking URL'),
			DropdownField::create('TrackerType', 'Tracker Type', $trackers),
			$shippingRegionGrid,
			$shippingExtraGrid));
		return $fields;
	}

	public function getTrackingLink($order){
		return $this->TrackerType ? singleton($this->TrackerType)->getTrackingLink($this->TrackingURL, $order) : false;
	}

	public static function process($region) {
		//find the carrier that ships in the region
		$carrier = DomesticShippingCarrier::get()
			->leftJoin('DomesticShippingCarrier_DomesticShippingRegions',
				'"DomesticShippingCarrier"."ID" = "DomesticShippingCarrier_DomesticShippingRegions"."DomesticShippingCarrierID"')
			->leftJoin('DomesticShippingRegion',
				'"DomesticShippingRegion"."ID" = "DomesticShippingCarrier_DomesticShippingRegions"."DomesticShippingRegionID"')
			->where('"DomesticShippingRegion"."Region" = \'' . $region . '\'')
			->first();

		//find the region charge
		$regionCharge = 0;
		foreach($carrier->DomesticShippingRegions() as $shippingRegion){
			if ($shippingRegion == $region){
				$regionCharge = $shippingRegion->Amount;
			}
		}

		//add up the extra charges from this carrier
		$extraCharge = 0;
		foreach($carrier->DomesticShippingExtras() as $shippingExtra){
			$extraCharge += $shippingExtra->Amount;
		}

		return $regionCharge + $extraCharge;
	}
}