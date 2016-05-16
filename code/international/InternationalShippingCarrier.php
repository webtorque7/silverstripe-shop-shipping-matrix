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

	public function getTrackingLink($order)
	{
		//todo
		return false;
	}

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

	public function calculateWeightBased($zone) {
		$totalWeight = 0;
		foreach($this->items as $item){
			$buyable = $item->buyable();
			$weight = ($buyable->hasField('Weight') && $buyable->Weight)
				? $buyable->Weight
				: (
					$buyable->ClassName == 'ProductVariation'
						? $buyable->Product()->Weight
						: 0
				);

			$totalWeight += $weight * $item->Quantity;
		}

		//round up to nearest weight
		if (ShippingMatrixConfig::current_config()->RoundUpWeight) {
			$totalWeight = ceil($totalWeight);
		}

		if($totalWeight > 0){
//			$weightRange = ShippingWeightRange::get()
//				->where($totalWeight . ' BETWEEN "MinWeight" AND "MaxWeight"');

//			$weightRangeID = $weightRange->first()->ID;
			$weightRate = ShippingRate::get()
				->leftJoin('InternationalShippingCarrier',
					'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
				->leftJoin('ShippingWeightRange', '"ShippingWeightRange"."ID" = "ShippingRate"."ShippingWeightRangeID"')
				->where(sprintf('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingWeightRange"."MinWeight" <= %s AND "ShippingWeightRange"."MaxWeight" > %s', $totalWeight, $totalWeight))
				->first();

			if(!empty($weightRate)){
				$rate = $weightRate->AmountPerUnit;
				$weightCharge = $totalWeight * $rate;
			}
			else{
				$weightCharge = 0;
//				throw new Exception('The total weight of the items exceeds the maximum weight of our couriers,
//				please contact us to arrange other shipping methods.');
			}
			return $weightCharge;
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
				->where($totalQuantity . ' BETWEEN "MinQuantity" AND "MaxQuantity"');

			$quantityRangeID = $quantityRange->first()->ID;
			$quantityRate = ShippingRate::get()
				->leftJoin('InternationalShippingCarrier',
					'"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
				->where('"InternationalShippingZoneID" = ' . $zone->ID . '
					AND "ShippingQuantityRangeID" = ' . $quantityRangeID)
				->first();

			if(!empty($quantityRate)){
				$rate = $quantityRate->AmountPerUnit;
				$quantityCharge = $totalQuantity * $rate;
			}
			else{
				throw new ShippingMatrixException('The total quantity of the items exceeds the maximum quantity of our couriers,
					please contact us to explore other shipping methods.');
			}
			return $quantityCharge;
		}
		return 0;
	}

	public function calculate($zone) {
		return $this->UnitType == 'Weight' ? $this->calculateWeightBased($zone) : $this->calculateQuantityBased($zone);
	}

	public function distributeItem($item){
		$product = $item->buyable();

		if ($product instanceof ProductVariation) {
			$product = $product->Product();
		}

		$productTypeArray = explode(',', $this->SupportedProductType);
		if(in_array($product->ClassName, $productTypeArray)){
			array_push($this->items, $item);
		}
		return $this;
	}

	public static function process($items, $country) {
		$shippingZone = InternationalShippingZone::get_shipping_zone($country);

		//must have a shipping zone
		if (!$shippingZone) {
			throw new ShippingMatrixException('Selected country is not supported,
					please contact us to arrange other shipping methods.');
		}

		$carriers = $shippingZone->InternationalShippingCarriers()->toArray();

		if (empty($carriers)) {
			throw new ShippingMatrixException('Selected country is not supported,
					please contact us to arrange other shipping methods.');
		}

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
		}

		return array('Amount' => $charge, 'Carriers' => $carriers);
	}
}