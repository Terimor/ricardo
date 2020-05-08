<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class MediaAccess extends Model
{
    protected $collection = 'media_access';

    protected $dates = ['created_at', 'updated_at'];

    public $timestamps = true;

    const TYPE_FILE = 'file';
    const TYPE_VIDEO = 'video';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number', 'type', 'document_id', 'count', 'accesses',
    ];

    /**
     * Default attributes on creating
     * @var array
     */
    protected $attributes = [
        'order_number' => null,
        'type' => null,
        'document_id' => null,
        'count' => 0,
        'accesses' => []
    ];

    /**
     * Save access by file
     * @param array|null $file
     * @param string $orderNumber
     * @return bool
     */
    public static function saveByOrderFile(?array $file, string $orderNumber): bool
    {
        $saved = false;
        if ($file && $orderNumber) {
            // check existing, if not - create new
            $model = static::getByOrderFileDocument($file, $orderNumber);
            $model->count++;
            $accesses = $model->accesses;
            $accesses[] = [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'accessed_at' => \Utils::getMongoTimeFromTS(time())
            ];
            $model->accesses = $accesses;
            $saved = $model->save();
        }
        return $saved;
    }

    /**
     * Get by file document and order number
     * If not exists create new
     * @param array $file
     * @param string $orderNumber
     * @return MediaAccess
     */
    public static function getByOrderFileDocument(array $file, string $orderNumber): MediaAccess
    {
        return static::firstOrNew(['document_id' => $file['id'], 'order_number' => $orderNumber, 'type' => $file['type']]);
    }

}
