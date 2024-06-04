<?php

namespace App\Helpers;

class PaginationHelper {
    public static function pagination($query, $per_page, callable $dataMapper = null) {
        $data = $query->paginate($per_page);

        $items = [];
        foreach ($data as $key => $value) {
            $items[] = !$dataMapper ? $value : $dataMapper($value);
        }

        return [
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
            'data' => $items,
        ];
    }
}
