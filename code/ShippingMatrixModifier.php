<?php
/**
 * Created by PhpStorm.
 * User: davis
 * Date: 06/11/13
 * Time: 13:58
 */

class ShippingMatrixModifier extends ShippingModifier{
	private static $db = array(
		'IsInternational' => 'Boolean',
		'IsDomestic' => 'Boolean',
		'IsPickup' => 'Boolean',
		'Weight' => 'Decimal',
		'ShippingMargin' => 'Percentage',
		'ShippingTitle' => 'Varchar(255)',
		'ShippingAddition' => 'Text'
	);

	private static $has_one = array(
		'InternationalShippingCarrier' => 'InternationalShippingCarrier'
	);

	private static $many_many = array(
		'DomesticShippingExtras' => 'DomesticShippingExtra'
	);

	private static $default_sort = 'Created';


	public function getLocalCharge($totalWeight, $shippingSettings){
		return $shippingSettings->DomesticLocalCharge ? $shippingSettings->DomesticLocalCharge : 0;
	}

	public function getDomesticShippingExtra($domesticShippingExtra){
		$return = false;
		if(!empty($domesticShippingExtra)){
			$domesticShippingExtraIDS = array();
			foreach($domesticShippingExtra as $dose){
				array_push($domesticShippingExtraIDS, $dose);
			}
			$return = DomesticShippingExtra::get()->filter(array('ID' => $domesticShippingExtraIDS));
		}
		return $return;
	}

	public function getCurrentShippingZone($deliveryCountry){
		$shippingZones = ShippingZone::get();
		foreach($shippingZones as $shippingZone){
			$countryArray = explode(",",$shippingZone);
			if(in_array($deliveryCountry, $countryArray))
				break;
		}
		return $shippingZone;
	}

	public function getWeightBasedCharge($totalItemsWeight, $deliveryCountry, $internationalShippingCarrier){
		$shippingZone = $this->getCurrentShippingZone($deliveryCountry);
		$internationalShippingWeightRangeDO = InternationalShippingWeightRange::get()->filter(array(
			'MinWeight:LessThanEqual' => $totalItemsWeight,
			'MaxWeight:GreaterThanEqual' => $totalItemsWeight
		));
		$iswrID = array();
		foreach($internationalShippingWeightRangeDO as $isrwDO){
			array_push($iswrID, $isrwDO->ID);
		}
		$carrierShippingZone = CarrierShippingZone::get()->filter(array(
			'ShippingZoneID' => $shippingZone->ID,
			'InternationalShippingCarrierID' => $internationalShippingCarrier,
			'InternationalShippingWeightRangeID' => $iswrID
		));
		$cszDO = $carrierShippingZone->first();
		if($cszDO){
			$totalCharge = ($cszDO->PerKGAmount * $totalItemsWeight) + $cszDO->PerItemAmount;
		}
		else {
			$totalCharge = 0;
		}
		return $totalCharge;
	}

	public function getCalculateShippingMargin($totalCharge, $shippingMargin){
		return ($totalCharge * $shippingMargin);
	}

	/*
	 * Determine what Local
	 * Todo
	 */
	public function notLocal($localcity){
		$shippingSettings = SiteConfig::current_site_config();
		return (strtolower($localcity) != strtolower($shippingSettings->LocalCity));
	}

	public function calculatePackageWeight(){}

	public function calculateCharge(){
		return null;
	}

	public function populate($data, $order){
		$shippingSettings = SiteConfig::current_site_config();
		$currency = $shippingSettings->Currency;

		/*
		 * For novelty products
		 */
		$nonWineProductWeight = 0;
		foreach ($order->Items() as $item) {
			if ($item->product()->ClassName != 'WineProduct'){
				$nonWineProductWeight += $item->product()->Weight;
			}
		}
		//Debug::dump($nonWineProductWeight);exit;

		/*
		 * For Wine products
		 */
		$wineBottles = $order->getNumberOfWines();
		$bottlesPerCrate = ShippingItemUnit::get()->first()->NumberOfItems;
		$wineCrates = 0;
		$totalCharge = 0;

		for($i=0; $i<$wineBottles; $i+=$bottlesPerCrate){
			$wineCrates++;
		}

		$shippingOption = $data['ShippingOptions'];
		if($shippingOption && $shippingOption != 'free'){
			if($shippingOption == 'domestic'){
				$this->IsDomestic = true;
				$region = DomesticShippingRegion::get();
				if($region->count() == 1){
					$shippingCharge = $region->first()->Amount;
					$totalCharge += ($wineCrates * $shippingCharge);

					//add in the charge for non wine based on weight
					$totalCharge += ($nonWineProductWeight * $shippingCharge);
				}
				else if($region->count() > 1){
					//need to add options for region selection on the delivery form
					//use the selection and compare with the regionID to find the region selected
				}

				// add in domestic shipping extra costs
				$this->DomesticShippingExtras()->removeAll();
				if(isset($data['DomesticShippingExtra'])){
					$DomesticShippingExtraDOS = $this->getDomesticShippingExtra($data['DomesticShippingExtra']);
					$separator = ' - ';
					foreach($DomesticShippingExtraDOS as $DomesticShippingExtraDO){
						$totalCharge += $DomesticShippingExtraDO->Amount;
						$this->DomesticShippingExtras()->add($DomesticShippingExtraDO);
						$this->ShippingTitle .= $separator . $DomesticShippingExtraDO->Title;
						$separator = ', ';
					}
					$this->ShippingTitle = nl2br($this->ShippingTitle);
				}

			}
			else if($shippingOption == 'international'){
				$this->IsInternational = true;
				$international = CarrierShippingZone::get();
				if($international->count() == 1){
					$shippingCharge = $international->first()->PerKGAmount;
					$totalCharge += ($wineCrates * $shippingCharge);

					//add in the charge for non wine based on weight
					$totalCharge += ($nonWineProductWeight * $shippingCharge);
				}
				else if($international->count() > 1){

				}
			}
		}

		$totalCharge += $this->getCalculateShippingMargin($totalCharge, $shippingSettings->ShippingMargin);
		$this->Amount = $totalCharge;
		Debug::dump($this->Amount);exit;
		$this->write();
	}

	public function value($subtotal = null)
	{
		return $this->Amount();
	}

	public function ShowInTable() {
		return $this->Amount() > 0;
	}

	public function TableTitle()
	{
		if ($this->ShippingTitle) {
			return $this->ShippingTitle;
		} else {
			return 'Courier Shipping';
		}
	}
}
