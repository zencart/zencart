<?php declare(strict_types=1);

namespace Restive\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Restive\ApiQueryParser;
use Restive\ComponentFactory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Restive\Http\Requests\Request;

class AbstractApiController extends \Illuminate\Routing\Controller
{
    protected Model $model;
    protected string $modelName = '';
    protected string $resource = '';
    protected string $resourceCollection = '';
    protected string $request = '';
    protected ApiQueryParser $parser;
    protected ComponentFactory $componentFactory;
    protected string $paginator = '';

    use AuthorizesRequests;

    public function __construct(ApiQueryParser $apiQueryParser, ComponentFactory $componentFactory)
    {
        $this->componentFactory = $componentFactory;
        $this->model = $this->componentFactory->resolveModel($this->modelName);
        $this->resource = $this->componentFactory->resolveResource($this->model, $this->resource);
        $this->resourceCollection = $this->componentFactory->resolveResourceCollection($this->model, $this->resourceCollection);
        $this->request = $this->componentFactory->resolveRequest($this->model, $this->request);
        $this->componentFactory->bindRequestClass($this->request);
        $this->parser = $apiQueryParser;
        $this->paginator = $this->componentFactory->resolvePaginator($this->paginator);
    }

    public function paginate(Builder $query, Request $request)
    {
        $paginator = new $this->paginator();
        return $paginator->paginate($query, $request);
    }

    public function getRequest() : string
    {
        return $this->request;
    }

    protected function convertIdToParserWhere($id, array $parsedKeys) : array
    {
        if (!isset($id)) {
            return $parsedKeys;
        }
        $key = $this->model->getKeyName();
        $where = $key . ':eq:' . $id;
        $parsedKeys[] = ['where', $where];
        return $parsedKeys;
    }

    protected function stripQueryParams(Request $request) : Request
    {
        return Request::create('/', 'GET', $request->request->all());
    }
}