<?php

/**
 * Add Carrier relations to Order
 */
class ShippingMatrixOrderExtension extends DataExtension
{
    private static $many_many = array(
        'DomesticCarriers' => 'DomesticShippingCarrier',
        'InternationalCarriers' => 'InternationalShippingCarrier'
    );

}