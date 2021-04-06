<?php

namespace VGirol\JsonApi\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;
use VGirol\JsonApiConstant\Members;

abstract class ResourceFormRequest extends AbstractFormRequest
{
    /**
     * Undocumented function
     *
     * @return array
     */
    protected function getCustomRules(): array
    {
        $star = $this->isCollection ? '.*' : '';
        $rules = [];
        foreach ($this->rules() as $customKey => $customRules) {
            if (is_string($customRules)) {
                $customRules = explode('|', $customRules);
            }
            if (!is_array($customRules)) {
                $customRules = [$customRules];
            }
            $rules[Members::DATA . $star . '.' . Members::ATTRIBUTES . '.' . $customKey] =
                $this->transformRules($customRules);
        }

        return $rules;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    protected function getCustomMessages(): array
    {
        $star = $this->isCollection ? '.*' : '';
        $mes = [];

        foreach ($this->preparedRules() as $key => $rules) {
            foreach ($rules as $rule) {
                if (($rule instanceof Unique) || (is_string($rule) && (strpos($rule, 'unique') !== false))) {
                    $mes[$key . '.unique'] = '(409) ' . trans('validation.unique');
                }
            }
        }
        $mes[Members::DATA . $star . '.' . Members::TYPE . '.in'] = '(409) ' . trans('validation.in');

        return array_merge($mes, $this->messages());
    }

    /**
     * Undocumented function
     *
     * @param array $customRules
     *
     * @return array
     */
    private function transformRules(array $customRules): array
    {
        if (!$this->isUpdate()) {
            return $customRules;
        }

        $cRules = [];
        foreach ($customRules as $customRule) {
            // if ($customRule instanceof IlluminateRule) {
            //     $cRules[] = $customRule;
            //     continue;
            // }

            // No required
            if ($customRule == 'required') {
                continue;
            }

            // Unique
            if (is_string($customRule) && (strpos($customRule, 'unique') !== false)) {
                $customRule = str_replace('unique:', null, $customRule);
                $a = explode(',', $customRule);
                // $a[0] = table, $a[1] = field
                $customRule = Rule::unique($a[0], $a[1]);
            }
            if ($customRule instanceof Unique) {
                $customRule->ignore($this->id, jsonapiAliases()->getModelKeyName($this));
            }
            $cRules[] = $customRule;
        }

        return $cRules;
    }
}
