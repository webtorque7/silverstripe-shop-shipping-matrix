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
        'DomesticShippingCarrier' => 'DomesticShippingCarrier'
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'Region' => 'Region',
        'Amount' => 'Amount'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'DomesticShippingCarrierID'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            TextField::create('Region', 'Region'),
            TextField::create('Amount', 'Amount'),
            CheckboxField::create('DefaultRegion', 'Default Region'),
        ));

        return $fields;
    }

    public static function get_shipping_region($deliveryRegion)
    {
        $availableRegions = self::get_available_regions();
        if (!empty($availableRegions)) {
            $shippingRegion = $availableRegions->filter('Region:PartialMatch', $deliveryRegion)->first();

            if ($shippingRegion && $shippingRegion->exists()) {
                return $shippingRegion;
            }

            //fall back looking for default region
            return $availableRegions->filter(array('DefaultRegion', true))->first();
        }
    }

    public static function get_available_regions()
    {
        $shippingMatrix = ShippingMatrixConfig::current();
        $domesticCarriers = $shippingMatrix->DomesticShippingCarriers();
        $carrierIDs = implode(',', $domesticCarriers->column('ID'));

        return DomesticShippingRegion::get()->where('"DomesticShippingCarrierID" IN (' . $carrierIDs . ')');
    }
}