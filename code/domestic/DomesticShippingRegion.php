<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 9/05/14
 * Time: 1:28 PM
 */
class DomesticShippingRegion extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Region' => 'Text',
        'Sort' => 'Int',
        'Amount' => 'Currency',
        'DefaultRegion' => 'Boolean'
    );

    private static $has_one = array(
        'DomesticShippingCarrier' => 'DomesticShippingCarrier',
        'ShippingMatrix' => 'StoreWarehouse',

        'ShippingQuantityRange' => 'ShippingQuantityRange'
    );

    private static $summary_fields = array(
        'Region' => 'Region',
        'Amount' => 'Amount'
    );

    private static $searchable_fields = array(
        'Region' => 'Region'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'ShippingMatrixID',
            'DomesticShippingCarrierID',
            'ShippingQuantityRangeID'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            TextField::create('Region', 'Region'),
            TextField::create('Amount', 'Amount'),
            CheckboxField::create('DefaultRegion', 'Default Region?'),
            DropdownField::create(
                'ShippingQuantityRangeID',
                'Shipping Quantity Range',
                ShippingQuantityRange::get()->map()
            )->setEmptyString('Not Applicable'),
        ));

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->Title){
            $this->Title = $this->Region;
        }
    }

    public static function get_shipping_region($deliveryRegion = null, $quantity)
    {
        $availableRegions = self::get_available_regions();
        if (!empty($availableRegions)) {

            if($deliveryRegion != null && $quantity > 0){
                $shippingRegion = $availableRegions->leftJoin('ShippingQuantityRange',
                    '"ShippingQuantityRange"."ID" = "DomesticShippingRegion"."ShippingQuantityRangeID"')
                    ->where(sprintf('"Region" = \'' . $deliveryRegion . '\'
				AND "ShippingQuantityRange"."MinQuantity" <= %s
				AND "ShippingQuantityRange"."MaxQuantity" >= %s', $quantity, $quantity))
                ->first();
            }

            if (empty($shippingRegion)) {
                $shippingRegion = $availableRegions->filter(array('Region' => $deliveryRegion, 'ShippingQuantityRangeID' => 0))->first();
            }

            if ($shippingRegion && $shippingRegion->exists()) {
                return $shippingRegion;
            }

            return $availableRegions->filter(array('DefaultRegion' => true))->first();
        }
    }

    public static function get_available_regions($country = null)
    {
        $shippingMatrix = ShippingMatrixConfig::current($country);
        $domesticCarriers = $shippingMatrix->DomesticShippingCarriers();
        $carrierIDs = implode(',', $domesticCarriers->column('ID'));
        if(!empty($carrierIDs)){
            return DomesticShippingRegion::get()->where('"DomesticShippingCarrierID" IN (' . $carrierIDs . ')');
        }
    }
}