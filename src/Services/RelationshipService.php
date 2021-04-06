<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Exceptions\JsonApi404Exception;
use VGirol\JsonApi\Exceptions\JsonApi500Exception;
use VGirol\JsonApi\Exceptions\JsonApiException;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApi\Model\Related;
use VGirol\JsonApiConstant\Members;

class RelationshipService
{
    public const ERROR_BAD_TYPE =
        'The parameter $related must be a VGirol\JsonApi\Model\Related instance.';
    public const ERROR_NOT_A_RELATION =
        'The parameter $relation must be a Illuminate\Database\Eloquent\Relations\Relation instance.';

    /**
     * Undocumented variable
     *
     * @var FetchService
     */
    private $fetch;

    /**
     * Class constructor
     *
     * @param FetchService $fetchService
     *
     * @return void
     */
    public function __construct(FetchService $fetchService)
    {
        $this->fetch = $fetchService;
    }

    /**
     * Save all resource's relationships
     *
     * @param array $data
     * @param Model $parent
     *
     * @return void
     * @throws JsonApi403Exception
     * @throws JsonApi404Exception
     * @throws JsonApi500Exception
     */
    public function saveAll($data, $parent): void
    {
        $this->dispatchFromRequest($data, $parent, 'create');
    }

    /**
     * Save all resource's relationships
     *
     * @param array $data
     * @param Model $model
     *
     * @return void
     * @throws JsonApi403Exception
     * @throws JsonApi404Exception
     * @throws JsonApi500Exception
     */
    public function updateAll($data, $parent): void
    {
        $this->dispatchFromRequest($data, $parent, 'update');
    }

    /**
     * Undocumented function
     *
     * @param Relation               $relation
     * @param Collection|Model|array $related  If $related is an array, it comes from the json content of the request
     *
     * @return void
     * @throws JsonApi500Exception
     */
    public function create($relation, $related): void
    {
        $this->dispatch($relation, 'create', $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation               $relation
     * @param Collection|Model|array $related  If $related is an array, it comes from the json content of the request
     *
     * @return void
     * @throws JsonApi500Exception
     */
    public function update($relation, $related): void
    {
        if ($relation->isToMany() && !config('jsonapi.relationshipFullReplacementIsAllowed')) {
            throw new JsonApi403Exception(Messages::RELATIONSHIP_FULL_REPLACEMENT);
        }

        $this->dispatch($relation, 'update', $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     *
     * @return void
     */
    public function clear($relation): void
    {
        $this->dispatch($relation, 'clear');
    }

    /**
     * Undocumented function
     *
     * @param Relation               $relation
     * @param Collection|Model|array $related  If $related is an array, it comes from the json content of the request
     *
     * @return void
     */
    public function remove($relation, $related): void
    {
        $this->dispatch($relation, 'remove', $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation                    $relation
     * @param string                      $action
     * @param Collection|Model|array|null $related
     *
     * @return void
     * @throws JsonApi500Exception
     * @throws JsonApiException
     */
    private function dispatch($relation, string $action, $related = null): void
    {
        if (!is_a($relation, Relation::class)) {
            throw new JsonApi500Exception(self::ERROR_NOT_A_RELATION);
        }

        $related = $this->fetch->extractRelated($related);

        if (($action != 'clear') && !$relation->isToMany() && !is_a($related, Related::class)) {
            throw new JsonApiException(self::ERROR_BAD_TYPE);
        }

        $method = $this->getInternalMethod($relation, $action);
        $this->{$method}($relation, $related);
    }

    /**
     * Save all resource's relationships
     *
     * @param array  $data
     * @param Model  $model
     * @param string $fn
     *
     * @return void
     * @throws JsonApi403Exception
     * @throws JsonApi404Exception
     * @throws JsonApi500Exception
     */
    private function dispatchFromRequest($data, $parent, string $fn): void
    {
        // Looks for relationships
        $relationships = Arr::get($data, Members::RELATIONSHIPS, null);

        // If no relationships, returns
        if (is_null($relationships)) {
            return;
        }

        // Iterates through relationships
        foreach ($relationships as $name => $values) {
            $relation = $this->fetch->getRelationFromModel($parent, $name);
            $this->{$fn}($relation, $values[Members::DATA]);
        }
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param Related  $related
     *
     * @return void
     */
    private function createHasOne($relation, $related): void
    {
        $relation->save($related->model);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param Related  $related
     *
     * @return void
     */
    private function updateHasOne($relation, $related): void
    {
        // Detach old one ...
        $this->removeHasOne($relation);

        // ... and attach new one
        $this->createHasOne($relation, $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     *
     * @return void
     */
    private function clearHasOne($relation): void
    {
        $this->removeHasOne($relation);
    }

    /**
     * Undocumented function
     *
     * @param Relation     $relation
     * @param Related|null $related
     *
     * @return void
     */
    private function removeHasOne($relation, $related = null): void
    {
        $old = $relation->getResults();
        if ($old === null) {
            return;
        }

        if (($related !== null) && $old->isNot($related->model)) {
            return;
        }

        $old->update([
            $relation->getParent()->getKeyName() => null
        ]);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param Related  $related
     *
     * @return void
     */
    private function createBelongsTo($relation, $related): void
    {
        $this->updateBelongsTo($relation, $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param Related  $related
     *
     * @return void
     */
    private function updateBelongsTo($relation, $related): void
    {
        $relation->associate($related->model);
        $relation->getParent()->save();
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     *
     * @return void
     */
    private function clearBelongsTo($relation): void
    {
        $this->removeBelongsTo($relation);
    }

    /**
     * Undocumented function
     *
     * @param Relation     $relation
     * @param Related|null $related
     *
     * @return void
     */
    private function removeBelongsTo($relation, $related = null): void
    {
        $old = $relation->getResults();
        if ($old === null) {
            return;
        }

        if (($related !== null) && $old->isNot($related->model)) {
            return;
        }

        $relation->dissociate();
        $relation->getParent()->save();
    }

    /**
     * Undocumented function
     *
     * @param Relation           $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function createBelongsToMany($relation, $related): void
    {
        $this->updateBelongsToMany($relation, $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation           $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function updateBelongsToMany($relation, $related): void
    {
        $detaching = true;
        $relcollection = is_a($related, SupportCollection::class) ? $related : collect([$related]);

        $relatedIds = $relcollection->pluck(
            'metaAttributes',
            'model.' . $relcollection->first()->model->getKeyName()
        )->toArray();
        // $relatedModel = $relation->getRelated();
        // if ($relatedModel->whereIn($relatedModel->getKeyName(), $relatedIds)->count() == 0) {
        //     throw new JsonApi404Exception(sprintf(Messages::UPDATING_REQUEST_RELATED_NOT_FOUND, $name));
        // }

        $relation->sync($relatedIds, $detaching);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     *
     * @return void
     */
    private function clearBelongsToMany($relation): void
    {
        $relation->sync([], true);
    }

    /**
     * Undocumented function
     *
     * @param Relation           $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function removeBelongsToMany($relation, $related): void
    {
        $related = is_a($related, Related::class) ? collect([$related]) : $related;

        $relation->detach(
            $related->pluck(
                'model.' . $related->first()->model->getKeyName()
            )->toArray()
        );
    }

    /**
     * Undocumented function
     *
     * @param Relation           $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function updateHasMany($relation, $related): void
    {
        $this->clearHasMany($relation);
        $this->createHasMany($relation, $related);
    }

    /**
     * Undocumented function
     *
     * @param Relation           $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function createHasMany($relation, $related): void
    {
        $related = is_iterable($related) ? $related : collect([$related]);
        $relation->saveMany($related->pluck('model'));
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     *
     * @return void
     */
    private function clearHasMany($relation): void
    {
        $relation->update([$relation->getForeignKeyName() => null]);
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param Related|Collection $related
     *
     * @return void
     */
    private function removeHasMany($relation, $related): void
    {
        $related = is_a($related, Related::class) ? collect([$related]) : $related;

        foreach ($related as $item) {
            $item->model->setAttribute($relation->getForeignKeyName(), null);
            $item->model->save();
        };
    }

    /**
     * Undocumented function
     *
     * @param Relation $relation
     * @param string   $action
     *
     * @return string
     */
    private function getInternalMethod($relation, string $action): string
    {
        $class = get_class($relation);

        return $action . substr($class, strrpos($class, '\\') + 1);
    }
}
