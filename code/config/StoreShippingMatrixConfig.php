<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 15/04/2016
 * Time: 11:15 AM
 */
class StoreShippingMatrixConfig extends ShippingMatrixConfig
{
    private static $singular_name = 'Store Shipping setting';
    private static $plural_name = 'Store Shipping settings';

    private static $db = array(
        'Title' => 'Varchar(200)',
        'ShopStoreID' => 'Int',
    );

    private static $summary_fields = array(
        'Title' => 'Title'
    );

    public function canDelete($member = null)
    {
        return Director::isLive() ? false : true;
    }

    public function canCreate($member = null)
    {
        return false;
    }

    public static function current()
    {
        $store = ShopConfig::current();
        if ($store && $store->ClassName == 'ShopStore') {
            return StoreShippingMatrixConfig::get()->filter(array('ShopStoreID' => $store->ID))->first();
        }

        return DataObject::get_one('ShippingMatrixConfig');
    }
}