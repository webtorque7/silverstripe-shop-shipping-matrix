<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/04/2016
 * Time: 10:48 AM
 */
class ShippingMatrixAdmin extends ModelAdmin
{
    public static $managed_models = array('ShippingMatrix', 'StoreShippingConfig');
    public static $url_segment = 'shipping-matrix-admin';
    public static $menu_title = 'Shipping';
}