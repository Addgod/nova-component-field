<?php

namespace Addgod\ComponentField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class ComponentField extends Field
{
    /**
     * Do not show this field on the index page.
     *
     * @var bool
     */
    public $showOnIndex = false;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'component-field';

    /**
     * The registered fields.
     *
     * @var array
     */
    private $fields = [];

    /**
     * Set the fields, for this component.
     *
     * @param array $fields
     *
     * @return $this
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Generate field-specific validation rules.
     *
     * @param  array $rules
     *
     * @return array
     */
    protected function generateRules(array $rules)
    {
        return collect($rules)->mapWithKeys(function ($rules, $key) {
            array_unshift($rules, 'sometimes');

            return [$this->attribute . '.*.' . $key => $rules];
        })->toArray();
    }

    /**
     * Get the creation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array|string
     */
    public function getCreationRules(NovaRequest $request)
    {
        $result = [];
        foreach ($this->fields as $field) {
            $rules = $this->generateRules($field->getCreationRules($request));
            $result = array_merge_recursive($result, $rules);
        }

        return $result;
    }

    /**
     * Get the update rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function getUpdateRules(NovaRequest $request)
    {
        $result = [];
        foreach ($this->fields as $field) {
            $rules = $this->generateRules($field->getUpdateRules($request));
            $result = array_merge_recursive($result, $rules);
        }

        return $result;
    }

    /**
     * Get additional meta information to merge with the field payload.
     *
     * @return array
     */
    public function meta()
    {
        return array_merge([
            'fields' => collect($this->fields),
        ], $this->meta);
    }
}