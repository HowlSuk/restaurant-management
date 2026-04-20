<?php
namespace App\Core;

abstract class Controller
{
    /** Read and decode JSON body (or fall back to $_POST). */
    protected function input(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $data = json_decode($raw, true);
            if (is_array($data)) return $data;
        }
        return $_POST ?: [];
    }

    /** Simple required-field validator. */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $parts = explode('|', $rule);
            foreach ($parts as $r) {
                if ($r === 'required') {
                    if (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null) {
                        $errors[$field] = "$field is required";
                    }
                } elseif ($r === 'email') {
                    if (isset($data[$field]) && !filter_var($data[$field], FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = "$field must be a valid email";
                    }
                } elseif (str_starts_with($r, 'min:')) {
                    $min = (int) substr($r, 4);
                    if (isset($data[$field]) && strlen((string)$data[$field]) < $min) {
                        $errors[$field] = "$field must be at least $min characters";
                    }
                } elseif ($r === 'numeric') {
                    if (isset($data[$field]) && !is_numeric($data[$field])) {
                        $errors[$field] = "$field must be numeric";
                    }
                } elseif ($r === 'integer') {
                    if (isset($data[$field]) && filter_var($data[$field], FILTER_VALIDATE_INT) === false) {
                        $errors[$field] = "$field must be an integer";
                    }
                }
            }
        }
        return $errors;
    }
}
