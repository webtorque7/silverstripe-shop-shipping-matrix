<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 09/11/13
 * Time: 04:23
 */

class ParcelType extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(200)',
		'MinimumBooks' => 'Int',
		'MaximumBooks' => 'Int',
		'Sort' => 'Int'
	);

	private static $summary_fields = array(
		'Title' => 'Title',
		'MinimumBooks' => 'Minimum Books',
		'MaximumBooks' => 'Maximum Books'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('Title', 'Parcel Description'),
			TextField::create('MaximumBooks', 'Maximum Books')
		));

		$fields->removeByName('Sort');

		return $fields;
	}
} 