<?php
namespace BZContact\Form;

/**
 * Build a Form from a JSON description string
 * @see https://github.com/adamwathan/form
 */
class FormBuilder extends \AdamWathan\Form\FormBuilder
{
    private $form;

    /**
     * @param  string  $data  JSON string
     * @return void
     */
    public function __construct($data)
    {

        // Try to parse JSON
        if (($form = json_decode($data)) === null) {
            $jsonErrorMessage = 'Unknown error';
            if (JSON_ERROR_NONE !== json_last_error()) {
                $jsonErrorMessage = json_last_error_msg();
            }
            throw new \ErrorException(sprintf('Invalid JSON data: %s', $jsonErrorMessage));
        }

        // We expect to have some fields
        if (empty($form->fields)) {
            throw new \ErrorException('Invalid form data: no fields');
        }

        $this->form = new Form;

        // Parse attributes
        if (!empty($form->attributes)) {
            foreach ($form->attributes as $k => $v) {
                $this->form->attributes[$k] = $v;
            }
        }

        $fieldCount = 1;
        $errors = [];
        $ids = [];
        foreach ($form->fields as $field) {
            // ID is autogenerated if not provided
            if (empty($field->id)) {
                $field->id = 'field-' . $fieldCount;
            }

            // Name is derived from ID
            if (empty($field->name)) {
                $field->name = $field->id;
            }

            // ID, if provided must be unique
            if (!empty($field->id)) {
                if (in_array($field->id, $ids)) {
                    $errors[] = sprintf("Attribute 'id' must be unique, field %d", $fieldCount);
                } else {
                    $ids[] = $field->id;
                }
            }

            // Type is optional, default to text
            if (empty($field->type)) {
                $field->type = 'text';
            }

            // Required is optional, default to false
            if (!isset($field->required)) {
                $field->required = false;
            }

            $this->form->fields[$field->id] = $field;

            // Keep field index by name
            $this->form->names[$field->name] = $field->id;

            $fieldCount++;
        }

        if (!empty($errors)) {
            throw new \ErrorException(sprintf('Invalid form data: %s', array_pop($errors)));
        }
    }

    /**
     * Builds a custom form element of type 'tel'
     *
     * @param string $name The name of the element
     * @return Elements\Tel
     */
    public function tel($name)
    {
        $tel = new Elements\Tel($name);
        if (!is_null($value = $this->getValueFor($name))) {
            $tel->value($value);
        }
        return $tel;
    }

    /**
     * Builds an enhanced form label element
     *
     * @param string $label The label text
     * @return Elements\Label
     */
    public function label($label = '')
    {
        $label = new Elements\Label($label, $this->form);
        return $label;
    }

    /**
     * Builds an enhanced form opening tag
     *
     * @return Elements\FormOpen
     */
    public function open()
    {
        $open = new Elements\FormOpen;

        if ($this->hasToken()) {
            $open->token($this->csrfToken);
        }

        foreach ($this->form->attributes as $k => $v) {
            switch ($k) {
                case 'class':
                    $open->addClass($v);
                    break;

                case 'method':
                    $method = strtolower($v);
                    if (!in_array($method, ['get', 'post', 'put', 'delete', 'patch'])) {
                        throw new \ErrorException(sprintf('Invalid form method: %s', $method));
                    }
                    $open->{$method}();
                    break;

                case 'action':
                    $open->action($v);
                    break;
                case 'encoding':
                    if ('multipart' === $v) {
                        $open->multipart();
                    } else {
                        $open->encodingType($v);
                    }
                    break;
                default:
                    $open->attribute($k, $v);
                    break;
            }
        }
        return $open;
    }

    /**
     * Retrieves a form field by its id
     *
     * @param string $id The id of the form element
     * @return Elements\Element|Elements\EmptyElement
     */
    public function field($id)
    {
        if (!empty($this->form->fields[$id])) {
            $field = $this->form->fields[$id];

            // Deal with submit and button field types
            if ('submit' === $field->type || 'button' === $field->type) {
                $buttonValue = (!empty($field->value)) ? $field->value : $field->name;
                $obj = $this->button($buttonValue, $field->name)->id($field->id);
                $obj->type($field->type);
            } else {
                $obj = $this->{$field->type}($field->name)->id($field->id);
            }
            // Deal with textarea
            if ('textarea' === $field->type) {
                if (!empty($field->rows)) {
                    $obj->rows($field->rows);
                }
                if (!empty($field->cols)) {
                    $obj->cols($field->cols);
                }
            }
            // Deal with select
            if ('select' === $field->type) {
                $obj->options($this->form->parseSelectOptions($field->options));
            }
            if (!empty($field->placeholder)) {
                $obj->placeholder($field->placeholder);
            }
            if (isset($field->required) && $field->required) {
                $obj->required();
                if (!empty($field->error)) {
                    $obj->data('msg-required', $field->error);
                } else {
                    $obj->data(
                        'msg-required',
                        sprintf('%s cannot be empty', !empty($field->label) ? $field->label : $field->name)
                    );
                }
            }
            if (!empty($field->class)) {
                $obj->addClass($field->class);
            }
            if (!empty($field->value)) {
                $obj->value($field->value);
            }
            return $obj;
        }
        return new Elements\EmptyElement();
    }

    /**
     * Render a radio button as a fieldset
     */
    public function radio($name, $value = null)
    {
        $rbg = new Elements\RadioButtonGroup($name, $this->form->fields[$name]);

        $oldValue = $this->getValueFor($name);
        $rbg->setOldValue($oldValue);

        return $rbg;
    }

    /**
     * Validates the form submitted data with the provided validator
     *
     * @param array $data Array of GET/POST data
     * @param Validator $v A validator object
     * @return boolean
     */
    public function validates(array $data, Validator $v)
    {
        return $this->form->validates($data, $v);
    }

    /**
     * Filter data removing unmapped and not-to-be-saved fields
     *
     * @param array $data Array of form data
     * @return array
     */
    public function filter(array $data)
    {
        return $this->form->filter($data);
    }

    /**
     * Return field objects for rendering
     * @return array
     */
    public function fields()
    {
        return $this->form->fields;
    }

    /**
     * Retrieves errors for a field
     *
     * @param string $id The id of the form element
     * @param string $format Formatted output string (not used)
     * @return string
     */
    public function getError($id, $format = null)
    {
        if (!empty($this->form->fields[$id])) {
            return parent::getError($this->form->fields[$id]->name, '<span class="error">:message</span>');
        }
        return '';
    }
}
