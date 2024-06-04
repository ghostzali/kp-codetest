<?php

namespace App;

class Functions {
    /**
     * format error message
     * @param array|string $errors
     * @return string
     */
    public function error(...$errors) {
        $message = is_array($errors) ? json_encode($errors) : $errors;
        return $message ? $message : '';
    }

    /**
     * attributes by selected columns
     */
    public function firstCreate(array $data, array $columns = ['*']) {
        $attributes = array_filter($data, function($key) use ($columns) {
            return $columns.contains($key);
        });

        return $attributes;
    }
}
