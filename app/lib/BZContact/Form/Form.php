<?php

namespace BZContact\Form;

/**
 * Generic form class
 */
class Form
{
    public $attributes = [];
    public $fields = [];
    public $names = [];

    /**
     * Validates the form submitted data with the provided validator
     *
     * @param array $data Array of GET/POST data
     * @param Validator $v A validator object
     * @return boolean
     */
    public function validates(array $data, Validator $v)
    {
        $v->rules($this->buildRules());
        $v->labels($this->buildLabels());
        return $v->validate($this->filter($data));
    }

    /**
     * Format options for select type fields
     * @param array $options A list of options in array of strings, array or objects
     * @return array
     */
    public function parseSelectOptions($options = null)
    {
        if (!empty($options && is_array($options))) {
            $selectOptions = [];
            foreach ($options as $option) {
                if (is_object($option)) {
                    $vars = get_object_vars($option);
                    $key = array_keys($vars)[0];
                    $selectOptions[$key] = $vars[$key];
                    continue;
                }
                $selectOptions[] = $option;
            }
            return $selectOptions;
        }
        return [];
    }

    private function buildRules()
    {
        $rules = [];
        foreach ($this->fields as $field) {
            //  Add rule for required fields
            if ($field->required === true) {
                $rules['required'][] = array(
                    'field' => $field->name,
                    'message' => (!empty($field->error)) ? $field->error : '{field} cannot be empty'
                );
            }
            // Add rule for email fields
            if ('email' === $field->type) {
                $rules['email'][] = array(
                    'field' => $field->name,
                    'message' => (!empty($field->error)) ? $field->error : '{field} must be valid'
                );
            }
            // Require acceptance of checkboxes (i.e terms & privacy policy)
            if ('checkbox' === $field->type && $field->required === true) {
                $rules['accepted'][] = array(
                    'field' => $field->name,
                    'message' => (!empty($field->error)) ? $field->error : '{field} must be checked'
                );
            }
            // Add rule for select fields: value must be included in options
            if ('select' === $field->type) {
                $fieldOptions = $this->parseSelectOptions($field->options);
                $acceptedOptions = array_keys($fieldOptions);
                $rules['in'][] = array(
                    'field' => $field->name,
                    'params' => $acceptedOptions,
                    'message' => (!empty($field->error)) ? $field->error : '{field} must be valid'
                );
            }
            //  Add rule for checkbox and radio fields: value must be included in options
            if ('radio' === $field->type && !empty($field->value)) {
            }
        }
        return $rules;
    }

    private function buildLabels()
    {
        $labels = [];
        foreach ($this->fields as $field) {
            // Custom labels for text boxes, emails and phones
            if (!empty($field->label) && in_array($field->type, ['text', 'email', 'tel', 'textarea'])) {
                $labels[$field->name] = $field->label;
            }
        }
        return $labels;
    }

    /**
     * Filter data removing unmapped and not-to-be-saved fields
     *
     * @param array $data Array of form data
     * @return array
     */
    public function filter(array $data)
    {
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->names)) {
                if (isset($this->fields[$this->names[$key]]->save)
                    && false === $this->fields[$this->names[$key]]->save) {
                    continue;
                }
                $filteredData[$key] = $value;
            }
        }
        return $filteredData;
    }
}
