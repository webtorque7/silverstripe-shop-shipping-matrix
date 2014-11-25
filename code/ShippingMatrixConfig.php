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
		'InternationalShippingWarningMessage' => 'HTMLText'
	);

	public function updateCMSFields(FieldList $fields) {
		$countries = SiteConfig::current_site_config()->getCountriesList();
		$fields->addFieldToTab('Root.Shop.ShopTabs.Main',
			DropdownField::create("DomesticCountry",_t('ShippingMatrix.DOMESTICCOUNTRY','Domestic Country'), $countries, 'NZ')
		);
		$shippingTab = new TabSet("ShippingTabs",
			$main = new Tab("Main",
				TextField::create('FreeShippingQuantity', 'Free Shipping Quantity'),
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