<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use App\Models\Localize;
use App\Services\AffiliateService;

class AffiliateSetting extends Model
{
    protected $collection = 'affiliate_setting';
    
    protected $dates = ['created_at', 'updated_at'];
    
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'ho_affiliate_id', 'postback_percent', 'is_signup_hidden', 'product_sales'
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
    public static $defaultPercent = 70;
    
    public static $salesQtyInTable = 20;
    
    const OWN_AFFILIATE_MAX = 10;
    const TXID_LENGTH = 20;
    const ALL_INCLUDE_INTERNAL_OPTION = '0';
    const ALL_EXCLUDE_INTERNAL_OPTION = '-1';
    
    public static $approvedNames = ['{aff_id}'];
    const AFFILIATE_ID_LENGTH = 5;
    
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
        'product_sales' => null,
        'postback_percent' => 0
    ];
    
    /**
     * @param $value
     */
    public function setHoAffiliateIdAttribute($value)
    {
        $this->attributes['ho_affiliate_id'] = trim($value);
    }    
    
    /**
     * Get affiliate by HasOffer ID
     * @param type $hasOfferId
     * @return type
     */
    public static function getByHasOfferId(string $hasOfferId)
    {
        $affiliate = null;
        if (AffiliateService::validateAffiliateID($hasOfferId)) {          
            $affiliate = AffiliateSetting::firstOrCreate(['ho_affiliate_id' => $hasOfferId]);             
        }
        return $affiliate;
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
            $products = $affiliate->product_sales;
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
                $qtyForCalculation = (int) (($qtyForCalculation+1) - ($tempRateCount * static::$salesQtyInTable));
            }
            
            // if we haven't mainQtyRules percentage check product reduce_percent
            if (!isset($reducePercent)) {
                $product = OdinProduct::getById($productId);
                if (!empty($product->reduce_percent)) {
                    $reducePercent = $product->reduce_percent;
                }
            }
            
            // if we haven't product reduce_percent
            if (!isset($reducePercent)) {
                $reducePercent = !empty($affiliate->postback_percent) ? $affiliate->postback_percent : static::$defaultPercent;
            }

            if (isset(static::$percentArray[$reducePercent][$qtyForCalculation])) {                
                $isReduce = static::$percentArray[$reducePercent][$qtyForCalculation];
                // save affiliate products
                $products[$productId] = $qty;
                $affiliate->product_sales = $products;
                $affiliate->save();
            } else {
                logger()->error("Wrong affiliate percent", ['productId' => $productId, 'qty' => $qty, 'qtyCalculation' => $qtyForCalculation, 'affiliateId' => $affiliate->ho_affiliate_id, 'reducePercent' => $reducePercent]);
            }           
        }        
        return $isReduce;
    }
    
    /**
     * Get locale affiliate id by hasOfferId
     * @param string $hasOfferId
     * @return Localize
     */
    public static function getLocaleAffiliate(AffiliateSetting $affiliate = null)
    {        
        $al = null;

        if ($affiliate) {
            $al['affiliate'] = $affiliate->ho_affiliate_id;
            $al['is_signup_hidden'] = $affiliate->is_signup_hidden;
        }

        return $al;
    }
}
