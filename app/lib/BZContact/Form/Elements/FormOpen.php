<?php
namespace BZContact\Form\Elements;

use AdamWathan\Form\Elements;

class FormOpen extends Elements\FormOpen
{

    public function render()
    {
        $tags = [sprintf('<form%s>', $this->renderAttributes())];

        if ($this->hasToken() && ($this->attributes['method'] !== 'GET')) {
            foreach ($this->token as $token) {
                $tags[] = $token->render();
            }
        }

        if ($this->hasHiddenMethod()) {
            $tags[] = $this->hiddenMethod->render();
        }

        return implode($tags);
    }

    /**
     * Creates CSRF token
     *
     * The token format is ['csrf_name' => '...', 'csrf_value' => '...']
     *
     * @param  array $token Associative array of token fields
     * @return AdamWathan\Form\Elements\Input
     */
    public function token($token)
    {
        foreach ($token as $key => $value) {
            $this->token[$key] = new Elements\Hidden($key);
            $this->token[$key]->value($value);
        }
        return $this;
    }
}
