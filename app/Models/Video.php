<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Video extends OdinModel
{
    protected $collection = 'video';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'share_id', 'title', 'st_file_id', 'image_id',
    ];

    const VIMEO_URL = 'https://vimeo.com/';
    const VIMEO_PLAYER_URL = 'https://player.vimeo.com/video/';

    /**
     * Getter title
     */
    public function getTitleAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Getter share_id
     */
    public function getShareIdAttribute($value)
    {
        return $this->getFieldLocalText($value);
    }

    /**
     * Returns Video by ID
     * @param $id
     * @return File|null
     */
    public static function getById($id): ?Video {
        return static::where(['_id' => (string)$id])->first();
    }

    /**
     * Get video by ids
     * @param array $ids
     * @return null|array
     */
    public static function getByIds(?array $ids, array $select = []): ?\Illuminate\Database\Eloquent\Collection
    {
        $videos = null;
        if ($ids) {
            $query = static::whereIn('_id', $ids);
            if ($select) {
                $query->select($select);
            }
            $videos = $query->get();
        }
        return $videos;
    }

    /**
     * Get vimeo video url
     * @return string
     */
    public function getVimeoVideo()
    {
        return static::VIMEO_PLAYER_URL.$this->share_id;
    }
}
