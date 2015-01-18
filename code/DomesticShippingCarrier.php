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

	public function canView($member = null) {
		return true;
	}

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

	public function Regions(){
		return $this->DomesticShippingRegions();
	}

	public static function process($items, $region = null) {

		$charge = 0;
		$freeShipping = true;
		$wineCount = 0;
		$carriers = array();

		//If order only contains events/gift vouchers then shipping is free
		foreach ($items as $item) {
			$product = $item->buyable();
			if($product->ClassName != 'Event' && $product->ClassName != 'GiftVoucherProduct'){
				$freeShipping = false;
			}
			if($product->ClassName == 'WineProduct'){
				$wineCount += $item->Quantity;
			}
		}

		if($freeShipping == false){
			if(!isset($region)){
				$region = 'AUK';
			}
			$regionObject = DomesticShippingRegion::get()->filter(array('Region' => $region))->first();
			if($regionObject){
				$charge = $regionObject->Amount;
				$carrier = $regionObject->DomesticShippingCarriers()->first();
				if($carrier){
					foreach($carrier->DomesticShippingExtras() as $shippingExtra){
						$charge += $shippingExtra->Amount;
					}

					array_push($carriers, $carrier->ID);
				}
			}
		}

		//If the bottles of wine in the order is 12 or more then shipping is free
		if($wineCount >= 12){
			$charge = 0;
		}

		return array('Amount' => $charge, 'Carriers' => $carriers);
	}
}
