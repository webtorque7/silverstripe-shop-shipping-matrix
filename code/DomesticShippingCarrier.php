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
		'Sort' => 'Int'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $many_many = array(
		'DomesticShippingRegions' => 'DomesticShippingRegion',
		'DomesticShippingExtras' => 'DomesticShippingExtra'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->removeByName('Sort');
		$fields->removeByName('DomesticShippingRegions');
		$fields->removeByName('DomesticShippingExtras');

		$shippingRegionGrid = GridField::create(
			'DomesticShippingRegions',
			'Domestic Shipping Regions',
			$this->DomesticShippingRegions(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$shippingExtraGrid = GridField::create(
			'DomesticShippingExtras',
			'Domesic Shipping Extras',
			$this->DomesticShippingExtras(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$fields->addFieldsToTab('Root.Main', array($shippingRegionGrid, $shippingExtraGrid));
		return $fields;
	}
}