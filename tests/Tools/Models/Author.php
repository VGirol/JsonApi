<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $table = 'author';
    protected $primaryKey = 'AUTHOR_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'AUTHOR_ID',
        'AUTHOR_NAME'
    ];

    public function photos()
    {
        return $this->hasMany(Photo::class, $this->getKeyName(), $this->getKeyName());
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, $this->getKeyName(), $this->getKeyName());
    }
}
