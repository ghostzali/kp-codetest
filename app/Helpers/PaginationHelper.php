<?php

namespace App\Helpers;

class PaginationHelper {
    public static function pagination($query, $per_page, callable $dataMapper = null) {
        $isHasPerPage = !!$per_page;
        $data = $isHasPerPage ? $query->paginate($per_page) : $query->get();

        $items = [];
        foreach ($data as $key => $value) {
            $items[] = !$dataMapper ? $value : $dataMapper($value);
        }

        return [
            'total' => $isHasPerPage ? $data->total() : $query->count(),
            'per_page' => $isHasPerPage ? $data->perPage() : 0,
            'current_page' => $isHasPerPage ? $data->currentPage() : 1,
            'last_page' => $isHasPerPage ? $data->lastPage() : 1,
            'from' => $isHasPerPage ? $data->firstItem() : 1,
            'to' => $isHasPerPage ? $data->lastItem() : $query->count(),
            'data' => $items,
        ];
    }
}
