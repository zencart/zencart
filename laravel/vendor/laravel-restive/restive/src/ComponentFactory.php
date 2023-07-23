<?php declare(strict_types=1);

namespace Restive;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Restive\Exceptions\InvalidModelException;
use Restive\Http\Requests\Request;
use Restive\Http\Resources\CollectionResource;
use Restive\Http\Resources\Resource;

class ComponentFactory
{
    protected string $modelNamespacePrefix = '';
    protected string $resourceNamespacePrefix = '';
    protected string $resourceCollectionNamespacePrefix = '';
    protected string $requestNamespacePrefix = '';

    public function resolveModel(string $modelName) : Model
    {
        if (empty($modelName)) {
            throw new InvalidModelException();
        }
        if (class_exists(ucfirst($modelName), true)) {
            $className = ucfirst($modelName);
            return new $className;
        }
        if (class_exists($this->modelNamespacePrefix . '\\App\\' . ucfirst($modelName), true)) {
            $className = $this->modelNamespacePrefix . '\\App\\' . ucfirst($modelName);
            return new $className;
        }
        if (class_exists($this->modelNamespacePrefix . '\\App\\Models\\' . ucfirst($modelName), true)) {
            $className = $this->modelNamespacePrefix . '\\App\\Models\\' . ucfirst($modelName);
            return new $className;
        }
        throw new InvalidModelException();
    }

    public function resolveResource(Model $model, string $resource): string
    {
        if (!empty($resource)) {
            return $resource;
        }
        $model = class_basename($model);
        $resourceName = $model . 'Resource';
        if (class_exists($this->resourceNamespacePrefix . '\\App\\Http\\Resources\\' . $resourceName, true)) {
            return $this->resourceNamespacePrefix . '\\App\\Http\\Resources\\' . $resourceName;
        }
        return Resource::class;
    }

    public function resolveResourceCollection($model, string $resourceCollection): string
    {
        if (!empty($resourceCollection)) {
            return $resourceCollection;
        }
        $model = class_basename($model);
        $resourceName = $model . 'CollectionResource';
        if (class_exists($this->resourceCollectionNamespacePrefix . '\\App\\Http\\Resources\\' . $resourceName, true)) {
            return $this->resourceCollectionNamespacePrefix . '\\App\\Http\\Resources\\' . $resourceName;
        }
        return CollectionResource::class;
    }

    public function resolveRequest($model, string $request): string
    {
        if (!empty($request)) {
            return $request;
        }
        $model = class_basename($model);
        $requestName = $model . 'Request';
        if (class_exists($this->requestNamespacePrefix . '\\App\\Http\\Requests\\' . $requestName, true)) {
            $className = $this->requestNamespacePrefix . '\\App\\Http\\Requests\\' . $requestName;
            return $className;
        }
        return Request::class;
    }

    public function resolvePaginator(string $paginator): string
    {
        if (!empty($paginator)) {
            return $paginator;
        }
        return Paginator::class;
    }

    public function setModelNamespacePrefix(string $prefix): void
    {
        $this->modelNamespacePrefix = $prefix;
    }

    public function setResourceNamespacePrefix(string $prefix): void
    {
        $this->resourceNamespacePrefix = $prefix;
    }

    public function setResourceCollectionNamespacePrefix(string $prefix): void
    {
        $this->resourceCollectionNamespacePrefix = $prefix;
    }

    public function setRequestNamespacePrefix(string $prefix): void
    {
        $this->requestNamespacePrefix = $prefix;
    }

    public function bindRequestClass(string $requestClass): void
    {
        App::bind(Request::class, $requestClass);
    }

}