<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Load
 *
 * @property int id
 */
class Load extends BaseModel
{
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
     * Возвращает связь родительской категории
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id')->withDefault();
    }

    /**
     * Возвращает связь подкатегорий
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort');
    }

    /**
     * Возвращает количество загрузок за последние 3 дней
     *
     * @return hasOne
     */
    public function new(): hasOne
    {
        return $this->hasOne(Down::class, 'category_id')
            ->selectRaw('category_id, count(*) as count_downs')
            ->where('active', 1)
            ->where('created_at', '>', strtotime('-3 day', SITETIME))
            ->groupBy('category_id');
    }
}
