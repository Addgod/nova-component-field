<?php

namespace Addgod\ComponentField;

use Illuminate\Support\Str;
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
     * The internal identifier for this component. It will be based on the $name parameter.
     *
     * @var
     */
    private $intl_slug;

    /**
     * ComponentField constructor.
     *
     * @param      $name
     * @param null $attribute
     * @param null $resolveCallback
     */
    public function __construct($name, $attribute = null, $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->intl_slug = str_replace(' ', '_', Str::lower($name));
    }

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
    protected function generateArrayRules(array $rules)
    {
        return collect($rules)->mapWithKeys(function ($rules, $key) {

            return [$this->attribute . '.*.' . $key => $rules];
        })->toArray();
    }

    /**
     * Generate field-specific validation rules.
     *
     * @param  array $rules
     *
     * @return array
     */
    protected function generateNestedRules(array $rules)
    {
        return collect($rules)->mapWithKeys(function ($rules, $key) {

            return [$this->attribute . '.' . $key => $rules];
        })->toArray();
    }

    /**
     * Get the validation rules for this field.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest $request
     *
     * @return array
     */
    public function getRules(NovaRequest $request)
    {
        $result = [];
        foreach ($this->fields as $field) {
            if ($field instanceof $this) {
                $rules = $this->generateArrayRules($field->getRules($request));
            } else {
                $rules = $this->generateNestedRules($field->getRules($request));
            }
            $result = array_merge_recursive($result, $rules);
        }

        return $result;
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
            if ($field instanceof $this) {
                $rules = $this->generateNestedRules($field->getCreationRules($request));
            } else {
                $rules = $this->generateArrayRules($field->getCreationRules($request));
            }
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
            if ($field instanceof $this) {
                $rules = $this->generateNestedRules($field->getUpdateRules($request));
            } else {
                $rules = $this->generateArrayRules($field->getUpdateRules($request));
            }
            $result = array_merge_recursive($result, $rules);
        }

        return $result;
    }

    /**
     * Set the layout of the component fields to horizontal.
     *
     * @param bool $horizontal
     *
     * @return \Addgod\ComponentField\ComponentField
     */
    public function horizontal($horizontal = true)
    {
        return $this->withMeta([
            'horizontal' => $horizontal,
        ]);
    }

    /**
     * Get additional meta information to merge with the field payload.
     *
     * @return array
     */
    public function meta()
    {
        return array_merge([
            'fields'    => collect($this->fields),
            'intl_slug' => $this->intl_slug,
        ], $this->meta);
    }

}
