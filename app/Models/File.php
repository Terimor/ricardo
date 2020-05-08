<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class File extends OdinModel
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
     * Getter title
     */
    public function getTitleAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter url
     */
    public function getUrlAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Returns File by ID
     * @param $id
     * @return File|null
     */
    public static function getById($id): ?File {
        return static::where(['_id' => (string)$id])->first();
    }

    /**
     * Get files by ids
     * @param array $ids
     * @return null|array
     */
    public static function getByIds(?array $ids, array $select = []): ?\Illuminate\Database\Eloquent\Collection
    {
        $files = null;
        if ($ids) {
            $query = static::whereIn('_id', $ids);
            if ($select) {
                $query->select($select);
            }
            $files = $query->get();
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
