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
		'Country' => 'Varchar(3)',
		'Region' => 'Varchar(50)'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier'
	);

	private static $singular_name = 'Shipping';

	private static $last_error = null;

	/**
	 * Calculates value to store, based on incoming running total.
	 * @param float $incoming the incoming running total.
	 */
	public function value($incoming) {
		$this->loadCountry();
		$this->loadRegion();

		$applyShipping = $this->extend('preUpdateValueCheck');
		if($applyShipping == true){
			$this->Amount = self::calculate($this->Region, $this->Country, $this->Order()->Items(), $this->Order());
		}

		$this->extend('updateValue');
		return $this->Amount;
	}

	public static function calculate($region, $country, $items, Order $order = null) {
		$shippingCharge = 0;

		if (!$country) return;

		try {
			if (self::is_domestic($country)) {
				$info = DomesticShippingCarrier::process($items, $region);
				$shippingCharge = $info['Amount'];
				if ($order) {
					$order->DomesticCarriers()->removeAll();
					$order->DomesticCarriers()->addMany($info['Carriers']);
				}
			} else {
				$info = InternationalShippingCarrier::process($items, $country);
				$shippingCharge = $info['Amount'];
				if ($order) {
					$order->InternationalCarriers()->removeAll();
					$order->InternationalCarriers()->addMany($info['Carriers']);
				}
			}
		} catch (ShippingMatrixException $e) {
			self::$last_error = $e->getMessage();
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

		$this->IsDomestic = self::is_domestic($country);
		return $this->Country = $country;
	}

	public function loadRegion() {
		$region = '';

		if ($this->Region && $this->Order()->Status !== 'Cart') $region = $this->Region;
		else if ($this->Order()->ShippingAddress()->exists()) $region = $this->Order()->ShippingAddress()->Region;
		else if (!empty($this->config()->defaults['Region'])) $region = $this->config()->defaults['Region'];

		return $this->Region = $region;
	}

	public static function is_domestic($country) {
		$config = ShippingMatrixConfig::current();
		return $config->Country === $country;
	}

	public function TableTitle() {
		if ($this->ShippingTitle) {
			return $this->ShippingTitle;
		}

		return $this->ShippingTitle = self::get_table_title($this->IsDomestic, $this->loadCountry(), $this->loadRegion());
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

	public function modify($subtotal, $forcecalculation = false)
	{
		$subtotal = parent::modify($subtotal, $forcecalculation);

		$this->extend('modify', $subtotal, $forcecalculation);

		return $subtotal;
	}

	public static function get_last_error()
	{
		return self::$last_error;
	}
}
