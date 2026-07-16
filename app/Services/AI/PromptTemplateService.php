<?php

namespace App\Services\AI;

use InvalidArgumentException;

class PromptTemplateService
{
    public function all(): array
    {
        return config('ai_templates');
    }

    public function get(string $key): array
    {
        $templates = config('ai_templates');

        if (!isset($templates[$key])) {
            throw new InvalidArgumentException("Unknown template: {$key}");
        }

        return $templates[$key];
    }

    public function build(string $key, array $values): string
    {
        $template = $this->get($key);
        $prompt = $template['template'];

        foreach ($template['fields'] as $field) {
            $value = trim($values[$field['name']] ?? '');

            if (($field['required'] ?? false) && $value === '') {
                throw new InvalidArgumentException("Field '{$field['label']}' is required.");
            }

            $prompt = str_replace('{' . $field['name'] . '}', $value, $prompt);
        }

        return $prompt;
    }
}