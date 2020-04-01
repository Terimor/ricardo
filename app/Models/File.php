<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class File extends Model
{
    protected $collection = 'file';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category', 'name', 'urls', 'title',
    ];

    const CATEGORY_FREE  = 'free';

    public static $categories = [
        self::CATEGORY_FREE => 'Free'
    ];

    /**
     * Returns File by ID
     * @param $id
     * @return File|null
     */
    public static function getById($id): ?File {
        return File::where(['_id' => (string)$id])->first();
    }

    /**
     * Get images by ids
     * @param array $ids
     */
    public static function getByIds(?array $ids)
    {
        $files = null;
        if ($ids) {
            $files = File::whereIn('_id', $ids)->get();
        }
        return $files;
    }

    /**
     * Returns s3 URL
     * @return string
     */
    public function getUrl(): ?string {
        return !empty($this->urls[app()->getLocale()]) ? \Utils::replaceUrlForCdn($this->urls[app()->getLocale()]) : (!empty($this->urls['en']) ? \Utils::replaceUrlForCdn($this->urls['en']) : '');
    }

}
