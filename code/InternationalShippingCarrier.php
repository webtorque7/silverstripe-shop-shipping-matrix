<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 14:06
 */

class InternationalShippingCarrier extends DataObject{
	private static $db = array(
		'Title' => 'Varchar(100)',
		'Sort' => 'Int',
		'UnitType' => 'Varchar(100)'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $many_many = array(
		'InternationalShippingZones' => 'InternationalShippingZone',
		'ShippingWeightRanges' => 'ShippingWeightRange',
		'ShippingQuantityRanges' => 'ShippingQuantityRange'
	);

	public function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Main', new CheckboxSetField('UnitType', 'Unit Type', array('Weight','Quantity')));
		$fields->removeByName('Sort');
		$fields->removeByName('InternationalShippingZones');
		$fields->removeByName('ShippingWeightRanges');
		$fields->removeByName('ShippingQuantityRanges');

		$shippingZoneGrid = GridField::create(
			'InternationalShippingZones',
			'International Shipping Zones',
			$this->InternationalShippingZones(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$weightRangeGrid = GridField::create(
			'ShippingWeightRanges',
			'Shipping Weight Ranges',
			$this->ShippingWeightRanges(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$quantityRangeGrid = GridField::create(
			'ShippingQuantityRanges',
			'Shipping Quantity Ranges',
			$this->ShippingQuantityRanges(),
			GridFieldConfig_RelationEditor::create()
				->addComponent(GridFieldOrderableRows::create('Sort')));

		$fields->addFieldsToTab('Root.Main', array($shippingZoneGrid, $weightRangeGrid, $quantityRangeGrid));
		return $fields;
	}
}