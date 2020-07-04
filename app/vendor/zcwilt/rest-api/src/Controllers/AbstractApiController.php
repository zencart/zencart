<?php

namespace Zcwilt\Api\Controllers;

use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\QueryException;
use Zcwilt\Api\Exceptions\ApiException;

class AbstractApiController extends Controller
{
    /**
     * @var int
     */
    protected $statusCode = 200;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): AbstractApiController
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    protected function respond(array $data, array $headers = []): JsonResponse
    {
        return Response::json($data, $this->getStatusCode(), $headers);
    }

    /**
     * @param mixed $message array || string
     * @return JsonResponse
     */
    protected function respondWithError($message): JsonResponse
    {
        return $this->respond([
            'error' => [
                'message' => $message,
                'status_code' => $this->getStatusCode()
            ]
        ]);
    }

    protected function handleExceptionMessage(\Exception $e)
    {
        $queryException = false;
        if ($e instanceof QueryException || $e instanceof RelationNotFoundException) {
            $queryException = true;
        }
        $production = (\App::environment() === 'production');
        if ($e instanceof ApiException) {
            return $e->getMessage();
        }
        $message = 'Invalid Query - probably invalid field name';
        if ($queryException && $production) {
            return $message;
        }
        if ($queryException) {
            return $e->getMessage();
        }
        throw $e;
    }
}
