<?php

namespace BZContact\Form\Elements;

use AdamWathan\Form\Elements;

/**
 * Manage a group of radio buttons as a fieldset
 */
class RadioButtonGroup extends Elements\Element
{
    protected $group;
    protected $required = false;
    protected $options = [];
    protected $labels = [];

    public function __construct($name, $field)
    {
        $this->group = $field;
        $this->initOptions();
    }

    private function initOptions()
    {
        $this->required = $this->group->required;
        foreach ($this->group->options as $option) {
            // Allow for simple string-only options
            $opt = $option;
            if (!is_object($option)) {
                $opt = new \stdClass;
                $opt->value = $option;
            }

            // Create option field
            $radio = new Elements\RadioButton($this->group->name, $opt->value);

            // Assign id, default is '<group-name>-<option-value>'
            $id = !empty($opt->id) ? $opt->id : $this->group->id . '-' . $opt->value;
            $radio->id($id);

            if ($this->required) {
                $radio->required();
            }

            $this->options[$id] = $radio;
            $this->labels[$id] = !empty($opt->label) ? $opt->label : $opt->value;
        }
    }

    public function required()
    {
        $this->required = true;
        return $this;
    }

    public function setOldValue($value)
    {
        foreach ($this->options as $option) {
            $option->setOldValue($value);
        }
    }

    public function render()
    {
        // Open group container
        $html = '<fieldset id="' . $this->group->id . '">';

        // Add legend, if available
        if (!empty($this->group->label)) {
            $html .= '<legend>' . $this->group->label . '</legend>';
        }

        // Render options
        foreach ($this->options as $optionId => $option) {
            // Wrap the single radio in a label, use custom label or option value
            $html .= '<label>' . $option->__toString() . $this->labels[$optionId] . '</label>';
        }

        // Close container
        $html .= '</fieldset>';
        return $html;
    }
}
