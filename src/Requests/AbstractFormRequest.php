<?php

namespace VGirol\JsonApi\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use VGirol\JsonApi\Exceptions\JsonApiValidationException;
use VGirol\JsonApi\Services\ValidateService;
use VGirol\JsonApiConstant\Members;
use VGirol\JsonApiStructure\Messages as JsonApiStructureMessages;

abstract class AbstractFormRequest extends FormRequest
{
    protected $isCollection = false;

    private $cachedRules = [];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules(): array;

    /**
     * Get the prepared validation rules that apply to the request.
     *
     * @return array
     */
    public function preparedRules(): array
    {
        if (empty($this->cachedRules)) {
            $this->cachedRules = \array_merge($this->getDefaultRules(), $this->getCustomRules());
        }

        return $this->cachedRules;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function preparedMessages(): array
    {
        return \array_merge($this->getDefaultMessages(), $this->getCustomMessages());
    }

    /**
     * Get the validated data from the request.
     *
     * @param string|null $name
     *
     * @return array|string|null
     */
    public function validated(string $name = null)
    {
        $all = parent::validated();

        return $name ? Arr::get($all, $name) : $all;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    protected function validateRequestStructure(): void
    {
        // Validates request structure
        resolve(ValidateService::class)
            ->setMethod($this->method())
            ->setRouteType($this->getRouteType())
            ->setCollection($this->isCollection)
            ->validateRequestStructure($this);
    }

    /**
     * Undocumented function
     *
     * @return int
     */
    protected function getRouteType(): int
    {
        if ($this->routeIs('*.related.*')) {
            return ValidateService::ROUTE_RELATED;
        }
        if ($this->routeIs('*.relationship.*')) {
            return ValidateService::ROUTE_RELATIONSHIP;
        }

        return ValidateService::ROUTE_MAIN;
    }

    /**
     * Override \Illuminate\Foundation\Http\FormRequest::createDefaultValidator
     */
    protected function createDefaultValidator(ValidationFactory $factory)
    {
        return $factory->make(
            $this->validationData(),
            $this->container->call([$this, 'preparedRules']),
            $this->preparedMessages(),
            $this->attributes()
        );
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->validateRequestStructure();
    }

    /**
     * Override \Illuminate\Foundation\Http\FormRequest::failedValidation
     */
    protected function failedValidation(Validator $validator)
    {
        throw (new JsonApiValidationException($validator))
            ->errorBag($this->errorBag);
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        return $this->rules();
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        return $this->messages();
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    protected function isUpdate(): bool
    {
        return ($this->isMethod('PUT') || $this->isMethod('PATCH'));
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    private function getDefaultRules(): array
    {
        $star = $this->isCollection ? '.*' : '';

        $idRule = [
            // 'string'
        ];
        if ($this->isUpdate()) {
            // array_push($idRule, 'required');
            \array_push($idRule, Rule::in([$this->id]));
        }

        $rules = [
            Members::DATA . $star . '.' . Members::TYPE => [
                // 'required',
                // 'string',
                Rule::in([jsonapiAliases()->getResourceType($this)]),
            ],
            Members::DATA . $star . '.' . Members::ID => $idRule
        ];

        return $rules;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    private function getDefaultMessages(): array
    {
        $star = $this->isCollection ? '.*' : '';

        return [
            Members::DATA . $star . '.' . Members::TYPE . '.required' =>
                // Messages::FORM_REQUEST_ERROR_MISSING_TYPE_MEMBER,
                JsonApiStructureMessages::RESOURCE_TYPE_MEMBER_IS_ABSENT,
            Members::DATA . $star . '.' . Members::ID . '.required' =>
                // Messages::FORM_REQUEST_ERROR_MISSING_ID_MEMBER
                JsonApiStructureMessages::RESOURCE_ID_MEMBER_IS_ABSENT
        ];
    }
}
