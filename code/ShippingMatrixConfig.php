<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */

class ShippingMatrixConfig extends DataExtension{
	private static $db = array(
		'DomesticNorthIslandCharge' => 'Currency',
		'DomesticSouthIslandCharge' => 'Currency',
		'DomesticLocalCharge' => 'Currency',
		'LocalCity' => 'Varchar(100)',
		'ShippingMargin' => 'Percentage',
		'ShippingMessage' => 'HTMLText',
		'AllowPickup' => 'Boolean',
		'DomesticCountry' => 'Varchar',
		'FreeShippingQuantity' => 'Int'
	);

	public function updateCMSFields(FieldList $fields) {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$shippingTab = new TabSet("ShippingTabs",
			$maintab = new Tab("Main",
				TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
				CheckboxField::create('AllowPickup', 'Allow Pickup'),
				HtmlEditorField::create('ShippingMessage', 'Shipping Message')->setRows(20)
			),
			$internationalShippingCarrier = new Tab("InternationalShipping",
				GridField::create(
					'ShippingZone',
					'Shipping Zone',
					ShippingZone::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				),
				GridField::create(
					'InternationalShippingCarrier',
					'International Shipping Carrier',
					InternationalShippingCarrier::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)
			),
			$shippingZone = new Tab("DomesticShipping",
				DropdownField::create("DomesticCountry",_t('Address.COUNTRY','Dometstic Country'), $countries, 'NZ'),
				GridField::create(
					'DomesticShippingRegion',
					'Domestic Shipping Region',
					DomesticShippingRegion::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				),
				GridField::create(
					'DomesticShippingExtra',
					'Domestic Shipping Extra',
					DomesticShippingExtra::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)
			),
			$itemWeights = new Tab("ShippingItemUnit",
				GridField::create(
					'ShippingItemUnit',
					'Shipping Item Unit',
					ShippingItemUnit::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)
			)
//		        $shippingZone = new Tab("ParcelType",
//				GridField::create(
//					'ParcelType',
//					'Parcel Type',
//					ParcelType::get(),
//					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
//				)
//			)
		);

		$fields->addFieldToTab('Root.Shop.ShopTabs.ShippingMatrix', $shippingTab);
	}
} 