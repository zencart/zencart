<?php declare(strict_types=1);

namespace Restive\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules() : array
    {
        if (!$this->route()) {
            return [];
        }

        if ($this->route()->getActionMethod() === 'store') {
            return array_merge($this->commonRules(), $this->storeRules());
        }

        if ($this->route()->getActionMethod() === 'update') {
            return array_merge($this->commonRules(), $this->updateRules());
        }

        return [];
    }

    public function commonRules() : array
    {
        return [];
    }

    public function storeRules() : array
    {
        return [];
    }

    public function updateRules() : array
    {
        return [];
    }

    public function failedValidation(Validator $validator)
    {
        $jsonResponse = response()->json(['errors' => $validator->errors()], 422);
        throw new HttpResponseException($jsonResponse);
    }
}
