<?php

namespace VGirol\JsonApi\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use VGirol\JsonApi\Exceptions\JsonApi403Exception;
use VGirol\JsonApi\Messages\Messages;
use VGirol\JsonApiConstant\Members;

trait ManageRelationships
{
    /**
     * Display a listing of the relationship.
     *
     * @param Request $request
     * @param mixed   $parentId
     * @param string  $relationship
     *
     * @return JsonResponse
     */
    public function relationshipIndex(Request $request, $parentId, string $relationship): JsonResponse
    {
        // Fetch related model or collection
        $related = $this->fetchService->getRelated($request, $parentId, $relationship);

        // Creates resource
        $resource = $this->exportService->exportAsResourceIdentifier($related, $request);

        return $this->responseService->ok($resource);
    }

    /**
     * Add one or more items to a relationships.
     *
     * @param Request $request
     * @param int $parentId
     * @param string $relationship
     *
     * @return JsonResponse
     */
    // public function relationshipStore(Request $request, $parentId, string $relationship): JsonResponse
    // {
    //     // Save relationship
    //     $resource = DB::transaction(function () use ($request, $parentId, $relationship) {
    //         // Retrieve relationship
    //         $relation = $this->getRelationFromRequest($request, $parentId, $relationship);

    //         // Checks that the relation is of type to-many
    //         if (!$relation->isToMany()) {
    //             throw new JsonApi403Exception(
    //                 sprintf(Messages::METHOD_NOT_ALLOWED_FOR_RELATIONSHIP, $request->method())
    //             );
    //         }

    //         // Validate data
    //         $this->validateResourceLinkages($request);

    //         // Gets the data
    //         $data = $request->input(Members::DATA);

    //         // Get related instances
    //         $related = $this->getRelatedFromRequestData($data, $relationship);

    //         // Attach related resources to parent
    //         $this->addToRelationship($parent, $relationship, $related, false);

    //         // Creates resource
    //         $resource = $this->fetchService->getRelated($request, $parentId, $relationship, true);

    //         return $resource;
    //     }, config('jsonapi.transactionAttempts'));

    //     // Fills response's content
    //     $response = $this->responseService->created($resource);

    //     return $response;
    // }

    /**
     * Updates all items of a relationship.
     *
     * @param Request $request
     * @param int     $parentId
     * @param string  $relationship
     *
     * @return  JsonResponse
     */
    public function relationshipUpdate(Request $request, $parentId, string $relationship): JsonResponse
    {
        // Save relationship
        $resource = DB::transaction(function () use ($request, $parentId, $relationship) {
            // Retrieve relationship
            $relation = $this->fetchService->getRelationFromRequest($request, $parentId, $relationship);

            if (
                $relation->isToMany()
                && (config('jsonapi.relationshipFullReplacementIsAllowed') === false)
            ) {
                throw new JsonApi403Exception(Messages::RELATIONSHIP_FULL_REPLACEMENT);
            }

            // Validate data
            $this->validateResourceLinkages($request);

            // Gets the data
            $data = $request->input(Members::DATA);
            if (($data === null) || ($data === [])) {
                $this->relationshipService->clear($relation);
            } else {
                $this->relationshipService->update($relation, $data);
            }

            // Fetch required relation
            $requiredRelated = $this->fetchService->getRelated($request, $parentId, $relationship, true);

            // Creates resource
            $resource = $this->exportService->exportAsResourceIdentifier($requiredRelated, $request);

            return $resource;
        }, config('jsonapi.transactionAttempts'));

        // Fills response's content
        $response = $this->responseService->ok($resource);

        return $response;
    }

    /**
     * Deletes some items of a relationship.
     *
     * @param Request $request
     * @param int     $parentId
     * @param string  $relationship
     *
     * @return  JsonResponse
     */
    public function relationshipDestroy(Request $request, $parentId, string $relationship): JsonResponse
    {
        // Save relationship
        $resource = DB::transaction(function () use ($request, $parentId, $relationship) {
            // Retrieve relationship
            $relation = $this->fetchService->getRelationFromRequest($request, $parentId, $relationship);

            // Checks that the relation is of type to-many
            if (!$relation->isToMany()) {
                throw new JsonApi403Exception(
                    sprintf(Messages::METHOD_NOT_ALLOWED_FOR_RELATIONSHIP, $request->method())
                );
            }

            // Validate data
            $this->validateResourceLinkages($request);

            // Gets the data
            $data = $request->input(Members::DATA);

            // Get related instances
            $related = $this->fetchService->getRelatedFromRequestData($data);

            // Attach related resources to parent
            $this->relationshipService->remove($relation, $related);

            // Creates resource
            $resource = $this->fetchService->getRelated($request, $parentId, $relationship, true);

            return $resource;
        }, config('jsonapi.transactionAttempts'));

        // Fills response's content
        $response = $this->responseService->ok($resource);

        return $response;
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     *
     * @return void
     */
    private function validateResourceLinkages($request)
    {
        // Gets the data
        $data = $request->input(Members::DATA, []);
    }
}
