<?php
function markdown($text)
{
    return Michelf\Markdown::defaultTransform($text);
}
