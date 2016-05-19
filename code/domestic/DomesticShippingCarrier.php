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

    private static $has_many = array(
        'DomesticShippingRegions' => 'DomesticShippingRegion'
    );

    private static $many_many = array(
        'DomesticShippingExtras' => 'DomesticShippingExtra'
    );

    public function canView($member = null)
    {
        return true;
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
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
            DropdownField::create('TrackerType', 'Tracker Type', $trackers),
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

        return $fields;
    }

    public function getTrackingLink($order)
    {
        return $this->TrackerType ? singleton($this->TrackerType)->getTrackingLink($this->TrackingURL, $order) : false;
    }

    public static function process($items, $region = null)
    {
        $extraCosts = 0;
        $shippingCharge = 0;
        $carriers = array();

        if ($extras = DomesticShippingExtra::get()) {
            foreach ($extras as $extra) {
                $extraCosts += $extra->Amount;
            }
        }

        if ($region) {
            $shippingRegion = DomesticShippingRegion::get()->filter('Region:PartialMatch', $region)->first();

            if ($shippingRegion) {
                $shippingCharge = $shippingRegion->Amount + $extraCosts;
                $carriers[] = $shippingRegion->DomesticShippingCarrier();
            }
        }

        singleton('DomesticShippingCarrier')->extend('updateShippingCharge', $shippingCharge, $carriers, $items);
        return array('Amount' => $shippingCharge, 'Carriers' => $carriers);
    }
}