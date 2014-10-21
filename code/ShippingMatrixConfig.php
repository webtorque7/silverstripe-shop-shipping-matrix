<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 15:41
 */

class ShippingMatrixConfig extends DataExtension{
	private static $db = array(
		'DomesticCountry' => 'Varchar',
		'LocalCity' => 'Varchar(100)',
		'ShippingMargin' => 'Percentage',
		'ShippingMessage' => 'HTMLText',
		'AllowPickup' => 'Boolean',
		'FreeShippingQuantity' => 'Int',
		'InternationalShippingWarningMessage' => 'HTMLText',

		//shipping option text
		'FreeShippingText' => 'Varchar(200)',
		'DomesticShippingText' => 'Varchar(200)',
		'InternationalShippingText' => 'HTMLText',
		'PickupText' => 'Varchar(200)',

		//PBT courier
		'PBTAccountNumber' => 'Varchar(20)',
		'PBTCourierNumber' => 'Varchar(20)'
	);

	public function updateCMSFields(FieldList $fields) {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$fields->addFieldToTab('Root.Shop.ShopTabs.Main',
			DropdownField::create("DomesticCountry",_t('Address.COUNTRY','Domestic Country'), $countries, 'NZ')
		);
		$shippingTab = new TabSet("ShippingTabs",
			$main = new Tab("Main",
				TextField::create('PBTAccountNumber', 'PBT Account Number'),
				TextField::create('PBTCourierNumber', 'PBT Courier Account Number'),
				TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
				TextField::create('FreeShippingText', 'Free Shipping Text'),
				TextField::create('DomesticShippingText', 'Domestic Shipping Text'),
				TextField::create('PickupText', 'Pickup Text'),
                                HtmlEditorField::create('InternationalShippingText', 'International Shipping Text'),
                                CheckboxField::create('AllowPickup', 'Allow Pickup'),
				HtmlEditorField::create('ShippingMessage', 'Shipping Message')->setRows(20),
				HtmlEditorField::create('InternationalShippingWarningMessage', 'International Shipping Warning Message')->setRows(20)
			),
			$internationalCarriers = new Tab("InternationalCarriers",
				GridField::create(
					'InternationalShippingCarrier',
					'International Shipping Carrier',
					InternationalShippingCarrier::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)
			),
			$domesticCarriers = new Tab("DomesticCarriers",
				GridField::create(
					'DomesticShippingCarrier',
					'Domestic Shipping Carrier',
					DomesticShippingCarrier::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)
			),
			$regionsAndZones = new Tab("RegionsAndZones",
				GridField::create(
					'DomesticShippingRegion',
					'Domestic Shipping Region',
					DomesticShippingRegion::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				),
				GridField::create(
					'InternationalShippingZone',
					'International Shipping Zone',
					InternationalShippingZone::get(),
					GridFieldConfig_RecordEditor::create()->addComponent(GridFieldOrderableRows::create('Sort'))
				)

			)
		);
		$fields->addFieldToTab('Root.Shop.ShopTabs.ShippingMatrix', $shippingTab);
	}
} 