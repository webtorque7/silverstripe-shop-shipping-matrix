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
		$this->loadCountry();
		$this->loadRegion();

		$this->IsDomestic = self::is_domestic($this->Country);
		$this->Amount = self::calculate($this->Region, $this->Country, $this->Order()->Items());

		return $this->Amount;
	}

	public static function calculate($region, $country, $items) {
		$shippingCharge = 0;

		if (!$country) return;

		if (self::is_domestic($country)) {

			$extraCosts = 0;
			if ($extras = DomesticShippingExtra::get()) {
				foreach ($extras as $extra) {
					$extraCosts += $extra->Amount;
				}
			}

			if ($region) {
				$shippingRegion = DomesticShippingRegion::get()->filter('Region:PartialMatch', $region)->first();
				$shippingCharge = $shippingRegion->Amount + $extraCosts;
			}
		}
		else {
			$shippingCharge = InternationalShippingCarrier::process($items, $country);
		}

		return $shippingCharge;
	}

	public function loadCountry() {
		$country = '';

		if ($this->Country && $this->Order()->Status !== 'Cart') $country = $this->Country;
		else if ($this->Order()->ShippingAddress()->exists()) $country = $this->Order()->ShippingAddress()->Country;
		else if ($this->Order()->Member()->exists()) $country = $this->Order()->Member()->Country;

		if (!$country) {
			$locale = i18n::get_locale();
			$zLocale = new Zend_Locale($locale);
			$country = $zLocale->getRegion();
		}

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
		return true;
	}

	public function TableTitle() {
		if ($this->ShippingTitle) {
			return $this->ShippingTitle;
		}

		return self::get_table_title($this->IsDomestic, $this->loadCountry(), $this->loadRegion());
	}

	public static function get_table_title($isDomestic, $country, $region = null) {
		//add region or country to title
		$extra = '';
		if ($isDomestic && $region) {
			$extra = ' (' . $region . ')';
		}
		else if ($country) {
			$extra = ' (' . _t('Countries.' . $country, ShopConfig::countryCode2name($country)) . ')';
		}

		return singleton('ShippingMatrixModifier')->i18n_singular_name() . $extra;
	}

	public static function is_domestic($country) {
		return SiteConfig::current_site_config()->DomesticCountry === $country;
	}

	public static function get_shipping_countries() {
		$cache = SS_Cache::factory('Countries', 'Output', array('automatic_serialization' => true));

		if (!($countries = $cache->load('deliveryCountry'))) {
			$defaultZone = InternationalShippingZone::get()->filter('DefaultZone', true)->first();
			if (!empty($defaultZone)) {
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
