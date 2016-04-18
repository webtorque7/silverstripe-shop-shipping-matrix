<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */
class ShippingMatrixConfig extends DataObject
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
        'AllowedCountries' => 'Text'
    );

    private static $has_one = array(
        'DefaultDomesticRegion' => 'DomesticShippingRegion'
    );

    private static $defaults = array(
        'RoundUpWeight' => true
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Main'
        ));

        $fields->addFieldsToTab('Root.Settings', array(
            DropdownField::create(
                'DomesticCountry',
                'Domestic Country',
                $this->getCountriesList(),
                'NZ'
            ),
            DropdownField::create(
                'DefaultDomesticRegion',
                'Default Domestic Region',
                DomesticShippingRegion::get()->map()
            ),
            TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
            CheckboxField::create('AllowPickup', 'Allow Pickup'),
            CheckboxField::create('RoundUpWeight', 'Round up weight')->setDescription('Round up weight to the nearest kg'),
            HtmlEditorField::create('ShippingMessage', 'Shipping Message')->setRows(20),
            HtmlEditorField::create('InternationalShippingWarningMessage', 'International Shipping Warning Message')->setRows(20)
        ));

        $fields->addFieldsToTab('Root.DomesticShippingCarrier', array(
            GridField::create(
                'DomesticShippingCarrier',
                'Domestic Shipping Carrier',
                DomesticShippingCarrier::get(),
                GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
            )
        ));

        $fields->addFieldsToTab('Root.InternationalCarriers', array(
            GridField::create(
                'InternationalShippingCarrier',
                'International Shipping Carrier',
                InternationalShippingCarrier::get(),
                GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
            )
        ));

        $fields->addFieldsToTab('Root.RegionsAndZones', array(
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
        ));

        $fields->addFieldsToTab('Root.AllowedCountries', array(
            CheckboxSetField::create('AllowedCountries', 'Allowed Ordering and Shipping Countries', $this->config()->iso_3166_country_codes)
        ));

        $this->extend('updateCMSFields', $fields);
        return $fields;
    }

    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        if (!DataObject::get_one('ShippingMatrixConfig')) {
            $matrix = ShippingMatrixConfig::create();
            $matrix->write();
        }
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canCreate($member = null)
    {
        return !DataObject::get_one('ShippingMatrixConfig');
    }

    public static function current()
    {
        if (class_exists('Fluent') && class_exists('ShopStore')) {
            $locale = Fluent::current_locale();
            $country = array_search($locale, self::config()->country_locale_mapping);
            $store = ShopStore::get()->filter(array('Country' => $country))->first();
            if ($store && $store->exists()) {
                return StoreShippingMatrixConfig::get()->filter(array('Country' => $store->Country))->first();
            }
        }

        return DataObject::get_one('ShippingMatrixConfig');
    }

    /**
     * Carried over from SilverShop's Shop Config
     * @param bool|false $prefixisocode
     * @return array|scalar
     */
    public function getCountriesList($prefixisocode = false)
    {
        $countries = $this->config()->iso_3166_country_codes;
        asort($countries);
        if ($allowed = $this->owner->AllowedCountries) {
            $allowed = explode(",", $allowed);
            if (count($allowed > 0)) {
                $countries = array_intersect_key($countries, array_flip($allowed));
            }
        }
        if ($prefixisocode) {
            foreach ($countries as $key => $value) {
                $countries[$key] = "$key - $value";
            }
        }
        return $countries;
    }

    /**
     * Carried over from SilverShop's Shop Config
     * @param bool|false $fullname
     * @return mixed|null
     */
    public function getSingleCountry($fullname = false)
    {
        $countries = $this->owner->getCountriesList();
        if (count($countries) == 1) {
            if ($fullname) {
                return array_pop($countries);
            } else {
                reset($countries);
                return key($countries);
            }
        }
        return null;
    }
} 