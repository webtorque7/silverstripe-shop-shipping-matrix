<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */
class ShippingMatrixConfig extends DataExtension
{
    private static $singular_name = 'Global Shipping setting';
    private static $plural_name = 'Global Shipping settings';

    private static $db = array(
        'DomesticCountry' => 'Varchar',
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

    private static $has_one = array(
        'DefaultDomesticRegion' => 'DomesticShippingRegion'
    );

    private static $defaults = array(
        'RoundUpWeight' => true
    );

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array(
            'Shipping',
            'DomesticCountry',
            'AllowPickup',
            'RoundUpWeight',
            'FreeShippingQuantity',
            'FreeShippingText',
            'DomesticShippingText',
            'PickupText',
            'InternationalShippingText',
            'ShippingMessage',
            'InternationalShippingWarningMessage',
            'DefaultDomesticRegion',
            'DomesticShippingCarrier',
            'InternationalShippingCarrier',
            'DomesticShippingRegion',
            'InternationalShippingZone'
        ));
        $fields->addFieldToTab('Root', new TabSet('Shipping',
            new Tab('Main',
                DropdownField::create(
                    'DomesticCountry',
                    'Domestic Country',
                    SiteConfig::current_site_config()->getCountriesList(),
                    'NZ'
                ),
                DropdownField::create(
                    'DefaultDomesticRegion',
                    'Default Domestic Region',
                    DomesticShippingRegion::get()->map()
                ),
                CheckboxField::create('AllowPickup', 'Allow Pickup'),
                CheckboxField::create('RoundUpWeight', 'Round up weight')
                    ->setDescription('Round up weight to the nearest kg'),
                TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
                TextField::create('FreeShippingText', 'Free Shipping Text'),
                TextField::create('DomesticShippingText', 'Domestic Shipping Text'),
                TextField::create('PickupText', 'Pickup Text'),
                HtmlEditorField::create('InternationalShippingText', 'International Shipping Text'),
                HtmlEditorField::create('ShippingMessage', 'Shipping Message')->setRows(20),
                HtmlEditorField::create('InternationalShippingWarningMessage',
                    'International Shipping Warning Message')->setRows(20)
            ),
            new Tab('DomesticShippingCarrier',
                GridField::create(
                    'DomesticShippingCarrier',
                    'Domestic Shipping Carrier',
                    DomesticShippingCarrier::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            ),
            new Tab('InternationalCarriers',
                GridField::create(
                    'InternationalShippingCarrier',
                    'International Shipping Carrier',
                    InternationalShippingCarrier::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            ),
            new Tab('RegionsAndZones',
                GridField::create(
                    'DomesticShippingRegion',
                    'Domestic Shipping Region',
                    DomesticShippingRegion::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
                ),
                GridField::create(
                    'InternationalShippingZone',
                    'International Shipping Zone',
                    InternationalShippingZone::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
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