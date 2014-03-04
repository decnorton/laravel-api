<?php namespace Dec\Api\Pagination;

use Carbon\Carbon;
use DateTime;
use Exception;
use Input;
use InvalidArgumentException;
use Response;

class Paginator {

    public static $defaultCount = 20;
    public static $defaultSimpleFields = ['id', 'name'];


    public static function paginate($model, array $simpleFields = null)
    {
        $showAll = Input::getBoolean('all');
        $simple = Input::getBoolean('simple');
        $with = Input::has('with') ? explode(',', Input::get('with')) : null;

        $simpleFields = $simpleFields ?: static::$defaultSimpleFields;

        if (is_string($model))
        {
            $builder = $model::query();
        }
        else
        {
            $builder = $model;
            $model = $builder->getModel();
        }

        if (!is_a($builder, 'Illuminate\Database\Eloquent\Builder'))
            throw new InvalidArgumentException('$model should be a string or Builder');


        if ($simple)
        {
            // Remove eager loads so we don't have null objects in the response
            $builder->setEagerLoads([]);
            $builder->select($simpleFields);
        }
        else if (count($with) > 0)
        {
            foreach ($with as $relation)
            {
                if (method_exists($model, $relation))
                    $builder->with($relation);
            }
        }

        if (Input::has('since'))
        {
            try
            {
                $builder->where('updated_at', '>', Input::getTimestamp('since'));
            }
            catch (Exception $e)
            {
                return Response::error("Invalid 'since' timestamp");
            }
        }

        if ($showAll)
        {
            return Response::json([
                'all' => true,
                'data' => $builder->get()->toArray()
            ], 200);
        }

        $count = Input::get('count') ?: static::$defaultCount;
        return Response::json($builder->paginate($count)->toArray(), 200);
    }

}
