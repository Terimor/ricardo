<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Localize extends Model
{
    /**
     * Add cities to review for local product
     * @param array|null $cities
     */
    public function addCityReviews(?array $cities): void
    {
        if (!empty($this->reviews) && is_array($this->reviews) && $cities) {
            $reviews = [];
            $countryCode = \Utils::getLocationCountryCode();
            foreach ($this->reviews as $key => $review) {
                $reviews[$key] = $review;
                $reviews[$key]['city'] = $cities[$key] ?? '';
                $reviews[$key]['country'] = \Utils::$countryCodes[$countryCode] ?? '';
                $reviews[$key]['country_code'] = strtoupper($countryCode);
            }
            $this->reviews = $reviews;
        }
    }
}
