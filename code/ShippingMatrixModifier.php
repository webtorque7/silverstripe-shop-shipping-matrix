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
		'ShippingType' => 'Enum("domestic,international,pickup,free")',
		'DefaultCountry' => 'Varchar(10)'
	);

	public function populate($data = null) {
		$this->Amount = $this->value();
//		if (!empty($data)) {
//			$shippingCharge = 0;
//			if ($deliveryCountry = $data['DeliveryCountry']) {
//				$this->DefaultCountry = $deliveryCountry;
//			}
//			//TODO currently region field doesn't exist on checkout page.
//			$deliveryRegion = null;
//			if ($shippingOption = $data['ShippingOptions']) {
//				$this->ShippingType = $shippingOption;
//
//				switch ($shippingOption) {
//					case "domestic":
//						$items = $this->Order()->Items();
//						$response = DomesticShippingCarrier::process($items, $deliveryRegion);
//						$shippingCharge = $response['Amount'];
//						if (!empty($response['Carriers'])) foreach ($response['Carriers'] as $carrier) {
//							$this->Order()->DomesticCarriers()->removeAll();
//							$this->Order()->DomesticCarriers()->add($carrier);
//						}
//						break;
//					case "international":
//						$items = $this->Order()->Items();
//						$response = InternationalShippingCarrier::process($items, $deliveryCountry);
//						$shippingCharge = $response['Amount'];
//						if (!empty($response['Carriers'])) foreach ($response['Carriers'] as $carrier) {
//							$this->Order()->InternationalCarriers()->removeAll();
//							$this->Order()->InternationalCarriers()->add($carrier);
//						}
//						break;
//				}
//			}
//			$this->Amount = $shippingCharge;
//			$this->write();
//		} else {
//			$this->ShippingType = null;
//			$this->Amount = 0;
//			$this->write();
//		}
	}

        //This needs to go through value even if order isnt in cart anymore.
        public function modify($subtotal, $forcecalculation = false) {
            $order = $this->Order();
            $value = $this->value($subtotal);
            switch($this->Type){
                case "Chargable":
                    $subtotal += $value;
                    break;
                case "Deductable":
                    $subtotal -= $value;
                    break;
                case "Ignored":
                    break;
            }
            $value = round($value, Order::config()->rounding_precision);
            $this->Amount = $value;
            return $subtotal;
        }

	public function ShowInTable() {
		return true;
	}

	public function value($subtotal = null) {
		$this->ShippingType = $this->Order()->OrderShippingType;
		$shippingCharge = 0;

		if ($this->ShippingType !== 'pickup') {
			$address = $this->Order()->ShippingAddress();

			if ($address->exists()) {
				$items = $this->Order()->Items();

				if ($address->Country === SiteConfig::current_site_config()->DomesticCountry) {
					$response = DomesticShippingCarrier::process($items, $address->Region);
					$shippingCharge = $response['Amount'];

					if (!empty($response['Carriers'])) {
						foreach ($response['Carriers'] as $carrier) {
							$this->Order()->DomesticCarriers()->removeAll();
							$this->Order()->DomesticCarriers()->add($carrier);
						}
					}
				} else {
					$response = InternationalShippingCarrier::process($items, $address->Country);

					$shippingCharge = $response['Amount'];

					if (!empty($response['Carriers'])) {
						foreach ($response['Carriers'] as $carrier) {
							$this->Order()->InternationalCarriers()->removeAll($carrier);
							$this->Order()->InternationalCarriers()->add($carrier);
						}
					}
				}
			}
		}

		return $shippingCharge;
	}

	public function TableTitle() {
//		return 'Shipping (' . $this->ShippingType . ')';
		return 'Shipping';
	}

	public static function get_shipping_countries() {
		$cache = SS_Cache::factory('Countries', 'Output', array('automatic_serialization' => true));

		if (!($countries = $cache->load('deliveryCountry'))) {
			$defultZone = InternationalShippingZone::get()->filter('DefaultZone', true)->first();
			if (!empty($defultZone)) {
				SiteConfig::current_site_config()->getCountriesList();
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

	public function Order() {
		if (!$this->OrderID) {
			return ShoppingCart::curr();
		}
		return $this->getComponent('Order');
	}
}
