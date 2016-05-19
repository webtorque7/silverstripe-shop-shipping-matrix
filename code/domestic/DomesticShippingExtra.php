<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 13:53
 */
class DomesticShippingExtra extends DataObject
{
    private static $db = array(
        'Title' => 'Varchar(100)',
        'Sort' => 'Int',
        'Amount' => 'Currency'
    );

    private static $belongs_many_many = array(
        'DomesticShippingCarriers' => 'DomesticShippingCarrier'
    );

    private static $summary_fields = array(
        'Title' => 'Title',
        'Amount' => 'Amount'
    );

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(array(
            'Sort',
            'DomesticShippingCarriers'
        ));

        $fields->addFieldsToTab('Root.Main', array(
            TextField::create('Title', 'Title'),
            TextField::create('Amount', 'Amount'),
        ));

        return $fields;
    }
} 