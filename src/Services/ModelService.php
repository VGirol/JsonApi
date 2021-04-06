<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Support\Facades\Schema;

class ModelService
{
    public function getModelTable(string $className): string
    {
        return $this->getModel($className)->getTable();
    }

    public function getModelKeyName(string $className): string
    {
        return $this->getModel($className)->getKeyName();
    }

    public function getVisible(string $className): array
    {
        $model = $this->getModel($className);

        $columns = Schema::getColumnListing($model->getTable());
        $visible = $model->getVisible();
        $hidden = $model->getHidden();

        $ret = !empty($visible) ? $visible : \array_diff($columns, $hidden);

        return \array_values($ret);
    }

    protected function getModel(string $className)
    {
        return new $className();
    }
}
