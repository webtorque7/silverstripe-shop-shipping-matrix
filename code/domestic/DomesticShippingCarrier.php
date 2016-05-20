<?php

/**
 * Class DomesticShippingCarrier
 */
class DomesticShippingCarrier extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Sort' => 'Int',
        'TrackerType' => 'Varchar'
    );

    private static $has_one = array(
        'ShippingMatrixConfig' => 'ShippingMatrixConfig'
    );

    private static $has_many = array(
        'DomesticShippingRegions' => 'DomesticShippingRegion'
    );

    private static $many_many = array(
        'DomesticShippingExtras' => 'DomesticShippingExtra'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'ShippingMatrixConfigID',
            'DomesticShippingRegions',
            'DomesticShippingExtras'
        ));

        $trackerClasses = ClassInfo::implementorsOf('TrackingLinkGeneratorInterface');
        $trackers = array();

        if (!empty($trackerClasses)) {
            foreach ($trackerClasses as $tracker) {
                $trackers[$tracker] = Config::inst()->get($tracker, 'name');
            }
        }

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Carrier Name'),
            DropdownField::create('TrackerType', 'Tracker Type', $trackers)
        ));

        if ($this->exists()) {
            $fields->addFieldsToTab('Root.Main', array(
                GridField::create(
                    'DomesticShippingRegions',
                    'Domestic Shipping Regions',
                    $this->DomesticShippingRegions(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                ),
                GridField::create(
                    'DomesticShippingExtras',
                    'Domestic Shipping Extras',
                    $this->DomesticShippingExtras(),
                    GridFieldConfig_RelationEditor::create()
                        ->addComponent(GridFieldOrderableRows::create('Sort'))
                )
            ));
        } else {
            $fields->addFieldToTab('Root.Main', LiteralField::create('SavingTip', '<p class="message">Please save before adding shipping regions and extra costs.</p>'));
        }

        return $fields;
    }

    public function getTrackingLink($order)
    {
        return $this->TrackerType ? singleton($this->TrackerType)->getTrackingLink($this->TrackingURL, $order) : false;
    }

    public static function process($items, $region = null)
    {
        $extraCharge = 0;
        $carriers = array();

        $shippingRegion = DomesticShippingRegion::get_shipping_region($region);
        $unsupportedRegionException = new ShippingMatrixException('Selected region is not supported please contact us to arrange other shipping methods.');

        if (empty($shippingRegion)) {
            throw $unsupportedRegionException;
        }

        $carrier = $shippingRegion->DomesticShippingCarrier();

        if (empty($carrier)) {
            throw $unsupportedRegionException;
        }

        $shippingExtras = $carrier->DomesticShippingExtras();
        foreach ($shippingExtras as $extra) {
            $extraCharge += $extra->Amount;
        }

        $shippingCharge = $shippingRegion->Amount + $extraCharge;
        $carriers[] = $carrier;

        singleton('DomesticShippingCarrier')->extend('UpdateShippingCharge', $shippingCharge, $carriers, $items, $region);
        return array('Amount' => $shippingCharge, 'Carriers' => $carriers);
    }
}