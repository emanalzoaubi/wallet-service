<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
interface BaseRepositoryInterface{
   /**
     * Get all models.
     *
     * @param int $perPage
     * @param array $filters Filters to apply.
     *                       Format: ['column' => 'value'] - exact match
     *                       Format: ['column' => ['like' => 'value']] - LIKE '%value%'
     *                       Format: ['column' => ['in' => [1,2,3]]] - IN clause
     *                       Format: ['column' => ['between' => [min, max]]] - BETWEEN clause
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function all(int $perPage, array $filters = [], array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Find model by id (throws exception if not found).
     *
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): Model;

    /**
     * Create a model.
     *
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): ?Model;

    /**
     * Insert multiple records into the database.
     *
     * @param array $payload Array of records to insert
     * @return bool|null Returns true on success, false on failure, or null if no records were inserted
     */
    public function insert(array $payload): ?bool;

    /**
     * Update existing model.
     *
     * @param int $modelId
     * @param array $payload
     * @return bool
     */
    public function update(int $modelId, array $payload): bool;

}
