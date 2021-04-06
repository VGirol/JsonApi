<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Exceptions\JsonApiDuplicateEntryException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Services\AliasesService;
use VGirol\JsonApiConstant\Members;

class StoreService
{
    /**
     * Undocumented variable
     *
     * @var AliasesService
     */
    private $alias;

    /**
     * Class constructor
     *
     * @param AliasesService $aliasesService
     *
     * @return void
     */
    public function __construct(
        AliasesService $aliasesService
    ) {
        $this->alias = $aliasesService;
    }

    /**
     * Save a single resource in database
     *
     * @param array  $data
     * @param string $routeKey
     *
     * @return Model
     * @throws JsonApi403Exception
     * @throws JsonApiDuplicateEntryException
     */
    public function saveModel($data, $routeKey)
    {
        // Creates an unsaved model instance
        $model = call_user_func([$this->alias->getModelClassName($routeKey), 'make']);

        $attributes = Arr::get($data, Members::ATTRIBUTES, []);

        // Checks if model exists in database
        $this->checkForDuplicateEntry($model, $attributes);

        // Creates columns to save
        $columns = $this->createColumnsArray($attributes, $model->getTable());

        // Retrieves client-generated ID
        $id = Arr::get($data, Members::ID, null);
        if ($id !== null) {
            if (config('jsonapi.clientGeneratedIdIsAllowed') === false) {
                throw new JsonApi403Exception(Messages::CLIENT_GENERATED_ID_NOT_ALLOWED);
            }

            // Assigns client-generated ID
            // Model key MUST be mass assignable
            $columns[$model->getKeyName()] = $id;
        }

        // Fill model's attributes
        $model->fill($columns);

        // Saves model
        $model->save();

        return $model;
    }

    /**
     * Update a single resource in database
     *
     * @param array  $data
     * @param int    $id
     * @param string $routeKey
     *
     * @return Model
     */
    public function updateModel($data, $id, $routeKey)
    {
        // Creates an unsaved model instance
        $model = call_user_func(
            [$this->alias->getModelClassName($routeKey), 'make']
        )->findOrFail($id);

        // Creates columns to save
        $attributes = Arr::get($data, Members::ATTRIBUTES, []);
        $columns = $this->createColumnsArray($attributes, $model->getTable());

        // Updates the model
        $model->update($columns);

        return $model;
    }

    /**
     * Delete a single resource in database
     *
     * @param string $routeKey
     * @param mixed  $id
     *
     * @return Model
     */
    public function deleteModel(string $routeKey, $id)
    {
        // Retrieve model instance
        $model = call_user_func(
            [$this->alias->getModelClassName($routeKey), 'make']
        )->findOrFail($id);

        // Deletes model
        if (!is_null($model)) {
            $model->delete();
        }

        return $model;
    }

    /**
     * Undocumented function
     *
     * @param array $request
     * @param string $table
     *
     * @return array
     */
    private function createColumnsArray(array $request, string $table): array
    {
        $a = [];
        $columns = DB::getSchemaBuilder()->getColumnListing($table);
        foreach ($columns as $key) {
            $aFields = [
                $key,
                strtolower($key)
            ];
            $intersect = array_values(array_intersect_key($request, array_flip($aFields)));
            if (count($intersect)) {
                $a[$key] = $intersect[0];
            }
        }

        return $a;
    }

    /**
     * Undocumented function
     *
     * @param Model $model
     * @param array $attributes
     *
     * @return void
     * @throws JsonApiDuplicateEntryException
     */
    private function checkForDuplicateEntry($model, $attributes)
    {
        $duplicate = $model->where(
            $model->getKeyName(),
            '=',
            Arr::get($attributes, $model->getKeyName(), null)
        )->exists();

        if ($duplicate) {
            throw new JsonApiDuplicateEntryException();
        }
    }
}
