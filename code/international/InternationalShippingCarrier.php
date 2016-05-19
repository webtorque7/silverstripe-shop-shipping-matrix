<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:06
 */
class InternationalShippingCarrier extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Sort' => 'Int',
        'UnitType' => 'Varchar',
        'SupportedProductType' => 'Varchar'
    );

    private static $has_one = array(
        'ShippingMatrixConfig' => 'ShippingMatrixConfig'
    );

    private static $belongs_to = array(
        'ShippingMatrixModifier' => 'ShippingMatrixModifier'
    );

    private static $has_many = array(
        'ShippingRates' => 'ShippingRate',
        'InternationalShippingZones' => 'InternationalShippingZone'
    );

    private static $many_many = array(
        'ShippingWeightRanges' => 'ShippingWeightRange',
        'ShippingQuantityRanges' => 'ShippingQuantityRange'
    );

    protected $items = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'ShippingMatrixConfigID',
            'InternationalShippingZones',
            'ShippingWeightRanges',
            'ShippingQuantityRanges',
            'ShippingRates'
        ));

        $subClasses = ClassInfo::subclassesFor('Product');
        $types = array_combine($subClasses, $subClasses);
        unset($types['Product']);

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Carrier Name'),
            OptionsetField::create('UnitType', 'Unit Type', array('Weight' => 'Weight', 'Quantity' => 'Quantity')),
            CheckboxSetField::create('SupportedProductType', 'Supported Product Types', $types),
        ));

        if($this->exists()){
            $fields->addFieldToTab('Root.Main',
                GridField::create(
                    'ShippingRates',
                    'Shipping Shipping Rates',
                    $this->ShippingRates(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            );

            $fields->addFieldToTab('Root.ShippingZones',
                GridField::create(
                    'InternationalShippingZones',
                    'International Shipping Zones',
                    $this->InternationalShippingZones(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            );

            $fields->addFieldToTab('Root.WeightRanges',
                GridField::create(
                    'ShippingWeightRanges',
                    'Shipping Weight Ranges',
                    $this->ShippingWeightRanges(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            );

            $fields->addFieldToTab('Root.QuantityRanges',
                GridField::create(
                    'ShippingQuantityRanges',
                    'Shipping Quantity Ranges',
                    $this->ShippingQuantityRanges(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            );
        }
        else{
            $fields->addFieldToTab('Root.Main', LiteralField::create('SavingTip', '<p class="message">Please save before adding setting shipping zones, rates and ranges.</p>'));
        }

        return $fields;
    }

    public function calculateWeightBased($zone)
    {
        $totalWeight = 0;
        $weightCharge = 0;
        foreach ($this->items as $item) {
            $buyable = $item->buyable();

            $weight = 0;
            if (($buyable->hasField('Weight') && $buyable->Weight)) {
                $weight = $buyable->Weight;
            } elseif ($buyable->ClassName == 'ProductVariation') {
                $weight = $buyable->Product()->Weight;
            }

            $totalWeight += $weight * $item->Quantity;
        }

        //round up to nearest weight
        if (ShippingMatrixConfig::current()->RoundUpWeight) {
            $totalWeight = ceil($totalWeight);
        }

        if ($totalWeight > 0) {
            $weightRate = ShippingRate::get()
                ->leftJoin('InternationalShippingCarrier',
                    '"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
                ->leftJoin('ShippingWeightRange', '"ShippingWeightRange"."ID" = "ShippingRate"."ShippingWeightRangeID"')
                ->where(sprintf('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingWeightRange"."MinWeight" <= %s
				AND "ShippingWeightRange"."MaxWeight" > %s', $totalWeight, $totalWeight))
                ->first();

            if (!empty($weightRate)) {
                $rate = $weightRate->AmountPerUnit;
                $weightCharge = $totalWeight * $rate;
            } else {
                throw new ShippingMatrixException('The total weight of the items exceeds the maximum weight of our couriers,
                    please contact us to arrange other shipping methods.');
            }
        }
        return $weightCharge;
    }

    public function calculateQuantityBased($zone)
    {
        $totalQuantity = 0;
        $quantityCharge = 0;
        foreach ($this->items as $item) {
            $totalQuantity += $item->Quantity;
        }
        if ($totalQuantity > 0) {
            $quantityRate = ShippingRate::get()
                ->leftJoin('InternationalShippingCarrier',
                    '"InternationalShippingCarrier"."ID" = "ShippingRate"."InternationalShippingCarrierID"')
                ->leftJoin('ShippingQuantityRange',
                    '"ShippingQuantityRange"."ID" = "ShippingRate"."ShippingQuantityRangeID"')
                ->where(sprintf('"InternationalShippingZoneID" = ' . $zone->ID . '
				AND "ShippingQuantityRange"."MinQuantity" <= %s
				AND "ShippingQuantityRange"."MaxQuantity" > %s', $totalQuantity, $totalQuantity))
                ->first();

            if (!empty($quantityRate)) {
                $rate = $quantityRate->AmountPerUnit;
                $quantityCharge = $totalQuantity * $rate;
            } else {
                throw new ShippingMatrixException('The total quantity of the items exceeds the maximum quantity of our couriers,
					please contact us to explore other shipping methods.');
            }
        }
        return $quantityCharge;
    }

    public function calculate($zone)
    {
        return $this->UnitType == 'Weight' ? $this->calculateWeightBased($zone) : $this->calculateQuantityBased($zone);
    }

    public function distributeItem($item)
    {
        $product = $item->buyable();

        if ($product instanceof ProductVariation) {
            $product = $product->Product();
        }

        $productTypeArray = explode(',', $this->SupportedProductType);

        if (in_array($product->ClassName, $productTypeArray)) {
            array_push($this->items, $item);
        }

        return $this;
    }

    public static function process($items, $country)
    {
        $shippingZone = InternationalShippingZone::get_shipping_zone($country);

        //must have a shipping zone
        if (empty($shippingZone)) {
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
            $charge += $carrier->calculate($shippingZone);
        }

        return array('Amount' => $charge, 'Carriers' => $carriers);
    }
}