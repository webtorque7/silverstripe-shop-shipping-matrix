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
		'MinimumWeigth' => 'Decimal',
		'Sort' => 'Int'
	);

	private static $belongs_to = array(
		'ShippingMatrixModifier' => 'ShippingMatrixModifier'
	);

	private static $has_many = array(
		'CarrierShippingZones' => 'CarrierShippingZone'
	);

	private static $many_many = array(
		'InternationalShippingWeightRanges' => 'InternationalShippingWeightRange',
	);

	public function getWeightRange($weight){
		return InternationalShippingWeightRange::get()->filter(array(
				'MinWeight:GreaterThanEqual' => $weight,
				'MaxWeight:LessThanEqual' => $weight
			)
		)->first();
	}

	public function getInternationalCourierTitle(){
		return 'Courier Shipping ('.$this->Title.')';
	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->removeByName('Sort');
//		$fields->removeByName('CarrierShippingZones');
//		$fields->removeByName('InternationalShippingWeightRanges');

//		$config = GridFieldConfig::create()
//			->addComponent(new GridFieldFilterHeader())
//			->addComponent(new GridFieldButtonRow('before'))
//			->addComponent(new GridFieldDataColumns())
//			->addComponent($existingSearch = new GridFieldAddExistingSearchButton('toolbar-header-right'))
//			->addComponent(new GridFieldToolbarHeader())
//			->addComponent(new GridFieldTitleHeader())
//			->addComponent(new GridFieldDeleteAction(true));

//		if($this->record['ID'] <> 0){
//			//$fields->addFieldToTab('Root.CarrierShippingZones', $grid = GridField::create('CarrierShippingZones', 'CarrierShippingZones', $this->CarrierShippingZones()));
//			$fields->addFieldToTab('Root.InternationalShippingWeightRanges', $grid = GridField::create('InternationalShippingWeightRanges', 'InternationalShippingWeightRanges', $this->InternationalShippingWeightRanges(), $config));
//		}

		return $fields;
	}

	public function onBeforeWrite(){
		parent::onBeforeWrite();

		if($this->ID){
			if($this->MinimumWeigth){
				$do = $this->getWeightRange($this->MinimumWeight);
				if($do){
					$this->InternationalShippingWeightRanges()->add($do);
				}
			}
		}
	}
}