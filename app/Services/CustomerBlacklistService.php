<?php


namespace App\Services;

use App\Models\CustomerBlacklist;
use App\Models\OdinOrder;

/**
 * Class CustomerBlacklistService
 * @package App\Services
 */
class CustomerBlacklistService
{
    const ORDER_PAUSE_AMOUNT_USD = 400;
    const ORDER_PAUSE_COUNT = 2;

    /**
     * Checks if order needs a pause
     * @param OdinOrder $order
     * @param bool|null $is_main
     * @return string|null
     */
    public static function getOrderPauseReason(OdinOrder $order, ?bool $is_main = false): ?string
    {
        if ($order->is_paused) {
            return null;
        }

        $reason = null;
        if ($order->total_paid_usd < self::ORDER_PAUSE_AMOUNT_USD) {
            if ($is_main) {
                if (!optional($order->ipqualityscore)['tor']) {
                    // remove spaces, explode and sort willful fields
                    $adrs_chars = mb_str_split(
                        preg_replace('/\s/', '', $order->shipping_street . $order->shipping_building . $order->shipping_apt)
                    );
                    sort($adrs_chars);
                    // create address string
                    $address = implode(' ', array_filter([
                        $order->shipping_zip,
                        $order->shipping_country,
                        $order->shipping_state,
                        $order->shipping_city,
                        implode('', $adrs_chars)
                    ]));
                    $address = preg_replace('/[^\w\s]+/u', '', $address);

                    $cus_blst = CustomerBlacklist::addOne($order->number, [
                        'fingerprint' => $order->fingerprint,
                        'address' => trim(strtolower($address)),
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                        'ip' => $order->ip
                    ]);
                    $reason = self::getPauseReason($cus_blst, $order);
                } else {
                    $reason = 'customer uses Tor.';
                }
            }
        } else {
            $reason = 'too expensive order.';
        }

        return $reason ? 'Odin: Anti-fraud check is required: ' . $reason : null;
    }

    /**
     * Returns the reason of the pause by CustomerBlacklist
     * @param CustomerBlacklist $cus_blst
     * @param OdinOrder $order
     * @return string|null
     */
    private static function getPauseReason(CustomerBlacklist $cus_blst, OdinOrder $order): ?string
    {
        $result = null;
        if ($cus_blst->orders_count > self::ORDER_PAUSE_COUNT) {
            if ($cus_blst->email === $order->customer_email) {
                $result = 'a few recent orders with the same email.';
            } elseif ($cus_blst->fingerprint === $order->fingerprint) {
                $result = 'a few recent orders from the same device.';
            } elseif ($cus_blst->phone === $order->customer_phone) {
                $result = 'a few recent orders with the same phone number.';
            } else {
                $result = 'a few recent orders to the same postal address';
            }
            $result .= ' (' . join(', ', array_diff($cus_blst->orders, [$order->number])) . ')';
        }
        return $result;
    }
}
