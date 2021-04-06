<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use VGirol\JsonApiConstant\Members;

trait IsRelationship
{
    /**
     * Transform the resource into an array used as relationship.
     *
     * @param Request $request
     * @param Model   $model            Parent model instance
     * @param string  $relationshipName
     *
     * @return array
     */
    public function asRelationship($request, $model, string $relationshipName): array
    {
        $data = [(self::$wrap ?? Members::DATA) => $this->toArray($request)];

        $this->setRelationshipMeta($request, $model);
        $this->setRelationshipLinks($request, $model, $relationshipName);

        return array_merge_recursive($data, $this->additional);
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     * @param Model   $model   Parent model instance
     *
     * @return void
     */
    protected function setRelationshipMeta($request, $model)
    {
        // $this->addRelationshipMeta('key', 'value');
    }

    /**
     * Could be overloaded
     *
     * @param Request $request
     * @param Model   $model            Parent model instance
     * @param string  $relationshipName
     *
     * @return void
     */
    protected function setRelationshipLinks($request, $model, string $relationshipName)
    {
        $this->addRelationshipLink(Members::LINK_SELF, $this->getRelationshipSelfLink($model, $relationshipName));
        $this->addRelationshipLink(Members::LINK_RELATED, $this->getRelationshipRelatedLink($model, $relationshipName));
    }
}
