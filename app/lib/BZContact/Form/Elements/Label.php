<?php

namespace BZContact\Form\Elements;

use BZContact\Form\Form;
use AdamWathan\Form\Elements;
use Michelf\Markdown;

/**
 * Enhanced Label element
 */
class Label extends Elements\Label
{
    protected $form;

    /**
     * Creates a new label with a link to the current Form
     *
     * @param string $label The label text
     * @param Form   $form  The current form that contains the label
     * @return void
     */
    public function __construct($label = null, Form $form = null)
    {
        parent::__construct($label);
        $this->form = $form;
    }

    /**
     * Generate a label for the given element id
     *
     * Search the form is the given element has a label text defined,
     * allows Markdown in label text.
     *
     * @param string $name The id of the element to bind
     * @return Label
     */
    public function forId($name)
    {
        parent::forId($name);
        if ($this->form->fields[$name]) {
            $this->setAttribute('for', $this->form->fields[$name]->id);

            // Use field provided label only if passed label is empty
            if (empty($this->label)) {
                // Fallback to id
                $this->label = $this->form->fields[$name]->id;
                // Use label provided with the field
                if (!empty($this->form->fields[$name]->label)) {
                    $this->label = trim(str_replace(
                        ['<p>','</p>'],
                        '',
                        Markdown::defaultTransform($this->form->fields[$name]->label)
                    ));
                }
            }
        }
        return $this;
    }
}
