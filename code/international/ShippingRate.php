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
        'ShippingCharge' => 'Decimal'
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

        $fields->removeByName(array(
            'Sort',
            'InternationalShippingCarrierID',
            'InternationalShippingZoneID',
            'ShippingWeightRangeID',
            'ShippingQuantityRangeID'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            DropdownField::create(
                'InternationalShippingCarrierID',
                'International Shipping Carrier',
                InternationalShippingCarrier::get()->map()
            ),
            LiteralField::create('UnitHelper',
                'Select a weight based range OR a quantity based range. Then specify the amount per unit selected'),
            DropdownField::create(
                'ShippingWeightRangeID',
                'Shipping Weight Range',
                ShippingWeightRange::get()->map()
            )->setEmptyString('Not Applicable'),
            DropdownField::create(
                'ShippingQuantityRangeID',
                'Shipping Quantity Range',
                ShippingQuantityRange::get()->map()
            )->setEmptyString('Not Applicable'),
            TextField::create('ShippingCharge', 'Shipping Charge')
                ->setDescription('For quantity based shipping, input the total charge for this range. For weight based shipping, input the multiplier per kg.')
        ));

        if ($this->exists() && $this->InternationalShippingCarrierID > 0) {
            $fields->addFieldToTab('Root.Main',
                DropdownField::create(
                    'InternationalShippingZoneID',
                    'Shipping Zone',
                    $this->InternationalShippingCarrier()->InternationalShippingZones()->map('ID', 'Title')
                )
            );
        } else {
            $fields->addFieldToTab('Root.Main',
                LiteralField::create('supportedzones',
                    '<p class="message">please save before choosing the shipping zone</p>')
            );
        }

        return $fields;
    }

}