<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;
use VGirol\JsonApi\Tests\Tools\Models\PhotoTags;

class Photo extends Model
{
    protected $table = 'photo';
    protected $primaryKey = 'PHOTO_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'PHOTO_ID',
        'PHOTO_TITLE',
        'PHOTO_SIZE',
        'PHOTO_DATE',
        'AUTHOR_ID'
    ];

    protected $casts = [
        'PHOTO_ID' => 'int',
        'PHOTO_SIZE' => 'int'
    ];

    protected $hidden = [
        'AUTHOR_ID'
    ];

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (empty($attributes['PHOTO_DATE']) && ($this->getAttributeValue('PHOTO_DATE') == null)) {
            $this->setAttribute('PHOTO_DATE', '01-01-1970');
        }

        return $this;
    }

    public function author()
    {
        return $this->belongsTo(Author::class, 'AUTHOR_ID', 'AUTHOR_ID');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, $this->getKeyName(), $this->getKeyName());
    }

    public function price()
    {
        return $this->hasOne(Price::class, $this->getKeyName(), $this->getKeyName());
    }

    public function tags()
    {
        return $this->belongsToMany(Tags::class, 'pivot_phototags', $this->getKeyName(), 'TAGS_ID')
            ->using(PhotoTags::class)
            ->withPivot('PIVOT_COMMENT');
    }
}
