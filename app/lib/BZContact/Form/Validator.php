<?php

namespace BZContact\Form;

use Valitron\Validator as V;

/**
 * Validator class
 *
 * A wrapper for the validator module of choice, currently Valitron
 * @see https://github.com/vlucas/valitron
 */
class Validator
{
    private $v = null;
    private $rules = [];
    private $labels = [];

    /**
     * Validates provided data
     *
     * @param array $data Array of data to validate
     * @return boolean
     */
    public function validate(array $data)
    {
        $this->v = new V($data);
        $this->buildRules();
        return $this->v->validate();
    }

    private function buildRules()
    {
        if (!isset($this->v)) {
            return false;
        }
        foreach ($this->rules as $ruleName => $ruleData) {
            foreach ($ruleData as $ruleDataItem) {
                $params = (!empty($ruleDataItem['params'])) ? $ruleDataItem['params'] : null;
                $r = $this->v->rule($ruleName, $ruleDataItem['field'], $params);
                if (!empty($ruleDataItem['message'])) {
                    $r->message($ruleDataItem['message']);
                }
            }
        }
        $this->v->labels($this->labels);
    }

    /**
     * Set a single validator rule
     *
     * @param array $rules Array of rules to use for validation
     * @return void
     */
    public function rule($rule, $fields)
    {
        return $this->v->rule($rule, $fields);
    }

    /**
     * Set batch validator rules
     *
     * @param array $rules Array of rules to use for validation
     * @return void
     */
    public function rules(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Set validator labels for error messages
     *
     * @param array $rules Array of rules to use for validation
     * @return void
     */
    public function labels(array $labels)
    {
        $this->labels = $labels;
    }

    /**
     * Return validation error messages
     *
     * [field1 => ['Error on rule A', 'Error on rule B'], field2 => [...]]
     *
     * @return array
     */
    public function errors()
    {
        if (isset($this->v)) {
            return $this->v->errors();
        }
        return [];
    }
}
