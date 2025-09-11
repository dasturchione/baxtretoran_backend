<?php

namespace App\Helpers;

class TemplateRenderer
{
    public static function render(string $content, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $content = str_replace("{" . $key . "}", $value, $content);
        }
        return $content;
    }
}
