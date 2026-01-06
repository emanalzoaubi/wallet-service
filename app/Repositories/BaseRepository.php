<?php

namespace App\Repositories;

use App\Repositories\Interfaces\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all models.
     *
     * @param int $perPage
     * @param array $filters Filters to apply.
     *                       Format: ['column' => 'value'] - exact match
     *                       Format: ['column' => ['like' => 'value']] - LIKE '%value%'
     *                       Format: ['column' => ['in' => [1,2,3]]] - IN clause
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function all(int $perPage, array $filters = [], array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        foreach ($filters as $column => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            // Handle special filter operators (associative array with string keys)
            if (is_array($value) && array_keys($value) !== range(0, count($value) - 1)) {
                // Associative array - check for special operators
                if (isset($value['like'])) {
                    $query->where($column, 'like', '%' . $value['like'] . '%');
                } elseif (isset($value['in'])) {
                    $query->whereIn($column, $value['in']);
                } elseif (isset($value['not_in'])) {
                    $query->whereNotIn($column, $value['not_in']);
                } elseif (isset($value['between'])) {
                    $query->whereBetween($column, $value['between']);
                } else {
                    // Other operators (>, <, >=, <=, !=, etc.)
                    foreach ($value as $operator => $operatorValue) {
                        $query->where($column, $operator, $operatorValue);
                    }
                }
            } elseif (is_array($value)) {
                // Numeric array - use IN clause
                $query->whereIn($column, $value);
            } else {
                // Simple exact match
                $query->where($column, $value);
            }
        }
    
        return $query->paginate($perPage, $columns);
    }


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
    ): Model {
        $model = $this->model->select($columns)->with($relations)->findOrFail($modelId);
        
        if (!empty($appends)) {
            $model->append($appends);
        }
        
        return $model;
    }

    /**
     * Create a model.
     *
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): ?Model
    {
        $model = $this->model->create($payload);

        return $model->fresh();
    }

    public function insert(array $payload): ?bool
    {
        $this->model->insert($payload);
        return true;
    }

    /**
     * Update existing model.
     *
     * @param int $modelId
     * @param array $payload
     * @return bool
     */
    public function update(int $modelId, array $payload): bool
    {
        $model = $this->findById($modelId);

        return $model->update($payload);
    }
}
