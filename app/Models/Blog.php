<?php

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Class Blog
 *
 * @property int id
 * @property int category_id
 * @property int user_id
 * @property string title
 * @property string text
 * @property string tags
 * @property int rating
 * @property int visits
 * @property int count_comments
 * @property int created_at
 * @property Collection files
 * @property Category category
 */
class Blog extends BaseModel
{
    use UploadTrait;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Директория загрузки файлов
     *
     * @var string
     */
    public $uploadPath = UPLOADS . '/blogs';

    /**
     * Возвращает комментарии блогов
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'relate');
    }

    /**
     * Возвращает последнии комментарии к статье
     *
     * @param int $limit
     * @return HasMany
     */
    public function lastComments($limit = 15): HasMany
    {
        return $this->hasMany(Comment::class, 'relate_id')
            ->where('relate_type', self::class)
            ->limit($limit);
    }

    /**
     * Возвращает связь категории блога
     *
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id')->withDefault();
    }

    /**
     * Возвращает загруженные файлы
     *
     * @return MorphMany
     */
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'relate');
    }

    /**
     * Возвращает размер шрифта для облака тегов
     *
     * @param int   $count
     * @param float $minCount
     * @param float $maxCount
     * @param int   $minSize
     * @param int   $maxSize
     * @return int
     */
    public static function logTagSize($count, $minCount, $maxCount, $minSize = 10, $maxSize = 30): int
    {
        $minCount = log($minCount + 1);
        $maxCount = log($maxCount + 1);

        $diffSize  = $maxSize - $minSize;
        $diffCount = $maxCount - $minCount;

        if (empty($diffCount)) {
            $diffCount = 1;
        }

        return round($minSize + (log(1 + $count) - $minCount) * ($diffSize / $diffCount));
    }

    /**
     * Удаление статьи и загруженных файлов
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete(): ?bool
    {
        $this->files->each(function($file) {
            deleteFile(HOME . $file->hash);
            $file->delete();
        });

        return parent::delete();
    }
}
