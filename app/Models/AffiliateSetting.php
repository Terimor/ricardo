<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class AffiliateSetting extends Model
{
    protected $collection = 'affiliate_setting';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'ho_affiliate_id', 'postback_percent', 'is_signup_hidden', 'products'
    ];
    
    /*
     * Main rules
     */
    public static $mainQtyRules = [
        10 => 100,
        20 => 90,
        25 => 80
    ];
    
    /**
     * If more than this qty minus it for calculation
     * @var type 
     */
    public static $maxQtyMainRules = 25;
    
    /**
     * Default percent after mainQtyRules
     * @var type 
     */
    public static $defaultPercent = 65;
    
    public static $salesQtyInTable = 20;
    
    /**
     * [Percent] = [sales count]
     * @var type 
     */
    public static $percentArray = [
      5 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => false, 10 => false,
        11 => false, 12 => false, 13 => false, 14 => false, 15 => false, 16 => false, 17 => false, 18 => false, 19 => false, 20 => false],
      10 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => false, 6 => false, 7 => false, 8 => false, 9 => false, 10 => true,
        11 => false, 12 => false, 13 => false, 14 => false, 15 => false, 16 => false, 17 => false, 18 => false, 19 => false, 20 => false],
      15 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => false, 6 => false, 7 => true, 8 => false, 9 => false, 10 => false,
        11 => false, 12 => false, 13 => true, 14 => false, 15 => false, 16 => false, 17 => false, 18 => false, 19 => false, 20 => false],
      20 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => false, 6 => true, 7 => false, 8 => false, 9 => false, 10 => true,
        11 => false, 12 => false, 13 => false, 14 => true, 15 => false, 16 => false, 17 => true, 18 => false, 19 => false, 20 => false],
      25 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => true, 6 => false, 7 => false, 8 => false, 9 => false, 10 => false,
        11 => true, 12 => false, 13 => false, 14 => false, 15 => true, 16 => false, 17 => false, 18 => true, 19 => false, 20 => false],
      30 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => true, 6 => false, 7 => false, 8 => true, 9 => false, 10 => false,
        11 => true, 12 => false, 13 => false, 14 => true, 15 => false, 16 => false, 17 => true, 18 => false, 19 => false, 20 => false],
      35 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => true, 6 => false, 7 => true, 8 => false, 9 => false, 10 => true,
        11 => false, 12 => false, 13 => true, 14 => false, 15 => false, 16 => true, 17 => false, 18 => false, 19 => true, 20 => false],
      40 => [1 => true, 2 => true, 3 => false, 4 => true, 5 => true, 6 => false, 7 => true, 8 => false, 9 => false, 10 => true,
        11 => false, 12 => false, 13 => true, 14 => false, 15 => true, 16 => false, 17 => false, 18 => true, 19 => false, 20 => false],
      45 => [1 => true, 2 => true, 3 => false, 4 => true, 5 => true, 6 => false, 7 => true, 8 => true, 9 => false, 10 => true,
        11 => false, 12 => false, 13 => true, 14 => false, 15 => true, 16 => false, 17 => false, 18 => true, 19 => false, 20 => false],
      50 => [1 => true, 2 => true, 3 => false, 4 => false, 5 => true, 6 => false, 7 => true, 8 => false, 9 => true, 10 => false,
        11 => true, 12 => false, 13 => true, 14 => false, 15 => true, 16 => false, 17 => true, 18 => false, 19 => true, 20 => false],
      55 => [1 => true, 2 => true, 3 => true, 4 => false, 5 => true, 6 => false, 7 => true, 8 => false, 9 => true, 10 => false,
        11 => true, 12 => false, 13 => true, 14 => true, 15 => true, 16 => false, 17 => true, 18 => false, 19 => true, 20 => false],
      60 => [1 => true, 2 => true, 3 => true, 4 => false, 5 => true, 6 => false, 7 => true, 8 => false, 9 => true, 10 => false,
        11 => true, 12 => false, 13 => true, 14 => true, 15 => true, 16 => true, 17 => true, 18 => false, 19 => true, 20 => false],
      65 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => false, 7 => true, 8 => false, 9 => true, 10 => false,
        11 => true, 12 => false, 13 => true, 14 => true, 15 => true, 16 => true, 17 => true, 18 => false, 19 => true, 20 => true],
      70 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => false, 7 => true, 8 => false, 9 => true, 10 => true,
        11 => true, 12 => false, 13 => true, 14 => true, 15 => true, 16 => true, 17 => true, 18 => false, 19 => true, 20 => true],
      75 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => false, 6 => true, 7 => true, 8 => false, 9 => true, 10 => false,
        11 => true, 12 => true, 13 => true, 14 => false, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => false],
      80 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => false, 6 => true, 7 => true, 8 => false, 9 => true, 10 => true,
        11 => true, 12 => true, 13 => true, 14 => false, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => false],
      85 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true, 8 => false, 9 => true, 10 => true,
        11 => true, 12 => true, 13 => true, 14 => false, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => false],
      90 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true, 8 => false, 9 => true, 10 => true,
        11 => true, 12 => true, 13 => true, 14 => false, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => true],
      95 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true, 8 => true, 9 => true, 10 => false,
        11 => true, 12 => true, 13 => true, 14 => true, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => true],
      100 => [1 => true, 2 => true, 3 => true, 4 => true, 5 => true, 6 => true, 7 => true, 8 => true, 9 => true, 10 => true,
        11 => true, 12 => true, 13 => true, 14 => true, 15 => true, 16 => true, 17 => true, 18 => true, 19 => true, 20 => true]
    ];
    
    /**
     * Attributes with default values
     *
     * @var array $attributes
     */
    protected $attributes = [
        'name' => null,
        'is_signup_hidden' => false,
        'products' => null,
        'postback_percent' => 0
    ];
    
    /**
     * Get affiliate by HasOffer ID
     * @param type $hasOfferId
     * @return type
     */
    public static function getByHasOfferId(string $hasOfferId)
    {
        return AffiliateSetting::where(['ho_affiliate_id' => $hasOfferId])->first();
    }
    
    /**
     * Calculate is fire
     * @param string $productId
     * @param \App\Models\AffiliateSetting $affiliate
     */
    public static function calculateIsReduced(string $productId, AffiliateSetting $affiliate)
    {
        $isReduce = false;
        if ($affiliate) {
            $products = $affiliate->products;
            if (isset($products[$productId])) {
                $qty = $products[$productId];
            } else {
                $qty = 0;
            }
            $qty = $qty + 1;
            
            // check main rules for all sales
            foreach (static::$mainQtyRules as $salesQty => $percent) {
                if ($qty <= $salesQty) {
                    $reducePercent = $percent;
                    break;
                }
            }

            $qtyForCalculation = $qty;
            
            if ($qtyForCalculation > static::$maxQtyMainRules) {
                $qtyForCalculation = $qtyForCalculation - static::$maxQtyMainRules;
            }
            
            // calculate value for $percentArray
            if ($qtyForCalculation > static::$salesQtyInTable) {
                $tempRateCount = floor($qtyForCalculation / static::$salesQtyInTable);
                $qtyForCalculation = (int) ($qtyForCalculation - ($tempRateCount * static::$salesQtyInTable));
            }

            // if we have mainQtyRules percentage
            if (!isset($reducePercent)) {
                $reducePercent = !empty($affiliate->postback_percent) ? $affiliate->postback_percent : static::$defaultPercent;
            }

            if (isset(static::$percentArray[$reducePercent][$qtyForCalculation])) {
                $isReduce = static::$percentArray[$reducePercent][$qtyForCalculation];
                // save affiliate products
                $products[$productId] = $qty;
                $affiliate->products = $products;
                $affiliate->save();
            } else {
                logger()->error("Wrong affiliate percent", ['productId' => $productId, 'qty' => $qty, 'affiliateId' => $affiliate->id, 'reducePercent' => $reducePercent]);
            }           
        }
        
        return $isReduce;
    }
    
    public function getLocaleAffiliateById()
    {
        
    }


}
