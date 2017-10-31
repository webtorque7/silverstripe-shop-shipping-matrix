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
        'ShippingMatrix' => 'StoreWarehouse'
    );

    private static $has_many = array(
        'ShippingRates' => 'ShippingRate'
    );

    private static $many_many = array(
        'ShippingWeightRanges' => 'ShippingWeightRange',
        'ShippingQuantityRanges' => 'ShippingQuantityRange',
        'InternationalShippingZones' => 'InternationalShippingZone'
    );

    protected $items = array();

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'ShippingMatrixID',
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
                    'Shipping Rates',
                    $this->ShippingRates(),
                    $rateConfig = GridFieldConfig_RelationEditor::create()
                )
            );

            $rateConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->removeComponentsByType('GridFieldAddExistingAutocompleter')
                ->removeComponentsByType('GridFieldDeleteAction')
                ->addComponent(new GridFieldDeleteAction(false));

            $fields->addFieldToTab('Root.ShippingZones',
                GridField::create(
                    'InternationalShippingZones',
                    'International Shipping Zones',
                    $this->InternationalShippingZones(),
                    $zoneConfig = GridFieldConfig_RecordViewer::create()
                )
            );

            $zoneConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->addComponent(new GridFieldAddExistingAutocompleter)
                ->addComponent(new GridFieldDeleteAction(true));

            $fields->addFieldToTab('Root.WeightRanges',
                GridField::create(
                    'ShippingWeightRanges',
                    'Shipping Weight Ranges',
                    $this->ShippingWeightRanges(),
                    $weightConfig = GridFieldConfig_RelationEditor::create()
                )
            );

            $weightConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->removeComponentsByType('GridFieldAddExistingAutocompleter')
                ->removeComponentsByType('GridFieldDeleteAction')
                ->addComponent(new GridFieldDeleteAction(false));

            $fields->addFieldToTab('Root.QuantityRanges',
                GridField::create(
                    'ShippingQuantityRanges',
                    'Shipping Quantity Ranges',
                    $this->ShippingQuantityRanges(),
                    $quantityConfig = GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            );

            $quantityConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->removeComponentsByType('GridFieldAddExistingAutocompleter')
                ->removeComponentsByType('GridFieldDeleteAction')
                ->addComponent(new GridFieldDeleteAction(false));
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
				AND "ShippingWeightRange"."MaxWeight" >= %s', $totalWeight, $totalWeight))
                ->first();

            if (!empty($weightRate)) {
                $rate = $weightRate->ShippingCharge;
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
				AND "ShippingQuantityRange"."MaxQuantity" >= %s', $totalQuantity, $totalQuantity))
                ->first();

            if (!empty($quantityRate)) {
                $quantityCharge = $quantityRate->ShippingCharge;
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

        if ($product && in_array($product->ClassName, $productTypeArray)) {
            array_push($this->items, $item);
        }

        return $this;
    }

    public static function process($items, $country)
    {
        $extraCharge = 0;

        $shippingZone = InternationalShippingZone::get_shipping_zone($country);
        $unsupportedCountryException = new ShippingMatrixException('Selected country is not supported please contact us to arrange other shipping methods.');

        if (empty($shippingZone)) {
            throw $unsupportedCountryException;
        }

        $carriers = $shippingZone->InternationalShippingCarriers()->toArray();

        if (empty($carriers)) {
            $unsupportedCountryException;
        }

        //distribute items to carrier which ships it
        foreach ($items as $item) {
            foreach ($carriers as $carrier) {
                $carrier->distributeItem($item);
            }
        }

        //get charge from each carrier
        foreach ($carriers as $carrier) {
            $extraCharge += $carrier->calculate($shippingZone);
        }

        singleton('InternationalShippingCarrier')->extend('UpdateShippingCharge', $shippingCharge, $carriers, $items, $country);
        return array('Amount' => $extraCharge, 'Carriers' => $carriers);
    }
}