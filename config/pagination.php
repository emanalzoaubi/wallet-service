<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Items Per Page
    |--------------------------------------------------------------------------
    |
    | This value is the default number of items that will be returned per page
    | when paginating results. This can be overridden by passing a 'per_page'
    | query parameter in the request.
    |
    */

    'per_page' => env('PAGINATION_PER_PAGE', 10),
];

