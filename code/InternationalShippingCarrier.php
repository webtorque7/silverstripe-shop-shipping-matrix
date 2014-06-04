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
		'SupportedProductType' => 'Varchar'
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

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$subClasses = ClassInfo::subclassesFor('Product');
		$types = array_combine($subClasses, $subClasses);
		unset($types['Product']);

		$fields->addFieldsToTab('Root.Main', array(
			OptionsetField::create('UnitType', 'Unit Type', array('Weight' => 'Weight', 'Quantity' => 'Quantity')),
			CheckboxSetField::create('SupportedProductType', 'Supported Product Types', $types)
		));
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingZones');
		$fields->removeByName('ShippingWeightRanges');
		$fields->removeByName('ShippingQuantityRanges');
		$fields->removeByName('ShippingRates');

		$ZoneRangeGrid = GridField::create(
			'ShippingRates',
			'Shipping Shipping Rates',
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
		return $fields;
	}

	public static function distributeItems($item){
		/** Products don't have info on shipping unit type.
		 *  Find the carrier that ships the product type and associate it with the item.
		 */
		$product = $item->buyable();
		$carrier = InternationalShippingCarrier::get()
			->where('"InternationalShippingCarrier"."SupportedProductType" LIKE \'%' . $product->ClassName . '%\'')
			->first();

		if(!empty($carrier)){
			$unitType = $carrier->UnitType;
			$unit = array();
			if($unitType == 'Quantity'){
				$unit = array($unitType, $item->Quantity);
			}
			else if($unitType == 'Weight'){
				$unit = array($unitType, $product->Weight);
			}
			return $unit;
		}
		else{
			echo ('Could not find a carrier that ships ' . $product->ClassName);
			return false;
		}
	}

	public static function calculateCharge($zone, $totalQuantity, $totalWeight){
		$quantityRange = ShippingQuantityRange::get()
			->where($totalQuantity . ' BETWEEN "MinQuantity" AND "MaxQuantity"');
		$weightRange = ShippingWeightRange::get()
			->where($totalWeight . ' BETWEEN "MinWeight" AND "MaxWeight"');

		$quantityRangeID = $quantityRange->first()->ID;
		$weightRangeID = $weightRange->first()->ID;

		$quantityRate = ShippingRate::get()
			->leftJoin('InternationalShippingCarrier',
				'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
			->where('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingQuantityRangeID" = ' . $quantityRangeID)
			->first()->AmountPerUnit;

		$weightRate = ShippingRate::get()
			->leftJoin('InternationalShippingCarrier',
				'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
			->where('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingWeightRangeID" = ' . $weightRangeID)
			->first()->AmountPerUnit;

		$totalQuantityCharge = $totalQuantity * $quantityRate;
		$totalWeightCharge = $totalWeight * $weightRate;

		$totalCharge = $totalQuantityCharge + $totalWeightCharge;
		return $totalCharge;
	}

//	public function calculateWeightBased() {
//
//		//lookup weight range
//	}
//
//	public function calculateItemBased() {
//
//		//lookup item range
//	}
//
//	public function calculate() {
//		return $this->Type == 'Weight' ? $this->calculateWeightBased() : $this->calculateItemBased();
//	}
//
//	public function addItem($item) {
//		$this->items[] = $item;
//		return $this;
//	}
//
//	public static function process($items, $country) {
//		$shippingZone = InternationalShippingZone::get_shipping_zone($country);
//		$carriers = InternationalShippingCarrier::get();
//
//		//distribute items to carrier which ships it
//		foreach ($items as $item) {
//			foreach ($carriers as $carrier) {
//				if ($carrier->shipsItem($item)) {
//					$carrier->addItem($item);
//				}
//			}
//		}
//
//		//get charge from each carrier
//		$charge = 0;
//		foreach ($carriers as $carrier) {
//			$charge += $carrier->calcluate();
//		}
//
//		return $charge;
//	}
}