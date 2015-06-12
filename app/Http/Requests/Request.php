<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class Request extends FormRequest {

	public function forbiddenResponse()
    {
        if ($this->ajax() || $this->wantsJson()) {
            return new JsonResponse(['Forbidden'], 403);
        }

        return parent::forbiddenResponse();
    }
}
