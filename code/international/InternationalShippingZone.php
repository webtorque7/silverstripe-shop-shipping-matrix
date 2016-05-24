<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:35
 */
class InternationalShippingZone extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Sort' => 'Int',
        'ShippingCountries' => 'Text',
        'DefaultZone' => 'Boolean'
    );

    private static $has_one = array(
        'ShippingRate' => 'ShippingRate',
        'ShippingMatrix' => 'StoreWarehouse'
    );

    private static $belongs_many_many = array(
        'InternationalShippingCarriers' => 'InternationalShippingCarrier'
    );

    private static $summary_fields = array(
        'ShippingCountries' => 'Shipping Countries'
    );

    private static $searchable_fields = array(
        'ShippingCountries' => 'ShippingCountries'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'ShippingMatrixID',
            'InternationalShippingCarriers',
            'ShippingRateID'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            CheckboxField::create('DefaultZone', 'Default Zone'),
            CheckboxSetField::create(
                'ShippingCountries',
                'Shipping Countries',
                ShopConfig::config()->iso_3166_country_codes
            ))
        );

        return $fields;
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if(!$this->Title){
            $this->Title = $this->ShippingCountries;
        }
    }

    public static function get_shipping_zone($deliveryCountry)
    {
        $shippingZones = self::get_available_zones();

        foreach ($shippingZones as $shippingZone) {
            $countryArray = explode(",", $shippingZone->ShippingCountries);
            if (in_array($deliveryCountry, $countryArray)) {
                return $shippingZone;
            }
        }

        //fall back looking for default zone
        return InternationalShippingZone::get()->filter(array('DefaultZone' => true))->first();
    }

    public static function get_available_zones($country = null){
        $shippingMatrix = ShippingMatrixConfig::current($country);

        $shippingZones = InternationalShippingZone::get()
            ->innerJoin('InternationalShippingCarrier_InternationalShippingZones',
                '"InternationalShippingCarrier_InternationalShippingZones"."InternationalShippingZoneID" = "InternationalShippingZone"."ID"')
            ->innerJoin('InternationalShippingCarrier', '"InternationalShippingCarrier"."ID" = "InternationalShippingCarrier_InternationalShippingZones"."InternationalShippingCarrierID"')
            ->where('"InternationalShippingCarrier"."ShippingMatrixID" =  ' . $shippingMatrix->ID);

        return $shippingZones;
    }
}