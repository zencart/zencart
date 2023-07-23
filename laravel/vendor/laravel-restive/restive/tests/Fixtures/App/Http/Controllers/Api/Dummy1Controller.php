<?php
namespace Tests\Fixtures\App\Http\Controllers\Api;

use Restive\Http\Controllers\ApiController;

class Dummy1Controller extends ApiController
{
    protected string $modelName = 'Dummy1';
    protected string $resource = 'Tests\\Fixtures\\App\\Http\\Resources\\DummyResource';
    protected string $resourceCollection = 'Tests\\Fixtures\\App\\Http\\Resources\\DummyResourceCollection';
    protected string $request = 'Tests\\Fixtures\\App\\Http\\Requests\\DummyRequest';
}