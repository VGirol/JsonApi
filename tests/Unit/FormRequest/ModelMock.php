<?php

namespace VGirol\JsonApi\Tests\Unit\FormRequest;

use Illuminate\Database\Eloquent\Model;

class ModelMock extends Model
{
    protected $table = 'main_table';
    protected $primaryKey = 'MAIN_ID';
}
