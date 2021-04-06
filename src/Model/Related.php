<?php

namespace VGirol\JsonApi\Model;

class Related
{
    public $model;
    public $metaAttributes;

    public function __construct($model, $metaAttributes = [])
    {
        $this->model = $model;
        $this->metaAttributes = $metaAttributes;
    }
}
