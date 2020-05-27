<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Class Localize
 * Specified class to prepare model data for display
 * @package App\Models
 */

class Localize extends Model
{
    /**
     * Add cities to review for local product
     * @param array|null $cities
     * @return void
     */
    public function addCityReviews(?array $cities): void
    {
        if (!empty($this->reviews) && is_array($this->reviews) && $cities) {
            $reviews = [];
            $countryCode = \Utils::getLocationCountryCode();
            foreach ($this->reviews as $key => $review) {
                $reviews[$key] = $review;
                $reviews[$key]['city'] = strtoupper($countryCode).', '.($cities[$key] ?? '');
            }
            $this->reviews = $reviews;
        }
    }

    /**
     * Collect images for media and fill image urls
     * @return array
     */
    public function collectVirtualMediaImages(): void
    {
        $image_ids = $this->collectImageIds();
        $images = AwsImage::getByIds($image_ids);
        // prepare images array and fill image for medias
        if ($images) {
            $image_urls = [];
            foreach ($images as $image) {
                $image_urls[(string)$image->_id] = $image->getFieldLocalText($image->urls);
            }
            $this->free_files = $this->setImagesVirtualMediaField('free_files', $image_urls);
            $this->sale_files = $this->setImagesVirtualMediaField('sale_files', $image_urls);
            $this->sale_videos = $this->setImagesVirtualMediaField('sale_videos', $image_urls);
            if (!empty($this->upsells_files)) {
                $this->upsells_files = $this->setImagesVirtualMediaField('upsells_files', $image_urls);
            }
            if (!empty($this->upsells_videos)) {
                $this->upsells_videos = $this->setImagesVirtualMediaField('upsells_videos', $image_urls);
            }
        }
    }

    /**
     * Collect image ids for virtual product
     * @return array
     */
    private function collectImageIds(): array
    {
        $image_ids = [];
        if (!empty($this->free_files)) {
            $image_ids = array_merge(array_column($this->free_files, 'image_id'), $image_ids);
        }
        if (!empty($this->sale_files)) {
            $image_ids = array_merge(array_column($this->sale_files, 'image_id'), $image_ids);
        }
        if (!empty($this->sale_videos)) {
            $image_ids = array_merge(array_column($this->sale_videos, 'image_id'), $image_ids);
        }
        if (!empty($this->upsells_files)) {
            $image_ids = array_merge(array_column($this->upsells_files, 'image_id'), $image_ids);
        }
        if (!empty($this->upsells_videos)) {
            $image_ids = array_merge(array_column($this->upsells_videos, 'image_id'), $image_ids);
        }
        // remove empty values and duplicates
        $image_ids = array_filter(array_unique($image_ids));
        return $image_ids;
    }

    /**
     * Set virtual media field images
     * @param string $field
     * @param array $images - array [id] => url
     * @return array
     */
    private function setImagesVirtualMediaField(string $field, array $images): array {
        $medias = [];
        if ($images) {
            if (!empty($this->$field)) {
                foreach ($this->$field as $file) {
                    $file['image'] = !empty($images[$file['image_id']]) ? $images[$file['image_id']] : '';
                    $medias[] = $file;
                }
            }
        }
        return $medias;
    }
}
