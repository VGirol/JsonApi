<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class Tags extends Model
{
    protected $table = 'tags';
    protected $primaryKey = 'TAGS_ID';
    public $timestamps = false;
    protected $hidden = ['PHOTO_ID', 'PIVOT_ID'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'TAGS_ID',
        'TAGS_NAME'
    ];

    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'pivot_phototags', $this->getKeyName(), 'PHOTO_ID');
    }
}
