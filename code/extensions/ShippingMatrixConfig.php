<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */
class ShippingMatrixConfig extends DataExtension
{
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

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(array(
            'ShippingMatrix',
            'DomesticCountry',
            'LocalCity',
            'ShippingMargin',
            'ShippingMessage',
            'AllowPickup',
            'RoundUpWeight',
            'FreeShippingQuantity',
            'InternationalShippingWarningMessage',
            'AllowedCountries',
            'DefaultDomesticRegionID',
        ));

        $shippingTab = new TabSet(
            'ShippingMatrix',
            new Tab(
                'Main',
                DropdownField::create("DomesticCountry", _t('ShippingMatrix.DOMESTICCOUNTRY', 'Domestic Country'),
                    $this->owner->getCountriesList(), 'NZ'),
                DropdownField::create("DefaultDomesticRegion",
                    _t('ShippingMatrix.DEFAULTDOMESTICREGION', 'Default Domestic Region'),
                    DomesticShippingRegion::get()->map()),
                TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
                CheckboxField::create('AllowPickup', 'Allow Pickup'),
                CheckboxField::create('RoundUpWeight', 'Round up weight')
                    ->setDescription('Round up weight to the nearest kg'),
                HtmlEditorField::create('ShippingMessage', 'Shipping Message')->setRows(20),
                HtmlEditorField::create('InternationalShippingWarningMessage',
                    'International Shipping Warning Message')->setRows(20)
            ),
            new Tab(
                'DomesticCarriers',
                GridField::create(
                    'DomesticShippingCarrier',
                    'Domestic Shipping Carrier',
                    DomesticShippingCarrier::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
                )),
            new Tab(
                'InternationalCarriers',
                GridField::create(
                    'InternationalShippingCarrier',
                    'International Shipping Carrier',
                    InternationalShippingCarrier::get(),
                    GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            ),
            new Tab(
                'RegionsAndZones',
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
            ),
            new Tab(
                'AllowedCountries',
                CheckboxSetField::create('AllowedCountries', 'Allowed Ordering and Shipping Countries',
                    ShopConfig::config()->iso_3166_country_codes)
            )
        );

        $fields->addFieldToTab('Root', $shippingTab);
    }

    public static function current_config()
    {
        if (class_exists('Fluent') && class_exists('ShopStore')) {
            $locale = Fluent::current_locale();
            $country = array_search($locale, ShopStore::config()->country_locale_mapping);
            $store = ShopStore::get()->filter(array('Country' => $country))->first();
            if ($store->exists()) {
                return $store;
            }
        }

        return SiteConfig::current_site_config();
    }

    /**
     * Carried over from SilverShop's Shop Config
     * @param bool|false $prefixisocode
     * @return array|scalar
     */
    public function getCountriesList($prefixisocode = false)
    {
        $countries = ShopConfig::config()->iso_3166_country_codes;
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