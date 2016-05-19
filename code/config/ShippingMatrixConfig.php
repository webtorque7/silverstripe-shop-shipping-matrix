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
        'LocalCity' => 'Varchar(100)',
        'ShippingMargin' => 'Percentage',
        'ShippingMessage' => 'HTMLText',
        'AllowPickup' => 'Boolean',
        'RoundUpWeight' => 'Boolean',
        'FreeShippingQuantity' => 'Int',
        'InternationalShippingWarningMessage' => 'HTMLText',
        'FreeShippingText' => 'Varchar(200)',
        'DomesticShippingText' => 'Varchar(200)',
        'InternationalShippingText' => 'HTMLText',
        'PickupText' => 'Varchar(200)'
    );

    private static $many_many = array(
        'DomesticShippingCarriers' => 'DomesticShippingCarrier',
        'InternationalShippingCarriers' => 'InternationalShippingCarrier'
    );

    private static $defaults = array(
        'RoundUpWeight' => true
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array(
            'Shipping',
            'LocalCity',
            'ShippingMargin',
            'ShippingMessage',
            'AllowPickup',
            'RoundUpWeight',
            'FreeShippingQuantity',
            'InternationalShippingWarningMessage',
            'FreeShippingText',
            'DomesticShippingText',
            'InternationalShippingText',
            'PickupText',
            'DomesticShippingCarriers',
            'InternationalShippingCarriers'
        ));
        $fields->addFieldToTab('Root', new TabSet('Shipping',
            new Tab('Main',
                CheckboxField::create('RoundUpWeight', 'Round up weight to the nearest kg?'),
                CheckboxField::create('AllowPickup', 'Allow Pickup?'),
                TextField::create('PickupText', 'Pickup Option Text'),
                TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
                TextField::create('FreeShippingText', 'Free Shipping Option Text'),
                TextField::create('DomesticShippingText', 'Domestic Option Text'),
                HtmlEditorField::create('InternationalShippingText', 'International Option Text')->setRows(5),
                HtmlEditorField::create('ShippingMessage', 'Shipping Message'),
                HtmlEditorField::create('InternationalShippingWarningMessage', 'International Shipping Warning Message')
            ),
            new Tab('Domestic',
                GridField::create(
                    'DomesticShippingRegion',
                    'Domestic Shipping Region',
                    DomesticShippingRegion::get(),
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
                    InternationalShippingZone::get(),
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

    public function canDelete($member = null)
    {
        return false;
    }

    public static function current()
    {
        $class = Config::inst()->get('ShippingMatrixConfig', 'config_class');
        return $class::current();
    }
} 