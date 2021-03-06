<?php

namespace App\Models;

/**
 * Class Mailing
 *
 * @property int id
 * @property int user_id
 * @property string type
 * @property string subject
 * @property string text
 * @property int sent
 * @property int created_at
 * @property int sent_at
 */
class Mailing extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mailings';

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
}
