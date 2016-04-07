<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 3/06/14
 * Time: 11:32 AM
 */
class ShippingRate extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Sort' => 'Int',
        'AmountPerUnit' => 'Decimal'
    );

    private static $has_one = array(
        'InternationalShippingCarrier' => 'InternationalShippingCarrier',
        'InternationalShippingZone' => 'InternationalShippingZone',
        'ShippingWeightRange' => 'ShippingWeightRange',
        'ShippingQuantityRange' => 'ShippingQuantityRange'
    );

    private static $summary_fields = array(
        'InternationalShippingCarrier.Title' => 'International Shipping Carrier',
        'ShippingWeightRange.Title' => 'Weight Range',
        'ShippingQuantityRange.Title' => 'Quantity Range',
        'InternationalShippingZone.Title' => 'Shipping Zone'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName('Sort');
        $fields->removeByName('Title');
        $fields->removeByName('InternationalShippingCarrier');

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            DropdownField::create(
                'InternationalShippingCarrierID',
                'International Shipping Carrier',
                InternationalShippingCarrier::get()->map('ID', 'Title')
            ),
            LiteralField::create('UnitHelper',
                'Select a weight based range OR a quantity based range. Then specify the amount per unit selected'),
            DropdownField::create(
                'ShippingWeightRangeID',
                'Shipping Weight Range',
                ShippingWeightRange::get()->map('ID', 'Title')

            )->setEmptyString('Not Applicable'),
            DropdownField::create(
                'ShippingQuantityRangeID',
                'Shipping Quantity Range',
                ShippingQuantityRange::get()->map('ID', 'Title')
            )->setEmptyString('Not Applicable'),
            TextField::create('AmountPerUnit', 'Amount Per Unit'),
            //show a message if InternationalShippingCarrier not available yet
            ($this->InternationalShippingCarrierID > 0) ?
                DropdownField::create(
                    'InternationalShippingZoneID',
                    'Shipping Zone',
                    $this->InternationalShippingCarrier()->InternationalShippingZones()->map('ID', 'Title')
                ) :
                LiteralField::create('supportedzones', '<p>please save before choosing the shipping zone</p>')
        ));

        return $fields;
    }

}