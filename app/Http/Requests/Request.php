<?php namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;

abstract class Request extends FormRequest {

	public function forbiddenResponse()
    {
        return new JsonResponse(['error'=>['message'=>'Not authorized for this request. Forbidden.']], 403);
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();
        $validator->addImplicitExtension('gt', function($attribute, $value, $parameters) {

            if($value > $parameters[0]) {
                return true;
            }
            return false;
        });

        return $validator;
    }

    public function response(array $errors)
    {
        $err_msg = "";
        foreach($errors as $error) {
            $err_msg .= implode(", ", $error);
        }
        throw new \Exception($err_msg);
    }
}
