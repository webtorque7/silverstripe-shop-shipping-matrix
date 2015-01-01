<?php

/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 13:58
 */
class ShippingMatrixModifier extends ShippingModifier
{
	private static $db = array(
		'ShippingTitle' => 'Varchar(255)',
		'IsDomestic' => 'Boolean',
		'IsPickup' => 'Boolean',
		'Country' => 'Varchar(3)',
		'Region' => 'Varchar(50)'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier'
	);

	private static $singular_name = 'Shipping';

	/**
	 * Calculates value to store, based on incoming running total.
	 * @param float $incoming the incoming running total.
	 */
	public function value($incoming) {

		$this->calculate($this->Order());

		return $this->Amount;
	}

	public function calculate($order) {

		$shippingCharge = 0;
		$this->loadCountry();
		$this->loadRegion();

		$config = SiteConfig::current_site_config();

		if (!$this->Country) return;

		if ($this->Country == $config->DomesticCountry) {
			$this->IsDomestic = true;

			$extraCosts = 0;
			if ($extras = DomesticShippingExtra::get()) {
				foreach ($extras as $extra) {
					$extraCosts += $extra->Amount;
				}
			}

			if ($this->Region) {
				$region = DomesticShippingRegion::get()->filter('Region:PartialMatch', $this->Region)->first();
				$shippingCharge = $region->Amount + $extraCosts;
			}
		}
		else {
			$this->IsDomestic = false;
			$items = $this->Order()->Items();
			$shippingCharge = InternationalShippingCarrier::process($items, $this->Country);
		}

		$this->Amount = $shippingCharge;
	}

	public function loadCountry() {
		$country = '';

		if ($this->Country && $this->Order()->Status !== 'Cart') $country = $this->Country;
		else if ($this->Order()->ShippingAddress()->exists()) $country = $this->Order()->ShippingAddress()->Country;
		else if ($this->Order()->Member()->exists()) $country = $this->Order()->Member()->Country;

		return $this->Country = $country;
	}

	public function loadRegion() {
		$region = '';

		if ($this->Region && $this->Order()->Status !== 'Cart') $region = $this->Region;
		else if ($this->Order()->ShippingAddress()->exists()) $region = $this->Order()->ShippingAddress()->Region;
		else if (!empty($this->config()->defaults['Region'])) $region = $this->config()->defaults['Region'];

		return $this->Region = $region;
	}

	public function ShowInTable() {
		return $this->Amount() > 0;
	}

	public function TableTitle() {
		if ($this->ShippingTitle) {
			return $this->ShippingTitle;
		} else {
			//add region or country to title
			$extra = '';
			if ($this->IsDomestic && ($region = $this->loadRegion())) {
				$extra = ' (' . $region . ')';
			}
			else if ($country = $this->loadCountry()) {
				$extra = ' (' . ShopConfig::countryCode2name($country) . ')';
			}
			return parent::TableTitle() . $extra;
		}
	}

	public static function get_shipping_countries() {
		$cache = SS_Cache::factory('Countries', 'Output', array('automatic_serialization' => true));

		if (!($countries = $cache->load('deliveryCountry'))) {
			$defultZone = InternationalShippingZone::get()->filter('DefaultZone', true)->first();
			if (!empty($defultZone)) {
				$countries = ShopConfig::$iso_3166_countryCodes;
			} else {
				$countries = array();
				$zones = InternationalShippingZone::get();
				foreach ($zones as $zone) {
					array_push($countries, $zone->ShippingCountries);
				}
				asort($countries);
			}
			$cache->save($countries);
		}
		return $countries;


	}
}
