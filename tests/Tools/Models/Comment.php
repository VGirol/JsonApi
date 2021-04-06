<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comment';
    protected $primaryKey = 'COMMENT_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'AUTHOR_ID',
        'PHOTO_ID',
        'COMMENT_TEXT',
        'COMMENT_DATE'
    ];

    public function photo()
    {
        return $this->belongsTo(Photo::class, 'PHOTO_ID', 'PHOTO_ID');
    }

    public function user()
    {
        return $this->belongsTo(Author::class, 'AUTHOR_ID', 'AUTHOR_ID');
    }
}
