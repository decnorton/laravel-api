<?php namespace Dec\Api\Transform;

use Carbon\Carbon;
use Exception;
use Input;
use League\Fractal;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class Transformer {

    /**
     * Fractal instance
     *
     * @var League\Fractal\Manager
     */
    protected $fractal;

    /**
     * Default page count
     *
     * @var int
     */
    public static $defaultPageCount = 20;

    public function __construct()
    {
        $this->fractal = new Fractal\Manager;

        if (Input::has('with'))
        {
            $this->fractal->setRequestedScopes(explode(',', Input::get('with')));
        }
    }

    public function transformCollection($collection, $transformer)
    {
        if (!$collection)
            return null;

        $resource = new Collection($collection, new $transformer);

        return $this->fractal->createData($resource)->toArray();
    }

    public function transform($model, $transformer)
    {
        if (!$model)
            return null;

        $resource = new Item($model, new $transformer);

        return $this->fractal->createData($resource)->toArray();
    }

    /**
     * Paginate a resource
     *
     * @param  mixed    $model
     * @param  string   $transformer    Name of the transformer
     * @return array
     */
    public function paginate($model, $transformer)
    {
        // If we got a string, get a new \Eloquent\Builder
        if (is_string($model))
            $builder = $model::query();

        // If it's not a string and not a builder, reject
        else if (!is_a($model, '\Illuminate\Database\Eloquent\Builder'))
            throw new InvalidArgumentException('Must be string or \Illuminate\Database\Eloquent\Builder');

        // Must be a builder
        else
            $builder = $model;

        // Check for a since parameter
        if ($since = Input::get('since'))
        {
            try
            {
                $since = new Carbon($since);
                $builder->where('updated_at', '>', $since);
            }
            catch (Exception $e) { /* Ignore */ }
        }

        if (Input::getBoolean('all'))
        {
            $collection = $builder->get();
        }
        else
        {
            $paginator = $builder->paginate(Input::get('count') ?: static::$defaultPageCount);
            $collection = $paginator->getCollection();
        }

        if (is_string($transformer))
            $transformer = new $transformer;

        $resource = new Collection($collection, $transformer);

        if (!empty($paginator))
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->fractal->createData($resource)->toArray();
    }

    public function paginateCollection($collectio, $tranformer)
    {

    }

}