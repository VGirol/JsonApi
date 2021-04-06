<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PhotoTags extends Pivot
{
    protected $table = 'pivot_phototags';
    protected $primaryKey = 'PIVOT_ID';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'PIVOT_COMMENT'
    ];
}
