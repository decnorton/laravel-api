<?php namespace Dec\Transform;

use Input;
use League\Fractal;
use League\Fractal\Resource\Collection;
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

        if (Input::has('embed'))
        {
            $this->fractal->setRequestedScopes(explode(',', Input::get('embed')));
        }
    }

    public function transformCollection($collection, $transformer)
    {
        $resource = new Collection($collection, new $transformer);

        // Turn all of that into JSON
        return $this->fractal->createData($resource)->toArray();
    }

    public function transform($model, $transformer)
    {
        $resource = new Item($model->toArray(), new $transformer);

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
        if (Input::getBoolean('all'))
        {
            if (is_string($model))
            {
                $collection = $model::all();
            }
            else if (is_a($model, '\Illuminate\Database\Eloquent\Builder'))
            {
                $collection = $model->get();
            }
            else
            {
                throw new InvalidArgumentException('Must be string or \Illuminate\Database\Eloquent\Builder');
            }
        }
        else
        {
            if (is_string($model))
            {
                $paginator = $model::paginate(Input::get('count') ?: static::$defaultPageCount);
            }
            else if (is_a($model, '\Illuminate\Database\Eloquent\Builder'))
            {
                $paginator = $model->paginate();
            }
            else
            {
                throw new InvalidArgumentException('Must be string or \Illuminate\Database\Eloquent\Builder');
            }

            $collection = $paginator->getCollection();
        }

        $resource = new Collection($collection, new $transformer);

        if (!empty($paginator))
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return $this->fractal->createData($resource)->toArray();
    }

}