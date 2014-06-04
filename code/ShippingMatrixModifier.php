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

	public function populate($data, $order) {
		$shippingCharge = 0;
		$deliveryCountry = $data['DeliveryCountry'];
		$deliveryRegion = $data['DeliveryRegion'];

		if ($shippingOption = $data['ShippingOptions']) {
			switch ($shippingOption) {
			case "free-domestic":
				$this->IsDomestic = true;
				break;

			case "domestic":
				$this->IsDomestic = true;
				$extraCosts = 0;
				if ($extras = DomesticShippingExtra::get()) {
					foreach ($extras as $extra) {
						$extraCosts += $extra->Amount;
					}
				}
				if ($deliveryRegion) {
					$region = DomesticShippingRegion::get()->filter('Region', $deliveryRegion)->first();
					$shippingCharge = $region->Amount + $extraCosts;
				}
				//TODO domestic shipping is a flat rate not per quantity or weight for now.
				break;

			case "international":
				$this->IsDomestic = false;
				$items = $this->Order()->Items();
				$shippingCharge = InternationalShippingCarrier::process($items, $deliveryCountry);
				break;
			}
		}
		$this->Amount = $shippingCharge;
		$this->write();
	}

	public function value($subtotal = null) {
		return $this->Amount();
	}

	public function ShowInTable() {
		return $this->Amount() > 0;
	}

	public function TableTitle() {
		if ($this->ShippingTitle) return $this->ShippingTitle;
		else return 'Courier Shipping';
	}
}
