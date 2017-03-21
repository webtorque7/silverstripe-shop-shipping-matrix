<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */
class ShippingMatrixConfig extends DataExtension
{
    private static $singular_name = 'Shipping setting';
    private static $plural_name = 'Shipping settings';

    private static $db = array(
        'ShippingMessage' => 'HTMLText',
        'AllowPickup' => 'Boolean',
        'RoundUpWeight' => 'Boolean',
        'FreeShippingQuantity' => 'Int',
        'FreeShippingText' => 'Varchar(200)',
        'FreeShippingPromotion' => 'HTMLText',
        'InternationalShippingWarningMessage' => 'HTMLText',
        'DomesticShippingText' => 'Varchar(200)',
        'InternationalShippingText' => 'HTMLText',
        'PickupText' => 'Varchar(200)'
    );

    private static $has_many = array(
        'DomesticShippingCarriers' => 'DomesticShippingCarrier',
        'InternationalShippingCarriers' => 'InternationalShippingCarrier',

        'InternationalShippingZones' => 'InternationalShippingZone',
        'DomesticShippingRegions' => 'DomesticShippingRegion'

    );

    private static $defaults = array(
        'RoundUpWeight' => true
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array(
            'Shipping',
            'ShippingMessage',
            'AllowPickup',
            'RoundUpWeight',
            'FreeShippingQuantity',
            'InternationalShippingWarningMessage',
            'FreeShippingText',
            'FreeShippingPromotion',
            'DomesticShippingText',
            'InternationalShippingText',
            'PickupText',
            'DomesticShippingCarriers',
            'InternationalShippingCarriers',
            'InternationalShippingZones',
            'DomesticShippingRegions',
        ));

        if($this->owner->exists()){
            $fields->addFieldToTab('Root', new TabSet('Shipping',
                new Tab('Main',
                    CheckboxField::create('RoundUpWeight', 'Round up weight to the nearest kg?'),
                    CheckboxField::create('AllowPickup', 'Allow Pickup?'),
                    TextField::create('PickupText', 'Pickup Option Text'),
                    TextField::create('DomesticShippingText', 'Domestic Option Text'),
                    TextField::create('FreeShippingQuantity', 'Free Shipping Quantity')
                        ->setDescription('set this to 0 to disable free shipping option'),
                    TextField::create('FreeShippingText', 'Free Shipping Option Text'),
                    HtmlEditorField::create('FreeShippingPromotion', 'Free Shipping Promotion')->setRows(5),
                    HtmlEditorField::create('InternationalShippingText', 'International Option Text')->setRows(5),
                    HtmlEditorField::create('ShippingMessage', 'Shipping Message'),
                    HtmlEditorField::create('InternationalShippingWarningMessage', 'International Shipping Warning Message')
                ),
                new Tab('Domestic',
                    GridField::create(
                        'DomesticShippingRegion',
                        'Domestic Shipping Region',
                        $this->owner->DomesticShippingRegions(),
                        GridFieldConfig_RecordEditor::create()
                            ->addComponent(GridFieldOrderableRows::create('Sort'))
                    ),
                    GridField::create(
                        'DomesticShippingCarriers',
                        'Domestic Shipping Carriers',
                        $this->owner->DomesticShippingCarriers(),
                        GridFieldConfig_RecordEditor::create()
                            ->addComponent(GridFieldOrderableRows::create('Sort'))
                    )
                ),
                new Tab('International',
                    GridField::create(
                        'InternationalShippingZone',
                        'International Shipping Zone',
                        $this->owner->InternationalShippingZones(),
                        GridFieldConfig_RecordEditor::create()
                            ->addComponent(GridFieldOrderableRows::create('Sort'))
                    ),
                    GridField::create(
                        'InternationalShippingCarriers',
                        'International Shipping Carriers',
                        $this->owner->InternationalShippingCarriers(),
                        GridFieldConfig_RecordEditor::create()
                            ->addComponent(GridFieldOrderableRows::create('Sort'))
                    )
                )
            ));
        }
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public static function current($country = null)
    {
        $class = Config::inst()->get('ShippingMatrixConfig', 'config_class');
        return $class::current($country);
    }

    public function canShipOverseas(){

        return ($this->owner->InternationalShippingCarriers()->count() > 0) ? true : false;
    }
} 