<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
interface BaseRepositoryInterface{
   /**
     * Get all models.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @param array $filters Filters to apply using LIKE operator.
     *                       Format: ['column' => 'value'] - applies LIKE '%value%'
     * @return LengthAwarePaginator
     */
    public function all(int $perPage,array $filters = [], array $columns = ['*'], array $relations = []): LengthAwarePaginator;

    /**
     * Find model by id.
     *
     * @param int $modelId
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model
     */
    public function findById(
        int $modelId,
        array $columns = ['*'],
        array $relations = [],
        array $appends = []
    ): ?Model;

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
