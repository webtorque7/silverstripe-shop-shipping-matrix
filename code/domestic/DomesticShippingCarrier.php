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
        'ShippingMatrix' => 'StoreWarehouse'
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
            'ShippingMatrixID',
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
                    $regionConfig = GridFieldConfig_RecordViewer::create()
                ),
                GridField::create(
                    'DomesticShippingExtras',
                    'Domestic Shipping Extras',
                    $this->DomesticShippingExtras(),
                    $extraConfig = GridFieldConfig_RelationEditor::create()
                )
            ));
            $regionConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->addComponent(new GridFieldAddExistingAutocompleter)
                ->addComponent(new GridFieldDeleteAction(true));

            $extraConfig
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->removeComponentsByType('GridFieldAddExistingAutocompleter')
                ->removeComponentsByType('GridFieldDeleteAction')
                ->addComponent(new GridFieldDeleteAction(false));

        } else {
            $fields->addFieldToTab('Root.Main', LiteralField::create('SavingTip', '<p class="message">Please save before adding shipping regions and extra costs.</p>'));
        }

        return $fields;
    }

    public function getTrackingLink($order)
    {
        return $this->TrackerType ? singleton($this->TrackerType)->getTrackingLink($order) : false;
    }

    public static function process($order, $region = null, $country = null)
    {
        $extraCharge = 0;
        $carriers = array();
        $items = $order->Items();

        $totalQuantity = 0;
        foreach ($items as $item) {
            $totalQuantity += $item->Quantity;
        }

        // turn off free shipping check from checkout page extension and conduct the check here because some regions are excluded from free shipping.
        $wineQuantity = $order->getNumberOfWines();
        $freeShippingQuantity = ShippingMatrixConfig::current($country)->FreeShippingQuantity;
        if($freeShippingQuantity > 0 && $wineQuantity >= $freeShippingQuantity){
            $excludeFreeShipping = DomesticShippingRegion::exclude_free_shipping($region);
            if(!$excludeFreeShipping){
                return array('Amount' => 0, 'Carriers' => '');
            }
        }

        $shippingRegion = DomesticShippingRegion::get_shipping_region($region, $country, $totalQuantity);
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