<?php

namespace VGirol\JsonApi\Tests\Tools\Resources;

use VGirol\JsonApi\Resources\ResourceObject;

class AuthorResource extends ResourceObject
{
    protected function setDocumentMeta($request)
    {
        if ($request->method() == 'DELETE') {
            $this->addDocumentMeta('message', sprintf(static::DELETED_MESSAGE, $this->getId()));
        } else {
            $this->addDocumentMeta('writes', 'best-sellers');
        }
    }
}
