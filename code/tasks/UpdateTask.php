<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 19/05/2016
 * Time: 2:09 PM
 */
class UpdateShippingRecordTask extends BuildTask
{
    public function run($request)
    {
        foreach (DomesticShippingRegion::get() as $region) {
            $carrier = DB::query("SELECT DomesticShippingCarrierID from DomesticShippingCarrier_DomesticShippingRegions WHERE DomesticShippingRegionID = {$region->ID}")->value();

            if ($carrier) {
                $region->DomesticShippingCarrierID = $carrier;

                //delete old records
                DB::query("DELETE FROM DomesticShippingCarrier_DomesticShippingRegions WHERE DomesticShippingRegionID = {$region->ID}");
            }
        }
    }
}