<?php

namespace VGirol\JsonApi\Services;

use Illuminate\Http\Request;
use VGirol\JsonApiStructure\Exception\ValidationException;
use VGirol\JsonApiStructure\ValidateService as JsonApiStructureValidateService;

class ValidateService extends JsonApiStructureValidateService
{
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param int     $options
     *
     * @return void
     */
    public function validateRequestStructure($request): void
    {
        try {
            $this->setMethod($request->method());
            $this->validateStructure($request->input(), $this->strict);
        } catch (ValidationException $e) {
            $class = "\VGirol\JsonApi\Exceptions\JsonApi{$e->errorStatus()}Exception";
            throw new $class($e->getMessage());
        }
    }
}
