<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $table = 'price';
    protected $primaryKey = 'PRICE_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'PRICE_ID',
        'PHOTO_ID',
        'PRICE_VALUE'
    ];

    protected $casts = [
        'PRICE_VALUE' => 'float'
    ];

    public function photo()
    {
        return $this->belongsTo(Photo::class, 'PHOTO_ID', 'PHOTO_ID');
    }
}
