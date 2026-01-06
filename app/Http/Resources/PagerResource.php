<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PagerResource
{
    /**
     * Transform pagination data into a consistent format.
     *
     * @param LengthAwarePaginator $paginator
     * @return array
     */
    public static function from(LengthAwarePaginator $paginator): array
    {
        return [
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }
}

