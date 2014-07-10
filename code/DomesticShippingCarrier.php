<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 30/05/14
 * Time: 9:22 AM
 */

class DomesticShippingCarrier extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'TrackingURL' => 'Varchar'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $many_many = array(
		'DomesticShippingRegions' => 'DomesticShippingRegion',
		'DomesticShippingExtras' => 'DomesticShippingExtra'
	);

	private static $has_one = array(
		'TrackingLinkGenerator' => 'TrackingLinkGenerator'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingRegions');
		$fields->removeByName('DomesticShippingExtras');

		$shippingRegionGrid = GridField::create(
			'DomesticShippingRegions',
			'International Shipping Regions',
			$this->DomesticShippingRegions(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$shippingExtraGrid = GridField::create(
			'DomesticShippingExtras',
			'International Shipping Extras',
			$this->DomesticShippingExtras(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$fields->addFieldsToTab('Root.Main', array(
			TextField::create('TrackingURL', 'Tracking URL'),
			$shippingRegionGrid,
			$shippingExtraGrid));
		return $fields;
	}

	public function getTrackingLink($order){
		return $this->TrackingLinkGenerator($this->TrackingURL, $order);
	}
}