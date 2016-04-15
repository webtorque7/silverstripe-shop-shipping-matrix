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
        'Country' => 'Varchar'
    );

    private static $summary_fields = array(
        'Country' => 'Country'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Country');
        return $fields;
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canCreate($member = null)
    {
        return false;
    }
}