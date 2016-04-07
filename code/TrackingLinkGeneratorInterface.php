<?php

/**
 * Created by PhpStorm.
 * User: Conrad
 * Date: 5/04/2016
 * Time: 4:55 PM
 */
interface TrackingLinkGeneratorInterface
{
    /**
     * @param $trackingURL
     * @param Order $order
     * @return mixed
     */
    public function getTrackingLink($trackingURL, Order $order);

    /**
     * @param $order Order
     * @return string
     */
    public static function generate_tracking_number(Order $order);
}