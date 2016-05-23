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
        'ShippingMatrix' => 'StoreWarehouse'
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
            'DomesticShippingCarrierID'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            TextField::create('Region', 'Region'),
            TextField::create('Amount', 'Amount'),
            CheckboxField::create('DefaultRegion', 'Default Region?')
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

    public static function get_shipping_region($deliveryRegion)
    {
        $availableRegions = self::get_available_regions();
        if (!empty($availableRegions)) {
            $shippingRegion = $availableRegions->filter('Region', $deliveryRegion)->first();

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