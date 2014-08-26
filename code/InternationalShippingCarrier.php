<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:06
 */

class InternationalShippingCarrier extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'UnitType' => 'Varchar',
		'SupportedProductType' => 'Varchar',
		'TrackingURL' => 'Varchar',
		'TrackerType' => 'Varchar'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $has_many = array(
		'ShippingRates' => 'ShippingRate'
	);

	private static $many_many = array(
		'InternationalShippingZones' => 'InternationalShippingZone',
		'ShippingWeightRanges' => 'ShippingWeightRange',
		'ShippingQuantityRanges' => 'ShippingQuantityRange'
	);

	protected $items = array();

	public function canView($member = null) {
		return true;
	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$subClasses = ClassInfo::subclassesFor('Product');
		$types = array_combine($subClasses, $subClasses);
		unset($types['Product']);

		$trackerClasses = ClassInfo::subclassesFor('TrackingLinkGenerator');
		$trackers = array_combine($trackerClasses, $trackerClasses);

		$fields->addFieldsToTab('Root.Main', array(
			OptionsetField::create('UnitType', 'Unit Type', array('Weight' => 'Weight', 'Quantity' => 'Quantity')),
			CheckboxSetField::create('SupportedProductType', 'Supported Product Types', $types),
			TextField::create('TrackingURL', 'Tracking URL'),
			DropdownField::create('TrackerType', 'Tracker Type', $trackers)
		));
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingZones');
		$fields->removeByName('ShippingWeightRanges');
		$fields->removeByName('ShippingQuantityRanges');
		$fields->removeByName('ShippingRates');

		$ZoneRangeGrid = GridField::create(
			'ShippingRates',
			'Shipping Rates',
			$this->ShippingRates(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));
		$fields->addFieldToTab('Root.Main', $ZoneRangeGrid);

		$shippingZoneGrid = GridField::create(
			'InternationalShippingZones',
			'International Shipping Zones',
			$this->InternationalShippingZones(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));
		$fields->addFieldToTab('Root.ShippingZones', $shippingZoneGrid);

		$weightRangeGrid = GridField::create(
			'ShippingWeightRanges',
			'Shipping Weight Ranges',
			$this->ShippingWeightRanges(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort', '')));
		$fields->addFieldToTab('Root.WeightRanges', $weightRangeGrid);

		$quantityRangeGrid = GridField::create(
			'ShippingQuantityRanges',
			'Shipping Quantity Ranges',
			$this->ShippingQuantityRanges(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$fields->addFieldToTab('Root.QuantityRanges', $quantityRangeGrid);
		$fields->removeByName('TrackingLinkGeneratorID');
		return $fields;
	}

	public function getTrackingLink($order){
		return $this->TrackerType ? singleton($this->TrackerType)->getTrackingLink($this->TrackingURL, $order) : false;
	}

	public function calculateWeightBased($zone) {
		$totalWeight = 0;
		foreach($this->items as $item){
			$product = $item->buyable();
			$totalWeight += $product->Weight * $item->Quantity;
		}
		if($totalWeight > 0){
			$weightRange = ShippingWeightRange::get()
				->where($totalWeight . ' BETWEEN "MinWeight" AND "MaxWeight"')
				->first();

			$weightRate = '';

			if(!empty($weightRange)){
			$weightRate = ShippingRate::get()
				->leftJoin('InternationalShippingCarrier',
					'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
				->where('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingWeightRangeID" = ' . $weightRange->ID)
				->first();
			}

			if(!empty($weightRate)){
				$rate = $weightRate->ShippingCharge;
				$totalCharge = $totalWeight * $rate;

				//save used carriers to session so it's tracking url can be used later
				$carrier = $weightRate->InternationalShippingCarrier();
				if ($carriers = Session::get('UsedCarriers')) {
					if(!in_array($carrier->ID, $carriers, true)){
						array_push($carriers, $carrier->ID);
						Session::set('UsedCarriers', $carriers);
					}
				}
				else{
					$carriers = array();
					array_push($carriers, $carrier->ID);
					Session::set('UsedCarriers', $carriers);
				}

				return $totalCharge;
			}
			else{
				user_error('The total weight of the items exceeds the maximum weight of our couriers,
				please contact us to arrange other shipping methods.');
			}
		}
		return 0;
	}

	public function calculateQuantityBased($zone) {
		$totalQuantity = 0;
		foreach($this->items as $item){
			$totalQuantity += $item->Quantity;
		}
		if($totalQuantity > 0){
			$quantityRange = ShippingQuantityRange::get()
				->where($totalQuantity . ' BETWEEN "MinQuantity" AND "MaxQuantity"')
				->first();

			$quantityRate = '';

			if(!empty($quantityRange)){
				$quantityRate = ShippingRate::get()
					->leftJoin('InternationalShippingCarrier',
						'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
					->where('"InternationalShippingZoneID" = ' . $zone->ID . '
					AND "ShippingQuantityRangeID" = ' . $quantityRange->ID)
					->first();
			}

			if(!empty($quantityRate)){
				$totalCharge = $quantityRate->ShippingCharge;

				//save used carriers to session so it's tracking url can be used later
				$carrier = $quantityRate->InternationalShippingCarrier();
				if ($carriers = Session::get('UsedCarriers')) {
					if(!in_array($carrier->ID, $carriers, true)){
						array_push($carriers, $carrier->ID);
						Session::set('UsedCarriers', $carriers);
					}
				}
				else{
					$carriers = array();
					array_push($carriers, $carrier->ID);
					Session::set('UsedCarriers', $carriers);
				}

				return $totalCharge;
			}
			else{
				user_error('The total quantity of the items exceeds the maximum quantity of our couriers,
					please contact us to explore other shipping methods.');
			}
		}
		return 0;
	}

	public function calculate($zone) {
		return $this->UnitType == 'Weight' ? $this->calculateWeightBased($zone) : $this->calculateQuantityBased($zone);
	}

	public function distributeItem($item){
		$product = $item->buyable();
		$productTypeArray = explode(',', $this->SupportedProductType);
		if(in_array($product->ClassName, $productTypeArray)){
			array_push($this->items, $item);
		}
		return $this;
	}

	public static function process($items, $country) {
		$shippingZone = InternationalShippingZone::get_shipping_zone($country);
		$carriers = InternationalShippingCarrier::get()->toArray();

		//distribute items to carrier which ships it
		foreach ($items as $item) {
			foreach ($carriers as $carrier) {
				$carrier->distributeItem($item);
			}
		}

		//get charge from each carrier
		$charge = 0;
		foreach ($carriers as $carrier) {
			if(!empty($shippingZone)){
				$charge += $carrier->calculate($shippingZone);
			}
			else{
				user_error('Selected country is not supported,
					please contact us to arrange other shipping methods.');
			}
		}
		return $charge;
	}
}