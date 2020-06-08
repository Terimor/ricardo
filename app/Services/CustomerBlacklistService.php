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
    const ORDER_PAUSE_AMOUNT_USD = 500;
    const ORDER_PAUSE_COUNT = 2;
    const ORDER_PAUSE_TOTAL_AMOUNT_USD = 500;
    const ORDER_PAUSE_TOTAL_COUNT = 1;

    /**
     * Checks if order needs a pause
     * @param OdinOrder $order
     * @param float $txn_amount_usd
     * @return string|null
     */
    public static function getOrderPauseReason(OdinOrder $order, float $txn_amount_usd): ?string
    {
        if ($order->is_paused) {
            return null;
        }

        $reason = null;
        if ($order->total_paid_usd < self::ORDER_PAUSE_AMOUNT_USD) {
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

                $cus_blst = CustomerBlacklist::addOne($order->number, $txn_amount_usd, [
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
        } else {
            $reason = 'too expensive order (more than $' . self::ORDER_PAUSE_AMOUNT_USD . ').';
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
        if ($cus_blst->getOrdersCount() > self::ORDER_PAUSE_TOTAL_COUNT) {
            $order_lst = join(', ', array_diff($cus_blst->orders, [$order->number]));
            if ($cus_blst->getOrdersCount() > self::ORDER_PAUSE_COUNT) {
                if ($cus_blst->email === $order->customer_email) {
                    $result = 'a few recent orders with the same email';
                } elseif ($cus_blst->fingerprint === $order->fingerprint) {
                    $result = 'a few recent orders from the same device';
                } elseif ($cus_blst->phone === $order->customer_phone) {
                    $result = 'a few recent orders with the same phone number';
                } else {
                    $result = 'a few recent orders to the same postal address';
                }
                $result .= " ({$order_lst}).";
            } elseif ($cus_blst->orders_paid_usd > self::ORDER_PAUSE_TOTAL_AMOUNT_USD) {
                $result = "a few recent customer's orders has \${$cus_blst->orders_paid_usd} in total ({$order_lst}).";
            }
        }
        return $result;
    }
}
