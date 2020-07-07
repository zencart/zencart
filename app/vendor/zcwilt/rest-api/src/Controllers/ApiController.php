<?php

namespace Zcwilt\Api\Controllers;

use Illuminate\Http\Request;
use Zcwilt\Api\ApiQueryParser;
use Zcwilt\Api\ParserFactory;
use Zcwilt\Api\ModelMakerFactory;
use Illuminate\Http\JsonResponse;
use Validator;

class ApiController extends AbstractApiController
{
    /**
     * @var Illuminate\Database\Eloquent\Model
     */
    protected $model;

    public function __construct(
        ModelMakerFactory $modelMaker
    ) {
        $this->model = $modelMaker->make($this->modelName);
    }

    public function index(Request $request): jsonResponse
    {
        try {
            $parser = new ApiQueryParser(new ParserFactory());
            $query = $parser->parseRequest($request)->buildparsers()->buildQuery($this->model);
            $count = $query->count();
            $defaultPerpage = 15;
            if ($request->input('paginate', 'yes') === 'no') {
                $defaultPerpage = $count;
            }
            $result = $query->paginate($request->input('per_page', $defaultPerpage));
        } catch (\Exception $e) {
            $message = $this->handleExceptionMessage($e);
            return $this->setStatusCode(400)->respondWithError($message);
        }
        return $this->respond($result->toArray());
    }

    public function store(Request $request): jsonResponse
    {
        $validator = Validator::make($request->all(), $this->loadRules());
        if ($validator->fails()) {
            return $this->setStatusCode(400)->respondWithError($validator->errors());
        }
        try {
            $result = $this->model->create($request->all());
        } catch (\Exception $e) {
            $message = $this->handleExceptionMessage($e);
            return $this->setStatusCode(400)->respondWithError($message);
        }
        return $this->respond([
            'data' => $result
        ]);
    }

    /**
     * @param mixed $id
     * @return JsonResponse
     */
    public function show($id): jsonResponse
    {
        $result = $this->model->find($id);
        if (!$result) {
            return $this->setStatusCode(400)->respondWithError('item does not exist');
        }

        return $this->respond([
            'data' => $result->toArray()
        ]);
    }

    /**
     * @param mixed $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request)
    {
        $result = $this->model->find($id);
        if (!$result) {
            return $this->setStatusCode(400)->respondWithError('item does not exist');
        }
        $validator = Validator::make($request->all(), $this->loadRules($id));
        if ($validator->fails()) {
            return $this->setStatusCode(400)->respondWithError($validator->errors());
        }

        $result->update($request->all());

        return $this->respond([
            'data' => $result->toArray()
        ]);
    }

    /**
     * @param mixed $id
     * @return JsonResponse
     */
    public function destroy($id): jsonResponse
    {
        $result = $this->model->find($id);
        if (!$result) {
            return $this->setStatusCode(400)->respondWithError('item does not exist');
        }
        $result->delete();
        return $this->respond([
            'data' => $result
        ]);
    }

    public function destroyByQuery(Request $request): jsonResponse
    {
        try {
            $parser = new ApiQueryParser(new ParserFactory());
            $query = $parser->parseRequest($request)->buildparsers()->buildQuery($this->model);
        } catch (\Exception $e) {
            $message = $this->handleExceptionMessage($e);
            return $this->setStatusCode(400)->respondWithError($message);
        }
        $result=$query->get();
        $query->each(function ($record) {
            $record->delete();
        });
        return $this->respond([
            'data' => $result->toArray()
        ]);
    }

    public function updateByQuery(Request $request): jsonResponse
    {
        try {
            $parser = new ApiQueryParser(new ParserFactory());
            $query = $parser->parseRequest($request)->buildparsers()->buildQuery($this->model);
            $result = $query->update($request->all()['fields']);
        } catch (\Exception $e) {
            $message = $this->handleExceptionMessage($e);
            return $this->setStatusCode(400)->respondWithError($message);
        }
        return $this->respond([
            'data' => 'affected rows = ' . $result
        ]);
    }

    /**
     * @param mixed $id
     * @return array
     */
    protected function loadRules($id = 0)
    {
        if (method_exists($this->model, 'rules')) {
            return $this->model->rules($id);
        }
        return [];
    }
}
