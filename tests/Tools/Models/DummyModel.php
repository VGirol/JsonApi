<?php

namespace VGirol\JsonApi\Tests\Tools\Models;

use Illuminate\Database\Eloquent\Model;

class DummyModel extends Model
{
    protected $table = 'dummy_table';
    protected $primaryKey = 'DUMMY_ID';
}
