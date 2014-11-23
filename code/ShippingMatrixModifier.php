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
		'IsPickup' => 'Boolean'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier'
	);

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
		$deliveryCountry = $order->ShippingAddress->Country;
		$deliveryRegion = $order->ShippingAddress->Region;

		$config = SiteConfig::current_site_config();

		if (!$deliveryCountry) return;

		if ($deliveryCountry == $config->DomesticCountry) {
			$this->IsDomestic = true;

			$extraCosts = 0;
			if ($extras = DomesticShippingExtra::get()) {
				foreach ($extras as $extra) {
					$extraCosts += $extra->Amount;
				}
			}

			if ($deliveryRegion) {
				$region = DomesticShippingRegion::get()->filter('Region:PartialMatch', $deliveryRegion)->first();
				$shippingCharge = $region->Amount + $extraCosts;
			}
		}
		else {
			$this->IsDomestic = false;
			$items = $this->Order()->Items();
			$shippingCharge = InternationalShippingCarrier::process($items, $deliveryCountry);
		}

		$this->Amount = $shippingCharge;
	}

	public function ShowInTable() {
		return $this->Amount() > 0;
	}

	public function TableTitle() {
		if ($this->ShippingTitle) {
			return $this->ShippingTitle;
		} else {
			return 'Shipping';
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
