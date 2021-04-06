<?php

namespace VGirol\JsonApi\Resources;

use Illuminate\Http\Request;

trait IsIncluded
{
    /**
     * Transform the resource into an array used as included resource.
     *
     * @param Request $request
     *
     * @return array
     */
    public function asIncluded($request): array
    {
        $data = $this->toArray($request);

        return array_merge_recursive($data, $this->additional);
    }
}
