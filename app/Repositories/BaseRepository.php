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
     * @param array $filters Filters to apply using LIKE operator.
     *                       Format: ['column' => 'value'] - applies LIKE '%value%'
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function all(int $perPage, array $filters = [], array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        $query = $this->model->with($relations);

        foreach ($filters as $column => $value) {
            
            $query->where(function ($q) use ($column, $value) {
                $values = (array) $value;
                
                foreach ($values as $val) {
                    $q->orWhereLike($column, '%' . $val . '%');
                }
            });
        }
    
        return $query->paginate($perPage, $columns);
    }


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
    ): ?Model {
        return $this->model->select($columns)->with($relations)->findOrFail($modelId)->append($appends);
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
